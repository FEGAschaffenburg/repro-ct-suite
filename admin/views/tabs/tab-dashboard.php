<?php
/**
 * Dashboard Tab - Vereinfacht
 *
 * Zeigt nur Status-Informationen ohne Termine und Schnellaktionen
 *
 * @package    Repro_CT_Suite
 * @subpackage Repro_CT_Suite/admin/views/tabs
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Verbindungsstatus
$ct_tenant   = get_option( 'repro_ct_suite_ct_tenant', '' );
$ct_username = get_option( 'repro_ct_suite_ct_username', '' );
$ct_password = get_option( 'repro_ct_suite_ct_password', '' );
$is_configured = ! empty( $ct_tenant ) && ! empty( $ct_username ) && ! empty( $ct_password );

// Cron-Status
$auto_sync_enabled = get_option( 'repro_ct_suite_auto_sync_enabled', 0 );
$next_scheduled = wp_next_scheduled( 'repro_ct_suite_auto_sync' );
$last_sync = get_option( 'repro_ct_suite_last_auto_sync', 0 );

// Statistiken
global $wpdb;
$events_table = $wpdb->prefix . 'rcts_events';
$events_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$events_table}" );

$calendars_table = $wpdb->prefix . 'rcts_calendars';
$calendars_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$calendars_table} WHERE is_selected = 1" );
?>

<h2><?php esc_html_e( 'Dashboard', 'repro-ct-suite' ); ?></h2>
<p class="description">
	<?php esc_html_e( 'Übersicht über den aktuellen Status der ChurchTools-Integration.', 'repro-ct-suite' ); ?>
</p>

<!-- Status-Grid -->
<div class="repro-ct-suite-grid repro-ct-suite-grid-3" style="margin-top: 20px;">
	
	<!-- ChurchTools Verbindung -->
	<div class="repro-ct-suite-card">
		<div class="repro-ct-suite-card-header">
			<span class="dashicons dashicons-cloud"></span>
			<h3><?php esc_html_e( 'ChurchTools', 'repro-ct-suite' ); ?></h3>
		</div>
		<div class="repro-ct-suite-card-body">
			<?php if ( $is_configured ) : ?>
				<div style="display: flex; align-items: center; gap: 8px; margin-bottom: 10px;">
					<span style="display: inline-block; width: 10px; height: 10px; background: #46b450; border-radius: 50%;"></span>
					<strong style="color: #46b450;"><?php esc_html_e( 'Verbunden', 'repro-ct-suite' ); ?></strong>
				</div>
				<p class="description">
					<?php printf( esc_html__( 'Tenant: %s.church.tools', 'repro-ct-suite' ), '<strong>' . esc_html( $ct_tenant ) . '</strong>' ); ?>
				</p>
			<?php else : ?>
				<div style="display: flex; align-items: center; gap: 8px; margin-bottom: 10px;">
					<span style="display: inline-block; width: 10px; height: 10px; background: #dc3232; border-radius: 50%;"></span>
					<strong style="color: #dc3232;"><?php esc_html_e( 'Nicht konfiguriert', 'repro-ct-suite' ); ?></strong>
				</div>
				<p class="description">
					<?php esc_html_e( 'Zugangsdaten fehlen', 'repro-ct-suite' ); ?>
				</p>
			<?php endif; ?>
		</div>
		<div class="repro-ct-suite-card-footer">
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=repro-ct-suite&tab=settings' ) ); ?>" class="button button-small">
				<?php esc_html_e( 'Einstellungen', 'repro-ct-suite' ); ?>
			</a>
		</div>
	</div>

	<!-- Automatischer Sync -->
	<div class="repro-ct-suite-card">
		<div class="repro-ct-suite-card-header">
			<span class="dashicons dashicons-update"></span>
			<h3><?php esc_html_e( 'Automatischer Sync', 'repro-ct-suite' ); ?></h3>
		</div>
		<div class="repro-ct-suite-card-body">
			<?php if ( $auto_sync_enabled ) : ?>
				<div style="display: flex; align-items: center; gap: 8px; margin-bottom: 10px;">
					<span style="display: inline-block; width: 10px; height: 10px; background: #46b450; border-radius: 50%;"></span>
					<strong style="color: #46b450;"><?php esc_html_e( 'Aktiv', 'repro-ct-suite' ); ?></strong>
				</div>
				<?php if ( $next_scheduled ) : ?>
					<p class="description">
						<strong><?php esc_html_e( 'Nächster Sync:', 'repro-ct-suite' ); ?></strong><br>
						<?php echo esc_html( date_i18n( 'd.m.Y H:i', $next_scheduled ) ); ?>
						<span style="color: #666;">
							(<?php echo esc_html( human_time_diff( time(), $next_scheduled ) ); ?>)
						</span>
					</p>
				<?php endif; ?>
			<?php else : ?>
				<div style="display: flex; align-items: center; gap: 8px; margin-bottom: 10px;">
					<span style="display: inline-block; width: 10px; height: 10px; background: #999; border-radius: 50%;"></span>
					<strong style="color: #999;"><?php esc_html_e( 'Deaktiviert', 'repro-ct-suite' ); ?></strong>
				</div>
				<p class="description">
					<?php esc_html_e( 'Automatische Synchronisation ist ausgeschaltet', 'repro-ct-suite' ); ?>
				</p>
			<?php endif; ?>
		</div>
		<div class="repro-ct-suite-card-footer">
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=repro-ct-suite&tab=settings#cron' ) ); ?>" class="button button-small">
				<?php esc_html_e( 'Konfigurieren', 'repro-ct-suite' ); ?>
			</a>
		</div>
	</div>

	<!-- Synchronisations-Statistik -->
	<div class="repro-ct-suite-card">
		<div class="repro-ct-suite-card-header">
			<span class="dashicons dashicons-calendar-alt"></span>
			<h3><?php esc_html_e( 'Synchronisation', 'repro-ct-suite' ); ?></h3>
		</div>
		<div class="repro-ct-suite-card-body">
			<div style="font-size: 28px; font-weight: 600; color: #0073aa; margin-bottom: 5px;">
				<?php echo esc_html( number_format_i18n( $events_count ) ); ?>
			</div>
			<p class="description" style="margin-bottom: 10px;">
				<?php esc_html_e( 'Termine gesamt', 'repro-ct-suite' ); ?>
			</p>
			<p class="description">
				<strong><?php printf( esc_html__( '%d Kalender ausgewählt', 'repro-ct-suite' ), $calendars_count ); ?></strong>
			</p>
		</div>
		<div class="repro-ct-suite-card-footer">
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=repro-ct-suite-events' ) ); ?>" class="button button-small">
				<?php esc_html_e( 'Termine ansehen', 'repro-ct-suite' ); ?>
			</a>
		</div>
	</div>
</div>

<!-- Cron-Details (nur wenn aktiv) -->
<?php if ( $auto_sync_enabled ) : ?>
<div class="repro-ct-suite-card" style="margin-top: 20px;">
	<div class="repro-ct-suite-card-header">
		<span class="dashicons dashicons-clock"></span>
		<h3><?php esc_html_e( 'Cron-Job Status', 'repro-ct-suite' ); ?></h3>
	</div>
	<div class="repro-ct-suite-card-body">
		<table class="widefat">
			<tbody>
				<tr>
					<td style="width: 30%; font-weight: 600;">
						<?php esc_html_e( 'Status', 'repro-ct-suite' ); ?>
					</td>
					<td>
						<span style="color: #46b450; font-weight: 600;">
							● <?php esc_html_e( 'Läuft', 'repro-ct-suite' ); ?>
						</span>
					</td>
				</tr>
				<tr>
					<td style="font-weight: 600;">
						<?php esc_html_e( 'Nächste Ausführung', 'repro-ct-suite' ); ?>
					</td>
					<td>
						<?php if ( $next_scheduled ) : ?>
							<strong><?php echo esc_html( date_i18n( 'd.m.Y H:i:s', $next_scheduled ) ); ?></strong>
							<span style="color: #666;">
								(<?php printf( esc_html__( 'in %s', 'repro-ct-suite' ), human_time_diff( time(), $next_scheduled ) ); ?>)
							</span>
						<?php else : ?>
							<span style="color: #dc3232;">
								<?php esc_html_e( 'Nicht geplant', 'repro-ct-suite' ); ?>
							</span>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<td style="font-weight: 600;">
						<?php esc_html_e( 'Letzte Ausführung', 'repro-ct-suite' ); ?>
					</td>
					<td>
						<?php if ( $last_sync ) : ?>
							<?php echo esc_html( date_i18n( 'd.m.Y H:i:s', $last_sync ) ); ?>
							<span style="color: #666;">
								(<?php echo esc_html( human_time_diff( $last_sync, time() ) ); ?> <?php esc_html_e( 'her', 'repro-ct-suite' ); ?>)
							</span>
						<?php else : ?>
							<span style="color: #999;">
								<?php esc_html_e( 'Noch keine Ausführung', 'repro-ct-suite' ); ?>
							</span>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<td style="font-weight: 600;">
						<?php esc_html_e( 'Intervall', 'repro-ct-suite' ); ?>
					</td>
					<td>
						<?php 
						$interval = get_option( 'repro_ct_suite_sync_interval', 60 );
						$unit = get_option( 'repro_ct_suite_sync_interval_unit', 'minutes' );
						$unit_label = array(
							'minutes' => _n( 'Minute', 'Minuten', $interval, 'repro-ct-suite' ),
							'hours' => _n( 'Stunde', 'Stunden', $interval, 'repro-ct-suite' ),
							'days' => _n( 'Tag', 'Tage', $interval, 'repro-ct-suite' ),
						);
						printf( esc_html__( '%1$d %2$s', 'repro-ct-suite' ), $interval, $unit_label[ $unit ] ?? $unit );
						?>
					</td>
				</tr>
				<tr>
					<td style="font-weight: 600;">
						<?php esc_html_e( 'WP-Cron', 'repro-ct-suite' ); ?>
					</td>
					<td>
						<?php if ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) : ?>
							<span style="color: #dc3232;">
								⚠ <?php esc_html_e( 'Deaktiviert (System-Cron erforderlich)', 'repro-ct-suite' ); ?>
							</span>
						<?php else : ?>
							<span style="color: #46b450;">
								✓ <?php esc_html_e( 'Aktiv', 'repro-ct-suite' ); ?>
							</span>
						<?php endif; ?>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
</div>
<?php endif; ?>

<!-- System-Info -->
<div class="repro-ct-suite-card" style="margin-top: 20px;">
	<div class="repro-ct-suite-card-header">
		<span class="dashicons dashicons-info"></span>
		<h3><?php esc_html_e( 'System', 'repro-ct-suite' ); ?></h3>
	</div>
	<div class="repro-ct-suite-card-body">
		<table class="widefat">
			<tbody>
				<tr>
					<td style="width: 30%; font-weight: 600;">
						<?php esc_html_e( 'Plugin-Version', 'repro-ct-suite' ); ?>
					</td>
					<td>
						<?php echo esc_html( REPRO_CT_SUITE_VERSION ); ?>
					</td>
				</tr>
				<tr>
					<td style="font-weight: 600;">
						<?php esc_html_e( 'WordPress-Version', 'repro-ct-suite' ); ?>
					</td>
					<td>
						<?php echo esc_html( get_bloginfo( 'version' ) ); ?>
					</td>
				</tr>
				<tr>
					<td style="font-weight: 600;">
						<?php esc_html_e( 'PHP-Version', 'repro-ct-suite' ); ?>
					</td>
					<td>
						<?php echo esc_html( phpversion() ); ?>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
	<div class="repro-ct-suite-card-footer">
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=repro-ct-suite&tab=update' ) ); ?>" class="button button-small">
			<span class="dashicons dashicons-update"></span>
			<?php esc_html_e( 'Update-Info', 'repro-ct-suite' ); ?>
		</a>
	</div>
</div>
