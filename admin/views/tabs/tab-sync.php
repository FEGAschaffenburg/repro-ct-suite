<?php
/**
 * Tab: Synchronisation
 *
 * Vereinfachte Synchronisation mit dem neuen einheitlichen Sync-Service:
 * - Kalenderauswahl
 * - Zeitraum-Konfiguration  
 * - Einheitlicher Sync-Button fÃ¼r alle Termine
 *
 * @package    Repro_CT_Suite
 * @subpackage Repro_CT_Suite/admin/views/tabs
 * @since      0.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Credentials prÃ¼fen
$ct_tenant   = get_option( 'repro_ct_suite_ct_tenant', '' );
$ct_username = get_option( 'repro_ct_suite_ct_username', '' );
$ct_password = get_option( 'repro_ct_suite_ct_password', '' );
$is_configured = ! empty( $ct_tenant ) && ! empty( $ct_username ) && ! empty( $ct_password );

// Zeitraum-Einstellungen
$sync_from_days = get_option( 'repro_ct_suite_sync_from_days', -7 );
$sync_to_days   = get_option( 'repro_ct_suite_sync_to_days', 90 );

// Kalender laden
$calendars = array();
$selected_count = 0;
$calendars_repo = null;

try {
	$plugin_path = defined( 'REPRO_CT_SUITE_PATH' ) ? REPRO_CT_SUITE_PATH : plugin_dir_path( dirname( dirname( dirname( __FILE__ ) ) ) );
	
	if ( ! class_exists( 'Repro_CT_Suite_Repository_Base' ) ) {
		require_once $plugin_path . 'includes/repositories/class-repro-ct-suite-repository-base.php';
	}
	
	if ( ! class_exists( 'Repro_CT_Suite_Calendars_Repository' ) ) {
		require_once $plugin_path . 'includes/repositories/class-repro-ct-suite-calendars-repository.php';
	}
	
	$calendars_repo = new Repro_CT_Suite_Calendars_Repository();
	$calendars = $calendars_repo->get_all();
	$selected_count = $calendars_repo->count_selected();
} catch ( Exception $e ) {
	error_log( 'Repro CT-Suite: Fehler beim Laden der Kalender in tab-sync.php: ' . $e->getMessage() );
}
?>

<div class="repro-ct-suite-sync-wrapper">

	<?php if ( ! $is_configured ) : ?>
		<!-- Konfiguration fehlt -->
		<div class="notice notice-warning">
			<p>
				<strong><?php esc_html_e( 'ChurchTools-Verbindung nicht konfiguriert', 'repro-ct-suite' ); ?></strong><br>
				<?php esc_html_e( 'Bitte konfigurieren Sie zuerst Ihre ChurchTools-Zugangsdaten im Tab "Einstellungen".', 'repro-ct-suite' ); ?>
			</p>
			<p>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=repro-ct-suite&tab=settings' ) ); ?>" class="button button-primary">
					<?php esc_html_e( 'Zu den Einstellungen', 'repro-ct-suite' ); ?>
				</a>
			</p>
		</div>
	<?php else : ?>

		<!-- Kalender synchronisieren -->
		<div class="repro-ct-suite-card">
			<div class="repro-ct-suite-card-header">
				<span class="dashicons dashicons-calendar-alt"></span>
				<h3><?php esc_html_e( 'Kalender aus ChurchTools laden', 'repro-ct-suite' ); ?></h3>
			</div>
			<div class="repro-ct-suite-card-body">
				<p class="description">
					<?php esc_html_e( 'LÃ¤dt die Kalenderliste aus ChurchTools und aktualisiert die verfÃ¼gbaren Kalender in der Datenbank.', 'repro-ct-suite' ); ?>
				</p>
				<p>
					<button type="button" class="repro-ct-suite-btn repro-ct-suite-btn-secondary repro-ct-suite-sync-calendars-btn">
						<span class="dashicons dashicons-update"></span>
						<?php esc_html_e( 'Kalender neu laden', 'repro-ct-suite' ); ?>
					</button>
				</p>
			</div>
		</div>

		<!-- Kalenderauswahl -->
		<div class="repro-ct-suite-card repro-ct-suite-mt-20">
			<div class="repro-ct-suite-card-header">
				<span class="dashicons dashicons-yes-alt"></span>
				<h3><?php esc_html_e( 'Kalenderauswahl', 'repro-ct-suite' ); ?></h3>
			</div>
			<div class="repro-ct-suite-card-body">
				<?php if ( empty( $calendars ) ) : ?>
					<p class="description">
						<?php esc_html_e( 'Keine Kalender vorhanden. Bitte laden Sie zuerst die Kalender von ChurchTools.', 'repro-ct-suite' ); ?>
					</p>
				<?php else : ?>
					<p class="description">
						<?php
						printf(
							esc_html__( 'WÃ¤hlen Sie die Kalender aus, deren Termine synchronisiert werden sollen. Aktuell ausgewÃ¤hlt: %d von %d', 'repro-ct-suite' ),
							(int) $selected_count,
							count( $calendars )
						);
						?>
					</p>
					
					<form method="post" action="">
						<?php wp_nonce_field( 'repro_ct_suite_calendar_selection', 'repro_ct_suite_calendar_selection_nonce' ); ?>
						<input type="hidden" name="repro_ct_suite_action" value="save_calendar_selection">
						
						<table class="widefat repro-ct-suite-mt-15">
							<thead>
								<tr>
									<th style="width: 40px;">
										<input type="checkbox" id="select-all-calendars-sync">
									</th>
									<th><?php esc_html_e( 'Kalender', 'repro-ct-suite' ); ?></th>
									<th><?php esc_html_e( 'ChurchTools-ID', 'repro-ct-suite' ); ?></th>
									<th><?php esc_html_e( 'Sichtbarkeit', 'repro-ct-suite' ); ?></th>
									<th><?php esc_html_e( 'Farbe', 'repro-ct-suite' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ( $calendars as $calendar ) : ?>
									<tr>
										<td>
											<input 
												type="checkbox" 
												name="selected_calendars[]" 
												value="<?php echo esc_attr( $calendar->id ); ?>"
												class="calendar-checkbox"
												<?php checked( $calendar->is_selected, 1 ); ?>
											>
										</td>
										<td>
											<strong><?php echo esc_html( $calendar->name_translated ?: $calendar->name ); ?></strong>
											<?php if ( $calendar->name !== $calendar->name_translated && ! empty( $calendar->name_translated ) ) : ?>
												<br><span class="description"><?php echo esc_html( $calendar->name ); ?></span>
											<?php endif; ?>
										</td>
										<td>
											<code><?php echo esc_html( $calendar->calendar_id ); ?></code>
										</td>
										<td>
											<?php if ( $calendar->is_public ) : ?>
												<span class="repro-ct-suite-badge repro-ct-suite-badge-success">
													<?php esc_html_e( 'Ã–ffentlich', 'repro-ct-suite' ); ?>
												</span>
											<?php else : ?>
												<span class="repro-ct-suite-badge repro-ct-suite-badge-secondary">
													<?php esc_html_e( 'Privat', 'repro-ct-suite' ); ?>
												</span>
											<?php endif; ?>
										</td>
										<td>
											<?php if ( ! empty( $calendar->color ) ) : ?>
												<div style="display: inline-block; width: 30px; height: 20px; background-color: <?php echo esc_attr( $calendar->color ); ?>; border: 1px solid #ddd; border-radius: 3px;"></div>
											<?php else : ?>
												â€”
											<?php endif; ?>
										</td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
						
						<p class="repro-ct-suite-mt-15">
							<button type="submit" class="repro-ct-suite-btn repro-ct-suite-btn-primary">
								<span class="dashicons dashicons-yes"></span>
								<?php esc_html_e( 'Auswahl speichern', 'repro-ct-suite' ); ?>
							</button>
						</p>
					</form>

					<script>
					jQuery(document).ready(function($) {
						$('#select-all-calendars-sync').on('change', function() {
							$('.calendar-checkbox').prop('checked', $(this).prop('checked'));
						});
						$('.calendar-checkbox').on('change', function() {
							var totalCheckboxes = $('.calendar-checkbox').length;
							var checkedCheckboxes = $('.calendar-checkbox:checked').length;
							$('#select-all-calendars-sync').prop('checked', totalCheckboxes === checkedCheckboxes);
						});
					});
					</script>
				<?php endif; ?>
			</div>
		</div>

		<!-- Zeitraum-Konfiguration -->
		<div class="repro-ct-suite-card repro-ct-suite-mt-20">
			<div class="repro-ct-suite-card-header">
				<span class="dashicons dashicons-clock"></span>
				<h3><?php esc_html_e( 'Zeitraum-Konfiguration', 'repro-ct-suite' ); ?></h3>
			</div>
			<div class="repro-ct-suite-card-body">
				<p class="description">
					<?php esc_html_e( 'Legen Sie fest, welcher Zeitraum bei der Synchronisation berÃ¼cksichtigt werden soll.', 'repro-ct-suite' ); ?>
				</p>
				
				<form method="post" action="">
					<?php wp_nonce_field( 'repro_ct_suite_sync_period', 'repro_ct_suite_sync_period_nonce' ); ?>
					<input type="hidden" name="repro_ct_suite_action" value="save_sync_period">
					
					<table class="form-table repro-ct-suite-mt-15">
						<tbody>
							<tr>
								<th scope="row">
									<label for="sync_from_days">
										<?php esc_html_e( 'Vergangenheit', 'repro-ct-suite' ); ?>
									</label>
								</th>
								<td>
									<input 
										type="number" 
										id="sync_from_days" 
										name="sync_from_days" 
										value="<?php echo esc_attr( $sync_from_days ); ?>" 
										class="small-text"
										min="-365"
										max="0"
									>
									<p class="description">
										<?php esc_html_e( 'Tage in der Vergangenheit (negative Zahl, z.B. -7 fÃ¼r eine Woche zurÃ¼ck)', 'repro-ct-suite' ); ?>
									</p>
								</td>
							</tr>
							<tr>
								<th scope="row">
									<label for="sync_to_days">
										<?php esc_html_e( 'Zukunft', 'repro-ct-suite' ); ?>
									</label>
								</th>
								<td>
									<input 
										type="number" 
										id="sync_to_days" 
										name="sync_to_days" 
										value="<?php echo esc_attr( $sync_to_days ); ?>" 
										class="small-text"
										min="0"
										max="730"
									>
									<p class="description">
										<?php esc_html_e( 'Tage in der Zukunft (z.B. 90 fÃ¼r drei Monate voraus)', 'repro-ct-suite' ); ?>
									</p>
								</td>
							</tr>
							<tr>
								<th scope="row">
									<?php esc_html_e( 'Aktueller Zeitraum', 'repro-ct-suite' ); ?>
								</th>
								<td>
									<?php
									$from_date = date_i18n( get_option( 'date_format' ), current_time( 'timestamp' ) + ( (int) $sync_from_days * DAY_IN_SECONDS ) );
									$to_date   = date_i18n( get_option( 'date_format' ), current_time( 'timestamp' ) + ( (int) $sync_to_days * DAY_IN_SECONDS ) );
									?>
									<strong><?php echo esc_html( $from_date ); ?></strong>
									<?php esc_html_e( 'bis', 'repro-ct-suite' ); ?>
									<strong><?php echo esc_html( $to_date ); ?></strong>
								</td>
							</tr>
						</tbody>
					</table>
					
					<p class="repro-ct-suite-mt-15">
						<button type="submit" class="repro-ct-suite-btn repro-ct-suite-btn-primary">
							<span class="dashicons dashicons-yes"></span>
							<?php esc_html_e( 'Zeitraum speichern', 'repro-ct-suite' ); ?>
						</button>
					</p>
				</form>
			</div>
		</div>

		<!-- Termine & Events synchronisieren -->
		<div class="repro-ct-suite-card repro-ct-suite-mt-20">
			<div class="repro-ct-suite-card-header">
				<span class="dashicons dashicons-update"></span>
				<h3><?php esc_html_e( 'Termine & Events synchronisieren', 'repro-ct-suite' ); ?></h3>
			</div>
			<div class="repro-ct-suite-card-body">
				<?php if ( $selected_count === 0 ) : ?>
					<div class="notice notice-warning inline">
						<p><?php esc_html_e( 'Keine Kalender ausgewÃ¤hlt. Bitte wÃ¤hlen Sie mindestens einen Kalender aus.', 'repro-ct-suite' ); ?></p>
					</div>
				<?php else : ?>
					<p class="description">
						<?php
						printf(
							esc_html__( 'Synchronisiert Termine und Events fÃ¼r %d ausgewÃ¤hlte Kalender im konfigurierten Zeitraum.', 'repro-ct-suite' ),
							(int) $selected_count
						);
						?>
					</p>
					<p class="description">
						<?php esc_html_e( 'Der neue einheitliche Sync-Service importiert alle Termine aus den ausgewÃ¤hlten Kalendern in einem Schritt.', 'repro-ct-suite' ); ?>
					</p>
					<p class="repro-ct-suite-mt-15">
						<button type="button" class="repro-ct-suite-btn repro-ct-suite-btn-primary repro-ct-suite-sync-appointments-btn">
							<span class="dashicons dashicons-update"></span>
							<?php esc_html_e( 'Alle Termine synchronisieren', 'repro-ct-suite' ); ?>
						</button>
					</p>
				<?php endif; ?>
			</div>
		</div>

	<?php endif; ?>

</div>
