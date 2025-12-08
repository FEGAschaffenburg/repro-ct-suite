<?php

/**

 * Plugin Name:       ChurchTools Suite

 * Plugin URI:        https://github.com/FEGAschaffenburg/repro-ct-suite

 * Description:       ChurchTools-Integration für WordPress. Synchronisiert Termine und Events aus ChurchTools.

 * Version:           0.9.5.2

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

define( 'REPRO_CT_SUITE_VERSION', '0.9.5.2' );

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

	add_action( 'admin_init', function() {

		// Force-Check Parameter für manuelles Update-Prüfung

		if ( isset( $_GET['force-check'] ) && current_user_can( 'update_plugins' ) ) {

			delete_transient( 'repro_ct_suite_release_info' );

			delete_site_transient( 'update_plugins' );

			wp_clean_plugins_cache();

		}

		

		new Repro_CT_Suite_Updater(

			__FILE__,

			'FEGAschaffenburg',

			'repro-ct-suite',

			'' // Leer für öffentliche Repositories

		);

	});

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

	

	// Shortcodes initialisieren

	if ( ! is_admin() ) {

		require_once REPRO_CT_SUITE_PATH . 'includes/class-repro-ct-suite-shortcodes.php';

		new Repro_CT_Suite_Shortcodes();

	}

}



/**

 * Registriere Gutenberg Block für ChurchTools Events

 */

function repro_ct_suite_register_block() {

	if ( ! function_exists( 'register_block_type' ) ) {

		return;

	}



	register_block_type( 'repro-ct-suite/events', array(

		'editor_script' => 'repro-ct-suite-gutenberg-block',

		'render_callback' => 'repro_ct_suite_render_block',

	) );

}

add_action( 'init', 'repro_ct_suite_register_block' );



/**

 * Render-Callback für Gutenberg Block

 */

function repro_ct_suite_render_block( $attributes ) {

	// Shortcode-Attribute aus Block-Attributen erstellen

	$shortcode_atts = array();

	

	if ( isset( $attributes['view'] ) ) {

		$shortcode_atts['view'] = $attributes['view'];

	}

	if ( isset( $attributes['limit'] ) ) {

		$shortcode_atts['limit'] = $attributes['limit'];

	}

	if ( isset( $attributes['calendarIds'] ) && !empty( $attributes['calendarIds'] ) ) {

		$shortcode_atts['calendar_ids'] = $attributes['calendarIds'];

	}

	if ( isset( $attributes['fromDays'] ) ) {

		$shortcode_atts['from_days'] = $attributes['fromDays'];

	}

	if ( isset( $attributes['toDays'] ) ) {

		$shortcode_atts['to_days'] = $attributes['toDays'];

	}

	if ( isset( $attributes['showPast'] ) && $attributes['showPast'] ) {

		$shortcode_atts['show_past'] = 'true';

	}

	if ( isset( $attributes['showFields'] ) ) {

		$shortcode_atts['show_fields'] = $attributes['showFields'];

	}



	// Shortcode-Klasse laden und rendern

	require_once REPRO_CT_SUITE_PATH . 'includes/class-repro-ct-suite-shortcodes.php';

	$shortcodes = new Repro_CT_Suite_Shortcodes();

	

	return $shortcodes->render_events( $shortcode_atts );

}

run_repro_ct_suite();



