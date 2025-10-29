<?php
/**
 * ChurchTools API Client
 *
 * Authentifizierung via Username/Passwort, Cookie-basierte Session-Verwaltung
 *
 * @package    Repro_CT_Suite
 * @subpackage Repro_CT_Suite/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Logger laden
require_once dirname( __FILE__ ) . '/class-repro-ct-suite-logger.php';

class Repro_CT_Suite_CT_Client {

	/**
	 * ChurchTools Tenant (z.B. "gemeinde" für gemeinde.church.tools)
	 *
	 * @var string
	 */
	private $tenant;

	/**
	 * Benutzername
	 *
	 * @var string
	 */
	private $username;

	/**
	 * Passwort (im Klartext, wird nicht gespeichert)
	 *
	 * @var string
	 */
	private $password;

	/**
	 * Gespeicherte Cookies für Session-Wiederverwendung
	 *
	 * @var array
	 */
	private $cookies = array();

	/**
	 * Option-Key für Cookie-Speicherung
	 */
	const COOKIE_OPTION_KEY = 'repro_ct_suite_ct_cookies';

	/**
	 * Konstruktor
	 *
	 * @param string $tenant   ChurchTools Tenant
	 * @param string $username Benutzername
	 * @param string $password Passwort (Klartext)
	 */
	public function __construct( $tenant, $username, $password ) {
		$this->tenant   = sanitize_text_field( $tenant );
		$this->username = sanitize_text_field( $username );
		$this->password = $password; // wird nicht gespeichert

		// Cookies aus DB laden
		$this->load_cookies();
	}

	/**
	 * Basis-URL für ChurchTools API
	 *
	 * @return string
	 */
	private function get_base_url() {
		return sprintf( 'https://%s.church.tools/api', $this->tenant );
	}

	/**
	 * Login bei ChurchTools und Cookie-Speicherung
	 *
	 * @return bool|WP_Error true bei Erfolg, WP_Error bei Fehler
	 */
	public function login() {
		$url = $this->get_base_url() . '/login';

		$response = wp_remote_post(
			$url,
			array(
				'body'    => wp_json_encode(
					array(
						'username' => $this->username,
						'password' => $this->password,
					)
				),
				'headers' => array(
					'Content-Type' => 'application/json',
				),
				'timeout' => 15,
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$status = wp_remote_retrieve_response_code( $response );
		if ( $status !== 200 ) {
			$body = wp_remote_retrieve_body( $response );
			$data = json_decode( $body, true );
			$message = isset( $data['message'] ) ? $data['message'] : __( 'Login fehlgeschlagen', 'repro-ct-suite' );
			return new WP_Error( 'ct_login_failed', $message, array( 'status' => $status ) );
		}

		// Cookies extrahieren und speichern
		$cookies = wp_remote_retrieve_cookies( $response );
		if ( ! empty( $cookies ) ) {
			$this->cookies = array();
			foreach ( $cookies as $cookie ) {
				$this->cookies[ $cookie->name ] = $cookie->value;
			}
			$this->save_cookies();
		}

		return true;
	}

	/**
	 * Prüft, ob eine gültige Session vorhanden ist (via gespeicherte Cookies)
	 *
	 * @return bool
	 */
	public function is_authenticated() {
		if ( empty( $this->cookies ) ) {
			return false;
		}

		// Optionaler Whoami-Check (kann hinzugefügt werden)
		// Für jetzt: wenn Cookies da sind, annehmen dass Session aktiv ist
		return true;
	}

	/**
	 * API GET-Request mit Cookie-basierter Authentifizierung
	 *
	 * @param string $endpoint API-Endpunkt (z.B. '/events')
	 * @param array  $args     Query-Parameter
	 * @return array|WP_Error Array mit 'data' und 'meta', oder WP_Error
	 */
	public function get( $endpoint, $args = array() ) {
		// DEBUG: Log Request Start
		Repro_CT_Suite_Logger::log( 'CT_Client::get() called' );
		Repro_CT_Suite_Logger::log( 'Endpoint: ' . $endpoint );
		if ( ! empty( $args ) ) {
			Repro_CT_Suite_Logger::dump( $args, 'Query Args' );
		}
		Repro_CT_Suite_Logger::log( 'Is Authenticated: ' . ( $this->is_authenticated() ? 'YES' : 'NO' ) );
		
		if ( ! $this->is_authenticated() ) {
			Repro_CT_Suite_Logger::log( 'Not authenticated, attempting login...', 'warning' );
			$login_result = $this->login();
			if ( is_wp_error( $login_result ) ) {
				Repro_CT_Suite_Logger::log( 'Login failed: ' . $login_result->get_error_message(), 'error' );
				return $login_result;
			}
			Repro_CT_Suite_Logger::log( 'Login successful', 'success' );
		}

		$url = $this->get_base_url() . $endpoint;
		if ( ! empty( $args ) ) {
			$url = add_query_arg( $args, $url );
		}

		Repro_CT_Suite_Logger::log( 'Full Request URL: ' . $url );
		Repro_CT_Suite_Logger::log( 'Sending HTTP GET request...' );

		$response = wp_remote_get(
			$url,
			array(
				'headers' => $this->get_headers(),
				'timeout' => 30,
			)
		);

		if ( is_wp_error( $response ) ) {
			Repro_CT_Suite_Logger::log( 'HTTP Request failed: ' . $response->get_error_message(), 'error' );
			return $response;
		}

		$status = wp_remote_retrieve_response_code( $response );
		Repro_CT_Suite_Logger::log( 'Response Status Code: ' . $status );
		
		if ( $status === 401 ) {
			Repro_CT_Suite_Logger::log( '401 Unauthorized - Session expired, attempting re-login', 'warning' );
			// Session abgelaufen, neu einloggen
			$this->clear_cookies();
			$login_result = $this->login();
			if ( is_wp_error( $login_result ) ) {
				Repro_CT_Suite_Logger::log( 'Re-login failed: ' . $login_result->get_error_message(), 'error' );
				return $login_result;
			}
			// Retry
			Repro_CT_Suite_Logger::log( 'Re-login successful, retrying request', 'success' );
			return $this->get( $endpoint, $args );
		}

		if ( $status !== 200 ) {
			$body = wp_remote_retrieve_body( $response );
			Repro_CT_Suite_Logger::log( 'API Error - Status: ' . $status, 'error' );
			Repro_CT_Suite_Logger::log( 'Response Body: ' . substr( $body, 0, 500 ), 'error' );
			return new WP_Error( 'ct_api_error', sprintf( 'API Error: %d - %s', $status, $body ), array( 'status' => $status ) );
		}

		$body = wp_remote_retrieve_body( $response );
		Repro_CT_Suite_Logger::log( 'Response Body Length: ' . strlen( $body ) . ' bytes', 'success' );
		Repro_CT_Suite_Logger::log( 'Response preview (first 500 chars): ' . substr( $body, 0, 500 ) );
		
		$data = json_decode( $body, true );
		
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			Repro_CT_Suite_Logger::log( 'JSON Decode Error: ' . json_last_error_msg(), 'error' );
			return new WP_Error( 'json_decode_error', 'Failed to decode JSON response: ' . json_last_error_msg() );
		}
		
		Repro_CT_Suite_Logger::log( 'Successfully decoded JSON response', 'success' );
		if ( is_array( $data ) ) {
			Repro_CT_Suite_Logger::log( 'Response has keys: ' . implode( ', ', array_keys( $data ) ) );
		}

		return $data;
	}

	/**
	 * API POST-Request mit JSON-Body und Cookie-basierter Authentifizierung
	 *
	 * @param string $endpoint API-Endpunkt (z.B. '/calendars/appointments')
	 * @param array  $body     Assoziatives Array, wird als JSON gesendet
	 * @return array|WP_Error  Array mit 'data' und 'meta', oder WP_Error
	 */
	public function post( $endpoint, $body = array() ) {
		Repro_CT_Suite_Logger::log( 'CT_Client::post() called' );
		Repro_CT_Suite_Logger::log( 'Endpoint: ' . $endpoint );
		if ( ! empty( $body ) ) {
			Repro_CT_Suite_Logger::dump( $body, 'POST Body' );
		}
		Repro_CT_Suite_Logger::log( 'Is Authenticated: ' . ( $this->is_authenticated() ? 'YES' : 'NO' ) );

		if ( ! $this->is_authenticated() ) {
			Repro_CT_Suite_Logger::log( 'Not authenticated, attempting login...', 'warning' );
			$login_result = $this->login();
			if ( is_wp_error( $login_result ) ) {
				Repro_CT_Suite_Logger::log( 'Login failed: ' . $login_result->get_error_message(), 'error' );
				return $login_result;
			}
			Repro_CT_Suite_Logger::log( 'Login successful', 'success' );
		}

		$url = $this->get_base_url() . $endpoint;
		Repro_CT_Suite_Logger::log( 'Full Request URL: ' . $url );

		$response = wp_remote_post(
			$url,
			array(
				'headers' => $this->get_headers(),
				'body'    => wp_json_encode( $body ),
				'timeout' => 30,
			)
		);

		if ( is_wp_error( $response ) ) {
			Repro_CT_Suite_Logger::log( 'HTTP POST failed: ' . $response->get_error_message(), 'error' );
			return $response;
		}

		$status = wp_remote_retrieve_response_code( $response );
		Repro_CT_Suite_Logger::log( 'POST Response Status Code: ' . $status );

		if ( $status === 401 ) {
			Repro_CT_Suite_Logger::log( '401 Unauthorized on POST - attempting re-login', 'warning' );
			$this->clear_cookies();
			$login_result = $this->login();
			if ( is_wp_error( $login_result ) ) {
				Repro_CT_Suite_Logger::log( 'Re-login failed: ' . $login_result->get_error_message(), 'error' );
				return $login_result;
			}
			Repro_CT_Suite_Logger::log( 'Re-login successful, retrying POST', 'success' );
			return $this->post( $endpoint, $body );
		}

		if ( $status !== 200 ) {
			$resp_body = wp_remote_retrieve_body( $response );
			Repro_CT_Suite_Logger::log( 'API Error (POST) - Status: ' . $status, 'error' );
			Repro_CT_Suite_Logger::log( 'Response Body: ' . substr( $resp_body, 0, 500 ), 'error' );
			return new WP_Error( 'ct_api_error', sprintf( 'API Error: %d - %s', $status, $resp_body ), array( 'status' => $status ) );
		}

		$resp_body = wp_remote_retrieve_body( $response );
		Repro_CT_Suite_Logger::log( 'POST Response Body Length: ' . strlen( $resp_body ) . ' bytes', 'success' );
		Repro_CT_Suite_Logger::log( 'Response preview (first 500 chars): ' . substr( $resp_body, 0, 500 ) );

		$data = json_decode( $resp_body, true );
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			Repro_CT_Suite_Logger::log( 'JSON Decode Error (POST): ' . json_last_error_msg(), 'error' );
			return new WP_Error( 'json_decode_error', 'Failed to decode JSON response: ' . json_last_error_msg() );
		}

		Repro_CT_Suite_Logger::log( 'Successfully decoded JSON POST response', 'success' );
		if ( is_array( $data ) ) {
			Repro_CT_Suite_Logger::log( 'Response has keys: ' . implode( ', ', array_keys( $data ) ) );
		}

		return $data;
	}

	/**
	 * Headers für API-Requests (inkl. Cookies)
	 *
	 * @return array
	 */
	private function get_headers() {
		$headers = array(
			'Content-Type' => 'application/json',
		);

		if ( ! empty( $this->cookies ) ) {
			$cookie_strings = array();
			foreach ( $this->cookies as $name => $value ) {
				$cookie_strings[] = $name . '=' . $value;
			}
			$headers['Cookie'] = implode( '; ', $cookie_strings );
		}

		return $headers;
	}

	/**
	 * Cookies in WordPress-Option speichern
	 */
	private function save_cookies() {
		update_option( self::COOKIE_OPTION_KEY, $this->cookies, false );
	}

	/**
	 * Cookies aus WordPress-Option laden
	 */
	private function load_cookies() {
		$cookies = get_option( self::COOKIE_OPTION_KEY, array() );
		if ( is_array( $cookies ) ) {
			$this->cookies = $cookies;
		}
	}

	/**
	 * Cookies löschen (Logout)
	 */
	public function clear_cookies() {
		$this->cookies = array();
		delete_option( self::COOKIE_OPTION_KEY );
	}

	/**
	 * Hilfsmethode: Whoami-Check (optional für is_authenticated)
	 *
	 * @return bool|WP_Error
	 */
	public function whoami() {
		if ( empty( $this->cookies ) ) {
			return new WP_Error( 'not_authenticated', __( 'Keine Cookies vorhanden', 'repro-ct-suite' ) );
		}

		$url = $this->get_base_url() . '/whoami';

		$response = wp_remote_get(
			$url,
			array(
				'headers' => $this->get_headers(),
				'timeout' => 10,
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$status = wp_remote_retrieve_response_code( $response );
		if ( $status === 200 ) {
			return true;
		}

		return new WP_Error( 'whoami_failed', sprintf( 'Whoami failed: %d', $status ), array( 'status' => $status ) );
	}
}
