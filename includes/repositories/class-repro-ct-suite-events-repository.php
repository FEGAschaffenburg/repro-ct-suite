<?php
/**
 * Events Repository
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Repro_CT_Suite_Events_Repository extends Repro_CT_Suite_Repository_Base {
	public function __construct() {
		global $wpdb;
		parent::__construct( $wpdb->prefix . 'rcts_events' );
	}

	public function upsert_by_external_id( $data ) {
		$defaults = array(
			'external_id'   => '',
			'calendar_id'   => null,
			'title'         => '',
			'description'   => null,
			'start_datetime'=> null,
			'end_datetime'  => null,
			'location_name' => null,
			'status'        => null,
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

	public function get_by_id( $id ) {
		return $this->db->get_row( $this->db->prepare( "SELECT * FROM {$this->table} WHERE id=%d", $id ) );
	}
}
