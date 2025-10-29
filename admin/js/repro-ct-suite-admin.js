/**
 * Admin-spezifisches JavaScript für das Plugin.
 *
 * Behandelt Tab-Navigation, Formular-Validierung und AJAX-Interaktionen.
 * Nutzt jQuery für Kompatibilität mit WordPress.
 *
 * @package Repro_CT_Suite
 * @version 1.0.0
 */

(function( $ ) {
	'use strict';

	/**
	 * Haupt-Objekt für alle Admin-Funktionen
	 * 
	 * Organisiert alle JavaScript-Funktionalitäten in einem Namespace
	 * um Konflikte mit anderen Plugins zu vermeiden.
	 */
	const ReproCTSuiteAdmin = {

		/**
		 * Initialisierung beim Document Ready
		 * 
		 * Wird aufgerufen, sobald das DOM vollständig geladen ist.
		 * Registriert alle Event-Handler und initialisiert Komponenten.
		 */
		init: function() {
			console.log('Repro CT-Suite Admin loaded');
			
			this.initTabs();
			this.initToggles();
			this.initTooltips();
			this.initFormValidation();
			this.initAjaxActions();
		},

		/**
		 * Tab-Navigation initialisieren
		 * 
		 * Ermöglicht das Wechseln zwischen verschiedenen Tab-Bereichen.
		 * Speichert den aktiven Tab in der URL für direkte Links.
		 * 
		 * @since 1.0.0
		 */
		initTabs: function() {
			// Tab-Click-Handler
			$('.repro-ct-suite-tabs-nav a').on('click', function(e) {
				e.preventDefault();
				
				const $tab = $(this);
				const target = $tab.attr('href');
				
				// Alle Tabs und Inhalte deaktivieren
				$('.repro-ct-suite-tabs-nav a').removeClass('active');
				$('.repro-ct-suite-tab-content').removeClass('active');
				
				// Aktuellen Tab aktivieren
				$tab.addClass('active');
				$(target).addClass('active');
				
				// Tab in URL speichern (ohne Seitenreload)
				if (history.pushState) {
					const url = new URL(window.location);
					url.searchParams.set('tab', target.replace('#', ''));
					history.pushState({}, '', url);
				}
			});
			
			// Tab aus URL laden (beim Seitenaufruf)
			const urlParams = new URLSearchParams(window.location.search);
			const activeTab = urlParams.get('tab');
			
			if (activeTab) {
				const $tabLink = $('.repro-ct-suite-tabs-nav a[href="#' + activeTab + '"]');
				if ($tabLink.length) {
					$tabLink.trigger('click');
				}
			}
		},

		/**
		 * Toggle-Switches initialisieren
		 * 
		 * Macht Checkbox-Felder zu modernen Toggle-Switches.
		 * Ermöglicht bessere UX für Ja/Nein-Optionen.
		 * 
		 * @since 1.0.0
		 */
		initToggles: function() {
			// Change-Event für Toggles
			$('.repro-ct-suite-toggle input[type="checkbox"]').on('change', function() {
				const $toggle = $(this);
				const isChecked = $toggle.is(':checked');
				
				// Optionaler Callback bei Änderung
				if ($toggle.data('callback')) {
					const callback = $toggle.data('callback');
					if (typeof window[callback] === 'function') {
						window[callback](isChecked, $toggle);
					}
				}
				
				// Visuelles Feedback
				$toggle.closest('.repro-ct-suite-toggle')
					.toggleClass('active', isChecked);
			});
		},

		/**
		 * Tooltips initialisieren
		 * 
		 * Zeigt hilfreiche Tooltips für Elemente mit data-tooltip Attribut.
		 * Nutzt native WordPress-Funktionalität wenn verfügbar.
		 * 
		 * @since 1.0.0
		 */
		initTooltips: function() {
			// Einfache Tooltip-Implementierung
			$('[data-tooltip]').each(function() {
				const $element = $(this);
				const tooltipText = $element.data('tooltip');
				
				// Title-Attribut für native Browser-Tooltips
				$element.attr('title', tooltipText);
			});
			
			// Für bessere Tooltips kann hier eine Bibliothek wie Tippy.js integriert werden
		},

		/**
		 * Formular-Validierung
		 * 
		 * Validiert Formulareingaben vor dem Absenden.
		 * Zeigt benutzerfreundliche Fehlermeldungen an.
		 * 
		 * @since 1.0.0
		 */
		initFormValidation: function() {
			$('.repro-ct-suite-form').on('submit', function(e) {
				const $form = $(this);
				let isValid = true;
				
				// Entferne alte Fehlermeldungen
				$form.find('.repro-ct-suite-field-error').remove();
				$form.find('.error').removeClass('error');
				
				// Validiere erforderliche Felder
				$form.find('[required]').each(function() {
					const $field = $(this);
					const value = $field.val().trim();
					
					if (!value) {
						isValid = false;
						ReproCTSuiteAdmin.showFieldError(
							$field, 
							'Dieses Feld ist erforderlich.'
						);
					}
				});
				
				// Validiere URL-Felder
				$form.find('input[type="url"]').each(function() {
					const $field = $(this);
					const value = $field.val().trim();
					
					if (value && !ReproCTSuiteAdmin.isValidUrl(value)) {
						isValid = false;
						ReproCTSuiteAdmin.showFieldError(
							$field, 
							'Bitte geben Sie eine gültige URL ein.'
						);
					}
				});
				
				// Verhindere Absenden bei Fehlern
				if (!isValid) {
					e.preventDefault();
					
					// Scrolle zum ersten Fehler
					const $firstError = $form.find('.error').first();
					if ($firstError.length) {
						$('html, body').animate({
							scrollTop: $firstError.offset().top - 100
						}, 300);
					}
					
					// Zeige globale Fehlermeldung
					ReproCTSuiteAdmin.showNotice(
						'Bitte korrigieren Sie die markierten Felder.',
						'error'
					);
				}
				
				return isValid;
			});
		},

		/**
		 * AJAX-Aktionen initialisieren
		 * 
		 * Registriert Event-Handler für AJAX-Buttons.
		 * Zeigt Loading-State und verarbeitet Responses.
		 * 
		 * @since 1.0.0
		 */
		initAjaxActions: function() {
			// Allgemeiner Sync-Button (mit data-action)
			$('.repro-ct-suite-sync-btn').on('click', function(e) {
				e.preventDefault();
				
				const $button = $(this);
				const action = $button.data('action');
				
				if (!action) return;
				
				// Loading-State aktivieren
				ReproCTSuiteAdmin.setButtonLoading($button, true);
				
				// AJAX-Request
				$.ajax({
					url: ajaxurl, // WordPress global
					type: 'POST',
					data: {
						action: action,
						nonce: $button.data('nonce')
					},
					success: function(response) {
						if (response.success) {
							ReproCTSuiteAdmin.showNotice(
								response.data.message || 'Erfolgreich synchronisiert!',
								'success'
							);
							
							// Optional: Seite neu laden nach Erfolg
							if ($button.data('reload')) {
								setTimeout(function() {
									location.reload();
								}, 1500);
							}
						} else {
							ReproCTSuiteAdmin.showNotice(
								response.data.message || 'Ein Fehler ist aufgetreten.',
								'error'
							);
						}
					},
					error: function(xhr, status, error) {
						ReproCTSuiteAdmin.showNotice(
							'Verbindungsfehler: ' + error,
							'error'
						);
					},
					complete: function() {
						// Loading-State deaktivieren
						ReproCTSuiteAdmin.setButtonLoading($button, false);
					}
				});
			});

			// Kalender synchronisieren (spezieller Handler)
			$('.repro-ct-suite-sync-calendars-btn').on('click', function(e) {
				e.preventDefault();
				
				const $button = $(this);
				
				// Bestätigungsdialog
				if (!confirm('Möchten Sie die Kalender jetzt von ChurchTools laden?')) {
					return;
				}
				
				console.log('[DEBUG] Kalender-Synchronisation gestartet...');
				
				// Loading-State aktivieren
				ReproCTSuiteAdmin.setButtonLoading($button, true);
				
				// AJAX-Request
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'repro_ct_suite_sync_calendars',
						nonce: reproCTSuiteAdmin.nonce
					},
					success: function(response) {
						console.log('[DEBUG] AJAX Response:', response);
						
						if (response.success) {
							console.log('[DEBUG] Erfolgreiche Synchronisation:');
							console.log('- Statistik:', response.data.stats);
							console.log('- Debug-Info:', response.data.debug);
							
							// Debug-Informationen anzeigen
							let debugMessage = response.data.message;
							if (response.data.debug) {
								debugMessage += '\n\n[DEBUG]\n';
								debugMessage += 'URL: ' + response.data.debug.url + '\n';
								debugMessage += 'Tenant: ' + response.data.debug.tenant + '\n';
								debugMessage += 'Zeitstempel: ' + response.data.debug.timestamp;
							}
							
							ReproCTSuiteAdmin.showNotice(
								debugMessage.replace(/\n/g, '<br>'),
								'success'
							);
							
							// Seite neu laden um aktualisierte Kalender anzuzeigen
							setTimeout(function() {
								location.reload();
							}, 3000);
						} else {
							console.error('[DEBUG] Fehler bei der Synchronisation:');
							console.error('- Nachricht:', response.data.message);
							console.error('- Debug-Info:', response.data.debug);
							console.error('- Vollständige Response:', response);
							
							// Detaillierte Fehlermeldung
							let errorMessage = response.data.message || 'Fehler bei der Synchronisation.';
							if (response.data.debug) {
								errorMessage += '<br><br><strong>Debug-Informationen:</strong><br>';
								if (response.data.debug.url) {
									errorMessage += 'URL: ' + response.data.debug.url + '<br>';
								}
								if (response.data.debug.error) {
									errorMessage += 'Fehler: ' + response.data.debug.error + '<br>';
								}
							}
							
							ReproCTSuiteAdmin.showNotice(
								errorMessage,
								'error'
							);
						}
					},
					error: function(xhr, status, error) {
						console.error('[DEBUG] AJAX-Fehler:');
						console.error('- Status:', status);
						console.error('- Fehler:', error);
						console.error('- Response Text:', xhr.responseText);
						console.error('- Status Code:', xhr.status);
						console.error('- Vollständiges XHR:', xhr);
						
						ReproCTSuiteAdmin.showNotice(
							'Verbindungsfehler: ' + error + '<br>' +
							'Status: ' + xhr.status + '<br>' +
							'Bitte prüfen Sie die Browser-Konsole (F12) für weitere Details.',
							'error'
						);
					},
					complete: function() {
						console.log('[DEBUG] AJAX-Request abgeschlossen');
						ReproCTSuiteAdmin.setButtonLoading($button, false);
					}
				});
			});

			// Termine synchronisieren (Dashboard)
			$('.repro-ct-suite-sync-appointments-btn').on('click', function(e) {
				e.preventDefault();
				
				const $button = $(this);
				
				// Loading-State aktivieren
				ReproCTSuiteAdmin.setButtonLoading($button, true);
				
				// AJAX-Request
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'repro_ct_suite_sync_appointments',
						nonce: reproCTSuiteAdmin.nonce
					},
					success: function(response) {
						if (response.success) {
							ReproCTSuiteAdmin.showNotice(
								response.data.message,
								'success'
							);
							
							// Seite neu laden um aktualisierte Termine anzuzeigen
							setTimeout(function() {
								location.reload();
							}, 1500);
						} else {
							ReproCTSuiteAdmin.showNotice(
								response.data.message || 'Fehler bei der Synchronisation.',
								'error'
							);
						}
					},
					error: function(xhr, status, error) {
						console.error('AJAX Error:', xhr.responseText);
						ReproCTSuiteAdmin.showNotice(
							'Verbindungsfehler: ' + error,
							'error'
						);
					},
					complete: function() {
						ReproCTSuiteAdmin.setButtonLoading($button, false);
					}
				});
			});
		},

		/**
		 * Zeigt eine Fehlermeldung bei einem Formularfeld
		 * 
		 * @param {jQuery} $field - Das Formularfeld
		 * @param {string} message - Die Fehlermeldung
		 * @since 1.0.0
		 */
		showFieldError: function($field, message) {
			$field.addClass('error');
			
			const $error = $('<span class="repro-ct-suite-field-error">')
				.text(message)
				.css({
					color: '#d63638',
					fontSize: '13px',
					display: 'block',
					marginTop: '5px'
				});
			
			$field.after($error);
		},

		/**
		 * Prüft ob eine URL valide ist
		 * 
		 * @param {string} url - Die zu prüfende URL
		 * @return {boolean} True wenn valide, sonst false
		 * @since 1.0.0
		 */
		isValidUrl: function(url) {
			try {
				const urlObj = new URL(url);
				return urlObj.protocol === 'http:' || urlObj.protocol === 'https:';
			} catch (e) {
				return false;
			}
		},

		/**
		 * Zeigt eine Notice-Nachricht an
		 * 
		 * @param {string} message - Die Nachricht
		 * @param {string} type - Typ: success, error, warning, info
		 * @since 1.0.0
		 */
		showNotice: function(message, type) {
			type = type || 'info';
			
			// Icon basierend auf Typ
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
		 * 
		 * @param {jQuery} $button - Der Button
		 * @param {boolean} loading - True für Loading, false für Normal
		 * @since 1.0.0
		 */
		setButtonLoading: function($button, loading) {
			if (loading) {
				// Original-Text speichern
				$button.data('original-text', $button.html());
				
				// Loading-State
				$button
					.prop('disabled', true)
					.html('<span class="repro-ct-suite-spinner"></span> Lädt...')
					.css('opacity', '0.7');
			} else {
				// Original-State wiederherstellen
				$button
					.prop('disabled', false)
					.html($button.data('original-text'))
					.css('opacity', '1');
			}
		},

		/**
		 * Progress Bar aktualisieren
		 * 
		 * @param {jQuery} $progressBar - Die Progress Bar
		 * @param {number} percent - Prozent (0-100)
		 * @since 1.0.0
		 */
		updateProgress: function($progressBar, percent) {
			percent = Math.min(100, Math.max(0, percent));
			$progressBar.css('width', percent + '%');
			
			// Optional: Text anzeigen
			const $text = $progressBar.siblings('.progress-text');
			if ($text.length) {
				$text.text(Math.round(percent) + '%');
			}
		}
	};

	/**
	 * Initialisierung beim Document Ready
	 */
	$(function() {
		ReproCTSuiteAdmin.init();
	});

	/**
	 * Globaler Zugriff für externe Aufrufe
	 */
	window.ReproCTSuiteAdmin = ReproCTSuiteAdmin;

})( jQuery );
