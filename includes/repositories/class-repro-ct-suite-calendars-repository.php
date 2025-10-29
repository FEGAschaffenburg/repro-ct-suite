<?php
/**
 * Calendars Repository
 *
 * Verwaltet ChurchTools-Kalender in der Datenbank.
 *
 * @package    Repro_CT_Suite
 * @subpackage Repro_CT_Suite/includes/repositories
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Repro_CT_Suite_Calendars_Repository extends Repro_CT_Suite_Repository_Base {

	/**
	 * Konstruktor
	 */
	public function __construct() {
		parent::__construct( $this->db->prefix . 'rcts_calendars' );
	}

	/**
	 * Upsert (Insert oder Update) eines Kalenders anhand der external_id
	 *
	 * @param array $data Kalender-Daten.
	 * @return int ID des Kalenders.
	 */
	public function upsert_by_external_id( $data ) {
		$defaults = array(
			'external_id'      => '',
			'name'             => '',
			'name_translated'  => null,
			'color'            => null,
			'is_public'        => 0,
			'is_selected'      => 0,
			'sort_order'       => null,
			'raw_payload'      => null,
		);
		$data = wp_parse_args( $data, $defaults );

		$data['updated_at'] = $this->now();

		$existing_id = $this->db->get_var(
			$this->db->prepare(
				"SELECT id FROM {$this->table} WHERE external_id=%s",
				$data['external_id']
			)
		);

		if ( $existing_id ) {
			// Update: is_selected beibehalten (User-Auswahl nicht überschreiben)
			unset( $data['is_selected'] );
			$this->db->update( $this->table, $data, array( 'id' => $existing_id ) );
			return (int) $existing_id;
		}

		// Insert
		$this->db->insert( $this->table, $data );
		return (int) $this->db->insert_id;
	}

	/**
	 * Holt alle Kalender
	 *
	 * @param array $args Query-Parameter (order_by, order).
	 * @return array Liste der Kalender.
	 */
	public function get_all( $args = array() ) {
		$defaults = array(
			'order_by' => 'sort_order',
			'order'    => 'ASC',
		);
		$args = wp_parse_args( $args, $defaults );

		$order_by = in_array( $args['order_by'], array( 'name', 'sort_order', 'is_selected' ), true )
			? $args['order_by']
			: 'sort_order';
		$order = in_array( strtoupper( $args['order'] ), array( 'ASC', 'DESC' ), true )
			? strtoupper( $args['order'] )
			: 'ASC';

		$sql = "SELECT * FROM {$this->table} ORDER BY {$order_by} {$order}";
		return $this->db->get_results( $sql );
	}

	/**
	 * Holt alle ausgewählten Kalender
	 *
	 * @return array Liste der ausgewählten Kalender.
	 */
	public function get_selected() {
		$sql = $this->db->prepare(
			"SELECT * FROM {$this->table} WHERE is_selected=%d ORDER BY sort_order ASC",
			1
		);
		return $this->db->get_results( $sql );
	}

	/**
	 * Holt IDs der ausgewählten Kalender
	 *
	 * @return array Liste der Calendar-IDs.
	 */
	public function get_selected_ids() {
		$sql = $this->db->prepare(
			"SELECT id FROM {$this->table} WHERE is_selected=%d ORDER BY sort_order ASC",
			1
		);
		return $this->db->get_col( $sql );
	}

	/**
	 * Setzt die Auswahl für einen Kalender
	 *
	 * @param int  $id Kalender-ID.
	 * @param bool $selected Ausgewählt oder nicht.
	 * @return bool Erfolg.
	 */
	public function set_selected( $id, $selected ) {
		return (bool) $this->db->update(
			$this->table,
			array( 'is_selected' => $selected ? 1 : 0 ),
			array( 'id' => $id ),
			array( '%d' ),
			array( '%d' )
		);
	}

	/**
	 * Setzt Auswahl für mehrere Kalender gleichzeitig
	 *
	 * @param array $selected_ids Array von Calendar-IDs die ausgewählt sein sollen.
	 * @return bool Erfolg.
	 */
	public function update_selected( $selected_ids ) {
		// Alle deselektieren
		$this->db->update(
			$this->table,
			array( 'is_selected' => 0 ),
			array( '1' => '1' )
		);

		// Ausgewählte setzen
		if ( empty( $selected_ids ) ) {
			return true;
		}

		$ids_placeholder = implode( ',', array_fill( 0, count( $selected_ids ), '%d' ) );
		$sql = $this->db->prepare(
			"UPDATE {$this->table} SET is_selected=1 WHERE id IN ({$ids_placeholder})",
			...$selected_ids
		);

		return (bool) $this->db->query( $sql );
	}

	/**
	 * Holt einen Kalender anhand der external_id
	 *
	 * @param string $external_id ChurchTools Calendar ID.
	 * @return object|null Kalender-Objekt oder null.
	 */
	public function get_by_external_id( $external_id ) {
		return $this->db->get_row(
			$this->db->prepare(
				"SELECT * FROM {$this->table} WHERE external_id=%s",
				$external_id
			)
		);
	}

	/**
	 * Zählt alle Kalender
	 *
	 * @return int Anzahl.
	 */
	public function count_all() {
		return (int) $this->db->get_var( "SELECT COUNT(*) FROM {$this->table}" );
	}

	/**
	 * Zählt ausgewählte Kalender
	 *
	 * @return int Anzahl.
	 */
	public function count_selected() {
		return (int) $this->db->get_var(
			$this->db->prepare(
				"SELECT COUNT(*) FROM {$this->table} WHERE is_selected=%d",
				1
			)
		);
	}
}
