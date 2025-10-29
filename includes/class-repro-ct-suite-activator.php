<?php
/**
 * Fired during plugin activation.
 *
 * @package    Repro_CT_Suite
 * @subpackage Repro_CT_Suite/includes
 */

class Repro_CT_Suite_Activator {

	/**
	 * Short Description.
	 *
	 * Long Description.
	 */
	public static function activate() {
		// Add activation code here
		// For example, create database tables, set default options, etc.

		// Standard: Auto-Update deaktiviert
		add_option( 'repro_ct_suite_auto_update', 0 );
		
		// Flush rewrite rules
		flush_rewrite_rules();
	}
}
