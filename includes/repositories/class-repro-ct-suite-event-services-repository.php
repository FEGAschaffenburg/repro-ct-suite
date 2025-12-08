<?php
/**
 * Event Services Repository
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Repro_CT_Suite_Event_Services_Repository extends Repro_CT_Suite_Repository_Base {
	public function __construct() {
		global $wpdb;
		parent::__construct( $wpdb->prefix . 'rcts_event_services' );
	}

	public function upsert( array $data ): int|false {
		$defaults = array(
			'event_id'      => null,
			'external_id'   => null,
			'service_name'  => '',
			'person_name'   => null,
			'status'        => null,
			'notes'         => null,
			'start_datetime'=> null,
		);
		$data = wp_parse_args( $data, $defaults );
		$data['updated_at'] = $this->now();

		// Wenn external_id vorhanden ist, nutze diese, sonst (event_id+service_name) als natürlichen Schlüssel
		if ( ! empty( $data['external_id'] ) ) {
			$existing_id = $this->db->get_var( $this->db->prepare( "SELECT id FROM {$this->table} WHERE external_id=%s", $data['external_id'] ) );
			if ( $existing_id ) {
				$this->db->update( $this->table, $data, array( 'id' => $existing_id ) );
				return (int) $existing_id;
			}
		} else {
			$existing_id = $this->db->get_var( $this->db->prepare( "SELECT id FROM {$this->table} WHERE event_id=%d AND service_name=%s", $data['event_id'], $data['service_name'] ) );
			if ( $existing_id ) {
				$this->db->update( $this->table, $data, array( 'id' => $existing_id ) );
				return (int) $existing_id;
			}
		}

		$this->db->insert( $this->table, $data );
		return (int) $this->db->insert_id;
	}

	public function get_for_event( int $event_id ): array {
		return $this->db->get_results( $this->db->prepare( "SELECT * FROM {$this->table} WHERE event_id=%d ORDER BY start_datetime IS NULL, start_datetime ASC", $event_id ) );
	}
}





