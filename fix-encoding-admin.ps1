# Encoding Fix für admin class
$ErrorActionPreference = 'Stop'

$file = Join-Path $PSScriptRoot 'admin\class-repro-ct-suite-admin.php'
Write-Host "Verarbeite: $file" -ForegroundColor Cyan

# Lese Datei als Byte-Array um Encoding-Probleme zu vermeiden
$bytes = [System.IO.File]::ReadAllBytes($file)
$content = [System.Text.Encoding]::UTF8.GetString($bytes)

# Ersetze korrupte Zeichen
$replacements = @{
    'unerwÃƒÆ''Ã‚Â¤Ãƒâ€šÃ‚Â¼nschte' = 'unerwünschte'
    'PrÃƒÆ''Ã‚Â¤Ãƒâ€šÃ‚Â¼fung' = 'Prüfung'
    'BerechtigungsPrÃƒÆ''Ã‚Â¤Ãƒâ€šÃ‚Â¼fung' = 'Berechtigungsprüfung'
    'fÃƒÆ''Ã‚Â¤Ãƒâ€šÃ‚Â¼r' = 'für'
    'Keine Berechtigung fÃƒÆ''Ã‚Â¤Ãƒâ€šÃ‚Â¼r diese Aktion.' = 'Keine Berechtigung für diese Aktion.'
}

$changed = $false
foreach ($pattern in $replacements.Keys) {
    if ($content -match [regex]::Escape($pattern)) {
        $content = $content -replace [regex]::Escape($pattern), $replacements[$pattern]
        Write-Host "  ✓ Ersetzt: $pattern" -ForegroundColor Green
        $changed = $true
    }
}

if ($changed) {
    # Schreibe mit UTF-8 ohne BOM
    $utf8NoBom = New-Object System.Text.UTF8Encoding $false
    [System.IO.File]::WriteAllText($file, $content, $utf8NoBom)
    Write-Host "Datei gespeichert mit UTF-8 ohne BOM" -ForegroundColor Green
} else {
    Write-Host "Keine Änderungen nötig" -ForegroundColor Yellow
}
