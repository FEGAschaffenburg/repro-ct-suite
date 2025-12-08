/**
 * Debug-Seite JavaScript - Erweiterungen fÃ¼r EinzellÃ¶schung und Plugin-Reset
 *
 * @package Repro_CT_Suite
 * @version 0.9.5.3
 */

(function( $ ) {
	'use strict';

	// Warte bis ReproCTSuiteDebug verfÃ¼gbar ist
	$(function() {
		if (typeof window.ReproCTSuiteDebug === 'undefined') {
			console.error('ReproCTSuiteDebug nicht gefunden');
			return;
		}

		const Debug = window.ReproCTSuiteDebug;

		/**
		 * Handler fÃ¼r TabelleneintrÃ¤ge anzeigen
		 */
		Debug.initViewTableEntriesHandler = function() {
			const self = this;

			// Modal schlieÃŸen
			$(document).on('click', '.repro-ct-suite-modal-close, .repro-ct-suite-modal', function(e) {
				if (e.target === this) {
					$('#repro-ct-suite-table-entries-modal').hide();
				}
			});

			// TabelleneintrÃ¤ge anzeigen
			$('.repro-ct-suite-view-table-entries').on('click', function(e) {
				e.preventDefault();
				
				const $button = $(this);
				const table = $button.data('table');
				const label = $button.data('label');
				const nonce = $button.data('nonce');
				
				// Modal Ã¶ffnen
				$('#repro-ct-suite-modal-title').text(label + ' - EintrÃ¤ge');
				$('#repro-ct-suite-entries-loader').show();
				$('#repro-ct-suite-entries-content').hide();
				$('#repro-ct-suite-table-entries-modal').show();
				
				// Daten laden
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'repro_ct_suite_get_table_entries',
						table: table,
						nonce: nonce
					},
					success: function(response) {
						if (response.success) {
							self.renderTableEntries(response.data, table, nonce);
						} else {
							$('#repro-ct-suite-entries-loader').hide();
							$('#repro-ct-suite-entries-content').html('<p class="error">' + response.data.message + '</p>').show();
						}
					},
					error: function(xhr, status, error) {
						$('#repro-ct-suite-entries-loader').hide();
						$('#repro-ct-suite-entries-content').html('<p class="error">Verbindungsfehler: ' + error + '</p>').show();
					}
				});
			});
		};

		/**
		 * Rendert die TabelleneintrÃ¤ge
		 */
		Debug.renderTableEntries = function(data, table, nonce) {
			$('#repro-ct-suite-entries-loader').hide();
			
			if (!data.entries || data.entries.length === 0) {
				$('#repro-ct-suite-entries-content').html('<p>Keine EintrÃ¤ge gefunden.</p>').show();
				return;
			}

			let html = '<div style="overflow-x: auto;"><table class="widefat striped" style="margin-top: 10px;"><thead><tr>';
			
			// Header
			data.columns.forEach(function(col) {
				html += '<th>' + col + '</th>';
			});
			html += '<th style="width: 100px;">Aktionen</th></tr></thead><tbody>';
			
			// Zeilen
			data.entries.forEach(function(entry) {
				html += '<tr data-entry-id="' + entry.id + '">';
				data.columns.forEach(function(col) {
					let value = entry[col];
					if (value === null) value = '<em>null</em>';
					if (typeof value === 'string' && value.length > 50) {
						value = value.substring(0, 50) + '...';
					}
					html += '<td>' + value + '</td>';
				});
				html += '<td><button class="button button-small button-link-delete repro-ct-suite-delete-entry" data-entry-id="' + entry.id + '" data-table="' + table + '" data-nonce="' + nonce + '">LÃ¶schen</button></td>';
				html += '</tr>';
			});
			
			html += '</tbody></table></div>';
			html += '<p class="description" style="margin-top: 10px;">Angezeigt: ' + data.total + ' EintrÃ¤ge (max. 100)</p>';
			
			$('#repro-ct-suite-entries-content').html(html).show();
			
			// Event-Handler fÃ¼r LÃ¶schen-Buttons
			this.initDeleteEntryHandlers();
		};

		/**
		 * Handler fÃ¼r einzelne EintrÃ¤ge lÃ¶schen
		 */
		Debug.initDeleteEntryHandlers = function() {
			const self = this;
			
			$('.repro-ct-suite-delete-entry').off('click').on('click', function(e) {
				e.preventDefault();
				
				const $button = $(this);
				const entryId = $button.data('entry-id');
				const table = $button.data('table');
				const nonce = $button.data('nonce');
				
				if (!confirm('Eintrag #' + entryId + ' wirklich lÃ¶schen?')) {
					return;
				}
				
				$button.prop('disabled', true).text('LÃ¶sche...');
				
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'repro_ct_suite_delete_single_entry',
						table: table,
						entry_id: entryId,
						nonce: nonce
					},
					success: function(response) {
						if (response.success) {
							$button.closest('tr').fadeOut(300, function() {
								$(this).remove();
								
								// PrÃ¼fen ob Tabelle leer ist
								if ($('#repro-ct-suite-entries-content table tbody tr:visible').length === 0) {
									$('#repro-ct-suite-entries-content').html('<p>Alle EintrÃ¤ge gelÃ¶scht.</p>');
									
									// Nach 2 Sekunden Seite neu laden
									setTimeout(function() {
										location.reload();
									}, 2000);
								}
							});
							self.showNotice(response.data.message, 'success');
						} else {
							self.showNotice(response.data.message, 'error');
							$button.prop('disabled', false).text('LÃ¶schen');
						}
					},
					error: function(xhr, status, error) {
						self.showNotice('Verbindungsfehler: ' + error, 'error');
						$button.prop('disabled', false).text('LÃ¶schen');
					}
				});
			});
		};

		/**
		 * Handler fÃ¼r vollstÃ¤ndigen Plugin-Reset
		 */
		Debug.initFullResetHandler = function() {
			const self = this;
			
			$('#repro-ct-suite-full-reset').on('click', function(e) {
				e.preventDefault();
				
				const $button = $(this);
				const nonce = $button.data('nonce');
				
				if (!confirm('âš ï¸ ACHTUNG: Dies setzt das gesamte Plugin zurÃ¼ck!\n\nFolgende Daten werden gelÃ¶scht:\n- Alle Tabellendaten (Kalender, Events, Services, Schedule)\n- ChurchTools Zugangsdaten\n- Synchronisations-Zeitstempel\n- Alle Plugin-Einstellungen\n\nDieser Vorgang kann NICHT rÃ¼ckgÃ¤ngig gemacht werden!\n\nMÃ¶chten Sie wirklich fortfahren?')) {
					return;
				}
				
				// Zweite BestÃ¤tigung
				if (!confirm('ðŸš¨ Sind Sie ABSOLUT SICHER?\n\nDas Plugin wird komplett zurÃ¼ckgesetzt und Sie mÃ¼ssen alle Einstellungen neu vornehmen!')) {
					return;
				}
				
				self.setButtonLoading($button, true);
				
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'repro_ct_suite_full_reset',
						nonce: nonce
					},
					success: function(response) {
						if (response.success) {
							self.showNotice(response.data.message + ' Seite wird neu geladen...', 'success');
							
							// Seite nach 3 Sekunden neu laden (zur Hauptseite)
							setTimeout(function() {
								window.location.href = window.location.pathname + '?page=repro-ct-suite';
							}, 3000);
						} else {
							self.showNotice(response.data.message, 'error');
							self.setButtonLoading($button, false);
						}
					},
					error: function(xhr, status, error) {
						self.showNotice('Verbindungsfehler: ' + error, 'error');
						self.setButtonLoading($button, false);
					}
				});
			});
		};

		// Initialisiere neue Handler
		if ($('.repro-ct-suite-admin-wrapper').length && (window.location.href.indexOf('repro-ct-suite-debug') !== -1 || window.location.href.indexOf('tab=debug') !== -1)) {
			Debug.initViewTableEntriesHandler();
			Debug.initFullResetHandler();
		}
	});

})( jQuery );
