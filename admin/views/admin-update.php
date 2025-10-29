<?php
/**
 * Update-Informationsseite für das Plugin
 *
 * Zeigt Informationen zu verfügbaren Updates und ermöglicht
 * manuelle Update-Prüfungen. Enthält auch Anweisungen für
 * Entwickler zur Erstellung neuer Releases auf GitHub.
 *
 * @package    Repro_CT_Suite
 * @subpackage Repro_CT_Suite/admin/views
 * @since      1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Instanz des Updaters holen
require_once plugin_dir_path( dirname( __DIR__ ) ) . 'includes/class-repro-ct-suite-updater.php';
$updater = new Repro_CT_Suite_Updater( REPRO_CT_SUITE_FILE, 'FEGAschaffenburg', 'repro-ct-suite' );

// Update-Cache löschen wenn angefordert
if ( isset( $_GET['check_update'] ) && check_admin_referer( 'repro_ct_suite_check_update' ) ) {
	delete_transient( 'repro_ct_suite_release_info' );
	delete_site_transient( 'update_plugins' );
	wp_safe_redirect( admin_url( 'admin.php?page=repro-ct-suite-update&updated=1' ) );
	exit;
}

// Update-Informationen von GitHub abrufen
$release_info = $updater->get_release_info();
$current_version = REPRO_CT_SUITE_VERSION;
$update_available = false;
$latest_version = $current_version;
$auto_update_enabled = (bool) get_option( 'repro_ct_suite_auto_update', 0 );

if ( $release_info && ! is_wp_error( $release_info ) ) {
	// $release_info ist ein Objekt von der GitHub API
	$latest_version = isset( $release_info->tag_name ) ? ltrim( $release_info->tag_name, 'v' ) : $current_version;
	$update_available = version_compare( $latest_version, $current_version, '>' );
}
?>

<div class="wrap repro-ct-suite-admin-wrapper">
	
	<!-- Header-Bereich -->
	<div class="repro-ct-suite-header">
		<h1>
			<span class="dashicons dashicons-update"></span>
			<?php echo esc_html( get_admin_page_title() ); ?>
		</h1>
		<p><?php esc_html_e( 'Informationen zu Plugin-Updates und Release-Verwaltung.', 'repro-ct-suite' ); ?></p>
	</div>

	<!-- Erfolgsnotiz nach Update-Check -->
	<?php if ( isset( $_GET['updated'] ) ) : ?>
		<div class="repro-ct-suite-notice repro-ct-suite-notice-success">
			<span class="dashicons dashicons-yes-alt"></span>
			<div>
				<strong><?php esc_html_e( 'Update-Check abgeschlossen!', 'repro-ct-suite' ); ?></strong>
				<p><?php esc_html_e( 'Bitte überprüfen Sie die Plugin-Seite für verfügbare Updates.', 'repro-ct-suite' ); ?></p>
			</div>
		</div>
	<?php endif; ?>

	<!-- Versionsübersicht -->
	<div class="repro-ct-suite-grid repro-ct-suite-grid-2">
		
		<!-- Aktuelle Version -->
		<div class="repro-ct-suite-card">
			<div class="repro-ct-suite-card-header">
				<span class="dashicons dashicons-admin-plugins"></span>
				<h3><?php esc_html_e( 'Installierte Version', 'repro-ct-suite' ); ?></h3>
			</div>
			<div class="repro-ct-suite-card-body">
				<div style="font-size: 32px; font-weight: 600; color: #0073aa; margin-bottom: 10px;">
					<?php echo esc_html( 'v' . $current_version ); ?>
				</div>
				<p class="description"><?php esc_html_e( 'Aktuell auf Ihrer Website installiert', 'repro-ct-suite' ); ?></p>
			</div>
		</div>

		<!-- Neueste Version -->
		<div class="repro-ct-suite-card">
			<div class="repro-ct-suite-card-header">
				<span class="dashicons dashicons-cloud"></span>
				<h3><?php esc_html_e( 'Neueste Version', 'repro-ct-suite' ); ?></h3>
			</div>
			<div class="repro-ct-suite-card-body">
				<div style="font-size: 32px; font-weight: 600; color: <?php echo $update_available ? '#46b450' : '#0073aa'; ?>; margin-bottom: 10px;">
					<?php echo esc_html( 'v' . $latest_version ); ?>
				</div>
				<p class="description">
					<?php 
					if ( $update_available ) {
						esc_html_e( 'Ein neues Update ist verfügbar!', 'repro-ct-suite' );
					} else {
						esc_html_e( 'Sie nutzen die neueste Version', 'repro-ct-suite' );
					}
					?>
				</p>
			</div>
			<div class="repro-ct-suite-card-footer">
				<?php if ( $update_available ) : ?>
					<span class="repro-ct-suite-badge repro-ct-suite-badge-success">
						<?php esc_html_e( 'Update verfügbar', 'repro-ct-suite' ); ?>
					</span>
				<?php else : ?>
					<span class="repro-ct-suite-badge repro-ct-suite-badge-info">
						<?php esc_html_e( 'Aktuell', 'repro-ct-suite' ); ?>
					</span>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<!-- Update-Status -->
	<?php if ( $update_available && $release_info ) : ?>
		<div class="repro-ct-suite-notice repro-ct-suite-notice-success repro-ct-suite-mt-20">
			<span class="dashicons dashicons-yes-alt"></span>
			<div>
				<strong><?php esc_html_e( 'Neues Update verfügbar!', 'repro-ct-suite' ); ?></strong>
				<p>
					<?php 
					/* translators: %s: Version number */
					printf( esc_html__( 'Version %s ist jetzt verfügbar. Bitte aktualisieren Sie das Plugin über die WordPress-Update-Seite.', 'repro-ct-suite' ), esc_html( $latest_version ) ); 
					?>
				</p>
				<a href="<?php echo esc_url( admin_url( 'plugins.php' ) ); ?>" class="repro-ct-suite-btn repro-ct-suite-btn-success repro-ct-suite-btn-small repro-ct-suite-mt-10">
					<span class="dashicons dashicons-update"></span>
					<?php esc_html_e( 'Zur Plugin-Seite', 'repro-ct-suite' ); ?>
				</a>
			</div>
		</div>
	<?php else : ?>
		<div class="repro-ct-suite-notice repro-ct-suite-notice-info repro-ct-suite-mt-20">
			<span class="dashicons dashicons-info"></span>
			<div>
				<strong><?php esc_html_e( 'Alles aktuell!', 'repro-ct-suite' ); ?></strong>
				<p><?php esc_html_e( 'Sie nutzen die neueste Version des Plugins. Es sind keine Updates verfügbar.', 'repro-ct-suite' ); ?></p>
			</div>
		</div>
	<?php endif; ?>

	<!-- Update-Aktionen -->
	<div class="repro-ct-suite-card repro-ct-suite-mt-20">
		<div class="repro-ct-suite-card-header">
			<span class="dashicons dashicons-admin-tools"></span>
			<h3><?php esc_html_e( 'Update-Aktionen', 'repro-ct-suite' ); ?></h3>
		</div>
		<div class="repro-ct-suite-card-body">
			<p><?php esc_html_e( 'Führen Sie einen manuellen Update-Check durch oder verwalten Sie Ihre Plugin-Installation.', 'repro-ct-suite' ); ?></p>
			<div class="repro-ct-suite-flex repro-ct-suite-mt-15">
				<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=repro-ct-suite-update&check_update=1' ), 'repro_ct_suite_check_update' ) ); ?>" class="repro-ct-suite-btn repro-ct-suite-btn-primary">
					<span class="dashicons dashicons-update"></span>
					<?php esc_html_e( 'Jetzt auf Updates prüfen', 'repro-ct-suite' ); ?>
				</a>
				<a href="<?php echo esc_url( admin_url( 'plugins.php' ) ); ?>" class="repro-ct-suite-btn repro-ct-suite-btn-secondary">
					<span class="dashicons dashicons-admin-plugins"></span>
					<?php esc_html_e( 'Zu den Plugins', 'repro-ct-suite' ); ?>
				</a>
			</div>
		</div>
	</div>

	<!-- Auto-Update Einstellung -->
	<div class="repro-ct-suite-card repro-ct-suite-mt-20">
		<div class="repro-ct-suite-card-header">
			<span class="dashicons dashicons-backup"></span>
			<h3><?php esc_html_e( 'Automatische Updates', 'repro-ct-suite' ); ?></h3>
		</div>
		<div class="repro-ct-suite-card-body">
			<p class="description"><?php esc_html_e( 'Wenn aktiviert, aktualisiert WordPress dieses Plugin automatisch im Hintergrund, sobald ein neues Release verfügbar ist.', 'repro-ct-suite' ); ?></p>
			<div class="repro-ct-suite-mt-10">
				<strong><?php esc_html_e( 'Status:', 'repro-ct-suite' ); ?></strong>
				<span class="repro-ct-suite-badge <?php echo $auto_update_enabled ? 'repro-ct-suite-badge-success' : 'repro-ct-suite-badge-info'; ?>">
					<?php echo $auto_update_enabled ? esc_html__( 'Aktiviert', 'repro-ct-suite' ) : esc_html__( 'Deaktiviert', 'repro-ct-suite' ); ?>
				</span>
			</div>

			<form method="post" action="options.php" class="repro-ct-suite-mt-15">
				<?php settings_fields( 'repro_ct_suite' ); ?>
				<input type="hidden" name="repro_ct_suite_auto_update" value="0" />
				<label>
					<input type="checkbox" name="repro_ct_suite_auto_update" value="1" <?php checked( $auto_update_enabled ); ?> />
					<?php esc_html_e( 'Automatische Updates aktivieren', 'repro-ct-suite' ); ?>
				</label>
				<p class="description repro-ct-suite-mt-5"><?php esc_html_e( 'Hinweis: Auto-Updates erfordern ein gültiges GitHub-Release mit ZIP-Asset. Die Einstellung wirkt sich nur auf dieses Plugin aus.', 'repro-ct-suite' ); ?></p>
				<p class="repro-ct-suite-mt-10">
					<button type="submit" class="repro-ct-suite-btn repro-ct-suite-btn-primary">
						<span class="dashicons dashicons-yes"></span>
						<?php esc_html_e( 'Einstellung speichern', 'repro-ct-suite' ); ?>
					</button>
				</p>
			</form>
		</div>
	</div>

	<!-- Release-Informationen -->
	<?php if ( $release_info && ! is_wp_error( $release_info ) && isset( $release_info->body ) ) : ?>
		<div class="repro-ct-suite-card repro-ct-suite-mt-20">
			<div class="repro-ct-suite-card-header">
				<span class="dashicons dashicons-megaphone"></span>
				<h3><?php esc_html_e( 'Was ist neu?', 'repro-ct-suite' ); ?></h3>
			</div>
			<div class="repro-ct-suite-card-body">
				<?php if ( isset( $release_info->name ) ) : ?>
					<h4><?php echo esc_html( $release_info->name ); ?></h4>
				<?php endif; ?>
				<?php if ( isset( $release_info->published_at ) ) : ?>
					<p class="description">
						<?php 
						/* translators: %s: Date */
						printf( esc_html__( 'Veröffentlicht am %s', 'repro-ct-suite' ), esc_html( date_i18n( get_option( 'date_format' ), strtotime( $release_info->published_at ) ) ) ); 
						?>
					</p>
				<?php endif; ?>
				<div class="repro-ct-suite-mt-15">
					<?php echo wp_kses_post( wpautop( $release_info->body ) ); ?>
				</div>
				<?php if ( isset( $release_info->html_url ) ) : ?>
					<a href="<?php echo esc_url( $release_info->html_url ); ?>" target="_blank" class="repro-ct-suite-btn repro-ct-suite-btn-secondary repro-ct-suite-btn-small repro-ct-suite-mt-15">
						<span class="dashicons dashicons-external"></span>
						<?php esc_html_e( 'Auf GitHub ansehen', 'repro-ct-suite' ); ?>
					</a>
				<?php endif; ?>
			</div>
		</div>
	<?php endif; ?>

	<!-- Entwickler-Informationen -->
	<div class="repro-ct-suite-card repro-ct-suite-mt-20">
		<div class="repro-ct-suite-card-header">
			<span class="dashicons dashicons-admin-tools"></span>
			<h3><?php esc_html_e( 'Für Entwickler: Neues Release erstellen', 'repro-ct-suite' ); ?></h3>
		</div>
		<div class="repro-ct-suite-card-body">
			<p><?php esc_html_e( 'So erstellen Sie ein neues Release auf GitHub:', 'repro-ct-suite' ); ?></p>
			
			<ol style="padding-left: 20px; margin-top: 15px;">
				<li style="margin-bottom: 10px;">
					<strong><?php esc_html_e( 'Version in repro-ct-suite.php aktualisieren', 'repro-ct-suite' ); ?></strong>
					<pre style="background: #f5f5f5; padding: 10px; margin-top: 5px; border-radius: 3px; overflow-x: auto;"><code>* Version: 1.1.0</code></pre>
				</li>
				<li style="margin-bottom: 10px;">
					<strong><?php esc_html_e( 'Änderungen committen und pushen', 'repro-ct-suite' ); ?></strong>
					<pre style="background: #f5f5f5; padding: 10px; margin-top: 5px; border-radius: 3px; overflow-x: auto;"><code>git add repro-ct-suite.php
