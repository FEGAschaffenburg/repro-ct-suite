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
require_once REPRO_CT_SUITE_PATH . 'includes/repositories/class-repro-ct-suite-events-repository.php';
require_once REPRO_CT_SUITE_PATH . 'includes/repositories/class-repro-ct-suite-calendars-repository.php';

$events_repo = new Repro_CT_Suite_Events_Repository();
$calendars_repo = new Repro_CT_Suite_Calendars_Repository();

// Prüfe auf frische Migration V6 (Unified Sync)
$db_version = get_option( 'repro_ct_suite_db_version', '0' );
$show_v6_notice = ( version_compare( $db_version, '6', '>=' ) && ! get_option( 'repro_ct_suite_v6_notice_dismissed', false ) );

// Veranstaltungen-Statistik (nur Events, keine Appointments)
global $wpdb;
$events_table = $wpdb->prefix . 'rcts_events';
$events_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$events_table}" );

// Nächste Veranstaltungen (Events)
$upcoming_sql = $wpdb->prepare(
	"SELECT * FROM {$events_table} WHERE start_datetime >= %s ORDER BY start_datetime ASC LIMIT 5",
	current_time( 'mysql' )
);
$upcoming_events = $wpdb->get_results( $upcoming_sql );

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

<?php if ( $show_v6_notice ) : ?>
<!-- Unified Sync System Notice -->
<div class="notice notice-success is-dismissible" style="margin-bottom: 20px;">
	<h3>🎉 Neues Unified Sync System aktiviert!</h3>
	<p><strong>Verbesserungen in Version 0.4.0:</strong></p>
	<ul style="list-style: disc; margin-left: 20px;">
		<li><strong>Intelligenter 2-Phase Sync:</strong> Events + Appointments ohne Duplikate</li>
		<li><strong>50% weniger Code:</strong> Vereinfachte, wartbare Architektur</li>
		<li><strong>Bereinigte Admin-UI:</strong> Einheitliche Terminverwaltung</li>
		<li><strong>Bessere Performance:</strong> Optimierte API-Nutzung</li>
	</ul>
	<p>
		<strong>Was ist neu:</strong> Das Plugin nutzt jetzt ein einheitliches System für alle Termine. 
		Alte separate "Events" und "Appointments" Listen wurden zu einer "Termine" Übersicht zusammengeführt.
	</p>
	<p>
		<button type="button" class="button button-secondary" onclick="dismissV6Notice()">
			✓ Verstanden, Notice ausblenden
		</button>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=repro-ct-suite&tab=sync' ) ); ?>" class="button button-primary">
			🚀 Jetzt synchronisieren
		</a>
	</p>
</div>

<script>
function dismissV6Notice() {
	fetch('<?php echo admin_url( 'admin-ajax.php' ); ?>', {
		method: 'POST',
		headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
		body: 'action=repro_ct_suite_dismiss_v6_notice&nonce=<?php echo wp_create_nonce( 'repro_ct_suite_admin' ); ?>'
	}).then(() => {
		document.querySelector('.notice').style.display = 'none';
	});
}
</script>
<?php endif; ?>

<!-- Statistik-Grid -->
<div class="repro-ct-suite-grid repro-ct-suite-grid-2">
	
	<!-- Veranstaltungen Card -->
	<div class="repro-ct-suite-card">
		<div class="repro-ct-suite-card-header">
			<span class="dashicons dashicons-calendar-alt"></span>
			<h3><?php esc_html_e( 'Termine gesamt', 'repro-ct-suite' ); ?></h3>
		</div>
		<div class="repro-ct-suite-card-body">
			<div style="font-size: 32px; font-weight: 600; color: #0073aa; margin-bottom: 10px;">
				<?php echo esc_html( $events_count ); ?>
			</div>
			<p class="description"><?php esc_html_e( 'Events (aus Events-API) und Termine (aus Appointments ohne Event)', 'repro-ct-suite' ); ?></p>
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
	</div>
	<div class="repro-ct-suite-card-body">
		<?php if ( ! empty( $upcoming_events ) ) : ?>
			<table class="widefat" style="margin-top: 0;">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Datum & Zeit', 'repro-ct-suite' ); ?></th>
						<th><?php esc_html_e( 'Titel', 'repro-ct-suite' ); ?></th>
						<th><?php esc_html_e( 'Ort', 'repro-ct-suite' ); ?></th>
						<th><?php esc_html_e( 'Art', 'repro-ct-suite' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $upcoming_events as $event ) : ?>
						<?php
						$start = strtotime( $event->start_datetime );
						$date_format = get_option( 'date_format' );
						$time_format = get_option( 'time_format' );
						$formatted_date = date_i18n( $date_format, $start );
						$formatted_time = date_i18n( $time_format, $start );
						// Art: Event (aus rcts_events) oder Termin (aus rcts_appointments)
						$type = 'Event'; // aus events-Tabelle, daher immer Event
						$type_class = 'repro-ct-suite-badge-info';
						?>
						<tr>
							<td>
								<strong><?php echo esc_html( $formatted_date ); ?></strong><br>
								<span class="description"><?php echo esc_html( $formatted_time ); ?></span>
			</td>
							<td>
								<strong><?php echo esc_html( $event->title ); ?></strong>
								<?php if ( ! empty( $event->description ) ) : ?>
									<br><span class="description"><?php echo esc_html( wp_trim_words( $event->description, 10 ) ); ?></span>
								<?php endif; ?>
							</td>
							<td>
								<?php echo ! empty( $event->location_name ) ? esc_html( $event->location_name ) : '—'; ?>
							</td>
							<td>
								<span class="repro-ct-suite-badge <?php echo esc_attr( $type_class ); ?>">
									<?php echo esc_html( $type ); ?>
								</span>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<div style="margin-top: 15px; text-align: right;">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=repro-ct-suite-events' ) ); ?>" class="repro-ct-suite-btn repro-ct-suite-btn-secondary repro-ct-suite-btn-small">
					<?php esc_html_e( 'Alle Termine ansehen', 'repro-ct-suite' ); ?>
				</a>
			</div>
		<?php else : ?>
			<p class="description">
				<?php esc_html_e( 'Keine bevorstehenden Termine gefunden.', 'repro-ct-suite' ); ?>
			</p>
			<?php if ( $connection_status === 'configured' ) : ?>
				<p>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=repro-ct-suite&tab=sync' ) ); ?>" class="repro-ct-suite-btn repro-ct-suite-btn-primary">
						<span class="dashicons dashicons-update"></span>
						<?php esc_html_e( 'Termine synchronisieren', 'repro-ct-suite' ); ?>
					</a>
				</p>
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
