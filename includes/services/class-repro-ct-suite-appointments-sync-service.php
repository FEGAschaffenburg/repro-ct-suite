<?php
/**
 * Appointments Sync Service
 *
 * Holt Termine (Appointments) für ausgewählte Kalender und verknüpft sie
 * optional mit bereits importierten Events.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once dirname( dirname( __FILE__ ) ) . '/class-repro-ct-suite-logger.php';

class Repro_CT_Suite_Appointments_Sync_Service {
	/** @var Repro_CT_Suite_CT_Client */
	private $ct_client;
	/** @var Repro_CT_Suite_Appointments_Repository */
	private $appointments_repo;
	/** @var Repro_CT_Suite_Events_Repository */
	private $events_repo;
	/** @var Repro_CT_Suite_Calendars_Repository */
	private $calendars_repo;

	public function __construct( $ct_client, $appointments_repo, $events_repo, $calendars_repo ) {
		$this->ct_client        = $ct_client;
		$this->appointments_repo = $appointments_repo;
		$this->events_repo      = $events_repo;
		$this->calendars_repo   = $calendars_repo;
	}

	/**
	 * Synchronisiert Appointments für ausgewählte Kalender
	 *
	 * @param array $args { calendar_ids: int[] (lokale IDs), from: Y-m-d, to: Y-m-d }
	 * @return array|WP_Error Stats-Array oder WP_Error
	 */
	public function sync_appointments( $args = array() ) {
		$defaults = array(
			'calendar_ids' => array(),
			'from' => date( 'Y-m-d', current_time( 'timestamp' ) - 7 * DAY_IN_SECONDS ),
			'to'   => date( 'Y-m-d', current_time( 'timestamp' ) + 90 * DAY_IN_SECONDS ),
		);
		$args = wp_parse_args( $args, $defaults );

		if ( empty( $args['calendar_ids'] ) ) {
			return new WP_Error( 'no_calendars_selected', __( 'Keine Kalender ausgewählt.', 'repro-ct-suite' ) );
		}

		// Lokale Kalender-IDs -> externe Calendar-IDs
		$external_calendar_ids = array();
		foreach ( $args['calendar_ids'] as $local_id ) {
			$cal = $this->calendars_repo->get_by_id( (int) $local_id );
			if ( $cal && ! empty( $cal->external_id ) ) {
				$external_calendar_ids[] = (string) $cal->external_id;
			}
		}

		if ( empty( $external_calendar_ids ) ) {
			return new WP_Error( 'no_external_ids', __( 'Keine externen Kalender-IDs gefunden.', 'repro-ct-suite' ) );
		}

		Repro_CT_Suite_Logger::header( 'APPOINTMENTS-SYNC START' );
		Repro_CT_Suite_Logger::log( 'Zeitraum: ' . $args['from'] . ' bis ' . $args['to'] );
		Repro_CT_Suite_Logger::log( 'Kalender (extern): ' . implode( ',', $external_calendar_ids ) );

		// Neue Strategie: pro Kalender GET /calendars/{id}/appointments
		$all_appointments = array();
		$errors = 0;
		foreach ( $external_calendar_ids as $cid ) {
			$endpoint = '/calendars/' . rawurlencode( (string) $cid ) . '/appointments';
			Repro_CT_Suite_Logger::log( 'Abruf: ' . $endpoint . ' ? from=' . $args['from'] . ' & to=' . $args['to'] );
			$response = $this->ct_client->get( $endpoint, array( 'from' => $args['from'], 'to' => $args['to'] ) );
			if ( is_wp_error( $response ) ) {
				$code = $response->get_error_data()['status'] ?? null;
				Repro_CT_Suite_Logger::log( 'Kalender ' . $cid . ' fehlgeschlagen (status=' . ( $code ?? 'n/a' ) . '): ' . $response->get_error_message(), 'warning' );
				if ( in_array( (int) $code, array( 400, 404, 405 ), true ) ) { $errors++; continue; }
				return $response; // harte Fehler abbrechen
			}
			if ( isset( $response['data'] ) && is_array( $response['data'] ) ) {
				$all_appointments = array_merge( $all_appointments, $response['data'] );
			} else {
				Repro_CT_Suite_Logger::log( 'Unerwartete Struktur bei Kalender ' . $cid, 'warning' );
				$errors++;
			}
		}

		Repro_CT_Suite_Logger::log( 'Gefundene Appointments gesamt: ' . count( $all_appointments ) );

		$appointments = $all_appointments;
		$stats = array( 'total' => count( $appointments ), 'inserted' => 0, 'updated' => 0, 'errors' => (int) $errors );

		foreach ( $appointments as $a ) {
			try {
				$external_id = (string) ( $a['id'] ?? '' );
				if ( $external_id === '' ) { throw new Exception( 'Appointment ohne id' ); }

				// Kalender-Zuordnung (extern -> lokal)
				$calendar_ext = $a['calendarId'] ?? ( $a['calendar']['id'] ?? null );
				$local_calendar_id = null;
				if ( $calendar_ext !== null ) {
					$cal = $this->calendars_repo->get_by_external_id( (string) $calendar_ext );
					$local_calendar_id = $cal ? (int) $cal->id : null;
				}

				// Event-Zuordnung (extern -> lokal), falls vorhanden
				$event_ext = $a['eventId'] ?? ( $a['event']['id'] ?? null );
				$local_event_id = null;
				if ( $event_ext !== null ) {
					$local_event_id = $this->events_repo->get_id_by_external_id( (string) $event_ext );
				}

				$title = sanitize_text_field( $a['title'] ?? $a['name'] ?? '' );
				$description = isset( $a['description'] ) ? (string) $a['description'] : null;
				$start_raw = $a['start'] ?? $a['startDate'] ?? null;
				$end_raw   = $a['end'] ?? $a['endDate'] ?? null;
				$is_all_day = ! empty( $a['isAllDay'] ) ? 1 : 0;

				$start_dt = $start_raw ? gmdate( 'Y-m-d H:i:s', strtotime( $start_raw ) ) : null;
				$end_dt   = $end_raw ? gmdate( 'Y-m-d H:i:s', strtotime( $end_raw ) ) : null;

				$data = array(
					'external_id'     => $external_id,
					'event_id'        => $local_event_id,
					'calendar_id'     => $local_calendar_id,
					'title'           => $title,
					'description'     => $description,
					'start_datetime'  => $start_dt,
					'end_datetime'    => $end_dt,
					'is_all_day'      => $is_all_day,
					'raw_payload'     => wp_json_encode( $a ),
				);

				// Insert/Update
				$existing_row = null;
				// effizient: direkt upsert_by_external_id und anhand Rückgabewert beurteilen
				$existing_id = $this->appointments_repo->db->get_var( $this->appointments_repo->db->prepare( "SELECT id FROM {$this->appointments_repo->table} WHERE external_id=%s", $external_id ) );
				$local_id = $this->appointments_repo->upsert_by_external_id( $data );
				$stats[ $existing_id ? 'updated' : 'inserted' ]++;
			} catch ( Exception $ex ) {
				$stats['errors']++;
				Repro_CT_Suite_Logger::log( 'Appointment-Import-Fehler: ' . $ex->getMessage(), 'error' );
			}
		}

		Repro_CT_Suite_Logger::dump( $stats, 'Appointments Sync Stats', 'success' );
		return $stats;
	}
}