git commit -m "Bump version to 1.1.0"
git push origin main</code></pre>
				</li>
				<li style="margin-bottom: 10px;">
					<strong><?php esc_html_e( 'Release auf GitHub erstellen', 'repro-ct-suite' ); ?></strong>
					<ul style="margin-top: 5px; padding-left: 20px;">
						<li><?php esc_html_e( 'Gehen Sie zu:', 'repro-ct-suite' ); ?> <a href="https://github.com/FEGAschaffenburg/repro-ct-suite/releases/new" target="_blank">https://github.com/FEGAschaffenburg/repro-ct-suite/releases/new</a></li>
						<li><?php esc_html_e( 'Tag-Version: v1.1.0 (mit "v" Präfix)', 'repro-ct-suite' ); ?></li>
						<li><?php esc_html_e( 'Release-Titel: Version 1.1.0', 'repro-ct-suite' ); ?></li>
						<li><?php esc_html_e( 'Beschreibung: Changelog mit Features/Fixes', 'repro-ct-suite' ); ?></li>
						<li><?php esc_html_e( 'Klicken Sie auf "Publish release"', 'repro-ct-suite' ); ?></li>
					</ul>
				</li>
				<li style="margin-bottom: 10px;">
					<strong><?php esc_html_e( 'WordPress prüft automatisch auf Updates', 'repro-ct-suite' ); ?></strong>
					<p class="description"><?php esc_html_e( 'Das Plugin überprüft alle 12 Stunden automatisch auf neue Releases. Sie können auch manuell prüfen:', 'repro-ct-suite' ); ?></p>
					<a href="<?php echo esc_url( admin_url( 'plugins.php' ) ); ?>" class="repro-ct-suite-btn repro-ct-suite-btn-primary repro-ct-suite-btn-small repro-ct-suite-mt-10">
						<span class="dashicons dashicons-update"></span>
						<?php esc_html_e( 'Jetzt auf Updates prüfen', 'repro-ct-suite' ); ?>
					</a>
				</li>
			</ol>

			<div class="repro-ct-suite-notice repro-ct-suite-notice-warning repro-ct-suite-mt-20">
				<span class="dashicons dashicons-warning"></span>
				<div>
					<strong><?php esc_html_e( 'Wichtig:', 'repro-ct-suite' ); ?></strong>
					<p><?php esc_html_e( 'Stellen Sie sicher, dass die Version in der Plugin-Datei mit dem GitHub-Release-Tag übereinstimmt (ohne "v" Präfix in der Datei).', 'repro-ct-suite' ); ?></p>
				</div>
			</div>
		</div>
	</div>

	<!-- GitHub-Repository-Link -->
	<div class="repro-ct-suite-card repro-ct-suite-mt-20">
		<div class="repro-ct-suite-card-header">
			<span class="dashicons dashicons-admin-site-alt3"></span>
			<h3><?php esc_html_e( 'Repository & Support', 'repro-ct-suite' ); ?></h3>
		</div>
		<div class="repro-ct-suite-card-body">
			<div class="repro-ct-suite-grid repro-ct-suite-grid-3">
				<div>
					<h4><?php esc_html_e( 'GitHub Repository', 'repro-ct-suite' ); ?></h4>
					<p class="description"><?php esc_html_e( 'Quellcode und Entwicklung', 'repro-ct-suite' ); ?></p>
					<a href="https://github.com/FEGAschaffenburg/repro-ct-suite" target="_blank" class="repro-ct-suite-btn repro-ct-suite-btn-secondary repro-ct-suite-btn-small repro-ct-suite-mt-10">
						<span class="dashicons dashicons-external"></span>
						<?php esc_html_e( 'Zum Repository', 'repro-ct-suite' ); ?>
					</a>
				</div>
				<div>
					<h4><?php esc_html_e( 'Issues & Bugs', 'repro-ct-suite' ); ?></h4>
					<p class="description"><?php esc_html_e( 'Probleme melden', 'repro-ct-suite' ); ?></p>
					<a href="https://github.com/FEGAschaffenburg/repro-ct-suite/issues" target="_blank" class="repro-ct-suite-btn repro-ct-suite-btn-secondary repro-ct-suite-btn-small repro-ct-suite-mt-10">
						<span class="dashicons dashicons-external"></span>
						<?php esc_html_e( 'Issue erstellen', 'repro-ct-suite' ); ?>
					</a>
				</div>
				<div>
					<h4><?php esc_html_e( 'Releases', 'repro-ct-suite' ); ?></h4>
					<p class="description"><?php esc_html_e( 'Alle Versionen ansehen', 'repro-ct-suite' ); ?></p>
					<a href="https://github.com/FEGAschaffenburg/repro-ct-suite/releases" target="_blank" class="repro-ct-suite-btn repro-ct-suite-btn-secondary repro-ct-suite-btn-small repro-ct-suite-mt-10">
						<span class="dashicons dashicons-external"></span>
						<?php esc_html_e( 'Zu den Releases', 'repro-ct-suite' ); ?>
					</a>
				</div>
			</div>
		</div>
	</div>

</div>
