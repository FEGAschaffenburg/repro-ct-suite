<?php
/**
 * Dashboard Tab Template
 *
 * Zeigt Übersicht mit Statistiken, Schnellaktionen und Hilfe.
 *
 * @package    Repro_CT_Suite
 * @subpackage Repro_CT_Suite/admin/views/tabs
 * @since      0.1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Statistiken abrufen (TODO: Später aus Datenbank holen)
$appointments_count = 0;
$events_count = 0;
$last_sync_time = __( 'Nie', 'repro-ct-suite' );
$connection_status = 'not_configured';
$connection_label = __( 'Nicht konfiguriert', 'repro-ct-suite' );
$connection_description = __( 'ChurchTools-API noch nicht eingerichtet', 'repro-ct-suite' );
?>

<!-- Statistik-Grid -->
<div class="repro-ct-suite-grid repro-ct-suite-grid-3">
	
	<!-- Termine Card -->
	<div class="repro-ct-suite-card">
		<div class="repro-ct-suite-card-header">
			<span class="dashicons dashicons-calendar-alt"></span>
			<h3><?php esc_html_e( 'Termine', 'repro-ct-suite' ); ?></h3>
		</div>
		<div class="repro-ct-suite-card-body">
			<div style="font-size: 32px; font-weight: 600; color: #0073aa; margin-bottom: 10px;">
				<?php echo esc_html( $appointments_count ); ?>
			</div>
			<p class="description"><?php esc_html_e( 'Synchronisierte Termine', 'repro-ct-suite' ); ?></p>
		</div>
		<div class="repro-ct-suite-card-footer">
			<span class="repro-ct-suite-badge repro-ct-suite-badge-info">
				<?php 
				/* translators: %s: Last sync time */
				printf( esc_html__( 'Letzter Sync: %s', 'repro-ct-suite' ), esc_html( $last_sync_time ) ); 
				?>
			</span>
		</div>
	</div>

	<!-- Events Card -->
	<div class="repro-ct-suite-card">
		<div class="repro-ct-suite-card-header">
			<span class="dashicons dashicons-tickets-alt"></span>
			<h3><?php esc_html_e( 'Events', 'repro-ct-suite' ); ?></h3>
		</div>
		<div class="repro-ct-suite-card-body">
			<div style="font-size: 32px; font-weight: 600; color: #46b450; margin-bottom: 10px;">
				<?php echo esc_html( $events_count ); ?>
			</div>
			<p class="description"><?php esc_html_e( 'Synchronisierte Events', 'repro-ct-suite' ); ?></p>
		</div>
		<div class="repro-ct-suite-card-footer">
			<span class="repro-ct-suite-badge repro-ct-suite-badge-info">
				<?php 
				/* translators: %s: Last sync time */
				printf( esc_html__( 'Letzter Sync: %s', 'repro-ct-suite' ), esc_html( $last_sync_time ) ); 
				?>
			</span>
		</div>
	</div>

	<!-- Verbindungsstatus Card -->
	<div class="repro-ct-suite-card">
		<div class="repro-ct-suite-card-header">
			<span class="dashicons dashicons-cloud"></span>
			<h3><?php esc_html_e( 'Verbindung', 'repro-ct-suite' ); ?></h3>
		</div>
		<div class="repro-ct-suite-card-body">
			<div class="repro-ct-suite-flex" style="margin-bottom: 10px;">
				<span class="repro-ct-suite-status-dot warning"></span>
				<strong><?php echo esc_html( $connection_label ); ?></strong>
			</div>
			<p class="description"><?php echo esc_html( $connection_description ); ?></p>
		</div>
		<div class="repro-ct-suite-card-footer">
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=repro-ct-suite&tab=settings' ) ); ?>" class="repro-ct-suite-btn repro-ct-suite-btn-primary repro-ct-suite-btn-small">
				<?php esc_html_e( 'Jetzt einrichten', 'repro-ct-suite' ); ?>
			</a>
		</div>
	</div>
</div>

