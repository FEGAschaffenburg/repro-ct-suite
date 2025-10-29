<?php
/**
 * Plugin Name:       Repro CT-Suite
 * Plugin URI:        https://github.com/FEGAschaffenburg/repro-ct-suite
 * Description:       ChurchTools-Integration für WordPress. Synchronisiert Termine und Events aus ChurchTools.
 * Version:           0.1.0.3
 * Requires at least: 5.0
 * Requires PHP:      7.4
 * Author:            FEGAschaffenburg
 * Author URI:        https://github.com/FEGAschaffenburg
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       repro-ct-suite
 * Domain Path:       /languages
 *
 * @package Repro_CT_Suite
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Currently plugin version.
 * Version mit 4 Zahlen: Major.Minor.Patch.Build
 * Build-Nummer erhöhen bei minimalen Änderungen
 */
define( 'REPRO_CT_SUITE_VERSION', '0.1.0.3' );
define( 'REPRO_CT_SUITE_FILE', __FILE__ );
define( 'REPRO_CT_SUITE_PATH', plugin_dir_path( __FILE__ ) );
define( 'REPRO_CT_SUITE_URL', plugin_dir_url( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 */
function activate_repro_ct_suite() {
	require_once REPRO_CT_SUITE_PATH . 'includes/class-repro-ct-suite-activator.php';
	Repro_CT_Suite_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_repro_ct_suite() {
	require_once REPRO_CT_SUITE_PATH . 'includes/class-repro-ct-suite-deactivator.php';
	Repro_CT_Suite_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_repro_ct_suite' );
register_deactivation_hook( __FILE__, 'deactivate_repro_ct_suite' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require_once REPRO_CT_SUITE_PATH . 'includes/class-repro-ct-suite.php';

/**
 * GitHub Updater für automatische Updates
 */
require_once REPRO_CT_SUITE_PATH . 'includes/class-repro-ct-suite-updater.php';

/**
 * Initialize the updater
 */
if ( is_admin() ) {
	new Repro_CT_Suite_Updater(
		__FILE__,
		'FEGAschaffenburg',
		'repro-ct-suite'
	);
}

/**
 * Auto-Updates für dieses Plugin erlauben (opt-in über Option)
 *
 * Wenn die Option 'repro_ct_suite_auto_update' aktiv ist, gibt dieser Filter
 * für dieses Plugin true zurück. Damit führt WordPress im Hintergrund automatische
 * Updates aus, sobald im Transient ein Update vorhanden ist.
 */
add_filter(
	'auto_update_plugin',
	function ( $update, $item ) {
		if ( empty( $item ) || empty( $item->plugin ) ) {
			return $update;
		}

		if ( $item->plugin === plugin_basename( __FILE__ ) ) {
			$enabled = (bool) get_option( 'repro_ct_suite_auto_update', 0 );
			if ( $enabled ) {
				return true;
			}
		}

		return $update;
	},
	10,
	2
);

/**
 * Begins execution of the plugin.
 */
function run_repro_ct_suite() {
	$plugin = new Repro_CT_Suite();
	$plugin->run();
}
run_repro_ct_suite();

