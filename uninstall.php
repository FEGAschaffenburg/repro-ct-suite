<?php

// Sicherheitsprüfung
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Aufräumarbeiten beim Entfernen des Plugins:
// - Löscht Plugin-Optionen
// - Entfernt Custom-DB-Tabellen
// Unterstützt Single- und Multisite-Installationen

global $wpdb;

/**
 * Löscht alle Plugin-Daten (Optionen und Tabellen) für die aktuelle Site
 */
function repro_ct_suite_cleanup_site() {
    global $wpdb;

    // Optionen löschen
    $option_keys = array(
        // ChurchTools Zugangsdaten und Session
        'repro_ct_suite_ct_tenant',
        'repro_ct_suite_ct_username',
        'repro_ct_suite_ct_password',
        'repro_ct_suite_ct_session',
        // Gespeicherte Cookies (CT Client)
        'repro_ct_suite_ct_cookies',
        // Sync-Einstellungen
        'repro_ct_suite_sync_from_days',
        'repro_ct_suite_sync_to_days',
        // DB-Version
        'repro_ct_suite_db_version',
    );

    foreach ( $option_keys as $opt ) {
        delete_option( $opt );
    }

    // Custom Tabellen entfernen
    $tables = array(
        $wpdb->prefix . 'rcts_calendars',
        $wpdb->prefix . 'rcts_events',
        $wpdb->prefix . 'rcts_appointments',
        $wpdb->prefix . 'rcts_event_services',
        $wpdb->prefix . 'rcts_schedule',
    );

    foreach ( $tables as $table ) {
        // Sicherheitscheck: Nur unsere erwarteten Tabellen droppen
        if ( preg_match( '/^' . preg_quote( $wpdb->prefix, '/' ) . 'rcts_([a-z_]+)$/', $table ) ) {
            $wpdb->query( "DROP TABLE IF EXISTS `{$table}`" );
        }
    }
}

if ( is_multisite() ) {
    // Alle Blogs durchgehen und jeweils aufräumen
    $blog_ids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs}" );
    if ( ! empty( $blog_ids ) ) {
        foreach ( $blog_ids as $blog_id ) {
            switch_to_blog( (int) $blog_id );
            repro_ct_suite_cleanup_site();
        }
        restore_current_blog();
    }
} else {
    // Single-Site
    repro_ct_suite_cleanup_site();
}
