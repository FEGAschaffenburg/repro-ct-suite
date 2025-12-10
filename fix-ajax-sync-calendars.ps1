$file = "admin\class-repro-ct-suite-admin.php"
$content = [System.IO.File]::ReadAllText((Resolve-Path $file), [System.Text.UTF8Encoding]::new($false))

# Fehlerhafte Escape-Sequenzen entfernen
Write-Host "Bereinige Escape-Sequenzen..." -ForegroundColor Yellow
$content = $content -replace '`n\t\t', "`r`n`t`t"
$content = $content -replace '`n\t\t\t', "`r`n`t`t`t"  
$content = $content -replace '`n\t\t\t\t', "`r`n`t`t`t`t"

[System.IO.File]::WriteAllText((Resolve-Path $file), $content, [System.Text.UTF8Encoding]::new($false))
Write-Host "✓ Escape-Sequenzen bereinigt" -ForegroundColor Green

# Prüfen ob das funktioniert hat
$check = Get-Content $file -Raw
if ($check -match '`n') {
    Write-Host "⚠ Noch Escape-Sequenzen vorhanden!" -ForegroundColor Red
} else {
    Write-Host "✓ Datei sauber - keine Escape-Sequenzen mehr" -ForegroundColor Green
}
