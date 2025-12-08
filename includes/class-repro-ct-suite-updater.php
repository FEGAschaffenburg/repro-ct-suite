<?php

/**

 * GitHub Plugin Updater

 *

 * Ermöglicht automatische Updates des Plugins direkt von GitHub.

 *

 * @package    Repro_CT_Suite

 * @subpackage Repro_CT_Suite/includes

 */



class Repro_CT_Suite_Updater {



	/**

	 * GitHub Username

	 *

	 * @var string

	 */

	private $username;



	/**

	 * GitHub Repository Name

	 *

	 * @var string

	 */

	private $repository;



	/**

	 * Plugin Slug

	 *

	 * @var string

	 */

	private $plugin_slug;



	/**

	 * Plugin Basename

	 *

	 * @var string

	 */

	private $plugin_basename;



	/**

	 * Plugin Daten

	 *

	 * @var array

	 */

	private $plugin_data;



	/**

	 * GitHub API URL

	 *

	 * @var string

	 */

	private $github_api_url;



	/**

	 * Access Token (optional, für private Repos)

	 *

	 * @var string

	 */

	private $access_token;



	/**

	 * Initialize the updater

	 *

	 * @param string $plugin_file Hauptdatei des Plugins.

	 * @param string $username GitHub Username.

	 * @param string $repository GitHub Repository Name.

	 * @param string $access_token Optional: GitHub Access Token für private Repos.

	 */

	public function __construct( string $plugin_file, string $username, string $repository, string $access_token = '' ) {

		$this->plugin_basename = plugin_basename( $plugin_file );

		$this->plugin_slug     = dirname( $this->plugin_basename );

		$this->username        = $username;

		$this->repository      = $repository;

		$this->access_token    = $access_token;

		$this->github_api_url  = "https://api.github.com/repos/{$username}/{$repository}";



		// Plugin-Daten abrufen.

		if ( ! function_exists( 'get_plugin_data' ) ) {

			require_once ABSPATH . 'wp-admin/includes/plugin.php';

	}

	$this->plugin_data = get_plugin_data( $plugin_file );



	// Hooks registrieren.

	add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_update' ) );

	add_filter( 'plugins_api', array( $this, 'plugin_info' ), 20, 3 );

	add_filter( 'upgrader_post_install', array( $this, 'after_install' ), 10, 3 );

	

	// Download-Hook nur registrieren wenn Access Token vorhanden ist

	if ( ! empty( $this->access_token ) ) {

		add_filter( 'upgrader_pre_download', array( $this, 'download_package' ), 10, 3 );

	}

}

	/**

	 * Prüft auf verfügbare Updates

	 *

	 * @param object $transient Update-Transient.

	 * @return object

	 */

	public function check_update( object $transient ): object {

		if ( empty( $transient->checked ) ) {

			return $transient;

		}



		// Neueste Release-Info von GitHub abrufen.

		$release_info = $this->get_release_info();



		if ( ! $release_info ) {

			return $transient;

		}



		// Versionen vergleichen (unterstützt 4-stellige Versionsnummern).

		$current_version = $this->plugin_data['Version'];

		$latest_version  = ltrim( $release_info->tag_name, 'v' );



		// Normalisiere Versionen für Vergleich (4-stellige Format: x.y.z.w).

		$current_normalized = $this->normalize_version( $current_version );

		$latest_normalized  = $this->normalize_version( $latest_version );



		if ( version_compare( $current_normalized, $latest_normalized, '<' ) ) {

			// Hole das erste Asset (sollte die ZIP-Datei sein).

			$package_url = $release_info->zipball_url;

			

			// Wenn Assets existieren, nutze das erste ZIP-Asset.

			if ( ! empty( $release_info->assets ) && is_array( $release_info->assets ) ) {

				foreach ( $release_info->assets as $asset ) {

					if ( isset( $asset->browser_download_url ) && strpos( $asset->name, '.zip' ) !== false ) {

						$package_url = $asset->browser_download_url;

						break;

					}

				}

			}



			$plugin_data = array(

				'slug'        => $this->plugin_slug,

				'new_version' => $latest_version,

				'url'         => $this->plugin_data['PluginURI'],

				'package'     => $package_url,

				'icons'       => array(),

				'banners'     => array(),

				'tested'      => '6.7',

			);



			$transient->response[ $this->plugin_basename ] = (object) $plugin_data;

		}



		return $transient;

	}



	/**

	 * Normalisiert Versionsnummer auf 4-stelliges Format

	 *

	 * @param string $version Versionsnummer.

	 * @return string Normalisierte Version.

	 */

	private function normalize_version( string $version ): string {

		$parts = explode( '.', $version );

		while ( count( $parts ) < 4 ) {

			$parts[] = '0';

		}

		return implode( '.', array_slice( $parts, 0, 4 ) );

	}



	/**

	 * Stellt Plugin-Informationen bereit

	 *

	 * @param false|object|array $result Plugin-Info.

	 * @param string             $action Art der Anfrage.

	 * @param object             $args Argumente.

	 * @return object|false

	 */

