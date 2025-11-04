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
	 * @param array $args { calendar_ids: int[] (ChurchTools externe IDs), from: Y-m-d, to: Y-m-d }
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
		Repro_CT_Suite_Logger::log( 'Ausgewählte Kalender (ChurchTools-IDs): ' . implode( ', ', $args['calendar_ids'] ) );

		// Direkte Verwendung der ChurchTools-IDs (kein lokales Mapping mehr!)
		$external_calendar_ids = array();
		foreach ( $args['calendar_ids'] as $calendar_id ) {
			// Optional: Kalender-Name aus der lokalen Tabelle holen für bessere Logs
			$calendar = $this->calendars_repo->get_by_calendar_id( $calendar_id );
			$external_calendar_ids[] = array(
				'calendar_id' => $calendar_id,
				'name'        => $calendar ? $calendar->name : "Kalender {$calendar_id}",
			);
			Repro_CT_Suite_Logger::log( "Kalender: ChurchTools-ID {$calendar_id}" . ( $calendar ? " ('{$calendar->name}')" : '' ) );
		}

		if ( empty( $external_calendar_ids ) ) {
			return new WP_Error( 'no_calendar_ids', __( 'Keine ChurchTools-Kalender-IDs übergeben.', 'repro-ct-suite' ) );
		}

		Repro_CT_Suite_Logger::log( 'Externe Kalender-IDs: ' . implode( ', ', array_column( $external_calendar_ids, 'calendar_id' ) ) );

		$stats = array(
			'calendars_processed' => 0,
			'events_found'        => 0,
			'events_inserted'     => 0,
			'events_updated'      => 0,
			'events_skipped'      => 0,
			'errors'              => 0,
		);

		// OPTIMIERUNG: Einmal alle Events abrufen, nicht pro Kalender
		$all_events_result = $this->fetch_all_events( $args );
		if ( is_wp_error( $all_events_result ) ) {
			return $all_events_result;
		}
		
		$all_events = $all_events_result['events'];
		$total_events_from_api = count( $all_events );
		
		Repro_CT_Suite_Logger::log( "API lieferte {$total_events_from_api} Events (alle Kalender)" );

		// Pro Kalender: Events filtern und speichern  
		foreach ( $external_calendar_ids as $cal_info ) {
			$external_id = $cal_info['calendar_id'];
			$cal_name    = $cal_info['name'];
			
			Repro_CT_Suite_Logger::log( "Bearbeite Kalender '{$cal_name}' (ID: {$external_id})..." );
			
			// Events für diesen Kalender filtern
			$relevant_events = array();
			foreach ( $all_events as $event ) {
				if ( $this->is_event_relevant_for_calendar( $event, $calendar_id ) ) {
					$relevant_events[] = $event;
				}
			}
			
			$events_found = count( $relevant_events );
			Repro_CT_Suite_Logger::log( "Kalender {$external_id}: {$events_found} relevante Events gefunden" );
			
			// Events verarbeiten (mit args für Phase 2 Appointments)
			$result = $this->process_calendar_events( $relevant_events, $calendar_id, $args );
			
			if ( is_wp_error( $result ) ) {
				Repro_CT_Suite_Logger::log( "Fehler bei Kalender {$calendar_id}: " . $result->get_error_message(), 'error' );
				$stats['errors']++;
				continue; // Nächsten Kalender versuchen
			}
			
			// Statistiken aggregieren
			$stats['calendars_processed']++;
			$stats['events_found']    += $result['events_found'];
			$stats['events_inserted'] += $result['events_inserted'];
			$stats['events_updated']  += $result['events_updated'];
			$stats['events_skipped']  += $result['events_skipped'];
			
			// Appointments-Statistiken aggregieren (falls vorhanden)
			if ( isset( $result['appointments_found'] ) ) {
				if ( ! isset( $stats['appointments_found'] ) ) {
					$stats['appointments_found'] = 0;
				}
				$stats['appointments_found'] += $result['appointments_found'];
			}
			
			Repro_CT_Suite_Logger::log( "Kalender '{$cal_name}': {$result['events_found']} gefunden, {$result['events_inserted']} neu, {$result['events_updated']} aktualisiert" );
		}

		// Schedule-Repository aktualisieren (falls vorhanden)
		if ( $this->schedule_repo ) {
			Repro_CT_Suite_Logger::log( 'Aktualisiere Schedule-Repository...' );
			$this->schedule_repo->rebuild_from_existing();
		}

		Repro_CT_Suite_Logger::separator();
		Repro_CT_Suite_Logger::log( 'UNIFIED SYNC ABGESCHLOSSEN (Events + Appointments)' );
		Repro_CT_Suite_Logger::log( "Kalender verarbeitet: {$stats['calendars_processed']}" );
		Repro_CT_Suite_Logger::log( "Events gefunden: {$stats['events_found']}" );
		Repro_CT_Suite_Logger::log( "Appointments gefunden: " . ($stats['appointments_found'] ?? 0) );
		Repro_CT_Suite_Logger::log( "Termine eingefügt: {$stats['events_inserted']}" );
		Repro_CT_Suite_Logger::log( "Termine aktualisiert: {$stats['events_updated']}" );
		Repro_CT_Suite_Logger::log( "Termine übersprungen: {$stats['events_skipped']}" );
		if ( $stats['errors'] > 0 ) {
			Repro_CT_Suite_Logger::log( "Fehler: {$stats['errors']}", 'warning' );
		}

		return $stats;
	}

	/**
	 * Holt alle Events einmalig von der API (optimiert)
	 *
	 * @param array $args Sync-Parameter (from, to)
	 * @return array|WP_Error {events: Event[], total: int}
	 */
	private function fetch_all_events( $args ) {
		Repro_CT_Suite_Logger::log( "API-Call: Alle Events abrufen..." );
		
		$endpoint = '/events';
		$response = $this->ct_client->get( $endpoint, array(
			'direction' => 'forward',
			'include'   => 'eventServices',
			'from'      => $args['from'],
			'to'        => $args['to'],
			'page'      => 1,
		) );

		if ( is_wp_error( $response ) ) {
			return new WP_Error( 'events_api_error', 'Events API Fehler: ' . $response->get_error_message() );
		}

		if ( ! isset( $response['data'] ) || ! is_array( $response['data'] ) ) {
			return new WP_Error( 'invalid_events_response', 'Ungültige Events API-Antwort' );
		}

		$events = $response['data'];
		$total = count( $events );
		
		Repro_CT_Suite_Logger::log( "API lieferte {$total} Events (alle Kalender)" );
		
		return array(
			'events' => $events,
			'total'  => $total,
		);
	}

	/**
	 * Verarbeitet Events für einen spezifischen Kalender (optimiert)
	 *
	 * @param array  $events Gefilterte Events für diesen Kalender
	 * @param string $external_calendar_id ChurchTools Kalender-ID
	 * @param array  $args Sync-Parameter (from, to) für Appointments-Abruf
	 * @return array|WP_Error Verarbeitungs-Statistiken
	 */
	private function process_calendar_events( $events, $external_calendar_id, $args = array() ) {
		$stats = array(
			'events_found'       => count( $events ),
			'appointments_found' => 0,
			'events_inserted'    => 0,
			'events_updated'     => 0,
			'events_skipped'     => 0,
		);
		
		$imported_appointment_ids = array();

		// Phase 1: Events verarbeiten
		foreach ( $events as $event ) {
			// Appointment-IDs sammeln für Phase 2
			if ( isset( $event['appointment'] ) && isset( $event['appointment']['id'] ) ) {
				$imported_appointment_ids[] = $event['appointment']['id'];
			}
			
			// Event verarbeiten und speichern
			Repro_CT_Suite_Logger::log( "Event {$event['id']} - Starte process_event()" );
			$result = $this->process_event( $event, $external_calendar_id );
			
			if ( is_wp_error( $result ) ) {
				Repro_CT_Suite_Logger::log( "Event {$event['id']} - process_event FEHLER: " . $result->get_error_message(), 'error' );
				$stats['events_skipped']++;
				continue;
			}
			
			Repro_CT_Suite_Logger::log( "Event {$event['id']} - process_event ERFOLGREICH: Action=" . $result['action'] . ", ID=" . $result['event_id'] );
			
			if ( $result['action'] === 'inserted' ) {
				$stats['events_inserted']++;
			} elseif ( $result['action'] === 'updated' ) {
				$stats['events_updated']++;
			}
		}
		
		// Phase 2: Appointments ohne Events importieren
		if ( ! empty( $args ) && isset( $args['from'] ) && isset( $args['to'] ) ) {
			Repro_CT_Suite_Logger::log( "Phase 2: Starte Appointments-Import für Kalender {$external_calendar_id}" );
			$appointments_result = $this->sync_phase2_appointments( $external_calendar_id, $args, $imported_appointment_ids );
			
			if ( is_wp_error( $appointments_result ) ) {
				Repro_CT_Suite_Logger::log( "Phase 2 Fehler: " . $appointments_result->get_error_message(), 'error' );
				// Nicht abbrechen, Phase 1 war ja erfolgreich
			} else {
				// Statistiken von Phase 2 übernehmen
				$stats['appointments_found'] = $appointments_result['appointments_found'];
				$stats['events_inserted'] += $appointments_result['events_inserted'];
				$stats['events_updated'] += $appointments_result['events_updated'];
				$stats['events_skipped'] += $appointments_result['events_skipped'];
				
				Repro_CT_Suite_Logger::log( "Phase 2 abgeschlossen: {$appointments_result['appointments_found']} Appointments gefunden, {$appointments_result['events_inserted']} neu importiert" );
			}
		} else {
			Repro_CT_Suite_Logger::log( "Phase 2 übersprungen: Keine Zeitraum-Parameter vorhanden" );
		}

		return $stats;
	}

	/**
	 * Synchronisiert Events eines einzelnen Kalenders - NUR PHASE 1 (Events API)
	 *
	 * Temporär vereinfacht: Nur Events API ohne Appointments für bessere Diagnose
	 *
	 * @param string $external_calendar_id ChurchTools Kalender-ID
	 * @param array  $args Sync-Parameter (from, to)
	 * @return array|WP_Error Einzelkalender-Statistiken
	 */
	private function sync_calendar_events( $external_calendar_id, $args ) {
		Repro_CT_Suite_Logger::log( "=== UNIFIED SYNC für Kalender {$external_calendar_id} ===" );
		
		$stats = array(
			'events_found'       => 0,
			'appointments_found' => 0,
			'events_inserted'    => 0,
			'events_updated'     => 0,
			'events_skipped'     => 0,
		);
		
		$imported_appointment_ids = array(); // Für spätere Nutzung
		
		// NUR PHASE 1: Events API
		Repro_CT_Suite_Logger::log( "Phase 2 (Appointments) temporär deaktiviert - nur Events werden synchronisiert" );
		$events_result = $this->sync_phase1_events( $external_calendar_id, $args, $imported_appointment_ids );
		if ( is_wp_error( $events_result ) ) {
			return $events_result;
		}
		
		// Statistiken von Phase 1 übernehmen
		$stats['events_found'] = $events_result['events_found'];
		$stats['events_inserted'] += $events_result['events_inserted'];
		$stats['events_updated'] += $events_result['events_updated'];
		$stats['events_skipped'] += $events_result['events_skipped'];
		
		// PHASE 2: Appointments ohne Events importieren
		Repro_CT_Suite_Logger::log( "Phase 2: Starte Appointments-Import (ohne bereits importierte Events)" );
		$appointments_result = $this->sync_phase2_appointments( $external_calendar_id, $args, $imported_appointment_ids );
		
		if ( is_wp_error( $appointments_result ) ) {
			Repro_CT_Suite_Logger::log( "Phase 2 Fehler: " . $appointments_result->get_error_message(), 'error' );
			// Nicht abbrechen, Phase 1 war ja erfolgreich
		} else {
			// Statistiken von Phase 2 übernehmen
			$stats['appointments_found'] = $appointments_result['appointments_found'];
			$stats['events_inserted'] += $appointments_result['events_inserted'];
			$stats['events_updated'] += $appointments_result['events_updated'];
			$stats['events_skipped'] += $appointments_result['events_skipped'];
			
			Repro_CT_Suite_Logger::log( "Phase 2 abgeschlossen: {$appointments_result['appointments_found']} Appointments gefunden, {$appointments_result['events_inserted']} neu importiert" );
		}
		
		$total_processed = $stats['events_found'] + ($stats['appointments_found'] ?? 0);
		$total_imported = $stats['events_inserted'] + $stats['events_updated'];
		
		Repro_CT_Suite_Logger::log( "Unified Sync Ergebnis: {$total_processed} gefunden ({$stats['events_found']} Events + " . ($stats['appointments_found'] ?? 0) . " Appointments), {$total_imported} importiert" );
		
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
		// KORREKTUR: Alle Events abrufen, dann clientseitig nach Kalender filtern
		$response = $this->ct_client->get( $endpoint, array(
			'direction' => 'forward',
			'include'   => 'eventServices',
			'from'      => $args['from'],
			'to'        => $args['to'],
			'page'      => 1,
		) );

		if ( is_wp_error( $response ) ) {
			return new WP_Error( 'events_api_error', 'Events API Fehler: ' . $response->get_error_message() );
		}

		if ( ! isset( $response['data'] ) || ! is_array( $response['data'] ) ) {
			return new WP_Error( 'invalid_events_response', 'Ungültige Events API-Antwort' );
		}

		$all_events = $response['data'];
		$total_events_found = count( $all_events );
		
		Repro_CT_Suite_Logger::log( "Phase 1: {$total_events_found} Events (alle Kalender) gefunden, filtere für Kalender {$external_calendar_id}" );

		$stats = array(
			'events_found'    => 0, // Wird nach Filterung gesetzt
			'events_inserted' => 0,
			'events_updated'  => 0,
			'events_skipped'  => 0,
		);

		// Filtere Events für den spezifischen Kalender
		$relevant_events = array();
		foreach ( $all_events as $event ) {
			if ( $this->is_event_relevant_for_calendar( $event, $external_calendar_id ) ) {
				$relevant_events[] = $event;
			}
		}
		
		$events_found = count( $relevant_events );
		$stats['events_found'] = $events_found;
		
		Repro_CT_Suite_Logger::log( "Phase 1: {$events_found} relevante Events für Kalender {$external_calendar_id} gefunden" );

		foreach ( $relevant_events as $event ) {
			// Event ist bereits als relevant validiert
			
			// Appointment-IDs sammeln für Phase 2
			if ( isset( $event['appointment'] ) && isset( $event['appointment']['id'] ) ) {
				$imported_appointment_ids[] = $event['appointment']['id'];
			}
			
			// Event verarbeiten und speichern
			$result = $this->process_event( $event, $external_calendar_id );
			
			if ( is_wp_error( $result ) ) {
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
		Repro_CT_Suite_Logger::log( "========================================" );
		Repro_CT_Suite_Logger::log( "Phase 2: Appointments API für Kalender {$external_calendar_id}" );
		Repro_CT_Suite_Logger::log( "Phase 2: Bereits importierte Appointment-IDs: " . implode( ', ', $imported_appointment_ids ) );
		
		$endpoint = '/calendars/' . rawurlencode( $external_calendar_id ) . '/appointments';
		
		// Vollständige URL für Transparenz
		$tenant = get_option( 'repro_ct_suite_ct_tenant', '' );
		$full_url = "https://{$tenant}.church.tools/api{$endpoint}?from={$args['from']}&to={$args['to']}";
		
		// Cookies für cURL-Befehl holen
		$saved_cookies = get_option( 'repro_ct_suite_ct_cookies', array() );
		$cookie_header = '';
		if ( ! empty( $saved_cookies ) ) {
			$cookie_parts = array();
			foreach ( $saved_cookies as $name => $value ) {
				$cookie_parts[] = $name . '=' . $value;
			}
			$cookie_header = implode( '; ', $cookie_parts );
		}
		
		Repro_CT_Suite_Logger::log( "========================================" );
		Repro_CT_Suite_Logger::log( "🌐 API GET REQUEST:" );
		Repro_CT_Suite_Logger::log( "   URL: {$full_url}" );
		Repro_CT_Suite_Logger::log( "   Endpoint: {$endpoint}" );
		Repro_CT_Suite_Logger::log( "   Parameter: from={$args['from']}, to={$args['to']}" );
		Repro_CT_Suite_Logger::log( "   Cookies: " . ( ! empty( $saved_cookies ) ? count( $saved_cookies ) . " Cookie(s) vorhanden" : "❌ KEINE COOKIES!" ) );
		Repro_CT_Suite_Logger::log( "" );
		Repro_CT_Suite_Logger::log( "📋 cURL-Befehl zum Testen:" );
		Repro_CT_Suite_Logger::log( "   curl -X GET '{$full_url}' \\" );
		Repro_CT_Suite_Logger::log( "     -H 'accept: application/json' \\" );
		Repro_CT_Suite_Logger::log( "     -H 'Content-Type: application/json' \\" );
		if ( ! empty( $cookie_header ) ) {
			Repro_CT_Suite_Logger::log( "     -H 'Cookie: {$cookie_header}'" );
		} else {
			Repro_CT_Suite_Logger::log( "     # ⚠️ KEINE COOKIES - Request wird vermutlich 401 Unauthorized zurückgeben" );
		}
		Repro_CT_Suite_Logger::log( "========================================" );
		
		$response = $this->ct_client->get( $endpoint, array(
			'from' => $args['from'],
			'to'   => $args['to'],
		) );

		if ( is_wp_error( $response ) ) {
			return new WP_Error( 'appointments_api_error', 'Appointments API Fehler: ' . $response->get_error_message() );
		}

		Repro_CT_Suite_Logger::log( "Phase 2: API-Response erhalten, prüfe Struktur..." );
		
		if ( ! isset( $response['data'] ) || ! is_array( $response['data'] ) ) {
			Repro_CT_Suite_Logger::log( "Phase 2: Response verfügbare Keys: " . implode( ', ', array_keys( $response ) ), 'error' );
			return new WP_Error( 'invalid_appointments_response', 'Ungültige Appointments API-Antwort' );
		}

		$appointments = $response['data'];
		$appointments_found = count( $appointments );
		
		Repro_CT_Suite_Logger::log( "Phase 2: {$appointments_found} Appointments gefunden in API-Response" );
		
		// DEBUG: Erste Appointment-Struktur analysieren
		if ( $appointments_found > 0 ) {
			$first_appointment = $appointments[0];
			Repro_CT_Suite_Logger::log( "Phase 2: Struktur des ersten Appointments analysieren..." );
			Repro_CT_Suite_Logger::log( "Phase 2: Appointment Keys: " . implode( ', ', array_keys( $first_appointment ) ) );
			
			if ( isset( $first_appointment['id'] ) ) {
				Repro_CT_Suite_Logger::log( "Phase 2: Appointment ID: {$first_appointment['id']}" );
			}
			if ( isset( $first_appointment['caption'] ) ) {
				Repro_CT_Suite_Logger::log( "Phase 2: Appointment Caption: {$first_appointment['caption']}" );
			}
			if ( isset( $first_appointment['base'] ) ) {
				Repro_CT_Suite_Logger::log( "Phase 2: Appointment hat 'base' - Keys: " . implode( ', ', array_keys( $first_appointment['base'] ) ) );
			}
			if ( isset( $first_appointment['calculated'] ) ) {
				Repro_CT_Suite_Logger::log( "Phase 2: Appointment hat 'calculated' - Keys: " . implode( ', ', array_keys( $first_appointment['calculated'] ) ) );
			}
			
			// Vollständige Struktur loggen
			Repro_CT_Suite_Logger::log( "Phase 2: Komplette Struktur des ersten Appointments:" );
			Repro_CT_Suite_Logger::log( print_r( $first_appointment, true ) );
		}
		
		Repro_CT_Suite_Logger::log( "Phase 2: {$appointments_found} Appointments gefunden" );

		$stats = array(
			'appointments_found' => $appointments_found,
			'events_inserted' => 0,
			'events_updated'  => 0,
			'events_skipped'  => 0,
		);
		
		$skipped_already_imported = 0;
		$skipped_wrong_calendar = 0;

		foreach ( $appointments as $appointment_data ) {
			// ChurchTools API liefert zwei Formate:
			// 1. NEU: { "appointment": { "base": {...}, "calculated": {...} }, "base": {...}, "calculated": {...} }
			// 2. DEPRECATED: { "base": {...}, "calculated": {...} }
			// Wir verwenden das neue Format wenn vorhanden, sonst deprecated
			$appointment = isset( $appointment_data['appointment'] ) ? $appointment_data['appointment'] : $appointment_data;
			
			// Appointment-ID aus base extrahieren
			$appointment_id = isset( $appointment['base']['id'] ) ? $appointment['base']['id'] : null;
			
			if ( ! $appointment_id ) {
				Repro_CT_Suite_Logger::log( "Phase 2: Appointment ohne ID gefunden - übersprungen", 'warning' );
				continue;
			}
			
			// Prüfung 1: Bereits als Event importiert?
			if ( in_array( $appointment_id, $imported_appointment_ids ) ) {
				$skipped_already_imported++;
				continue;
			}
			
			// Prüfung 2: Kalender-ID zugelassen?
			if ( ! $this->is_appointment_relevant_for_calendar( $appointment, $external_calendar_id ) ) {
				$skipped_wrong_calendar++;
				continue;
			}
			
			Repro_CT_Suite_Logger::log( "Phase 2: Zusätzliches Appointment {$appointment_id} wird importiert" );
			
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
		// Prüfung 1: calendar.domainIdentifier (richtige ChurchTools Event-Struktur)
		if ( isset( $event['calendar'] ) && isset( $event['calendar']['domainIdentifier'] ) ) {
			$event_calendar_id = (string) $event['calendar']['domainIdentifier'];
			return $event_calendar_id === (string) $external_calendar_id;
		}
		
		// Prüfung 1b: Fallback für ältere calendar.id Struktur
		if ( isset( $event['calendar'] ) && isset( $event['calendar']['id'] ) ) {
			$event_calendar_id = (string) $event['calendar']['id'];
			return $event_calendar_id === (string) $external_calendar_id;
		}
		
		// Prüfung 2: calendars Array mit domainIdentifier
		if ( isset( $event['calendars'] ) && is_array( $event['calendars'] ) ) {
			foreach ( $event['calendars'] as $calendar ) {
				if ( isset( $calendar['domainIdentifier'] ) ) {
					$event_calendar_id = (string) $calendar['domainIdentifier'];
					if ( $event_calendar_id === (string) $external_calendar_id ) {
						return true;
					}
				}
				// Fallback für ältere id-Struktur
				if ( isset( $calendar['id'] ) ) {
					$event_calendar_id = (string) $calendar['id'];
					if ( $event_calendar_id === (string) $external_calendar_id ) {
						return true;
					}
				}
			}
		}
		
		// Prüfung 3: Direkte calendarId property
		if ( isset( $event['calendarId'] ) ) {
			$event_calendar_id = (string) $event['calendarId'];
			return $event_calendar_id === (string) $external_calendar_id;
		}
		
		// Prüfung 4: appointment mit calendar.domainIdentifier
		if ( isset( $event['appointment'] ) && isset( $event['appointment']['calendar'] ) && isset( $event['appointment']['calendar']['domainIdentifier'] ) ) {
			$event_calendar_id = (string) $event['appointment']['calendar']['domainIdentifier'];
			return $event_calendar_id === (string) $external_calendar_id;
		}
		
		// Prüfung 4b: Fallback für appointment.calendar.id
		if ( isset( $event['appointment'] ) && isset( $event['appointment']['calendar'] ) && isset( $event['appointment']['calendar']['id'] ) ) {
			$event_calendar_id = (string) $event['appointment']['calendar']['id'];
			return $event_calendar_id === (string) $external_calendar_id;
		}
		
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
		// Debug: Welche Kalender-Informationen hat das Appointment?
		$appointment_title = isset( $appointment['base']['title'] ) ? $appointment['base']['title'] : 
			( isset( $appointment['base']['caption'] ) ? $appointment['base']['caption'] : 'Unbekannt' );
		$has_calendar_id = isset( $appointment['calendar_id'] );
		$has_calendar_obj = isset( $appointment['calendar'] );
		$has_base_calendar = isset( $appointment['base']['calendar'] );
		
		Repro_CT_Suite_Logger::log( "Kalender-Check für '{$appointment_title}': calendar_id=" . ($has_calendar_id ? 'JA' : 'NEIN') . ", calendar=" . ($has_calendar_obj ? 'JA' : 'NEIN') . ", base.calendar=" . ($has_base_calendar ? 'JA' : 'NEIN') );
		
		// Appointments haben normalerweise einen direkten calendar_id
		if ( isset( $appointment['calendar_id'] ) ) {
			$match = (string) $appointment['calendar_id'] === (string) $external_calendar_id;
			Repro_CT_Suite_Logger::log( "calendar_id Check: {$appointment['calendar_id']} vs {$external_calendar_id} = " . ($match ? 'MATCH' : 'NO MATCH') );
			return $match;
		}
		
		// Alternativ: calendar-Objekt prüfen
		if ( isset( $appointment['calendar'] ) && isset( $appointment['calendar']['id'] ) ) {
			$match = (string) $appointment['calendar']['id'] === (string) $external_calendar_id;
			Repro_CT_Suite_Logger::log( "calendar.id Check: {$appointment['calendar']['id']} vs {$external_calendar_id} = " . ($match ? 'MATCH' : 'NO MATCH') );
			return $match;
		}
		
		// Standard: base.calendar prüfen (laut ChurchTools API Doku)
		if ( isset( $appointment['base']['calendar']['id'] ) ) {
			$match = (string) $appointment['base']['calendar']['id'] === (string) $external_calendar_id;
			Repro_CT_Suite_Logger::log( "base.calendar.id Check: {$appointment['base']['calendar']['id']} vs {$external_calendar_id} = " . ($match ? 'MATCH' : 'NO MATCH') );
			return $match;
		}
		
		Repro_CT_Suite_Logger::log( "Keine Kalender-ID gefunden - REJECTED" );
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
		$exists = $this->events_repo->get_by_event_id( $event_data['event_id'] );
		
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
			$error_msg = is_wp_error( $event_id ) ? $event_id->get_error_message() : 'Unbekannter Fehler';
			return new WP_Error( 'save_failed', 'Event konnte nicht gespeichert werden: ' . $error_msg );
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
			'event_id'       => (string) $event['id'],
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
			$event_data['start_datetime'] = $this->format_datetime_for_db( $event['startDate'] );
		}
		if ( isset( $event['endDate'] ) ) {
			$event_data['end_datetime'] = $this->format_datetime_for_db( $event['endDate'] );
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
		Repro_CT_Suite_Logger::log( "process_appointment: START für Kalender {$calendar_id}" );
		
		// Daten aus dem komplexen ChurchTools-Format extrahieren
		$extract_result = $this->extract_appointment_data( $appointment, $calendar_id );
		
		if ( is_wp_error( $extract_result ) ) {
			Repro_CT_Suite_Logger::log( "process_appointment: extract_appointment_data FEHLER: " . $extract_result->get_error_message(), 'error' );
			return $extract_result;
		}

		$event_data = $extract_result;
		
		Repro_CT_Suite_Logger::log( "process_appointment: Event-Daten extrahiert, event_id={$event_data['event_id']}" );

		// Event in die Datenbank speichern (Insert oder Update)
		$exists = $this->events_repo->get_by_event_id( $event_data['event_id'] );
		
		if ( $exists ) {
			// Event existiert bereits - ÜBERSPRINGEN statt Update
			Repro_CT_Suite_Logger::log( "process_appointment: Event existiert bereits (ID={$exists->id}) - ÜBERSPRUNGEN (kein Update)" );
			return array(
				'action'   => 'skipped',
				'event_id' => $exists->id,
			);
		} else {
			Repro_CT_Suite_Logger::log( "process_appointment: Event ist neu, führe INSERT aus" );
			Repro_CT_Suite_Logger::log( "process_appointment: INSERT - Event-Daten: " . wp_json_encode( array(
				'event_id' => $event_data['event_id'],
				'title' => $event_data['title'],
				'start_datetime' => $event_data['start_datetime'],
				'end_datetime' => $event_data['end_datetime'],
			) ) );
			
			// Insert
			try {
				$event_id = $this->events_repo->insert( $event_data );
				$success = ! is_wp_error( $event_id );
				$action = 'inserted';
				
				if ( $success ) {
					Repro_CT_Suite_Logger::log( "process_appointment: INSERT ERFOLGREICH, neue Event-ID={$event_id}" );
				} else {
					$error_msg = is_wp_error( $event_id ) ? $event_id->get_error_message() : 'Unbekannter Fehler';
					Repro_CT_Suite_Logger::log( "process_appointment: INSERT FEHLGESCHLAGEN: {$error_msg}", 'error' );
				}
			} catch ( Exception $e ) {
				Repro_CT_Suite_Logger::log( "process_appointment: INSERT EXCEPTION: " . $e->getMessage(), 'error' );
				return new WP_Error( 'insert_exception', 'Insert Exception: ' . $e->getMessage() );
			}
		}

		if ( ! $success || is_wp_error( $event_id ) ) {
			Repro_CT_Suite_Logger::log( "process_appointment: Speichern fehlgeschlagen", 'error' );
			return new WP_Error( 'save_failed', 'Event konnte nicht gespeichert werden' );
		}

		// Schedule-Repository aktualisieren (falls vorhanden)
		if ( $this->schedule_repo && is_int( $event_id ) ) {
			Repro_CT_Suite_Logger::log( "process_appointment: Aktualisiere Schedule-Repository für Event-ID={$event_id}" );
			$event = $this->events_repo->get_by_id( $event_id );
			if ( $event ) {
				// Event-Objekt in Array konvertieren für schedule_repo
				$event_array = (array) $event;
				$this->schedule_repo->upsert_from_event( $event_array );
				Repro_CT_Suite_Logger::log( "process_appointment: Schedule aktualisiert" );
			}
		}

		Repro_CT_Suite_Logger::log( "process_appointment: ABGESCHLOSSEN - action={$action}, event_id={$event_id}" );

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
		// ACHTUNG: In sync_phase2_appointments() wird bereits auf { "base": {...}, "calculated": {...} } normalisiert!

		$base_data       = $appointment['base'] ?? null;
		$calculated_data = $appointment['calculated'] ?? null;

		if ( ! $base_data ) {
			Repro_CT_Suite_Logger::log( "extract_appointment_data: Keine base-Daten gefunden. Keys: " . implode( ', ', array_keys( $appointment ) ), 'error' );
			return new WP_Error( 'invalid_format', 'Kein base-Objekt im Appointment gefunden' );
		}

		// Basis-Daten extrahieren
		$appointment_id = $base_data['id'] ?? null;
		$title          = $base_data['title'] ?? $base_data['caption'] ?? 'Unbenannter Termin';
		$description    = $base_data['description'] ?? $base_data['note'] ?? '';

		Repro_CT_Suite_Logger::log( "extract_appointment_data: ID={$appointment_id}, Titel='{$title}'" );

		// Berechnete Zeiten verwenden (falls vorhanden), sonst Basis-Zeiten
		$start_raw = $calculated_data['startDate'] ?? $base_data['startDate'] ?? null;
		$end_raw   = $calculated_data['endDate'] ?? $base_data['endDate'] ?? null;

		if ( ! $start_raw ) {
			Repro_CT_Suite_Logger::log( "extract_appointment_data: Kein Startdatum gefunden", 'error' );
			return new WP_Error( 'missing_start', 'Kein Startdatum gefunden' );
		}

		// Zeiten normalisieren
		$start_dt = gmdate( 'Y-m-d H:i:s', strtotime( $start_raw ) );
		$end_dt   = $end_raw ? gmdate( 'Y-m-d H:i:s', strtotime( $end_raw ) ) : null;

		Repro_CT_Suite_Logger::log( "extract_appointment_data: Start={$start_dt}, End={$end_dt}" );

		// Eindeutige External-ID generieren
		// Format: appointment_id + Startzeit für eindeutige Instanzen
		$event_id = $appointment_id . '_' . gmdate( 'Ymd_His', strtotime( $start_raw ) );

		// Event-Daten zusammenstellen
		$event_data = array(
			'event_id'        => $event_id,
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

		Repro_CT_Suite_Logger::log( "extract_appointment_data: Event-Daten erfolgreich extrahiert (event_id={$event_id})" );

		return $event_data;
	}

	/**
	 * Formatiert Datetime für Datenbank (MySQL DATETIME Format)
	 *
	 * @param string $datetime ChurchTools DateTime
	 * @return string MySQL DATETIME (Y-m-d H:i:s)
	 */
	private function format_datetime_for_db( $datetime ) {
		if ( empty( $datetime ) ) {
			return '';
		}
		
		// ChurchTools liefert meist ISO 8601 Format
		$timestamp = strtotime( $datetime );
		if ( $timestamp === false ) {
			Repro_CT_Suite_Logger::log( "Warnung: Ungültiges Datumsformat: {$datetime}", 'warning' );
			return '';
		}
		
		return date( 'Y-m-d H:i:s', $timestamp );
	}
}