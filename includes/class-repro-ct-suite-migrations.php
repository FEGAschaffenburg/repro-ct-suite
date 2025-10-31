<?php
/**
 * Datenbank-Migrationen für Repro CT-Suite
 *
 * Erstellt und aktualisiert benutzerdefinierte Tabellen für Events, Appointments und Services.
 *
 * @package    Repro_CT_Suite
 * @subpackage Repro_CT_Suite/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Repro_CT_Suite_Migrations {

	const DB_VERSION = '6';
	const OPTION_KEY = 'repro_ct_suite_db_version';

	/**
	 * Führt Installation oder Upgrade durch.
	 */
	public static function migrate() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset_collate = $wpdb->get_charset_collate();

		$calendars_table    = $wpdb->prefix . 'rcts_calendars';
		$events_table       = $wpdb->prefix . 'rcts_events';
		$appointments_table = $wpdb->prefix . 'rcts_appointments';
		$services_table     = $wpdb->prefix . 'rcts_event_services';
		$schedule_table     = $wpdb->prefix . 'rcts_schedule';

		// Calendars (Kalender aus ChurchTools)
		$sql_calendars = "CREATE TABLE {$calendars_table} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			external_id VARCHAR(64) NOT NULL,
			name VARCHAR(255) NOT NULL,
			name_translated VARCHAR(255) NULL,
			color VARCHAR(7) NULL,
			is_public TINYINT(1) NOT NULL DEFAULT 0,
			is_selected TINYINT(1) NOT NULL DEFAULT 0,
			sort_order INT(11) NULL,
			updated_at DATETIME NOT NULL,
			raw_payload LONGTEXT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY external_id (external_id),
			KEY is_selected (is_selected)
		) {$charset_collate};";

		$sql_events = "CREATE TABLE {$events_table} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			external_id VARCHAR(64) NOT NULL,
			calendar_id VARCHAR(64) NULL,
			appointment_id BIGINT(20) UNSIGNED NULL,
			title VARCHAR(255) NOT NULL,
			description LONGTEXT NULL,
			start_datetime DATETIME NOT NULL,
			end_datetime DATETIME NULL,
			location_name VARCHAR(255) NULL,
			status VARCHAR(32) NULL,
			updated_at DATETIME NOT NULL,
			raw_payload LONGTEXT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY external_id (external_id),
			KEY calendar_id (calendar_id),
			KEY appointment_id (appointment_id),
			KEY start_datetime (start_datetime),
			KEY end_datetime (end_datetime)
		) {$charset_collate};";

		$sql_appointments = "CREATE TABLE {$appointments_table} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			external_id VARCHAR(64) NOT NULL,
			event_id BIGINT(20) UNSIGNED NULL,
			calendar_id VARCHAR(64) NULL,
			title VARCHAR(255) NOT NULL,
			description LONGTEXT NULL,
			start_datetime DATETIME NOT NULL,
			end_datetime DATETIME NULL,
			is_all_day TINYINT(1) NOT NULL DEFAULT 0,
			updated_at DATETIME NOT NULL,
			raw_payload LONGTEXT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY external_id (external_id),
			KEY event_id (event_id),
			KEY calendar_id (calendar_id),
			KEY start_datetime (start_datetime)
		) {$charset_collate};";

		$sql_services = "CREATE TABLE {$services_table} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			event_id BIGINT(20) UNSIGNED NOT NULL,
			external_id VARCHAR(64) NULL,
			service_name VARCHAR(255) NOT NULL,
			person_name VARCHAR(255) NULL,
			status VARCHAR(32) NULL,
			notes TEXT NULL,
			start_datetime DATETIME NULL,
			updated_at DATETIME NOT NULL,
			PRIMARY KEY  (id),
			KEY event_id (event_id),
			KEY service_name (service_name)
		) {$charset_collate};";

		dbDelta( $sql_calendars );
		dbDelta( $sql_events );
		dbDelta( $sql_appointments );
		dbDelta( $sql_services );

		// Kombinierte Terminübersicht (Schedule)
		$sql_schedule = "CREATE TABLE {$schedule_table} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			source_type VARCHAR(20) NOT NULL,
			source_local_id BIGINT(20) UNSIGNED NOT NULL,
			external_id VARCHAR(64) NULL,
			calendar_id VARCHAR(64) NULL,
			title VARCHAR(255) NOT NULL,
			description LONGTEXT NULL,
			start_datetime DATETIME NOT NULL,
			end_datetime DATETIME NULL,
			is_all_day TINYINT(1) NOT NULL DEFAULT 0,
			location_name VARCHAR(255) NULL,
			status VARCHAR(32) NULL,
			updated_at DATETIME NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY unique_source (source_type, source_local_id),
			KEY calendar_id (calendar_id),
			KEY start_datetime (start_datetime),
			KEY end_datetime (end_datetime)
		) {$charset_collate};";

		dbDelta( $sql_schedule );

		// Versionsabhängige Daten-Migrationen
		$current = get_option( self::OPTION_KEY, '0' );
		
		// Migration von Version 3 auf 4: calendar_id Werte korrigieren
		if ( version_compare( $current, '4', '<' ) ) {
			self::migrate_calendar_ids_v4();
		}
		
		// Migration von Version 5 auf 6: Unified Sync System vorbereiten
		if ( version_compare( $current, '6', '<' ) ) {
			self::migrate_to_unified_sync_v6();
		}

		// Platzhalter für zukünftige Migrationen (z.B. Backfill der Schedule-Tabelle)

		update_option( self::OPTION_KEY, self::DB_VERSION );
	}

	/**
	 * Migration V6: Unified Sync System Vorbereitung
	 * 
	 * Bereitet die Datenbank für das neue unified sync system vor:
	 * - Stellt sicher, dass Events-Tabelle appointment_id Feld hat
	 * - Migriert verwaiste Appointments optional zu Events
	 * - Bereinigt Dateninkonsistenzen
	 */
	private static function migrate_to_unified_sync_v6() {
		global $wpdb;
		
		$events_table = $wpdb->prefix . 'rcts_events';
		$appointments_table = $wpdb->prefix . 'rcts_appointments';
		
		error_log( 'Repro CT-Suite: Starte Migration V6 - Unified Sync System' );
		
		// 1. Events-Tabelle erweitern um appointment_id für 2-Phase Sync
		$appointment_id_exists = $wpdb->get_results( "SHOW COLUMNS FROM {$events_table} LIKE 'appointment_id'" );
		if ( empty( $appointment_id_exists ) ) {
			$wpdb->query( "ALTER TABLE {$events_table} ADD COLUMN appointment_id VARCHAR(64) NULL AFTER external_id" );
			$wpdb->query( "ALTER TABLE {$events_table} ADD INDEX idx_appointment_id (appointment_id)" );
			error_log( 'Migration V6: appointment_id Feld zu Events-Tabelle hinzugefügt' );
		}
		
		// 2. Statistiken sammeln
		$events_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$events_table}" );
		$appointments_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$appointments_table}" );
		
		error_log( "Migration V6: Datenbestand - {$events_count} Events, {$appointments_count} Appointments" );
		
		// 3. Prüfe auf verwaiste Appointments (ohne Event-Verknüpfung)
		$orphaned_count = (int) $wpdb->get_var( "
			SELECT COUNT(*) FROM {$appointments_table} a
			LEFT JOIN {$events_table} e ON (e.appointment_id = a.external_id)
			WHERE e.id IS NULL
			AND a.calendar_id IS NOT NULL
		" );
		
		if ( $orphaned_count > 0 ) {
			error_log( "Migration V6: {$orphaned_count} verwaiste Appointments gefunden" );
			
			// Optional: Automatische Migration kleinerer Mengen (bis 50)
			if ( $orphaned_count <= 50 ) {
				$migrated = self::migrate_orphaned_appointments_to_events();
				error_log( "Migration V6: {$migrated} Appointments als Events migriert" );
			} else {
				error_log( "Migration V6: Zu viele verwaiste Appointments ({$orphaned_count}) - manuelle Prüfung empfohlen" );
			}
		}
		
		// 4. Konsistenz-Checks für neues System
		$duplicate_externals = (int) $wpdb->get_var( "
			SELECT COUNT(*) - COUNT(DISTINCT external_id) FROM {$events_table}
			WHERE external_id IS NOT NULL
		" );
		
		if ( $duplicate_externals > 0 ) {
			error_log( "Migration V6: WARNUNG - {$duplicate_externals} doppelte external_id Werte in Events-Tabelle" );
		}
		
		error_log( 'Migration V6: Unified Sync System Vorbereitung abgeschlossen' );
	}
	
	/**
	 * Hilfsfunktion: Migriert verwaiste Appointments zu Events
	 * 
	 * @return int Anzahl migrierter Appointments
	 */
	private static function migrate_orphaned_appointments_to_events() {
		global $wpdb;
		
		$events_table = $wpdb->prefix . 'rcts_events';
		$appointments_table = $wpdb->prefix . 'rcts_appointments';
		
		$orphaned_appointments = $wpdb->get_results( "
			SELECT a.* FROM {$appointments_table} a
			LEFT JOIN {$events_table} e ON (e.appointment_id = a.external_id)
			WHERE e.id IS NULL
			AND a.calendar_id IS NOT NULL
			AND a.external_id IS NOT NULL
			LIMIT 50
		" );
		
		$migrated = 0;
		foreach ( $orphaned_appointments as $appointment ) {
			// Erstelle Event aus Appointment
			$event_data = array(
				'external_id'     => 'migrated_app_' . $appointment->external_id,
				'appointment_id'  => $appointment->external_id,
				'calendar_id'     => $appointment->calendar_id,
				'title'           => $appointment->title ?: 'Migrierter Termin',
				'description'     => $appointment->description ?: '',
				'start_datetime'  => $appointment->start_datetime,
				'end_datetime'    => $appointment->end_datetime,
				'location_name'   => $appointment->location_name,
				'status'          => 'migrated',
				'created_at'      => current_time( 'mysql' ),
				'updated_at'      => current_time( 'mysql' ),
			);
			
			$result = $wpdb->insert( $events_table, $event_data );
			if ( $result ) {
				$migrated++;
			}
		}
		
		return $migrated;
	}

	/**
	 * Migration V3 -> V4: calendar_id aus raw_payload extrahieren und korrigieren
	 * 
	 * Diese Funktion versucht, die calendar_id aus dem raw_payload JSON zu extrahieren
	 * und in die calendar_id Spalte zu schreiben (für Events und Appointments).
	 */
	private static function migrate_calendar_ids_v4() {
		global $wpdb;
		
		$events_table = $wpdb->prefix . 'rcts_events';
		$appointments_table = $wpdb->prefix . 'rcts_appointments';
		
		// Events: calendar_id aus raw_payload extrahieren
		$events = $wpdb->get_results( "SELECT id, raw_payload FROM {$events_table} WHERE raw_payload IS NOT NULL" );
		$events_updated = 0;
		
		foreach ( $events as $event ) {
			$payload = json_decode( $event->raw_payload, true );
			if ( ! $payload ) continue;
			
			// calendar_id extrahieren
			$calendar_id = null;
			if ( isset( $payload['calendar']['id'] ) ) {
				$calendar_id = (string) $payload['calendar']['id'];
			} elseif ( isset( $payload['calendarId'] ) ) {
				$calendar_id = (string) $payload['calendarId'];
			} elseif ( isset( $payload['calendar_id'] ) ) {
				$calendar_id = (string) $payload['calendar_id'];
			}
			
			if ( $calendar_id !== null ) {
				$wpdb->update(
					$events_table,
					array( 'calendar_id' => $calendar_id ),
					array( 'id' => $event->id ),
					array( '%s' ),
					array( '%d' )
				);
				$events_updated++;
			}
		}
		
		// Appointments: calendar_id aus raw_payload extrahieren
		$appointments = $wpdb->get_results( "SELECT id, raw_payload FROM {$appointments_table} WHERE raw_payload IS NOT NULL" );
		$appointments_updated = 0;
		
		foreach ( $appointments as $appointment ) {
			$payload = json_decode( $appointment->raw_payload, true );
			if ( ! $payload ) continue;
			
			// Bei Appointments kann die Struktur verschachtelt sein
			$base = $payload['base'] ?? $payload;
			$calendar_id = null;
			
			if ( isset( $base['calendar']['id'] ) ) {
				$calendar_id = (string) $base['calendar']['id'];
			} elseif ( isset( $base['calendarId'] ) ) {
				$calendar_id = (string) $base['calendarId'];
			} elseif ( isset( $payload['calendar']['id'] ) ) {
				$calendar_id = (string) $payload['calendar']['id'];
			}
			
			if ( $calendar_id !== null ) {
				$wpdb->update(
					$appointments_table,
					array( 'calendar_id' => $calendar_id ),
					array( 'id' => $appointment->id ),
					array( '%s' ),
					array( '%d' )
				);
				$appointments_updated++;
			}
		}
		
		// Log für Debug-Zwecke
		if ( $events_updated > 0 || $appointments_updated > 0 ) {
			error_log( sprintf(
				'Repro CT-Suite Migration V4: %d Events und %d Appointments calendar_id aktualisiert',
				$events_updated,
				$appointments_updated
			) );
		}
	}

	/**
	 * Prüft und führt Upgrades bei Bedarf durch.
	 */
	public static function maybe_upgrade() {
		$current = get_option( self::OPTION_KEY, '0' );
		if ( version_compare( $current, self::DB_VERSION, '<' ) ) {
			self::migrate();
		}
	}
}
