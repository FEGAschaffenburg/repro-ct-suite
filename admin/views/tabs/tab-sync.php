<?php
/**
 * Synchronisation Tab Template
 *
 * Zeigt manuelle Synchronisations-Optionen und Status.
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
		<p><?php esc_html_e( 'Die Synchronisations-Funktionen werden in der nächsten Version verfügbar sein.', 'repro-ct-suite' ); ?></p>
	</div>
</div>

<div class="repro-ct-suite-card repro-ct-suite-mt-20">
	<div class="repro-ct-suite-card-header">
		<span class="dashicons dashicons-update"></span>
		<h3><?php esc_html_e( 'Geplante Synchronisations-Optionen', 'repro-ct-suite' ); ?></h3>
	</div>
	<div class="repro-ct-suite-card-body">
		<ul style="padding-left: 20px;">
			<li><?php esc_html_e( 'Alle Daten synchronisieren', 'repro-ct-suite' ); ?></li>
			<li><?php esc_html_e( 'Nur Termine synchronisieren', 'repro-ct-suite' ); ?></li>
			<li><?php esc_html_e( 'Nur Events synchronisieren', 'repro-ct-suite' ); ?></li>
			<li><?php esc_html_e( 'Synchronisations-Historie anzeigen', 'repro-ct-suite' ); ?></li>
			<li><?php esc_html_e( 'Fehlerhafte Einträge erneut versuchen', 'repro-ct-suite' ); ?></li>
		</ul>
	</div>
</div>
