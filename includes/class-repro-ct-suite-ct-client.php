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
		error_log( '[REPRO CT-SUITE DEBUG] CT_Client::get() called' );
		error_log( '[REPRO CT-SUITE DEBUG] - Endpoint: ' . $endpoint );
		error_log( '[REPRO CT-SUITE DEBUG] - Args: ' . print_r( $args, true ) );
		error_log( '[REPRO CT-SUITE DEBUG] - Is Authenticated: ' . ( $this->is_authenticated() ? 'yes' : 'no' ) );
		
		if ( ! $this->is_authenticated() ) {
			error_log( '[REPRO CT-SUITE DEBUG] Not authenticated, attempting login...' );
			$login_result = $this->login();
			if ( is_wp_error( $login_result ) ) {
				error_log( '[REPRO CT-SUITE DEBUG] Login failed: ' . $login_result->get_error_message() );
				return $login_result;
			}
			error_log( '[REPRO CT-SUITE DEBUG] Login successful' );
		}

		$url = $this->get_base_url() . $endpoint;
		if ( ! empty( $args ) ) {
			$url = add_query_arg( $args, $url );
		}

		error_log( '[REPRO CT-SUITE DEBUG] Full Request URL: ' . $url );
		error_log( '[REPRO CT-SUITE DEBUG] Request Headers: ' . print_r( $this->get_headers(), true ) );

		$response = wp_remote_get(
			$url,
			array(
				'headers' => $this->get_headers(),
				'timeout' => 30,
			)
		);

		if ( is_wp_error( $response ) ) {
			error_log( '[REPRO CT-SUITE DEBUG] HTTP Request failed: ' . $response->get_error_message() );
			return $response;
		}

		$status = wp_remote_retrieve_response_code( $response );
		error_log( '[REPRO CT-SUITE DEBUG] Response Status Code: ' . $status );
		
		if ( $status === 401 ) {
			error_log( '[REPRO CT-SUITE DEBUG] 401 Unauthorized - Session expired, re-login and retry' );
			// Session abgelaufen, neu einloggen
			$this->clear_cookies();
			$login_result = $this->login();
			if ( is_wp_error( $login_result ) ) {
				error_log( '[REPRO CT-SUITE DEBUG] Re-login failed: ' . $login_result->get_error_message() );
				return $login_result;
			}
			// Retry
			error_log( '[REPRO CT-SUITE DEBUG] Re-login successful, retrying request' );
			return $this->get( $endpoint, $args );
		}

		if ( $status !== 200 ) {
			$body = wp_remote_retrieve_body( $response );
			error_log( '[REPRO CT-SUITE DEBUG] API Error - Status: ' . $status . ', Body: ' . $body );
			return new WP_Error( 'ct_api_error', sprintf( 'API Error: %d - %s', $status, $body ), array( 'status' => $status ) );
		}

		$body = wp_remote_retrieve_body( $response );
		error_log( '[REPRO CT-SUITE DEBUG] Response Body Length: ' . strlen( $body ) . ' bytes' );
		error_log( '[REPRO CT-SUITE DEBUG] Response Body (first 500 chars): ' . substr( $body, 0, 500 ) );
		
		$data = json_decode( $body, true );
		
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			error_log( '[REPRO CT-SUITE DEBUG] JSON Decode Error: ' . json_last_error_msg() );
			return new WP_Error( 'json_decode_error', 'Failed to decode JSON response: ' . json_last_error_msg() );
		}
		
		error_log( '[REPRO CT-SUITE DEBUG] Successfully decoded JSON response' );
		error_log( '[REPRO CT-SUITE DEBUG] Response has keys: ' . print_r( array_keys( $data ), true ) );

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
