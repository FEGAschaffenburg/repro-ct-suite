# Add debug_info array to ajax_sync_calendars method

$file = 'admin\class-repro-ct-suite-admin.php'
$content = Get-Content $file -Raw -Encoding UTF8

# 1. Add debug_info array after $sync_service instantiation
$search1 = '		$sync_service = new Repro_CT_Suite_Calendar_Sync_Service( $ct_client, $calendars_repo );

		// Log Header'

$replace1 = '		$sync_service = new Repro_CT_Suite_Calendar_Sync_Service( $ct_client, $calendars_repo );

		// Debug-Informationen sammeln
		$debug_info = array(
			''tenant'' => $tenant,
			''username'' => $username,
			''api_base_url'' => sprintf( ''https://%s.church.tools/api'', $tenant ),
			''endpoint'' => ''/calendars'',
			''full_url'' => sprintf( ''https://%s.church.tools/api/calendars'', $tenant ),
			''timestamp'' => current_time( ''mysql'' ),
			''php_version'' => PHP_VERSION,
			''wordpress_version'' => get_bloginfo( ''version'' )
		);

		// Log Header'

$content = $content -replace [regex]::Escape($search1), $replace1

# 2. Add API URL to log output
$search2 = '		Repro_CT_Suite_Logger::log( ''Tenant: '' . $tenant );
		Repro_CT_Suite_Logger::separator();'

$replace2 = '		Repro_CT_Suite_Logger::log( ''Tenant: '' . $tenant );
		Repro_CT_Suite_Logger::log( ''ChurchTools API URL: '' . $debug_info[''full_url''] );
		Repro_CT_Suite_Logger::separator();'

$content = $content -replace [regex]::Escape($search2), $replace2

# Save
Set-Content $file -Value $content -Encoding UTF8 -NoNewline

Write-Host "✓ Debug-Info hinzugefügt" -ForegroundColor Green
