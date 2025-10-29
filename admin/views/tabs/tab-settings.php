<?php
/**
 * Einstellungen Tab Template
 *
 * Formular für ChurchTools-API-Konfiguration.
 *
 * @package    Repro_CT_Suite
 * @subpackage Repro_CT_Suite/admin/views/tabs
 * @since      0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$base_url = get_option( 'repro_ct_suite_ct_base_url', '' );
$username = get_option( 'repro_ct_suite_ct_username', '' );
$enc_pw   = get_option( 'repro_ct_suite_ct_password', '' );

?>
<div class="repro-ct-suite-card">
	<div class="repro-ct-suite-card-header">
		<span class="dashicons dashicons-admin-settings"></span>
		<h3><?php esc_html_e( 'ChurchTools-Verbindung', 'repro-ct-suite' ); ?></h3>
	</div>
	<div class="repro-ct-suite-card-body">
		<form method="post" action="options.php">
			<?php settings_fields( 'repro_ct_suite' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="repro_ct_suite_ct_base_url"><?php esc_html_e( 'Basis-URL', 'repro-ct-suite' ); ?></label></th>
					<td>
						<input type="url" id="repro_ct_suite_ct_base_url" name="repro_ct_suite_ct_base_url" value="<?php echo esc_attr( $base_url ); ?>" class="regular-text" placeholder="https://gemeinde.church.tools" />
						<p class="description"><?php esc_html_e( 'Beispiel: https://gemeinde.church.tools (ohne Slash am Ende)', 'repro-ct-suite' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="repro_ct_suite_ct_username"><?php esc_html_e( 'Benutzername', 'repro-ct-suite' ); ?></label></th>
					<td>
						<input type="text" id="repro_ct_suite_ct_username" name="repro_ct_suite_ct_username" value="<?php echo esc_attr( $username ); ?>" class="regular-text" />
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="repro_ct_suite_ct_password"><?php esc_html_e( 'Passwort', 'repro-ct-suite' ); ?></label></th>
					<td>
						<input type="password" id="repro_ct_suite_ct_password" name="repro_ct_suite_ct_password" value="" class="regular-text" autocomplete="new-password" />
						<p class="description"><?php esc_html_e( 'Wird verschlüsselt gespeichert. Leer lassen, um das bestehende Passwort beizubehalten.', 'repro-ct-suite' ); ?></p>
						<?php if ( ! empty( $enc_pw ) ) : ?>
							<p class="description" style="color:#46b450;">✓ <?php esc_html_e( 'Ein Passwort ist gespeichert.', 'repro-ct-suite' ); ?></p>
						<?php endif; ?>
					</td>
				</tr>
			</table>
			<p>
				<button type="submit" class="button button-primary">
					<?php esc_html_e( 'Einstellungen speichern', 'repro-ct-suite' ); ?>
				</button>
			</p>
		</form>
	</div>
</div>
