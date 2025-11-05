<?php
/**
 * Frontend Tab: Shortcode Generator
 *
 * @package Repro_CT_Suite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Kalender für Dropdown laden
global $wpdb;
$calendars_table = $wpdb->prefix . 'rcts_calendars';
$calendars = $wpdb->get_results( "SELECT id, calendar_id, name, color FROM {$calendars_table} WHERE is_selected = 1 ORDER BY name ASC" );
?>

<div class="shortcode-generator-wrapper">
	<div class="generator-columns">
		<!-- Linke Spalte: Konfiguration -->
		<div class="generator-config">
			<h2><?php esc_html_e( 'Shortcode Generator', 'repro-ct-suite' ); ?></h2>
			<p class="description">
				<?php esc_html_e( 'Erstellen Sie einen individualisierten Shortcode für die Anzeige Ihrer Termine.', 'repro-ct-suite' ); ?>
			</p>

			<!-- Preset Manager -->
			<div class="preset-manager">
				<div class="preset-controls">
					<div class="preset-load">
						<label for="preset-select">
							<?php esc_html_e( 'Gespeicherte Presets', 'repro-ct-suite' ); ?>
						</label>
						<div class="preset-select-wrapper">
							<select id="preset-select" class="regular-text">
								<option value=""><?php esc_html_e( '-- Neues Preset --', 'repro-ct-suite' ); ?></option>
							</select>
							<button type="button" id="load-preset-btn" class="button" disabled>
								<span class="dashicons dashicons-download"></span>
								<?php esc_html_e( 'Laden', 'repro-ct-suite' ); ?>
							</button>
							<button type="button" id="delete-preset-btn" class="button button-link-delete" disabled>
								<span class="dashicons dashicons-trash"></span>
							</button>
						</div>
					</div>
					<div class="preset-save">
						<button type="button" id="save-preset-btn" class="button">
							<span class="dashicons dashicons-saved"></span>
							<?php esc_html_e( 'Als Preset speichern', 'repro-ct-suite' ); ?>
						</button>
					</div>
				</div>
			</div>

			<hr style="margin: 20px 0;">

			<form id="shortcode-generator-form" class="generator-form">
				
				<!-- Ansicht -->
				<div class="form-group">
					<label for="view">
						<?php esc_html_e( 'Ansicht', 'repro-ct-suite' ); ?>
						<span class="dashicons dashicons-info-outline" title="<?php esc_attr_e( 'Wählen Sie die Darstellungsart der Termine', 'repro-ct-suite' ); ?>"></span>
					</label>
					<select id="view" name="view" class="regular-text">
						<option value="list"><?php esc_html_e( 'Liste (einfach)', 'repro-ct-suite' ); ?></option>
						<option value="list-grouped"><?php esc_html_e( 'Liste (nach Datum gruppiert)', 'repro-ct-suite' ); ?></option>
						<option value="cards" selected><?php esc_html_e( 'Kacheln (Grid)', 'repro-ct-suite' ); ?></option>
					</select>
					<p class="description">
						<?php esc_html_e( 'Die Darstellungsart beeinflusst das Layout der Termine.', 'repro-ct-suite' ); ?>
					</p>
				</div>

				<!-- Anzahl -->
				<div class="form-group">
					<label for="limit">
						<?php esc_html_e( 'Anzahl Termine', 'repro-ct-suite' ); ?>
					</label>
					<input type="number" id="limit" name="limit" value="10" min="1" max="100" class="small-text">
					<p class="description">
						<?php esc_html_e( 'Maximale Anzahl anzuzeigender Termine (1-100)', 'repro-ct-suite' ); ?>
					</p>
				</div>

				<!-- Kalender-Auswahl -->
				<div class="form-group">
					<label for="calendar_ids">
						<?php esc_html_e( 'Kalender', 'repro-ct-suite' ); ?>
					</label>
					<select id="calendar_ids" name="calendar_ids[]" multiple class="regular-text" style="height: 120px;">
						<option value="" selected><?php esc_html_e( 'Alle Kalender', 'repro-ct-suite' ); ?></option>
						<?php foreach ( $calendars as $calendar ) : ?>
							<option value="<?php echo esc_attr( $calendar->calendar_id ); ?>" 
								style="color: <?php echo esc_attr( $calendar->color ); ?>">
								<?php echo esc_html( $calendar->name ); ?>
							</option>
						<?php endforeach; ?>
					</select>
					<p class="description">
						<?php esc_html_e( 'Mehrfachauswahl mit Strg/Cmd + Klick. Leer = alle Kalender.', 'repro-ct-suite' ); ?>
					</p>
				</div>

				<!-- Zeitraum -->
				<div class="form-group">
					<label><?php esc_html_e( 'Zeitraum', 'repro-ct-suite' ); ?></label>
					<div class="date-range-inputs">
						<div class="date-input">
							<label for="from_days" class="small-label">
								<?php esc_html_e( 'Von (Tage)', 'repro-ct-suite' ); ?>
							</label>
							<input type="number" id="from_days" name="from_days" value="0" class="small-text">
						</div>
						<div class="date-input">
							<label for="to_days" class="small-label">
								<?php esc_html_e( 'Bis (Tage)', 'repro-ct-suite' ); ?>
							</label>
							<input type="number" id="to_days" name="to_days" value="30" class="small-text">
						</div>
					</div>
					<p class="description">
						<?php esc_html_e( 'Anzahl Tage relativ zu heute. Negative Werte = Vergangenheit.', 'repro-ct-suite' ); ?>
					</p>
				</div>

				<!-- Vergangene Events -->
				<div class="form-group">
					<label>
						<input type="checkbox" id="show_past" name="show_past" value="true">
						<?php esc_html_e( 'Vergangene Termine anzeigen', 'repro-ct-suite' ); ?>
					</label>
				</div>

				<!-- Sortierung -->
				<div class="form-group">
					<label for="order">
						<?php esc_html_e( 'Sortierung', 'repro-ct-suite' ); ?>
					</label>
					<select id="order" name="order" class="regular-text">
						<option value="asc"><?php esc_html_e( 'Aufsteigend (älteste zuerst)', 'repro-ct-suite' ); ?></option>
						<option value="desc"><?php esc_html_e( 'Absteigend (neueste zuerst)', 'repro-ct-suite' ); ?></option>
					</select>
				</div>

				<!-- Angezeigte Felder -->
				<div class="form-group">
					<label><?php esc_html_e( 'Angezeigte Felder', 'repro-ct-suite' ); ?></label>
					<div class="checkbox-group">
						<label><input type="checkbox" name="show_fields[]" value="title" checked> <?php esc_html_e( 'Titel', 'repro-ct-suite' ); ?></label>
						<label><input type="checkbox" name="show_fields[]" value="date" checked> <?php esc_html_e( 'Datum', 'repro-ct-suite' ); ?></label>
						<label><input type="checkbox" name="show_fields[]" value="time" checked> <?php esc_html_e( 'Uhrzeit', 'repro-ct-suite' ); ?></label>
						<label><input type="checkbox" name="show_fields[]" value="location" checked> <?php esc_html_e( 'Ort', 'repro-ct-suite' ); ?></label>
						<label><input type="checkbox" name="show_fields[]" value="description"> <?php esc_html_e( 'Beschreibung', 'repro-ct-suite' ); ?></label>
						<label><input type="checkbox" name="show_fields[]" value="calendar"> <?php esc_html_e( 'Kalender', 'repro-ct-suite' ); ?></label>
					</div>
				</div>

				<button type="button" id="generate-shortcode" class="button button-primary button-large">
					<?php esc_html_e( 'Shortcode generieren', 'repro-ct-suite' ); ?>
				</button>
			</form>
		</div>

		<!-- Rechte Spalte: Generierter Shortcode + Vorschau -->
		<div class="generator-output">
			<h3><?php esc_html_e( 'Generierter Shortcode', 'repro-ct-suite' ); ?></h3>
			<div class="shortcode-output-box">
				<code id="generated-shortcode">[rcts_events view="cards" limit="10"]</code>
				<button type="button" id="copy-shortcode" class="button button-secondary">
					<span class="dashicons dashicons-clipboard"></span>
					<?php esc_html_e( 'Kopieren', 'repro-ct-suite' ); ?>
				</button>
			</div>
			<p class="description">
				<?php esc_html_e( 'Kopieren Sie diesen Shortcode in einen Beitrag oder eine Seite.', 'repro-ct-suite' ); ?>
			</p>

			<h3><?php esc_html_e( 'Live-Vorschau', 'repro-ct-suite' ); ?></h3>
			<div class="shortcode-preview-box">
				<div id="shortcode-preview" class="preview-loading">
					<p><?php esc_html_e( 'Klicken Sie auf "Shortcode generieren" für eine Vorschau...', 'repro-ct-suite' ); ?></p>
				</div>
			</div>

			<h3><?php esc_html_e( 'Verwendungsbeispiele', 'repro-ct-suite' ); ?></h3>
			<div class="usage-examples">
				<h4><?php esc_html_e( 'Kachelansicht (empfohlen)', 'repro-ct-suite' ); ?></h4>
				<code>[rcts_events view="cards" limit="12"]</code>

				<h4><?php esc_html_e( 'Nur nächste 7 Tage', 'repro-ct-suite' ); ?></h4>
				<code>[rcts_events to_days="7"]</code>

				<h4><?php esc_html_e( 'Bestimmte Kalender', 'repro-ct-suite' ); ?></h4>
				<code>[rcts_events calendar_ids="1,2,3"]</code>

				<h4><?php esc_html_e( 'Mit Vergangenheit', 'repro-ct-suite' ); ?></h4>
				<code>[rcts_events from_days="-7" show_past="true"]</code>
			</div>
		</div>
	</div>
</div>

<style>
.preset-manager {
	background: #f9f9f9;
	padding: 15px;
	border: 1px solid #ddd;
	border-radius: 4px;
	margin-bottom: 20px;
}

.preset-controls {
	display: grid;
	grid-template-columns: 2fr 1fr;
	gap: 15px;
}

.preset-load label {
	display: block;
	margin-bottom: 8px;
	font-weight: 600;
}

.preset-select-wrapper {
	display: flex;
	gap: 10px;
	align-items: center;
}

.preset-select-wrapper select {
	flex: 1;
}

.preset-select-wrapper button {
	white-space: nowrap;
}

#delete-preset-btn {
	color: #a00;
}

#delete-preset-btn:hover {
	color: #dc3232;
}

.preset-save {
	display: flex;
	align-items: flex-end;
}

.preset-save button {
	width: 100%;
}

.generator-columns {
	display: grid;
	grid-template-columns: 1fr 1fr;
	gap: 30px;
	margin-top: 20px;
}

.generator-config,
.generator-output {
	background: #fff;
	padding: 20px;
	border: 1px solid #ddd;
	border-radius: 4px;
}

.form-group {
	margin-bottom: 20px;
}

.form-group label {
	display: block;
	margin-bottom: 8px;
	font-weight: 600;
}

.form-group .small-label {
	font-weight: normal;
	font-size: 0.9em;
}

.date-range-inputs {
	display: grid;
	grid-template-columns: 1fr 1fr;
	gap: 15px;
}

.checkbox-group {
	display: grid;
	grid-template-columns: repeat(2, 1fr);
	gap: 8px;
}

.checkbox-group label {
	font-weight: normal;
	display: flex;
	align-items: center;
	gap: 5px;
}

.shortcode-output-box {
	background: #f5f5f5;
	border: 1px solid #ddd;
	padding: 15px;
	border-radius: 4px;
	position: relative;
	margin-bottom: 15px;
}

.shortcode-output-box code {
	display: block;
	font-size: 14px;
	word-wrap: break-word;
	margin-bottom: 10px;
}

.shortcode-preview-box {
	background: #f9f9f9;
	border: 2px dashed #ccc;
	padding: 20px;
	min-height: 300px;
	border-radius: 4px;
	margin-bottom: 20px;
}

.preview-loading {
	text-align: center;
	color: #666;
}

.usage-examples {
	background: #f0f0f1;
	padding: 15px;
	border-radius: 4px;
}

.usage-examples h4 {
	margin-top: 15px;
	margin-bottom: 5px;
	font-size: 13px;
}

.usage-examples h4:first-child {
	margin-top: 0;
}

.usage-examples code {
	display: block;
	background: #fff;
	padding: 8px;
	border-left: 3px solid #2271b1;
	margin-bottom: 10px;
}

@media (max-width: 1200px) {
	.generator-columns {
		grid-template-columns: 1fr;
	}
}
</style>

<script>
jQuery(document).ready(function($) {
	// Shortcode generieren
	$('#generate-shortcode, #shortcode-generator-form input, #shortcode-generator-form select').on('change input', function() {
		generateShortcode();
	});

	function generateShortcode() {
		var shortcode = '[rcts_events';
		var attributes = [];

		// View
		var view = $('#view').val();
		if (view !== 'list') {
			attributes.push('view="' + view + '"');
		}

		// Limit
		var limit = $('#limit').val();
		if (limit && limit != 10) {
			attributes.push('limit="' + limit + '"');
		}

		// Kalender
		var calendars = $('#calendar_ids').val();
		if (calendars && calendars.length > 0 && calendars[0] !== '') {
			attributes.push('calendar_ids="' + calendars.join(',') + '"');
		}

		// Zeitraum
		var fromDays = $('#from_days').val();
		var toDays = $('#to_days').val();
		if (fromDays && fromDays != 0) {
			attributes.push('from_days="' + fromDays + '"');
		}
		if (toDays && toDays != 30) {
			attributes.push('to_days="' + toDays + '"');
		}

		// Vergangene
		if ($('#show_past').is(':checked')) {
			attributes.push('show_past="true"');
		}

		// Sortierung
		var order = $('#order').val();
		if (order === 'desc') {
			attributes.push('order="desc"');
		}

		// Felder
		var fields = [];
		$('input[name="show_fields[]"]:checked').each(function() {
			fields.push($(this).val());
		});
		if (fields.length > 0 && fields.join(',') !== 'title,date,time,location') {
			attributes.push('show_fields="' + fields.join(',') + '"');
		}

		if (attributes.length > 0) {
			shortcode += ' ' + attributes.join(' ');
		}
		shortcode += ']';

		$('#generated-shortcode').text(shortcode);
		
		// Vorschau laden
		loadPreview(shortcode);
	}

	// Shortcode kopieren
	$('#copy-shortcode').on('click', function() {
		var shortcode = $('#generated-shortcode').text();
		navigator.clipboard.writeText(shortcode).then(function() {
			var $btn = $('#copy-shortcode');
			$btn.html('<span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Kopiert!', 'repro-ct-suite' ); ?>');
			setTimeout(function() {
				$btn.html('<span class="dashicons dashicons-clipboard"></span> <?php esc_html_e( 'Kopieren', 'repro-ct-suite' ); ?>');
			}, 2000);
		});
	});

	// Vorschau laden
	function loadPreview(shortcode) {
		$('#shortcode-preview').html('<p class="preview-loading"><span class="spinner is-active" style="float:none;"></span> <?php esc_html_e( 'Vorschau wird geladen...', 'repro-ct-suite' ); ?></p>');

		$.post(ajaxurl, {
			action: 'repro_ct_suite_preview_shortcode',
			shortcode: shortcode,
			nonce: '<?php echo wp_create_nonce( 'repro_ct_suite_preview' ); ?>'
		}, function(response) {
			if (response.success) {
				$('#shortcode-preview').html(response.data.html);
			} else {
				$('#shortcode-preview').html('<p class="error"><?php esc_html_e( 'Fehler beim Laden der Vorschau', 'repro-ct-suite' ); ?>: ' + response.data.message + '</p>');
			}
		}).fail(function() {
			$('#shortcode-preview').html('<p class="error"><?php esc_html_e( 'Fehler beim Laden der Vorschau', 'repro-ct-suite' ); ?></p>');
		});
	}

	// Initial generieren
	generateShortcode();

	// ========================================
	// PRESET MANAGEMENT
	// ========================================

	var presets = [];
	var currentPresetId = null;

	// Presets laden
	function loadPresets() {
		$.post(ajaxurl, {
			action: 'repro_ct_suite_get_presets',
			nonce: '<?php echo wp_create_nonce( 'repro_ct_suite_presets' ); ?>'
		}, function(response) {
			if (response.success) {
				presets = response.data.presets;
				updatePresetDropdown();
			}
		});
	}

	// Preset-Dropdown aktualisieren
	function updatePresetDropdown() {
		var $select = $('#preset-select');
		$select.find('option:not(:first)').remove();
		
		presets.forEach(function(preset) {
			$select.append(
				$('<option></option>')
					.val(preset.id)
					.text(preset.name)
			);
		});
	}

	// Preset-Select onChange
	$('#preset-select').on('change', function() {
		var presetId = $(this).val();
		var hasPreset = presetId !== '';
		
		$('#load-preset-btn').prop('disabled', !hasPreset);
		$('#delete-preset-btn').prop('disabled', !hasPreset);
		
		currentPresetId = hasPreset ? parseInt(presetId) : null;
	});

	// Preset laden
	$('#load-preset-btn').on('click', function() {
		if (!currentPresetId) return;

		$.post(ajaxurl, {
			action: 'repro_ct_suite_load_preset',
			preset_id: currentPresetId,
			nonce: '<?php echo wp_create_nonce( 'repro_ct_suite_presets' ); ?>'
		}, function(response) {
			if (response.success) {
				var preset = response.data.preset;
				fillFormWithPreset(preset);
				alert('<?php esc_html_e( 'Preset geladen!', 'repro-ct-suite' ); ?>');
			} else {
				alert('<?php esc_html_e( 'Fehler beim Laden:', 'repro-ct-suite' ); ?> ' + response.data.message);
			}
		});
	});

	// Formular mit Preset-Daten füllen
	function fillFormWithPreset(preset) {
		// View
		$('#view').val(preset.view || 'cards');
		
		// Limit
		$('#limit').val(preset.limit_count || 10);
		
		// Calendar IDs
		$('#calendar_ids').val([]);
		if (preset.calendar_ids) {
			var ids = preset.calendar_ids.split(',');
			$('#calendar_ids').val(ids);
		}
		
		// Date range
		$('#from_days').val(preset.from_days || 0);
		$('#to_days').val(preset.to_days || 90);
		
		// Show past
		$('#show_past').prop('checked', preset.show_past == 1);
		
		// Order
		$('#order').val(preset.order_dir || 'ASC');
		
		// Show fields
		$('#show_fields input[type="checkbox"]').prop('checked', false);
		if (preset.show_fields) {
			var fields = preset.show_fields.split(',');
			fields.forEach(function(field) {
				$('#show_fields input[value="' + field + '"]').prop('checked', true);
			});
		}
		
		// Shortcode neu generieren
		generateShortcode();
	}

	// Preset speichern
	$('#save-preset-btn').on('click', function() {
		var name = prompt('<?php esc_html_e( 'Name für das Preset:', 'repro-ct-suite' ); ?>');
		
		if (!name || name.trim() === '') {
			alert('<?php esc_html_e( 'Bitte geben Sie einen Namen ein.', 'repro-ct-suite' ); ?>');
			return;
		}

		var presetData = getFormData();
		presetData.name = name.trim();

		$.post(ajaxurl, {
			action: 'repro_ct_suite_save_preset',
			preset: presetData,
			nonce: '<?php echo wp_create_nonce( 'repro_ct_suite_presets' ); ?>'
		}, function(response) {
			if (response.success) {
				alert('<?php esc_html_e( 'Preset gespeichert!', 'repro-ct-suite' ); ?>');
				loadPresets();
			} else {
				alert('<?php esc_html_e( 'Fehler:', 'repro-ct-suite' ); ?> ' + response.data.message);
			}
		});
	});

	// Preset löschen
	$('#delete-preset-btn').on('click', function() {
		if (!currentPresetId) return;

		if (!confirm('<?php esc_html_e( 'Preset wirklich löschen?', 'repro-ct-suite' ); ?>')) {
			return;
		}

		$.post(ajaxurl, {
			action: 'repro_ct_suite_delete_preset',
			preset_id: currentPresetId,
			nonce: '<?php echo wp_create_nonce( 'repro_ct_suite_presets' ); ?>'
		}, function(response) {
			if (response.success) {
				alert('<?php esc_html_e( 'Preset gelöscht!', 'repro-ct-suite' ); ?>');
				$('#preset-select').val('');
				$('#load-preset-btn').prop('disabled', true);
				$('#delete-preset-btn').prop('disabled', true);
				currentPresetId = null;
				loadPresets();
			} else {
				alert('<?php esc_html_e( 'Fehler:', 'repro-ct-suite' ); ?> ' + response.data.message);
			}
		});
	});

	// Formular-Daten sammeln
	function getFormData() {
		var selectedCalendars = $('#calendar_ids').val() || [];
		var selectedFields = [];
		$('#show_fields input[type="checkbox"]:checked').each(function() {
			selectedFields.push($(this).val());
		});

		return {
			view: $('#view').val(),
			limit_count: parseInt($('#limit').val()) || 10,
			calendar_ids: selectedCalendars.join(','),
			from_days: parseInt($('#from_days').val()) || 0,
			to_days: parseInt($('#to_days').val()) || 90,
			show_past: $('#show_past').is(':checked') ? 1 : 0,
			order_dir: $('#order').val(),
			show_fields: selectedFields.join(',')
		};
	}

	// Presets initial laden
	loadPresets();
});
</script>
