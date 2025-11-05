<?php
/**
 * Update-Tab
 *
 * @package    Repro_CT_Suite
 * @subpackage Repro_CT_Suite/admin/views/tabs
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Updater laden
require_once plugin_dir_path( dirname( dirname( dirname( __FILE__ ) ) ) ) . 'includes/class-repro-ct-suite-updater.php';
$updater = new Repro_CT_Suite_Updater( REPRO_CT_SUITE_FILE, 'FEGAschaffenburg', 'repro-ct-suite' );

// Update-Informationen
$release_info = $updater->get_release_info();
$current_version = REPRO_CT_SUITE_VERSION;
$update_available = false;
$latest_version = $current_version;

if ( $release_info && ! is_wp_error( $release_info ) ) {
	$latest_version = isset( $release_info->tag_name ) ? ltrim( $release_info->tag_name, 'v' ) : $current_version;
	$update_available = version_compare( $latest_version, $current_version, '>' );
}
?>

<h2><?php esc_html_e( 'Update-Informationen', 'repro-ct-suite' ); ?></h2>
<p class="description">
	<?php esc_html_e( 'Plugin-Versionen und Update-Status.', 'repro-ct-suite' ); ?>
</p>

<div class="repro-ct-suite-grid repro-ct-suite-grid-2" style="margin-top: 20px;">
	
	<!-- Installierte Version -->
	<div class="repro-ct-suite-card">
		<div class="repro-ct-suite-card-header">
			<span class="dashicons dashicons-admin-plugins"></span>
			<h3><?php esc_html_e( 'Installiert', 'repro-ct-suite' ); ?></h3>
		</div>
		<div class="repro-ct-suite-card-body">
			<div style="font-size: 28px; font-weight: 600; color: #0073aa; margin-bottom: 5px;">
				<?php echo esc_html( 'v' . $current_version ); ?>
			</div>
			<p class="description"><?php esc_html_e( 'Aktuell installierte Version', 'repro-ct-suite' ); ?></p>
		</div>
	</div>

	<!-- Neueste Version -->
	<div class="repro-ct-suite-card">
		<div class="repro-ct-suite-card-header">
			<span class="dashicons dashicons-cloud"></span>
			<h3><?php esc_html_e( 'Verfügbar', 'repro-ct-suite' ); ?></h3>
		</div>
		<div class="repro-ct-suite-card-body">
			<div style="font-size: 28px; font-weight: 600; color: <?php echo $update_available ? '#46b450' : '#0073aa'; ?>; margin-bottom: 5px;">
				<?php echo esc_html( 'v' . $latest_version ); ?>
			</div>
			<p class="description">
				<?php 
				if ( $update_available ) {
					esc_html_e( 'Update verfügbar!', 'repro-ct-suite' );
				} else {
					esc_html_e( 'Sie nutzen die neueste Version', 'repro-ct-suite' );
				}
				?>
			</p>
		</div>
		<div class="repro-ct-suite-card-footer">
			<?php if ( $update_available ) : ?>
				<a href="<?php echo esc_url( admin_url( 'plugins.php' ) ); ?>" class="button button-primary">
					<?php esc_html_e( 'Jetzt updaten', 'repro-ct-suite' ); ?>
				</a>
			<?php endif; ?>
			<a href="<?php echo esc_url( admin_url( 'plugins.php?repro_ct_suite_check_update=1' ) ); ?>" class="button">
				<span class="dashicons dashicons-update"></span>
				<?php esc_html_e( 'Updates prüfen', 'repro-ct-suite' ); ?>
			</a>
		</div>
	</div>
</div>

<?php if ( $release_info && ! is_wp_error( $release_info ) && isset( $release_info->body ) ) : ?>
<div class="repro-ct-suite-card" style="margin-top: 20px;">
	<div class="repro-ct-suite-card-header">
		<span class="dashicons dashicons-info"></span>
		<h3><?php esc_html_e( 'Release-Notes', 'repro-ct-suite' ); ?></h3>
	</div>
	<div class="repro-ct-suite-card-body">
		<h4><?php echo esc_html( $release_info->name ); ?></h4>
		<div class="description" style="white-space: pre-wrap; line-height: 1.6;">
			<?php echo esc_html( $release_info->body ); ?>
		</div>
	</div>
	<div class="repro-ct-suite-card-footer">
		<a href="<?php echo esc_url( $release_info->html_url ); ?>" target="_blank" class="button">
			<span class="dashicons dashicons-external"></span>
			<?php esc_html_e( 'Auf GitHub ansehen', 'repro-ct-suite' ); ?>
		</a>
	</div>
</div>
<?php endif; ?>
