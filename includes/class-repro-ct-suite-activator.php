<?php
/**
 * Fired during plugin activation.
 *
 * @package    Repro_CT_Suite
 * @subpackage Repro_CT_Suite/includes
 */

class Repro_CT_Suite_Activator {

	/**
	 * Aktivierung: Optionen setzen und DB-Migrationen ausführen.
	 */
	public static function activate() {
		// Bereinige alte/verschachtelte Plugin-Installationen
		self::cleanup_old_installations();

		// Standard: Auto-Update deaktiviert
		add_option( 'repro_ct_suite_auto_update', 0 );

		// DB-Tabellen erstellen/aktualisieren
		require_once plugin_dir_path( __FILE__ ) . 'class-repro-ct-suite-migrations.php';
		Repro_CT_Suite_Migrations::migrate();
		
		// Flush rewrite rules
		flush_rewrite_rules();
	}

	/**
	 * Bereinigt alte oder verschachtelte Plugin-Installationen.
	 * 
	 * Prüft auf:
	 * - Verschachtelte Strukturen (repro-ct-suite/repro-ct-suite/)
	 * - Alte Ordner (repro-ct-suite-clean, etc.)
	 * 
	 * @since 0.2.4.1
	 */
	private static function cleanup_old_installations() {
		$plugins_dir = WP_PLUGIN_DIR;
		$target_slug = 'repro-ct-suite';
		$current_path = plugin_dir_path( dirname( __FILE__ ) );
		
		// 1. Prüfe auf verschachtelte Struktur
		$nested_path = $current_path . $target_slug;
		if ( is_dir( $nested_path ) ) {
			// Verschachtelte Struktur erkannt - sollte nach Installation nicht mehr vorkommen
			// Aber als Fallback: Loggen für Debug
			error_log( 'Repro CT-Suite: Verschachtelte Struktur erkannt bei Aktivierung - bitte manuell prüfen.' );
		}
		
		// 2. Bereinige alte Plugin-Ordner (nur wenn NICHT der aktuelle Ordner)
		$old_folders = array( 'repro-ct-suite-clean', 'repro-ct-suite-old', 'repro-ct-suite-backup' );
		
		foreach ( $old_folders as $old_folder ) {
			$old_path = $plugins_dir . '/' . $old_folder;
			
			// Nur löschen wenn:
			// - Ordner existiert
			// - Nicht der aktuell aktivierte Ordner ist
			if ( is_dir( $old_path ) && realpath( $old_path ) !== realpath( $current_path ) ) {
				// Prüfe ob Plugin aktiv ist (sollte nicht sein, aber sicher ist sicher)
				$old_plugin_file = $old_folder . '/' . $target_slug . '.php';
				if ( is_plugin_active( $old_plugin_file ) ) {
					// Nicht löschen wenn noch aktiv - loggen
					error_log( "Repro CT-Suite: Alter Ordner '$old_folder' ist noch aktiv - überspringe Löschung." );
					continue;
				}
				
				// Ordner rekursiv löschen
				self::delete_directory_recursive( $old_path );
				
				// Erfolg loggen
				error_log( "Repro CT-Suite: Alter Ordner '$old_folder' wurde bereinigt." );
			}
		}
	}

	/**
	 * Löscht ein Verzeichnis rekursiv.
	 * 
	 * @param string $dir Pfad zum Verzeichnis.
	 * @return bool True bei Erfolg, false bei Fehler.
	 */
	private static function delete_directory_recursive( $dir ) {
		if ( ! is_dir( $dir ) ) {
			return false;
		}
		
		$items = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator( $dir, RecursiveDirectoryIterator::SKIP_DOTS ),
			RecursiveIteratorIterator::CHILD_FIRST
		);
		
		foreach ( $items as $item ) {
			if ( $item->isDir() ) {
				rmdir( $item->getPathname() );
			} else {
				unlink( $item->getPathname() );
			}
		}
		
		return rmdir( $dir );
	}
}
