<?php
/**
 * Template Loader
 *
 * Lädt Templates aus dem Plugin oder Theme
 *
 * @package    Repro_CT_Suite
 * @subpackage Repro_CT_Suite/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Repro_CT_Suite_Template_Loader {

	/**
	 * Plugin Template-Pfad
	 *
	 * @var string
	 */
	private $plugin_template_path;

	/**
	 * Theme Template-Pfad
	 *
	 * @var string
	 */
	private $theme_template_path;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->plugin_template_path = REPRO_CT_SUITE_PATH . 'templates/';
		$this->theme_template_path  = get_stylesheet_directory() . '/repro-ct-suite/';
	}

	/**
	 * Template laden
	 *
	 * Sucht zuerst im Theme, dann im Plugin
	 *
	 * @param string $template_name Template-Dateiname (z.B. 'events/list-simple.php')
	 * @param array  $data          Daten für das Template
	 * @param bool   $return        Ob Template zurückgegeben oder ausgegeben werden soll
	 * @return string|void
	 */
	public function get_template( $template_name, $data = array(), $return = false ) {
		// Template-Datei finden
		$template_file = $this->locate_template( $template_name );

		if ( ! $template_file ) {
			return $this->template_not_found_notice( $template_name );
		}

		// Daten extrahieren
		if ( ! empty( $data ) && is_array( $data ) ) {
			extract( $data );
		}

		// Output buffering wenn return = true
		if ( $return ) {
			ob_start();
		}

		// Template einbinden
		include $template_file;

		// Output zurückgeben wenn return = true
		if ( $return ) {
			return ob_get_clean();
		}
	}

	/**
	 * Template-Datei lokalisieren
	 *
	 * Prüft zuerst Theme, dann Plugin
	 *
	 * @param string $template_name Template-Name
	 * @return string|false Template-Pfad oder false
	 */
	private function locate_template( $template_name ) {
		// Zuerst im Theme suchen
		$theme_template = $this->theme_template_path . $template_name;
		if ( file_exists( $theme_template ) ) {
			return $theme_template;
		}

		// Dann im Plugin suchen
		$plugin_template = $this->plugin_template_path . $template_name;
		if ( file_exists( $plugin_template ) ) {
			return $plugin_template;
		}

		return false;
	}

	/**
	 * Notice wenn Template nicht gefunden
	 *
	 * @param string $template_name Template-Name
	 * @return string HTML Notice
	 */
	private function template_not_found_notice( $template_name ) {
		if ( current_user_can( 'manage_options' ) ) {
			return sprintf(
				'<div class="rcts-error"><strong>Repro CT-Suite:</strong> Template "%s" nicht gefunden.</div>',
				esc_html( $template_name )
			);
		}
		return '';
	}

	/**
	 * Template-Pfade abrufen (für Debugging)
	 *
	 * @return array Template-Pfade
	 */
	public function get_template_paths() {
		return array(
			'theme'  => $this->theme_template_path,
			'plugin' => $this->plugin_template_path,
		);
	}
}
