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
	public static function init() {
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
	public static function add_custom_cron_intervals( $schedules ) {
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
	public static function reschedule_sync_job() {
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
	private static function calculate_interval_seconds( $interval, $unit ) {
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
	private static function get_recurrence_string( $seconds ) {
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
	public static function execute_sync() {
		// Logger initialisieren
		require_once plugin_dir_path( __FILE__ ) . 'class-repro-ct-suite-logger.php';
		$logger = Repro_CT_Suite_Logger::get_instance();
		
		$logger->info( '🔄 Automatischer Sync gestartet' );
		
		// Prüfen ob Sync aktiviert ist
		$enabled = get_option( 'repro_ct_suite_auto_sync_enabled', 0 );
		if ( ! $enabled ) {
			$logger->warning( 'Auto-Sync ist deaktiviert - Abbruch' );
			return;
		}
		
		// Prüfen ob ChurchTools-Verbindung konfiguriert ist
		$tenant = get_option( 'repro_ct_suite_ct_tenant', '' );
		$username = get_option( 'repro_ct_suite_ct_username', '' );
		$password = get_option( 'repro_ct_suite_ct_password', '' );
		
		if ( empty( $tenant ) || empty( $username ) || empty( $password ) ) {
			$logger->error( 'ChurchTools-Zugangsdaten nicht konfiguriert - Abbruch' );
			return;
		}
		
		// Prüfen ob Kalender ausgewählt sind
		global $wpdb;
		$calendars_table = $wpdb->prefix . 'rcts_calendars';
		$selected_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$calendars_table} WHERE is_selected = 1" );
		
		if ( $selected_count === 0 ) {
			$logger->warning( 'Keine Kalender ausgewählt - Abbruch' );
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
			
			$logger->info( 'Starte Synchronisation...' );
			$result = $sync_service->sync_events();
			
			if ( is_wp_error( $result ) ) {
				$logger->error( 'Sync fehlgeschlagen: ' . $result->get_error_message() );
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
			
			$logger->info( sprintf(
				'Sync erfolgreich: %d Kalender, %d Events, %d Termine gefunden | %d neu, %d aktualisiert, %d übersprungen',
				$stats['calendars'],
				$stats['events'],
				$stats['appointments'],
				$stats['inserted'],
				$stats['updated'],
				$stats['skipped']
			) );
			
			// Zeitstempel speichern
			update_option( 'repro_ct_suite_last_auto_sync', time() );
			
		} catch ( Exception $e ) {
			$logger->error( 'Sync-Fehler: ' . $e->getMessage() );
		}
	}

	/**
	 * Cleanup bei Plugin-Deaktivierung
	 */
	public static function cleanup() {
		$timestamp = wp_next_scheduled( self::HOOK_NAME );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, self::HOOK_NAME );
		}
	}
}
