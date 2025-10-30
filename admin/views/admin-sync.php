<?php
/**
 * Termine-Sync Seite
 *
 * Manuelle Synchronisation von Terminen und Events aus ChurchTools.
 *
 * @package    Repro_CT_Suite
 * @subpackage Repro_CT_Suite/admin/views
 * @since      0.3.5.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

// Repositories laden
require_once REPRO_CT_SUITE_PATH . 'includes/repositories/class-repro-ct-suite-repository-base.php';
require_once REPRO_CT_SUITE_PATH . 'includes/repositories/class-repro-ct-suite-calendars-repository.php';

$calendars_repo = new Repro_CT_Suite_Calendars_Repository();
$selected_calendars = $calendars_repo->get_all( array( 'where' => array( 'is_selected' => 1 ), 'order_by' => 'sort_order', 'order' => 'ASC' ) );
$selected_count = count( $selected_calendars );

// Zeitraum-Einstellungen
$sync_days_past = get_option( 'repro_ct_suite_sync_days_past', 30 );
$sync_days_future = get_option( 'repro_ct_suite_sync_days_future', 90 );
$from_date = date_i18n( get_option( 'date_format' ), current_time( 'timestamp' ) - ( (int) $sync_days_past * DAY_IN_SECONDS ) );
$to_date   = date_i18n( get_option( 'date_format' ), current_time( 'timestamp' ) + ( (int) $sync_days_future * DAY_IN_SECONDS ) );

// Letzter Sync
$last_sync_time = get_option( 'repro_ct_suite_last_sync_time', null );
$last_sync_stats = get_option( 'repro_ct_suite_last_sync_stats', null );
?>

<div class="wrap repro-ct-suite-admin-wrapper">
	
	<!-- Header-Bereich -->
	<div class="repro-ct-suite-header">
		<h1>
			<span class="dashicons dashicons-update"></span>
			<?php esc_html_e( 'Termine-Sync', 'repro-ct-suite' ); ?>
		</h1>
		<p><?php esc_html_e( 'Synchronisieren Sie Termine und Events aus den ausgewählten ChurchTools-Kalendern.', 'repro-ct-suite' ); ?></p>
	</div>

	<!-- Übersicht: Ausgewählte Kalender -->
	<div class="repro-ct-suite-card">
		<div class="repro-ct-suite-card-header">
			<span class="dashicons dashicons-calendar-alt"></span>
			<h3><?php esc_html_e( 'Ausgewählte Kalender', 'repro-ct-suite' ); ?></h3>
		</div>
		<div class="repro-ct-suite-card-body">
			<?php if ( $selected_count === 0 ) : ?>
				<div class="repro-ct-suite-notice repro-ct-suite-notice-warning">
					<span class="dashicons dashicons-warning"></span>
					<div>
						<p><strong><?php esc_html_e( 'Keine Kalender ausgewählt', 'repro-ct-suite' ); ?></strong></p>
						<p><?php esc_html_e( 'Bitte wählen Sie zunächst mindestens einen Kalender im Tab "Kalender" aus.', 'repro-ct-suite' ); ?></p>
						<p><a href="<?php echo esc_url( admin_url( 'admin.php?page=repro-ct-suite-calendars' ) ); ?>" class="repro-ct-suite-btn repro-ct-suite-btn-secondary"><?php esc_html_e( 'Zur Kalenderauswahl', 'repro-ct-suite' ); ?></a></p>
					</div>
				</div>
			<?php else : ?>
				<p class="description">
					<?php
					printf(
						/* translators: %d: Number of selected calendars */
						esc_html__( 'Es werden Termine aus %d Kalender(n) synchronisiert:', 'repro-ct-suite' ),
						$selected_count
					);
					?>
				</p>
				<ul class="repro-ct-suite-mt-10" style="padding-left: 20px;">
					<?php foreach ( $selected_calendars as $calendar ) : ?>
						<li>
							<?php if ( ! empty( $calendar->color ) ) : ?>
								<span style="display: inline-block; width: 12px; height: 12px; background-color: <?php echo esc_attr( $calendar->color ); ?>; border: 1px solid #ddd; border-radius: 2px; margin-right: 5px;"></span>
							<?php endif; ?>
							<strong><?php echo esc_html( $calendar->name_translated ?: $calendar->name ); ?></strong>
							<?php if ( $calendar->is_public ) : ?>
								<span class="repro-ct-suite-badge repro-ct-suite-badge-success" style="font-size: 11px; padding: 2px 6px; margin-left: 5px;"><?php esc_html_e( 'Öffentlich', 'repro-ct-suite' ); ?></span>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
	</div>

	<!-- Zeitraum -->
	<div class="repro-ct-suite-card repro-ct-suite-mt-20">
		<div class="repro-ct-suite-card-header">
			<span class="dashicons dashicons-clock"></span>
			<h3><?php esc_html_e( 'Synchronisations-Zeitraum', 'repro-ct-suite' ); ?></h3>
		</div>
		<div class="repro-ct-suite-card-body">
			<p class="description">
				<?php esc_html_e( 'Aktuell konfigurierter Zeitraum für die Synchronisation:', 'repro-ct-suite' ); ?>
			</p>
			<p class="repro-ct-suite-mt-10">
				<strong><?php echo esc_html( $from_date ); ?></strong>
				<?php esc_html_e( 'bis', 'repro-ct-suite' ); ?>
				<strong><?php echo esc_html( $to_date ); ?></strong>
				<br>
				<span class="description">
					<?php
					printf(
						/* translators: 1: Days in past, 2: Days in future */
						esc_html__( '(%d Tage zurück, %d Tage voraus)', 'repro-ct-suite' ),
						(int) $sync_days_past,
						(int) $sync_days_future
					);
					?>
				</span>
			</p>
			<p class="repro-ct-suite-mt-10">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=repro-ct-suite-settings' ) ); ?>" class="repro-ct-suite-btn repro-ct-suite-btn-secondary">
					<span class="dashicons dashicons-admin-settings"></span>
					<?php esc_html_e( 'Zeitraum ändern', 'repro-ct-suite' ); ?>
				</a>
			</p>
		</div>
	</div>

	<!-- Synchronisation -->
	<div class="repro-ct-suite-card repro-ct-suite-mt-20">
		<div class="repro-ct-suite-card-header">
			<span class="dashicons dashicons-update"></span>
			<h3><?php esc_html_e( 'Synchronisation starten', 'repro-ct-suite' ); ?></h3>
		</div>
		<div class="repro-ct-suite-card-body">
			<?php if ( $selected_count === 0 ) : ?>
				<div class="repro-ct-suite-notice repro-ct-suite-notice-warning">
					<span class="dashicons dashicons-info"></span>
					<div>
						<p><?php esc_html_e( 'Bitte wählen Sie mindestens einen Kalender aus, um die Synchronisation zu starten.', 'repro-ct-suite' ); ?></p>
					</div>
				</div>
			<?php else : ?>
				<p><?php esc_html_e( 'Die Synchronisation läuft in zwei Schritten:', 'repro-ct-suite' ); ?></p>
				<ol style="padding-left: 25px; margin-top: 10px;">
					<li><?php esc_html_e( 'Events werden aus der ChurchTools Events-API abgerufen und importiert.', 'repro-ct-suite' ); ?></li>
					<li><?php esc_html_e( 'Appointments (Termininstanzen) werden aus der Kalender-API abgerufen. Termine, die bereits als Event vorhanden sind, werden übersprungen.', 'repro-ct-suite' ); ?></li>
				</ol>

				<?php if ( $last_sync_time ) : ?>
					<div class="repro-ct-suite-notice repro-ct-suite-notice-info repro-ct-suite-mt-15">
						<span class="dashicons dashicons-info"></span>
						<div>
							<p>
								<strong><?php esc_html_e( 'Letzter Sync:', 'repro-ct-suite' ); ?></strong>
								<?php echo esc_html( human_time_diff( strtotime( $last_sync_time ), current_time( 'timestamp' ) ) . ' ' . __( 'her', 'repro-ct-suite' ) ); ?>
								(<?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $last_sync_time ) ) ); ?>)
							</p>
							<?php if ( $last_sync_stats && is_array( $last_sync_stats ) ) : ?>
								<p>
									<strong><?php esc_html_e( 'Statistik:', 'repro-ct-suite' ); ?></strong>
									<?php
									$stats_parts = array();
									if ( isset( $last_sync_stats['events_created'] ) ) {
										$stats_parts[] = sprintf( __( '%d Events erstellt', 'repro-ct-suite' ), $last_sync_stats['events_created'] );
									}
									if ( isset( $last_sync_stats['events_updated'] ) ) {
										$stats_parts[] = sprintf( __( '%d Events aktualisiert', 'repro-ct-suite' ), $last_sync_stats['events_updated'] );
									}
									if ( isset( $last_sync_stats['appointments_created'] ) ) {
										$stats_parts[] = sprintf( __( '%d Appointments erstellt', 'repro-ct-suite' ), $last_sync_stats['appointments_created'] );
									}
									if ( isset( $last_sync_stats['skipped_has_event'] ) ) {
										$stats_parts[] = sprintf( __( '%d übersprungen (Event vorhanden)', 'repro-ct-suite' ), $last_sync_stats['skipped_has_event'] );
									}
									echo esc_html( implode( ', ', $stats_parts ) );
									?>
								</p>
							<?php endif; ?>
						</div>
					</div>
				<?php endif; ?>

				<p class="repro-ct-suite-mt-15">
					<button type="button" class="repro-ct-suite-btn repro-ct-suite-btn-primary repro-ct-suite-sync-appointments-btn">
						<span class="dashicons dashicons-update"></span>
						<?php esc_html_e( 'Jetzt synchronisieren', 'repro-ct-suite' ); ?>
					</button>
				</p>
			<?php endif; ?>
		</div>
	</div>

	<!-- Hinweise -->
	<div class="repro-ct-suite-card repro-ct-suite-mt-20">
		<div class="repro-ct-suite-card-header">
			<span class="dashicons dashicons-info"></span>
			<h3><?php esc_html_e( 'Hinweise', 'repro-ct-suite' ); ?></h3>
		</div>
		<div class="repro-ct-suite-card-body">
			<ul style="padding-left: 20px;">
				<li><?php esc_html_e( 'Die Synchronisation kann je nach Anzahl der Termine einige Zeit in Anspruch nehmen.', 'repro-ct-suite' ); ?></li>
				<li><?php esc_html_e( 'Während der Synchronisation werden bestehende Termine in der Datenbank aktualisiert.', 'repro-ct-suite' ); ?></li>
				<li><?php esc_html_e( 'Events und Appointments werden getrennt synchronisiert, um Duplikate zu vermeiden.', 'repro-ct-suite' ); ?></li>
				<li><?php esc_html_e( 'Bei Problemen prüfen Sie bitte die Debug-Ausgaben im Debug-Tab.', 'repro-ct-suite' ); ?></li>
			</ul>
		</div>
	</div>

</div>
