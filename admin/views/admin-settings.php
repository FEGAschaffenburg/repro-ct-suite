<?php
/**
 * Einstellungen-Seite
 *
 * Konfiguration der ChurchTools-Verbindung und Synchronisations-Parameter.
 *
 * @package    Repro_CT_Suite
 * @subpackage Repro_CT_Suite/admin/views
 * @since      0.3.5.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$tenant   = get_option( 'repro_ct_suite_ct_tenant', '' );
$username = get_option( 'repro_ct_suite_ct_username', '' );
$enc_pw   = get_option( 'repro_ct_suite_ct_password', '' );

// Test-Ergebnis aus Transient abrufen (wird von handle_test_connection() gesetzt)
$test_result = get_transient( 'repro_ct_suite_test_result' );
if ( $test_result !== false ) {
	delete_transient( 'repro_ct_suite_test_result' );
}
?>

<div class="wrap repro-ct-suite-admin-wrapper">
	
	<!-- Header-Bereich -->
	<div class="repro-ct-suite-header">
		<h1>
			<span class="dashicons dashicons-admin-settings"></span>
			<?php esc_html_e( 'Einstellungen', 'repro-ct-suite' ); ?>
		</h1>
		<p><?php esc_html_e( 'Konfigurieren Sie die Verbindung zu ChurchTools und die Synchronisations-Parameter.', 'repro-ct-suite' ); ?></p>
	</div>

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

	<!-- Sektion 1: ChurchTools-Verbindung -->
	<div class="repro-ct-suite-card">
		<div class="repro-ct-suite-card-header">
			<span class="dashicons dashicons-admin-links"></span>
			<h3><?php esc_html_e( 'ChurchTools-Verbindung', 'repro-ct-suite' ); ?></h3>
		</div>
		<div class="repro-ct-suite-card-body">
			<form method="post" action="options.php">
				<?php settings_fields( 'repro_ct_suite' ); ?>
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
				<p>
					<button type="submit" class="repro-ct-suite-btn repro-ct-suite-btn-primary">
						<span class="dashicons dashicons-yes"></span>
						<?php esc_html_e( 'Einstellungen speichern', 'repro-ct-suite' ); ?>
					</button>
				</p>
			</form>

			<hr style="margin: 20px 0; border: none; border-top: 1px solid #ddd;">

			<h4><?php esc_html_e( 'Verbindung testen', 'repro-ct-suite' ); ?></h4>
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
					<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=repro-ct-suite-settings&test_connection=1' ), 'repro_ct_suite_test_connection' ) ); ?>" class="repro-ct-suite-btn repro-ct-suite-btn-secondary">
						<span class="dashicons dashicons-update"></span>
						<?php esc_html_e( 'Verbindung jetzt testen', 'repro-ct-suite' ); ?>
					</a>
				</p>
			<?php endif; ?>
		</div>
	</div>

	<!-- Sektion 2: Abrufzeitraum -->
	<div class="repro-ct-suite-card repro-ct-suite-mt-20">
		<div class="repro-ct-suite-card-header">
			<span class="dashicons dashicons-calendar"></span>
			<h3><?php esc_html_e( 'Abrufzeitraum', 'repro-ct-suite' ); ?></h3>
		</div>
		<div class="repro-ct-suite-card-body">
			<p><?php esc_html_e( 'Definieren Sie, welcher Zeitraum bei der Synchronisation berücksichtigt werden soll.', 'repro-ct-suite' ); ?></p>
			<form method="post" action="options.php">
				<?php settings_fields( 'repro_ct_suite' ); ?>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="repro_ct_suite_sync_days_past"><?php esc_html_e( 'Tage in der Vergangenheit', 'repro-ct-suite' ); ?></label></th>
						<td>
							<input type="number" id="repro_ct_suite_sync_days_past" name="repro_ct_suite_sync_days_past" value="<?php echo esc_attr( get_option( 'repro_ct_suite_sync_days_past', 30 ) ); ?>" class="small-text" min="0" max="365" />
							<p class="description"><?php esc_html_e( 'Wie viele Tage in die Vergangenheit sollen Termine synchronisiert werden? (Standard: 30 Tage)', 'repro-ct-suite' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="repro_ct_suite_sync_days_future"><?php esc_html_e( 'Tage in der Zukunft', 'repro-ct-suite' ); ?></label></th>
						<td>
							<input type="number" id="repro_ct_suite_sync_days_future" name="repro_ct_suite_sync_days_future" value="<?php echo esc_attr( get_option( 'repro_ct_suite_sync_days_future', 90 ) ); ?>" class="small-text" min="0" max="730" />
							<p class="description"><?php esc_html_e( 'Wie viele Tage in die Zukunft sollen Termine synchronisiert werden? (Standard: 90 Tage)', 'repro-ct-suite' ); ?></p>
						</td>
					</tr>
				</table>
				<p>
					<button type="submit" class="repro-ct-suite-btn repro-ct-suite-btn-primary">
						<span class="dashicons dashicons-yes"></span>
						<?php esc_html_e( 'Zeitraum speichern', 'repro-ct-suite' ); ?>
					</button>
				</p>
			</form>
		</div>
	</div>

	<!-- Sektion 3: Automatisierung (Platzhalter für zukünftige Cron-Einstellungen) -->
	<div class="repro-ct-suite-card repro-ct-suite-mt-20">
		<div class="repro-ct-suite-card-header">
			<span class="dashicons dashicons-clock"></span>
			<h3><?php esc_html_e( 'Automatisierung', 'repro-ct-suite' ); ?></h3>
		</div>
		<div class="repro-ct-suite-card-body">
			<p><?php esc_html_e( 'Automatische Synchronisation per Cron-Job (in Entwicklung).', 'repro-ct-suite' ); ?></p>
			<div class="repro-ct-suite-notice repro-ct-suite-notice-info repro-ct-suite-mt-10">
				<span class="dashicons dashicons-info"></span>
				<div>
					<p><?php esc_html_e( 'Diese Funktion wird in einer zukünftigen Version verfügbar sein.', 'repro-ct-suite' ); ?></p>
				</div>
			</div>
		</div>
	</div>

	<!-- Hinweise zur Sicherheit -->
	<div class="repro-ct-suite-card repro-ct-suite-mt-20">
		<div class="repro-ct-suite-card-header">
			<span class="dashicons dashicons-shield"></span>
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

</div>
