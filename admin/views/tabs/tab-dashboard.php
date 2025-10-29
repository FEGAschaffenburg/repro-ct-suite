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

// Repository laden
require_once REPRO_CT_SUITE_PATH . 'includes/repositories/class-repro-ct-suite-repository-base.php';
require_once REPRO_CT_SUITE_PATH . 'includes/repositories/class-repro-ct-suite-appointments-repository.php';

$appointments_repo = new Repro_CT_Suite_Appointments_Repository();

// Kombinierte Termine abrufen
$appointments_count = $appointments_repo->count_combined_appointments();
$upcoming_appointments = $appointments_repo->get_combined_appointments( array(
	'from'  => current_time( 'mysql' ),
	'limit' => 5,
) );

// Letzten Sync-Zeitpunkt holen (TODO: später aus Option/Transient)
$last_sync_time = get_option( 'repro_ct_suite_last_sync_time', __( 'Nie', 'repro-ct-suite' ) );
if ( $last_sync_time !== __( 'Nie', 'repro-ct-suite' ) ) {
	$last_sync_time = human_time_diff( strtotime( $last_sync_time ), current_time( 'timestamp' ) ) . ' ' . __( 'her', 'repro-ct-suite' );
}

// Verbindungsstatus prüfen
$ct_tenant   = get_option( 'repro_ct_suite_ct_tenant', '' );
$ct_username = get_option( 'repro_ct_suite_ct_username', '' );
$ct_password = get_option( 'repro_ct_suite_ct_password', '' );

if ( empty( $ct_tenant ) || empty( $ct_username ) || empty( $ct_password ) ) {
	$connection_status      = 'not_configured';
	$connection_label       = __( 'Nicht konfiguriert', 'repro-ct-suite' );
	$connection_description = __( 'ChurchTools-API noch nicht eingerichtet', 'repro-ct-suite' );
} else {
	$connection_status      = 'configured';
	$connection_label       = __( 'Konfiguriert', 'repro-ct-suite' );
	$connection_description = sprintf(
		/* translators: %s: ChurchTools tenant name */
		__( 'Verbunden mit: %s.church.tools', 'repro-ct-suite' ),
		esc_html( $ct_tenant )
	);
}
?>

<!-- Statistik-Grid -->
<div class="repro-ct-suite-grid repro-ct-suite-grid-2">
	
	<!-- Kombinierte Termine Card -->
	<div class="repro-ct-suite-card">
		<div class="repro-ct-suite-card-header">
			<span class="dashicons dashicons-calendar-alt"></span>
			<h3><?php esc_html_e( 'Termine', 'repro-ct-suite' ); ?></h3>
		</div>
		<div class="repro-ct-suite-card-body">
			<div style="font-size: 32px; font-weight: 600; color: #0073aa; margin-bottom: 10px;">
				<?php echo esc_html( $appointments_count ); ?>
			</div>
			<p class="description"><?php esc_html_e( 'Synchronisierte Termine (kombiniert aus Appointments & Events)', 'repro-ct-suite' ); ?></p>
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
				<span class="repro-ct-suite-status-dot <?php echo $connection_status === 'configured' ? 'success' : 'warning'; ?>"></span>
				<strong><?php echo esc_html( $connection_label ); ?></strong>
			</div>
			<p class="description"><?php echo esc_html( $connection_description ); ?></p>
			
			<!-- Plugin-Version und Update-Check -->
			<div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #f0f1f3;">
				<div class="repro-ct-suite-flex" style="align-items: center; gap: 10px;">
					<span class="description">
						<?php 
						printf( 
							/* translators: %s: Plugin version */
							esc_html__( 'Plugin-Version: %s', 'repro-ct-suite' ), 
							'<strong>' . esc_html( REPRO_CT_SUITE_VERSION ) . '</strong>' 
						); 
						?>
					</span>
					<a href="<?php echo esc_url( admin_url( 'plugins.php?repro_ct_suite_check_update=1' ) ); ?>" 
					   class="repro-ct-suite-btn repro-ct-suite-btn-sm repro-ct-suite-btn-secondary"
					   style="margin-left: auto;">
						<span class="dashicons dashicons-update" style="font-size: 14px; margin-top: 2px;"></span>
						<?php esc_html_e( 'Updates prüfen', 'repro-ct-suite' ); ?>
					</a>
				</div>
			</div>
		</div>
		<div class="repro-ct-suite-card-footer">
			<?php if ( $connection_status === 'not_configured' ) : ?>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=repro-ct-suite&tab=settings' ) ); ?>" class="repro-ct-suite-btn repro-ct-suite-btn-primary repro-ct-suite-btn-small">
				<?php esc_html_e( 'Jetzt einrichten', 'repro-ct-suite' ); ?>
			</a>
			<?php else : ?>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=repro-ct-suite&tab=settings' ) ); ?>" class="repro-ct-suite-btn repro-ct-suite-btn-secondary repro-ct-suite-btn-small">
				<?php esc_html_e( 'Einstellungen ändern', 'repro-ct-suite' ); ?>
			</a>
			<?php endif; ?>
		</div>
	</div>
