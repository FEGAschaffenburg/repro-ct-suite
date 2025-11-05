<?php
/**
 * Einstellungen Tab Template
 *
 * Formular für ChurchTools-API-Konfiguration.
 *
 * @package    Repro_CT_Suite
 * @subpackage Repro_CT_Suite/admin/views/tabs
 * @since      0.2.0
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

// Test-Ergebnis aus Transient abrufen (wird von handle_test_connection() gesetzt)
$test_result = get_transient( 'repro_ct_suite_test_result' );
if ( $test_result !== false ) {
	delete_transient( 'repro_ct_suite_test_result' );
}
?>

<?php if ( $test_result !== null ) : ?>
	<?php if ( is_wp_error( $test_result ) ) : ?>
		<div class="repro-ct-suite-notice repro-ct-suite-notice-error repro-ct-suite-mt-10">
			<span class="dashicons dashicons-warning"></span>
			<div>
				<strong><?php esc_html_e( 'Verbindungstest fehlgeschlagen', 'repro-ct-suite' ); ?></strong>
				<p><?php echo esc_html( $test_result->get_error_message() ); ?></p>
			</div>
		</div>
	<?php else : ?>
		<div class="repro-ct-suite-notice repro-ct-suite-notice-success repro-ct-suite-mt-10">
			<span class="dashicons dashicons-yes-alt"></span>
			<div>
				<strong><?php esc_html_e( 'Verbindung erfolgreich!', 'repro-ct-suite' ); ?></strong>
				<p><?php esc_html_e( 'Die Anmeldung bei ChurchTools war erfolgreich.', 'repro-ct-suite' ); ?></p>
			</div>
		</div>
	<?php endif; ?>
<?php endif; ?>

<div class="repro-ct-suite-card">
	<div class="repro-ct-suite-card-header">
		<span class="dashicons dashicons-admin-settings"></span>
		<h3><?php esc_html_e( 'ChurchTools-Verbindung', 'repro-ct-suite' ); ?></h3>
	</div>
	<div class="repro-ct-suite-card-body">
		<form method="post" action="options.php">
			<?php settings_fields( 'repro_ct_suite' ); ?>
			
			<!-- ChurchTools Zugangsdaten -->
			<h4><?php esc_html_e( 'Zugangsdaten', 'repro-ct-suite' ); ?></h4>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="repro_ct_suite_ct_tenant"><?php esc_html_e( 'ChurchTools Tenant', 'repro-ct-suite' ); ?></label></th>
					<td>
						<input type="text" id="repro_ct_suite_ct_tenant" name="repro_ct_suite_ct_tenant" value="<?php echo esc_attr( $tenant ); ?>" class="regular-text" placeholder="gemeinde" />
						<p class="description"><?php esc_html_e( 'Der Tenant-Name aus Ihrer ChurchTools-URL (z.B. "gemeinde" für gemeinde.church.tools)', 'repro-ct-suite' ); ?></p>
						<?php if ( ! empty( $tenant ) ) : ?>
							<p class="description"><strong><?php esc_html_e( 'Ihre URL:', 'repro-ct-suite' ); ?></strong> <code>https://<?php echo esc_html( $tenant ); ?>.church.tools</code></p>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="repro_ct_suite_ct_username"><?php esc_html_e( 'Benutzername', 'repro-ct-suite' ); ?></label></th>
					<td>
						<input type="text" id="repro_ct_suite_ct_username" name="repro_ct_suite_ct_username" value="<?php echo esc_attr( $username ); ?>" class="regular-text" autocomplete="username" />
						<p class="description"><?php esc_html_e( 'Ihr ChurchTools-Benutzername (E-Mail oder Login-Name)', 'repro-ct-suite' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="repro_ct_suite_ct_password"><?php esc_html_e( 'Passwort', 'repro-ct-suite' ); ?></label></th>
					<td>
						<input type="password" id="repro_ct_suite_ct_password" name="repro_ct_suite_ct_password" value="" class="regular-text" autocomplete="new-password" placeholder="<?php echo ! empty( $enc_pw ) ? esc_attr__( '(gespeichert)', 'repro-ct-suite' ) : ''; ?>" />
						<p class="description"><?php esc_html_e( 'Wird verschlüsselt gespeichert. Leer lassen, um das bestehende Passwort beizubehalten.', 'repro-ct-suite' ); ?></p>
						<?php if ( ! empty( $enc_pw ) ) : ?>
							<p class="description" style="color:#46b450;">✓ <?php esc_html_e( 'Ein Passwort ist gespeichert.', 'repro-ct-suite' ); ?></p>
						<?php endif; ?>
					</td>
				</tr>
			</table>

			<!-- Sync-Zeitraum -->
			<h4 style="margin-top: 30px;"><?php esc_html_e( 'Sync-Zeitraum', 'repro-ct-suite' ); ?></h4>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row">
						<label for="repro_ct_suite_sync_from_days"><?php esc_html_e( 'Termine von (Tage)', 'repro-ct-suite' ); ?></label>
					</th>
					<td>
						<input type="number" id="repro_ct_suite_sync_from_days" name="repro_ct_suite_sync_from_days" value="<?php echo esc_attr( get_option( 'repro_ct_suite_sync_from_days', -7 ) ); ?>" class="small-text" />
						<p class="description">
							<?php esc_html_e( 'Negative Zahl = Tage in der Vergangenheit (z.B. -7 = 7 Tage zurück). Standard: -7', 'repro-ct-suite' ); ?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="repro_ct_suite_sync_to_days"><?php esc_html_e( 'Termine bis (Tage)', 'repro-ct-suite' ); ?></label>
					</th>
					<td>
						<input type="number" id="repro_ct_suite_sync_to_days" name="repro_ct_suite_sync_to_days" value="<?php echo esc_attr( get_option( 'repro_ct_suite_sync_to_days', 365 ) ); ?>" class="small-text" />
						<p class="description">
							<?php esc_html_e( 'Positive Zahl = Tage in der Zukunft (z.B. 365 = 1 Jahr voraus). Standard: 365', 'repro-ct-suite' ); ?>
						</p>
					</td>
				</tr>
			</table>

			<!-- Erweiterte Einstellungen -->
			<h4 style="margin-top: 30px;"><?php esc_html_e( 'Erweitert', 'repro-ct-suite' ); ?></h4>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Logging: Syslog aktivieren', 'repro-ct-suite' ); ?></th>
					<td>
						<input type="checkbox" id="repro_ct_suite_syslog" name="repro_ct_suite_syslog" value="1" <?php checked( get_option( 'repro_ct_suite_syslog', 0 ), 1 ); ?> />
						<p class="description"><?php esc_html_e( 'Bei Aktivierung werden Log-Einträge zusätzlich an das System-Log (syslog) gesendet. Dies setzt entsprechende Serverrechte/Logging voraus.', 'repro-ct-suite' ); ?></p>
					</td>
				</tr>
			</table>

			<!-- Speichern-Button -->
			<p style="margin-top: 20px;">
				<button type="submit" class="repro-ct-suite-btn repro-ct-suite-btn-primary">
					<span class="dashicons dashicons-yes"></span>
					<?php esc_html_e( 'Einstellungen speichern', 'repro-ct-suite' ); ?>
				</button>
				<?php if ( ! empty( $tenant ) || ! empty( $username ) || ! empty( $enc_pw ) ) : ?>
					<button type="button" class="repro-ct-suite-btn repro-ct-suite-btn-danger" id="reset-login-credentials" style="margin-left: 10px;">
						<span class="dashicons dashicons-trash"></span>
						<?php esc_html_e( 'Zugangsdaten löschen', 'repro-ct-suite' ); ?>
					</button>
				<?php endif; ?>
			</p>
		</form>

		<script>
		jQuery(document).ready(function($) {
			$('#reset-login-credentials').on('click', function() {
				if (!confirm('<?php esc_html_e( 'Möchten Sie wirklich alle Zugangsdaten (Tenant, Benutzername, Passwort) löschen?\n\nDiese Aktion kann nicht rückgängig gemacht werden.', 'repro-ct-suite' ); ?>')) {
					return;
				}
				
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'repro_ct_suite_reset_credentials',
						nonce: reproCTSuite.nonce
					},
					success: function(response) {
						if (response.success) {
							// Erste Stufe erfolgreich - Frage nach vollständigem Reset
							if (response.data.ask_full_reset) {
								if (confirm('<?php esc_html_e( 'Zugangsdaten wurden gelöscht.\n\nMöchten Sie auch ALLE anderen Daten löschen?\n\n⚠️ WARNUNG: Dies löscht:\n• Alle Kalender-Einstellungen\n• Alle synchronisierten Events\n• Alle Termine (Appointments)\n• Alle Service-Zuordnungen\n• Alle Synchronisations-Zeitstempel\n\nDiese Aktion kann NICHT rückgängig gemacht werden!', 'repro-ct-suite' ); ?>')) {
									// Vollständiger Reset
									$.ajax({
										url: ajaxurl,
										type: 'POST',
										data: {
											action: 'repro_ct_suite_full_reset',
											nonce: reproCTSuite.nonce
										},
										success: function(response) {
											if (response.success) {
												alert('<?php esc_html_e( 'Vollständiger Reset durchgeführt. Alle Daten wurden gelöscht.', 'repro-ct-suite' ); ?>');
												location.reload();
											} else {
												alert('<?php esc_html_e( 'Fehler beim vollständigen Reset: ', 'repro-ct-suite' ); ?>' + (response.data.message || '<?php esc_html_e( 'Unbekannter Fehler', 'repro-ct-suite' ); ?>'));
											}
										},
										error: function() {
											alert('<?php esc_html_e( 'Fehler bei der AJAX-Anfrage.', 'repro-ct-suite' ); ?>');
										}
									});
								} else {
									// Nur Zugangsdaten gelöscht, kein vollständiger Reset
									alert('<?php esc_html_e( 'Nur Zugangsdaten wurden gelöscht. Alle anderen Daten bleiben erhalten.', 'repro-ct-suite' ); ?>');
									location.reload();
								}
							} else {
								location.reload();
							}
						} else {
							alert('<?php esc_html_e( 'Fehler beim Löschen der Zugangsdaten: ', 'repro-ct-suite' ); ?>' + (response.data.message || '<?php esc_html_e( 'Unbekannter Fehler', 'repro-ct-suite' ); ?>'));
						}
					},
					error: function() {
						alert('<?php esc_html_e( 'Fehler bei der AJAX-Anfrage.', 'repro-ct-suite' ); ?>');
					}
				});
			});
		});
		</script>
	</div>
</div>

<div class="repro-ct-suite-card repro-ct-suite-mt-20">
	<div class="repro-ct-suite-card-header">
		<span class="dashicons dashicons-admin-tools"></span>
		<h3><?php esc_html_e( 'Verbindung testen', 'repro-ct-suite' ); ?></h3>
	</div>
	<div class="repro-ct-suite-card-body">
		<p><?php esc_html_e( 'Testen Sie die Verbindung zu ChurchTools mit den gespeicherten Zugangsdaten.', 'repro-ct-suite' ); ?></p>
		<?php if ( empty( $tenant ) || empty( $username ) || empty( $enc_pw ) ) : ?>
			<div class="repro-ct-suite-notice repro-ct-suite-notice-warning repro-ct-suite-mt-10">
				<span class="dashicons dashicons-info"></span>
				<div>
					<p><?php esc_html_e( 'Bitte speichern Sie zuerst Ihre Zugangsdaten (Tenant, Benutzername und Passwort).', 'repro-ct-suite' ); ?></p>
				</div>
			</div>
		<?php else : ?>
			<p class="repro-ct-suite-mt-10">
				<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=repro-ct-suite&test_connection=1' ), 'repro_ct_suite_test_connection' ) ); ?>" class="repro-ct-suite-btn repro-ct-suite-btn-secondary">
					<span class="dashicons dashicons-update"></span>
					<?php esc_html_e( 'Verbindung jetzt testen', 'repro-ct-suite' ); ?>
				</a>
			</p>
		<?php endif; ?>
	</div>
</div>

<!-- Kalender-Verwaltung -->
<div class="repro-ct-suite-card repro-ct-suite-mt-20">
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

<div class="repro-ct-suite-card repro-ct-suite-mt-20">
	<div class="repro-ct-suite-card-header">
		<span class="dashicons dashicons-info"></span>
		<h3><?php esc_html_e( 'Hinweise zur Sicherheit', 'repro-ct-suite' ); ?></h3>
	</div>
	<div class="repro-ct-suite-card-body">
		<ul style="padding-left: 20px;">
			<li><?php esc_html_e( 'Ihr Passwort wird mit AES-256-CBC verschlüsselt in der Datenbank gespeichert.', 'repro-ct-suite' ); ?></li>
			<li><?php esc_html_e( 'Die Session-Cookies werden in der Datenbank gespeichert und für API-Zugriffe wiederverwendet.', 'repro-ct-suite' ); ?></li>
			<li><?php esc_html_e( 'Bei abgelaufener Session erfolgt automatisch ein erneuter Login.', 'repro-ct-suite' ); ?></li>
			<li><?php esc_html_e( 'Verwenden Sie einen dedizierten ChurchTools-Benutzer mit minimalen Rechten für die Synchronisation.', 'repro-ct-suite' ); ?></li>
		</ul>
	</div>
</div>

<?php
// Cron-Einstellungen
if ( isset( $_POST['repro_ct_suite_save_cron_settings'] ) ) {
	check_admin_referer( 'repro_ct_suite_cron_settings' );
	
	$auto_sync_enabled = isset( $_POST['repro_ct_suite_auto_sync_enabled'] ) ? 1 : 0;
	$sync_interval = absint( $_POST['repro_ct_suite_sync_interval'] ?? 60 );
	$sync_interval_unit = sanitize_text_field( $_POST['repro_ct_suite_sync_interval_unit'] ?? 'minutes' );
	
	if ( $sync_interval_unit === 'minutes' && $sync_interval < 30 ) {
		$sync_interval = 30;
	}
	
	update_option( 'repro_ct_suite_auto_sync_enabled', $auto_sync_enabled );
	update_option( 'repro_ct_suite_sync_interval', $sync_interval );
	update_option( 'repro_ct_suite_sync_interval_unit', $sync_interval_unit );
	
	require_once plugin_dir_path( dirname( dirname( dirname( __FILE__ ) ) ) ) . 'includes/class-repro-ct-suite-cron.php';
	Repro_CT_Suite_Cron::reschedule_sync_job();
	
	echo '<div class="notice notice-success is-dismissible"><p>';
	esc_html_e( 'Cron-Einstellungen gespeichert.', 'repro-ct-suite' );
	echo '</p></div>';
}

$auto_sync_enabled = get_option( 'repro_ct_suite_auto_sync_enabled', 0 );
$sync_interval = get_option( 'repro_ct_suite_sync_interval', 60 );
$sync_interval_unit = get_option( 'repro_ct_suite_sync_interval_unit', 'minutes' );
$next_scheduled = wp_next_scheduled( 'repro_ct_suite_auto_sync' );
$last_sync = get_option( 'repro_ct_suite_last_auto_sync', 0 );
?>

<div class="repro-ct-suite-card repro-ct-suite-mt-20" id="cron">
	<div class="repro-ct-suite-card-header">
		<span class="dashicons dashicons-clock"></span>
		<h3><?php esc_html_e( 'Automatischer Sync', 'repro-ct-suite' ); ?></h3>
	</div>
	<div class="repro-ct-suite-card-body">
		<form method="post" action="">
			<?php wp_nonce_field( 'repro_ct_suite_cron_settings' ); ?>
			
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row">
							<?php esc_html_e( 'Automatischer Sync', 'repro-ct-suite' ); ?>
						</th>
						<td>
							<label>
								<input type="checkbox" name="repro_ct_suite_auto_sync_enabled" value="1" <?php checked( $auto_sync_enabled, 1 ); ?> />
								<?php esc_html_e( 'Aktiviert', 'repro-ct-suite' ); ?>
							</label>
							<p class="description">
								<?php esc_html_e( 'Wenn aktiviert, werden Termine automatisch im gewählten Intervall synchronisiert.', 'repro-ct-suite' ); ?>
			</p>
						</td>
					</tr>
					
					<tr>
						<th scope="row">
							<?php esc_html_e( 'Intervall', 'repro-ct-suite' ); ?>
						</th>
						<td>
							<input type="number" name="repro_ct_suite_sync_interval" value="<?php echo esc_attr( $sync_interval ); ?>" min="1" max="999" class="small-text" />
							<select name="repro_ct_suite_sync_interval_unit">
								<option value="minutes" <?php selected( $sync_interval_unit, 'minutes' ); ?>><?php esc_html_e( 'Minuten', 'repro-ct-suite' ); ?></option>
								<option value="hours" <?php selected( $sync_interval_unit, 'hours' ); ?>><?php esc_html_e( 'Stunden', 'repro-ct-suite' ); ?></option>
								<option value="days" <?php selected( $sync_interval_unit, 'days' ); ?>><?php esc_html_e( 'Tage', 'repro-ct-suite' ); ?></option>
							</select>
							<p class="description">
								<?php esc_html_e( 'Mindestens 30 Minuten. Empfohlen: 2-6 Stunden.', 'repro-ct-suite' ); ?>
							</p>
						</td>
					</tr>
					
					<?php if ( $auto_sync_enabled ) : ?>
					<tr>
						<th scope="row">
							<?php esc_html_e( 'Status', 'repro-ct-suite' ); ?>
						</th>
						<td>
							<?php if ( $next_scheduled ) : ?>
								<strong><?php esc_html_e( 'Nächster Sync:', 'repro-ct-suite' ); ?></strong>
								<?php echo esc_html( date_i18n( 'd.m.Y H:i', $next_scheduled ) ); ?>
								(<?php echo esc_html( human_time_diff( time(), $next_scheduled ) ); ?>)
								<br>
							<?php endif; ?>
							<?php if ( $last_sync ) : ?>
								<strong><?php esc_html_e( 'Letzter Sync:', 'repro-ct-suite' ); ?></strong>
								<?php echo esc_html( date_i18n( 'd.m.Y H:i', $last_sync ) ); ?>
								(<?php echo esc_html( human_time_diff( $last_sync, time() ) ); ?> <?php esc_html_e( 'her', 'repro-ct-suite' ); ?>)
							<?php endif; ?>
						</td>
					</tr>
					<?php endif; ?>
				</tbody>
			</table>
			
			<p class="submit">
				<button type="submit" name="repro_ct_suite_save_cron_settings" class="button button-primary">
					<?php esc_html_e( 'Einstellungen speichern', 'repro-ct-suite' ); ?>
				</button>
			</p>
		</form>
	</div>
</div>

	</div>
</div>
