<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Repro_CT_Suite {
    public function __construct() {
        // Initialisiere Hooks
        add_action( 'init', array( $this, 'load_textdomain' ) );
    }

    public function run() {
        // Plugin starten (Hooks, Shortcodes, Admin etc.)
    }

    public function load_textdomain() {
        load_plugin_textdomain( 'repro-ct-suite', false, dirname( plugin_basename( REPRO_CT_SUITE_FILE ) ) . '/languages' );
    }
}
