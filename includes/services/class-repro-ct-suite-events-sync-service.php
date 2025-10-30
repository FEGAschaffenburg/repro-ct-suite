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
	/** @var Repro_CT_Suite_Calendars_Repository */
	private $calendars_repo;
	/** @var Repro_CT_Suite_Schedule_Repository */
	private $schedule_repo;

	public function __construct( $ct_client, $events_repo, $calendars_repo = null, $schedule_repo = null ) {
		$this->ct_client     = $ct_client;
		$this->events_repo   = $events_repo;
		$this->calendars_repo = $calendars_repo;
		$this->schedule_repo = $schedule_repo;
	}

	/**
	 * Synchronisiert Events (Veranstaltungen-Einzeltermine) im Zeitraum
	 *
	 * @param array $args { from: Y-m-d, to: Y-m-d, calendar_ids: array (externe ChurchTools IDs) }
	 * @return array|WP_Error Stats-Array oder WP_Error
	 */
	public function sync_events( $args = array() ) {
		$defaults = array(
			'from'         => date( 'Y-m-d', current_time( 'timestamp' ) - 7 * DAY_IN_SECONDS ),
			'to'           => date( 'Y-m-d', current_time( 'timestamp' ) + 90 * DAY_IN_SECONDS ),
			'calendar_ids' => array(), // Externe ChurchTools Calendar-IDs zum Filtern
		);
		$args = wp_parse_args( $args, $defaults );

		Repro_CT_Suite_Logger::header( 'EVENTS-SYNC START (Veranstaltungen-Einzeltermine)' );
		Repro_CT_Suite_Logger::log( 'Zeitraum: ' . $args['from'] . ' bis ' . $args['to'] );
		
		// Ausgewählte Kalender-IDs loggen
		if ( ! empty( $args['calendar_ids'] ) ) {
			Repro_CT_Suite_Logger::log( 'Filter auf Kalender-IDs: ' . implode( ', ', $args['calendar_ids'] ) );
		} else {
			Repro_CT_Suite_Logger::log( 'WARNUNG: Keine Kalender-Filter - alle Events werden importiert!', 'warning' );
		}

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
		Repro_CT_Suite_Logger::log( 'Gefundene Events (gesamt): ' . count( $events ) );

		// Kalender-Filter anwenden (nachträglich, da Events-API keine calendar_ids unterstützt)
		$allowed_calendar_ids = array_map( 'strval', $args['calendar_ids'] ); // als Strings
		$filtered_events = array();
		$stats_filtered = 0;
		
		foreach ( $events as $e ) {
			// calendar_id extrahieren
			$event_calendar_id = null;
			if ( isset( $e['calendar']['id'] ) ) {
				$event_calendar_id = (string) $e['calendar']['id'];
			} elseif ( isset( $e['calendarId'] ) ) {
				$event_calendar_id = (string) $e['calendarId'];
			} elseif ( isset( $e['calendar_id'] ) ) {
				$event_calendar_id = (string) $e['calendar_id'];
			}
			
			// Wenn Kalender-Filter aktiv ist UND Event hat calendar_id
			if ( ! empty( $allowed_calendar_ids ) && $event_calendar_id !== null ) {
				if ( ! in_array( $event_calendar_id, $allowed_calendar_ids, true ) ) {
					$stats_filtered++;
					continue; // Event überspringen
				}
			}
			
			$filtered_events[] = $e;
		}
		
		if ( $stats_filtered > 0 ) {
			Repro_CT_Suite_Logger::log( 'Events gefiltert (nicht ausgewählte Kalender): ' . $stats_filtered, 'info' );
		}
		Repro_CT_Suite_Logger::log( 'Events nach Filter: ' . count( $filtered_events ) );

		$stats = array( 
			'total' => count( $events ), 
			'filtered' => $stats_filtered,
			'processed' => count( $filtered_events ),
			'inserted' => 0, 
			'updated' => 0, 
			'errors' => 0 
		);

		foreach ( $filtered_events as $e ) {
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

				// Appointment-ID extrahieren (falls Event aus Appointment stammt)
				// ChurchTools liefert bei Events aus Appointments: appointment.id oder appointmentId
				$appointment_id = null;
				if ( isset( $e['appointment']['id'] ) ) {
					$appointment_id = (int) $e['appointment']['id'];
				} elseif ( isset( $e['appointmentId'] ) ) {
					$appointment_id = (int) $e['appointmentId'];
				} elseif ( isset( $e['appointment_id'] ) ) {
					$appointment_id = (int) $e['appointment_id'];
				}

				// Kalender-ID extrahieren (externe ChurchTools Calendar-ID)
				// WICHTIG: Wir speichern die EXTERNE Calendar-ID, nicht die interne WordPress-ID
				$calendar_id = null;
				if ( isset( $e['calendar']['id'] ) ) {
					$calendar_id = (string) $e['calendar']['id'];
				} elseif ( isset( $e['calendarId'] ) ) {
					$calendar_id = (string) $e['calendarId'];
				} elseif ( isset( $e['calendar_id'] ) ) {
					$calendar_id = (string) $e['calendar_id'];
				}

				// Upsert
				$data = array(
					'external_id'    => $external_id,
					'calendar_id'    => $calendar_id, // Externe ChurchTools Calendar-ID
					'appointment_id' => $appointment_id, // NULL wenn nicht aus Appointment
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

				// Schedule updaten
				if ( $this->schedule_repo && $local_id ) {
					$this->schedule_repo->upsert_from_event( array_merge( $data, array( 'id' => $local_id ) ) );
				}
			} catch ( Exception $ex ) {
				$stats['errors']++;
				Repro_CT_Suite_Logger::log( 'Event-Import-Fehler: ' . $ex->getMessage(), 'error' );
			}
		}

		Repro_CT_Suite_Logger::dump( $stats, 'Events Sync Stats', 'success' );
		return $stats;
	}
}
