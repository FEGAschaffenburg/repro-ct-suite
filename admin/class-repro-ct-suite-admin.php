<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @package    Repro_CT_Suite
 * @subpackage Repro_CT_Suite/admin
 */

class Repro_CT_Suite_Admin {

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
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 */
	public function enqueue_styles() {
		wp_enqueue_style(
			$this->plugin_name,
			plugin_dir_url( __FILE__ ) . 'css/repro-ct-suite-admin.css',
			array(),
			$this->version,
			'all'
		);
	}

	/**
	 * Register the JavaScript for the admin area.
	 */
	public function enqueue_scripts() {
		wp_enqueue_script(
			$this->plugin_name,
			plugin_dir_url( __FILE__ ) . 'js/repro-ct-suite-admin.js',
			array( 'jquery' ),
			$this->version,
			false
		);
	}

	/**
	 * Add admin menu pages.
	 */
	public function add_admin_menu() {
		add_menu_page(
			__( 'Repro CT-Suite', 'repro-ct-suite' ),
			__( 'Repro CT-Suite', 'repro-ct-suite' ),
			'manage_options',
			'repro-ct-suite',
			array( $this, 'display_admin_page' ),
			'dashicons-admin-generic',
			30
		);

		add_submenu_page(
			'repro-ct-suite',
			__( 'Einstellungen', 'repro-ct-suite' ),
			__( 'Einstellungen', 'repro-ct-suite' ),
			'manage_options',
			'repro-ct-suite',
			array( $this, 'display_admin_page' )
		);

		add_submenu_page(
			'repro-ct-suite',
			__( 'Update-Info', 'repro-ct-suite' ),
			__( 'Update-Info', 'repro-ct-suite' ),
			'manage_options',
			'repro-ct-suite-update',
			array( $this, 'display_update_page' )
		);
	}

	/**
	 * Display the admin page.
	 */
	public function display_admin_page() {
		include_once plugin_dir_path( __FILE__ ) . 'views/admin-display.php';
	}

	/**
	 * Display the update info page.
	 */
	public function display_update_page() {
		include_once plugin_dir_path( __FILE__ ) . 'views/admin-update.php';
	}
}
