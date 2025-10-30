<?php
/**
 * Schedule Repository: kombinierte Liste aus Events und Appointments
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Repro_CT_Suite_Schedule_Repository extends Repro_CT_Suite_Repository_Base {
	public function __construct() {
		global $wpdb;
		parent::__construct( $wpdb->prefix . 'rcts_schedule' );
	}

	/**
	 * Upsert eines Schedulesatzes aus einem Event-Datensatz
	 *
	 * @param array $event Array mit Event-Feldern (id, external_id, calendar_id, title, description, start_datetime, end_datetime, location_name, status)
	 * @return int Lokale Schedule-ID
	 */
	public function upsert_from_event( $event ) {
		$data = array(
			'source_type'     => 'event',
			'source_local_id' => (int) ( $event['id'] ?? 0 ),
			'external_id'     => isset( $event['external_id'] ) ? (string) $event['external_id'] : null,
			'calendar_id'     => isset( $event['calendar_id'] ) ? (string) $event['calendar_id'] : null,
			'title'           => isset( $event['title'] ) ? (string) $event['title'] : '',
			'description'     => isset( $event['description'] ) ? (string) $event['description'] : null,
			'start_datetime'  => isset( $event['start_datetime'] ) ? (string) $event['start_datetime'] : null,
			'end_datetime'    => isset( $event['end_datetime'] ) ? (string) $event['end_datetime'] : null,
			'is_all_day'      => isset( $event['is_all_day'] ) ? (int) $event['is_all_day'] : 0,
			'location_name'   => isset( $event['location_name'] ) ? (string) $event['location_name'] : null,
			'status'          => isset( $event['status'] ) ? (string) $event['status'] : null,
		);
		return $this->upsert_unique_source( $data );
	}

	/**
	 * Upsert eines Schedulesatzes aus einem Appointment-Datensatz
	 *
	 * @param array $appointment Array mit Appointment-Feldern (id, external_id, calendar_id, title, description, start_datetime, end_datetime, is_all_day)
	 * @return int Lokale Schedule-ID
	 */
	public function upsert_from_appointment( $appointment ) {
		$data = array(
			'source_type'     => 'appointment',
			'source_local_id' => (int) ( $appointment['id'] ?? 0 ),
			'external_id'     => isset( $appointment['external_id'] ) ? (string) $appointment['external_id'] : null,
			'calendar_id'     => isset( $appointment['calendar_id'] ) ? (string) $appointment['calendar_id'] : null,
			'title'           => isset( $appointment['title'] ) ? (string) $appointment['title'] : '',
			'description'     => isset( $appointment['description'] ) ? (string) $appointment['description'] : null,
			'start_datetime'  => isset( $appointment['start_datetime'] ) ? (string) $appointment['start_datetime'] : null,
			'end_datetime'    => isset( $appointment['end_datetime'] ) ? (string) $appointment['end_datetime'] : null,
			'is_all_day'      => isset( $appointment['is_all_day'] ) ? (int) $appointment['is_all_day'] : 0,
			'location_name'   => null,
			'status'          => null,
		);
		return $this->upsert_unique_source( $data );
	}

	/**
	 * Upsert basierend auf (source_type, source_local_id)
	 */
	private function upsert_unique_source( $data ) {
		$data['updated_at'] = $this->now();

		$existing_id = $this->db->get_var(
			$this->db->prepare(
				"SELECT id FROM {$this->table} WHERE source_type=%s AND source_local_id=%d",
				$data['source_type'],
				(int) $data['source_local_id']
			)
		);

		if ( $existing_id ) {
			$this->db->update( $this->table, $data, array( 'id' => (int) $existing_id ) );
			return (int) $existing_id;
		}

		$this->db->insert( $this->table, $data );
		return (int) $this->db->insert_id;
	}

	/**
	 * Liste der kombinierten Termine holen (optional gefiltert)
	 */
	public function get_list( $args = array() ) {
		$defaults = array(
			'from'        => null,
			'to'          => null,
			'calendar_id' => null, // externe ID
			'type'        => null, // event|appointment|null
			'limit'       => 50,
			'offset'      => 0,
			'order'       => 'ASC',
		);
		$args = wp_parse_args( $args, $defaults );

		$where = 'WHERE 1=1';
		$params = array();

		if ( ! empty( $args['from'] ) ) { $where .= ' AND start_datetime >= %s'; $params[] = $args['from']; }
		if ( ! empty( $args['to'] ) ) { $where .= ' AND start_datetime <= %s'; $params[] = $args['to']; }
		if ( ! empty( $args['calendar_id'] ) ) { $where .= ' AND calendar_id = %s'; $params[] = $args['calendar_id']; }
		if ( ! empty( $args['type'] ) ) { $where .= ' AND source_type = %s'; $params[] = $args['type']; }

		$order = strtoupper( $args['order'] ) === 'DESC' ? 'DESC' : 'ASC';

		$params[] = (int) $args['limit'];
		$params[] = (int) $args['offset'];

		$sql = $this->db->prepare(
			"SELECT * FROM {$this->table} {$where} ORDER BY start_datetime {$order} LIMIT %d OFFSET %d",
			...$params
		);

		return $this->db->get_results( $sql );
	}

	public function count_list( $args = array() ) {
		$defaults = array(
			'from'        => null,
			'to'          => null,
			'calendar_id' => null,
			'type'        => null,
		);
		$args = wp_parse_args( $args, $defaults );

		$where = 'WHERE 1=1';
		$params = array();

		if ( ! empty( $args['from'] ) ) { $where .= ' AND start_datetime >= %s'; $params[] = $args['from']; }
		if ( ! empty( $args['to'] ) ) { $where .= ' AND start_datetime <= %s'; $params[] = $args['to']; }
		if ( ! empty( $args['calendar_id'] ) ) { $where .= ' AND calendar_id = %s'; $params[] = $args['calendar_id']; }
		if ( ! empty( $args['type'] ) ) { $where .= ' AND source_type = %s'; $params[] = $args['type']; }

		if ( empty( $params ) ) {
			$sql = "SELECT COUNT(*) FROM {$this->table} {$where}";
			return (int) $this->db->get_var( $sql );
		}

		$sql = $this->db->prepare(
			"SELECT COUNT(*) FROM {$this->table} {$where}",
			...$params
		);
		return (int) $this->db->get_var( $sql );
	}
}
