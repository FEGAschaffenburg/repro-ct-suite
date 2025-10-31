<?php
/**
 * Debug-Script: Tabellen-Duplikate prüfen
 * 
 * Dieses Script prüft, warum Event-Services möglicherweise doppelt angezeigt werden.
 * Führe es in einem WordPress-Kontext aus (z.B. als Plugin-Test oder in wp-cli).
 */

// Sicherheitsprüfung
if ( ! defined( 'ABSPATH' ) ) {
    // Für wp-cli oder direkte Ausführung
    require_once( dirname( __FILE__ ) . '/../../wp-config.php' );
}

global $wpdb;

echo "=== Repro CT-Suite Debug: Tabellen-Analyse ===\n\n";

// 1. Aktuelles WordPress Prefix anzeigen
echo "WordPress Prefix: " . $wpdb->prefix . "\n";

// 2. Alle Plugin-bezogenen Tabellen finden
$plugin_tables = $wpdb->get_results( "SHOW TABLES LIKE '{$wpdb->prefix}%rcts_%'" );

echo "\nGefundene Plugin-Tabellen:\n";
foreach ( $plugin_tables as $table ) {
    $table_name = array_values( (array) $table )[0];
    $count = $wpdb->get_var( "SELECT COUNT(*) FROM `{$table_name}`" );
    echo "  - {$table_name} ({$count} Einträge)\n";
}

// 3. Spezifisch Event-Services Tabellen prüfen
echo "\nEvent-Services bezogene Tabellen:\n";
$event_service_tables = $wpdb->get_results( "SHOW TABLES LIKE '%event_service%'" );
foreach ( $event_service_tables as $table ) {
    $table_name = array_values( (array) $table )[0];
    $count = $wpdb->get_var( "SELECT COUNT(*) FROM `{$table_name}`" );
    echo "  - {$table_name} ({$count} Einträge)\n";
}

// 4. Alle Tabellen mit 'rcts' im Namen
echo "\nAlle 'rcts' Tabellen (alle Prefixe):\n";
$all_rcts_tables = $wpdb->get_results( "SHOW TABLES LIKE '%rcts_%'" );
foreach ( $all_rcts_tables as $table ) {
    $table_name = array_values( (array) $table )[0];
    $count = $wpdb->get_var( "SELECT COUNT(*) FROM `{$table_name}`" );
    echo "  - {$table_name} ({$count} Einträge)\n";
}

// 5. Erwartete vs. tatsächliche Tabellennamen
echo "\nErwartete Tabellennamen:\n";
$expected_tables = array(
    'calendars' => $wpdb->prefix . 'rcts_calendars',
    'events' => $wpdb->prefix . 'rcts_events',
    'appointments' => $wpdb->prefix . 'rcts_appointments',
    'event_services' => $wpdb->prefix . 'rcts_event_services',
    'schedule' => $wpdb->prefix . 'rcts_schedule',
);

foreach ( $expected_tables as $type => $expected_name ) {
    $exists = $wpdb->get_var( "SHOW TABLES LIKE '{$expected_name}'" );
    $count = $exists ? $wpdb->get_var( "SELECT COUNT(*) FROM `{$expected_name}`" ) : 0;
    $status = $exists ? "✅ EXISTS ({$count} Einträge)" : "❌ MISSING";
    echo "  - {$type}: {$expected_name} - {$status}\n";
}

echo "\n=== Ende der Analyse ===\n";