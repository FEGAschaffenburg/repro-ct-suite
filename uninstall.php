<?php

// Sicherheitsprüfung
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Hier Aufräumarbeiten durchführen (Optionen löschen, Custom Tables entfernen)
// Beispiel:
// delete_option( 'repro_ct_suite_option' );
