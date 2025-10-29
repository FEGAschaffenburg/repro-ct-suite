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
}
