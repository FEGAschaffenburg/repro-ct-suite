<?php
/**
 * Base Repository for DB helpers
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class Repro_CT_Suite_Repository_Base {
	/** @var wpdb */
	protected $db;
	/** @var string */
	protected $table;

	public function __construct( $table ) {
		global $wpdb;
		$this->db    = $wpdb;
		$this->table = $table;
	}

	protected function now() {
		return current_time( 'mysql', 1 ); // GMT
	}

	/**
	 * Holt einen einzelnen Datensatz anhand der ID
	 *
	 * @param int $id Die ID des Datensatzes
	 * @return object|null Der Datensatz oder null
	 */
	public function get_by_id( $id ) {
		return $this->db->get_row(
			$this->db->prepare(
				"SELECT * FROM {$this->table} WHERE id = %d",
				$id
			)
		);
	}

	/**
	 * Löscht einen einzelnen Datensatz anhand der ID
	 *
	 * @param int $id Die ID des Datensatzes
	 * @return bool|int Anzahl der gelöschten Zeilen oder false bei Fehler
	 */
	public function delete_by_id( $id ) {
		return $this->db->delete(
			$this->table,
			array( 'id' => $id ),
			array( '%d' )
		);
	}

	/**
	 * Aktualisiert einen einzelnen Datensatz anhand der ID
	 *
	 * @param int   $id   Die ID des Datensatzes
	 * @param array $data Array mit zu aktualisierenden Feldern
	 * @return bool|int Anzahl der aktualisierten Zeilen oder false bei Fehler
	 */
	public function update_by_id( $id, $data ) {
		$data['updated_at'] = $this->now();
		return $this->db->update(
			$this->table,
			$data,
			array( 'id' => $id ),
			null,
			array( '%d' )
		);
	}

	/**
	 * Prüft, ob ein Datensatz mit der ID existiert
	 *
	 * @param int $id Die ID des Datensatzes
	 * @return bool True wenn vorhanden, sonst false
	 */
	public function exists( $id ) {
		$count = $this->db->get_var(
			$this->db->prepare(
				"SELECT COUNT(*) FROM {$this->table} WHERE id = %d",
				$id
			)
		);
		return (int) $count > 0;
	}
}
