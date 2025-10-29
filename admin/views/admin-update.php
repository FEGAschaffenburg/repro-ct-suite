<?php
/**
 * Update-Informationen Ansicht
 *
 * @package    Repro_CT_Suite
 * @subpackage Repro_CT_Suite/admin/views
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin-Daten abrufen
if ( ! function_exists( 'get_plugin_data' ) ) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
}
$plugin_data = get_plugin_data( REPRO_CT_SUITE_PATH . 'repro-ct-suite.php' );

// Update-Cache löschen wenn angefordert
if ( isset( $_GET['check_update'] ) && check_admin_referer( 'repro_ct_suite_check_update' ) ) {
	delete_transient( 'repro_ct_suite_release_info' );
	delete_site_transient( 'update_plugins' );
	wp_safe_redirect( admin_url( 'admin.php?page=repro-ct-suite-update&updated=1' ) );
	exit;
}
?>

<div class="wrap repro-ct-suite-admin-wrapper">
	<div class="repro-ct-suite-header">
		<h1><?php echo esc_html__( 'Plugin Update-Informationen', 'repro-ct-suite' ); ?></h1>
		<p><?php echo esc_html__( 'Informationen über verfügbare Updates und aktuelle Version.', 'repro-ct-suite' ); ?></p>
	</div>

	<?php if ( isset( $_GET['updated'] ) ) : ?>
		<div class="notice notice-success is-dismissible">
			<p><?php esc_html_e( 'Update-Check wurde durchgeführt. Bitte überprüfen Sie die Plugin-Seite für verfügbare Updates.', 'repro-ct-suite' ); ?></p>
		</div>
	<?php endif; ?>

	<div class="repro-ct-suite-content">
		<div class="card">
			<h2><?php esc_html_e( 'Aktuelle Version', 'repro-ct-suite' ); ?></h2>
			<table class="widefat">
				<tbody>
					<tr>
						<td><strong><?php esc_html_e( 'Plugin-Name:', 'repro-ct-suite' ); ?></strong></td>
						<td><?php echo esc_html( $plugin_data['Name'] ); ?></td>
					</tr>
					<tr>
						<td><strong><?php esc_html_e( 'Version:', 'repro-ct-suite' ); ?></strong></td>
						<td><?php echo esc_html( $plugin_data['Version'] ); ?></td>
					</tr>
					<tr>
						<td><strong><?php esc_html_e( 'Autor:', 'repro-ct-suite' ); ?></strong></td>
						<td><?php echo wp_kses_post( $plugin_data['Author'] ); ?></td>
					</tr>
					<tr>
						<td><strong><?php esc_html_e( 'Beschreibung:', 'repro-ct-suite' ); ?></strong></td>
						<td><?php echo esc_html( $plugin_data['Description'] ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>

		<div class="card" style="margin-top: 20px;">
			<h2><?php esc_html_e( 'Update-Quelle', 'repro-ct-suite' ); ?></h2>
			<table class="widefat">
				<tbody>
					<tr>
						<td><strong><?php esc_html_e( 'GitHub Repository:', 'repro-ct-suite' ); ?></strong></td>
						<td>
							<a href="https://github.com/FEGAschaffenburg/repro-ct-suite" target="_blank">
								FEGAschaffenburg/repro-ct-suite
							</a>
						</td>
					</tr>
					<tr>
						<td><strong><?php esc_html_e( 'Update-Methode:', 'repro-ct-suite' ); ?></strong></td>
						<td><?php esc_html_e( 'Automatisch via GitHub Releases', 'repro-ct-suite' ); ?></td>
					</tr>
					<tr>
						<td><strong><?php esc_html_e( 'Update-Intervall:', 'repro-ct-suite' ); ?></strong></td>
						<td><?php esc_html_e( 'Alle 12 Stunden (WordPress Standard)', 'repro-ct-suite' ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>

		<div class="card" style="margin-top: 20px;">
			<h2><?php esc_html_e( 'Update-Aktionen', 'repro-ct-suite' ); ?></h2>
			<p><?php esc_html_e( 'Führen Sie einen manuellen Update-Check durch, um zu prüfen, ob eine neue Version verfügbar ist.', 'repro-ct-suite' ); ?></p>
			<p>
				<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=repro-ct-suite-update&check_update=1' ), 'repro_ct_suite_check_update' ) ); ?>" class="button button-primary">
					<?php esc_html_e( 'Jetzt auf Updates prüfen', 'repro-ct-suite' ); ?>
				</a>
				<a href="<?php echo esc_url( admin_url( 'plugins.php' ) ); ?>" class="button button-secondary">
					<?php esc_html_e( 'Zu den Plugins', 'repro-ct-suite' ); ?>
				</a>
			</p>
		</div>

		<div class="card" style="margin-top: 20px;">
			<h2><?php esc_html_e( 'So funktionieren Updates', 'repro-ct-suite' ); ?></h2>
			<ol>
				<li><?php esc_html_e( 'Updates werden automatisch von GitHub abgerufen', 'repro-ct-suite' ); ?></li>
				<li><?php esc_html_e( 'WordPress prüft alle 12 Stunden auf neue Versionen', 'repro-ct-suite' ); ?></li>
				<li><?php esc_html_e( 'Verfügbare Updates erscheinen auf der Plugin-Seite', 'repro-ct-suite' ); ?></li>
				<li><?php esc_html_e( 'Updates können wie bei anderen Plugins installiert werden', 'repro-ct-suite' ); ?></li>
			</ol>
		</div>

		<div class="card" style="margin-top: 20px;">
			<h2><?php esc_html_e( 'Neue Version erstellen (für Entwickler)', 'repro-ct-suite' ); ?></h2>
			<ol>
				<li><?php esc_html_e( 'Version in repro-ct-suite.php und readme.txt aktualisieren', 'repro-ct-suite' ); ?></li>
				<li><?php esc_html_e( 'Änderungen committen und pushen', 'repro-ct-suite' ); ?></li>
				<li><?php esc_html_e( 'Auf GitHub ein neues Release erstellen (z.B. "v1.0.1")', 'repro-ct-suite' ); ?></li>
				<li><?php esc_html_e( 'Release-Notes im Changelog beschreiben', 'repro-ct-suite' ); ?></li>
				<li><?php esc_html_e( 'WordPress erkennt das Update automatisch', 'repro-ct-suite' ); ?></li>
			</ol>
			<p>
				<a href="https://github.com/FEGAschaffenburg/repro-ct-suite/releases" target="_blank" class="button">
					<?php esc_html_e( 'Zu GitHub Releases', 'repro-ct-suite' ); ?>
				</a>
			</p>
		</div>
	</div>
</div>
