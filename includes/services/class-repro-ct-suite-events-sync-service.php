<?php
/**
 * Events Sync Service
 *
 * Holt Events (Veranstaltungen-Einzeltermine) aus ChurchTools im angegebenen
 * Zeitraum und speichert/aktualisiert sie in der Veranstaltungen-Gesamtliste.
 * Events sind hier die direkt aus /events kommenden Einzeltermine.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once dirname( dirname( __FILE__ ) ) . '/class-repro-ct-suite-logger.php';

class Repro_CT_Suite_Events_Sync_Service {
	/** @var Repro_CT_Suite_CT_Client */
	private $ct_client;
	/** @var Repro_CT_Suite_Events_Repository */
	private $events_repo;

	public function __construct( $ct_client, $events_repo ) {
		$this->ct_client  = $ct_client;
		$this->events_repo = $events_repo;
	}

	/**
	 * Synchronisiert Events (Veranstaltungen-Einzeltermine) im Zeitraum
	 *
	 * @param array $args { from: Y-m-d, to: Y-m-d }
	 * @return array|WP_Error Stats-Array oder WP_Error
	 */
	public function sync_events( $args = array() ) {
		$defaults = array(
			'from' => date( 'Y-m-d', current_time( 'timestamp' ) - 7 * DAY_IN_SECONDS ),
			'to'   => date( 'Y-m-d', current_time( 'timestamp' ) + 90 * DAY_IN_SECONDS ),
		);
		$args = wp_parse_args( $args, $defaults );

		Repro_CT_Suite_Logger::header( 'EVENTS-SYNC START (Veranstaltungen-Einzeltermine)' );
		Repro_CT_Suite_Logger::log( 'Zeitraum: ' . $args['from'] . ' bis ' . $args['to'] );

		$endpoints = array(
			'/events',
			// Fallbacks (abhängig von CT-Version)
			'/event',
			'/calendars/events',
		);

		$response = null;
		foreach ( $endpoints as $ep ) {
			Repro_CT_Suite_Logger::log( 'Try endpoint: ' . $ep );
			$response = $this->ct_client->get( $ep, array(
				'from'      => $args['from'],
				'to'        => $args['to'],
				'direction' => 'forward',
				'include'   => 'eventServices',
			) );
			if ( is_wp_error( $response ) ) {
				$code = $response->get_error_data()['status'] ?? null;
				Repro_CT_Suite_Logger::log( 'Endpoint failed (status=' . ( $code ?? 'n/a' ) . '): ' . $response->get_error_message(), 'warning' );
				if ( (int) $code === 404 ) {
					continue; // versuche nächsten Endpoint
				}
				// bei anderen Fehlern abbrechen
				return $response;
			}
			break; // Erfolg
		}

		if ( is_wp_error( $response ) ) {
			return $response; // alle Endpoints fehlgeschlagen
		}

		if ( ! isset( $response['data'] ) || ! is_array( $response['data'] ) ) {
			Repro_CT_Suite_Logger::log( 'Ungültige Events-Response-Struktur', 'error' );
			Repro_CT_Suite_Logger::dump( $response, 'Full Response', 'error' );
			return new WP_Error( 'invalid_events_response', __( 'Ungültige API-Antwort für Events', 'repro-ct-suite' ) );
		}

		$events = $response['data'];
		Repro_CT_Suite_Logger::log( 'Gefundene Events: ' . count( $events ) );

		$stats = array( 'total' => count( $events ), 'inserted' => 0, 'updated' => 0, 'errors' => 0 );

		foreach ( $events as $e ) {
			try {
				$external_id = (string) ( $e['id'] ?? '' );
				if ( $external_id === '' ) { throw new Exception( 'Event ohne id' ); }

				$title = sanitize_text_field( $e['name'] ?? $e['title'] ?? '' );
				$description = isset( $e['description'] ) ? (string) $e['description'] : null;
				$start_raw = $e['startDate'] ?? $e['start'] ?? null;
				$end_raw   = $e['endDate'] ?? $e['end'] ?? null;
				$location  = isset( $e['location'] ) && is_array( $e['location'] ) ? ( $e['location']['name'] ?? null ) : ( $e['location_name'] ?? null );
				$status    = $e['status'] ?? null;

				$start_dt = $start_raw ? gmdate( 'Y-m-d H:i:s', strtotime( $start_raw ) ) : null;
				$end_dt   = $end_raw ? gmdate( 'Y-m-d H:i:s', strtotime( $end_raw ) ) : null;

				// Upsert
				$data = array(
					'external_id'    => $external_id,
					'title'          => $title,
					'description'    => $description,
					'start_datetime' => $start_dt,
					'end_datetime'   => $end_dt,
					'location_name'  => $location ? sanitize_text_field( $location ) : null,
					'status'         => $status ? sanitize_text_field( $status ) : null,
					'raw_payload'    => wp_json_encode( $e ),
				);

				$existing_id = $this->events_repo->get_id_by_external_id( $external_id );
				$local_id = $this->events_repo->upsert_by_external_id( $data );
				$stats[ $existing_id ? 'updated' : 'inserted' ]++;
			} catch ( Exception $ex ) {
				$stats['errors']++;
				Repro_CT_Suite_Logger::log( 'Event-Import-Fehler: ' . $ex->getMessage(), 'error' );
			}
		}

		Repro_CT_Suite_Logger::dump( $stats, 'Events Sync Stats', 'success' );
		return $stats;
	}
}
