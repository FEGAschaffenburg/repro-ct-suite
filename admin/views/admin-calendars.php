<?php
/**
 * Kalender-Seite
 *
 * Synchronisation und Auswahl der ChurchTools-Kalender.
 *
 * @package    Repro_CT_Suite
 * @subpackage Repro_CT_Suite/admin/views
 * @since      0.3.5.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

// Repositories laden für Kalender-Verwaltung
require_once REPRO_CT_SUITE_PATH . 'includes/repositories/class-repro-ct-suite-repository-base.php';
require_once REPRO_CT_SUITE_PATH . 'includes/repositories/class-repro-ct-suite-calendars-repository.php';

$calendars_repo = new Repro_CT_Suite_Calendars_Repository();
$all_calendars = $calendars_repo->get_all( array( 'order_by' => 'sort_order', 'order' => 'ASC' ) );
$selected_count = $calendars_repo->count_selected();
$last_calendar_sync = get_option( 'repro_ct_suite_calendars_last_sync', null );

$tenant   = get_option( 'repro_ct_suite_ct_tenant', '' );
$username = get_option( 'repro_ct_suite_ct_username', '' );
$enc_pw   = get_option( 'repro_ct_suite_ct_password', '' );
?>

<div class="wrap repro-ct-suite-admin-wrapper">
	
	<!-- Header-Bereich -->
	<div class="repro-ct-suite-header">
		<h1>
			<span class="dashicons dashicons-calendar-alt"></span>
			<?php esc_html_e( 'Kalender', 'repro-ct-suite' ); ?>
		</h1>
		<p><?php esc_html_e( 'Synchronisieren Sie Kalender aus ChurchTools und wählen Sie, welche Kalender für die Termin-Synchronisation verwendet werden sollen.', 'repro-ct-suite' ); ?></p>
	</div>

	<!-- Kalender-Verwaltung -->
	<div class="repro-ct-suite-card">
		<div class="repro-ct-suite-card-header">
			<span class="dashicons dashicons-calendar-alt"></span>
			<h3><?php esc_html_e( 'Kalender-Auswahl', 'repro-ct-suite' ); ?></h3>
		</div>
		<div class="repro-ct-suite-card-body">
			<p><?php esc_html_e( 'Wählen Sie, welche Kalender synchronisiert werden sollen. Nur Termine und Events aus ausgewählten Kalendern werden importiert.', 'repro-ct-suite' ); ?></p>
			
			<?php if ( empty( $all_calendars ) ) : ?>
				<div class="repro-ct-suite-notice repro-ct-suite-notice-warning repro-ct-suite-mt-10">
					<span class="dashicons dashicons-info"></span>
					<div>
						<p><?php esc_html_e( 'Keine Kalender gefunden. Bitte synchronisieren Sie zuerst die Kalender aus ChurchTools.', 'repro-ct-suite' ); ?></p>
					</div>
				</div>
				<?php if ( ! empty( $tenant ) && ! empty( $username ) && ! empty( $enc_pw ) ) : ?>
					<p class="repro-ct-suite-mt-10">
						<button type="button" class="repro-ct-suite-btn repro-ct-suite-btn-primary repro-ct-suite-sync-calendars-btn" data-action="sync_calendars">
							<span class="dashicons dashicons-update"></span>
							<?php esc_html_e( 'Kalender jetzt synchronisieren', 'repro-ct-suite' ); ?>
						</button>
					</p>
				<?php endif; ?>
			<?php else : ?>
				<div class="repro-ct-suite-mt-10">
					<p class="description">
						<?php 
						printf(
							/* translators: 1: Number of selected calendars, 2: Total number of calendars */
							esc_html__( '%1$d von %2$d Kalendern ausgewählt', 'repro-ct-suite' ),
							$selected_count,
							count( $all_calendars )
						);
						?>
						<?php if ( $last_calendar_sync ) : ?>
							<br>
							<?php 
							printf(
								/* translators: %s: Last sync time */
								esc_html__( 'Letzter Sync: %s', 'repro-ct-suite' ),
								human_time_diff( strtotime( $last_calendar_sync ), current_time( 'timestamp' ) ) . ' ' . esc_html__( 'her', 'repro-ct-suite' )
							);
							?>
						<?php endif; ?>
					</p>
				</div>
				
				<form method="post" action="" id="repro-ct-suite-calendars-form" class="repro-ct-suite-mt-15">
					<?php wp_nonce_field( 'repro_ct_suite_update_calendars', 'repro_ct_suite_calendars_nonce' ); ?>
					<input type="hidden" name="action" value="repro_ct_suite_update_calendars">
					
					<table class="widefat" style="margin-top: 0;">
						<thead>
							<tr>
								<th style="width: 40px;">
									<input type="checkbox" id="select-all-calendars" />
								</th>
								<th><?php esc_html_e( 'Kalender', 'repro-ct-suite' ); ?></th>
								<th><?php esc_html_e( 'Status', 'repro-ct-suite' ); ?></th>
								<th style="width: 80px;"><?php esc_html_e( 'Farbe', 'repro-ct-suite' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $all_calendars as $calendar ) : ?>
								<tr>
									<td>
										<input 
											type="checkbox" 
											name="selected_calendars[]" 
											value="<?php echo esc_attr( $calendar->id ); ?>" 
											<?php checked( $calendar->is_selected, 1 ); ?>
											class="calendar-checkbox"
										/>
									</td>
									<td>
										<strong><?php echo esc_html( $calendar->name_translated ?: $calendar->name ); ?></strong>
										<?php if ( $calendar->name !== $calendar->name_translated && ! empty( $calendar->name_translated ) ) : ?>
											<br><span class="description"><?php echo esc_html( $calendar->name ); ?></span>
										<?php endif; ?>
									</td>
									<td>
										<?php if ( $calendar->is_public ) : ?>
											<span class="repro-ct-suite-badge repro-ct-suite-badge-success">
												<?php esc_html_e( 'Öffentlich', 'repro-ct-suite' ); ?>
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
											—
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
						<button type="button" class="repro-ct-suite-btn repro-ct-suite-btn-secondary repro-ct-suite-sync-calendars-btn" data-action="sync_calendars">
							<span class="dashicons dashicons-update"></span>
							<?php esc_html_e( 'Kalender neu laden', 'repro-ct-suite' ); ?>
						</button>
					</p>
				</form>
				
				<script>
				jQuery(document).ready(function($) {
					// Select All Checkbox
					$('#select-all-calendars').on('change', function() {
						$('.calendar-checkbox').prop('checked', $(this).prop('checked'));
					});
					
					// Update Select All state
					$('.calendar-checkbox').on('change', function() {
						var allChecked = $('.calendar-checkbox:checked').length === $('.calendar-checkbox').length;
						$('#select-all-calendars').prop('checked', allChecked);
					});
				});
				</script>
			<?php endif; ?>
		</div>
	</div>

	<!-- Debug-Anzeige -->
	<div class="repro-ct-suite-card repro-ct-suite-mt-20" id="repro-ct-suite-debug-panel" style="display: none;">
		<div class="repro-ct-suite-card-header" style="background: #f0f0f1;">
			<span class="dashicons dashicons-admin-generic"></span>
			<h3><?php esc_html_e( 'Debug-Informationen', 'repro-ct-suite' ); ?></h3>
		</div>
		<div class="repro-ct-suite-card-body">
			<div id="repro-ct-suite-debug-content" style="font-family: 'Courier New', monospace; font-size: 12px; max-height: 400px; overflow-y: auto; background: #fff; border: 1px solid #ddd; padding: 10px; border-radius: 3px;">
				<div style="color: #666;"><?php esc_html_e( 'Warte auf Debug-Ausgaben...', 'repro-ct-suite' ); ?></div>
			</div>
			<p class="repro-ct-suite-mt-10">
				<button type="button" class="repro-ct-suite-btn repro-ct-suite-btn-primary" onclick="reproCTSuiteCopyDebugLog();">
					<span class="dashicons dashicons-clipboard"></span>
					<?php esc_html_e( 'Debug-Log kopieren', 'repro-ct-suite' ); ?>
				</button>
				<button type="button" class="repro-ct-suite-btn repro-ct-suite-btn-secondary" onclick="jQuery('#repro-ct-suite-debug-content').html('<div style=\'color: #666;\'><?php esc_html_e( 'Debug-Log gelöscht', 'repro-ct-suite' ); ?></div>');">
					<span class="dashicons dashicons-trash"></span>
					<?php esc_html_e( 'Debug-Log löschen', 'repro-ct-suite' ); ?>
				</button>
				<button type="button" class="repro-ct-suite-btn repro-ct-suite-btn-secondary" onclick="jQuery('#repro-ct-suite-debug-panel').hide();">
					<span class="dashicons dashicons-no"></span>
					<?php esc_html_e( 'Debug-Panel schließen', 'repro-ct-suite' ); ?>
				</button>
			</p>
			
			<script>
			function reproCTSuiteCopyDebugLog() {
				var debugContent = jQuery('#repro-ct-suite-debug-content').text();
				
				if (navigator.clipboard && navigator.clipboard.writeText) {
					navigator.clipboard.writeText(debugContent).then(function() {
						alert('<?php esc_html_e( 'Debug-Log wurde in die Zwischenablage kopiert!', 'repro-ct-suite' ); ?>');
					}).catch(function(err) {
						console.error('Fehler beim Kopieren:', err);
						fallbackCopyDebugLog(debugContent);
					});
				} else {
					fallbackCopyDebugLog(debugContent);
				}
			}
			
			function fallbackCopyDebugLog(text) {
				var textarea = document.createElement('textarea');
				textarea.value = text;
				textarea.style.position = 'fixed';
				textarea.style.opacity = '0';
				document.body.appendChild(textarea);
				textarea.select();
				try {
					document.execCommand('copy');
					alert('<?php esc_html_e( 'Debug-Log wurde in die Zwischenablage kopiert!', 'repro-ct-suite' ); ?>');
				} catch (err) {
					console.error('Fehler beim Kopieren:', err);
					alert('<?php esc_html_e( 'Kopieren fehlgeschlagen. Bitte markieren Sie den Text manuell und kopieren Sie ihn.', 'repro-ct-suite' ); ?>');
				}
				document.body.removeChild(textarea);
			}
			</script>
			
			<p class="description">
				<?php esc_html_e( 'Diese Informationen zeigen detailliert, was beim Kalender-Sync passiert. Öffnen Sie außerdem die Browser-Konsole (F12) für weitere Details.', 'repro-ct-suite' ); ?>
			</p>
		</div>
	</div>

</div>
