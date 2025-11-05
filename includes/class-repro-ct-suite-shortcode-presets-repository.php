<?php
/**
 * Repository für Shortcode Presets
 *
 * Verwaltet gespeicherte Shortcode-Konfigurationen in der Datenbank.
 *
 * @package    Repro_CT_Suite
 * @subpackage Repro_CT_Suite/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Repro_CT_Suite_Shortcode_Presets_Repository {

	/**
	 * @var wpdb WordPress Database Objekt
	 */
	private $wpdb;

	/**
	 * @var string Tabellenname mit Prefix
	 */
	private $table_name;

	/**
	 * Konstruktor
	 */
	public function __construct() {
		global $wpdb;
		$this->wpdb = $wpdb;
		$this->table_name = $wpdb->prefix . 'rcts_shortcode_presets';
	}

	/**
	 * Alle Presets abrufen
	 *
	 * @return array Liste aller Presets
	 */
	public function get_all() {
		$results = $this->wpdb->get_results(
			"SELECT * FROM {$this->table_name} ORDER BY name ASC",
			ARRAY_A
		);

		return $results ?: array();
	}

	/**
	 * Preset nach ID abrufen
	 *
	 * @param int $id Preset ID
	 * @return array|null Preset-Daten oder null
	 */
	public function get_by_id( $id ) {
		$result = $this->wpdb->get_row(
			$this->wpdb->prepare(
				"SELECT * FROM {$this->table_name} WHERE id = %d",
				$id
			),
			ARRAY_A
		);

		return $result ?: null;
	}

	/**
	 * Preset speichern (erstellen)
	 *
	 * @param array $data Preset-Daten
	 * @return int|false Neue ID oder false bei Fehler
	 */
	public function save( $data ) {
		$current_time = current_time( 'mysql' );

		$insert_data = array(
			'name'         => sanitize_text_field( $data['name'] ?? '' ),
			'view'         => sanitize_text_field( $data['view'] ?? 'list-simple' ),
			'limit_count'  => isset( $data['limit_count'] ) ? intval( $data['limit_count'] ) : null,
			'calendar_ids' => isset( $data['calendar_ids'] ) ? sanitize_text_field( $data['calendar_ids'] ) : null,
			'from_days'    => isset( $data['from_days'] ) ? intval( $data['from_days'] ) : null,
			'to_days'      => isset( $data['to_days'] ) ? intval( $data['to_days'] ) : null,
			'show_past'    => isset( $data['show_past'] ) ? (int) $data['show_past'] : 0,
			'order_dir'    => isset( $data['order_dir'] ) ? sanitize_text_field( $data['order_dir'] ) : 'ASC',
			'show_fields'  => isset( $data['show_fields'] ) ? sanitize_text_field( $data['show_fields'] ) : null,
			'created_at'   => $current_time,
			'updated_at'   => $current_time,
		);

		$result = $this->wpdb->insert(
			$this->table_name,
			$insert_data
		);

		if ( $result === false ) {
			return false;
		}

		return $this->wpdb->insert_id;
	}

	/**
	 * Preset aktualisieren
	 *
	 * @param int   $id   Preset ID
	 * @param array $data Neue Daten
	 * @return bool Success
	 */
	public function update( $id, $data ) {
		$current_time = current_time( 'mysql' );

		$update_data = array(
			'updated_at' => $current_time,
		);

		// Nur vorhandene Felder aktualisieren
		$allowed_fields = array( 'name', 'view', 'limit_count', 'calendar_ids', 'from_days', 'to_days', 'show_past', 'order_dir', 'show_fields' );
		
		foreach ( $allowed_fields as $field ) {
			if ( isset( $data[ $field ] ) ) {
				if ( in_array( $field, array( 'limit_count', 'from_days', 'to_days' ), true ) ) {
					$update_data[ $field ] = intval( $data[ $field ] );
				} elseif ( $field === 'show_past' ) {
					$update_data[ $field ] = (int) $data[ $field ];
				} else {
					$update_data[ $field ] = sanitize_text_field( $data[ $field ] );
				}
			}
		}

		$result = $this->wpdb->update(
			$this->table_name,
			$update_data,
			array( 'id' => $id ),
			null,
			array( '%d' )
		);

		return $result !== false;
	}

	/**
	 * Preset löschen
	 *
	 * @param int $id Preset ID
	 * @return bool Success
	 */
	public function delete( $id ) {
		$result = $this->wpdb->delete(
			$this->table_name,
			array( 'id' => $id ),
			array( '%d' )
		);

		return $result !== false;
	}

	/**
	 * Preset nach Name suchen
	 *
	 * @param string $name Preset Name
	 * @return array|null Preset-Daten oder null
	 */
	public function get_by_name( $name ) {
		$result = $this->wpdb->get_row(
			$this->wpdb->prepare(
				"SELECT * FROM {$this->table_name} WHERE name = %s",
				$name
			),
			ARRAY_A
		);

		return $result ?: null;
	}

	/**
	 * Prüft ob Preset-Name bereits existiert
	 *
	 * @param string $name    Preset Name
	 * @param int    $exclude_id Optional: ID zum Ausschließen (für Update)
	 * @return bool True wenn Name existiert
	 */
	public function name_exists( $name, $exclude_id = null ) {
		$query = "SELECT COUNT(*) FROM {$this->table_name} WHERE name = %s";
		$params = array( $name );

		if ( $exclude_id ) {
			$query .= " AND id != %d";
			$params[] = $exclude_id;
		}

		$count = (int) $this->wpdb->get_var(
			$this->wpdb->prepare( $query, $params )
		);

		return $count > 0;
	}
}
