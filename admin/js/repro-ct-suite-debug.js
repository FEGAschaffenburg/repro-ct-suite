/**
 * Debug-Seite JavaScript
 *
 * Behandelt AJAX-Interaktionen auf der Debug-Seite.
 *
 * @package Repro_CT_Suite
 * @version 0.3.4.0
 */

(function( $ ) {
	'use strict';

	const ReproCTSuiteDebug = {

		/**
		 * Initialisierung
		 */
		init: function() {
			console.log('Repro CT-Suite Debug loaded');
			
			this.initClearTableHandlers();
			this.initMigrationHandler();
			this.initLogHandlers();
		},

		/**
		 * Handler für Tabellen-Reset
		 */
		initClearTableHandlers: function() {
			const self = this;

			// Einzelne Tabelle leeren
			$('.repro-ct-suite-clear-single-table').on('click', function(e) {
				e.preventDefault();
				
				const $button = $(this);
				const table = $button.data('table');
				const tableName = $button.data('table-name');
				const label = $button.data('label');
				const nonce = $button.data('nonce');
				
				if (!confirm('WARNUNG: Alle Daten in der Tabelle "' + label + '" werden unwiderruflich gelöscht!\n\nMöchten Sie fortfahren?')) {
					return;
				}
				
				self.setButtonLoading($button, true);
				
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'repro_ct_suite_clear_single_table',
						table: table,
						nonce: nonce
					},
					success: function(response) {
						if (response.success) {
							self.showNotice(response.data.message, 'success');
							
							// Seite nach 2 Sekunden neu laden
							setTimeout(function() {
								location.reload();
							}, 2000);
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

			// Alle Tabellen leeren
			$('#repro-ct-suite-clear-all-tables').on('click', function(e) {
				e.preventDefault();
				
				const $button = $(this);
				const $result = $('#repro-ct-suite-clear-result');
				const nonce = $button.data('nonce');
				
				if (!confirm('WARNUNG: ALLE synchronisierten Daten werden unwiderruflich gelöscht!\n\nTabellen die geleert werden:\n- rcts_events\n- rcts_appointments\n- rcts_calendars\n- rcts_event_services\n\nMöchten Sie wirklich fortfahren?')) {
					return;
				}
				
				self.setButtonLoading($button, true);
				$result.hide();
				
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'repro_ct_suite_clear_tables',
						nonce: nonce
					},
					success: function(response) {
						if (response.success) {
							$result
								.removeClass('error')
								.addClass('success')
								.css('color', '#46b450')
								.html('<span class="dashicons dashicons-yes-alt"></span> ' + response.data.message)
								.fadeIn();
							
							self.showNotice(response.data.message, 'success');
							
							// Seite nach 3 Sekunden neu laden
							setTimeout(function() {
								location.reload();
							}, 3000);
						} else {
							$result
								.removeClass('success')
								.addClass('error')
								.css('color', '#d63638')
								.html('<span class="dashicons dashicons-dismiss"></span> ' + response.data.message)
								.fadeIn();
							
							self.showNotice(response.data.message, 'error');
							self.setButtonLoading($button, false);
						}
					},
					error: function(xhr, status, error) {
						$result
							.removeClass('success')
							.addClass('error')
							.css('color', '#d63638')
							.html('<span class="dashicons dashicons-dismiss"></span> Verbindungsfehler: ' + error)
							.fadeIn();
						
						self.showNotice('Verbindungsfehler: ' + error, 'error');
						self.setButtonLoading($button, false);
					}
				});
			});
		},

		/**
		 * Handler für DB-Migrationen
		 */
		initMigrationHandler: function() {
			const self = this;

			$('#repro-ct-suite-run-migrations').on('click', function(e) {
				e.preventDefault();
				
				const $button = $(this);
				const $result = $('#repro-ct-suite-migration-result');
				const nonce = $button.data('nonce');
				
				if (!confirm('Datenbank-Migrationen jetzt ausführen?\n\nDies kann Änderungen an der Datenbank-Struktur vornehmen.')) {
					return;
				}
				
				self.setButtonLoading($button, true);
				$result.hide();
				
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'repro_ct_suite_run_migrations',
						nonce: nonce
					},
					success: function(response) {
						if (response.success) {
							$result
								.removeClass('error')
								.addClass('success')
								.css('color', '#46b450')
								.html('<span class="dashicons dashicons-yes-alt"></span> ' + response.data.message)
								.fadeIn();
							
							self.showNotice(response.data.message, 'success');
							
							// Seite nach 2 Sekunden neu laden um neue DB-Version anzuzeigen
							setTimeout(function() {
								location.reload();
							}, 2000);
						} else {
							$result
								.removeClass('success')
								.addClass('error')
								.css('color', '#d63638')
								.html('<span class="dashicons dashicons-dismiss"></span> ' + response.data.message)
								.fadeIn();
							
							self.showNotice(response.data.message, 'error');
							self.setButtonLoading($button, false);
						}
					},
					error: function(xhr, status, error) {
						let errorMessage = 'Migration-Fehler: ' + error;
						
						// Detaillierte Fehlerinfo sammeln
						if (xhr.responseText) {
							try {
								const response = JSON.parse(xhr.responseText);
								if (response.data && response.data.message) {
									errorMessage = 'Migration-Fehler: ' + response.data.message;
								} else if (response.message) {
									errorMessage = 'Migration-Fehler: ' + response.message;
								}
							} catch (e) {
								// Falls Response kein JSON ist
								errorMessage += ' (HTTP ' + xhr.status + ': ' + xhr.statusText + ')';
								if (xhr.responseText && xhr.responseText.length < 300) {
									errorMessage += ' - Response: ' + xhr.responseText.substring(0, 200);
								}
							}
						}
						
						console.error('Migration AJAX Error:', {
							status: xhr.status,
							statusText: xhr.statusText,
							responseText: xhr.responseText,
							error: error
						});
						
						$result
							.removeClass('success')
							.addClass('error')
							.css('color', '#d63638')
							.html('<span class="dashicons dashicons-dismiss"></span> ' + errorMessage)
							.fadeIn();
						
						self.showNotice(errorMessage, 'error');
						self.setButtonLoading($button, false);
					}
				});
			});
		},

		/**
		 * Handler für Log-Funktionen
		 */
		initLogHandlers: function() {
			const self = this;

			// Log aktualisieren
			$('#repro-ct-suite-refresh-log').on('click', function(e) {
				e.preventDefault();
				location.reload();
			});

			// Log leeren
			$('#repro-ct-suite-clear-log').on('click', function(e) {
				e.preventDefault();
				
				const $button = $(this);
				const nonce = $button.data('nonce');
				
				if (!confirm('Debug-Log wirklich leeren?')) {
					return;
				}
				
				self.setButtonLoading($button, true);
				
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'repro_ct_suite_clear_log',
						nonce: nonce
					},
					success: function(response) {
						if (response.success) {
							self.showNotice(response.data.message, 'success');
							
							// Log-Viewer leeren
							$('#repro-ct-suite-log-viewer').html('<div style="color: #888;">Log wurde geleert</div>');
							
							self.setButtonLoading($button, false);
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
		},

		/**
		 * Zeigt eine Notice-Nachricht an
		 */
		showNotice: function(message, type) {
			type = type || 'info';
			
			const icons = {
				success: 'yes-alt',
				error: 'dismiss',
				warning: 'warning',
				info: 'info'
			};
			
			const $notice = $('<div>')
				.addClass('repro-ct-suite-notice repro-ct-suite-notice-' + type)
				.html(
					'<span class="dashicons dashicons-' + icons[type] + '"></span>' +
					'<div>' + message + '</div>'
				)
				.hide()
				.prependTo('.repro-ct-suite-admin-wrapper')
				.slideDown(300);
			
			// Auto-Hide nach 5 Sekunden
			setTimeout(function() {
				$notice.slideUp(300, function() {
					$(this).remove();
				});
			}, 5000);
			
			// Schließen-Button
			$notice.on('click', function() {
				$(this).slideUp(300, function() {
					$(this).remove();
				});
			});
		},

		/**
		 * Setzt den Loading-State eines Buttons
		 */
		setButtonLoading: function($button, loading) {
			if (loading) {
				$button.data('original-text', $button.html());
				$button
					.prop('disabled', true)
					.html('<span class="repro-ct-suite-spinner"></span> Lädt...')
					.css('opacity', '0.7');
			} else {
				$button
					.prop('disabled', false)
					.html($button.data('original-text'))
					.css('opacity', '1');
			}
		}
	};

	/**
	 * Initialisierung beim Document Ready
	 */
	$(function() {
		// Nur auf der Debug-Seite ausführen
		if ($('.repro-ct-suite-admin-wrapper').length && window.location.href.indexOf('repro-ct-suite-debug') !== -1) {
			ReproCTSuiteDebug.init();
		}
	});

	window.ReproCTSuiteDebug = ReproCTSuiteDebug;

})( jQuery );
