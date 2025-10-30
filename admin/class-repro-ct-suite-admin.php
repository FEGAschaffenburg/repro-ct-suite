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
		
		// Update-Check Handler
		add_action( 'admin_init', array( $this, 'check_manual_update_request' ) );
		
		// AJAX Handlers
		add_action( 'wp_ajax_repro_ct_suite_clear_tables', array( $this, 'ajax_clear_tables' ) );
		add_action( 'wp_ajax_repro_ct_suite_clear_single_table', array( $this, 'ajax_clear_single_table' ) );
		add_action( 'wp_ajax_repro_ct_suite_run_migrations', array( $this, 'ajax_run_migrations' ) );
		add_action( 'wp_ajax_repro_ct_suite_clear_log', array( $this, 'ajax_clear_log' ) );
		add_action( 'wp_ajax_repro_ct_suite_reset_credentials', array( $this, 'ajax_reset_credentials' ) );
		add_action( 'wp_ajax_repro_ct_suite_full_reset', array( $this, 'ajax_full_reset' ) );
		add_action( 'wp_ajax_repro_ct_suite_fix_calendar_ids', array( $this, 'ajax_fix_calendar_ids' ) );
		add_action( 'wp_ajax_repro_ct_suite_delete_event', array( $this, 'ajax_delete_event' ) );
		add_action( 'wp_ajax_repro_ct_suite_delete_appointment', array( $this, 'ajax_delete_appointment' ) );
		add_action( 'wp_ajax_repro_ct_suite_update_event', array( $this, 'ajax_update_event' ) );
		add_action( 'wp_ajax_repro_ct_suite_update_appointment', array( $this, 'ajax_update_appointment' ) );
	}

	/**
	 * Prüft ob ChurchTools-Verbindung konfiguriert ist
	 *
	 * @return bool
	 */
	private function has_connection() {
		$tenant   = get_option( 'repro_ct_suite_ct_tenant', '' );
		$username = get_option( 'repro_ct_suite_ct_username', '' );
		$password = get_option( 'repro_ct_suite_ct_password', '' );
		return ! empty( $tenant ) && ! empty( $username ) && ! empty( $password );
	}

	/**
	 * Prüft ob mindestens ein Kalender ausgewählt ist
	 *
	 * @return bool
	 */
	private function has_calendars_selected() {
		global $wpdb;
		$table = $wpdb->prefix . 'rcts_calendars';
		
		// Prüfe ob Tabelle existiert
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
	 * Funktioniert unabhängig von WP_DEBUG - aktiviert sich selbst wenn nötig.
	 *
	 * @param string $message Die Log-Nachricht
	 * @param string $level   Log-Level: 'info', 'error', 'warning', 'success'
	 */
	private function debug_log( $message, $level = 'info' ) {
		// Stelle sicher, dass Debug-Log aktiviert ist
		if ( ! defined( 'WP_DEBUG_LOG' ) ) {
			// Temporär aktivieren für diesen Request
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
				$prefix .= '❌ ERROR: ';
				break;
			case 'warning':
				$prefix .= '⚠️  WARNING: ';
				break;
			case 'success':
				$prefix .= '✅ SUCCESS: ';
				break;
			default:
				$prefix .= 'ℹ️  INFO: ';
		}
		
		error_log( $prefix . $message );
	}

	/**
	 * Behandelt manuelle Update-Check-Anfragen
	 */
	public function check_manual_update_request() {
		if ( isset( $_GET['repro_ct_suite_check_update'] ) && current_user_can( 'update_plugins' ) ) {
			// Lösche alle Update-bezogenen Transients
			delete_transient( 'repro_ct_suite_release_info' );
			delete_site_transient( 'update_plugins' );
			
			// Redirect zurück zur Plugins-Seite
			wp_safe_redirect( admin_url( 'plugins.php?repro_ct_suite_update_checked=1' ) );
			exit;
		}
		
		// Zeige Erfolgs-Notice
		if ( isset( $_GET['repro_ct_suite_update_checked'] ) ) {
			add_action( 'admin_notices', function() {
				echo '<div class="notice notice-success is-dismissible"><p>';
				esc_html_e( 'Update-Check durchgeführt. Bitte Seite neu laden um Updates zu sehen.', 'repro-ct-suite' );
				echo '</p></div>';
			} );
		}
	}

	/**
	 * Register the stylesheets for the admin area.
	 */
	public function enqueue_styles() {
		wp_enqueue_style(
			$this->plugin_name,
			plugin_dir_url( __FILE__ ) . 'css/repro-ct-suite-admin.css',
			array(),
			null,
			'all'
		);
	}

	/**
	 * Register the JavaScript for the admin area.
	 */
	public function enqueue_scripts() {
		wp_enqueue_script(
			$this->plugin_name,
			plugin_dir_url( __FILE__ ) . 'js/repro-ct-suite-admin.js',
			array( 'jquery' ),
			null,
			false
		);

		// Debug-Seite JavaScript
		$screen = get_current_screen();
		if ( $screen && strpos( $screen->id, 'repro-ct-suite-debug' ) !== false ) {
			wp_enqueue_script(
				$this->plugin_name . '-debug',
				plugin_dir_url( __FILE__ ) . 'js/repro-ct-suite-debug.js',
				array( 'jquery' ),
				null,
				false
			);
		}

		// Localize script für AJAX
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
	public function add_admin_menu() {
		// Hauptmenü (Dashboard)
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

		// Submenu: Terminkalender (immer sichtbar)
		add_submenu_page(
			'repro-ct-suite',
			__( 'Terminkalender', 'repro-ct-suite' ),
			__( 'Terminkalender', 'repro-ct-suite' ),
			'manage_options',
			'repro-ct-suite-events',
			array( $this, 'display_events_page' )
		);

		// Submenu: Terminübersicht (kombinierte Liste)
		add_submenu_page(
			'repro-ct-suite',
			__( 'Terminübersicht', 'repro-ct-suite' ),
			__( 'Terminübersicht', 'repro-ct-suite' ),
			'manage_options',
			'repro-ct-suite-schedule',
			array( $this, 'display_schedule_page' )
		);

		// Submenu: Update-Info (immer sichtbar)
		add_submenu_page(
			'repro-ct-suite',
			__( 'Update-Info', 'repro-ct-suite' ),
			__( 'Update-Info', 'repro-ct-suite' ),
			'manage_options',
			'repro-ct-suite-update',
			array( $this, 'display_update_page' )
		);

		// Submenu: Debug (immer sichtbar für Admins)
		add_submenu_page(
			'repro-ct-suite',
			__( 'Debug', 'repro-ct-suite' ),
			__( 'Debug', 'repro-ct-suite' ),
			'manage_options',
			'repro-ct-suite-debug',
			array( $this, 'display_debug_page' )
		);
	}

	/**
	 * Display the admin page.
	 */
	public function display_admin_page() {
		include_once plugin_dir_path( __FILE__ ) . 'views/admin-display.php';
	}

	/**
	 * Display the update info page.
	 */
	public function display_update_page() {
		include_once plugin_dir_path( __FILE__ ) . 'views/admin-update.php';
	}

	/**
	 * Display the events (Terminkalender) page.
	 */
	public function display_events_page() {
		include_once plugin_dir_path( __FILE__ ) . 'views/admin-events.php';
	}

	/**
	 * Display the schedule (Terminübersicht) page.
	 */
	public function display_schedule_page() {
		include_once plugin_dir_path( __FILE__ ) . 'views/admin-schedule.php';
	}

	/**
	 * Display the debug page.
	 */
	public function display_debug_page() {
		include_once plugin_dir_path( __FILE__ ) . 'views/admin-debug.php';
	}

	/**
	 * Handle test connection request.
	 */
	public function handle_test_connection() {
		if ( ! isset( $_GET['page'] ) || $_GET['page'] !== 'repro-ct-suite' ) {
			return;
		}

		if ( ! isset( $_GET['test_connection'] ) || ! check_admin_referer( 'repro_ct_suite_test_connection' ) ) {
			return;
		}

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-repro-ct-suite-crypto.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-repro-ct-suite-ct-client.php';

		$test_tenant       = get_option( 'repro_ct_suite_ct_tenant', '' );
		$test_username     = get_option( 'repro_ct_suite_ct_username', '' );
		$test_password_enc = get_option( 'repro_ct_suite_ct_password', '' );
		$test_password     = Repro_CT_Suite_Crypto::decrypt( $test_password_enc );

		if ( empty( $test_tenant ) || empty( $test_username ) || empty( $test_password ) ) {
			set_transient( 'repro_ct_suite_test_result', new WP_Error( 'missing_credentials', __( 'Bitte alle Felder ausfüllen.', 'repro-ct-suite' ) ), 30 );
		} else {
			$client = new Repro_CT_Suite_CT_Client( $test_tenant, $test_username, $test_password );
			$login  = $client->login();
			if ( is_wp_error( $login ) ) {
				set_transient( 'repro_ct_suite_test_result', $login, 30 );
			} else {
				$whoami = $client->whoami();
				set_transient( 'repro_ct_suite_test_result', is_wp_error( $whoami ) ? $whoami : true, 30 );
			}
		}

		// Redirect zurück zum Settings-Tab ohne test_connection Parameter
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
	public function handle_calendar_selection() {
		// Kalenderauswahl speichern
		if ( isset( $_POST['repro_ct_suite_action'] ) && $_POST['repro_ct_suite_action'] === 'save_calendar_selection' ) {
			if ( ! check_admin_referer( 'repro_ct_suite_calendar_selection', 'repro_ct_suite_calendar_selection_nonce' ) ) {
				wp_die( __( 'Sicherheitsprüfung fehlgeschlagen.', 'repro-ct-suite' ) );
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
				wp_die( __( 'Sicherheitsprüfung fehlgeschlagen.', 'repro-ct-suite' ) );
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
			wp_die( __( 'Sicherheitsprüfung fehlgeschlagen.', 'repro-ct-suite' ) );
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
	public function register_settings() {
		register_setting(
			'repro_ct_suite',
			'repro_ct_suite_auto_update',
			array(
				'type'              => 'boolean',
				'description'       => __( 'Automatische Updates für Repro CT-Suite aktivieren', 'repro-ct-suite' ),
				'sanitize_callback' => function ( $value ) { return (int) ( ! empty( $value ) ); },
				'default'           => 0,
			)
		);

		// ChurchTools Einstellungen: Tenant, Benutzername, Passwort (verschlüsselt)
		register_setting(
			'repro_ct_suite',
			'repro_ct_suite_ct_tenant',
			array(
				'type'              => 'string',
				'description'       => __( 'ChurchTools Tenant (z.B. "gemeinde" für gemeinde.church.tools)', 'repro-ct-suite' ),
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
				'description'       => __( 'ChurchTools Passwort (wird verschlüsselt gespeichert)', 'repro-ct-suite' ),
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

	}

	/**
	 * AJAX Handler: Kalender synchronisieren
	 *
	 * Ruft Kalender von ChurchTools ab und speichert sie in der Datenbank.
	 * Behält die Benutzer-Auswahl (is_selected) bei Updates bei.
	 *
	 * @since    0.3.0
	 */
	public function ajax_sync_calendars() {
		// Nonce-Prüfung
		check_ajax_referer( 'repro_ct_suite_admin', 'nonce' );

		// Berechtigungsprüfung
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array(
				'message' => __( 'Keine Berechtigung für diese Aktion.', 'repro-ct-suite' )
			) );
		}

		// Fehlerbehandlung aktivieren
		error_log( '[REPRO CT-SUITE] AJAX Handler gestartet: ajax_sync_calendars' );

		try {
			// Dependencies laden (Logger zuerst)
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-repro-ct-suite-logger.php';
			
			error_log( '[REPRO CT-SUITE] Logger geladen' );
			
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-repro-ct-suite-ct-client.php';
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/repositories/class-repro-ct-suite-repository-base.php';
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/repositories/class-repro-ct-suite-calendars-repository.php';
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/services/class-repro-ct-suite-calendar-sync-service.php';
			
			error_log( '[REPRO CT-SUITE] Alle Dependencies geladen' );

		} catch ( Exception $e ) {
			error_log( '[REPRO CT-SUITE] FEHLER beim Laden der Dependencies: ' . $e->getMessage() );
			error_log( '[REPRO CT-SUITE] Stack: ' . $e->getTraceAsString() );
			wp_send_json_error( array(
				'message' => 'Fehler beim Laden: ' . $e->getMessage()
			) );
			return;
		} catch ( Error $e ) {
			error_log( '[REPRO CT-SUITE] PHP ERROR beim Laden: ' . $e->getMessage() );
			error_log( '[REPRO CT-SUITE] File: ' . $e->getFile() . ' Line: ' . $e->getLine() );
			wp_send_json_error( array(
				'message' => 'PHP Error: ' . $e->getMessage()
			) );
			return;
		}

		try {
			// Service instanziieren
			error_log( '[REPRO CT-SUITE] Instanziiere CT_Client...' );
			
			// Credentials aus WordPress Optionen laden
			$tenant = get_option( 'repro_ct_suite_ct_tenant', '' );
			$username = get_option( 'repro_ct_suite_ct_username', '' );
			$encrypted_password = get_option( 'repro_ct_suite_ct_password', '' );
			
			// Passwort entschlüsseln
			$password = Repro_CT_Suite_Crypto::decrypt( $encrypted_password );
			
			// CT_Client mit Credentials instanziieren
			$ct_client = new Repro_CT_Suite_CT_Client( $tenant, $username, $password );
			
			error_log( '[REPRO CT-SUITE] Instanziiere Calendars_Repository...' );
			$calendars_repo = new Repro_CT_Suite_Calendars_Repository();
			
			error_log( '[REPRO CT-SUITE] Instanziiere Calendar_Sync_Service...' );
			$sync_service = new Repro_CT_Suite_Calendar_Sync_Service( $ct_client, $calendars_repo );

			// DEBUG: Log Request-Details ins WordPress Debug-Log
			$debug_info = array(
				'tenant' => $tenant,
				'url' => 'https://' . $tenant . '.church.tools/api/calendars',
				'timestamp' => current_time( 'mysql' )
			);
			
			error_log( '[REPRO CT-SUITE] Rufe Logger::header() auf...' );
			Repro_CT_Suite_Logger::header( 'KALENDER-SYNCHRONISATION GESTARTET' );
			Repro_CT_Suite_Logger::log( 'Zeitpunkt: ' . current_time( 'mysql' ) );
			Repro_CT_Suite_Logger::log( 'Tenant: ' . $tenant );
			Repro_CT_Suite_Logger::log( 'API-URL: ' . $debug_info['url'] );
			Repro_CT_Suite_Logger::separator();

			// Synchronisation durchführen
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
			Repro_CT_Suite_Logger::log( 'Neu eingefügt: ' . ( isset( $result['inserted'] ) ? $result['inserted'] : '0' ), 'success' );
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
	 * Synchronisiert Events und Appointments von ausgewählten Kalendern.
	 *
	 * @since    0.3.0
	 */
	public function ajax_sync_appointments() {
		// Nonce-Prüfung
		check_ajax_referer( 'repro_ct_suite_admin', 'nonce' );

		// Berechtigungsprüfung
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array(
				'message' => __( 'Keine Berechtigung für diese Aktion.', 'repro-ct-suite' )
			) );
		}

		// Dependencies laden (mit robuster Fehlerbehandlung)
		try {
			// Logger zuerst laden, damit wir früh loggen können
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-repro-ct-suite-logger.php';
			Repro_CT_Suite_Logger::header( 'AJAX: APPOINTMENTS-SYNC HANDLER START (Terminvorlagen -> Events)' );
			Repro_CT_Suite_Logger::log( 'Lade Dependencies...' );

			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-repro-ct-suite-ct-client.php';
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-repro-ct-suite-crypto.php';
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/repositories/class-repro-ct-suite-repository-base.php';
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/repositories/class-repro-ct-suite-calendars-repository.php';
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/repositories/class-repro-ct-suite-events-repository.php';
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/repositories/class-repro-ct-suite-appointments-repository.php';
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/repositories/class-repro-ct-suite-schedule-repository.php';
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/services/class-repro-ct-suite-events-sync-service.php';
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/services/class-repro-ct-suite-appointments-sync-service.php';

			Repro_CT_Suite_Logger::log( 'Dependencies geladen.' );
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

			// Nur ausgewählte Kalender synchronisieren
			$selected_calendar_ids = $calendars_repo->get_selected_ids();
			Repro_CT_Suite_Logger::log( 'Ausgewählte Kalender (lokale IDs): ' . ( $selected_calendar_ids ? implode( ',', $selected_calendar_ids ) : '[keine]' ) );

			if ( empty( $selected_calendar_ids ) ) {
				wp_send_json_error( array(
					'message' => __( 'Keine Kalender ausgewählt. Bitte wählen Sie mindestens einen Kalender aus.', 'repro-ct-suite' )
				) );
			}

			// Externe Calendar-IDs für Events-Filterung holen
			$selected_external_calendar_ids = array();
			foreach ( $selected_calendar_ids as $local_id ) {
				$calendar = $calendars_repo->get_by_id( $local_id );
				if ( $calendar && ! empty( $calendar->external_id ) ) {
					$selected_external_calendar_ids[] = (string) $calendar->external_id;
				}
			}
			Repro_CT_Suite_Logger::log( 'Ausgewählte Kalender (externe IDs): ' . ( $selected_external_calendar_ids ? implode( ',', $selected_external_calendar_ids ) : '[keine]' ) );

			// Zeitraum bestimmen (aus den gespeicherten Optionen)
			$sync_from_days = get_option( 'repro_ct_suite_sync_from_days', -7 );
			$sync_to_days   = get_option( 'repro_ct_suite_sync_to_days', 90 );
			$from = date( 'Y-m-d', current_time( 'timestamp' ) + ( (int) $sync_from_days * DAY_IN_SECONDS ) );
			$to   = date( 'Y-m-d', current_time( 'timestamp' ) + ( (int) $sync_to_days * DAY_IN_SECONDS ) );
			Repro_CT_Suite_Logger::log( 'Zeitraum: von ' . $from . ' bis ' . $to );

			// STRATEGIE: 
			// 1. Zuerst Events aus /events synchronisieren (enthält alle ChurchTools-Events)
			//    WICHTIG: Mit calendar_ids filtern, damit nur Events ausgewählter Kalender importiert werden
			// 2. Dann Appointments - ABER nur die, deren appointment_id noch NICHT in rcts_events vorkommt
			//    (d.h. Appointments OHNE zugeordnetes Event)
			// -> Verhindert Duplikate, da Events aus /events Vorrang haben

			// 1) Events synchronisieren (nur von ausgewählten Kalendern)
			Repro_CT_Suite_Logger::log( 'SCHRITT 1: Events synchronisieren (nur ausgewählte Kalender)...' );
			$events_service = new Repro_CT_Suite_Events_Sync_Service( $ct_client, $events_repo, $calendars_repo, $schedule_repo );
			$events_result = $events_service->sync_events( array(
				'from'         => $from,
				'to'           => $to,
				'calendar_ids' => $selected_external_calendar_ids, // Externe ChurchTools Calendar-IDs
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
	 * AJAX: Leert alle Plugin-Tabellen (nur für Admins, Debug-Funktion)
	 */
	public function ajax_clear_tables() {
		// Nonce-Prüfung
		check_ajax_referer( 'repro_ct_suite_admin', 'nonce' );

		// Berechtigungsprüfung
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array(
				'message' => __( 'Keine Berechtigung für diese Aktion.', 'repro-ct-suite' )
			) );
		}

		global $wpdb;
		
		// Tabellen leeren (TRUNCATE löscht alle Daten und resettet AUTO_INCREMENT)
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
			// Prüfe, ob Tabelle existiert
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
	public function ajax_clear_single_table() {
		// Nonce-Prüfung
		check_ajax_referer( 'repro_ct_suite_admin', 'nonce' );

		// Berechtigungsprüfung
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array(
				'message' => __( 'Keine Berechtigung für diese Aktion.', 'repro-ct-suite' )
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
				'message' => __( 'Ungültige Tabelle.', 'repro-ct-suite' )
			) );
		}

		$table = $table_mapping[ $table_key ];

		// Prüfe, ob Tabelle existiert
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
	 * AJAX: Führt DB-Migrationen manuell aus
	 */
	public function ajax_run_migrations() {
		// Nonce-Prüfung
		check_ajax_referer( 'repro_ct_suite_admin', 'nonce' );

		// Berechtigungsprüfung
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array(
				'message' => __( 'Keine Berechtigung für diese Aktion.', 'repro-ct-suite' )
			) );
		}

		// Migrations-Klasse laden
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-repro-ct-suite-migrations.php';

		$old_version = get_option( 'repro_ct_suite_db_version', '0' );

		try {
			// Migration ausführen
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
	public function ajax_clear_log() {
		// Nonce-Prüfung
		check_ajax_referer( 'repro_ct_suite_admin', 'nonce' );

		// Berechtigungsprüfung
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array(
				'message' => __( 'Keine Berechtigung für diese Aktion.', 'repro-ct-suite' )
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
	 * AJAX: Löscht alle ChurchTools-Zugangsdaten
	 */
	/**
	 * AJAX Handler: Zugangsdaten zurücksetzen
	 *
	 * @since 0.3.5.3
	 */
	public function ajax_reset_credentials() {
		// Nonce-Prüfung
		check_ajax_referer( 'repro_ct_suite_admin', 'nonce' );

		// Berechtigungsprüfung
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array(
				'message' => __( 'Keine Berechtigung für diese Aktion.', 'repro-ct-suite' )
			) );
		}

		// Zugangsdaten löschen
		delete_option( 'repro_ct_suite_ct_tenant' );
		delete_option( 'repro_ct_suite_ct_username' );
		delete_option( 'repro_ct_suite_ct_password' );
		delete_option( 'repro_ct_suite_ct_session' );
		delete_option( 'repro_ct_suite_connection_verified' );

		wp_send_json_success( array(
			'message' => __( 'Zugangsdaten wurden erfolgreich gelöscht.', 'repro-ct-suite' ),
			'ask_full_reset' => true
		) );
	}

	/**
	 * AJAX Handler: Vollständiger Reset (Zugangsdaten + alle Daten + Einstellungen)
	 *
	 * @since 0.3.5.6
	 */
	public function ajax_full_reset() {
		// Nonce-Prüfung
		check_ajax_referer( 'repro_ct_suite_admin', 'nonce' );

		// Berechtigungsprüfung
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array(
				'message' => __( 'Keine Berechtigung für diese Aktion.', 'repro-ct-suite' )
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

		// Alle Plugin-Optionen löschen
		delete_option( 'repro_ct_suite_ct_tenant' );
		delete_option( 'repro_ct_suite_ct_username' );
		delete_option( 'repro_ct_suite_ct_password' );
		delete_option( 'repro_ct_suite_ct_session' );
		delete_option( 'repro_ct_suite_connection_verified' );
		delete_option( 'repro_ct_suite_last_calendar_sync' );
		delete_option( 'repro_ct_suite_last_event_sync' );
		delete_option( 'repro_ct_suite_last_appointment_sync' );

		wp_send_json_success( array(
			'message' => __( 'Vollständiger Reset durchgeführt. Alle Zugangsdaten, Einstellungen und Daten wurden gelöscht.', 'repro-ct-suite' )
		) );
	}

	/**
	 * AJAX Handler: Calendar-IDs aus raw_payload extrahieren und korrigieren
	 *
	 * @since 0.3.6.0
	 */
	public function ajax_fix_calendar_ids() {
		// Nonce-Prüfung
		check_ajax_referer( 'repro_ct_suite_admin', 'nonce' );

		// Berechtigungsprüfung
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array(
				'message' => __( 'Keine Berechtigung für diese Aktion.', 'repro-ct-suite' )
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
	 * AJAX Handler: Einzelnes Event löschen
	 *
	 * @since 0.3.6.1
	 */
	public function ajax_delete_event() {
		check_ajax_referer( 'repro_ct_suite_admin', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array(
				'message' => __( 'Keine Berechtigung für diese Aktion.', 'repro-ct-suite' )
			) );
		}

		$event_id = isset( $_POST['event_id'] ) ? (int) $_POST['event_id'] : 0;

		if ( $event_id <= 0 ) {
			wp_send_json_error( array(
				'message' => __( 'Ungültige Event-ID.', 'repro-ct-suite' )
			) );
		}

		require_once REPRO_CT_SUITE_PATH . 'includes/repositories/class-repro-ct-suite-repository-base.php';
		require_once REPRO_CT_SUITE_PATH . 'includes/repositories/class-repro-ct-suite-events-repository.php';

		$events_repo = new Repro_CT_Suite_Events_Repository();

		// Prüfen ob Event existiert
		if ( ! $events_repo->exists( $event_id ) ) {
			wp_send_json_error( array(
				'message' => __( 'Event nicht gefunden.', 'repro-ct-suite' )
			) );
		}

		// Event löschen
		$result = $events_repo->delete_by_id( $event_id );

		if ( $result === false ) {
			wp_send_json_error( array(
				'message' => __( 'Fehler beim Löschen des Events.', 'repro-ct-suite' )
			) );
		}

		wp_send_json_success( array(
			'message' => __( 'Event erfolgreich gelöscht.', 'repro-ct-suite' )
		) );
	}

	/**
	 * AJAX Handler: Einzelnen Appointment löschen
	 *
	 * @since 0.3.6.1
	 */
	public function ajax_delete_appointment() {
		check_ajax_referer( 'repro_ct_suite_admin', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array(
				'message' => __( 'Keine Berechtigung für diese Aktion.', 'repro-ct-suite' )
			) );
		}

		$appointment_id = isset( $_POST['appointment_id'] ) ? (int) $_POST['appointment_id'] : 0;

		if ( $appointment_id <= 0 ) {
			wp_send_json_error( array(
				'message' => __( 'Ungültige Appointment-ID.', 'repro-ct-suite' )
			) );
		}

		require_once REPRO_CT_SUITE_PATH . 'includes/repositories/class-repro-ct-suite-repository-base.php';
		require_once REPRO_CT_SUITE_PATH . 'includes/repositories/class-repro-ct-suite-appointments-repository.php';

		$appointments_repo = new Repro_CT_Suite_Appointments_Repository();

		// Prüfen ob Appointment existiert
		if ( ! $appointments_repo->exists( $appointment_id ) ) {
			wp_send_json_error( array(
				'message' => __( 'Appointment nicht gefunden.', 'repro-ct-suite' )
			) );
		}

		// Appointment löschen
		$result = $appointments_repo->delete_by_id( $appointment_id );

		if ( $result === false ) {
			wp_send_json_error( array(
				'message' => __( 'Fehler beim Löschen des Appointments.', 'repro-ct-suite' )
			) );
		}

		wp_send_json_success( array(
			'message' => __( 'Appointment erfolgreich gelöscht.', 'repro-ct-suite' )
		) );
	}

	/**
	 * AJAX Handler: Einzelnes Event aktualisieren
	 *
	 * @since 0.3.6.1
	 */
	public function ajax_update_event() {
		check_ajax_referer( 'repro_ct_suite_admin', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array(
				'message' => __( 'Keine Berechtigung für diese Aktion.', 'repro-ct-suite' )
			) );
		}

		$event_id = isset( $_POST['event_id'] ) ? (int) $_POST['event_id'] : 0;

		if ( $event_id <= 0 ) {
			wp_send_json_error( array(
				'message' => __( 'Ungültige Event-ID.', 'repro-ct-suite' )
			) );
		}

		require_once REPRO_CT_SUITE_PATH . 'includes/repositories/class-repro-ct-suite-repository-base.php';
		require_once REPRO_CT_SUITE_PATH . 'includes/repositories/class-repro-ct-suite-events-repository.php';

		$events_repo = new Repro_CT_Suite_Events_Repository();

		// Prüfen ob Event existiert
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
	 * AJAX Handler: Einzelnen Appointment aktualisieren
	 *
	 * @since 0.3.6.1
	 */
	public function ajax_update_appointment() {
		check_ajax_referer( 'repro_ct_suite_admin', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array(
				'message' => __( 'Keine Berechtigung für diese Aktion.', 'repro-ct-suite' )
			) );
		}

		$appointment_id = isset( $_POST['appointment_id'] ) ? (int) $_POST['appointment_id'] : 0;

		if ( $appointment_id <= 0 ) {
			wp_send_json_error( array(
				'message' => __( 'Ungültige Appointment-ID.', 'repro-ct-suite' )
			) );
		}

		require_once REPRO_CT_SUITE_PATH . 'includes/repositories/class-repro-ct-suite-repository-base.php';
		require_once REPRO_CT_SUITE_PATH . 'includes/repositories/class-repro-ct-suite-appointments-repository.php';

		$appointments_repo = new Repro_CT_Suite_Appointments_Repository();

		// Prüfen ob Appointment existiert
		if ( ! $appointments_repo->exists( $appointment_id ) ) {
			wp_send_json_error( array(
				'message' => __( 'Appointment nicht gefunden.', 'repro-ct-suite' )
			) );
		}

		// Erlaubte Felder zum Aktualisieren
		$allowed_fields = array( 'title', 'description', 'start_datetime', 'end_datetime', 'is_all_day' );
		$update_data = array();

		foreach ( $allowed_fields as $field ) {
			if ( isset( $_POST[ $field ] ) ) {
				if ( $field === 'is_all_day' ) {
					$update_data[ $field ] = (int) $_POST[ $field ];
				} else {
					$update_data[ $field ] = sanitize_text_field( wp_unslash( $_POST[ $field ] ) );
				}
			}
		}

		if ( empty( $update_data ) ) {
			wp_send_json_error( array(
				'message' => __( 'Keine Daten zum Aktualisieren vorhanden.', 'repro-ct-suite' )
			) );
		}

		// Appointment aktualisieren
		$result = $appointments_repo->update_by_id( $appointment_id, $update_data );

		if ( $result === false ) {
			wp_send_json_error( array(
				'message' => __( 'Fehler beim Aktualisieren des Appointments.', 'repro-ct-suite' )
			) );
		}

		wp_send_json_success( array(
			'message' => __( 'Appointment erfolgreich aktualisiert.', 'repro-ct-suite' ),
			'updated_fields' => array_keys( $update_data )
		) );
	}
}

