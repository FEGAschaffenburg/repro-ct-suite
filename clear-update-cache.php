<?php
/**
 * Temporäres Script zum Löschen des Update-Caches
 * 
 * Dieses Script auf dem Server ausführen, um den Cache zu löschen:
 * php clear-update-cache.php
 */

// WordPress laden
require_once __DIR__ . '/../../../wp-load.php';

echo "Lösche Update-Caches...\n";

// Plugin-Update-Cache löschen
$deleted1 = delete_transient( 'repro_ct_suite_release_info' );
echo "repro_ct_suite_release_info: " . ( $deleted1 ? 'GELÖSCHT' : 'nicht vorhanden' ) . "\n";

// WordPress Plugin-Update-Cache löschen
$deleted2 = delete_site_transient( 'update_plugins' );
echo "update_plugins: " . ( $deleted2 ? 'GELÖSCHT' : 'nicht vorhanden' ) . "\n";

echo "\nCaches erfolgreich gelöscht!\n";
echo "Gehe jetzt zu: Dashboard > Aktualisierungen\n";
