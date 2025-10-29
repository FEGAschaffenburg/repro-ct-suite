<?php
// Einfaches Bootstrap für PHPUnit-Tests
// Lädt die Haupt-Plugin-Datei, wenn vorhanden.
$plugin = __DIR__ . '/../repro-ct-suite.php';
if (file_exists($plugin)) {
    require_once $plugin;
}
