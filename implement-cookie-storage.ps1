# Cookie-Storage korrekt implementieren
$file = "includes\class-repro-ct-suite-ct-client.php"
$lines = Get-Content $file
$newLines = @()
$modified = @()

for ($i = 0; $i -lt $lines.Count; $i++) {
    $line = $lines[$i]
    $newLines += $line
    
    # 1. Nach COOKIE_OPTION_KEY die neuen Konstanten einfügen
    if ($line -match "const COOKIE_OPTION_KEY = 'repro_ct_suite_ct_cookies';" -and $lines[$i+1] -notmatch "COOKIE_EXPIRY_KEY") {
        $newLines += ""
        $newLines += "`t/**"
        $newLines += "`t * Option-Key für Cookie-Ablaufzeit"
        $newLines += "`t */"
        $newLines += "`tconst COOKIE_EXPIRY_KEY = 'repro_ct_suite_ct_cookie_expiry';"
        $newLines += ""
        $newLines += "`t/**"
        $newLines += "`t * Cookie-Gültigkeitsdauer in Sekunden (12 Stunden)"
        $newLines += "`t */"
        $newLines += "`tconst COOKIE_LIFETIME = 43200;"
        $newLines += ""
        $newLines += "`t/**"
        $newLines += "`t * Zeitpunkt der Cookie-Erstellung"
        $newLines += "`t *"
        $newLines += "`t * @var int"
        $newLines += "`t */"
        $newLines += "`tprivate `$cookie_created_at = 0;"
        $modified += "✓ Konstanten hinzugefügt"
    }
    
    # 2. In save_cookies() nach "private function save_cookies" die Zeit setzen
    if ($line -match 'private function save_cookies\(\): void \{') {
        $newLines += "`t`t`$this->cookie_created_at = time();"
        $modified += "✓ save_cookies(): cookie_created_at = time()"
    }
    
    # 3. In save_cookies() nach update_option COOKIE_OPTION_KEY auch EXPIRY_KEY speichern
    if ($line -match 'update_option\( self::COOKIE_OPTION_KEY, \$this->cookies, false \);' -and $lines[$i-1] -notmatch 'COOKIE_EXPIRY_KEY') {
        $newLines += "`t`tupdate_option( self::COOKIE_EXPIRY_KEY, `$this->cookie_created_at, false );"
        $modified += "✓ save_cookies(): EXPIRY_KEY speichern"
    }
    
    # 4. In load_cookies() vor der schließenden } die Ablaufzeit laden
    if ($line -match '^\s+\}$' -and $lines[$i-1] -match '\$this->cookies = \$cookies;' -and $lines[$i-2] -match 'is_array') {
        $newLines += "`t`t`$this->cookie_created_at = (int) get_option( self::COOKIE_EXPIRY_KEY, 0 );"
        $newLines += $line
        $modified += "✓ load_cookies(): cookie_created_at laden"
        continue
    }
    
    # 5. In clear_cookies() nach "$this->cookies = array();" auch created_at nullen
    if ($line -match '\$this->cookies = array\(\);' -and $lines[$i+1] -match 'delete_option\( self::COOKIE_OPTION_KEY' -and $lines[$i+1] -notmatch 'cookie_created_at') {
        $newLines += "`t`t`$this->cookie_created_at = 0;"
        $modified += "✓ clear_cookies(): cookie_created_at = 0"
    }
    
    # 6. In clear_cookies() nach delete_option COOKIE_OPTION_KEY auch EXPIRY_KEY löschen
    if ($line -match 'delete_option\( self::COOKIE_OPTION_KEY \);' -and $lines[$i+1] -notmatch 'COOKIE_EXPIRY_KEY') {
        $newLines += "`t`tdelete_option( self::COOKIE_EXPIRY_KEY );"
        $modified += "✓ clear_cookies(): EXPIRY_KEY löschen"
    }
}

# Speichern
$newLines | Set-Content $file -Encoding UTF8

Write-Host "`n=== Cookie-Storage implementiert ===" -ForegroundColor Green
$modified | ForEach-Object { Write-Host $_ -ForegroundColor Cyan }
Write-Host "`nDatei gespeichert: $file" -ForegroundColor Green
