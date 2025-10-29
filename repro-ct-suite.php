<?php
/**
 * Plugin Name: Repro CT-Suite
 * Plugin URI:  
 * Description: Grundgerüst für das Repro CT-Suite WordPress-Plugin.
 * Version:     0.1.0
 * Author:      Dein Name
 * Author URI:  
 * Text Domain: repro-ct-suite
 * Domain Path: /languages
 */

// Verhindern, dass die Datei direkt aufgerufen wird.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'REPRO_CT_SUITE_VERSION', '0.1.0' );
define( 'REPRO_CT_SUITE_FILE', __FILE__ );
define( 'REPRO_CT_SUITE_PATH', plugin_dir_path( __FILE__ ) );

require_once REPRO_CT_SUITE_PATH . 'includes/class-repro-ct-suite.php';

function repro_ct_suite_run_plugin() {
    $plugin = new Repro_CT_Suite();
    $plugin->run();
}
repro_ct_suite_run_plugin();
