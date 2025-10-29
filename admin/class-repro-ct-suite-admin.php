<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @package    Repro_CT_Suite
 * @subpackage Repro_CT_Suite/admin
 */

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
		add_menu_page(
			__( 'Repro CT-Suite', 'repro-ct-suite' ),
			__( 'Repro CT-Suite', 'repro-ct-suite' ),
			'manage_options',
			'repro-ct-suite',
			array( $this, 'display_admin_page' ),
			'dashicons-admin-generic',
			30
		);

		add_submenu_page(
			'repro-ct-suite',
			__( 'Einstellungen', 'repro-ct-suite' ),
			__( 'Einstellungen', 'repro-ct-suite' ),
			'manage_options',
			'repro-ct-suite',
			array( $this, 'display_admin_page' )
		);

		add_submenu_page(
			'repro-ct-suite',
			__( 'Termine', 'repro-ct-suite' ),
			__( 'Termine', 'repro-ct-suite' ),
			'manage_options',
			'repro-ct-suite-appointments',
			array( $this, 'display_appointments_page' )
		);

		add_submenu_page(
			'repro-ct-suite',
			__( 'Update-Info', 'repro-ct-suite' ),
			__( 'Update-Info', 'repro-ct-suite' ),
			'manage_options',
			'repro-ct-suite-update',
			array( $this, 'display_update_page' )
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
	 * Display the appointments consolidated page.
	 */
	public function display_appointments_page() {
		include_once plugin_dir_path( __FILE__ ) . 'views/admin-appointments.php';
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

		// Dependencies laden
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-repro-ct-suite-ct-client.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/repositories/class-repro-ct-suite-repository-base.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/repositories/class-repro-ct-suite-calendars-repository.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/services/class-repro-ct-suite-calendar-sync-service.php';

		try {
			// Service instanziieren
			$ct_client = new Repro_CT_Suite_CT_Client();
			$calendars_repo = new Repro_CT_Suite_Calendars_Repository();
			$sync_service = new Repro_CT_Suite_Calendar_Sync_Service( $ct_client, $calendars_repo );

			// DEBUG: Log Request-Details ins WordPress Debug-Log
			$tenant = get_option( 'repro_ct_suite_ct_tenant', '' );
			$debug_info = array(
				'tenant' => $tenant,
				'url' => 'https://' . $tenant . '.church.tools/api/calendars',
				'timestamp' => current_time( 'mysql' )
			);
			
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

		// Dependencies laden
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-repro-ct-suite-ct-client.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/repositories/class-repro-ct-suite-repository-base.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/repositories/class-repro-ct-suite-calendars-repository.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/repositories/class-repro-ct-suite-events-repository.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/repositories/class-repro-ct-suite-appointments-repository.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/services/class-repro-ct-suite-events-sync-service.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/services/class-repro-ct-suite-appointments-sync-service.php';

		try {
			// Services instanziieren
			$ct_client = new Repro_CT_Suite_CT_Client();
			$calendars_repo = new Repro_CT_Suite_Calendars_Repository();
			$events_repo = new Repro_CT_Suite_Events_Repository();
			$appointments_repo = new Repro_CT_Suite_Appointments_Repository();

			// Nur ausgewählte Kalender synchronisieren
			$selected_calendar_ids = $calendars_repo->get_selected_ids();

			if ( empty( $selected_calendar_ids ) ) {
				wp_send_json_error( array(
					'message' => __( 'Keine Kalender ausgewählt. Bitte wählen Sie mindestens einen Kalender aus.', 'repro-ct-suite' )
				) );
			}

			// Events synchronisieren
			$events_service = new Repro_CT_Suite_Events_Sync_Service( $ct_client, $events_repo );
			$events_result = $events_service->sync_events( array(
				'calendar_ids' => $selected_calendar_ids
			) );

			// Appointments synchronisieren
			$appointments_service = new Repro_CT_Suite_Appointments_Sync_Service( $ct_client, $appointments_repo );
			$appointments_result = $appointments_service->sync_appointments( array(
				'calendar_ids' => $selected_calendar_ids
			) );

			wp_send_json_success( array(
				'message' => sprintf(
					__( 'Synchronisation abgeschlossen: %d Events, %d Termine importiert.', 'repro-ct-suite' ),
					$events_result['inserted'] + $events_result['updated'],
					$appointments_result['inserted'] + $appointments_result['updated']
				),
				'stats' => array(
					'events' => $events_result,
					'appointments' => $appointments_result
				)
			) );

		} catch ( Exception $e ) {
			wp_send_json_error( array(
				'message' => sprintf(
					__( 'Fehler bei der Synchronisation: %s', 'repro-ct-suite' ),
					$e->getMessage()
				)
			) );
		}
	}
}
