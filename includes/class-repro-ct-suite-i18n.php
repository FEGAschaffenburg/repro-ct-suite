<?php
/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @package    Repro_CT_Suite
 * @subpackage Repro_CT_Suite/includes
 */

class Repro_CT_Suite_i18n {

	/**
	 * Load the plugin text domain for translation.
	 */
	public function load_plugin_textdomain(): void {
		load_plugin_textdomain(
			'repro-ct-suite',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);
	}
}