	public function plugin_info( mixed $result, string $action, object $args ): mixed {

		if ( 'plugin_information' !== $action ) {

			return $result;

		}



		if ( $args->slug !== $this->plugin_slug ) {

			return $result;

		}



		$release_info = $this->get_release_info();



		if ( ! $release_info ) {

			return $result;

		}



		$plugin_info = array(

			'name'              => $this->plugin_data['Name'],

			'slug'              => $this->plugin_slug,

			'version'           => ltrim( $release_info->tag_name, 'v' ),

			'author'            => $this->plugin_data['Author'],

			'author_profile'    => $this->plugin_data['AuthorURI'],

			'requires'          => $this->plugin_data['RequiresWP'] ?? '5.0',

			'tested'            => $this->plugin_data['RequiresWP'] ?? '6.4',

			'requires_php'      => $this->plugin_data['RequiresPHP'] ?? '7.4',

			'last_updated'      => $release_info->published_at,

			'sections'          => array(

				'description'  => $this->plugin_data['Description'],

				'changelog'    => $this->parse_changelog( $release_info->body ),

			),

			'download_link'     => $release_info->zipball_url,

			'banners'           => array(),

		);



		return (object) $plugin_info;

	}



	/**

	 * Nach der Installation: Ordner umbenennen

	 *

	 * @param bool  $response Installation erfolgreich.

	 * @param array $hook_extra Zusätzliche Informationen.

	 * @param array $result Ergebnis der Installation.

	 * @return array

	 */

	public function after_install( mixed $response, array $hook_extra, array $result ): mixed {

		global $wp_filesystem;



		if ( ! isset( $hook_extra['plugin'] ) || $hook_extra['plugin'] !== $this->plugin_basename ) {

			return $result;

		}



		// Von GitHub kommt der Ordnername mit Commit-Hash, wir wollen ihn umbenennen.

		$plugin_folder = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $this->plugin_slug;

		$wp_filesystem->move( $result['destination'], $plugin_folder );

		$result['destination'] = $plugin_folder;



		// Plugin aktivieren, wenn es vorher aktiv war.

		if ( is_plugin_active( $this->plugin_basename ) ) {

			activate_plugin( $this->plugin_basename );

		}



		return $result;

	}



	/**

	 * Ruft Release-Informationen von GitHub ab

	 *

	 * @return object|false

	 */

	public function get_release_info(): ?object {

		$cache_key   = 'repro_ct_suite_release_info';

		$cache_time  = 300; // 5 Minuten Cache

		$cached_data = get_transient( $cache_key );



		if ( false !== $cached_data && ! defined( 'WP_DEBUG' ) ) {

			return $cached_data;

		}



		$url = $this->github_api_url . '/releases/latest';



		$args = array(

			'headers' => array(

				'Accept' => 'application/vnd.github.v3+json',

			),

			'timeout' => 15,

		);



		if ( ! empty( $this->access_token ) ) {

			$args['headers']['Authorization'] = 'token ' . $this->access_token;

		}



		$response = wp_remote_get( $url, $args );



		if ( is_wp_error( $response ) ) {

			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {

				error_log( 'Repro CT-Suite Update Check Fehler: ' . $response->get_error_message() );

		}

		return null;

	}



	$response_code = wp_remote_retrieve_response_code( $response );		

		if ( 200 !== $response_code ) {

			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {

				error_log( 'Repro CT-Suite Update Check HTTP Error: ' . $response_code );

		}

		return null;

	}



	$body = wp_remote_retrieve_body( $response );		

		$data = json_decode( $body );



		if ( ! $data || isset( $data->message ) ) {

			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {

				$json_error = $data->message ?? 'Invalid JSON';

				error_log( 'Repro CT-Suite Update Check JSON Error: ' . $json_error );

			}

			return null;

		}



		set_transient( $cache_key, $data, $cache_time );



		return $data;

	}



	/**

	 * Parst Changelog aus GitHub Release Notes

	 *

	 * @param string $body Release-Body.

	 * @return string

	 */

	private function parse_changelog( string $body ): string {

		if ( empty( $body ) ) {

			return 'Keine Changelog-Informationen verfügbar.';

		}



		// Markdown in HTML konvertieren (einfache Konvertierung).

		$body = wpautop( $body );

		return $body;

	}



	/**

	 * Lädt das Update-Paket mit GitHub-Token herunter

	 *

	 * Dieser Filter ermöglicht den Download von privaten GitHub-Assets mit Authentifizierung.

	 *

	 * @param bool   $reply Ob der Download durch einen Filter bereits erledigt wurde.

	 * @param string $package Die URL des Pakets.

	 * @param object $upgrader Der Upgrader-Instanz.

	 * @return bool|string Pfad zur heruntergeladenen Datei oder false.

	 */

	public function download_package( string $reply, string $package, object $upgrader ): string {

		// Nur für GitHub-Downloads von diesem Repository.

		if ( strpos( $package, 'github.com/' . $this->username . '/' . $this->repository ) === false ) {

			return $reply;

		}



		// Wenn kein Access Token gesetzt ist, Standard-Download verwenden

		if ( empty( $this->access_token ) ) {

			return $reply;

		}



		// Temporäre Datei erstellen.

		$tmpfile = wp_tempnam( $package );

		if ( ! $tmpfile ) {

			return new WP_Error( 'temp_file_failed', 'Could not create temporary file.' );

		}



		// Download mit Authorization-Header.

		$args = array(

			'timeout' => 300,

			'stream'  => true,

			'filename' => $tmpfile,

			'headers' => array(

				'Authorization' => 'token ' . $this->access_token,

				'Accept'        => 'application/octet-stream',

			),

		);



		$response = wp_remote_get( $package, $args );



		if ( is_wp_error( $response ) ) {

			@unlink( $tmpfile );

			return $response;

		}



		$response_code = wp_remote_retrieve_response_code( $response );

		if ( 200 !== $response_code ) {

			@unlink( $tmpfile );

			return new WP_Error( 

				'download_failed', 

				sprintf( 'Download failed with HTTP code %d', $response_code ) 

			);

		}



		return $tmpfile;

	}

}











