<?php
/**
 * Hauptansicht für den Admin-Bereich - Modern Design
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

// Prüfen ob Cron aktiv ist
$auto_sync_enabled = get_option( 'repro_ct_suite_auto_sync_enabled', 0 );
?>

<div class="wrap rcts-modern-wrap">
	
	<!-- Header-Bereich -->
	<div class="rcts-header-section">
		<h1>
			<div class="rcts-header-logo">
				<img src="<?php echo esc_url( plugin_dir_url( dirname( dirname( __FILE__ ) ) ) . 'assets/images/churchtools-suite-icon.svg' ); ?>" alt="ChurchTools Suite">
			</div>
			<?php echo esc_html( get_admin_page_title() ); ?>
		</h1>
		<p><?php esc_html_e( 'ChurchTools-Integration für WordPress. Synchronisieren Sie Termine aus ChurchTools.', 'repro-ct-suite' ); ?></p>
	</div>

	<!-- Tab-Navigation -->
	<div class="rcts-tabs-wrapper">
		<ul class="rcts-tabs-nav">
			<li>
				<a href="?page=repro-ct-suite&tab=dashboard" class="<?php echo $active_tab === 'dashboard' ? 'active' : ''; ?>">
					<span class="dashicons dashicons-dashboard"></span>
					<?php esc_html_e( 'Dashboard', 'repro-ct-suite' ); ?>
				</a>
			</li>
			<li>
				<a href="?page=repro-ct-suite&tab=settings" class="<?php echo $active_tab === 'settings' ? 'active' : ''; ?>">
					<span class="dashicons dashicons-admin-settings"></span>
					<?php esc_html_e( 'Einstellungen', 'repro-ct-suite' ); ?>
				</a>
			</li>
			<?php if ( ! $auto_sync_enabled ) : ?>
			<li>
				<a href="?page=repro-ct-suite&tab=sync" class="<?php echo $active_tab === 'sync' ? 'active' : ''; ?>">
					<span class="dashicons dashicons-update"></span>
					<?php esc_html_e( 'Sync', 'repro-ct-suite' ); ?>
				</a>
			</li>
			<?php endif; ?>
			<li>
				<a href="?page=repro-ct-suite&tab=update" class="<?php echo $active_tab === 'update' ? 'active' : ''; ?>">
					<span class="dashicons dashicons-download"></span>
					<?php esc_html_e( 'Update', 'repro-ct-suite' ); ?>
				</a>
			</li>
			<li>
				<a href="?page=repro-ct-suite&tab=debug" class="<?php echo $active_tab === 'debug' ? 'active' : ''; ?>">
					<span class="dashicons dashicons-admin-tools"></span>
					<?php esc_html_e( 'Debug', 'repro-ct-suite' ); ?>
				</a>
			</li>
		</ul>
	</div>

	<!-- Tab: Dashboard -->
	<div id="dashboard" class="rcts-tab-content <?php echo $active_tab === 'dashboard' ? 'active' : ''; ?>">
		<?php require_once plugin_dir_path( __FILE__ ) . 'tabs/tab-dashboard.php'; ?>
	</div>

	<!-- Tab: Einstellungen -->
	<div id="settings" class="rcts-tab-content <?php echo $active_tab === 'settings' ? 'active' : ''; ?>">
		<?php require_once plugin_dir_path( __FILE__ ) . 'tabs/tab-settings.php'; ?>
	</div>

	<?php if ( ! $auto_sync_enabled ) : ?>
	<!-- Tab: Synchronisation (nur wenn kein Cron aktiv) -->
	<div id="sync" class="rcts-tab-content <?php echo $active_tab === 'sync' ? 'active' : ''; ?>">
		<?php require_once plugin_dir_path( __FILE__ ) . 'tabs/tab-sync.php'; ?>
	</div>
	<?php endif; ?>

	<!-- Tab: Update -->
	<div id="update" class="rcts-tab-content <?php echo $active_tab === 'update' ? 'active' : ''; ?>">
		<?php require_once plugin_dir_path( __FILE__ ) . 'tabs/tab-update.php'; ?>
	</div>

	<!-- Tab: Debug -->
	<div id="debug" class="rcts-tab-content <?php echo $active_tab === 'debug' ? 'active' : ''; ?>">
		<?php require_once plugin_dir_path( __FILE__ ) . 'tabs/tab-debug.php'; ?>
	</div>

</div>

