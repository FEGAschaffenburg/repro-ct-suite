<?php
/**
 * Migration V10: Shortcode Manager Erweiterungen
 * Fügt neue Felder zur rcts_shortcode_presets Tabelle hinzu
 */

// Einfügen in class-repro-ct-suite-migrations.php nach migrate_v9()

/**
 * Migration V10: Erweitere Shortcode-Presets Tabelle
 * Fügt neue Felder für Shortcode Manager v0.9.5 hinzu
 */
private static function migrate_v10() {
	global $wpdb;
	$presets_table = $wpdb->prefix . 'rcts_shortcode_presets';
	
	error_log( 'Repro CT-Suite: Starte Migration V10 - Shortcode Manager Erweiterungen' );
	
	// Prüfe und füge neue Spalten hinzu
	$columns_to_add = array(
		'shortcode_tag' => "VARCHAR(100) NULL AFTER name",
		'display_mode' => "VARCHAR(50) NULL AFTER view",
		'days_ahead' => "INT(11) NULL AFTER to_days",
		'show_time' => "TINYINT(1) DEFAULT 1 AFTER show_past",
		'show_location' => "TINYINT(1) DEFAULT 1 AFTER show_time",
		'show_description' => "TINYINT(1) DEFAULT 1 AFTER show_location",
		'show_organizer' => "TINYINT(1) DEFAULT 0 AFTER show_description"
	);
	
	foreach ( $columns_to_add as $column => $definition ) {
		$column_exists = $wpdb->get_results( 
			$wpdb->prepare( "SHOW COLUMNS FROM {$presets_table} LIKE %s", $column )
		);
		
		if ( empty( $column_exists ) ) {
			$wpdb->query( "ALTER TABLE {$presets_table} ADD COLUMN {$column} {$definition}" );
			error_log( "Migration V10: Spalte '{$column}' hinzugefügt" );
		}
	}
	
	// Migriere bestehende Daten: view -> display_mode
	$wpdb->query( "UPDATE {$presets_table} SET display_mode = view WHERE display_mode IS NULL AND view IS NOT NULL" );
	
	// Generiere shortcode_tags für bestehende Presets
	$presets = $wpdb->get_results( "SELECT id, name FROM {$presets_table} WHERE shortcode_tag IS NULL" );
	foreach ( $presets as $preset ) {
		$shortcode_tag = 'ct_' . sanitize_title( $preset->name );
		$wpdb->update(
			$presets_table,
			array( 'shortcode_tag' => $shortcode_tag ),
			array( 'id' => $preset->id )
		);
	}
	
	error_log( 'Migration V10: Shortcode Manager Erweiterungen abgeschlossen' );
}

// Auch die migrate() Methode erweitern um V10 aufzurufen:
// Nach dem V9 Block:
if ( version_compare( $current, '10', '<' ) ) {
	self::migrate_v10();
}
