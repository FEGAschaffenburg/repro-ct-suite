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
		<p><?php esc_html_e( 'ChurchTools-Integration für WordPress. Synchronisieren Sie Veranstaltungen (Events) aus ChurchTools Events-API und Appointments-Terminvorlagen.', 'repro-ct-suite' ); ?></p>
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
		<?php require_once plugin_dir_path( __FILE__ ) . 'tabs/tab-dashboard.php'; ?>
	</div>

	<!-- Tab: Einstellungen -->
	<div id="settings" class="repro-ct-suite-tab-content <?php echo $active_tab === 'settings' ? 'active' : ''; ?>">
		<?php require_once plugin_dir_path( __FILE__ ) . 'tabs/tab-settings.php'; ?>
	</div>

	<!-- Tab: Synchronisation -->
	<div id="sync" class="repro-ct-suite-tab-content <?php echo $active_tab === 'sync' ? 'active' : ''; ?>">
		<?php require_once plugin_dir_path( __FILE__ ) . 'tabs/tab-sync.php'; ?>
	</div>

	<!-- Tab: Logs -->
	<div id="logs" class="repro-ct-suite-tab-content <?php echo $active_tab === 'logs' ? 'active' : ''; ?>">
		<?php require_once plugin_dir_path( __FILE__ ) . 'tabs/tab-logs.php'; ?>
	</div>

</div>
