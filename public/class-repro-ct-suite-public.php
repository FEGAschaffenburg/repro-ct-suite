<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @package    Repro_CT_Suite
 * @subpackage Repro_CT_Suite/public
 */

class Repro_CT_Suite_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @var string $plugin_name
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @var string $version
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of the plugin.
	 * @param string $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 */
	public function enqueue_styles() {
		// Basis Public CSS
		wp_enqueue_style(
			$this->plugin_name,
			plugin_dir_url( __FILE__ ) . 'css/repro-ct-suite-public.css',
			array(),
			null,
			'all'
		);
		
		// Frontend Events CSS
		wp_enqueue_style(
			$this->plugin_name . '-frontend',
			plugin_dir_url( __FILE__ ) . 'css/repro-ct-suite-frontend.css',
			array(),
			REPRO_CT_SUITE_VERSION,
			'all'
		);
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 */
	public function enqueue_scripts() {
		wp_enqueue_script(
			$this->plugin_name,
			plugin_dir_url( __FILE__ ) . 'js/repro-ct-suite-public.js',
			array( 'jquery' ),
			null,
			false
		);
	}
}
