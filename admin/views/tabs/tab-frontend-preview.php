<?php
/**
 * Frontend Tab: Vorschau
 *
 * @package Repro_CT_Suite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<h2><?php esc_html_e( 'Live-Vorschau', 'repro-ct-suite' ); ?></h2>
<p class="description">
	<?php esc_html_e( 'Testen Sie verschiedene Shortcode-Konfigurationen direkt hier.', 'repro-ct-suite' ); ?>
</p>

<div class="preview-wrapper">
	<div class="preview-controls">
		<select id="preview-template" class="regular-text">
			<option value="[rcts_events view=&quot;list&quot; limit=&quot;5&quot;]"><?php esc_html_e( 'Liste (einfach) - 5 Termine', 'repro-ct-suite' ); ?></option>
			<option value="[rcts_events view=&quot;list-grouped&quot; limit=&quot;10&quot;]"><?php esc_html_e( 'Liste (gruppiert) - 10 Termine', 'repro-ct-suite' ); ?></option>
			<option value="[rcts_events view=&quot;cards&quot; limit=&quot;12&quot;]" selected><?php esc_html_e( 'Kacheln - 12 Termine', 'repro-ct-suite' ); ?></option>
			<option value="[rcts_events to_days=&quot;7&quot;]"><?php esc_html_e( 'Nächste 7 Tage', 'repro-ct-suite' ); ?></option>
		</select>
		<button type="button" id="reload-preview" class="button">
			<span class="dashicons dashicons-update"></span>
			<?php esc_html_e( 'Neu laden', 'repro-ct-suite' ); ?>
		</button>
	</div>

	<div id="preview-container" class="preview-container">
		<p class="preview-loading">
			<span class="spinner is-active" style="float:none;"></span>
			<?php esc_html_e( 'Vorschau wird geladen...', 'repro-ct-suite' ); ?>
		</p>
	</div>
</div>

<style>
.preview-wrapper {
	background: #fff;
	padding: 20px;
	border: 1px solid #ddd;
	border-radius: 4px;
}

.preview-controls {
	display: flex;
	gap: 10px;
	margin-bottom: 20px;
	align-items: center;
}

.preview-controls select {
	flex: 1;
}

.preview-container {
	min-height: 400px;
	background: #f9f9f9;
	border: 2px dashed #ccc;
	padding: 20px;
	border-radius: 4px;
}

.preview-loading {
	text-align: center;
	color: #666;
	padding: 40px;
}
</style>

<script>
jQuery(document).ready(function($) {
	function loadPreview() {
		var shortcode = $('#preview-template').val();
		
		$('#preview-container').html('<p class="preview-loading"><span class="spinner is-active" style="float:none;"></span> <?php esc_html_e( 'Vorschau wird geladen...', 'repro-ct-suite' ); ?></p>');

		$.post(ajaxurl, {
			action: 'repro_ct_suite_preview_shortcode',
			shortcode: shortcode,
			nonce: '<?php echo wp_create_nonce( 'repro_ct_suite_preview' ); ?>'
		}, function(response) {
			if (response.success) {
				$('#preview-container').html(response.data.html);
			} else {
				$('#preview-container').html('<p class="error"><?php esc_html_e( 'Fehler beim Laden der Vorschau', 'repro-ct-suite' ); ?>: ' + response.data.message + '</p>');
			}
		}).fail(function() {
			$('#preview-container').html('<p class="error"><?php esc_html_e( 'Fehler beim Laden der Vorschau', 'repro-ct-suite' ); ?></p>');
		});
	}

	$('#preview-template').on('change', loadPreview);
	$('#reload-preview').on('click', loadPreview);

	// Initial laden
	loadPreview();
});
</script>
