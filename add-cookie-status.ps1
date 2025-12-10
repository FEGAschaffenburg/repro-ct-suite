# Script zum Hinzufügen der Cookie-Status-Features
$file = "includes\class-repro-ct-suite-ct-client.php"
$content = [System.IO.File]::ReadAllText((Resolve-Path $file), [System.Text.UTF8Encoding]::new($false))

Write-Host "=== Cookie-Status-Features hinzufügen ===" -ForegroundColor Cyan

# 1. Neue Konstanten und Property nach COOKIE_OPTION_KEY
$search1 = "const COOKIE_OPTION_KEY = 'repro_ct_suite_ct_cookies';"
$replace1 = @"
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
$content = $content -replace [regex]::Escape($search1), $replace1
Write-Host "✓ 1. Konstanten und Property hinzugefügt" -ForegroundColor Green

# 2. load_cookies() erweitern
$search2 = @"
	private function load_cookies\(\): void \{
		\`$cookies = get_option\( self::COOKIE_OPTION_KEY, array\(\) \);
		if \( is_array\( \`$cookies \) \) \{
			\`$this->cookies = \`$cookies;
		\}
	\}
"@
$replace2 = @"
private function load_cookies(): void {
		`$cookies = get_option( self::COOKIE_OPTION_KEY, array() );
		if ( is_array( `$cookies ) ) {
			`$this->cookies = `$cookies;
		}
		`$this->cookie_created_at = (int) get_option( self::COOKIE_EXPIRY_KEY, 0 );
	}
"@
$content = $content -replace $search2, "`t$replace2"
Write-Host "✓ 2. load_cookies() erweitert" -ForegroundColor Green

# 3. save_cookies() erweitern
$search3 = @"
	private function save_cookies\(\): void \{
		update_option\( self::COOKIE_OPTION_KEY, \`$this->cookies, false \);
	\}
"@
$replace3 = @"
private function save_cookies(): void {
		`$this->cookie_created_at = time();
		update_option( self::COOKIE_OPTION_KEY, `$this->cookies, false );
		update_option( self::COOKIE_EXPIRY_KEY, `$this->cookie_created_at, false );
	}
"@
$content = $content -replace $search3, "`t$replace3"
Write-Host "✓ 3. save_cookies() erweitert" -ForegroundColor Green

# 4. clear_cookies() erweitern  
$search4 = @"
	public function clear_cookies\(\): void \{
		\`$this->cookies = array\(\);
		delete_option\( self::COOKIE_OPTION_KEY \);
	\}
"@
$replace4 = @"
public function clear_cookies(): void {
		`$this->cookies = array();
		`$this->cookie_created_at = 0;
		delete_option( self::COOKIE_OPTION_KEY );
		delete_option( self::COOKIE_EXPIRY_KEY );
	}
"@
$content = $content -replace $search4, "`t$replace4"
Write-Host "✓ 4. clear_cookies() erweitert" -ForegroundColor Green

# 5. is_authenticated() verbessern
$search5 = @"
	public function is_authenticated\(\): bool \{
		if \( empty\( \`$this->cookies \) \) \{
			return false;
		\}

		// Optionaler Whoami-Check \(kann hinzugefügt werden\)
		// Für jetzt: wenn Cookies da sind, annehmen dass Session aktiv ist
		return true;
	\}
"@
$replace5 = @"
public function is_authenticated(): bool {
		if ( empty( `$this->cookies ) ) {
			return false;
		}

		// Prüfe ob Cookie abgelaufen ist
		if ( `$this->is_cookie_expired() ) {
			return false;
		}

		return true;
	}
"@
$content = $content -replace $search5, "`t$replace5"
Write-Host "✓ 5. is_authenticated() verbessert" -ForegroundColor Green

# Speichern
[System.IO.File]::WriteAllText((Resolve-Path $file), $content, [System.Text.UTF8Encoding]::new($false))
Write-Host "`n✅ Basis-Änderungen abgeschlossen" -ForegroundColor Green
Write-Host "Jetzt werden die neuen Methoden hinzugefügt..." -ForegroundColor Yellow
