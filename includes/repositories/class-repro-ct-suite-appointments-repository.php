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
}