</div>

<!-- Nächste Termine -->
<div class="repro-ct-suite-card repro-ct-suite-mt-20">
	<div class="repro-ct-suite-card-header">
		<span class="dashicons dashicons-calendar"></span>
		<h3><?php esc_html_e( 'Nächste Termine', 'repro-ct-suite' ); ?></h3>
		<?php if ( ! empty( $ct_tenant ) && ! empty( $ct_username ) && ! empty( $ct_password ) ) : ?>
			<button type="button" class="repro-ct-suite-btn repro-ct-suite-btn-sm repro-ct-suite-sync-appointments-btn" style="margin-left: auto;">
				<span class="dashicons dashicons-update"></span>
				<?php esc_html_e( 'Jetzt synchronisieren', 'repro-ct-suite' ); ?>
			</button>
		<?php endif; ?>
	</div>
	<div class="repro-ct-suite-card-body">
		<?php if ( ! empty( $upcoming_appointments ) ) : ?>
			<table class="widefat" style="margin-top: 0;">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Datum & Zeit', 'repro-ct-suite' ); ?></th>
						<th><?php esc_html_e( 'Titel', 'repro-ct-suite' ); ?></th>
						<th><?php esc_html_e( 'Ort', 'repro-ct-suite' ); ?></th>
						<th><?php esc_html_e( 'Quelle', 'repro-ct-suite' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $upcoming_appointments as $appointment ) : ?>
						<?php
						$start = strtotime( $appointment->start_datetime );
						$date_format = get_option( 'date_format' );
						$time_format = get_option( 'time_format' );
						$formatted_date = date_i18n( $date_format, $start );
						$formatted_time = $appointment->is_all_day ? __( 'Ganztägig', 'repro-ct-suite' ) : date_i18n( $time_format, $start );
						?>
						<tr>
							<td>
								<strong><?php echo esc_html( $formatted_date ); ?></strong><br>
								<span class="description"><?php echo esc_html( $formatted_time ); ?></span>
							</td>
							<td>
								<strong><?php echo esc_html( $appointment->title ); ?></strong>
								<?php if ( ! empty( $appointment->description ) ) : ?>
									<br><span class="description"><?php echo esc_html( wp_trim_words( $appointment->description, 10 ) ); ?></span>
								<?php endif; ?>
							</td>
							<td>
								<?php echo ! empty( $appointment->location_name ) ? esc_html( $appointment->location_name ) : '—'; ?>
							</td>
							<td>
								<?php if ( $appointment->source === 'event' ) : ?>
									<span class="repro-ct-suite-badge repro-ct-suite-badge-success"><?php esc_html_e( 'Event', 'repro-ct-suite' ); ?></span>
								<?php else : ?>
									<span class="repro-ct-suite-badge repro-ct-suite-badge-info"><?php esc_html_e( 'Termin', 'repro-ct-suite' ); ?></span>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<div style="margin-top: 15px; text-align: right;">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=repro-ct-suite-appointments' ) ); ?>" class="repro-ct-suite-btn repro-ct-suite-btn-secondary repro-ct-suite-btn-small">
					<?php esc_html_e( 'Alle Termine ansehen', 'repro-ct-suite' ); ?>
				</a>
			</div>
		<?php else : ?>
			<p><?php esc_html_e( 'Keine bevorstehenden Termine gefunden.', 'repro-ct-suite' ); ?></p>
			<?php if ( $connection_status === 'configured' ) : ?>
				<button class="repro-ct-suite-btn repro-ct-suite-btn-primary repro-ct-suite-sync-btn" data-action="repro_ct_suite_sync_all" disabled>
					<span class="dashicons dashicons-update"></span>
					<?php esc_html_e( 'Jetzt synchronisieren', 'repro-ct-suite' ); ?>
				</button>
			<?php endif; ?>
		<?php endif; ?>
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
