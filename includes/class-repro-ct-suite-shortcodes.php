<?php
/**
 * Shortcode Handler
 *
 * Verwaltet alle Shortcodes für die Frontend-Anzeige von Events
 *
 * @package    Repro_CT_Suite
 * @subpackage Repro_CT_Suite/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Repro_CT_Suite_Shortcodes {

	/**
	 * Template Loader Instance
	 *
	 * @var Repro_CT_Suite_Template_Loader
	 */
	private $template_loader;

	/**
	 * Events Repository
	 *
	 * @var Repro_CT_Suite_Events_Repository
	 */
	private $events_repo;

	/**
	 * Initialize the shortcodes
	 */
	public function __construct() {
		// Template Loader laden
		require_once plugin_dir_path( __FILE__ ) . 'class-repro-ct-suite-template-loader.php';
		$this->template_loader = new Repro_CT_Suite_Template_Loader();

		// Events Repository laden
		require_once plugin_dir_path( __FILE__ ) . 'repositories/class-repro-ct-suite-repository-base.php';
		require_once plugin_dir_path( __FILE__ ) . 'repositories/class-repro-ct-suite-events-repository.php';
		$this->events_repo = new Repro_CT_Suite_Events_Repository();

		// Shortcodes registrieren
		add_shortcode( 'rcts_events', array( $this, 'render_events' ) );
	}

	/**
	 * Render Events Shortcode
	 *
	 * @param array $atts Shortcode Attribute
	 * @return string HTML Output
	 */
	public function render_events( $atts ) {
		// Standard-Attribute
		$atts = shortcode_atts(
			array(
				'view'         => 'list',           // list, list-grouped, cards
				'limit'        => 10,               // Anzahl Events
				'calendar_ids' => '',               // Komma-getrennte IDs
				'from_days'    => 0,                // Relative Tage (negativ = Vergangenheit)
				'to_days'      => 30,               // Relative Tage (positiv = Zukunft)
				'order'        => 'asc',            // asc, desc
				'show_past'    => 'false',          // true, false
				'show_fields'  => 'title,date,time,location', // Angezeigte Felder
			),
			$atts,
			'rcts_events'
		);

		// Events abrufen
		$events = $this->get_events( $atts );

		// Template-Daten vorbereiten
		$template_data = array(
			'events'      => $events,
			'atts'        => $atts,
			'show_fields' => $this->parse_show_fields( $atts['show_fields'] ),
		);

		// Template laden basierend auf View
		$template_name = $this->get_template_name( $atts['view'] );
		
		// Output buffering
		ob_start();
		$this->template_loader->get_template( $template_name, $template_data );
		return ob_get_clean();
	}

	/**
	 * Events aus Datenbank abrufen
	 *
	 * @param array $atts Shortcode Attribute
	 * @return array Events
	 */
	private function get_events( $atts ) {
		global $wpdb;
		$events_table = $wpdb->prefix . 'rcts_events';

		// Zeitraum berechnen
		$current_time = current_time( 'timestamp' );
		$from_date = date( 'Y-m-d H:i:s', $current_time + ( (int) $atts['from_days'] * DAY_IN_SECONDS ) );
		$to_date   = date( 'Y-m-d H:i:s', $current_time + ( (int) $atts['to_days'] * DAY_IN_SECONDS ) );

		// Base Query
		$where = array();
		$where[] = $wpdb->prepare( 'start_datetime >= %s', $from_date );
		$where[] = $wpdb->prepare( 'start_datetime <= %s', $to_date );

		// Kalender-Filter
		if ( ! empty( $atts['calendar_ids'] ) ) {
			$calendar_ids = array_map( 'intval', explode( ',', $atts['calendar_ids'] ) );
			$placeholders = implode( ',', array_fill( 0, count( $calendar_ids ), '%d' ) );
			$where[] = $wpdb->prepare( "calendar_id IN ($placeholders)", $calendar_ids );
		}

		// Vergangene Events filtern
		if ( $atts['show_past'] === 'false' ) {
			$now = current_time( 'mysql' );
			$where[] = $wpdb->prepare( 'start_datetime >= %s', $now );
		}

		// Query zusammenbauen
		$where_clause = implode( ' AND ', $where );
		$order_by = ( $atts['order'] === 'desc' ) ? 'DESC' : 'ASC';
		$limit = absint( $atts['limit'] );

		$sql = "
			SELECT e.*, c.name as calendar_name, c.color as calendar_color
			FROM {$events_table} e
			LEFT JOIN {$wpdb->prefix}rcts_calendars c ON e.calendar_id = c.id
			WHERE {$where_clause}
			ORDER BY e.start_datetime {$order_by}
			LIMIT {$limit}
		";

		$events = $wpdb->get_results( $sql );

		// Events mit zusätzlichen Daten anreichern
		foreach ( $events as &$event ) {
			$event->start_date = wp_date( 'Y-m-d', strtotime( $event->start_datetime ) );
			$event->start_time = wp_date( 'H:i', strtotime( $event->start_datetime ) );
			$event->end_time   = $event->end_datetime ? wp_date( 'H:i', strtotime( $event->end_datetime ) ) : '';
			$event->date_formatted = wp_date( get_option( 'date_format' ), strtotime( $event->start_datetime ) );
			$event->time_formatted = wp_date( get_option( 'time_format' ), strtotime( $event->start_datetime ) );
		}

		return $events;
	}

	/**
	 * Parse show_fields Attribut in Array
	 *
	 * @param string $fields Komma-getrennte Felder
	 * @return array Felder
	 */
	private function parse_show_fields( $fields ) {
		$allowed_fields = array( 'title', 'date', 'time', 'datetime', 'location', 'description', 'calendar', 'image' );
		$fields_array = array_map( 'trim', explode( ',', $fields ) );
		return array_intersect( $fields_array, $allowed_fields );
	}

	/**
	 * Template-Namen basierend auf View ermitteln
	 *
	 * @param string $view View-Typ
	 * @return string Template-Pfad
	 */
	private function get_template_name( $view ) {
		$templates = array(
			'list'         => 'events/list-simple.php',
			'list-grouped' => 'events/list-grouped.php',
			'cards'        => 'events/cards.php',
		);

		return isset( $templates[ $view ] ) ? $templates[ $view ] : $templates['list'];
	}
}
