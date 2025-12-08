<?php
/**
 * Admin-Seite: Tab Lizenz
 *
 * @package    Repro_CT_Suite
 * @subpackage Repro_CT_Suite/admin/views/tabs
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Lizenzinformationen aus Optionen abrufen
$license_key    = get_option( 'repro_ct_suite_license_key', '' );
$license_status = get_option( 'repro_ct_suite_license_status', 'inactive' );
$license_email  = get_option( 'repro_ct_suite_license_email', '' );
$license_expiry = get_option( 'repro_ct_suite_license_expiry', '' );

?>

<div class="rcts-section">
	<h2><?php esc_html_e( 'Lizenzverwaltung', 'repro-ct-suite' ); ?></h2>
	
	<div class="rcts-info-box">
		<p>
			<span class="dashicons dashicons-info"></span>
			<?php esc_html_e( 'Verwalten Sie hier Ihre Plugin-Lizenz. Eine gültige Lizenz ist erforderlich für automatische Updates und Support.', 'repro-ct-suite' ); ?>
		</p>
	</div>

	<form method="post" action="options.php" class="rcts-form">
		<?php settings_fields( 'repro_ct_suite_license_group' ); ?>
		
		<table class="form-table">
			<tr>
				<th scope="row">
					<label for="license_key">
						<?php esc_html_e( 'Lizenzschlüssel', 'repro-ct-suite' ); ?>
					</label>
				</th>
				<td>
					<input 
						type="text" 
						id="license_key" 
						name="repro_ct_suite_license_key" 
						value="<?php echo esc_attr( $license_key ); ?>" 
						class="regular-text"
						placeholder="XXXX-XXXX-XXXX-XXXX"
					/>
					<p class="description">
						<?php esc_html_e( 'Geben Sie Ihren Lizenzschlüssel ein.', 'repro-ct-suite' ); ?>
					</p>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<label for="license_email">
						<?php esc_html_e( 'E-Mail-Adresse', 'repro-ct-suite' ); ?>
					</label>
				</th>
				<td>
					<input 
						type="email" 
						id="license_email" 
						name="repro_ct_suite_license_email" 
						value="<?php echo esc_attr( $license_email ); ?>" 
						class="regular-text"
					/>
					<p class="description">
						<?php esc_html_e( 'Die E-Mail-Adresse, die bei der Lizenzregistrierung verwendet wurde.', 'repro-ct-suite' ); ?>
					</p>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<?php esc_html_e( 'Lizenzstatus', 'repro-ct-suite' ); ?>
				</th>
				<td>
					<?php if ( $license_status === 'active' ) : ?>
						<span class="rcts-status rcts-status-success">
							<span class="dashicons dashicons-yes-alt"></span>
							<?php esc_html_e( 'Aktiv', 'repro-ct-suite' ); ?>
						</span>
					<?php elseif ( $license_status === 'expired' ) : ?>
						<span class="rcts-status rcts-status-warning">
							<span class="dashicons dashicons-warning"></span>
							<?php esc_html_e( 'Abgelaufen', 'repro-ct-suite' ); ?>
						</span>
					<?php else : ?>
						<span class="rcts-status rcts-status-error">
							<span class="dashicons dashicons-dismiss"></span>
							<?php esc_html_e( 'Inaktiv', 'repro-ct-suite' ); ?>
						</span>
					<?php endif; ?>

					<?php if ( $license_expiry ) : ?>
						<p class="description">
							<?php 
							printf( 
								esc_html__( 'Gültig bis: %s', 'repro-ct-suite' ), 
								esc_html( date_i18n( get_option( 'date_format' ), strtotime( $license_expiry ) ) )
							); 
							?>
						</p>
					<?php endif; ?>
				</td>
			</tr>
		</table>

		<?php submit_button( __( 'Lizenz speichern', 'repro-ct-suite' ) ); ?>
	</form>

	<?php if ( ! empty( $license_key ) ) : ?>
		<hr>
		
		<h3><?php esc_html_e( 'Lizenzaktionen', 'repro-ct-suite' ); ?></h3>
		
		<div class="rcts-button-group">
			<?php if ( $license_status !== 'active' ) : ?>
				<button type="button" id="activate-license" class="button button-primary">
					<span class="dashicons dashicons-yes"></span>
					<?php esc_html_e( 'Lizenz aktivieren', 'repro-ct-suite' ); ?>
				</button>
			<?php else : ?>
				<button type="button" id="deactivate-license" class="button button-secondary">
					<span class="dashicons dashicons-no"></span>
					<?php esc_html_e( 'Lizenz deaktivieren', 'repro-ct-suite' ); ?>
				</button>
			<?php endif; ?>
			
			<button type="button" id="check-license" class="button button-secondary">
				<span class="dashicons dashicons-update"></span>
				<?php esc_html_e( 'Lizenz prüfen', 'repro-ct-suite' ); ?>
			</button>
		</div>

		<div id="license-message" class="notice" style="display: none; margin-top: 15px;"></div>
	<?php endif; ?>

	<hr>

	<div class="rcts-info-box" style="margin-top: 20px;">
		<h3><?php esc_html_e( 'Lizenz erwerben', 'repro-ct-suite' ); ?></h3>
		<p>
			<?php esc_html_e( 'Sie haben noch keine Lizenz? Erwerben Sie eine Lizenz, um Zugriff auf automatische Updates und Premium-Support zu erhalten.', 'repro-ct-suite' ); ?>
		</p>
		<p>
			<a href="https://feg-aschaffenburg.de/produkt/churchtools-suite/" target="_blank" rel="noopener" class="button button-primary">
				<span class="dashicons dashicons-cart"></span>
				<?php esc_html_e( 'Lizenz kaufen', 'repro-ct-suite' ); ?>
			</a>
			<a href="https://feg-aschaffenburg.de/support/" target="_blank" rel="noopener" class="button button-secondary">
				<span class="dashicons dashicons-sos"></span>
				<?php esc_html_e( 'Support kontaktieren', 'repro-ct-suite' ); ?>
			</a>
		</p>
	</div>
</div>

<style>
.rcts-status {
	display: inline-flex;
	align-items: center;
	gap: 5px;
	padding: 5px 12px;
	border-radius: 3px;
	font-weight: 600;
}

.rcts-status-success {
	background-color: #d4edda;
	color: #155724;
}

.rcts-status-warning {
	background-color: #fff3cd;
	color: #856404;
}

.rcts-status-error {
	background-color: #f8d7da;
	color: #721c24;
}

.rcts-button-group {
	display: flex;
	gap: 10px;
	flex-wrap: wrap;
}

.rcts-button-group .button .dashicons {
	margin-top: 3px;
}
</style>

<script>
jQuery(document).ready(function($) {
	$('#activate-license').on('click', function() {
		const button = $(this);
		const licenseKey = $('#license_key').val();
		const licenseEmail = $('#license_email').val();
		
		if (!licenseKey || !licenseEmail) {
			showLicenseMessage('error', '<?php esc_html_e( 'Bitte geben Sie Lizenzschlüssel und E-Mail-Adresse ein.', 'repro-ct-suite' ); ?>');
			return;
		}

		button.prop('disabled', true).text('<?php esc_html_e( 'Aktiviere...', 'repro-ct-suite' ); ?>');

		$.post(ajaxurl, {
			action: 'repro_ct_suite_activate_license',
			license_key: licenseKey,
			license_email: licenseEmail,
			nonce: '<?php echo wp_create_nonce( 'repro_ct_suite_license_nonce' ); ?>'
		}, function(response) {
			if (response.success) {
				showLicenseMessage('success', response.data.message);
				setTimeout(() => location.reload(), 2000);
			} else {
				showLicenseMessage('error', response.data.message);
				button.prop('disabled', false).text('<?php esc_html_e( 'Lizenz aktivieren', 'repro-ct-suite' ); ?>');
			}
		});
	});

	$('#deactivate-license').on('click', function() {
		if (!confirm('<?php esc_html_e( 'Möchten Sie die Lizenz wirklich deaktivieren?', 'repro-ct-suite' ); ?>')) {
			return;
		}

		const button = $(this);
		button.prop('disabled', true).text('<?php esc_html_e( 'Deaktiviere...', 'repro-ct-suite' ); ?>');

		$.post(ajaxurl, {
			action: 'repro_ct_suite_deactivate_license',
			nonce: '<?php echo wp_create_nonce( 'repro_ct_suite_license_nonce' ); ?>'
		}, function(response) {
			if (response.success) {
				showLicenseMessage('success', response.data.message);
				setTimeout(() => location.reload(), 2000);
			} else {
				showLicenseMessage('error', response.data.message);
				button.prop('disabled', false).text('<?php esc_html_e( 'Lizenz deaktivieren', 'repro-ct-suite' ); ?>');
			}
		});
	});

	$('#check-license').on('click', function() {
		const button = $(this);
		button.prop('disabled', true).text('<?php esc_html_e( 'Prüfe...', 'repro-ct-suite' ); ?>');

		$.post(ajaxurl, {
			action: 'repro_ct_suite_check_license',
			nonce: '<?php echo wp_create_nonce( 'repro_ct_suite_license_nonce' ); ?>'
		}, function(response) {
			if (response.success) {
				showLicenseMessage('success', response.data.message);
				setTimeout(() => location.reload(), 2000);
			} else {
				showLicenseMessage('error', response.data.message);
			}
			button.prop('disabled', false).text('<?php esc_html_e( 'Lizenz prüfen', 'repro-ct-suite' ); ?>');
		});
	});

	function showLicenseMessage(type, message) {
		const messageBox = $('#license-message');
		messageBox
			.removeClass('notice-success notice-error')
			.addClass('notice-' + type)
			.html('<p>' + message + '</p>')
			.slideDown();
	}
});
</script>
