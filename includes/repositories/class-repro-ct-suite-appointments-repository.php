<?php
/**
 * Appointments Repository
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Repro_CT_Suite_Appointments_Repository extends Repro_CT_Suite_Repository_Base {
	public function __construct() {
		parent::__construct( $this->db->prefix . 'rcts_appointments' );
	}

	public function upsert_by_external_id( $data ) {
		$defaults = array(
			'external_id'   => '',
			'event_id'      => null,
			'calendar_id'   => null,
			'title'         => '',
			'description'   => null,
			'start_datetime'=> null,
			'end_datetime'  => null,
			'is_all_day'    => 0,
			'raw_payload'   => null,
		);
		$data = wp_parse_args( $data, $defaults );

		$data['updated_at'] = $this->now();

		$existing_id = $this->db->get_var( $this->db->prepare( "SELECT id FROM {$this->table} WHERE external_id=%s", $data['external_id'] ) );
		if ( $existing_id ) {
			$this->db->update( $this->table, $data, array( 'id' => $existing_id ) );
			return (int) $existing_id;
		}

		$this->db->insert( $this->table, $data );
		return (int) $this->db->insert_id;
	}

	public function query_without_event( $args = array() ) {
		$defaults = array(
			'from' => null,
			'to'   => null,
			'limit'=> 50,
			'offset'=> 0,
		);
		$args = wp_parse_args( $args, $defaults );

		$where = 'WHERE event_id IS NULL';
		$params = array();
		if ( ! empty( $args['from'] ) ) {
			$where .= ' AND start_datetime >= %s';
			$params[] = $args['from'];
		}
		if ( ! empty( $args['to'] ) ) {
			$where .= ' AND start_datetime <= %s';
			$params[] = $args['to'];
		}
		$params[] = (int) $args['limit'];
		$params[] = (int) $args['offset'];

		$sql = $this->db->prepare(
			"SELECT * FROM {$this->table} {$where} ORDER BY start_datetime ASC LIMIT %d OFFSET %d",
			...$params
		);
		return $this->db->get_results( $sql );
	}

	/**
	 * Holt kombinierte Termine (Appointments mit zugeordneten Events + standalone Appointments)
	 *
	 * Berücksichtigt nur Termine aus ausgewählten Kalendern (is_selected=1).
	 *
	 * @param array $args Query-Parameter (from, to, limit, offset, selected_only).
	 * @return array Kombinierte Termine-Liste.
	 */
	public function get_combined_appointments( $args = array() ) {
		$defaults = array(
			'from'          => null,
			'to'            => null,
			'limit'         => 50,
			'offset'        => 0,
			'selected_only' => true,
		);
		$args = wp_parse_args( $args, $defaults );

		$events_table    = $this->db->prefix . 'rcts_events';
		$calendars_table = $this->db->prefix . 'rcts_calendars';
		
		$where = 'WHERE 1=1';
		$params = array();
		
		// Nur Termine aus ausgewählten Kalendern
		if ( $args['selected_only'] ) {
			$where .= ' AND (c.is_selected = 1 OR a.calendar_id IS NULL)';
		}
		
		if ( ! empty( $args['from'] ) ) {
			$where .= ' AND a.start_datetime >= %s';
			$params[] = $args['from'];
		}
		if ( ! empty( $args['to'] ) ) {
			$where .= ' AND a.start_datetime <= %s';
			$params[] = $args['to'];
		}
		
		$params[] = (int) $args['limit'];
		$params[] = (int) $args['offset'];

		// LEFT JOIN mit Events und Calendars um kombinierte Daten zu erhalten
		$sql = $this->db->prepare(
			"SELECT 
				a.id,
				a.external_id,
				a.event_id,
				a.calendar_id,
				a.title AS appointment_title,
				a.description AS appointment_description,
				a.start_datetime,
				a.end_datetime,
				a.is_all_day,
				e.title AS event_title,
				e.description AS event_description,
				e.location_name,
				e.status,
				c.name AS calendar_name,
				c.color AS calendar_color,
				a.created_at,
				a.updated_at
			FROM {$this->table} a
			LEFT JOIN {$events_table} e ON a.event_id = e.id
			LEFT JOIN {$calendars_table} c ON a.calendar_id = c.id
			{$where}
			ORDER BY a.start_datetime ASC
			LIMIT %d OFFSET %d",
			...$params
		);

		$results = $this->db->get_results( $sql );
		
		// Daten aufbereiten: Event-Titel bevorzugen, falls vorhanden
		foreach ( $results as &$row ) {
			$row->title = ! empty( $row->event_title ) ? $row->event_title : $row->appointment_title;
			$row->description = ! empty( $row->event_description ) ? $row->event_description : $row->appointment_description;
			$row->source = ! empty( $row->event_id ) ? 'event' : 'appointment';
		}
		
		return $results;
	}

	/**
	 * Zählt alle kombinierten Termine
	 *
	 * Berücksichtigt nur Termine aus ausgewählten Kalendern (is_selected=1).
	 *
	 * @param array $args Query-Parameter (from, to, selected_only).
	 * @return int Anzahl der Termine.
	 */
	public function count_combined_appointments( $args = array() ) {
		$defaults = array(
			'from'          => null,
			'to'            => null,
			'selected_only' => true,
		);
		$args = wp_parse_args( $args, $defaults );

		$calendars_table = $this->db->prefix . 'rcts_calendars';
		
		$where = 'WHERE 1=1';
		$params = array();
		
		// Nur Termine aus ausgewählten Kalendern
		if ( $args['selected_only'] ) {
			$where .= ' AND (c.is_selected = 1 OR a.calendar_id IS NULL)';
		}
		
		if ( ! empty( $args['from'] ) ) {
			$where .= ' AND a.start_datetime >= %s';
			$params[] = $args['from'];
		}
		if ( ! empty( $args['to'] ) ) {
			$where .= ' AND a.start_datetime <= %s';
			$params[] = $args['to'];
		}

		if ( empty( $params ) ) {
			$sql = "SELECT COUNT(*) FROM {$this->table} a
				LEFT JOIN {$calendars_table} c ON a.calendar_id = c.id
				{$where}";
			return (int) $this->db->get_var( $sql );
		}

		$sql = $this->db->prepare(
			"SELECT COUNT(*) FROM {$this->table} a
			LEFT JOIN {$calendars_table} c ON a.calendar_id = c.id
			{$where}",
			...$params
		);
		return (int) $this->db->get_var( $sql );
	}
}
