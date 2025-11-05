<?php
/**
 * Einstellungsseite für automatischen Sync
 *
 * @package    Repro_CT_Suite
 * @subpackage Repro_CT_Suite/admin/views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Einstellungen speichern
if ( isset( $_POST['repro_ct_suite_save_sync_settings'] ) ) {
	check_admin_referer( 'repro_ct_suite_sync_settings' );
	
	$auto_sync_enabled = isset( $_POST['repro_ct_suite_auto_sync_enabled'] ) ? 1 : 0;
	$sync_interval = absint( $_POST['repro_ct_suite_sync_interval'] ?? 60 );
	$sync_interval_unit = sanitize_text_field( $_POST['repro_ct_suite_sync_interval_unit'] ?? 'minutes' );
	
	// Validierung: Mindestens 30 Minuten
	if ( $sync_interval_unit === 'minutes' && $sync_interval < 30 ) {
		$sync_interval = 30;
	}
	
	update_option( 'repro_ct_suite_auto_sync_enabled', $auto_sync_enabled );
	update_option( 'repro_ct_suite_sync_interval', $sync_interval );
	update_option( 'repro_ct_suite_sync_interval_unit', $sync_interval_unit );
	
	// Cron-Job neu schedulen
	require_once plugin_dir_path( dirname( dirname( __FILE__ ) ) ) . 'includes/class-repro-ct-suite-cron.php';
	Repro_CT_Suite_Cron::reschedule_sync_job();
	
	echo '<div class="notice notice-success is-dismissible"><p>';
	esc_html_e( 'Einstellungen gespeichert.', 'repro-ct-suite' );
	echo '</p></div>';
}

// Aktuelle Einstellungen abrufen
$auto_sync_enabled = get_option( 'repro_ct_suite_auto_sync_enabled', 0 );
$sync_interval = get_option( 'repro_ct_suite_sync_interval', 60 );
$sync_interval_unit = get_option( 'repro_ct_suite_sync_interval_unit', 'minutes' );

// Nächste geplante Ausführung
$next_scheduled = wp_next_scheduled( 'repro_ct_suite_auto_sync' );
$last_sync = get_option( 'repro_ct_suite_last_auto_sync', 0 );

?>

<div class="wrap">
	<h1><?php esc_html_e( 'Automatischer Sync', 'repro-ct-suite' ); ?></h1>
	
	<div class="card" style="max-width: 800px;">
		<h2><?php esc_html_e( 'Sync-Einstellungen', 'repro-ct-suite' ); ?></h2>
		
		<form method="post" action="">
			<?php wp_nonce_field( 'repro_ct_suite_sync_settings' ); ?>
			
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row">
							<label for="auto_sync_enabled">
								<?php esc_html_e( 'Automatischer Sync', 'repro-ct-suite' ); ?>
							</label>
						</th>
						<td>
							<label>
								<input 
									type="checkbox" 
									name="repro_ct_suite_auto_sync_enabled" 
									id="auto_sync_enabled"
									value="1"
									<?php checked( $auto_sync_enabled, 1 ); ?>
								/>
								<?php esc_html_e( 'Automatische Synchronisation aktivieren', 'repro-ct-suite' ); ?>
							</label>
							<p class="description">
								<?php esc_html_e( 'Wenn aktiviert, werden die Termine automatisch im gewählten Intervall synchronisiert.', 'repro-ct-suite' ); ?>
							</p>
						</td>
					</tr>
					
					<tr>
						<th scope="row">
							<label for="sync_interval">
								<?php esc_html_e( 'Sync-Intervall', 'repro-ct-suite' ); ?>
							</label>
						</th>
						<td>
							<div style="display: flex; gap: 10px; align-items: center;">
								<input 
									type="number" 
									name="repro_ct_suite_sync_interval" 
									id="sync_interval"
									value="<?php echo esc_attr( $sync_interval ); ?>"
									min="1"
									max="999"
									class="small-text"
									required
								/>
								
								<select name="repro_ct_suite_sync_interval_unit" id="sync_interval_unit">
									<option value="minutes" <?php selected( $sync_interval_unit, 'minutes' ); ?>>
										<?php esc_html_e( 'Minuten', 'repro-ct-suite' ); ?>
									</option>
									<option value="hours" <?php selected( $sync_interval_unit, 'hours' ); ?>>
										<?php esc_html_e( 'Stunden', 'repro-ct-suite' ); ?>
									</option>
									<option value="days" <?php selected( $sync_interval_unit, 'days' ); ?>>
										<?php esc_html_e( 'Tage', 'repro-ct-suite' ); ?>
									</option>
								</select>
							</div>
							
							<p class="description">
								<?php esc_html_e( 'Mindestintervall: 30 Minuten. Empfohlen: 1-6 Stunden für optimale Performance.', 'repro-ct-suite' ); ?>
							</p>
							
							<?php 
							// Berechne Intervall in Sekunden für Anzeige
							$interval_seconds = $sync_interval;
							switch ( $sync_interval_unit ) {
								case 'hours':
									$interval_seconds *= 60;
									// fall through
								case 'minutes':
									$interval_seconds *= 60;
									break;
								case 'days':
									$interval_seconds *= 86400;
									break;
							}
							
							// Zeige Beispiele
							$per_day = 86400 / $interval_seconds;
							?>
							<p class="description">
								<strong><?php esc_html_e( 'Vorschau:', 'repro-ct-suite' ); ?></strong>
								<?php 
								if ( $sync_interval_unit === 'minutes' ) {
									printf( 
										esc_html__( 'Sync alle %d Minuten ≈ %d Sync-Vorgänge pro Tag', 'repro-ct-suite' ), 
										$sync_interval,
										round( $per_day )
									);
								} elseif ( $sync_interval_unit === 'hours' ) {
									printf( 
										esc_html__( 'Sync alle %d Stunden ≈ %d Sync-Vorgänge pro Tag', 'repro-ct-suite' ), 
										$sync_interval,
										round( $per_day, 1 )
									);
								} else {
									printf( 
										esc_html__( 'Sync alle %d Tage', 'repro-ct-suite' ), 
										$sync_interval
									);
								}
								?>
							</p>
						</td>
					</tr>
				</tbody>
			</table>
			
			<p class="submit">
				<button type="submit" name="repro_ct_suite_save_sync_settings" class="button button-primary">
					<?php esc_html_e( 'Einstellungen speichern', 'repro-ct-suite' ); ?>
				</button>
			</p>
		</form>
	</div>
	
	<!-- Status-Information -->
	<div class="card" style="max-width: 800px; margin-top: 20px;">
		<h2><?php esc_html_e( 'Sync-Status', 'repro-ct-suite' ); ?></h2>
		
		<table class="widefat fixed">
			<tbody>
				<tr>
					<td style="width: 30%; font-weight: 600;">
						<?php esc_html_e( 'Status', 'repro-ct-suite' ); ?>
					</td>
					<td>
						<?php if ( $auto_sync_enabled ) : ?>
							<span style="color: #46b450; font-weight: 600;">
								● <?php esc_html_e( 'Aktiv', 'repro-ct-suite' ); ?>
							</span>
						<?php else : ?>
							<span style="color: #999;">
								○ <?php esc_html_e( 'Deaktiviert', 'repro-ct-suite' ); ?>
							</span>
						<?php endif; ?>
					</td>
				</tr>
				
				<?php if ( $auto_sync_enabled ) : ?>
					<tr>
						<td style="font-weight: 600;">
							<?php esc_html_e( 'Nächster Sync', 'repro-ct-suite' ); ?>
						</td>
						<td>
							<?php if ( $next_scheduled ) : ?>
								<strong><?php echo esc_html( date_i18n( 'd.m.Y H:i:s', $next_scheduled ) ); ?></strong>
								<span style="color: #666;">
									(<?php 
									$time_diff = $next_scheduled - time();
									if ( $time_diff > 0 ) {
										printf( 
											esc_html__( 'in %s', 'repro-ct-suite' ), 
											human_time_diff( time(), $next_scheduled ) 
										);
									} else {
										esc_html_e( 'überfällig', 'repro-ct-suite' );
									}
									?>)
								</span>
							<?php else : ?>
								<span style="color: #dc3232;">
									<?php esc_html_e( 'Nicht geplant - bitte Einstellungen speichern', 'repro-ct-suite' ); ?>
								</span>
							<?php endif; ?>
						</td>
					</tr>
					
					<tr>
						<td style="font-weight: 600;">
							<?php esc_html_e( 'Letzter Sync', 'repro-ct-suite' ); ?>
						</td>
						<td>
							<?php if ( $last_sync ) : ?>
								<?php echo esc_html( date_i18n( 'd.m.Y H:i:s', $last_sync ) ); ?>
								<span style="color: #666;">
									(<?php echo esc_html( human_time_diff( $last_sync, time() ) ); ?> <?php esc_html_e( 'her', 'repro-ct-suite' ); ?>)
								</span>
							<?php else : ?>
								<span style="color: #999;">
									<?php esc_html_e( 'Noch kein automatischer Sync durchgeführt', 'repro-ct-suite' ); ?>
								</span>
							<?php endif; ?>
						</td>
					</tr>
				<?php endif; ?>
				
				<tr>
					<td style="font-weight: 600;">
						<?php esc_html_e( 'WP-Cron Status', 'repro-ct-suite' ); ?>
					</td>
					<td>
						<?php if ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) : ?>
							<span style="color: #dc3232;">
								⚠ <?php esc_html_e( 'WP-Cron ist deaktiviert', 'repro-ct-suite' ); ?>
							</span>
							<p class="description">
								<?php esc_html_e( 'Automatischer Sync funktioniert nur, wenn WP-Cron aktiv ist oder ein System-Cron eingerichtet wurde.', 'repro-ct-suite' ); ?>
							</p>
						<?php else : ?>
							<span style="color: #46b450;">
								✓ <?php esc_html_e( 'WP-Cron ist aktiv', 'repro-ct-suite' ); ?>
							</span>
						<?php endif; ?>
					</td>
				</tr>
			</tbody>
		</table>
		
		<?php if ( $auto_sync_enabled ) : ?>
			<form method="post" style="margin-top: 15px;">
				<?php wp_nonce_field( 'repro_ct_suite_manual_sync_trigger' ); ?>
				<button type="submit" name="repro_ct_suite_trigger_sync_now" class="button">
					🔄 <?php esc_html_e( 'Sync jetzt manuell ausführen', 'repro-ct-suite' ); ?>
				</button>
			</form>
			
			<?php
			// Manueller Sync-Trigger
			if ( isset( $_POST['repro_ct_suite_trigger_sync_now'] ) ) {
				check_admin_referer( 'repro_ct_suite_manual_sync_trigger' );
				
				require_once plugin_dir_path( dirname( dirname( __FILE__ ) ) ) . 'includes/class-repro-ct-suite-cron.php';
				Repro_CT_Suite_Cron::execute_sync();
				
				echo '<div class="notice notice-success is-dismissible" style="margin-top: 15px;"><p>';
				esc_html_e( 'Sync wurde ausgeführt. Prüfen Sie die Termine-Seite für Ergebnisse.', 'repro-ct-suite' );
				echo '</p></div>';
			}
			?>
		<?php endif; ?>
	</div>
	
	<!-- Hilfe und Tipps -->
	<div class="card" style="max-width: 800px; margin-top: 20px;">
		<h2><?php esc_html_e( 'Empfehlungen', 'repro-ct-suite' ); ?></h2>
		
		<ul style="line-height: 1.8;">
			<li>
				<strong><?php esc_html_e( 'Kleine Gemeinden (< 100 Termine/Woche):', 'repro-ct-suite' ); ?></strong>
				<?php esc_html_e( '2-4 Stunden Intervall', 'repro-ct-suite' ); ?>
			</li>
			<li>
				<strong><?php esc_html_e( 'Mittlere Gemeinden (100-500 Termine/Woche):', 'repro-ct-suite' ); ?></strong>
				<?php esc_html_e( '4-6 Stunden Intervall', 'repro-ct-suite' ); ?>
			</li>
			<li>
				<strong><?php esc_html_e( 'Große Gemeinden (> 500 Termine/Woche):', 'repro-ct-suite' ); ?></strong>
				<?php esc_html_e( '1-2 Mal täglich', 'repro-ct-suite' ); ?>
			</li>
			<li>
				<strong><?php esc_html_e( 'Performance-Tipp:', 'repro-ct-suite' ); ?></strong>
				<?php esc_html_e( 'Führen Sie Sync-Vorgänge außerhalb der Hauptnutzungszeiten durch (z.B. nachts).', 'repro-ct-suite' ); ?>
			</li>
		</ul>
	</div>
</div>
