<?php
/**
 * Cron-Job Handler für automatischen Sync
 *
 * @package    Repro_CT_Suite
 * @subpackage Repro_CT_Suite/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Repro_CT_Suite_Cron {

	/**
	 * Hook-Name für den Cron-Job
	 */
	const HOOK_NAME = 'repro_ct_suite_auto_sync';

	/**
	 * Initialisierung
	 */
	public static function init(): void {
		// Cron-Hook registrieren
		add_action( self::HOOK_NAME, array( __CLASS__, 'execute_sync' ) );
		
		// Custom Cron-Intervalle hinzufügen
		add_filter( 'cron_schedules', array( __CLASS__, 'add_custom_cron_intervals' ) );
		
		// Bei Plugin-Deaktivierung aufräumen
		register_deactivation_hook( REPRO_CT_SUITE_FILE, array( __CLASS__, 'cleanup' ) );
	}

	/**
	 * Fügt benutzerdefinierte Cron-Intervalle hinzu
	 *
	 * @param array $schedules Vorhandene Intervalle
	 * @return array Erweiterte Intervalle
	 */
	public static function add_custom_cron_intervals( array $schedules ): array {
		// 30 Minuten
		$schedules['repro_ct_suite_30min'] = array(
			'interval' => 1800,
			'display'  => __( 'Alle 30 Minuten', 'repro-ct-suite' )
		);
		
		// 2 Stunden
		$schedules['repro_ct_suite_2hours'] = array(
			'interval' => 7200,
			'display'  => __( 'Alle 2 Stunden', 'repro-ct-suite' )
		);
		
		// 3 Stunden
		$schedules['repro_ct_suite_3hours'] = array(
			'interval' => 10800,
			'display'  => __( 'Alle 3 Stunden', 'repro-ct-suite' )
		);
		
		// 4 Stunden
		$schedules['repro_ct_suite_4hours'] = array(
			'interval' => 14400,
			'display'  => __( 'Alle 4 Stunden', 'repro-ct-suite' )
		);
		
		// 6 Stunden
		$schedules['repro_ct_suite_6hours'] = array(
			'interval' => 21600,
			'display'  => __( 'Alle 6 Stunden', 'repro-ct-suite' )
		);
		
		// 12 Stunden
		$schedules['repro_ct_suite_12hours'] = array(
			'interval' => 43200,
			'display'  => __( 'Alle 12 Stunden', 'repro-ct-suite' )
		);
		
		return $schedules;
	}

	/**
	 * Scheduliert den Sync-Job neu basierend auf den Einstellungen
	 */
	public static function reschedule_sync_job(): void {
		// Alten Job entfernen
		$timestamp = wp_next_scheduled( self::HOOK_NAME );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, self::HOOK_NAME );
		}
		
		// Prüfen ob Auto-Sync aktiviert ist
		$enabled = get_option( 'repro_ct_suite_auto_sync_enabled', 0 );
		if ( ! $enabled ) {
			return;
		}
		
		// Intervall-Einstellungen abrufen
		$interval = get_option( 'repro_ct_suite_sync_interval', 60 );
		$unit = get_option( 'repro_ct_suite_sync_interval_unit', 'minutes' );
		
		// Intervall in Sekunden berechnen
		$interval_seconds = self::calculate_interval_seconds( $interval, $unit );
		
		// Mindestens 30 Minuten
		if ( $interval_seconds < 1800 ) {
			$interval_seconds = 1800;
		}
		
		// Passenden Recurrence-String finden oder erstellen
		$recurrence = self::get_recurrence_string( $interval_seconds );
		
		// Neuen Job schedulen
		if ( ! wp_next_scheduled( self::HOOK_NAME ) ) {
			wp_schedule_event( time(), $recurrence, self::HOOK_NAME );
		}
	}

	/**
	 * Berechnet Intervall in Sekunden
	 *
	 * @param int    $interval Intervall-Wert
	 * @param string $unit     Einheit (minutes, hours, days)
	 * @return int Sekunden
	 */
	private static function calculate_interval_seconds( int $interval, string $unit ): int {
		switch ( $unit ) {
			case 'hours':
				return $interval * 3600;
			case 'days':
				return $interval * 86400;
			case 'minutes':
			default:
				return $interval * 60;
		}
	}

	/**
	 * Findet den passenden Recurrence-String für ein Intervall
	 *
	 * @param int $seconds Intervall in Sekunden
	 * @return string Recurrence-String
	 */
	private static function get_recurrence_string( int $seconds ): string {
		// Mapping von bekannten Intervallen
		$intervals = array(
			1800   => 'repro_ct_suite_30min',
			3600   => 'hourly',
			7200   => 'repro_ct_suite_2hours',
			10800  => 'repro_ct_suite_3hours',
			14400  => 'repro_ct_suite_4hours',
			21600  => 'repro_ct_suite_6hours',
			43200  => 'repro_ct_suite_12hours',
			86400  => 'daily',
			604800 => 'weekly',
		);
		
		// Exakte Übereinstimmung suchen
		if ( isset( $intervals[ $seconds ] ) ) {
			return $intervals[ $seconds ];
		}
		
		// Nächstgelegenes Intervall finden
		$closest = 'hourly';
		$min_diff = PHP_INT_MAX;
		
		foreach ( $intervals as $interval_seconds => $recurrence ) {
			$diff = abs( $seconds - $interval_seconds );
			if ( $diff < $min_diff ) {
				$min_diff = $diff;
				$closest = $recurrence;
			}
		}
		
		return $closest;
	}

	/**
	 * Führt den Sync-Job aus
	 */
	public static function execute_sync(): void {
		// Logger initialisieren
		require_once plugin_dir_path( __FILE__ ) . 'class-repro-ct-suite-logger.php';
		
		Repro_CT_Suite_Logger::log( '🔄 Automatischer Sync gestartet', 'info' );
		
		// Prüfen ob Sync aktiviert ist
		$enabled = get_option( 'repro_ct_suite_auto_sync_enabled', 0 );
		if ( ! $enabled ) {
			Repro_CT_Suite_Logger::log( 'Auto-Sync ist deaktiviert - Abbruch', 'warning' );
			return;
		}
		
		// Prüfen ob ChurchTools-Verbindung konfiguriert ist
		$tenant = get_option( 'repro_ct_suite_ct_tenant', '' );
		$username = get_option( 'repro_ct_suite_ct_username', '' );
		$password = get_option( 'repro_ct_suite_ct_password', '' );
		
		if ( empty( $tenant ) || empty( $username ) || empty( $password ) ) {
			Repro_CT_Suite_Logger::log( 'ChurchTools-Zugangsdaten nicht konfiguriert - Abbruch', 'error' );
			return;
		}
		
		// Prüfen ob Kalender ausgewählt sind
		global $wpdb;
		$calendars_table = $wpdb->prefix . 'rcts_calendars';
		$selected_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$calendars_table} WHERE is_selected = 1" );
		
		if ( $selected_count === 0 ) {
			Repro_CT_Suite_Logger::log( 'Keine Kalender ausgewählt - Abbruch', 'warning' );
			return;
		}
		
		try {
			// Sync-Service laden und ausführen
			require_once plugin_dir_path( __FILE__ ) . 'class-repro-ct-suite-crypto.php';
			require_once plugin_dir_path( __FILE__ ) . 'class-repro-ct-suite-ct-client.php';
			require_once plugin_dir_path( __FILE__ ) . 'repositories/class-repro-ct-suite-repository-base.php';
			require_once plugin_dir_path( __FILE__ ) . 'repositories/class-repro-ct-suite-events-repository.php';
			require_once plugin_dir_path( __FILE__ ) . 'repositories/class-repro-ct-suite-calendars-repository.php';
			require_once plugin_dir_path( __FILE__ ) . 'services/class-repro-ct-suite-sync-service.php';
			
			$decrypted_password = Repro_CT_Suite_Crypto::decrypt( $password );
			$client = new Repro_CT_Suite_CT_Client( $tenant, $username, $decrypted_password );
			
			$events_repo = new Repro_CT_Suite_Events_Repository();
			$calendars_repo = new Repro_CT_Suite_Calendars_Repository();
			
			$sync_service = new Repro_CT_Suite_Sync_Service( $client, $events_repo, $calendars_repo );
			
			// Ausgewählte Kalender-IDs aus der Datenbank laden
			$selected_calendars = $wpdb->get_results( 
				"SELECT calendar_id FROM {$calendars_table} WHERE is_selected = 1", 
				ARRAY_A 
			);
			$calendar_ids = array_column( $selected_calendars, 'calendar_id' );
			
			Repro_CT_Suite_Logger::log( 'Starte Synchronisation mit Kalendern: ' . implode( ', ', $calendar_ids ), 'info' );
			
			// Zeitraum: Vergangenheit (7 Tage) bis Zukunft (90 Tage)
			$from = gmdate( 'Y-m-d', current_time( 'timestamp' ) - 7 * DAY_IN_SECONDS );
			$to   = gmdate( 'Y-m-d', current_time( 'timestamp' ) + 90 * DAY_IN_SECONDS );
			
			$result = $sync_service->sync_events( array(
				'calendar_ids' => $calendar_ids,
				'from'         => $from,
				'to'           => $to,
			) );
			
			if ( is_wp_error( $result ) ) {
				Repro_CT_Suite_Logger::log( 'Sync fehlgeschlagen: ' . $result->get_error_message(), 'error' );
				return;
			}
			
			// Erfolg loggen
			$stats = array(
				'calendars' => $result['calendars_processed'] ?? 0,
				'events' => $result['events_found'] ?? 0,
				'appointments' => $result['appointments_found'] ?? 0,
				'inserted' => $result['events_inserted'] ?? 0,
				'updated' => $result['events_updated'] ?? 0,
				'skipped' => $result['events_skipped'] ?? 0,
			);
			
			Repro_CT_Suite_Logger::log( sprintf(
				'Sync erfolgreich: %d Kalender, %d Events, %d Termine gefunden | %d neu, %d aktualisiert, %d übersprungen',
				$stats['calendars'],
				$stats['events'],
				$stats['appointments'],
				$stats['inserted'],
				$stats['updated'],
				$stats['skipped']
			), 'success' );
			
			// Zeitstempel speichern
			update_option( 'repro_ct_suite_last_auto_sync', time() );
			
		} catch ( Exception $e ) {
			Repro_CT_Suite_Logger::log( 'Sync-Fehler: ' . $e->getMessage(), 'error' );
		}
	}

	/**
	 * Cleanup bei Plugin-Deaktivierung
	 */
	public static function cleanup(): void {
		$timestamp = wp_next_scheduled( self::HOOK_NAME );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, self::HOOK_NAME );
		}
	}
}








