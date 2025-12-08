<?php

/**

 * The admin-specific functionality of the plugin.

 *

 * @package    Repro_CT_Suite

 * @subpackage Repro_CT_Suite/admin

 */



if ( ! defined( 'ABSPATH' ) ) {

	exit;

}



// Logger laden
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-repro-ct-suite-logger.php';

// Modern Shortcode Manager laden
require_once plugin_dir_path( __FILE__ ) . 'class-modern-shortcode-manager.php';

class Repro_CT_Suite_Admin {



	/**

	 * The ID of this plugin.

	 *

	 * @var string $plugin_name

	 */

	private $plugin_name;



	/**

	 * The version of this plugin.

	 *

	 * @var string $version

	 */

	private $version;



	/**

	 * Initialize the class and set its properties.

	 *

	 * @param string $plugin_name The name of this plugin.

	 * @param string $version     The version of this plugin.

	 */

	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;

		$this->version     = $version;

		
		// Modern Shortcode Manager initialisieren
		$this->init_modern_shortcode_manager();
		
		// WordPress Admin Footer ausblenden
		add_filter( 'admin_footer_text', array( $this, 'remove_admin_footer_text' ) );
		add_filter( 'update_footer', array( $this, 'remove_update_footer' ), 11 );

		// Update-Check Handler

		add_action( 'admin_init', array( $this, 'check_manual_update_request' ) );

		

		// Form Handler fÃ¼r Kalenderauswahl

		add_action( 'admin_init', array( $this, 'handle_calendar_selection' ) );

		

		// Sync Success Notice anzeigen

		add_action( 'admin_notices', array( $this, 'show_sync_success_notice' ) );

		

		// AJAX Handlers

		add_action( 'wp_ajax_repro_ct_suite_clear_tables', array( $this, 'ajax_clear_tables' ) );

		add_action( 'wp_ajax_repro_ct_suite_clear_single_table', array( $this, 'ajax_clear_single_table' ) );

		add_action( 'wp_ajax_repro_ct_suite_run_migrations', array( $this, 'ajax_run_migrations' ) );

		add_action( 'wp_ajax_repro_ct_suite_clear_log', array( $this, 'ajax_clear_log' ) );

		add_action( 'wp_ajax_repro_ct_suite_reset_credentials', array( $this, 'ajax_reset_credentials' ) );

		add_action( 'wp_ajax_repro_ct_suite_full_reset', array( $this, 'ajax_full_reset' ) );

		add_action( 'wp_ajax_repro_ct_suite_fix_calendar_ids', array( $this, 'ajax_fix_calendar_ids' ) );

		add_action( 'wp_ajax_repro_ct_suite_delete_event', array( $this, 'ajax_delete_event' ) );

		add_action( 'wp_ajax_repro_ct_suite_update_event', array( $this, 'ajax_update_event' ) );
		add_action( 'wp_ajax_repro_ct_suite_get_table_entries', array( $this, 'ajax_get_table_entries' ) );
		add_action( 'wp_ajax_repro_ct_suite_delete_single_entry', array( $this, 'ajax_delete_single_entry' ) );

		add_action( 'wp_ajax_repro_ct_suite_dismiss_v6_notice', array( $this, 'ajax_dismiss_v6_notice' ) );

		add_action( 'wp_ajax_repro_ct_suite_preview_shortcode', array( $this, 'ajax_preview_shortcode' ) );

		

		// Preset AJAX Handlers

		add_action( 'wp_ajax_repro_ct_suite_get_presets', array( $this, 'ajax_get_presets' ) );

		add_action( 'wp_ajax_repro_ct_suite_save_preset', array( $this, 'ajax_save_preset' ) );

		add_action( 'wp_ajax_repro_ct_suite_update_preset', array( $this, 'ajax_update_preset' ) );

		add_action( 'wp_ajax_repro_ct_suite_delete_preset', array( $this, 'ajax_delete_preset' ) );

		add_action( 'wp_ajax_repro_ct_suite_load_preset', array( $this, 'ajax_load_preset' ) );
		
