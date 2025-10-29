<?php
/**
 * Calendar Sync Service
 *
 * Synchronisiert Kalender aus ChurchTools in die lokale Datenbank.
 *
 * @package    Repro_CT_Suite
 * @subpackage Repro_CT_Suite/includes/services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Repro_CT_Suite_Calendar_Sync_Service {

	/**
	 * ChurchTools API Client
	 *
	 * @var Repro_CT_Suite_CT_Client
	 */
	private $ct_client;

	/**
	 * Calendars Repository
	 *
	 * @var Repro_CT_Suite_Calendars_Repository
	 */
	private $calendars_repo;

	/**
	 * Konstruktor
	 *
	 * @param Repro_CT_Suite_CT_Client            $ct_client ChurchTools API Client.
	 * @param Repro_CT_Suite_Calendars_Repository $calendars_repo Calendars Repository.
	 */
	public function __construct( $ct_client, $calendars_repo ) {
		$this->ct_client      = $ct_client;
		$this->calendars_repo = $calendars_repo;
	}

	/**
	 * Synchronisiert Kalender aus ChurchTools
	 *
	 * Ruft alle Kalender ab und speichert sie in der Datenbank.
	 * Behält User-Auswahl (is_selected) bei Update bei.
	 *
	 * @return array|WP_Error Array mit Statistiken oder WP_Error bei Fehler.
	 */
	public function sync_calendars() {
		$response = $this->ct_client->get( '/calendars' );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		if ( ! isset( $response['data'] ) || ! is_array( $response['data'] ) ) {
			return new WP_Error(
				'invalid_response',
				__( 'Ungültige API-Antwort: data-Array fehlt', 'repro-ct-suite' )
			);
		}

		$calendars = $response['data'];
		$stats = array(
			'total'    => count( $calendars ),
			'inserted' => 0,
			'updated'  => 0,
			'errors'   => 0,
		);

		foreach ( $calendars as $calendar_data ) {
			$result = $this->import_calendar( $calendar_data );
			
			if ( is_wp_error( $result ) ) {
				$stats['errors']++;
				continue;
			}

			if ( $result['action'] === 'insert' ) {
				$stats['inserted']++;
			} else {
				$stats['updated']++;
			}
		}

		// Sync-Zeitpunkt speichern
		update_option( 'repro_ct_suite_calendars_last_sync', current_time( 'mysql' ), false );

		return $stats;
	}

	/**
	 * Importiert einen einzelnen Kalender
	 *
	 * @param array $calendar_data Kalender-Daten aus ChurchTools API.
	 * @return array|WP_Error Array mit 'id' und 'action' oder WP_Error.
	 */
	private function import_calendar( $calendar_data ) {
		if ( empty( $calendar_data['id'] ) ) {
			return new WP_Error( 'missing_id', __( 'Kalender-ID fehlt', 'repro-ct-suite' ) );
		}

		// Prüfe ob Kalender bereits existiert
		$existing = $this->calendars_repo->get_by_external_id( $calendar_data['id'] );
		$action = $existing ? 'update' : 'insert';

		// Daten aufbereiten
		$data = array(
			'external_id'     => sanitize_text_field( $calendar_data['id'] ),
			'name'            => sanitize_text_field( $calendar_data['name'] ?? '' ),
			'name_translated' => ! empty( $calendar_data['nameTranslated'] )
				? sanitize_text_field( $calendar_data['nameTranslated'] )
				: null,
			'color'           => ! empty( $calendar_data['color'] )
				? sanitize_hex_color( $calendar_data['color'] )
				: null,
			'is_public'       => ! empty( $calendar_data['isPublic'] ) ? 1 : 0,
			'sort_order'      => isset( $calendar_data['sortKey'] )
				? absint( $calendar_data['sortKey'] )
				: null,
			'raw_payload'     => wp_json_encode( $calendar_data ),
		);

		// Bei neuem Kalender: Standard-Auswahl basierend auf is_public
		if ( $action === 'insert' ) {
			$data['is_selected'] = $data['is_public'];
		}

		$calendar_id = $this->calendars_repo->upsert_by_external_id( $data );

		if ( ! $calendar_id ) {
			return new WP_Error(
				'upsert_failed',
				__( 'Kalender konnte nicht gespeichert werden', 'repro-ct-suite' )
			);
		}

		return array(
			'id'     => $calendar_id,
			'action' => $action,
		);
	}

	/**
	 * Holt einen einzelnen Kalender aus ChurchTools
	 *
	 * @param string $calendar_id ChurchTools Calendar ID.
	 * @return array|WP_Error Kalender-Daten oder WP_Error.
	 */
	public function fetch_calendar( $calendar_id ) {
		$response = $this->ct_client->get( "/calendars/{$calendar_id}" );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		if ( ! isset( $response['data'] ) ) {
			return new WP_Error(
				'invalid_response',
				__( 'Ungültige API-Antwort: data fehlt', 'repro-ct-suite' )
			);
		}

		return $response['data'];
	}
}
