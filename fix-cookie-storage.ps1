$file = "includes\class-repro-ct-suite-ct-client.php"
$content = Get-Content $file -Raw

Write-Host "=== Cookie-Speicherung aktualisieren ===" -ForegroundColor Cyan

# 1. save_cookies() erweitern
$old1 = @"
	private function save_cookies(): void {

		update_option( self::COOKIE_OPTION_KEY, `$this->cookies, false );

	}
"@

$new1 = @"
	private function save_cookies(): void {

		`$this->cookie_created_at = time();

		update_option( self::COOKIE_OPTION_KEY, `$this->cookies, false );

		update_option( self::COOKIE_EXPIRY_KEY, `$this->cookie_created_at, false );

	}
"@

if ($content.Contains($old1)) {
    $content = $content.Replace($old1, $new1)
    Write-Host "✓ save_cookies() erweitert" -ForegroundColor Green
} else {
    Write-Host "⚠ save_cookies() nicht gefunden oder bereits geändert" -ForegroundColor Yellow
}

# 2. load_cookies() erweitern
$old2 = @"
	private function load_cookies(): void {

		`$cookies = get_option( self::COOKIE_OPTION_KEY, array() );

		if ( is_array( `$cookies ) ) {

			`$this->cookies = `$cookies;

		}

	}
"@

$new2 = @"
	private function load_cookies(): void {

		`$cookies = get_option( self::COOKIE_OPTION_KEY, array() );

		if ( is_array( `$cookies ) ) {

			`$this->cookies = `$cookies;

		}

		`$this->cookie_created_at = (int) get_option( self::COOKIE_EXPIRY_KEY, 0 );

	}
"@

if ($content.Contains($old2)) {
    $content = $content.Replace($old2, $new2)
    Write-Host "✓ load_cookies() erweitert" -ForegroundColor Green
} else {
    Write-Host "⚠ load_cookies() nicht gefunden oder bereits geändert" -ForegroundColor Yellow
}

# 3. clear_cookies() erweitern
$old3 = @"
	public function clear_cookies(): void {

		`$this->cookies = array();

		delete_option( self::COOKIE_OPTION_KEY );

	}
"@

$new3 = @"
	public function clear_cookies(): void {

		`$this->cookies = array();

		`$this->cookie_created_at = 0;

		delete_option( self::COOKIE_OPTION_KEY );

		delete_option( self::COOKIE_EXPIRY_KEY );

	}
"@

if ($content.Contains($old3)) {
    $content = $content.Replace($old3, $new3)
    Write-Host "✓ clear_cookies() erweitert" -ForegroundColor Green
} else {
    Write-Host "⚠ clear_cookies() nicht gefunden oder bereits geändert" -ForegroundColor Yellow
}

# Speichern
$content | Set-Content $file -NoNewline -Encoding UTF8
Write-Host "`n=== Änderungen gespeichert ===" -ForegroundColor Green
Write-Host "Cookies werden jetzt mit Ablaufzeit in DB gespeichert!" -ForegroundColor Cyan
