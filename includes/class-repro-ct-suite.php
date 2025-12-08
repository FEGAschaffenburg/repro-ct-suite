<?php

/**

 * The core plugin class.

 *

 * @package    Repro_CT_Suite

 * @subpackage Repro_CT_Suite/includes

 */



if ( ! defined( 'ABSPATH' ) ) {

	exit;

}



class Repro_CT_Suite {



	/**

	 * The loader that's responsible for maintaining and registering all hooks.

	 *

	 * @var Repro_CT_Suite_Loader $loader

	 */

	protected $loader;



	/**

	 * The unique identifier of this plugin.

	 *

	 * @var string $plugin_name

	 */

	protected $plugin_name;



	/**

	 * The current version of the plugin.

	 *

	 * @var string $version

	 */

	protected $version;



	/**

	 * Define the core functionality of the plugin.

	 */

	public function __construct() {

		$this->version     = defined( 'REPRO_CT_SUITE_VERSION' ) ? REPRO_CT_SUITE_VERSION : '1.0.0';

		$this->plugin_name = 'repro-ct-suite';



		$this->load_dependencies();

		$this->set_locale();

		$this->define_admin_hooks();

		$this->define_public_hooks();

	}



	/**

	 * Load the required dependencies for this plugin.

	 */

	private function load_dependencies(): void {

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-repro-ct-suite-loader.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-repro-ct-suite-i18n.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-repro-ct-suite-migrations.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-repro-ct-suite-crypto.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-repro-ct-suite-ct-client.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-repro-ct-suite-cron.php';



		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-repro-ct-suite-admin.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-repro-ct-suite-public.php';



		$this->loader = new Repro_CT_Suite_Loader();

		

		// Cron initialisieren

		Repro_CT_Suite_Cron::init();

	}



	/**

	 * Define the locale for this plugin for internationalization.

	 */

	private function set_locale(): void {

		$plugin_i18n = new Repro_CT_Suite_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}



	/**

	 * Register all of the hooks related to the admin area functionality.

	 */

	private function define_admin_hooks(): void {

		$plugin_admin = new Repro_CT_Suite_Admin( $this->get_plugin_name(), $this->get_version() );



		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		$this->loader->add_action( 'admin_init', $plugin_admin, 'register_settings' );

		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_admin_menu' );

		$this->loader->add_action( 'admin_init', $plugin_admin, 'handle_test_connection' );

		$this->loader->add_action( 'admin_init', $plugin_admin, 'handle_calendar_selection' );



		// AJAX Hooks: Kalender synchronisieren

		$this->loader->add_action( 'wp_ajax_repro_ct_suite_sync_calendars', $plugin_admin, 'ajax_sync_calendars' );



		// AJAX Hooks: Termine synchronisieren

		$this->loader->add_action( 'wp_ajax_repro_ct_suite_sync_appointments', $plugin_admin, 'ajax_sync_appointments' );



















		// DB-Upgrades auf admin_init prüfen (sicheres Timing)

		$this->loader->add_action( 'admin_init', 'Repro_CT_Suite_Migrations', 'maybe_upgrade' );

	}



	/**

	 * Register all of the hooks related to the public-facing functionality.

	 */

	private function define_public_hooks(): void {

		$plugin_public = new Repro_CT_Suite_Public( $this->get_plugin_name(), $this->get_version() );



		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}



	/**

	 * Run the loader to execute all of the hooks with WordPress.

	 */

	public function run(): void {

		$this->loader->run();

	}



	/**

	 * The name of the plugin used to uniquely identify it.

	 *

	 * @return string

	 */

	public function get_plugin_name(): string {

		return $this->plugin_name;

	}



	/**

	 * The reference to the class that orchestrates the hooks.

	 *

	 * @return Repro_CT_Suite_Loader

	 */

	public function get_loader(): object {

		return $this->loader;

	}



	/**

	 * Retrieve the version number of the plugin.

	 *

	 * @return string

	 */

	public function get_version(): string {

		return $this->version;

	}

}









