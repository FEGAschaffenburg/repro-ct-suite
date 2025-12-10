# Fix encoding in all PHP files
$utf8NoBom = New-Object System.Text.UTF8Encoding $false
$files = Get-ChildItem -Path . -Include *.php -Recurse | Where-Object { 
    $_.FullName -notmatch 'vendor' -and $_.FullName -notmatch 'node_modules'
}

$fixedCount = 0

foreach ($file in $files) {
    $content = Get-Content $file.FullName -Raw -Encoding UTF8
    $originalContent = $content
    
    # Fix corrupted German umlauts and special characters
    $content = $content -creplace 'LÃƒÆ[^L]+?dt', 'Lädt'
    $content = $content -creplace 'lÃƒÆ[^l]+?dt', 'lädt'
    $content = $content -creplace 'LÃƒÆ[^L]+?uft', 'Läuft'
    $content = $content -creplace 'lÃƒÆ[^l]+?uft', 'läuft'
    $content = $content -creplace 'LÃƒÆ[^L]+?schen', 'Löschen'
    $content = $content -creplace 'lÃƒÆ[^l]+?schen', 'löschen'
    $content = $content -creplace 'gelÃƒÆ[^g]+?scht', 'gelöscht'
    $content = $content -creplace 'verfÃƒÆ[^v]+?gbar', 'verfügbar'
    $content = $content -creplace 'verschlÃƒÆ[^v]+?sselt', 'verschlüsselt'
    $content = $content -creplace 'LizenzschlÃƒÆ[^L]+?ssel', 'Lizenzschlüssel'
    $content = $content -creplace 'VollstÃƒÆ[^V]+?ndig', 'Vollständig'
    $content = $content -creplace 'durchgefÃƒÆ[^d]+?hrt', 'durchgeführt'
    $content = $content -creplace 'EintrÃƒÆ[^E]+?ge', 'Einträge'
    $content = $content -creplace 'OberflÃƒÆ[^O]+?che', 'Oberfläche'
    $content = $content -creplace 'fÃƒÆ[^f]+?r\s', 'für '
    $content = $content -creplace 'PrÃƒÆ[^P]+?fung', 'Prüfung'
    $content = $content -creplace 'erhÃƒÆ[^e]+?hen', 'erhöhen'
    $content = $content -creplace 'ÃƒÆ[^Ã]+?nderung', 'Änderung'
    $content = $content -creplace 'ablÃƒÆ[^a]+?uft', 'abläuft'
    
    if ($content -ne $originalContent) {
        [System.IO.File]::WriteAllText($file.FullName, $content, $utf8NoBom)
        $fixedCount++
        Write-Host "Fixed: $($file.Name)" -ForegroundColor Green
    }
}

Write-Host "`nTotal behoben: $fixedCount Dateien" -ForegroundColor Cyan
