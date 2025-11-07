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
		// Preset-Parameter auswerten
		$preset_atts = array();
		if ( ! empty( $atts['preset'] ) ) {
			$preset_atts = $this->load_preset_by_name( $atts['preset'] );
			if ( empty( $preset_atts ) ) {
				return '<p class="rcts-error">' . sprintf(
					__( 'Preset "%s" nicht gefunden.', 'repro-ct-suite' ),
					esc_html( $atts['preset'] )
				) . '</p>';
			}
		}

		// Standard-Attribute (mit Preset-Override)
		$default_atts = array(
			'view'         => 'list',           // list, list-grouped, cards
			'limit'        => 10,               // Anzahl Events
			'calendar_ids' => '',               // Komma-getrennte IDs
			'from_days'    => 0,                // Relative Tage (negativ = Vergangenheit)
			'to_days'      => 30,               // Relative Tage (positiv = Zukunft)
			'order'        => 'asc',            // asc, desc
			'show_past'    => 'false',          // true, false
			'show_fields'  => 'title,date,time,location', // Angezeigte Felder
		);

		// Preset-Werte als Defaults verwenden
		if ( ! empty( $preset_atts ) ) {
			$default_atts = array_merge( $default_atts, $preset_atts );
		}

		// Shortcode-Attribute haben höchste Priorität (Override)
		$atts = shortcode_atts( $default_atts, $atts, 'rcts_events' );

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
		$where[] = $wpdb->prepare( 'e.start_datetime >= %s', $from_date );
		$where[] = $wpdb->prepare( 'e.start_datetime <= %s', $to_date );

		// Kalender-Filter
		if ( ! empty( $atts['calendar_ids'] ) ) {
			// Konvertiere WordPress-IDs zu ChurchTools calendar_ids
			$wp_ids = array_map( 'intval', explode( ',', $atts['calendar_ids'] ) );
			
			// Hole die ChurchTools calendar_ids aus der Kalendar-Tabelle
			$placeholders_in = implode( ',', array_fill( 0, count( $wp_ids ), '%d' ) );
			$ct_calendar_ids = $wpdb->get_col(
				$wpdb->prepare(
					"SELECT calendar_id FROM {$wpdb->prefix}rcts_calendars WHERE id IN ($placeholders_in)",
					$wp_ids
				)
			);
			
			// Wenn ChurchTools-IDs gefunden wurden, filtere danach
			if ( ! empty( $ct_calendar_ids ) ) {
				$placeholders = implode( ',', array_fill( 0, count( $ct_calendar_ids ), '%s' ) );
				$where[] = $wpdb->prepare( "e.calendar_id IN ($placeholders)", $ct_calendar_ids );
			}
		}

		// Vergangene Events filtern
		if ( $atts['show_past'] === 'false' ) {
			$now = current_time( 'mysql' );
			$where[] = $wpdb->prepare( 'e.start_datetime >= %s', $now );
		}

		// Query zusammenbauen
		$where_clause = implode( ' AND ', $where );
		$order_by = ( $atts['order'] === 'desc' ) ? 'DESC' : 'ASC';
		$limit = absint( $atts['limit'] );

		$sql = "
			SELECT e.*, c.name as calendar_name, c.color as calendar_color
			FROM {$events_table} e
			LEFT JOIN {$wpdb->prefix}rcts_calendars c ON e.calendar_id = c.calendar_id
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
			
			// Zeit-Formatierung mit WordPress-Einstellungen
			$time_format = get_option( 'time_format' );
			$event->time_formatted = wp_date( $time_format, strtotime( $event->start_datetime ) );
			$event->end_time_formatted = $event->end_datetime ? wp_date( $time_format, strtotime( $event->end_datetime ) ) : '';
			
			// "Uhr" bei 24h-Format hinzufügen, AM/PM bei 12h-Format ist bereits im Format
			$is_24h_format = ( strpos( $time_format, 'a' ) === false && strpos( $time_format, 'A' ) === false );
			if ( $is_24h_format ) {
				$event->time_formatted .= ' Uhr';
				if ( $event->end_time_formatted ) {
					$event->end_time_formatted .= ' Uhr';
				}
			}
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

	/**
	 * Preset nach Name laden
	 *
	 * @param string $preset_name Name des Presets
	 * @return array|null Preset-Attribute oder null
	 */
	private function load_preset_by_name( $preset_name ) {
		global $wpdb;

		// Preset Repository laden
		require_once plugin_dir_path( __FILE__ ) . 'class-repro-ct-suite-shortcode-presets-repository.php';
		$repository = new Repro_CT_Suite_Shortcode_Presets_Repository();

		$preset = $repository->get_by_name( $preset_name );

		if ( ! $preset ) {
			return null;
		}

		// Preset-Daten in Shortcode-Attribute umwandeln
		$atts = array();

		if ( ! empty( $preset['view'] ) ) {
			$atts['view'] = $preset['view'];
		}

		if ( ! empty( $preset['limit_count'] ) ) {
			$atts['limit'] = $preset['limit_count'];
		}

		if ( ! empty( $preset['calendar_ids'] ) ) {
			$atts['calendar_ids'] = $preset['calendar_ids'];
		}

		if ( isset( $preset['from_days'] ) ) {
			$atts['from_days'] = $preset['from_days'];
		}

		if ( isset( $preset['to_days'] ) ) {
			$atts['to_days'] = $preset['to_days'];
		}

		if ( isset( $preset['show_past'] ) ) {
			$atts['show_past'] = $preset['show_past'] ? 'true' : 'false';
		}

		if ( ! empty( $preset['order_dir'] ) ) {
			$atts['order'] = strtolower( $preset['order_dir'] );
		}

		if ( ! empty( $preset['show_fields'] ) ) {
			$atts['show_fields'] = $preset['show_fields'];
		}

		return $atts;
	}
}

