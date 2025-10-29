<?php
/**
 * Fired during plugin activation.
 *
 * @package    Repro_CT_Suite
 * @subpackage Repro_CT_Suite/includes
 */

class Repro_CT_Suite_Activator {

	/**
	 * Aktivierung: Optionen setzen und DB-Migrationen ausführen.
	 */
	public static function activate() {
		// Standard: Auto-Update deaktiviert
		add_option( 'repro_ct_suite_auto_update', 0 );

		// DB-Tabellen erstellen/aktualisieren
		require_once plugin_dir_path( __FILE__ ) . 'class-repro-ct-suite-migrations.php';
		Repro_CT_Suite_Migrations::migrate();
		
		// Flush rewrite rules
		flush_rewrite_rules();
	}
}
