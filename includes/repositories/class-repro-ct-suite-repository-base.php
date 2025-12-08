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

	public function __construct( string $table ) {

		global $wpdb;

		$this->db    = $wpdb;

		$this->table = $table;

	}



	protected function now(): string {

		return current_time( 'mysql', 1 ); // GMT

	}



	/**

	 * Holt einen einzelnen Datensatz anhand der ID

	 *

	 * @param int $id Die ID des Datensatzes

	 * @return object|null Der Datensatz oder null

	 */

	public function get_by_id( int $id ) {

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

	public function delete_by_id( int $id ): int|false {

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

	public function update_by_id( int $id, array $data ): int|false {

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

	public function exists( int $id ): bool {

		$count = $this->db->get_var(

			$this->db->prepare(

				"SELECT COUNT(*) FROM {$this->table} WHERE id = %d",

				$id

			)

		);

		return (int) $count > 0;

	}



	/**

	 * Fügt einen neuen Datensatz hinzu

	 *

	 * @param array $data Array mit zu speichernden Feldern

	 * @return int|WP_Error Die neue ID oder WP_Error bei Fehler

	 */

	public function insert( array $data ): int|false {

		// Automatische Zeitstempel hinzufügen

		$data['created_at'] = $this->now();

		$data['updated_at'] = $this->now();



		$result = $this->db->insert( $this->table, $data );

		

		if ( $result === false ) {

			return new WP_Error( 'db_insert_error', 'Datenbankfehler beim Einfügen: ' . $this->db->last_error );

		}



		return (int) $this->db->insert_id;

	}

}










