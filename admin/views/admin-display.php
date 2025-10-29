<?php
/**
 * Hauptansicht für den Admin-Bereich
 *
 * Zeigt die Haupt-Admin-Seite mit Tab-Navigation für verschiedene Bereiche:
 * - Dashboard: Übersicht und Statistiken
 * - Einstellungen: ChurchTools-Konfiguration
 * - Synchronisation: Manuelle Sync-Optionen
 * - Logs: Synchronisations-Protokoll
 *
 * @package    Repro_CT_Suite
 * @subpackage Repro_CT_Suite/admin/views
 * @since      1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Aktuellen Tab ermitteln (Standard: dashboard)
$active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'dashboard';
?>

<div class="wrap repro-ct-suite-admin-wrapper">
	
	<!-- Header-Bereich -->
	<div class="repro-ct-suite-header">
		<h1>
			<span class="dashicons dashicons-admin-generic"></span>
			<?php echo esc_html( get_admin_page_title() ); ?>
		</h1>
		<p><?php esc_html_e( 'ChurchTools-Integration für WordPress. Synchronisieren Sie Termine und Events aus ChurchTools.', 'repro-ct-suite' ); ?></p>
	</div>

	<!-- Tab-Navigation -->
	<div class="repro-ct-suite-tabs">
		<ul class="repro-ct-suite-tabs-nav">
			<li>
				<a href="#dashboard" class="<?php echo $active_tab === 'dashboard' ? 'active' : ''; ?>">
					<span class="dashicons dashicons-dashboard"></span>
					<?php esc_html_e( 'Dashboard', 'repro-ct-suite' ); ?>
				</a>
			</li>
			<li>
				<a href="#settings" class="<?php echo $active_tab === 'settings' ? 'active' : ''; ?>">
					<span class="dashicons dashicons-admin-settings"></span>
					<?php esc_html_e( 'Einstellungen', 'repro-ct-suite' ); ?>
				</a>
			</li>
			<li>
				<a href="#sync" class="<?php echo $active_tab === 'sync' ? 'active' : ''; ?>">
					<span class="dashicons dashicons-update"></span>
					<?php esc_html_e( 'Synchronisation', 'repro-ct-suite' ); ?>
				</a>
			</li>
			<li>
				<a href="#logs" class="<?php echo $active_tab === 'logs' ? 'active' : ''; ?>">
					<span class="dashicons dashicons-list-view"></span>
					<?php esc_html_e( 'Logs', 'repro-ct-suite' ); ?>
				</a>
			</li>
		</ul>
	</div>

	<!-- Tab: Dashboard -->
	<div id="dashboard" class="repro-ct-suite-tab-content <?php echo $active_tab === 'dashboard' ? 'active' : ''; ?>">
		
		<!-- Statistik-Grid -->
		<div class="repro-ct-suite-grid repro-ct-suite-grid-3">
			
			<!-- Termine -->
			<div class="repro-ct-suite-card">
				<div class="repro-ct-suite-card-header">
					<span class="dashicons dashicons-calendar-alt"></span>
					<h3><?php esc_html_e( 'Termine', 'repro-ct-suite' ); ?></h3>
				</div>
				<div class="repro-ct-suite-card-body">
					<div style="font-size: 32px; font-weight: 600; color: #0073aa; margin-bottom: 10px;">
						<?php echo esc_html( '0' ); // TODO: Echte Anzahl ?>
					</div>
					<p class="description"><?php esc_html_e( 'Synchronisierte Termine', 'repro-ct-suite' ); ?></p>
				</div>
				<div class="repro-ct-suite-card-footer">
					<span class="repro-ct-suite-badge repro-ct-suite-badge-info">
						<?php esc_html_e( 'Letzter Sync: Nie', 'repro-ct-suite' ); ?>
					</span>
				</div>
			</div>

			<!-- Events -->
			<div class="repro-ct-suite-card">
				<div class="repro-ct-suite-card-header">
					<span class="dashicons dashicons-tickets-alt"></span>
					<h3><?php esc_html_e( 'Events', 'repro-ct-suite' ); ?></h3>
				</div>
				<div class="repro-ct-suite-card-body">
					<div style="font-size: 32px; font-weight: 600; color: #46b450; margin-bottom: 10px;">
						<?php echo esc_html( '0' ); // TODO: Echte Anzahl ?>
					</div>
					<p class="description"><?php esc_html_e( 'Synchronisierte Events', 'repro-ct-suite' ); ?></p>
				</div>
				<div class="repro-ct-suite-card-footer">
					<span class="repro-ct-suite-badge repro-ct-suite-badge-info">
						<?php esc_html_e( 'Letzter Sync: Nie', 'repro-ct-suite' ); ?>
					</span>
				</div>
			</div>

			<!-- Status -->
			<div class="repro-ct-suite-card">
				<div class="repro-ct-suite-card-header">
					<span class="dashicons dashicons-cloud"></span>
					<h3><?php esc_html_e( 'Verbindung', 'repro-ct-suite' ); ?></h3>
				</div>
				<div class="repro-ct-suite-card-body">
					<div class="repro-ct-suite-flex" style="margin-bottom: 10px;">
						<span class="repro-ct-suite-status-dot warning"></span>
						<strong><?php esc_html_e( 'Nicht konfiguriert', 'repro-ct-suite' ); ?></strong>
					</div>
					<p class="description"><?php esc_html_e( 'ChurchTools-API noch nicht eingerichtet', 'repro-ct-suite' ); ?></p>
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

		<!-- Hilfe & Dokumentation -->
		<div class="repro-ct-suite-grid repro-ct-suite-grid-2 repro-ct-suite-mt-20">
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

	</div>

	<!-- Tab: Einstellungen -->
	<div id="settings" class="repro-ct-suite-tab-content <?php echo $active_tab === 'settings' ? 'active' : ''; ?>">
		<div class="repro-ct-suite-notice repro-ct-suite-notice-info">
			<span class="dashicons dashicons-info"></span>
			<div><?php esc_html_e( 'Einstellungen werden in Kürze verfügbar sein.', 'repro-ct-suite' ); ?></div>
		</div>
	</div>

	<!-- Tab: Synchronisation -->
	<div id="sync" class="repro-ct-suite-tab-content <?php echo $active_tab === 'sync' ? 'active' : ''; ?>">
		<div class="repro-ct-suite-notice repro-ct-suite-notice-info">
			<span class="dashicons dashicons-info"></span>
			<div><?php esc_html_e( 'Synchronisations-Optionen werden in Kürze verfügbar sein.', 'repro-ct-suite' ); ?></div>
		</div>
	</div>

	<!-- Tab: Logs -->
	<div id="logs" class="repro-ct-suite-tab-content <?php echo $active_tab === 'logs' ? 'active' : ''; ?>">
		<div class="repro-ct-suite-notice repro-ct-suite-notice-info">
			<span class="dashicons dashicons-info"></span>
			<div><?php esc_html_e( 'Logs werden in Kürze verfügbar sein.', 'repro-ct-suite' ); ?></div>
		</div>
	</div>

</div>
