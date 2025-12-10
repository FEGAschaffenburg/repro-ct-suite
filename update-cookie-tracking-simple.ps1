$file = "includes\class-repro-ct-suite-ct-client.php"
$content = Get-Content $file -Raw

# Schritt 1: Nach COOKIE_OPTION_KEY neue Konstanten hinzufügen
$old1 = "	const COOKIE_OPTION_KEY = 'repro_ct_suite_ct_cookies';"
$new1 = @"
	const COOKIE_OPTION_KEY = 'repro_ct_suite_ct_cookies';

	/**
	 * Option-Key für Cookie-Ablaufzeit
	 */
	const COOKIE_EXPIRY_KEY = 'repro_ct_suite_ct_cookie_expiry';

	/**
	 * Cookie-Gültigkeitsdauer in Sekunden (12 Stunden)
	 */
	const COOKIE_LIFETIME = 43200;

	/**
	 * Zeitpunkt der Cookie-Erstellung
	 *
	 * @var int
	 */
	private `$cookie_created_at = 0;
"@

if ($content -match [regex]::Escape($old1)) {
    $content = $content.Replace($old1, $new1)
    Write-Host "✓ 1. Konstanten hinzugefügt" -ForegroundColor Green
} else {
    Write-Host "✗ 1. Pattern nicht gefunden" -ForegroundColor Red
}

# Schritt 2: load_cookies() erweitern mit cookie_created_at Laden
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

if ($content -match [regex]::Escape($old2.Trim())) {
    $content = $content.Replace($old2, $new2)
    Write-Host "✓ 2. load_cookies() erweitert" -ForegroundColor Green
} else {
    Write-Host "✗ 2. Pattern nicht gefunden" -ForegroundColor Red
}

# Schritt 3: save_cookies() erweitern
$old3 = @"
	private function save_cookies(): void {
		update_option( self::COOKIE_OPTION_KEY, `$this->cookies, false );
	}
"@

$new3 = @"
	private function save_cookies(): void {
		`$this->cookie_created_at = time();
		update_option( self::COOKIE_OPTION_KEY, `$this->cookies, false );
		update_option( self::COOKIE_EXPIRY_KEY, `$this->cookie_created_at, false );
	}
"@

if ($content -match [regex]::Escape($old3.Trim())) {
    $content = $content.Replace($old3, $new3)
    Write-Host "✓ 3. save_cookies() erweitert" -ForegroundColor Green
} else {
    Write-Host "✗ 3. Pattern nicht gefunden" -ForegroundColor Red
}

# Schritt 4: clear_cookies() erweitern
$old4 = @"
	public function clear_cookies(): void {
		`$this->cookies = array();
		delete_option( self::COOKIE_OPTION_KEY );
	}
"@

$new4 = @"
	public function clear_cookies(): void {
		`$this->cookies = array();
		`$this->cookie_created_at = 0;
		delete_option( self::COOKIE_OPTION_KEY );
		delete_option( self::COOKIE_EXPIRY_KEY );
	}
"@

if ($content -match [regex]::Escape($old4.Trim())) {
    $content = $content.Replace($old4, $new4)
    Write-Host "✓ 4. clear_cookies() erweitert" -ForegroundColor Green
} else {
    Write-Host "✗ 4. Pattern nicht gefunden" -ForegroundColor Red
}

# Speichern
$content | Set-Content $file -NoNewline
Write-Host "`n=== Basis-Änderungen gespeichert ===" -ForegroundColor Cyan
