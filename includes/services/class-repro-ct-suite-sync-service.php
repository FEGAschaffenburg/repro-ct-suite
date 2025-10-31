<?php
/**
 * Unified Sync Service
 *
 * Neuer, vereinfachter Service für die Synchronisation aller Termine aus ChurchTools.
 * Ersetzt die komplexe Events/Appointments-Trennung durch einen einheitlichen Ansatz.
 *
 * @package Repro_CT_Suite
 * @since   0.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once dirname( dirname( __FILE__ ) ) . '/class-repro-ct-suite-logger.php';

class Repro_CT_Suite_Sync_Service {
	
	/** @var Repro_CT_Suite_CT_Client */
	private $ct_client;
	
	/** @var Repro_CT_Suite_Events_Repository */
	private $events_repo;
	
	/** @var Repro_CT_Suite_Calendars_Repository */
	private $calendars_repo;
	
	/** @var Repro_CT_Suite_Schedule_Repository */
	private $schedule_repo;

	public function __construct( $ct_client, $events_repo, $calendars_repo, $schedule_repo = null ) {
		$this->ct_client      = $ct_client;
		$this->events_repo    = $events_repo;
		$this->calendars_repo = $calendars_repo;
		$this->schedule_repo  = $schedule_repo;
	}

	/**
	 * Synchronisiert alle Termine von ausgewählten Kalendern
	 *
	 * Vereinfachter Ansatz: Ruft pro Kalender /calendars/{id}/appointments ab
	 * und speichert alle gefundenen Termine als Events.
	 *
	 * @param array $args { calendar_ids: int[] (lokale IDs), from: Y-m-d, to: Y-m-d }
	 * @return array|WP_Error Statistiken oder WP_Error
	 */
	public function sync_events( $args = array() ) {
		$defaults = array(
			'calendar_ids' => array(),
			'from' => date( 'Y-m-d', current_time( 'timestamp' ) - 7 * DAY_IN_SECONDS ),
			'to'   => date( 'Y-m-d', current_time( 'timestamp' ) + 90 * DAY_IN_SECONDS ),
		);
		$args = wp_parse_args( $args, $defaults );

		if ( empty( $args['calendar_ids'] ) ) {
			return new WP_Error( 'no_calendars_selected', __( 'Keine Kalender für den Import ausgewählt.', 'repro-ct-suite' ) );
		}

		Repro_CT_Suite_Logger::header( 'UNIFIED SYNC START - Alle Termine' );
		Repro_CT_Suite_Logger::log( 'Zeitraum: ' . $args['from'] . ' bis ' . $args['to'] );
		Repro_CT_Suite_Logger::log( 'Ausgewählte Kalender (lokale IDs): ' . implode( ', ', $args['calendar_ids'] ) );

		// Lokale Kalender-IDs zu externen ChurchTools-IDs mappen
		$external_calendar_ids = array();
		foreach ( $args['calendar_ids'] as $local_id ) {
			$calendar = $this->calendars_repo->get_by_id( (int) $local_id );
			if ( $calendar && ! empty( $calendar->external_id ) ) {
				$external_calendar_ids[] = array(
					'local_id'    => $local_id,
					'external_id' => $calendar->external_id,
					'name'        => $calendar->name,
				);
			}
		}

		if ( empty( $external_calendar_ids ) ) {
			return new WP_Error( 'no_external_ids', __( 'Keine gültigen ChurchTools-Kalender-IDs gefunden.', 'repro-ct-suite' ) );
		}

		Repro_CT_Suite_Logger::log( 'Externe Kalender-IDs: ' . implode( ', ', array_column( $external_calendar_ids, 'external_id' ) ) );

		$stats = array(
			'calendars_processed' => 0,
			'events_found'        => 0,
			'events_inserted'     => 0,
			'events_updated'      => 0,
			'events_skipped'      => 0,
			'errors'              => 0,
		);

		// Pro Kalender: Termine abrufen und speichern
		foreach ( $external_calendar_ids as $cal_info ) {
			$external_id = $cal_info['external_id'];
			$cal_name    = $cal_info['name'];
			
			Repro_CT_Suite_Logger::log( "Bearbeite Kalender '{$cal_name}' (ID: {$external_id})..." );
			
			$result = $this->sync_calendar_events( $external_id, $args );
			
			if ( is_wp_error( $result ) ) {
				Repro_CT_Suite_Logger::log( "Fehler bei Kalender {$external_id}: " . $result->get_error_message(), 'error' );
				$stats['errors']++;
				continue; // Nächsten Kalender versuchen
			}
			
			// Statistiken aggregieren
			$stats['calendars_processed']++;
			$stats['events_found']    += $result['events_found'];
			$stats['events_inserted'] += $result['events_inserted'];
			$stats['events_updated']  += $result['events_updated'];
			$stats['events_skipped']  += $result['events_skipped'];
			
			Repro_CT_Suite_Logger::log( "Kalender '{$cal_name}': {$result['events_found']} gefunden, {$result['events_inserted']} neu, {$result['events_updated']} aktualisiert" );
		}

		// Schedule-Repository aktualisieren (falls vorhanden)
		if ( $this->schedule_repo ) {
			Repro_CT_Suite_Logger::log( 'Aktualisiere Schedule-Repository...' );
			$this->schedule_repo->rebuild_from_existing();
		}

		Repro_CT_Suite_Logger::separator();
		Repro_CT_Suite_Logger::log( 'SYNC ABGESCHLOSSEN' );
		Repro_CT_Suite_Logger::log( "Kalender verarbeitet: {$stats['calendars_processed']}" );
		Repro_CT_Suite_Logger::log( "Events gefunden: {$stats['events_found']}" );
		Repro_CT_Suite_Logger::log( "Events eingefügt: {$stats['events_inserted']}" );
		Repro_CT_Suite_Logger::log( "Events aktualisiert: {$stats['events_updated']}" );
		if ( $stats['errors'] > 0 ) {
			Repro_CT_Suite_Logger::log( "Fehler: {$stats['errors']}", 'warning' );
		}

		return $stats;
	}

	/**
	 * Synchronisiert Events eines einzelnen Kalenders - 2-Phase Ansatz
	 *
	 * Phase 1: Events API - sammelt appointment_ids von Events
	 * Phase 2: Appointments API - holt zusätzliche Appointments (nicht in Events enthalten)
	 *
	 * @param string $external_calendar_id ChurchTools Kalender-ID
	 * @param array  $args Sync-Parameter (from, to)
	 * @return array|WP_Error Einzelkalender-Statistiken
	 */
	private function sync_calendar_events( $external_calendar_id, $args ) {
		Repro_CT_Suite_Logger::log( "=== 2-PHASE SYNC für Kalender {$external_calendar_id} ===" );
		
		$stats = array(
			'events_found'    => 0,
			'appointments_found' => 0,
			'events_inserted' => 0,
			'events_updated'  => 0,
			'events_skipped'  => 0,
		);
		
		$imported_appointment_ids = array(); // Tracking für Phase 2
		
		// PHASE 1: Events API - Sammle Events mit appointment_ids
		$events_result = $this->sync_phase1_events( $external_calendar_id, $args, $imported_appointment_ids );
		if ( is_wp_error( $events_result ) ) {
			return $events_result;
		}
		
		// Statistiken von Phase 1 übernehmen
		$stats['events_found'] = $events_result['events_found'];
		$stats['events_inserted'] += $events_result['events_inserted'];
		$stats['events_updated'] += $events_result['events_updated'];
		$stats['events_skipped'] += $events_result['events_skipped'];
		
		// PHASE 2: Appointments API - Hole zusätzliche Appointments
		$appointments_result = $this->sync_phase2_appointments( $external_calendar_id, $args, $imported_appointment_ids );
		if ( is_wp_error( $appointments_result ) ) {
			// Phase 2 Fehler ist nicht kritisch, loggen aber weitermachen
			Repro_CT_Suite_Logger::log( 'Phase 2 Fehler (nicht kritisch): ' . $appointments_result->get_error_message(), 'warning' );
		} else {
			// Statistiken von Phase 2 hinzufügen
			$stats['appointments_found'] = $appointments_result['appointments_found'];
			$stats['events_inserted'] += $appointments_result['events_inserted'];
			$stats['events_updated'] += $appointments_result['events_updated'];
			$stats['events_skipped'] += $appointments_result['events_skipped'];
		}
		
		$total_processed = $stats['events_found'] + $stats['appointments_found'];
		$total_imported = $stats['events_inserted'] + $stats['events_updated'];
		
		Repro_CT_Suite_Logger::log( "2-Phase Sync Ergebnis: {$total_processed} gefunden, {$total_imported} importiert" );
		
		return $stats;
	}
	
	/**
	 * Phase 1: Events API - sammelt Events und deren appointment_ids
	 *
	 * @param string $external_calendar_id ChurchTools Kalender-ID
	 * @param array  $args Sync-Parameter
	 * @param array  &$imported_appointment_ids Referenz für tracking
	 * @return array|WP_Error Phase 1 Statistiken
	 */
	private function sync_phase1_events( $external_calendar_id, $args, &$imported_appointment_ids ) {
		Repro_CT_Suite_Logger::log( "Phase 1: Events API für Kalender {$external_calendar_id}" );
		
		$endpoint = '/events';
		$response = $this->ct_client->get( $endpoint, array(
			'calendar_ids' => array( $external_calendar_id ),
			'from' => $args['from'],
			'to'   => $args['to'],
		) );

		if ( is_wp_error( $response ) ) {
			return new WP_Error( 'events_api_error', 'Events API Fehler: ' . $response->get_error_message() );
		}

		if ( ! isset( $response['data'] ) || ! is_array( $response['data'] ) ) {
			return new WP_Error( 'invalid_events_response', 'Ungültige Events API-Antwort' );
		}

		$events = $response['data'];
		$events_found = count( $events );
		
		Repro_CT_Suite_Logger::log( "Phase 1: {$events_found} Events gefunden" );

		$stats = array(
			'events_found'    => $events_found,
			'events_inserted' => 0,
			'events_updated'  => 0,
			'events_skipped'  => 0,
		);

		foreach ( $events as $event ) {
			// Kalender-ID prüfen (Event kann mehrere Kalender haben)
			if ( ! $this->is_event_relevant_for_calendar( $event, $external_calendar_id ) ) {
				Repro_CT_Suite_Logger::log( "Event {$event['id']} nicht relevant für Kalender {$external_calendar_id}" );
				$stats['events_skipped']++;
				continue;
			}
			
			// Appointment-IDs sammeln für Phase 2
			if ( isset( $event['appointment'] ) && isset( $event['appointment']['id'] ) ) {
				$imported_appointment_ids[] = $event['appointment']['id'];
				Repro_CT_Suite_Logger::log( "Event {$event['id']} → Appointment {$event['appointment']['id']} gemerkt" );
			}
			
			// Event verarbeiten und speichern
			$result = $this->process_event( $event, $external_calendar_id );
			
			if ( is_wp_error( $result ) ) {
				Repro_CT_Suite_Logger::log( 'Fehler bei Event: ' . $result->get_error_message(), 'warning' );
				$stats['events_skipped']++;
				continue;
			}
			
			if ( $result['action'] === 'inserted' ) {
				$stats['events_inserted']++;
			} elseif ( $result['action'] === 'updated' ) {
				$stats['events_updated']++;
			}
		}
		
		Repro_CT_Suite_Logger::log( "Phase 1 abgeschlossen: {$stats['events_inserted']} neu, {$stats['events_updated']} aktualisiert" );
		Repro_CT_Suite_Logger::log( "Gesammelte Appointment-IDs: " . implode( ', ', $imported_appointment_ids ) );
		
		return $stats;
	}
	
	/**
	 * Phase 2: Appointments API - holt zusätzliche Appointments
	 *
	 * @param string $external_calendar_id ChurchTools Kalender-ID
	 * @param array  $args Sync-Parameter
	 * @param array  $imported_appointment_ids Bereits importierte Appointment-IDs
	 * @return array|WP_Error Phase 2 Statistiken
	 */
	private function sync_phase2_appointments( $external_calendar_id, $args, $imported_appointment_ids ) {
		Repro_CT_Suite_Logger::log( "Phase 2: Appointments API für Kalender {$external_calendar_id}" );
		
		$endpoint = '/calendars/' . rawurlencode( $external_calendar_id ) . '/appointments';
		$response = $this->ct_client->get( $endpoint, array(
			'from' => $args['from'],
			'to'   => $args['to'],
		) );

		if ( is_wp_error( $response ) ) {
			return new WP_Error( 'appointments_api_error', 'Appointments API Fehler: ' . $response->get_error_message() );
		}

		if ( ! isset( $response['data'] ) || ! is_array( $response['data'] ) ) {
			return new WP_Error( 'invalid_appointments_response', 'Ungültige Appointments API-Antwort' );
		}

		$appointments = $response['data'];
		$appointments_found = count( $appointments );
		
		Repro_CT_Suite_Logger::log( "Phase 2: {$appointments_found} Appointments gefunden" );

		$stats = array(
			'appointments_found' => $appointments_found,
			'events_inserted' => 0,
			'events_updated'  => 0,
			'events_skipped'  => 0,
		);
		
		$skipped_already_imported = 0;
		$skipped_wrong_calendar = 0;

		foreach ( $appointments as $appointment ) {
			// Prüfung 1: Bereits als Event importiert?
			if ( in_array( $appointment['id'], $imported_appointment_ids ) ) {
				$skipped_already_imported++;
				continue;
			}
			
			// Prüfung 2: Kalender-ID zugelassen?
			if ( ! $this->is_appointment_relevant_for_calendar( $appointment, $external_calendar_id ) ) {
				$skipped_wrong_calendar++;
				continue;
			}
			
			Repro_CT_Suite_Logger::log( "Phase 2: Zusätzliches Appointment {$appointment['id']} wird importiert" );
			
			// Appointment verarbeiten und als Event speichern
			$result = $this->process_appointment( $appointment, $external_calendar_id );
			
			if ( is_wp_error( $result ) ) {
				Repro_CT_Suite_Logger::log( 'Fehler bei Appointment: ' . $result->get_error_message(), 'warning' );
				$stats['events_skipped']++;
				continue;
			}
			
			if ( $result['action'] === 'inserted' ) {
				$stats['events_inserted']++;
			} elseif ( $result['action'] === 'updated' ) {
				$stats['events_updated']++;
			}
		}
		
		Repro_CT_Suite_Logger::log( "Phase 2 abgeschlossen: {$stats['events_inserted']} neu, {$stats['events_updated']} aktualisiert" );
		Repro_CT_Suite_Logger::log( "Übersprungen: {$skipped_already_imported} bereits importiert, {$skipped_wrong_calendar} falscher Kalender" );
		
		return $stats;
	}
	
	/**
	 * Prüft ob ein Event für den spezifischen Kalender relevant ist
	 *
	 * @param array  $event Event-Daten aus der ChurchTools API
	 * @param string $external_calendar_id Ziel-Kalender-ID
	 * @return bool
	 */
	private function is_event_relevant_for_calendar( $event, $external_calendar_id ) {
		// Debug: Event-Struktur loggen
		if ( isset( $event['id'] ) ) {
			Repro_CT_Suite_Logger::log( "Event {$event['id']} Struktur-Check für Kalender {$external_calendar_id}" );
		}
		
		// Prüfung 1: Direkte calendar property
		if ( isset( $event['calendar'] ) && isset( $event['calendar']['id'] ) ) {
			$event_calendar_id = (string) $event['calendar']['id'];
			$matches = $event_calendar_id === (string) $external_calendar_id;
			Repro_CT_Suite_Logger::log( "Event calendar.id: {$event_calendar_id}, Ziel: {$external_calendar_id}, Match: " . ($matches ? 'YES' : 'NO') );
			return $matches;
		}
		
		// Prüfung 2: calendars Array
		if ( isset( $event['calendars'] ) && is_array( $event['calendars'] ) ) {
			foreach ( $event['calendars'] as $calendar ) {
				if ( isset( $calendar['id'] ) ) {
					$event_calendar_id = (string) $calendar['id'];
					if ( $event_calendar_id === (string) $external_calendar_id ) {
						Repro_CT_Suite_Logger::log( "Event calendars[].id: {$event_calendar_id}, Match: YES" );
						return true;
					}
				}
			}
			Repro_CT_Suite_Logger::log( "Event calendars[] geprüft, kein Match gefunden" );
		}
		
		// Prüfung 3: Direkte calendarId property
		if ( isset( $event['calendarId'] ) ) {
			$event_calendar_id = (string) $event['calendarId'];
			$matches = $event_calendar_id === (string) $external_calendar_id;
			Repro_CT_Suite_Logger::log( "Event calendarId: {$event_calendar_id}, Ziel: {$external_calendar_id}, Match: " . ($matches ? 'YES' : 'NO') );
			return $matches;
		}
		
		// Prüfung 4: appointment mit calendar
		if ( isset( $event['appointment'] ) && isset( $event['appointment']['calendar'] ) && isset( $event['appointment']['calendar']['id'] ) ) {
			$event_calendar_id = (string) $event['appointment']['calendar']['id'];
			$matches = $event_calendar_id === (string) $external_calendar_id;
			Repro_CT_Suite_Logger::log( "Event appointment.calendar.id: {$event_calendar_id}, Ziel: {$external_calendar_id}, Match: " . ($matches ? 'YES' : 'NO') );
			return $matches;
		}
		
		// Debug: Verfügbare Keys loggen
		if ( isset( $event['id'] ) ) {
			$available_keys = implode( ', ', array_keys( $event ) );
			Repro_CT_Suite_Logger::log( "Event {$event['id']} verfügbare Keys: {$available_keys}" );
		}
		
		Repro_CT_Suite_Logger::log( "Event hat keine erkannten Kalender-Informationen - ÜBERSPRUNGEN" );
		return false;
	}
	
	/**
	 * Prüft ob ein Appointment für den spezifischen Kalender relevant ist
	 *
	 * @param array  $appointment Appointment-Daten aus der ChurchTools API
	 * @param string $external_calendar_id Ziel-Kalender-ID
	 * @return bool
	 */
	private function is_appointment_relevant_for_calendar( $appointment, $external_calendar_id ) {
		// Appointments haben normalerweise einen direkten calendar_id
		if ( isset( $appointment['calendar_id'] ) ) {
			return (string) $appointment['calendar_id'] === (string) $external_calendar_id;
		}
		
		// Alternativ: calendar-Objekt prüfen
		if ( isset( $appointment['calendar'] ) && isset( $appointment['calendar']['id'] ) ) {
			return (string) $appointment['calendar']['id'] === (string) $external_calendar_id;
		}
		
		return false;
	}
	
	/**
	 * Verarbeitet ein einzelnes Event aus der ChurchTools Events-API
	 *
	 * @param array  $event Event-Daten aus der API
	 * @param string $calendar_id ChurchTools Kalender-ID
	 * @return array|WP_Error { action: 'inserted'|'updated'|'skipped', event_id: int }
	 */
	private function process_event( $event, $calendar_id ) {
		// Event-Daten extrahieren und normalisieren
		$extract_result = $this->extract_event_data( $event, $calendar_id );
		
		if ( is_wp_error( $extract_result ) ) {
			return $extract_result;
		}

		$event_data = $extract_result;

		// Event in die Datenbank speichern (Insert oder Update)
		$exists = $this->events_repo->get_by_external_id( $event_data['external_id'] );
		
		if ( $exists ) {
			// Update
			$success = $this->events_repo->update_by_id( $exists->id, $event_data );
			$action = 'updated';
			$event_id = $exists->id;
		} else {
			// Insert
			$event_id = $this->events_repo->insert( $event_data );
			$success = ! is_wp_error( $event_id );
			$action = 'inserted';
		}

		if ( ! $success || is_wp_error( $event_id ) ) {
			return new WP_Error( 'save_failed', 'Event konnte nicht gespeichert werden' );
		}

		return array(
			'action'   => $action,
			'event_id' => $event_id,
		);
	}
	
	/**
	 * Extrahiert Event-Daten für die Datenbank aus ChurchTools Events-API
	 *
	 * @param array  $event Rohe Event-Daten aus der API
	 * @param string $calendar_id ChurchTools Kalender-ID
	 * @return array|WP_Error Event-Daten für die Datenbank
	 */
	private function extract_event_data( $event, $calendar_id ) {
		if ( ! isset( $event['id'] ) ) {
			return new WP_Error( 'missing_event_id', 'Event hat keine ID' );
		}

		// Basis-Daten extrahieren
		$event_data = array(
			'external_id'    => (string) $event['id'],
			'calendar_id'    => $calendar_id,
			'title'          => $event['name'] ?? $event['designation'] ?? 'Unbenannt',
			'description'    => $event['note'] ?? '',
			'location_name'  => '',
			'start_datetime' => '',
			'end_datetime'   => '',
			'status'         => 'active',
		);

		// Zeitdaten aus Event-Struktur extrahieren
		if ( isset( $event['startDate'] ) ) {
			$event_data['start_datetime'] = $this->normalize_datetime( $event['startDate'] );
		}
		if ( isset( $event['endDate'] ) ) {
			$event_data['end_datetime'] = $this->normalize_datetime( $event['endDate'] );
		}

		// Location aus verschiedenen möglichen Feldern
		if ( isset( $event['location'] ) ) {
			$event_data['location_name'] = $event['location'];
		} elseif ( isset( $event['address'] ) ) {
			$event_data['location_name'] = $event['address'];
		}

		return $event_data;
	}

	/**
	 * Verarbeitet einen einzelnen Appointment/Event aus der ChurchTools-API
	 *
	 * @param array  $appointment API-Payload des Termins
	 * @param string $calendar_id ChurchTools Kalender-ID
	 * @return array|WP_Error { action: 'inserted'|'updated'|'skipped', event_id: int }
	 */
	private function process_appointment( $appointment, $calendar_id ) {
		// Daten aus dem komplexen ChurchTools-Format extrahieren
		$extract_result = $this->extract_appointment_data( $appointment, $calendar_id );
		
		if ( is_wp_error( $extract_result ) ) {
			return $extract_result;
		}

		$event_data = $extract_result;

		// Event in die Datenbank speichern (Insert oder Update)
		$exists = $this->events_repo->get_by_external_id( $event_data['external_id'] );
		
		if ( $exists ) {
			// Update
			$success = $this->events_repo->update( $exists->id, $event_data );
			$action = 'updated';
			$event_id = $exists->id;
		} else {
			// Insert
			$event_id = $this->events_repo->insert( $event_data );
			$success = ! is_wp_error( $event_id );
			$action = 'inserted';
		}

		if ( ! $success || is_wp_error( $event_id ) ) {
			return new WP_Error( 'save_failed', 'Event konnte nicht gespeichert werden' );
		}

		// Schedule-Repository aktualisieren (falls vorhanden)
		if ( $this->schedule_repo && is_int( $event_id ) ) {
			$event = $this->events_repo->get_by_id( $event_id );
			if ( $event ) {
				$this->schedule_repo->upsert_from_event( $event );
			}
		}

		return array(
			'action'   => $action,
			'event_id' => $event_id,
		);
	}

	/**
	 * Extrahiert Event-Daten aus dem ChurchTools-Appointment-Format
	 *
	 * @param array  $appointment ChurchTools-Appointment-Payload
	 * @param string $calendar_id ChurchTools Kalender-ID
	 * @return array|WP_Error Event-Daten für die Datenbank
	 */
	private function extract_appointment_data( $appointment, $calendar_id ) {
		// ChurchTools liefert Appointments im Format:
		// { "appointment": { "base": {...}, "calculated": {...} } }
		// oder { "base": {...}, "calculated": {...} }

		$base_data       = $appointment['appointment']['base'] ?? $appointment['base'] ?? null;
		$calculated_data = $appointment['appointment']['calculated'] ?? $appointment['calculated'] ?? null;

		if ( ! $base_data ) {
			return new WP_Error( 'invalid_format', 'Kein base-Objekt im Appointment gefunden' );
		}

		// Basis-Daten extrahieren
		$appointment_id = $base_data['id'] ?? null;
		$title          = $base_data['caption'] ?? $base_data['title'] ?? 'Unbenannter Termin';
		$description    = $base_data['note'] ?? $base_data['description'] ?? '';

		// Berechnete Zeiten verwenden (falls vorhanden), sonst Basis-Zeiten
		$start_raw = $calculated_data['startDate'] ?? $base_data['startDate'] ?? null;
		$end_raw   = $calculated_data['endDate'] ?? $base_data['endDate'] ?? null;

		if ( ! $start_raw ) {
			return new WP_Error( 'missing_start', 'Kein Startdatum gefunden' );
		}

		// Zeiten normalisieren
		$start_dt = gmdate( 'Y-m-d H:i:s', strtotime( $start_raw ) );
		$end_dt   = $end_raw ? gmdate( 'Y-m-d H:i:s', strtotime( $end_raw ) ) : null;

		// Eindeutige External-ID generieren
		// Format: appointment_id + Startzeit für eindeutige Instanzen
		$external_id = $appointment_id . '_' . gmdate( 'Ymd_His', strtotime( $start_raw ) );

		// Event-Daten zusammenstellen
		$event_data = array(
			'external_id'     => $external_id,
			'calendar_id'     => $calendar_id, // ChurchTools Kalender-ID
			'appointment_id'  => $appointment_id,
			'title'           => sanitize_text_field( $title ),
			'description'     => wp_kses_post( $description ),
			'start_datetime'  => $start_dt,
			'end_datetime'    => $end_dt,
			'location_name'   => null, // TODO: Falls verfügbar aus Appointment extrahieren
			'status'          => null,
			'raw_payload'     => wp_json_encode( $appointment ),
		);

		return $event_data;
	}
}