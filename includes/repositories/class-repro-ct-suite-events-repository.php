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



	public function upsert_by_event_id( array $data ): int|false {

		$defaults = array(

			'event_id'      => '',

			'calendar_id'   => null,

			'appointment_id'=> null,

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



		$existing_id = $this->db->get_var( $this->db->prepare( "SELECT id FROM {$this->table} WHERE event_id=%s", $data['event_id'] ) );

		if ( $existing_id ) {

			$this->db->update( $this->table, $data, array( 'id' => $existing_id ) );

			return (int) $existing_id;

		}



		$this->db->insert( $this->table, $data );

		return (int) $this->db->insert_id;

	}



	public function get_by_id( int $id ) {

		return $this->db->get_row( $this->db->prepare( "SELECT * FROM {$this->table} WHERE id=%d", $id ) );

	}



	/**

	 * Holt die interne ID eines Events anhand der event_id

	 *

	 * @param string $event_id Event-ID aus ChurchTools

	 * @return int|null Interne ID oder null, wenn nicht gefunden

	 */

	public function get_id_by_event_id( int $event_id ): ?int {

		$val = $this->db->get_var(

			$this->db->prepare(

				"SELECT id FROM {$this->table} WHERE event_id=%s",

				$event_id

			)

		);

		return $val !== null ? (int) $val : null;

	}



	/**

	 * Holt ein Event-Objekt anhand der event_id

	 *

	 * @param string $event_id Event-ID aus ChurchTools

	 * @return object|null Event-Objekt oder null, wenn nicht gefunden

	 */

	public function get_by_event_id( int $event_id ) {

		return $this->db->get_row(

			$this->db->prepare(

				"SELECT * FROM {$this->table} WHERE event_id=%s",

				$event_id

			)

		);

	}



	/**

	 * Holt ein Event-Objekt anhand der appointment_id

	 *

	 * @param string|int $appointment_id Appointment-ID aus ChurchTools

	 * @return object|null Event-Objekt oder null, wenn nicht gefunden

	 */

	public function get_by_appointment_id( int $appointment_id ) {

		return $this->db->get_row(

			$this->db->prepare(

				"SELECT * FROM {$this->table} WHERE appointment_id=%s",

				$appointment_id

			)

		);

	}

}









