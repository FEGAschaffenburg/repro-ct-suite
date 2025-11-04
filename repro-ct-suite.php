<?php
/**
 * Plugin Name:       Repro CT-Suite
 * Plugin URI:        https://github.com/FEGAschaffenburg/repro-ct-suite
 * Description:       ChurchTools-Integration für WordPress. Synchronisiert Termine und Events aus ChurchTools.
 * Version:           0.4.6.4
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
define( 'REPRO_CT_SUITE_VERSION', '0.4.6.4' );
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
 * 
 * Hinweis: Für private Repositories kann ein GitHub Token als vierter Parameter
 * übergeben werden. Für öffentliche Repositories ist dies nicht erforderlich.
 */
if ( is_admin() ) {
	// GitHub Updater in späterem Hook laden um "Headers already sent" zu vermeiden
	add_action( 'admin_init', function() {
		$github_token = ''; // Leer lassen für öffentliche Repositories
		
		// Update-Cache löschen um neue Version zu erkennen
		if ( isset( $_GET['force-check'] ) ) {
			delete_transient( 'repro_ct_suite_github_release_cache' );
			delete_site_transient( 'update_plugins' );
		}
		
		new Repro_CT_Suite_Updater(
			__FILE__,
			'FEGAschaffenburg',
			'repro-ct-suite',
			$github_token
		);
	});
	
	// Moderates Cache-Clearing nur für Plugin-Updates  
	add_action( 'init', function() {
		// Nur Cache für unser Plugin clearen, nicht bei jedem Admin-Load
		if ( is_admin() && current_user_can( 'update_plugins' ) ) {
			// Gelegentlich Update-Cache leeren (nicht bei jedem Request)
			if ( ! get_transient( 'repro_ct_suite_last_check' ) ) {
				delete_transient( 'repro_ct_suite_release_info' );
				set_transient( 'repro_ct_suite_last_check', true, 300 ); // 5 Minuten
			}
		}
	} );
}

/**
 * Fix für private GitHub Repository Downloads
 *
 * WordPress kann standardmäßig keine privaten GitHub Assets herunterladen,
 * da die Download-URL eine Authentifizierung erfordert. Dieser Filter fügt
 * den Authorization-Header hinzu, damit der Download funktioniert.
 *
 * Hinweis: Dieser Filter wird nur benötigt, wenn das Repository privat ist.
 * Bei öffentlichen Repositories kann dieser Code entfernt werden.
 *
 * @since 0.2.4.3
 */
add_filter(
	'upgrader_pre_download',
	function ( $reply, $package, $upgrader ) {
		// Nur für unsere GitHub Releases
		if ( strpos( $package, 'github.com/FEGAschaffenburg/repro-ct-suite' ) === false ) {
			return $reply;
		}

		// GitHub Token (nur für private Repositories erforderlich)
		$github_token = ''; // Leer lassen für öffentliche Repositories

		// Wenn kein Token vorhanden, normalen Download verwenden
		if ( empty( $github_token ) ) {
			return $reply;
		}

		// Download mit Authorization Header (nur für private Repos)
		$temp_file = download_url(
			$package,
			300,
			false,
			array(
				'headers' => array(
					'Authorization' => 'token ' . $github_token,
					'Accept'        => 'application/octet-stream',
				),
			)
		);

		if ( is_wp_error( $temp_file ) ) {
			return $temp_file;
		}

		return $temp_file;
	},
	10,
	3
);

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

