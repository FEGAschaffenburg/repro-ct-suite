# Ersetze ajax_sync_calendars Methode mit korrektem UTF-8
$ErrorActionPreference = 'Stop'

$file = 'c:\privat\repro-ct-suite\admin\class-repro-ct-suite-admin.php'

# Lese Datei
$lines = [System.IO.File]::ReadAllLines($file, [System.Text.Encoding]::UTF8)

# Finde Start und Ende
$start = -1
$end = -1
for($i=0; $i -lt $lines.Length; $i++) {
    if($lines[$i] -match 'AJAX Handler: Kalender synchronisieren') {
        $start = $i - 2  # 2 Zeilen vor dem Kommentar
    }
    if($start -gt 0 -and $lines[$i] -match 'AJAX Handler: Termine synchronisieren') {
        $end = $i - 4  # 4 Zeilen vor dem nächsten Handler
        break
    }
}

Write-Host "Gefunden: Start=$start, End=$end" -ForegroundColor Cyan

if($start -lt 0 -or $end -lt 0) {
    Write-Host "Methode nicht gefunden!" -ForegroundColor Red
    exit 1
}

# Neue Methode (komplett neu mit korrektem UTF-8)
$newMethod = @'

	/**
	 * AJAX Handler: Kalender synchronisieren
	 *
	 * Ruft Kalender von ChurchTools ab und speichert sie in der Datenbank.
	 * Behält die Benutzer-Auswahl (is_selected) bei Updates bei.
	 *
	 * @since    0.3.0
	 */
	public function ajax_sync_calendars(): void {
		// Output Buffering starten um unerwünschte Ausgaben vor JSON zu verhindern
		ob_start();

		// Nonce-Prüfung
		check_ajax_referer( 'repro_ct_suite_admin', 'nonce' );

		// Berechtigungsprüfung
		if ( ! current_user_can( 'manage_options' ) ) {
			ob_end_clean();
			wp_send_json_error( array(
				'message' => __( 'Keine Berechtigung für diese Aktion.', 'repro-ct-suite' )
			) );
			return;
		}

		try {
			// Dependencies laden
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-repro-ct-suite-logger.php';
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-repro-ct-suite-crypto.php';
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-repro-ct-suite-ct-client.php';
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/repositories/class-repro-ct-suite-repository-base.php';
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/repositories/class-repro-ct-suite-calendars-repository.php';
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/services/class-repro-ct-suite-calendar-sync-service.php';
		} catch ( Exception $e ) {
			ob_end_clean();
			wp_send_json_error( array(
				'message' => 'Fehler beim Laden: ' . $e->getMessage()
			) );
			return;
		} catch ( Error $e ) {
			ob_end_clean();
			wp_send_json_error( array(
				'message' => 'PHP Error: ' . $e->getMessage()
			) );
			return;
		}

		try {
			// Credentials aus WordPress Optionen laden
			$tenant = get_option( 'repro_ct_suite_ct_tenant', '' );
			$username = get_option( 'repro_ct_suite_ct_username', '' );
			$encrypted_password = get_option( 'repro_ct_suite_ct_password', '' );
			$password = Repro_CT_Suite_Crypto::decrypt( $encrypted_password );
			
			// Debug-Info initialisieren
			$api_base_url = 'https://' . $tenant . '.church.tools/api';
			$debug_info = array(
				'tenant' => $tenant,
				'api_base_url' => $api_base_url,
				'endpoint' => '/calendars',
				'full_url' => $api_base_url . '/calendars',
				'username' => $username,
				'has_password' => !empty($password),
				'cookies_before' => count(get_option('repro_ct_suite_ct_cookies', array()))
			);

			Repro_CT_Suite_Logger::log( 'API Request URL: ' . $debug_info['full_url'] );
			Repro_CT_Suite_Logger::log( 'Username: ' . $username );
			Repro_CT_Suite_Logger::log( 'Gespeicherte Cookies: ' . $debug_info['cookies_before'] );

			// CT_Client mit Credentials instanziieren
			$ct_client = new Repro_CT_Suite_CT_Client( $tenant, $username, $password );
			$calendars_repo = new Repro_CT_Suite_Calendars_Repository();
			$sync_service = new Repro_CT_Suite_Calendar_Sync_Service( $ct_client, $calendars_repo );

			// Log Header
			Repro_CT_Suite_Logger::header( 'KALENDER-SYNCHRONISATION GESTARTET' );
			Repro_CT_Suite_Logger::log( 'Zeitpunkt: ' . current_time( 'mysql' ) );
			Repro_CT_Suite_Logger::log( 'Tenant: ' . $tenant );
			Repro_CT_Suite_Logger::separator();

			// Synchronisation durchführen
			$result = $sync_service->sync_calendars();

			// DEBUG: Log Response
			if ( is_wp_error( $result ) ) {
				Repro_CT_Suite_Logger::log( 'WP_Error aufgetreten!', 'error' );
				Repro_CT_Suite_Logger::log( 'Error Code: ' . $result->get_error_code(), 'error' );
				Repro_CT_Suite_Logger::log( 'Error Message: ' . $result->get_error_message(), 'error' );
				$error_data = $result->get_error_data();
				if ( !empty($error_data) ) {
					Repro_CT_Suite_Logger::dump( $error_data, 'Error Data' );
				}
				Repro_CT_Suite_Logger::separator( '=', 60 );
				ob_end_clean();
				wp_send_json_error( array(
					'message' => $result->get_error_message(),
					'debug' => array_merge(
						$debug_info,
						array(
							'error_code' => $result->get_error_code(),
							'error_message' => $result->get_error_message(),
							'error_data' => $error_data
						)
					)
				) );
				return;
			}

			Repro_CT_Suite_Logger::log( 'Response erhalten - Typ: ' . gettype( $result ) );
			Repro_CT_Suite_Logger::log( 'Kalender gesamt: ' . ( isset( $result['total'] ) ? $result['total'] : '0' ), 'info' );
			Repro_CT_Suite_Logger::log( 'Neu eingefügt: ' . ( isset( $result['inserted'] ) ? $result['inserted'] : '0' ), 'success' );
			Repro_CT_Suite_Logger::log( 'Aktualisiert: ' . ( isset( $result['updated'] ) ? $result['updated'] : '0' ), 'success' );
			Repro_CT_Suite_Logger::log( 'Fehler: ' . ( isset( $result['errors'] ) ? $result['errors'] : '0' ), ( isset( $result['errors'] ) && $result['errors'] > 0 ? 'warning' : 'success' ) );

			if ( isset( $result['errors'] ) && ! empty( $result['errors'] ) ) {
				Repro_CT_Suite_Logger::dump( $result['errors'], 'Fehler-Details', 'warning' );
				Repro_CT_Suite_Logger::header( 'SYNC MIT FEHLERN BEENDET', 'warning' );
				ob_end_clean();
				wp_send_json_error( array(
					'message' => sprintf(
						__( 'Synchronisation mit Fehlern abgeschlossen. %d Kalender importiert, %d Fehler aufgetreten.', 'repro-ct-suite' ),
						$result['inserted'] + $result['updated'],
						count( $result['errors'] )
					),
					'stats' => $result,
					'debug' => $debug_info
				) );
				return;
			}

			Repro_CT_Suite_Logger::header( 'KALENDER-SYNCHRONISATION ERFOLGREICH', 'success' );
			ob_end_clean();
			wp_send_json_success( array(
				'message' => sprintf(
					__( 'Erfolgreich %d Kalender synchronisiert (%d neu, %d aktualisiert).', 'repro-ct-suite' ),
					$result['total'],
					$result['inserted'],
					$result['updated']
				),
				'stats' => $result,
				'debug' => $debug_info
			) );

		} catch ( Exception $e ) {
			error_log( '[REPRO CT-SUITE] EXCEPTION: ' . $e->getMessage() );
			error_log( '[REPRO CT-SUITE] File: ' . $e->getFile() . ' Line: ' . $e->getLine() );
			error_log( '[REPRO CT-SUITE] Trace: ' . $e->getTraceAsString() );
			
			Repro_CT_Suite_Logger::header( 'EXCEPTION AUFGETRETEN', 'error' );
			Repro_CT_Suite_Logger::log( 'Exception: ' . $e->getMessage(), 'error' );
			Repro_CT_Suite_Logger::log( 'File: ' . $e->getFile() . ' (Line ' . $e->getLine() . ')', 'error' );
			Repro_CT_Suite_Logger::log( 'Stack Trace:', 'error' );
			$trace_lines = explode( "\n", $e->getTraceAsString() );
			foreach ( array_slice( $trace_lines, 0, 10 ) as $line ) {
				Repro_CT_Suite_Logger::log( '  ' . $line, 'error' );
			}
			Repro_CT_Suite_Logger::header( 'SYNC FAILED', 'error' );
			ob_end_clean();
			wp_send_json_error( array(
				'message' => sprintf(
					__( 'Fehler bei der Synchronisation: %s', 'repro-ct-suite' ),
					$e->getMessage()
				),
				'debug' => array(
					'error' => $e->getMessage(),
					'file' => $e->getFile(),
					'line' => $e->getLine(),
					'trace' => $e->getTraceAsString()
				)
			) );
			return;
		} catch ( Error $e ) {
			error_log( '[REPRO CT-SUITE] PHP ERROR: ' . $e->getMessage() );
			error_log( '[REPRO CT-SUITE] File: ' . $e->getFile() . ' Line: ' . $e->getLine() );
			error_log( '[REPRO CT-SUITE] Trace: ' . $e->getTraceAsString() );
			ob_end_clean();
			wp_send_json_error( array(
				'message' => 'PHP Error: ' . $e->getMessage(),
				'debug' => array(
					'error' => $e->getMessage(),
					'file' => $e->getFile(),
					'line' => $e->getLine(),
					'trace' => $e->getTraceAsString()
				)
			) );
			return;
		}
	}

'@

# Erstelle neue Datei-Inhalte
$newLines = @()
$newLines += $lines[0..($start-1)]  # Alles vor der Methode
$newLines += $newMethod -split "`n"  # Neue Methode
$newLines += $lines[($end+1)..($lines.Length-1)]  # Alles nach der Methode

# Speichere mit UTF-8 ohne BOM
$utf8NoBom = New-Object System.Text.UTF8Encoding $false
[System.IO.File]::WriteAllLines($file, $newLines, $utf8NoBom)

Write-Host "✓ Methode ajax_sync_calendars() ersetzt" -ForegroundColor Green
Write-Host "✓ UTF-8 ohne BOM" -ForegroundColor Green
Write-Host "✓ Alle Umlaute korrigiert" -ForegroundColor Green
Write-Host "✓ return-Statements hinzugefügt" -ForegroundColor Green
