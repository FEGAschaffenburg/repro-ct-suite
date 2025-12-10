$file = 'admin\class-repro-ct-suite-admin.php'
$lines = Get-Content $file -Encoding UTF8

# Insert error_log after function declaration
for ($i = 0; $i -lt $lines.Count; $i++) {
    if ($lines[$i] -match 'public function ajax_sync_calendars\(\)') {
        $newLines = @()
        $newLines += $lines[0..$i]
        $newLines += "`t`terror_log( '>>> REPRO CT SUITE: ajax_sync_calendars() START <<<' );"
        $newLines += ""
        $newLines += $lines[($i+1)..($lines.Count-1)]
        
        $newLines | Set-Content $file -Encoding UTF8
        Write-Host "Added START log" -ForegroundColor Green
        break
    }
}
