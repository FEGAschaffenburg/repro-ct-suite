<?php
/**
 * Einstellungen Tab Template
 *
 * Zeigt Formular für ChurchTools-API-Konfiguration.
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
		<p><?php esc_html_e( 'Die Einstellungen für die ChurchTools-API werden in der nächsten Version verfügbar sein.', 'repro-ct-suite' ); ?></p>
	</div>
</div>

<div class="repro-ct-suite-card repro-ct-suite-mt-20">
	<div class="repro-ct-suite-card-header">
		<span class="dashicons dashicons-admin-settings"></span>
		<h3><?php esc_html_e( 'Geplante Einstellungen', 'repro-ct-suite' ); ?></h3>
	</div>
	<div class="repro-ct-suite-card-body">
		<ul style="padding-left: 20px;">
			<li><?php esc_html_e( 'ChurchTools API URL', 'repro-ct-suite' ); ?></li>
			<li><?php esc_html_e( 'ChurchTools API Token', 'repro-ct-suite' ); ?></li>
			<li><?php esc_html_e( 'Synchronisations-Intervall', 'repro-ct-suite' ); ?></li>
			<li><?php esc_html_e( 'Kalender-Auswahl', 'repro-ct-suite' ); ?></li>
			<li><?php esc_html_e( 'Event-Kategorien', 'repro-ct-suite' ); ?></li>
		</ul>
	</div>
</div>
