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

	const DB_VERSION = '1';
	const OPTION_KEY = 'repro_ct_suite_db_version';

	/**
	 * Führt Installation oder Upgrade durch.
	 */
	public static function migrate() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset_collate = $wpdb->get_charset_collate();

		$events_table       = $wpdb->prefix . 'rcts_events';
		$appointments_table = $wpdb->prefix . 'rcts_appointments';
		$services_table     = $wpdb->prefix . 'rcts_event_services';

		$sql_events = "CREATE TABLE {$events_table} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			external_id VARCHAR(64) NOT NULL,
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
			KEY start_datetime (start_datetime),
			KEY end_datetime (end_datetime)
		) {$charset_collate};";

		$sql_appointments = "CREATE TABLE {$appointments_table} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			external_id VARCHAR(64) NOT NULL,
			event_id BIGINT(20) UNSIGNED NULL,
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

		dbDelta( $sql_events );
		dbDelta( $sql_appointments );
		dbDelta( $sql_services );

		update_option( self::OPTION_KEY, self::DB_VERSION );
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
