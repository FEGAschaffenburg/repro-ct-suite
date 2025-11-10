<?php
/**
 * Fired during plugin deactivation.
 *
 * @package    Repro_CT_Suite
 * @subpackage Repro_CT_Suite/includes
 */

class Repro_CT_Suite_Deactivator {

	/**
	 * Short Description.
	 *
	 * Long Description.
	 */
	public static function deactivate() {
		// Add deactivation code here
		
		// Flush rewrite rules
		flush_rewrite_rules();
	}
}
