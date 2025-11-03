<?php
/**
 * Plugin Name:       Repro CT-Suite
 * Plugin URI:        https://github.com/FEGAschaffenburg/repro-ct-suite
 * Description:       ChurchTools-Integration für WordPress. Synchronisiert Termine und Events aus ChurchTools.
 * Version:           0.4.1.7
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
define( 'REPRO_CT_SUITE_VERSION', '0.4.1.7' );
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
	$github_token = ''; // Leer lassen für öffentliche Repositories
	
	new Repro_CT_Suite_Updater(
		__FILE__,
		'FEGAschaffenburg',
		'repro-ct-suite',
		$github_token
	);
	
	// Force update check on admin pages - aggressive clearing
	add_action( 'admin_init', function() {
		// Clear ALL update caches
		delete_transient( 'repro_ct_suite_update_check' );
		delete_transient( 'repro_ct_suite_release_info' );
		delete_site_transient( 'update_plugins' );
		delete_transient( 'repro_ct_suite_github_releases' );
		
		// Force WordPress to check for plugin updates
		wp_clean_plugins_cache();
		
		// Force immediate update check
		wp_update_plugins();
	} );

	// Add admin notice for available updates
	add_action( 'admin_notices', function() {
		$current_version = REPRO_CT_SUITE_VERSION;
		$github_version = null;
		
		// Quick GitHub API check
		$response = wp_remote_get( 'https://api.github.com/repos/FEGAschaffenburg/repro-ct-suite/releases/latest', array( 'timeout' => 10 ) );
		if ( ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) === 200 ) {
			$data = json_decode( wp_remote_retrieve_body( $response ), true );
			if ( isset( $data['tag_name'] ) ) {
				$github_version = ltrim( $data['tag_name'], 'v' );
			}
		}
		
		if ( $github_version && version_compare( $current_version, $github_version, '<' ) ) {
			echo '<div class="notice notice-warning is-dismissible">';
			echo '<h3>🔄 Repro CT-Suite Update verfügbar!</h3>';
			echo '<p><strong>Aktuelle Version:</strong> ' . esc_html( $current_version ) . '</p>';
			echo '<p><strong>Neue Version:</strong> ' . esc_html( $github_version ) . '</p>';
			echo '<p><a href="' . admin_url( 'plugins.php' ) . '" class="button button-primary">Jetzt aktualisieren</a></p>';
			echo '</div>';
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

