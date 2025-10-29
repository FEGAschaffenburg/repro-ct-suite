<?php
/**
 * Logs Tab Template
 *
 * Zeigt Synchronisations-Protokoll und Debug-Informationen.
 *
 * @package    Repro_CT_Suite
 * @subpackage Repro_CT_Suite/admin/views/tabs
 * @since      0.1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="repro-ct-suite-notice repro-ct-suite-notice-info">
	<span class="dashicons dashicons-info"></span>
	<div>
		<strong><?php esc_html_e( 'In Entwicklung', 'repro-ct-suite' ); ?></strong>
		<p><?php esc_html_e( 'Das Logging-System wird in der nächsten Version verfügbar sein.', 'repro-ct-suite' ); ?></p>
	</div>
</div>

<div class="repro-ct-suite-card repro-ct-suite-mt-20">
	<div class="repro-ct-suite-card-header">
		<span class="dashicons dashicons-list-view"></span>
		<h3><?php esc_html_e( 'Geplante Log-Funktionen', 'repro-ct-suite' ); ?></h3>
	</div>
	<div class="repro-ct-suite-card-body">
		<ul style="padding-left: 20px;">
			<li><?php esc_html_e( 'Synchronisations-Logs mit Zeitstempel', 'repro-ct-suite' ); ?></li>
			<li><?php esc_html_e( 'Fehlerprotokolle mit Details', 'repro-ct-suite' ); ?></li>
			<li><?php esc_html_e( 'API-Anfragen und Antworten', 'repro-ct-suite' ); ?></li>
			<li><?php esc_html_e( 'Log-Export als CSV/JSON', 'repro-ct-suite' ); ?></li>
			<li><?php esc_html_e( 'Logs nach Datum filtern', 'repro-ct-suite' ); ?></li>
		</ul>
	</div>
</div>
