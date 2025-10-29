<?php
/**
 * Plugin Name:       Repro CT-Suite
 * Plugin URI:        https://github.com/FEGAschaffenburg/repro-ct-suite
 * Description:       Modernes WordPress-Plugin für Repro CT-Suite mit zeitgemäßer Architektur.
 * Version:           1.0.0
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
 */
define( 'REPRO_CT_SUITE_VERSION', '1.0.0' );
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
 * Begins execution of the plugin.
 */
function run_repro_ct_suite() {
	$plugin = new Repro_CT_Suite();
	$plugin->run();
}
run_repro_ct_suite();

