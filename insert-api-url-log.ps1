$file = 'admin\class-repro-ct-suite-admin.php'
$lines = Get-Content $file -Encoding UTF8

# Find line with 'Tenant: '
for ($i = 0; $i -lt $lines.Count; $i++) {
    if ($lines[$i] -match "Repro_CT_Suite_Logger::log\( 'Tenant: ' \. \`$tenant \);") {
        Write-Host "Found Tenant log at line $($i+1)"
        # Insert API URL line after
        $newLines = @()
        $newLines += $lines[0..$i]
        $newLines += "`t`tRepro_CT_Suite_Logger::log( 'ChurchTools API URL: ' . `$debug_info['full_url'] );"
        $newLines += $lines[($i+1)..($lines.Count-1)]
        
        $newLines | Set-Content $file -Encoding UTF8
        Write-Host "Done! Added API URL log" -ForegroundColor Green
        break
    }
}
