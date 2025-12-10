$file = 'admin\class-repro-ct-suite-admin.php'
$lines = Get-Content $file -Encoding UTF8

# Find line with $sync_service
for ($i = 0; $i -lt $lines.Count; $i++) {
    if ($lines[$i] -match '\$sync_service = new Repro_CT_Suite_Calendar_Sync_Service') {
        Write-Host "Found at line $($i+1)"
        # Insert after this line (after blank lines)
        $insertAt = $i + 1
        while ($insertAt -lt $lines.Count -and $lines[$insertAt] -match '^\s*$') {
            $insertAt++
        }
        Write-Host "Will insert at line $($insertAt+1) before: $($lines[$insertAt])"
        
        # Build new lines
        $newLines = @()
        $newLines += $lines[0..($insertAt-1)]
        $newLines += ""
        $newLines += "`t`t// Debug-Informationen sammeln"
        $newLines += "`t`t`$debug_info = array("
        $newLines += "`t`t`t'tenant' => `$tenant,"
        $newLines += "`t`t`t'username' => `$username,"
        $newLines += "`t`t`t'api_base_url' => sprintf( 'https://%s.church.tools/api', `$tenant ),"
        $newLines += "`t`t`t'endpoint' => '/calendars',"
        $newLines += "`t`t`t'full_url' => sprintf( 'https://%s.church.tools/api/calendars', `$tenant ),"
        $newLines += "`t`t`t'timestamp' => current_time( 'mysql' ),"
        $newLines += "`t`t`t'php_version' => PHP_VERSION,"
        $newLines += "`t`t`t'wordpress_version' => get_bloginfo( 'version' )"
        $newLines += "`t`t);"
        $newLines += $lines[$insertAt..($lines.Count-1)]
        
        $newLines | Set-Content $file -Encoding UTF8
        Write-Host "Done! Inserted debug_info array" -ForegroundColor Green
        break
    }
}