<!-- Schnellaktionen -->
<div class="repro-ct-suite-card repro-ct-suite-mt-20">
	<div class="repro-ct-suite-card-header">
		<span class="dashicons dashicons-admin-tools"></span>
		<h3><?php esc_html_e( 'Schnellaktionen', 'repro-ct-suite' ); ?></h3>
	</div>
	<div class="repro-ct-suite-card-body">
		<p><?php esc_html_e( 'Häufig verwendete Aktionen und Shortcuts.', 'repro-ct-suite' ); ?></p>
		<div class="repro-ct-suite-flex" style="margin-top: 15px;">
			<button class="repro-ct-suite-btn repro-ct-suite-btn-success repro-ct-suite-sync-btn" data-action="repro_ct_suite_sync_all" disabled>
				<span class="dashicons dashicons-update"></span>
				<?php esc_html_e( 'Jetzt synchronisieren', 'repro-ct-suite' ); ?>
			</button>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=repro-ct-suite&tab=settings' ) ); ?>" class="repro-ct-suite-btn repro-ct-suite-btn-secondary">
				<span class="dashicons dashicons-admin-settings"></span>
				<?php esc_html_e( 'Einstellungen', 'repro-ct-suite' ); ?>
			</a>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=repro-ct-suite-update' ) ); ?>" class="repro-ct-suite-btn repro-ct-suite-btn-secondary">
				<span class="dashicons dashicons-download"></span>
				<?php esc_html_e( 'Updates prüfen', 'repro-ct-suite' ); ?>
			</a>
		</div>
	</div>
</div>

<!-- Hilfe & Dokumentation Grid -->
<div class="repro-ct-suite-grid repro-ct-suite-grid-2 repro-ct-suite-mt-20">
	
	<!-- Dokumentation Card -->
	<div class="repro-ct-suite-card">
		<div class="repro-ct-suite-card-header">
			<span class="dashicons dashicons-book"></span>
			<h3><?php esc_html_e( 'Dokumentation', 'repro-ct-suite' ); ?></h3>
		</div>
		<div class="repro-ct-suite-card-body">
			<p><?php esc_html_e( 'Erfahren Sie, wie Sie das Plugin optimal nutzen können.', 'repro-ct-suite' ); ?></p>
			<ul style="margin-top: 10px; padding-left: 20px;">
				<li><a href="https://github.com/FEGAschaffenburg/repro-ct-suite#readme" target="_blank"><?php esc_html_e( 'Schnellstart-Anleitung', 'repro-ct-suite' ); ?></a></li>
				<li><a href="https://github.com/FEGAschaffenburg/repro-ct-suite#verwendung" target="_blank"><?php esc_html_e( 'Shortcodes verwenden', 'repro-ct-suite' ); ?></a></li>
				<li><a href="https://github.com/FEGAschaffenburg/repro-ct-suite/issues" target="_blank"><?php esc_html_e( 'Support & Fragen', 'repro-ct-suite' ); ?></a></li>
			</ul>
		</div>
	</div>

	<!-- System-Informationen Card -->
	<div class="repro-ct-suite-card">
		<div class="repro-ct-suite-card-header">
			<span class="dashicons dashicons-info"></span>
			<h3><?php esc_html_e( 'System-Informationen', 'repro-ct-suite' ); ?></h3>
		</div>
		<div class="repro-ct-suite-card-body">
			<table class="widefat" style="margin-top: 0;">
				<tbody>
					<tr>
						<td><strong><?php esc_html_e( 'Plugin-Version:', 'repro-ct-suite' ); ?></strong></td>
						<td><?php echo esc_html( REPRO_CT_SUITE_VERSION ); ?></td>
					</tr>
					<tr>
						<td><strong><?php esc_html_e( 'WordPress-Version:', 'repro-ct-suite' ); ?></strong></td>
						<td><?php echo esc_html( get_bloginfo( 'version' ) ); ?></td>
					</tr>
					<tr>
						<td><strong><?php esc_html_e( 'PHP-Version:', 'repro-ct-suite' ); ?></strong></td>
						<td><?php echo esc_html( phpversion() ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>