		// Modern Shortcode Manager AJAX Handlers sind in eigener Klasse registriert
		// (siehe init_modern_shortcode_manager)

	}



	/**

	 * PrÃ¼ft ob ChurchTools-Verbindung konfiguriert ist

	 *

	 * @return bool

	 */

	private function has_connection(): bool {

		$tenant   = get_option( 'repro_ct_suite_ct_tenant', '' );

		$username = get_option( 'repro_ct_suite_ct_username', '' );

		$password = get_option( 'repro_ct_suite_ct_password', '' );

		return ! empty( $tenant ) && ! empty( $username ) && ! empty( $password );

	}



	/**

	 * PrÃ¼ft ob mindestens ein Kalender ausgewÃ¤hlt ist

	 *

	 * @return bool

	 */

	private function has_calendars_selected(): bool {

		global $wpdb;

		$table = $wpdb->prefix . 'rcts_calendars';

		

		// PrÃ¼fe ob Tabelle existiert

		$table_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table ) );

		if ( ! $table_exists ) {

			return false;

		}

		

		$count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE is_selected = 1" );

		return $count > 0;

	}



	/**

	 * Debug-Logging Helper

	 * 

	 * Schreibt Debug-Informationen ins WordPress Debug-Log.

	 * Funktioniert unabhÃ¤ngig von WP_DEBUG - aktiviert sich selbst wenn nÃ¶tig.

	 *

	 * @param string $message Die Log-Nachricht

	 * @param string $level   Log-Level: 'info', 'error', 'warning', 'success'

	 */

	private function debug_log( string $message, string $level = 'info' ): void {

		// Stelle sicher, dass Debug-Log aktiviert ist

		if ( ! defined( 'WP_DEBUG_LOG' ) ) {

			// TemporÃ¤r aktivieren fÃ¼r diesen Request

			if ( ! @ini_get( 'log_errors' ) ) {

				@ini_set( 'log_errors', '1' );

			}

			$log_file = WP_CONTENT_DIR . '/debug.log';

			if ( ! @ini_get( 'error_log' ) ) {

				@ini_set( 'error_log', $log_file );

			}

		}

		

		$prefix = '[REPRO CT-SUITE] ';

		switch ( $level ) {

			case 'error':

				$prefix .= 'âŒ ERROR: ';

				break;

			case 'warning':

				$prefix .= 'âš ï¸  WARNING: ';

				break;

			case 'success':

				$prefix .= 'âœ… SUCCESS: ';

				break;

			default:

				$prefix .= 'â„¹ï¸  INFO: ';

		}

		

		error_log( $prefix . $message );

	}



	/**

	 * Behandelt manuelle Update-Check-Anfragen

	 */

	public function check_manual_update_request(): void {

		if ( isset( $_GET['repro_ct_suite_check_update'] ) && current_user_can( 'update_plugins' ) ) {

			// LÃ¶sche alle Update-bezogenen Transients

			delete_transient( 'repro_ct_suite_release_info' );

			delete_site_transient( 'update_plugins' );

			

			// Redirect zurÃ¼ck zur Plugins-Seite

			wp_safe_redirect( admin_url( 'plugins.php?repro_ct_suite_update_checked=1' ) );

			exit;

		}

		

		// Zeige Erfolgs-Notice

		if ( isset( $_GET['repro_ct_suite_update_checked'] ) ) {

			add_action( 'admin_notices', function() {

				echo '<div class="notice notice-success is-dismissible"><p>';

				esc_html_e( 'Update-Check durchgefÃ¼hrt. Bitte Seite neu laden um Updates zu sehen.', 'repro-ct-suite' );

				echo '</p></div>';

			} );

		}

	}



	/**

	 * Zeigt eine Success-Notice nach erfolgreichem Sync an

	 */

	public function show_sync_success_notice(): void {

		// Nur auf Plugin-Seiten anzeigen

		if ( ! isset( $_GET['page'] ) || strpos( $_GET['page'], 'repro-ct-suite' ) === false ) {

			return;

		}



		// PrÃ¼fen ob Sync-Notice vorhanden ist

		$sync_notice = get_transient( 'repro_ct_suite_sync_notice' );

		if ( ! $sync_notice ) {

			return;

		}



		// Transient lÃ¶schen (nur einmal anzeigen)

		delete_transient( 'repro_ct_suite_sync_notice' );



		// Notice-Typ bestimmen

		$notice_class = isset( $sync_notice['type'] ) && $sync_notice['type'] === 'error' ? 'notice-error' : 'notice-success';

		$message = isset( $sync_notice['message'] ) ? $sync_notice['message'] : '';



		if ( empty( $message ) ) {

			return;

		}



		?>

		<div class="notice <?php echo esc_attr( $notice_class ); ?> is-dismissible">

			<p><strong><?php echo esc_html( $message ); ?></strong></p>

			<?php if ( isset( $sync_notice['stats'] ) ) : 

				$stats = $sync_notice['stats'];

			?>

				<p>

					<?php if ( isset( $stats['calendars_processed'] ) ) : ?>

						ðŸ“… <?php printf( __( 'Kalender verarbeitet: %d', 'repro-ct-suite' ), $stats['calendars_processed'] ); ?><br>

					<?php endif; ?>

					<?php if ( isset( $stats['events_found'] ) ) : ?>

						ðŸ” <?php printf( __( 'Events gefunden: %d', 'repro-ct-suite' ), $stats['events_found'] ); ?><br>

					<?php endif; ?>

					<?php if ( isset( $stats['appointments_found'] ) ) : ?>

						ðŸ“‹ <?php printf( __( 'Termine gefunden: %d', 'repro-ct-suite' ), $stats['appointments_found'] ); ?><br>

					<?php endif; ?>

					<?php if ( isset( $stats['events_inserted'] ) ) : ?>

						âž• <?php printf( __( 'Neu importiert: %d', 'repro-ct-suite' ), $stats['events_inserted'] ); ?><br>

					<?php endif; ?>

					<?php if ( isset( $stats['events_updated'] ) ) : ?>

						ðŸ”„ <?php printf( __( 'Aktualisiert: %d', 'repro-ct-suite' ), $stats['events_updated'] ); ?><br>

					<?php endif; ?>

					<?php if ( isset( $stats['events_skipped'] ) && $stats['events_skipped'] > 0 ) : ?>

						â­ï¸ <?php printf( __( 'Ãœbersprungen: %d', 'repro-ct-suite' ), $stats['events_skipped'] ); ?><br>

					<?php endif; ?>

				</p>

			<?php endif; ?>

		</div>

		<?php

	}



	/**

	 * Add plugin action links.

	 *

	 * @param array $links Existing plugin action links.

	 * @return array Modified plugin action links.

	 */

	public function add_plugin_action_links( array $links ): array {

		$custom_links = array(

			'dashboard'  => sprintf(

				'<a href="%s">%s</a>',

				esc_url( admin_url( 'admin.php?page=repro-ct-suite' ) ),

				__( 'Dashboard', 'repro-ct-suite' )

			),

			'settings'   => sprintf(

				'<a href="%s">%s</a>',

				esc_url( admin_url( 'admin.php?page=repro-ct-suite&tab=settings' ) ),

				__( 'Einstellungen', 'repro-ct-suite' )

			),

			'license'    => sprintf(

				'<a href="%s">%s</a>',

				esc_url( admin_url( 'admin.php?page=repro-ct-suite&tab=license' ) ),

				__( 'Lizenz', 'repro-ct-suite' )

			),

		);



		return array_merge( $custom_links, $links );

	}



	/**

	 * Register the stylesheets for the admin area.

	 */

	public function enqueue_styles(): void {

		wp_enqueue_style(

			$this->plugin_name,

			plugin_dir_url( __FILE__ ) . 'css/repro-ct-suite-admin.css',


		array(),

		$this->version,

		'all'

	);
	wp_enqueue_style(
		$this->plugin_name . '-modal',
		plugin_dir_url( __FILE__ ) . 'css/repro-ct-suite-modal.css',
		array(),
		$this->version,
		'all'
	);

		// Shortcode Manager Styles (nur auf Shortcode Manager Seite)

		$screen = get_current_screen();

		if ( $screen && strpos( $screen->id, 'repro-ct-suite-shortcodes' ) !== false ) {

			wp_enqueue_style(

				$this->plugin_name . '-shortcode-manager',

				plugin_dir_url( __FILE__ ) . 'css/modern-shortcode-manager.css',

				array(),

				$this->version,

				'all'

			);

		}



	}



	/**

	 * Register the JavaScript for the admin area.

	 */

	public function enqueue_scripts(): void {

		wp_enqueue_script(

			$this->plugin_name,

			plugin_dir_url( __FILE__ ) . 'js/repro-ct-suite-admin.js',

			array( 'jquery' ),

			null,

			false

		);



		// Gutenberg Block fÃ¼r Events-Shortcode

		if ( function_exists( 'register_block_type' ) ) {

			wp_enqueue_script(

				$this->plugin_name . '-gutenberg-block',

				plugin_dir_url( __FILE__ ) . 'js/gutenberg-block.js',

				array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components' ),

				$this->version,

				true

			);

		}



		// Neuer Shortcode Manager JavaScript (nur auf Shortcode Manager Seite)

		$screen = get_current_screen();

		if ( $screen && strpos( $screen->id, 'repro-ct-suite-shortcodes' ) !== false ) {

			wp_enqueue_script(

				$this->plugin_name . '-shortcode-manager',

				plugin_dir_url( __FILE__ ) . 'js/modern-shortcode-manager.js',

				array( 'jquery' ),

				$this->version,

				true

			);



			// AJAX-Daten fÃ¼r neuen Shortcode Manager

			wp_localize_script(

				$this->plugin_name . '-shortcode-manager',

				'rctsShortcodeManager',

				array(

					'ajaxurl' => admin_url( 'admin-ajax.php' ),

					'nonce'   => wp_create_nonce( 'sm_nonce' ),

				)

			);

		}



		// Debug-Seite JavaScript

		// Enqueue debug JS either on the dedicated debug page OR on the main plugin page when the Logs tab is active

		$enqueue_debug = false;

		if ( $screen && strpos( $screen->id, 'repro-ct-suite-debug' ) !== false ) {

			$enqueue_debug = true;

		} elseif ( $screen && strpos( $screen->id, 'toplevel_page_repro-ct-suite' ) !== false ) {

			// If we're on the main plugin page and the Logs tab is requested, enqueue the debug script so the buttons work

			if ( isset( $_GET['tab'] ) && $_GET['tab'] === 'logs' ) {

				$enqueue_debug = true;

			}

		}

		if ( $enqueue_debug ) {

			wp_enqueue_script(

				$this->plugin_name . '-debug',

				plugin_dir_url( __FILE__ ) . 'js/repro-ct-suite-debug.js',

				array( 'jquery' ),

			$this->version,

			false

		);
		wp_enqueue_script(
			$this->plugin_name . '-debug-extensions',
			plugin_dir_url( __FILE__ ) . 'js/repro-ct-suite-debug-extensions.js',
			array( 'jquery', $this->plugin_name . '-debug' ),
			$this->version,
			false
		);		}



		// Localize script fÃ¼r AJAX

		wp_localize_script(

			$this->plugin_name,

			'reproCTSuiteAdmin',

			array(

				'ajaxUrl' => admin_url( 'admin-ajax.php' ),

				'nonce'   => wp_create_nonce( 'repro_ct_suite_admin' ),

			)

		);

	}



	/**

	 * Add admin menu pages.

	 */

	public function add_admin_menu(): void {

		// PrÃ¼fen ob Cron aktiv ist

		$auto_sync_enabled = get_option( 'repro_ct_suite_auto_sync_enabled', 0 );

		

		// HauptmenÃ¼ (Dashboard)

		add_menu_page(

			__( 'Repro CT-Suite', 'repro-ct-suite' ),

			__( 'Repro CT-Suite', 'repro-ct-suite' ),

			'manage_options',

			'repro-ct-suite',

			array( $this, 'display_admin_page' ),

			'dashicons-admin-generic',

			30

		);



		// Submenu: Dashboard (Umbenennung des ersten Eintrags)

		add_submenu_page(

			'repro-ct-suite',

			__( 'Dashboard', 'repro-ct-suite' ),

			__( 'Dashboard', 'repro-ct-suite' ),

			'manage_options',

			'repro-ct-suite',

			array( $this, 'display_admin_page' )

		);



		// Submenu: Termine (immer anzeigen)

		add_submenu_page(

			'repro-ct-suite',

			__( 'Termine', 'repro-ct-suite' ),

			__( 'Termine', 'repro-ct-suite' ),

			'manage_options',

			'repro-ct-suite-events',

			array( $this, 'display_events_page' )

		);



		// Submenu: Anzeige im Frontend

		// Submenu: Shortcode Manager (neue Subpage)

		add_submenu_page(

			'repro-ct-suite',

			__( 'Shortcode Manager', 'repro-ct-suite' ),

			__( 'Shortcode Manager', 'repro-ct-suite' ),

			'manage_options',

			'repro-ct-suite-shortcodes',

			array( $this, 'display_shortcode_manager_page' )

		);

		add_submenu_page(
            'repro-ct-suite',
            __( 'Dokumentation', 'repro-ct-suite' ),
            __( 'Dokumentation', 'repro-ct-suite' ),
            'manage_options',
            'repro-ct-suite-dokumentation',
            array( $this, 'display_documentation_page' )
        );
	}



	/**

	 * Display the admin page.

	 */

	public function display_admin_page(): void {

		include_once plugin_dir_path( __FILE__ ) . 'views/admin-display.php';

	}



	/**

	 * Display the events (Terminkalender) page.

	 */

	public function display_events_page(): void {

		include_once plugin_dir_path( __FILE__ ) . 'views/admin-events.php';

	}



	/**

	 * Display the shortcode manager page.

	 */

	public function display_shortcode_manager_page(): void {

		include_once plugin_dir_path( __FILE__ ) . 'views/modern-shortcode-manager.php';

	}



	/**

	 * Display the documentation page.

	 */

	public function display_documentation_page(): void {

		include_once plugin_dir_path( __FILE__ ) . 'page-dokumentation.php';

	}



	/**

	 * Handle test connection request.

	 */

	public function handle_test_connection(): void {

		if ( ! isset( $_GET['page'] ) || $_GET['page'] !== 'repro-ct-suite' ) {

			return;

		}



		if ( ! isset( $_GET['test_connection'] ) || ! check_admin_referer( 'repro_ct_suite_test_connection' ) ) {

			return;

		}



		Repro_CT_Suite_Logger::log( 'info', 'ðŸ”Œ Login-Test gestartet' );



		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-repro-ct-suite-crypto.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-repro-ct-suite-ct-client.php';



		$test_tenant       = get_option( 'repro_ct_suite_ct_tenant', '' );

		$test_username     = get_option( 'repro_ct_suite_ct_username', '' );

		$test_password_enc = get_option( 'repro_ct_suite_ct_password', '' );

		$test_password     = Repro_CT_Suite_Crypto::decrypt( $test_password_enc );



		Repro_CT_Suite_Logger::log( 'debug', sprintf( 'Zugangsdaten geladen - Tenant: %s, Username: %s', $test_tenant, $test_username ) );



		if ( empty( $test_tenant ) || empty( $test_username ) || empty( $test_password ) ) {

			Repro_CT_Suite_Logger::log( 'error', 'âŒ Login-Test fehlgeschlagen: Zugangsdaten unvollstÃ¤ndig' );

			set_transient( 'repro_ct_suite_test_result', new WP_Error( 'missing_credentials', __( 'Bitte alle Felder ausfÃ¼llen.', 'repro-ct-suite' ) ), 30 );

		} else {

			$client = new Repro_CT_Suite_CT_Client( $test_tenant, $test_username, $test_password );

			Repro_CT_Suite_Logger::log( 'debug', 'CT-Client erstellt, starte Login-Versuch' );

			

			$login  = $client->login();

			if ( is_wp_error( $login ) ) {

				Repro_CT_Suite_Logger::log( 'error', sprintf( 'âŒ Login fehlgeschlagen: %s', $login->get_error_message() ) );

				set_transient( 'repro_ct_suite_test_result', $login, 30 );

			} else {

				Repro_CT_Suite_Logger::log( 'success', 'âœ… Login erfolgreich, rufe whoami() ab' );

				$whoami = $client->whoami();

				if ( is_wp_error( $whoami ) ) {

					Repro_CT_Suite_Logger::log( 'error', sprintf( 'âŒ whoami() fehlgeschlagen: %s', $whoami->get_error_message() ) );

				} else {

					Repro_CT_Suite_Logger::log( 'success', sprintf( 'âœ… Login-Test erfolgreich - User: %s', $whoami['userName'] ?? 'unbekannt' ) );

				}

				set_transient( 'repro_ct_suite_test_result', is_wp_error( $whoami ) ? $whoami : true, 30 );

			}

		}



		// Redirect zurÃ¼ck zum Settings-Tab ohne test_connection Parameter

		$redirect_url = add_query_arg(

			array(

				'page' => 'repro-ct-suite',

				'tab'  => 'settings',

			),

			admin_url( 'admin.php' )

		);

		wp_safe_redirect( $redirect_url );

		exit;

	}



	/**

	 * Handle calendar selection update.

	 */

	public function handle_calendar_selection(): void {

		// Kalenderauswahl speichern

		if ( isset( $_POST['repro_ct_suite_action'] ) && $_POST['repro_ct_suite_action'] === 'save_calendar_selection' ) {

			

			if ( ! check_admin_referer( 'repro_ct_suite_calendar_selection', 'repro_ct_suite_calendar_selection_nonce' ) ) {

				wp_die( __( 'SicherheitsprÃ¼fung fehlgeschlagen.', 'repro-ct-suite' ) );

			}



			if ( ! current_user_can( 'manage_options' ) ) {

				wp_die( __( 'Keine Berechtigung.', 'repro-ct-suite' ) );

			}



			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/repositories/class-repro-ct-suite-repository-base.php';

			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/repositories/class-repro-ct-suite-calendars-repository.php';



			$calendars_repo = new Repro_CT_Suite_Calendars_Repository();

			$selected_ids = isset( $_POST['selected_calendars'] ) && is_array( $_POST['selected_calendars'] )

				? array_map( 'intval', $_POST['selected_calendars'] )

				: array();



			$result = $calendars_repo->update_selected( $selected_ids );



			if ( $result ) {

				add_settings_error(

					'repro_ct_suite_sync',

					'calendars_updated',

					__( 'Kalender-Auswahl erfolgreich gespeichert.', 'repro-ct-suite' ),

					'success'

				);

			} else {

				add_settings_error(

					'repro_ct_suite_sync',

					'calendars_update_failed',

					__( 'Fehler beim Speichern der Kalender-Auswahl.', 'repro-ct-suite' ),

					'error'

				);

			}



			set_transient( 'settings_errors', get_settings_errors(), 30 );



			$redirect_url = add_query_arg(

				array(

					'page'              => 'repro-ct-suite',

					'tab'               => 'sync',

					'settings-updated'  => 'true',

				),

				admin_url( 'admin.php' )

			);

			wp_safe_redirect( $redirect_url );

			exit;

		}



		// Zeitraum-Konfiguration speichern

		if ( isset( $_POST['repro_ct_suite_action'] ) && $_POST['repro_ct_suite_action'] === 'save_sync_period' ) {

			if ( ! check_admin_referer( 'repro_ct_suite_sync_period', 'repro_ct_suite_sync_period_nonce' ) ) {

				wp_die( __( 'SicherheitsprÃ¼fung fehlgeschlagen.', 'repro-ct-suite' ) );

			}



			if ( ! current_user_can( 'manage_options' ) ) {

				wp_die( __( 'Keine Berechtigung.', 'repro-ct-suite' ) );

			}



			$sync_from_days = isset( $_POST['sync_from_days'] ) ? intval( $_POST['sync_from_days'] ) : -7;

			$sync_to_days   = isset( $_POST['sync_to_days'] ) ? intval( $_POST['sync_to_days'] ) : 90;



			// Validierung

			$sync_from_days = max( -365, min( 0, $sync_from_days ) );

			$sync_to_days   = max( 0, min( 730, $sync_to_days ) );



			update_option( 'repro_ct_suite_sync_from_days', $sync_from_days, false );

			update_option( 'repro_ct_suite_sync_to_days', $sync_to_days, false );



			add_settings_error(

				'repro_ct_suite_sync',

				'period_updated',

				__( 'Zeitraum-Konfiguration erfolgreich gespeichert.', 'repro-ct-suite' ),

				'success'

			);



			set_transient( 'settings_errors', get_settings_errors(), 30 );



			$redirect_url = add_query_arg(

				array(

					'page'              => 'repro-ct-suite',

					'tab'               => 'sync',

					'settings-updated'  => 'true',

				),

				admin_url( 'admin.php' )

			);

			wp_safe_redirect( $redirect_url );

			exit;

		}



		// Legacy-Handler (falls noch verwendet)

		if ( ! isset( $_POST['action'] ) || $_POST['action'] !== 'repro_ct_suite_update_calendars' ) {

			return;

		}



		if ( ! check_admin_referer( 'repro_ct_suite_update_calendars', 'repro_ct_suite_calendars_nonce' ) ) {

			wp_die( __( 'SicherheitsprÃ¼fung fehlgeschlagen.', 'repro-ct-suite' ) );

		}



		if ( ! current_user_can( 'manage_options' ) ) {

			wp_die( __( 'Keine Berechtigung.', 'repro-ct-suite' ) );

		}



		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/repositories/class-repro-ct-suite-repository-base.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/repositories/class-repro-ct-suite-calendars-repository.php';



		$calendars_repo = new Repro_CT_Suite_Calendars_Repository();

		$selected_ids = isset( $_POST['selected_calendars'] ) && is_array( $_POST['selected_calendars'] )

			? array_map( 'intval', $_POST['selected_calendars'] )

			: array();



		$result = $calendars_repo->update_selected( $selected_ids );



		if ( $result ) {

			add_settings_error(

				'repro_ct_suite_calendars',

				'calendars_updated',

				__( 'Kalender-Auswahl erfolgreich gespeichert.', 'repro-ct-suite' ),

				'success'

			);

		} else {

			add_settings_error(

				'repro_ct_suite_calendars',

				'calendars_update_failed',

				__( 'Fehler beim Speichern der Kalender-Auswahl.', 'repro-ct-suite' ),

				'error'

			);

		}



		set_transient( 'settings_errors', get_settings_errors(), 30 );



		$redirect_url = add_query_arg(

			array(

				'page'              => 'repro-ct-suite',

				'tab'               => 'settings',

				'settings-updated'  => 'true',

			),

			admin_url( 'admin.php' )

		);

		wp_safe_redirect( $redirect_url );

		exit;

	}



	/**

	 * Register plugin settings.

	 */

	public function register_settings(): void {

		register_setting(

			'repro_ct_suite',

			'repro_ct_suite_auto_update',

			array(

				'type'              => 'boolean',

				'description'       => __( 'Automatische Updates fÃ¼r Repro CT-Suite aktivieren', 'repro-ct-suite' ),

				'sanitize_callback' => function ( $value ) { return (int) ( ! empty( $value ) ); },

				'default'           => 0,

			)

		);



		// Optional: Syslog output for debug messages

		register_setting(

			'repro_ct_suite',

			'repro_ct_suite_syslog',

			array(

				'type'              => 'boolean',

				'description'       => __( 'Enable syslog output for plugin logging', 'repro-ct-suite' ),

				'sanitize_callback' => function ( $value ) { return (int) ( ! empty( $value ) ); },

				'default'           => 0,

			)

		);



		// ChurchTools Einstellungen: Tenant, Benutzername, Passwort (verschlÃ¼sselt)

		register_setting(

			'repro_ct_suite',

			'repro_ct_suite_ct_tenant',

			array(

				'type'              => 'string',

				'description'       => __( 'ChurchTools Tenant (z.B. "gemeinde" fÃ¼r gemeinde.church.tools)', 'repro-ct-suite' ),

				'sanitize_callback' => function ( $value ) { return sanitize_text_field( trim( $value ) ); },

				'default'           => '',

			)

		);



		register_setting(

			'repro_ct_suite',

			'repro_ct_suite_ct_username',

			array(

				'type'              => 'string',

				'description'       => __( 'ChurchTools Benutzername', 'repro-ct-suite' ),

				'sanitize_callback' => function ( $value ) { return sanitize_text_field( $value ); },

				'default'           => '',

			)

		);



		register_setting(

			'repro_ct_suite',

			'repro_ct_suite_ct_password',

			array(

				'type'              => 'string',

				'description'       => __( 'ChurchTools Passwort (wird verschlÃ¼sselt gespeichert)', 'repro-ct-suite' ),

				'sanitize_callback' => function ( $value ) {

					require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-repro-ct-suite-crypto.php';

					$value = (string) $value;

					// Leer = bestehendes Passwort beibehalten

					if ( empty( $value ) ) {

						return get_option( 'repro_ct_suite_ct_password', '' );

					}

					return Repro_CT_Suite_Crypto::encrypt( $value );

				},

				'default'           => '',

			)

		);



		// Sync-Zeitraum: Termine von (Tage in der Vergangenheit, negativ)



		// Lizenz-Einstellungen

		register_setting(

			'repro_ct_suite_license_group',

			'repro_ct_suite_license_key',

			array(

				'type'              => 'string',

				'description'       => __( 'Lizenzschlüssel', 'repro-ct-suite' ),

				'sanitize_callback' => 'sanitize_text_field',

				'default'           => '',

			)

		);



		register_setting(

			'repro_ct_suite_license_group',

			'repro_ct_suite_license_email',

			array(

				'type'              => 'string',

				'description'       => __( 'Lizenz E-Mail', 'repro-ct-suite' ),

				'sanitize_callback' => 'sanitize_email',

				'default'           => '',

			)

		);



		register_setting(

			'repro_ct_suite_license_group',

			'repro_ct_suite_license_status',

			array(

				'type'    => 'string',

				'default' => 'inactive',

			)

		);



		register_setting(

			'repro_ct_suite_license_group',

			'repro_ct_suite_license_expiry',

			array(

				'type'    => 'string',

				'default' => '',

			)

		);



		// Sync-Zeitraum: Termine von (Tage in der Vergangenheit, negativ)

		register_setting(

			'repro_ct_suite',

			'repro_ct_suite_sync_from_days',

			array(

				'type'              => 'integer',

				'description'       => __( 'Anzahl der Tage in der Vergangenheit fÃ¼r Event-Synchronisation (negativ)', 'repro-ct-suite' ),

				'sanitize_callback' => function ( $value ) {

					$value = intval( $value );

					return max( -365, min( 0, $value ) );

				},

				'default'           => -7,

			)

		);



		// Sync-Zeitraum: Termine bis (Tage in der Zukunft, positiv)

		register_setting(

			'repro_ct_suite',

			'repro_ct_suite_sync_to_days',

			array(

				'type'              => 'integer',

				'description'       => __( 'Anzahl der Tage in der Zukunft fÃ¼r Event-Synchronisation (positiv)', 'repro-ct-suite' ),

				'sanitize_callback' => function ( $value ) {

					$value = intval( $value );

					return max( 0, min( 730, $value ) );

				},

				'default'           => 365,

			)

		);



	}



	/**

	 * AJAX Handler: Kalender synchronisieren

	 *

	 * Ruft Kalender von ChurchTools ab und speichert sie in der Datenbank.

	 * BehÃ¤lt die Benutzer-Auswahl (is_selected) bei Updates bei.

	 *

	 * @since    0.3.0

	 */

	public function ajax_sync_calendars(): void {

		// Nonce-PrÃ¼fung

		check_ajax_referer( 'repro_ct_suite_admin', 'nonce' );



		// BerechtigungsprÃ¼fung

		if ( ! current_user_can( 'manage_options' ) ) {

			wp_send_json_error( array(

				'message' => __( 'Keine Berechtigung fÃ¼r diese Aktion.', 'repro-ct-suite' )

			) );

		}



		try {

			// Dependencies laden

			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-repro-ct-suite-logger.php';

			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-repro-ct-suite-ct-client.php';

			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/repositories/class-repro-ct-suite-repository-base.php';

			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/repositories/class-repro-ct-suite-calendars-repository.php';

			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/services/class-repro-ct-suite-calendar-sync-service.php';



		} catch ( Exception $e ) {

			wp_send_json_error( array(

				'message' => 'Fehler beim Laden: ' . $e->getMessage()

			) );

			return;

		} catch ( Error $e ) {

			wp_send_json_error( array(

				'message' => 'PHP Error: ' . $e->getMessage()

			) );

			return;

		}



		try {

			// Credentials aus WordPress Optionen laden

			$tenant = get_option( 'repro_ct_suite_ct_tenant', '' );

			$username = get_option( 'repro_ct_suite_ct_username', '' );

			$encrypted_password = get_option( 'repro_ct_suite_ct_password', '' );

			$password = Repro_CT_Suite_Crypto::decrypt( $encrypted_password );

			

			// CT_Client mit Credentials instanziieren

			$ct_client = new Repro_CT_Suite_CT_Client( $tenant, $username, $password );

			$calendars_repo = new Repro_CT_Suite_Calendars_Repository();

			$sync_service = new Repro_CT_Suite_Calendar_Sync_Service( $ct_client, $calendars_repo );



			// Log Header

			Repro_CT_Suite_Logger::header( 'KALENDER-SYNCHRONISATION GESTARTET' );

			Repro_CT_Suite_Logger::log( 'Zeitpunkt: ' . current_time( 'mysql' ) );

			Repro_CT_Suite_Logger::log( 'Tenant: ' . $tenant );

			Repro_CT_Suite_Logger::separator();



			// Synchronisation durchfÃ¼hren

			$result = $sync_service->sync_calendars();



			// DEBUG: Log Response

			if ( is_wp_error( $result ) ) {

				Repro_CT_Suite_Logger::log( 'WP_Error aufgetreten!', 'error' );

				Repro_CT_Suite_Logger::log( 'Error Code: ' . $result->get_error_code(), 'error' );

				Repro_CT_Suite_Logger::log( 'Error Message: ' . $result->get_error_message(), 'error' );

				Repro_CT_Suite_Logger::separator( '=', 60 );

				

				wp_send_json_error( array(

					'message' => $result->get_error_message(),

					'debug' => array(

						'error_code' => $result->get_error_code(),

						'error_message' => $result->get_error_message(),

						'url' => $debug_info['url']

					)

				) );

				return;

			}



			Repro_CT_Suite_Logger::log( 'Response erhalten - Typ: ' . gettype( $result ) );

			Repro_CT_Suite_Logger::log( 'Kalender gesamt: ' . ( isset( $result['total'] ) ? $result['total'] : '0' ), 'info' );

			Repro_CT_Suite_Logger::log( 'Neu eingefÃ¼gt: ' . ( isset( $result['inserted'] ) ? $result['inserted'] : '0' ), 'success' );

			Repro_CT_Suite_Logger::log( 'Aktualisiert: ' . ( isset( $result['updated'] ) ? $result['updated'] : '0' ), 'success' );

			Repro_CT_Suite_Logger::log( 'Fehler: ' . ( isset( $result['errors'] ) ? $result['errors'] : '0' ), ( isset( $result['errors'] ) && $result['errors'] > 0 ? 'warning' : 'success' ) );



			if ( isset( $result['errors'] ) && ! empty( $result['errors'] ) ) {

				Repro_CT_Suite_Logger::dump( $result['errors'], 'Fehler-Details', 'warning' );

				Repro_CT_Suite_Logger::header( 'SYNC MIT FEHLERN BEENDET', 'warning' );

				

				wp_send_json_error( array(

					'message' => sprintf(

						__( 'Synchronisation mit Fehlern abgeschlossen. %d Kalender importiert, %d Fehler aufgetreten.', 'repro-ct-suite' ),

						$result['inserted'] + $result['updated'],

						count( $result['errors'] )

					),

					'stats' => $result,

					'debug' => $debug_info

				) );

			}



			Repro_CT_Suite_Logger::header( 'KALENDER-SYNCHRONISATION ERFOLGREICH', 'success' );



			wp_send_json_success( array(

				'message' => sprintf(

					__( 'Erfolgreich %d Kalender synchronisiert (%d neu, %d aktualisiert).', 'repro-ct-suite' ),

					$result['total'],

					$result['inserted'],

					$result['updated']

				),

				'stats' => $result,

				'debug' => $debug_info

			) );



		} catch ( Exception $e ) {

			error_log( '[REPRO CT-SUITE] EXCEPTION: ' . $e->getMessage() );

			error_log( '[REPRO CT-SUITE] File: ' . $e->getFile() . ' Line: ' . $e->getLine() );

			error_log( '[REPRO CT-SUITE] Trace: ' . $e->getTraceAsString() );

			

			Repro_CT_Suite_Logger::header( 'EXCEPTION AUFGETRETEN', 'error' );

			Repro_CT_Suite_Logger::log( 'Exception: ' . $e->getMessage(), 'error' );

			Repro_CT_Suite_Logger::log( 'File: ' . $e->getFile() . ' (Line ' . $e->getLine() . ')', 'error' );

			Repro_CT_Suite_Logger::log( 'Stack Trace:', 'error' );

			$trace_lines = explode( "\n", $e->getTraceAsString() );

			foreach ( array_slice( $trace_lines, 0, 10 ) as $line ) {

				Repro_CT_Suite_Logger::log( '  ' . $line, 'error' );

			}

			Repro_CT_Suite_Logger::header( 'SYNC FAILED', 'error' );

			

			wp_send_json_error( array(

				'message' => sprintf(

					__( 'Fehler bei der Synchronisation: %s', 'repro-ct-suite' ),

					$e->getMessage()

				),

				'debug' => array(

					'error' => $e->getMessage(),

					'file' => $e->getFile(),

					'line' => $e->getLine(),

					'trace' => $e->getTraceAsString()

				)

			) );

		} catch ( Error $e ) {

			error_log( '[REPRO CT-SUITE] PHP ERROR: ' . $e->getMessage() );

			error_log( '[REPRO CT-SUITE] File: ' . $e->getFile() . ' Line: ' . $e->getLine() );

			error_log( '[REPRO CT-SUITE] Trace: ' . $e->getTraceAsString() );

			

			wp_send_json_error( array(

				'message' => 'PHP Error: ' . $e->getMessage(),

				'debug' => array(

					'error' => $e->getMessage(),

					'file' => $e->getFile(),

					'line' => $e->getLine(),

					'trace' => $e->getTraceAsString()

				)

			) );

		}

	}



	/**

	 * AJAX Handler: Termine synchronisieren

	 *

	 * Verwendet den neuen vereinfachten Sync-Service fÃ¼r alle Termine.

	 *

	 * @since    0.4.0

	 */

	public function ajax_sync_appointments(): void {

		// Nonce-PrÃ¼fung

		check_ajax_referer( 'repro_ct_suite_admin', 'nonce' );



		// BerechtigungsprÃ¼fung

		if ( ! current_user_can( 'manage_options' ) ) {

			wp_send_json_error( array(

				'message' => __( 'Keine Berechtigung fÃ¼r diese Aktion.', 'repro-ct-suite' )

			) );

		}



		// Dependencies laden

		try {

			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-repro-ct-suite-logger.php';

			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-repro-ct-suite-ct-client.php';

			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/repositories/class-repro-ct-suite-repository-base.php';

			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/repositories/class-repro-ct-suite-calendars-repository.php';

			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/repositories/class-repro-ct-suite-events-repository.php';

			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/repositories/class-repro-ct-suite-schedule-repository.php';

			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/services/class-repro-ct-suite-sync-service.php';

		} catch ( Exception $e ) {

			wp_send_json_error( array(

				'message' => 'Fehler beim Laden der Dependencies: ' . $e->getMessage()

			) );

			return;

		}



		// Konfiguration prÃ¼fen

		$tenant   = get_option( 'repro_ct_suite_ct_tenant', '' );

		$username = get_option( 'repro_ct_suite_ct_username', '' );

		$password = get_option( 'repro_ct_suite_ct_password', '' );



		if ( empty( $tenant ) || empty( $username ) || empty( $password ) ) {

			wp_send_json_error( array(

				'message' => __( 'ChurchTools-Verbindung nicht konfiguriert. Bitte prÃ¼fen Sie die Einstellungen.', 'repro-ct-suite' )

			) );

			return;

		}



		// Services initialisieren

		$ct_client      = new Repro_CT_Suite_CT_Client( $tenant, $username, $password );

		$calendars_repo = new Repro_CT_Suite_Calendars_Repository();

		$events_repo    = new Repro_CT_Suite_Events_Repository();

		$schedule_repo  = new Repro_CT_Suite_Schedule_Repository();

		$sync_service   = new Repro_CT_Suite_Sync_Service( $ct_client, $events_repo, $calendars_repo, $schedule_repo );



		// AusgewÃ¤hlte Kalender ermitteln

		$selected_calendar_ids = $calendars_repo->get_selected_calendar_ids();

		

		if ( empty( $selected_calendar_ids ) ) {

			wp_send_json_error( array(

				'message' => __( 'Keine Kalender fÃ¼r den Import ausgewÃ¤hlt. Bitte wÃ¤hlen Sie Kalender in den Einstellungen aus.', 'repro-ct-suite' )

			) );

			return;

		}



		Repro_CT_Suite_Logger::log( 'AusgewÃ¤hlte Kalender (externe ChurchTools-IDs): ' . implode( ', ', $selected_calendar_ids ) );



		// Sync-Zeitraum

		$sync_from_days = get_option( 'repro_ct_suite_sync_from_days', -7 );

		$sync_to_days   = get_option( 'repro_ct_suite_sync_to_days', 90 );

		$from = date( 'Y-m-d', current_time( 'timestamp' ) + ( (int) $sync_from_days * DAY_IN_SECONDS ) );

		$to   = date( 'Y-m-d', current_time( 'timestamp' ) + ( (int) $sync_to_days * DAY_IN_SECONDS ) );



		Repro_CT_Suite_Logger::log( 'Zeitraum: von ' . $from . ' bis ' . $to );



		// Sync ausfÃ¼hren mit externen ChurchTools-IDs (kein lokales Mapping mehr!)

		$result = $sync_service->sync_events( array(

			'calendar_ids' => $selected_calendar_ids, // Direkt ChurchTools-IDs!

			'from'         => $from,

			'to'           => $to,

		) );



		if ( is_wp_error( $result ) ) {

			Repro_CT_Suite_Logger::header( 'SYNC FEHLGESCHLAGEN', 'error' );

			Repro_CT_Suite_Logger::log( 'Error: ' . $result->get_error_message(), 'error' );



			wp_send_json_error( array(

				'message' => sprintf(

					__( 'Fehler beim Synchronisieren der Termine: %s', 'repro-ct-suite' ),

					$result->get_error_message()

				),

				'debug' => array(

					'error_code'    => $result->get_error_code(),

					'error_message' => $result->get_error_message(),

				),

			) );

			return;

		}



		// Erfolg

		Repro_CT_Suite_Logger::log( 'SYNC ERFOLGREICH ABGESCHLOSSEN', 'success' );



		wp_send_json_success( array(

			'message' => sprintf(

				__( 'Synchronisation erfolgreich: %d Termine verarbeitet (%d neu, %d aktualisiert)', 'repro-ct-suite' ),

				$result['events_found'],

				$result['events_inserted'],

				$result['events_updated']

			),

			'stats' => $result,

		) );

	}



	/**

	 * AJAX Handler: Einzelne Tabelle leeren

		} catch ( Exception $e ) {

			error_log( '[REPRO CT-SUITE] FEHLER beim Laden der Dependencies (appointments): ' . $e->getMessage() );

			wp_send_json_error( array(

				'message' => 'Fehler beim Laden: ' . $e->getMessage(),

				'debug'   => array(

					'file' => $e->getFile(),

					'line' => $e->getLine(),

					'trace' => $e->getTraceAsString(),

				),

			) );

			return;

		} catch ( Error $e ) {

			error_log( '[REPRO CT-SUITE] PHP ERROR beim Laden (appointments): ' . $e->getMessage() );

			wp_send_json_error( array(

				'message' => 'PHP Error: ' . $e->getMessage(),

				'debug'   => array(

					'file' => $e->getFile(),

					'line' => $e->getLine(),

					'trace' => $e->getTraceAsString(),

				),

			) );

			return;

		}



		try {

			// Services instanziieren

			$tenant = get_option( 'repro_ct_suite_ct_tenant', '' );

			$username = get_option( 'repro_ct_suite_ct_username', '' );

			$encrypted_password = get_option( 'repro_ct_suite_ct_password', '' );

			$password = Repro_CT_Suite_Crypto::decrypt( $encrypted_password );

			$ct_client = new Repro_CT_Suite_CT_Client( $tenant, $username, $password );

			$calendars_repo = new Repro_CT_Suite_Calendars_Repository();

			$events_repo = new Repro_CT_Suite_Events_Repository();

			$appointments_repo = new Repro_CT_Suite_Appointments_Repository();

			$schedule_repo = new Repro_CT_Suite_Schedule_Repository();



			// Debug-Kontext

			Repro_CT_Suite_Logger::log( 'Tenant: ' . $tenant );

			Repro_CT_Suite_Logger::log( 'Username: ' . ( $username ? '[gesetzt]' : '[leer]' ) );



			// Nur ausgewÃ¤hlte Kalender synchronisieren

			$selected_calendar_ids = $calendars_repo->get_selected_ids();

			Repro_CT_Suite_Logger::log( 'AusgewÃ¤hlte Kalender (lokale IDs): ' . ( $selected_calendar_ids ? implode( ',', $selected_calendar_ids ) : '[keine]' ) );



			if ( empty( $selected_calendar_ids ) ) {

				wp_send_json_error( array(

					'message' => __( 'Keine Kalender ausgewÃ¤hlt. Bitte wÃ¤hlen Sie mindestens einen Kalender aus.', 'repro-ct-suite' )

				) );

			}



			// Externe Calendar-IDs fÃ¼r Events-Filterung holen

			$selected_calendar_ids = array();

			foreach ( $selected_calendar_ids as $local_id ) {

				$calendar = $calendars_repo->get_by_id( $local_id );

				if ( $calendar && ! empty( $calendar->calendar_id ) ) {

					$selected_calendar_ids[] = (string) $calendar->calendar_id;

				}

			}

			Repro_CT_Suite_Logger::log( 'AusgewÃ¤hlte Kalender (externe IDs): ' . ( $selected_calendar_ids ? implode( ',', $selected_calendar_ids ) : '[keine]' ) );



			// Zeitraum bestimmen (aus den gespeicherten Optionen)

			$sync_from_days = get_option( 'repro_ct_suite_sync_from_days', -7 );

			$sync_to_days   = get_option( 'repro_ct_suite_sync_to_days', 90 );

			$from = date( 'Y-m-d', current_time( 'timestamp' ) + ( (int) $sync_from_days * DAY_IN_SECONDS ) );

			$to   = date( 'Y-m-d', current_time( 'timestamp' ) + ( (int) $sync_to_days * DAY_IN_SECONDS ) );

			Repro_CT_Suite_Logger::log( 'Zeitraum: von ' . $from . ' bis ' . $to );



			// STRATEGIE: 

			// 1. Zuerst Events aus /events synchronisieren (enthÃ¤lt alle ChurchTools-Events)

			//    WICHTIG: Mit calendar_ids filtern, damit nur Events ausgewÃ¤hlter Kalender importiert werden

			// 2. Dann Appointments - ABER nur die, deren appointment_id noch NICHT in rcts_events vorkommt

			//    (d.h. Appointments OHNE zugeordnetes Event)

			// -> Verhindert Duplikate, da Events aus /events Vorrang haben



			// 1) Events synchronisieren (nur von ausgewÃ¤hlten Kalendern)

			Repro_CT_Suite_Logger::log( 'SCHRITT 1: Events synchronisieren (nur ausgewÃ¤hlte Kalender)...' );

			$events_service = new Repro_CT_Suite_Events_Sync_Service( $ct_client, $events_repo, $calendars_repo, $schedule_repo );

			$events_result = $events_service->sync_events( array(

				'from'         => $from,

				'to'           => $to,

				'calendar_ids' => $selected_calendar_ids, // Externe ChurchTools Calendar-IDs

			) );



			if ( is_wp_error( $events_result ) ) {

				Repro_CT_Suite_Logger::header( 'EVENTS-SYNC FEHLGESCHLAGEN', 'error' );

				Repro_CT_Suite_Logger::log( 'Error: ' . $events_result->get_error_message(), 'error' );

				wp_send_json_error( array(

					'message' => sprintf(

						__( 'Fehler beim Synchronisieren der Events: %s', 'repro-ct-suite' ),

						$events_result->get_error_message()

					),

					'debug' => array(

						'stage' => 'events',

						'error_code' => $events_result->get_error_code(),

						'error_message' => $events_result->get_error_message(),

					),

				) );

				return;

			}



			// 2) Appointments synchronisieren (nur die OHNE Event)

			Repro_CT_Suite_Logger::log( 'SCHRITT 2: Appointments synchronisieren (nur ohne Event)...' );

			$appointments_service = new Repro_CT_Suite_Appointments_Sync_Service( $ct_client, $appointments_repo, $events_repo, $calendars_repo, $schedule_repo );

			$appointments_result = $appointments_service->sync_appointments( array(

				'calendar_ids' => $selected_calendar_ids,

				'from' => $from,

				'to'   => $to,

			) );



			// Fehler aus dem Service robust behandeln

			if ( is_wp_error( $appointments_result ) ) {

				$which = 'appointments';

				$err   = $appointments_result;



				Repro_CT_Suite_Logger::header( 'SYNC FEHLGESCHLAGEN: ' . strtoupper( $which ), 'error' );

				Repro_CT_Suite_Logger::log( 'Error Code: ' . $err->get_error_code(), 'error' );

				Repro_CT_Suite_Logger::log( 'Error Message: ' . $err->get_error_message(), 'error' );

				$error_data = $err->get_error_data();

				if ( ! empty( $error_data ) ) {

					Repro_CT_Suite_Logger::dump( $error_data, 'Error Data', 'error' );

				}



				wp_send_json_error( array(

					'message' => sprintf(

						__( 'Fehler bei der Synchronisation (%s): %s', 'repro-ct-suite' ),

						$which,

						$err->get_error_message()

					),

					'debug' => array(

						'stage' => $which,

						'error_code' => $err->get_error_code(),

						'error_message' => $err->get_error_message(),

						'error_data' => $error_data,

						'period' => array( 'from' => $from, 'to' => $to ),

						'selected_calendar_ids' => $selected_calendar_ids,

					),

				) );

				return;

			}



			// Erfolgsmeldung

			// Zeitstempel und Statistik speichern

			update_option( 'repro_ct_suite_last_sync_time', current_time( 'mysql' ) );

			$combined_stats = array(

				'events_created' => ( isset( $events_result['inserted'] ) ? (int) $events_result['inserted'] : 0 ),

				'events_updated' => ( isset( $events_result['updated'] ) ? (int) $events_result['updated'] : 0 ),

				'appointments_created' => ( isset( $appointments_result['appointments_inserted'] ) ? (int) $appointments_result['appointments_inserted'] : 0 ),

				'appointments_updated' => ( isset( $appointments_result['appointments_updated'] ) ? (int) $appointments_result['appointments_updated'] : 0 ),

				'skipped_has_event' => ( isset( $appointments_result['skipped_has_event'] ) ? (int) $appointments_result['skipped_has_event'] : 0 ),

			);

			update_option( 'repro_ct_suite_last_sync_stats', $combined_stats );



			// Notice fÃ¼r nÃ¤chsten Seitenaufruf vorbereiten

			$total_new = ( isset( $events_result['events_inserted'] ) ? (int) $events_result['events_inserted'] : 0 );

			$total_updated = ( isset( $events_result['events_updated'] ) ? (int) $events_result['events_updated'] : 0 );

			$appointments_found = ( isset( $events_result['appointments_found'] ) ? (int) $events_result['appointments_found'] : 0 );

			

			set_transient( 'repro_ct_suite_sync_notice', array(

				'type' => 'success',

				'message' => sprintf(

					__( 'âœ… Synchronisation erfolgreich abgeschlossen!', 'repro-ct-suite' )

				),

				'stats' => array(

					'calendars_processed' => isset( $events_result['calendars_processed'] ) ? $events_result['calendars_processed'] : 0,

					'events_found' => isset( $events_result['events_found'] ) ? $events_result['events_found'] : 0,

					'appointments_found' => $appointments_found,

					'events_inserted' => $total_new,

					'events_updated' => $total_updated,

					'events_skipped' => isset( $events_result['events_skipped'] ) ? $events_result['events_skipped'] : 0,

				)

			), 60 ); // 60 Sekunden gÃ¼ltig



			wp_send_json_success( array(

				'message' => sprintf(

					__( 'Synchronisation abgeschlossen: %d Events (aus /events), %d Events aus Appointments, %d Termine ohne Event.', 'repro-ct-suite' ),

					( isset( $events_result['inserted'] ) ? (int) $events_result['inserted'] : 0 ) + ( isset( $events_result['updated'] ) ? (int) $events_result['updated'] : 0 ),

					( isset( $appointments_result['events_inserted'] ) ? (int) $appointments_result['events_inserted'] : 0 ) + ( isset( $appointments_result['events_updated'] ) ? (int) $appointments_result['events_updated'] : 0 ),

					( isset( $appointments_result['appointments_inserted'] ) ? (int) $appointments_result['appointments_inserted'] : 0 ) + ( isset( $appointments_result['appointments_updated'] ) ? (int) $appointments_result['appointments_updated'] : 0 )

				),

				'stats' => array(

					'events' => $events_result,

					'appointments' => $appointments_result

				)

			) );



		} catch ( Exception $e ) {

			Repro_CT_Suite_Logger::header( 'EXCEPTION IM APPOINTMENTS-SYNC (Terminvorlagen)', 'error' );

			Repro_CT_Suite_Logger::log( 'Message: ' . $e->getMessage(), 'error' );

			Repro_CT_Suite_Logger::log( 'File: ' . $e->getFile() . ' Line: ' . $e->getLine(), 'error' );

			wp_send_json_error( array(

				'message' => sprintf(

					__( 'Fehler bei der Synchronisation: %s', 'repro-ct-suite' ),

					$e->getMessage()

				),

				'debug' => array(

					'file' => $e->getFile(),

					'line' => $e->getLine(),

				)

			) );

		} catch ( Error $e ) {

			Repro_CT_Suite_Logger::header( 'PHP ERROR IM TERMINE-SYNC', 'error' );

			Repro_CT_Suite_Logger::log( 'Message: ' . $e->getMessage(), 'error' );

			Repro_CT_Suite_Logger::log( 'File: ' . $e->getFile() . ' Line: ' . $e->getLine(), 'error' );

			wp_send_json_error( array(

				'message' => 'PHP Error: ' . $e->getMessage(),

				'debug' => array(

					'file' => $e->getFile(),

					'line' => $e->getLine(),

				)

			) );

		}

	}



	/**

	 * AJAX: Leert alle Plugin-Tabellen (nur fÃ¼r Admins, Debug-Funktion)

	 */

	public function ajax_clear_tables(): void {

		// Nonce-PrÃ¼fung

		check_ajax_referer( 'repro_ct_suite_admin', 'nonce' );



		// BerechtigungsprÃ¼fung

		if ( ! current_user_can( 'manage_options' ) ) {

			wp_send_json_error( array(

				'message' => __( 'Keine Berechtigung fÃ¼r diese Aktion.', 'repro-ct-suite' )

			) );

		}



		global $wpdb;

		

		// Tabellen leeren (TRUNCATE lÃ¶scht alle Daten und resettet AUTO_INCREMENT)

		$tables = array(

			$wpdb->prefix . 'rcts_event_services',

			$wpdb->prefix . 'rcts_appointments',

			$wpdb->prefix . 'rcts_events',

			$wpdb->prefix . 'rcts_schedule',

			$wpdb->prefix . 'rcts_calendars',

		);



		$cleared = array();

		$errors = array();



		foreach ( $tables as $table ) {

			// PrÃ¼fe, ob Tabelle existiert

			$table_exists = $wpdb->get_var( $wpdb->prepare( 

				"SHOW TABLES LIKE %s", 

				$table 

			) );



			if ( $table_exists ) {

				$result = $wpdb->query( "TRUNCATE TABLE `{$table}`" );

				if ( $result !== false ) {

					$cleared[] = $table;

				} else {

					$errors[] = $table . ' (Fehler: ' . $wpdb->last_error . ')';

				}

			} else {

				$errors[] = $table . ' (existiert nicht)';

			}

		}



		if ( empty( $errors ) ) {

			wp_send_json_success( array(

				'message' => sprintf(

					__( 'Alle Tabellen wurden geleert: %s', 'repro-ct-suite' ),

					implode( ', ', array_map( function( $t ) use ( $wpdb ) { 

						return str_replace( $wpdb->prefix, '', $t ); 

					}, $cleared ) )

				),

				'cleared' => $cleared,

			) );

		} else {

			wp_send_json_error( array(

				'message' => sprintf(

					__( 'Tabellen teilweise geleert. Fehler bei: %s', 'repro-ct-suite' ),

					implode( ', ', $errors )

				),

				'cleared' => $cleared,

				'errors' => $errors,

			) );

		}

	}



	/**

	 * AJAX: Leert eine einzelne Tabelle (Debug-Funktion)

	 */

	public function ajax_clear_single_table(): void {

		// Nonce-PrÃ¼fung

		check_ajax_referer( 'repro_ct_suite_admin', 'nonce' );



		// BerechtigungsprÃ¼fung

		if ( ! current_user_can( 'manage_options' ) ) {

			wp_send_json_error( array(

				'message' => __( 'Keine Berechtigung fÃ¼r diese Aktion.', 'repro-ct-suite' )

			) );

		}



		$table_key = isset( $_POST['table'] ) ? sanitize_text_field( $_POST['table'] ) : '';



		if ( empty( $table_key ) ) {

			wp_send_json_error( array(

				'message' => __( 'Keine Tabelle angegeben.', 'repro-ct-suite' )

			) );

		}



		global $wpdb;



		// Mapping von table_key zu echtem Tabellennamen

		$table_mapping = array(

			'rcts_calendars'       => $wpdb->prefix . 'rcts_calendars',

			'rcts_events'          => $wpdb->prefix . 'rcts_events',

			'rcts_appointments'    => $wpdb->prefix . 'rcts_appointments',

			'rcts_event_services'  => $wpdb->prefix . 'rcts_event_services',

			'rcts_schedule'        => $wpdb->prefix . 'rcts_schedule',

		);



		if ( ! isset( $table_mapping[ $table_key ] ) ) {

			wp_send_json_error( array(

				'message' => __( 'UngÃ¼ltige Tabelle.', 'repro-ct-suite' )

			) );

		}



		$table = $table_mapping[ $table_key ];



		// PrÃ¼fe, ob Tabelle existiert

		$table_exists = $wpdb->get_var( $wpdb->prepare( 

			"SHOW TABLES LIKE %s", 

			$table 

		) );



		if ( ! $table_exists ) {

			wp_send_json_error( array(

				'message' => sprintf( 

					__( 'Tabelle %s existiert nicht.', 'repro-ct-suite' ),

					$table

				)

			) );

		}



		// Tabelle leeren

		$result = $wpdb->query( "TRUNCATE TABLE `{$table}`" );



		if ( $result !== false ) {

			wp_send_json_success( array(

				'message' => sprintf(

					__( 'Tabelle %s wurde erfolgreich geleert.', 'repro-ct-suite' ),

					str_replace( $wpdb->prefix, '', $table )

				),

				'table' => $table_key,

			) );

		} else {

			wp_send_json_error( array(

				'message' => sprintf(

					__( 'Fehler beim Leeren der Tabelle %s: %s', 'repro-ct-suite' ),

					$table,

					$wpdb->last_error

				)

			) );

		}

	}



	/**

	 * AJAX: FÃ¼hrt DB-Migrationen manuell aus

	 */

	public function ajax_run_migrations(): void {

		// Nonce-PrÃ¼fung

		check_ajax_referer( 'repro_ct_suite_admin', 'nonce' );



		// BerechtigungsprÃ¼fung

		if ( ! current_user_can( 'manage_options' ) ) {

			wp_send_json_error( array(

				'message' => __( 'Keine Berechtigung fÃ¼r diese Aktion.', 'repro-ct-suite' )

			) );

		}



		// Migrations-Klasse laden

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-repro-ct-suite-migrations.php';



		$old_version = get_option( 'repro_ct_suite_db_version', '0' );



		try {

			// Migration ausfÃ¼hren

			Repro_CT_Suite_Migrations::run();



			$new_version = get_option( 'repro_ct_suite_db_version', '0' );



			if ( $old_version === $new_version ) {

				wp_send_json_success( array(

					'message' => sprintf(

						__( 'Datenbank ist bereits auf dem neuesten Stand (Version %s).', 'repro-ct-suite' ),

						$new_version

					),

					'old_version' => $old_version,

					'new_version' => $new_version,

				) );

			} else {

				wp_send_json_success( array(

					'message' => sprintf(

						__( 'Datenbank erfolgreich aktualisiert von Version %s auf %s.', 'repro-ct-suite' ),

						$old_version,

						$new_version

					),

					'old_version' => $old_version,

					'new_version' => $new_version,

				) );

			}

		} catch ( Exception $e ) {

			wp_send_json_error( array(

				'message' => sprintf(

					__( 'Fehler bei der Migration: %s', 'repro-ct-suite' ),

					$e->getMessage()

				)

			) );

		}

	}



	/**

	 * AJAX: Leert die Debug-Log-Datei

	 */

	public function ajax_clear_log(): void {

		// Nonce-PrÃ¼fung

		check_ajax_referer( 'repro_ct_suite_admin', 'nonce' );



		// BerechtigungsprÃ¼fung

		if ( ! current_user_can( 'manage_options' ) ) {

			wp_send_json_error( array(

				'message' => __( 'Keine Berechtigung fÃ¼r diese Aktion.', 'repro-ct-suite' )

			) );

		}



		$log_file = WP_CONTENT_DIR . '/repro-ct-suite-debug.log';



		if ( ! file_exists( $log_file ) ) {

			wp_send_json_error( array(

				'message' => __( 'Log-Datei existiert nicht.', 'repro-ct-suite' )

			) );

		}



		// Log-Datei leeren

		$result = file_put_contents( $log_file, '' );



		if ( $result !== false ) {

			wp_send_json_success( array(

				'message' => __( 'Debug-Log wurde erfolgreich geleert.', 'repro-ct-suite' )

			) );

		} else {

			wp_send_json_error( array(

				'message' => __( 'Fehler beim Leeren der Log-Datei.', 'repro-ct-suite' )

			) );

		}

	}



	/**

	 * AJAX: LÃ¶scht alle ChurchTools-Zugangsdaten

	 */

	/**

	 * AJAX Handler: Zugangsdaten zurÃ¼cksetzen

	 *

	 * @since 0.3.5.3

	 */

	public function ajax_reset_credentials(): void {

		// Nonce-PrÃ¼fung

		check_ajax_referer( 'repro_ct_suite_admin', 'nonce' );



		// BerechtigungsprÃ¼fung

		if ( ! current_user_can( 'manage_options' ) ) {

			wp_send_json_error( array(

				'message' => __( 'Keine Berechtigung fÃ¼r diese Aktion.', 'repro-ct-suite' )

			) );

		}



		// Zugangsdaten lÃ¶schen

		delete_option( 'repro_ct_suite_ct_tenant' );

		delete_option( 'repro_ct_suite_ct_username' );

		delete_option( 'repro_ct_suite_ct_password' );

		delete_option( 'repro_ct_suite_ct_session' );

		delete_option( 'repro_ct_suite_connection_verified' );



		wp_send_json_success( array(

			'message' => __( 'Zugangsdaten wurden erfolgreich gelÃ¶scht.', 'repro-ct-suite' ),

			'ask_full_reset' => true

		) );

	}



	/**

	 * AJAX Handler: VollstÃ¤ndiger Reset (Zugangsdaten + alle Daten + Einstellungen)

	 *

	 * @since 0.3.5.6

	 */

	public function ajax_full_reset(): void {

		// Nonce-PrÃ¼fung

		check_ajax_referer( 'repro_ct_suite_admin', 'nonce' );



		// BerechtigungsprÃ¼fung

		if ( ! current_user_can( 'manage_options' ) ) {

			wp_send_json_error( array(

				'message' => __( 'Keine Berechtigung fÃ¼r diese Aktion.', 'repro-ct-suite' )

			) );

		}



		global $wpdb;



		// Alle Tabellen leeren

		$tables = array(

			$wpdb->prefix . 'rcts_calendars',

			$wpdb->prefix . 'rcts_events',

			$wpdb->prefix . 'rcts_appointments',

			$wpdb->prefix . 'rcts_event_services'

		);



		foreach ( $tables as $table ) {

			$wpdb->query( "TRUNCATE TABLE {$table}" );

		}



		// Alle Plugin-Optionen lÃ¶schen

		delete_option( 'repro_ct_suite_ct_tenant' );

		delete_option( 'repro_ct_suite_ct_username' );

		delete_option( 'repro_ct_suite_ct_password' );

		delete_option( 'repro_ct_suite_ct_session' );

		delete_option( 'repro_ct_suite_connection_verified' );

		delete_option( 'repro_ct_suite_last_calendar_sync' );

		delete_option( 'repro_ct_suite_last_event_sync' );

		delete_option( 'repro_ct_suite_last_appointment_sync' );



		wp_send_json_success( array(

			'message' => __( 'VollstÃ¤ndiger Reset durchgefÃ¼hrt. Alle Zugangsdaten, Einstellungen und Daten wurden gelÃ¶scht.', 'repro-ct-suite' )

		) );

	}



	/**

	 * AJAX Handler: Calendar-IDs aus raw_payload extrahieren und korrigieren

	 *

	 * @since 0.3.6.0

	 */

	public function ajax_fix_calendar_ids(): void {

		// Nonce-PrÃ¼fung

		check_ajax_referer( 'repro_ct_suite_admin', 'nonce' );



		// BerechtigungsprÃ¼fung

		if ( ! current_user_can( 'manage_options' ) ) {

			wp_send_json_error( array(

				'message' => __( 'Keine Berechtigung fÃ¼r diese Aktion.', 'repro-ct-suite' )

			) );

		}



		global $wpdb;

		

		$events_table = $wpdb->prefix . 'rcts_events';

		$appointments_table = $wpdb->prefix . 'rcts_appointments';

		

		$stats = array(

			'events_total' => 0,

			'events_updated' => 0,

			'events_skipped' => 0,

			'appointments_total' => 0,

			'appointments_updated' => 0,

			'appointments_skipped' => 0

		);

		

		// Events: calendar_id aus raw_payload extrahieren

		$events = $wpdb->get_results( "SELECT id, calendar_id, raw_payload FROM {$events_table} WHERE raw_payload IS NOT NULL" );

		$stats['events_total'] = count( $events );

		

		foreach ( $events as $event ) {

			$payload = json_decode( $event->raw_payload, true );

			if ( ! $payload ) {

				$stats['events_skipped']++;

				continue;

			}

			

			// calendar_id extrahieren

			$new_calendar_id = null;

			if ( isset( $payload['calendar']['id'] ) ) {

				$new_calendar_id = (string) $payload['calendar']['id'];

			} elseif ( isset( $payload['calendarId'] ) ) {

				$new_calendar_id = (string) $payload['calendarId'];

			} elseif ( isset( $payload['calendar_id'] ) ) {

				$new_calendar_id = (string) $payload['calendar_id'];

			}

			

			// Nur updaten wenn neuer Wert gefunden wurde und sich vom alten unterscheidet

			if ( $new_calendar_id !== null && $new_calendar_id !== $event->calendar_id ) {

				$wpdb->update(

					$events_table,

					array( 'calendar_id' => $new_calendar_id ),

					array( 'id' => $event->id ),

					array( '%s' ),

					array( '%d' )

				);

				$stats['events_updated']++;

			} else {

				$stats['events_skipped']++;

			}

		}

		

		// Appointments: calendar_id aus raw_payload extrahieren

		$appointments = $wpdb->get_results( "SELECT id, calendar_id, raw_payload FROM {$appointments_table} WHERE raw_payload IS NOT NULL" );

		$stats['appointments_total'] = count( $appointments );

		

		foreach ( $appointments as $appointment ) {

			$payload = json_decode( $appointment->raw_payload, true );

			if ( ! $payload ) {

				$stats['appointments_skipped']++;

				continue;

			}

			

			// Bei Appointments kann die Struktur verschachtelt sein

			$base = $payload['base'] ?? $payload;

			$new_calendar_id = null;

			

			if ( isset( $base['calendar']['id'] ) ) {

				$new_calendar_id = (string) $base['calendar']['id'];

			} elseif ( isset( $base['calendarId'] ) ) {

				$new_calendar_id = (string) $base['calendarId'];

			} elseif ( isset( $payload['calendar']['id'] ) ) {

				$new_calendar_id = (string) $payload['calendar']['id'];

			}

			

			// Nur updaten wenn neuer Wert gefunden wurde und sich vom alten unterscheidet

			if ( $new_calendar_id !== null && $new_calendar_id !== $appointment->calendar_id ) {

				$wpdb->update(

					$appointments_table,

					array( 'calendar_id' => $new_calendar_id ),

					array( 'id' => $appointment->id ),

					array( '%s' ),

					array( '%d' )

				);

				$stats['appointments_updated']++;

			} else {

				$stats['appointments_skipped']++;

			}

		}

		

		wp_send_json_success( array(

			'message' => sprintf(

				__( 'Calendar-IDs korrigiert: %d von %d Events und %d von %d Appointments aktualisiert.', 'repro-ct-suite' ),

				$stats['events_updated'],

				$stats['events_total'],

				$stats['appointments_updated'],

				$stats['appointments_total']

			),

			'stats' => $stats

		) );

	}



	/**

	 * AJAX Handler: Einzelnes Event lÃ¶schen

	 *

	 * @since 0.3.6.1

	 */

	public function ajax_delete_event(): void {

		check_ajax_referer( 'repro_ct_suite_admin', 'nonce' );



		if ( ! current_user_can( 'manage_options' ) ) {

			wp_send_json_error( array(

				'message' => __( 'Keine Berechtigung fÃ¼r diese Aktion.', 'repro-ct-suite' )

			) );

		}



		$event_id = isset( $_POST['event_id'] ) ? (int) $_POST['event_id'] : 0;



		if ( $event_id <= 0 ) {

			wp_send_json_error( array(

				'message' => __( 'UngÃ¼ltige Event-ID.', 'repro-ct-suite' )

			) );

		}



		require_once REPRO_CT_SUITE_PATH . 'includes/repositories/class-repro-ct-suite-repository-base.php';

		require_once REPRO_CT_SUITE_PATH . 'includes/repositories/class-repro-ct-suite-events-repository.php';



		$events_repo = new Repro_CT_Suite_Events_Repository();



		// PrÃ¼fen ob Event existiert

		if ( ! $events_repo->exists( $event_id ) ) {

			wp_send_json_error( array(

				'message' => __( 'Event nicht gefunden.', 'repro-ct-suite' )

			) );

		}



		// Event lÃ¶schen

		$result = $events_repo->delete_by_id( $event_id );



		if ( $result === false ) {

			wp_send_json_error( array(

				'message' => __( 'Fehler beim LÃ¶schen des Events.', 'repro-ct-suite' )

			) );

		}



		wp_send_json_success( array(

			'message' => __( 'Event erfolgreich gelÃ¶scht.', 'repro-ct-suite' )

		) );

	}



	/**

	 * AJAX Handler: Einzelnes Event aktualisieren

	 *

	 * @since 0.3.6.1

	 */

	public function ajax_update_event(): void {

		check_ajax_referer( 'repro_ct_suite_admin', 'nonce' );



		if ( ! current_user_can( 'manage_options' ) ) {

			wp_send_json_error( array(

				'message' => __( 'Keine Berechtigung fÃ¼r diese Aktion.', 'repro-ct-suite' )

			) );

		}



		$event_id = isset( $_POST['event_id'] ) ? (int) $_POST['event_id'] : 0;



		if ( $event_id <= 0 ) {

			wp_send_json_error( array(

				'message' => __( 'UngÃ¼ltige Event-ID.', 'repro-ct-suite' )

			) );

		}



		require_once REPRO_CT_SUITE_PATH . 'includes/repositories/class-repro-ct-suite-repository-base.php';

		require_once REPRO_CT_SUITE_PATH . 'includes/repositories/class-repro-ct-suite-events-repository.php';



		$events_repo = new Repro_CT_Suite_Events_Repository();



		// PrÃ¼fen ob Event existiert

		if ( ! $events_repo->exists( $event_id ) ) {

			wp_send_json_error( array(

				'message' => __( 'Event nicht gefunden.', 'repro-ct-suite' )

			) );

		}



		// Erlaubte Felder zum Aktualisieren

		$allowed_fields = array( 'title', 'description', 'start_datetime', 'end_datetime', 'location_name', 'status' );

		$update_data = array();



		foreach ( $allowed_fields as $field ) {

			if ( isset( $_POST[ $field ] ) ) {

				$update_data[ $field ] = sanitize_text_field( wp_unslash( $_POST[ $field ] ) );

			}

		}



		if ( empty( $update_data ) ) {

			wp_send_json_error( array(

				'message' => __( 'Keine Daten zum Aktualisieren vorhanden.', 'repro-ct-suite' )

			) );

		}



		// Event aktualisieren

		$result = $events_repo->update_by_id( $event_id, $update_data );



		if ( $result === false ) {

			wp_send_json_error( array(

				'message' => __( 'Fehler beim Aktualisieren des Events.', 'repro-ct-suite' )

			) );

		}



		wp_send_json_success( array(

			'message' => __( 'Event erfolgreich aktualisiert.', 'repro-ct-suite' ),

			'updated_fields' => array_keys( $update_data )

		) );

	}



	/**

	 * AJAX Handler: V6 Notice ausblenden

	 */

	public function ajax_dismiss_v6_notice(): void {

		check_ajax_referer( 'repro_ct_suite_admin', 'nonce' );



		if ( ! current_user_can( 'manage_options' ) ) {

			wp_send_json_error( array(

				'message' => __( 'Keine Berechtigung fÃ¼r diese Aktion.', 'repro-ct-suite' )

			) );

		}



		update_option( 'repro_ct_suite_v6_notice_dismissed', true );



		wp_send_json_success( array(

			'message' => __( 'Notice ausgeblendet.', 'repro-ct-suite' )

		) );

	}



	/**

	 * AJAX Handler: Shortcode Vorschau

	 */

	public function ajax_preview_shortcode(): void {

		check_ajax_referer( 'repro_ct_suite_preview', 'nonce' );



		if ( ! current_user_can( 'manage_options' ) ) {

			wp_send_json_error( array(

				'message' => __( 'Keine Berechtigung fÃ¼r diese Aktion.', 'repro-ct-suite' )

			) );

		}



		$shortcode = isset( $_POST['shortcode'] ) ? stripslashes( $_POST['shortcode'] ) : '';



		if ( empty( $shortcode ) ) {

			wp_send_json_error( array(

				'message' => __( 'Kein Shortcode angegeben.', 'repro-ct-suite' )

			) );

		}



		// Shortcode ausfÃ¼hren

		$html = do_shortcode( $shortcode );



		if ( empty( $html ) ) {

			$html = '<p class="no-events">' . __( 'Keine Termine gefunden.', 'repro-ct-suite' ) . '</p>';

		}



		wp_send_json_success( array(

			'html' => $html

		) );

	}



	/**

	 * AJAX Handler: Alle Presets abrufen

	 */

	public function ajax_get_presets(): void {

		check_ajax_referer( 'repro_ct_suite_presets', 'nonce' );



		if ( ! current_user_can( 'manage_options' ) ) {

			wp_send_json_error( array(

				'message' => __( 'Keine Berechtigung fÃ¼r diese Aktion.', 'repro-ct-suite' )

			) );

		}



		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-repro-ct-suite-shortcode-presets-repository.php';

		$repository = new Repro_CT_Suite_Shortcode_Presets_Repository();



		$presets = $repository->get_all();



		wp_send_json_success( array(

			'presets' => $presets

		) );

	}



	/**

	 * AJAX Handler: Preset speichern

	 */

	public function ajax_save_preset(): void {

		check_ajax_referer( 'repro_ct_suite_presets', 'nonce' );



		if ( ! current_user_can( 'manage_options' ) ) {

			wp_send_json_error( array(

				'message' => __( 'Keine Berechtigung fÃ¼r diese Aktion.', 'repro-ct-suite' )

			) );

		}



		$preset_data = isset( $_POST['preset'] ) ? $_POST['preset'] : array();



		if ( empty( $preset_data['name'] ) ) {

			wp_send_json_error( array(

				'message' => __( 'Preset-Name ist erforderlich.', 'repro-ct-suite' )

			) );

		}



		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-repro-ct-suite-shortcode-presets-repository.php';

		$repository = new Repro_CT_Suite_Shortcode_Presets_Repository();



		// PrÃ¼fe ob Name bereits existiert

		if ( $repository->name_exists( $preset_data['name'] ) ) {

			wp_send_json_error( array(

				'message' => __( 'Ein Preset mit diesem Namen existiert bereits.', 'repro-ct-suite' )

			) );

		}



		$preset_id = $repository->save( $preset_data );



		if ( $preset_id === false ) {

			wp_send_json_error( array(

				'message' => __( 'Fehler beim Speichern des Presets.', 'repro-ct-suite' )

			) );

		}



		wp_send_json_success( array(

			'message'   => __( 'Preset erfolgreich gespeichert.', 'repro-ct-suite' ),

			'preset_id' => $preset_id

		) );

	}



	/**

	 * AJAX Handler: Preset aktualisieren

	 */

	public function ajax_update_preset(): void {

		check_ajax_referer( 'repro_ct_suite_presets', 'nonce' );



		if ( ! current_user_can( 'manage_options' ) ) {

			wp_send_json_error( array(

				'message' => __( 'Keine Berechtigung fÃ¼r diese Aktion.', 'repro-ct-suite' )

			) );

		}



		$preset_id = isset( $_POST['preset_id'] ) ? intval( $_POST['preset_id'] ) : 0;

		$preset_data = isset( $_POST['preset'] ) ? $_POST['preset'] : array();



		if ( ! $preset_id ) {

			wp_send_json_error( array(

				'message' => __( 'Preset-ID fehlt.', 'repro-ct-suite' )

			) );

		}



		if ( empty( $preset_data['name'] ) ) {

			wp_send_json_error( array(

				'message' => __( 'Preset-Name ist erforderlich.', 'repro-ct-suite' )

			) );

		}



		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-repro-ct-suite-shortcode-presets-repository.php';

		$repository = new Repro_CT_Suite_Shortcode_Presets_Repository();



		// PrÃ¼fe ob Name bereits existiert (auÃŸer bei diesem Preset)

		if ( $repository->name_exists( $preset_data['name'], $preset_id ) ) {

			wp_send_json_error( array(

				'message' => __( 'Ein anderes Preset mit diesem Namen existiert bereits.', 'repro-ct-suite' )

			) );

		}



		$success = $repository->update( $preset_id, $preset_data );



		if ( ! $success ) {

			wp_send_json_error( array(

				'message' => __( 'Fehler beim Aktualisieren des Presets.', 'repro-ct-suite' )

			) );

		}



		wp_send_json_success( array(

			'message' => __( 'Preset erfolgreich aktualisiert.', 'repro-ct-suite' )

		) );

	}



	/**

	 * AJAX Handler: Preset lÃ¶schen

	 */

	public function ajax_delete_preset(): void {

		check_ajax_referer( 'repro_ct_suite_presets', 'nonce' );



		if ( ! current_user_can( 'manage_options' ) ) {

			wp_send_json_error( array(

				'message' => __( 'Keine Berechtigung fÃ¼r diese Aktion.', 'repro-ct-suite' )

			) );

		}



		$preset_id = isset( $_POST['preset_id'] ) ? intval( $_POST['preset_id'] ) : 0;



		if ( ! $preset_id ) {

			wp_send_json_error( array(

				'message' => __( 'Preset-ID fehlt.', 'repro-ct-suite' )

			) );

		}



		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-repro-ct-suite-shortcode-presets-repository.php';

		$repository = new Repro_CT_Suite_Shortcode_Presets_Repository();



		$success = $repository->delete( $preset_id );



		if ( ! $success ) {

			wp_send_json_error( array(

				'message' => __( 'Fehler beim LÃ¶schen des Presets.', 'repro-ct-suite' )

			) );

		}



		wp_send_json_success( array(

			'message' => __( 'Preset erfolgreich gelÃ¶scht.', 'repro-ct-suite' )

		) );

	}



	/**

	 * AJAX Handler: Preset laden

	 */

	public function ajax_load_preset(): void {

		check_ajax_referer( 'repro_ct_suite_presets', 'nonce' );



		if ( ! current_user_can( 'manage_options' ) ) {

			wp_send_json_error( array(

				'message' => __( 'Keine Berechtigung fÃ¼r diese Aktion.', 'repro-ct-suite' )

			) );

		}



		$preset_id = isset( $_POST['preset_id'] ) ? intval( $_POST['preset_id'] ) : 0;



		if ( ! $preset_id ) {

			wp_send_json_error( array(

				'message' => __( 'Preset-ID fehlt.', 'repro-ct-suite' )

			) );

		}



		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-repro-ct-suite-shortcode-presets-repository.php';

		$repository = new Repro_CT_Suite_Shortcode_Presets_Repository();



		$preset = $repository->get_by_id( $preset_id );



		if ( ! $preset ) {

			wp_send_json_error( array(

				'message' => __( 'Preset nicht gefunden.', 'repro-ct-suite' )

			) );

		}



		wp_send_json_success( array(

			'preset' => $preset

		) );
	}

	/**
	 * Modern Shortcode Manager initialisieren
	 */
	private function init_modern_shortcode_manager(): void {
		// Include der Klasse
		require_once plugin_dir_path( __FILE__ ) . 'class-modern-shortcode-manager.php';
		
		// Instanz erstellen - die Klasse registriert sich selbst fÃ¼r AJAX
		new Repro_CT_Suite_Modern_Shortcode_Manager();
	}

	/**
	 * Entfernt den WordPress Admin Footer Text
	 *
	 * @return string|null
	 */
	public function remove_admin_footer_text( string $text ): string {
		// Nur auf Plugin-Seiten ausblenden
		$screen = get_current_screen();
		if ( $screen && strpos( $screen->id, 'repro-ct-suite' ) !== false ) {
			return '';
		}
		return $text;
	}

	/**
	 * Entfernt die WordPress Version im Footer
	 *
	 * @return string|null
	 */
	public function remove_update_footer( string $text ): string {
		// Nur auf Plugin-Seiten ausblenden
		$screen = get_current_screen();
		if ( $screen && strpos( $screen->id, 'repro-ct-suite' ) !== false ) {
			return '';
		}
		return $text;
	}

	/**
	 * Handle Debug AJAX requests
	 */
	public function handle_debug_ajax() {
		if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'repro_ct_suite_debug_nonce')) {
			wp_send_json_error('Security check failed');
		}

		if (!current_user_can('manage_options')) {
			wp_send_json_error('Insufficient permissions');
		}

		$action = sanitize_text_field($_POST['debug_action']);
		$table = sanitize_text_field($_POST['table']);

		global $wpdb;

		$allowed_tables = [
			'calendars' => $wpdb->prefix . 'rcts_calendars',
			'events' => $wpdb->prefix . 'rcts_events',
			'event_services' => $wpdb->prefix . 'rcts_event_services'
		];

		if (!array_key_exists($table, $allowed_tables)) {
			wp_send_json_error('Invalid table');
			return;
		}

		$table_name = $allowed_tables[$table];

		switch ($action) {
			case 'view_data':
				$this->handle_debug_view_data($table_name);
				break;
			case 'export_data':
				$this->handle_debug_export_data($table_name);
				break;
			case 'clear_table':
				$this->handle_debug_clear_table($table_name, $table);
				break;
			default:
				wp_send_json_error('Invalid action');
		}
	}

	/**
	 * Handle debug view data request
	 */
	private function handle_debug_view_data($table_name) {
		global $wpdb;

		try {
			$results = $wpdb->get_results("SELECT * FROM {$table_name} LIMIT 50", ARRAY_A);
			
			if (empty($results)) {
				wp_send_json_error('Keine Daten vorhanden');
				return;
			}

			$html = '<table style="width: 100%; border-collapse: collapse;">';
			$html .= '<thead><tr>';
			
			foreach (array_keys($results[0]) as $header) {
				$html .= '<th style="border: 1px solid #ddd; padding: 8px; text-align: left; background: #f5f5f5; font-weight: bold;">' . esc_html($header) . '</th>';
			}
			
			$html .= '</tr></thead><tbody>';
			
			foreach ($results as $row) {
				$html .= '<tr>';
				foreach ($row as $cell) {
					$html .= '<td style="border: 1px solid #ddd; padding: 8px;">' . esc_html(is_array($cell) ? json_encode($cell) : $cell) . '</td>';
				}
				$html .= '</tr>';
			}
			
			$html .= '</tbody></table>';

			wp_send_json_success(['html' => $html]);

		} catch (Exception $e) {
			wp_send_json_error('Fehler beim Laden: ' . $e->getMessage());
		}
	}

	/**
	 * Handle debug export data request
	 */
	private function handle_debug_export_data($table_name) {
		global $wpdb;

		try {
			$results = $wpdb->get_results("SELECT * FROM {$table_name}", ARRAY_A);
			
			if (empty($results)) {
				wp_send_json_error('Keine Daten zum Exportieren vorhanden');
				return;
			}

			$headers = array_keys($results[0]);
			$csv = implode(',', array_map(function($h) { return '"' . str_replace('"', '""', $h) . '"'; }, $headers)) . "\n";

			foreach ($results as $row) {
				$csv_row = [];
				foreach ($row as $value) {
					$value = str_replace('"', '""', $value);
					$csv_row[] = '"' . $value . '"';
				}
				$csv .= implode(',', $csv_row) . "\n";
			}

			wp_send_json_success(['csv' => $csv]);

		} catch (Exception $e) {
			wp_send_json_error('Fehler beim Export: ' . $e->getMessage());
		}
	}

	/**
	 * Handle debug clear table request
	 */
	private function handle_debug_clear_table($table_name, $table_key) {
		global $wpdb;

		try {
			$count_before = $wpdb->get_var("SELECT COUNT(*) FROM {$table_name}");
			
			if ($count_before == 0) {
				wp_send_json_error('Tabelle ist bereits leer');
				return;
			}

			if (class_exists('Repro_CT_Suite_Logger')) {
				Repro_CT_Suite_Logger::log("Debug: Tabelle '{$table_key}' wird geleert", 'warning');
			}

			$result = $wpdb->query("DELETE FROM {$table_name}");

			if ($result === false) {
				wp_send_json_error('Fehler beim LÃ¶schen der Daten: ' . $wpdb->last_error);
				return;
			}

			$count_after = $wpdb->get_var("SELECT COUNT(*) FROM {$table_name}");
			$deleted_rows = $count_before - $count_after;

			if (class_exists('Repro_CT_Suite_Logger')) {
				Repro_CT_Suite_Logger::log("Debug: Tabelle '{$table_key}' erfolgreich geleert - {$deleted_rows} EintrÃ¤ge gelÃ¶scht", 'success');
			}

			wp_send_json_success([
				'deleted_rows' => $deleted_rows,
				'message' => sprintf('Erfolgreich %d EintrÃ¤ge aus Tabelle %s gelÃ¶scht', $deleted_rows, $table_key)
			]);

		} catch (Exception $e) {
			if (class_exists('Repro_CT_Suite_Logger')) {
				Repro_CT_Suite_Logger::log("Debug: Fehler beim Leeren der Tabelle '{$table_key}': " . $e->getMessage(), 'error');
			}
			
			wp_send_json_error('Fehler beim LÃ¶schen: ' . $e->getMessage());
		}
	}

	/**
	 * AJAX Handler: TabelleneintrÃ¤ge abrufen
	 *
	 * @since 0.9.5.3
	 */
	public function ajax_get_table_entries(): void {
		// Nonce-PrÃ¼fung
		check_ajax_referer( 'repro_ct_suite_admin', 'nonce' );

		// BerechtigungsprÃ¼fung
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array(
				'message' => __( 'Keine Berechtigung fÃ¼r diese Aktion.', 'repro-ct-suite' )
			) );
		}

		global $wpdb;

		$table_key = isset( $_POST['table'] ) ? sanitize_text_field( $_POST['table'] ) : '';

		if ( empty( $table_key ) ) {
			wp_send_json_error( array( 'message' => __( 'Keine Tabelle angegeben.', 'repro-ct-suite' ) ) );
		}

		// Tabellen-Mapping
		$tables_map = array(
			'rcts_calendars' => array(
				'table' => $wpdb->prefix . 'rcts_calendars',
				'columns' => array( 'id', 'ct_id', 'name', 'created_at' )
			),
			'rcts_events' => array(
				'table' => $wpdb->prefix . 'rcts_events',
				'columns' => array( 'id', 'ct_id', 'name', 'start_date', 'calendar_id' )
			),
			'rcts_event_services' => array(
				'table' => $wpdb->prefix . 'rcts_event_services',
				'columns' => array( 'id', 'ct_id', 'name', 'service_group_id' )
			),
			'rcts_schedule' => array(
				'table' => $wpdb->prefix . 'rcts_schedule',
				'columns' => array( 'id', 'ct_id', 'name', 'start_date', 'event_id' )
			),
		);

		if ( ! isset( $tables_map[ $table_key ] ) ) {
			wp_send_json_error( array( 'message' => __( 'UngÃ¼ltige Tabelle.', 'repro-ct-suite' ) ) );
		}

		$table_info = $tables_map[ $table_key ];
		$table_name = $table_info['table'];
		$columns = implode( ', ', $table_info['columns'] );

		// EintrÃ¤ge abrufen (limitiert auf 100)
		$results = $wpdb->get_results( "SELECT {$columns} FROM {$table_name} ORDER BY id DESC LIMIT 100", ARRAY_A );

		if ( $wpdb->last_error ) {
			wp_send_json_error( array( 'message' => 'Datenbankfehler: ' . $wpdb->last_error ) );
		}

		wp_send_json_success( array(
			'entries' => $results,
			'columns' => $table_info['columns'],
			'total' => count( $results )
		) );
	}

	/**
	 * AJAX Handler: Einzelnen Eintrag lÃ¶schen
	 *
	 * @since 0.9.5.3
	 */
	public function ajax_delete_single_entry(): void {
		// Nonce-PrÃ¼fung
		check_ajax_referer( 'repro_ct_suite_admin', 'nonce' );

		// BerechtigungsprÃ¼fung
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array(
				'message' => __( 'Keine Berechtigung fÃ¼r diese Aktion.', 'repro-ct-suite' )
			) );
		}

		global $wpdb;

		$table_key = isset( $_POST['table'] ) ? sanitize_text_field( $_POST['table'] ) : '';
		$entry_id = isset( $_POST['entry_id'] ) ? absint( $_POST['entry_id'] ) : 0;

		if ( empty( $table_key ) || $entry_id === 0 ) {
			wp_send_json_error( array( 'message' => __( 'UngÃ¼ltige Parameter.', 'repro-ct-suite' ) ) );
		}

		// Tabellen-Mapping
		$tables_map = array(
			'rcts_calendars' => $wpdb->prefix . 'rcts_calendars',
			'rcts_events' => $wpdb->prefix . 'rcts_events',
			'rcts_event_services' => $wpdb->prefix . 'rcts_event_services',
			'rcts_schedule' => $wpdb->prefix . 'rcts_schedule',
		);

		if ( ! isset( $tables_map[ $table_key ] ) ) {
			wp_send_json_error( array( 'message' => __( 'UngÃ¼ltige Tabelle.', 'repro-ct-suite' ) ) );
		}

		$table_name = $tables_map[ $table_key ];

		// PrÃ¼fen ob Eintrag existiert
		$exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table_name} WHERE id = %d", $entry_id ) );

		if ( ! $exists ) {
			wp_send_json_error( array( 'message' => __( 'Eintrag nicht gefunden.', 'repro-ct-suite' ) ) );
		}

		// LÃ¶schen
		$result = $wpdb->delete( $table_name, array( 'id' => $entry_id ), array( '%d' ) );

		if ( $result === false ) {
			wp_send_json_error( array( 'message' => 'Datenbankfehler: ' . $wpdb->last_error ) );
		}

		if ( class_exists( 'Repro_CT_Suite_Logger' ) ) {
			Repro_CT_Suite_Logger::log( "Debug: Eintrag #{$entry_id} aus Tabelle '{$table_key}' gelÃ¶scht", 'info' );
		}

		wp_send_json_success( array(
			'message' => sprintf( __( 'Eintrag #%d erfolgreich gelÃ¶scht.', 'repro-ct-suite' ), $entry_id )
		) );
	}

}































