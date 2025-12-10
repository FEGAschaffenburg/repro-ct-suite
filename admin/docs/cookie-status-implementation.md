# Cookie-Status & Automatische Erneuerung - Implementierung v0.9.7.8

## Übersicht

Die ChurchTools Suite verwaltet jetzt den Cookie-Status und zeigt ihn im Debug-Tab an. Das System meldet sich automatisch neu an, wenn die Session abläuft.

## Features

### 1. Cookie-Status-Anzeige (Debug-Tab)

**Anzeige umfasst:**
- ✅ **Status**: Aktiv / Abgelaufen / Nicht angemeldet
- ✅ **Erstellzeit**: Wann wurde die Session erstellt
- ✅ **Verbleibende Gültigkeit**: Countdown in Stunden und Minuten
- ✅ **Cookie-Anzahl**: Anzahl gespeicherter Session-Cookies
- ✅ **Farbcodierung**:
  - 🟢 Grün: Aktiv (> 1h verbleibend)
  - 🟡 Gelb: Warnung (< 1h verbleibend)
  - 🔴 Rot: Abgelaufen / Nicht angemeldet

**Datei:** `admin/views/tabs/tab-debug.php`

### 2. Ablaufzeit-Tracking (CT_Client)

**Neue Konstanten:**
```php
const COOKIE_EXPIRY_KEY = 'repro_ct_suite_ct_cookie_expiry'; // WordPress-Option für Zeitstempel
const COOKIE_LIFETIME = 43200; // 12 Stunden in Sekunden
```

**Neue Property:**
```php
private $cookie_created_at = 0; // Zeitpunkt der Cookie-Erstellung
```

**Erweiterte Methoden:**
- `save_cookies()` - Speichert jetzt auch `cookie_created_at` mit `time()`
- `load_cookies()` - Lädt `cookie_created_at` aus WordPress-Option
- `clear_cookies()` - Löscht auch die Ablaufzeit

**Datei:** `includes/class-repro-ct-suite-ct-client.php`

### 3. Automatische Neuanmeldung (bereits vorhanden)

**Bereits implementiert in:**
- `CT_Client::get()` - Bei 401-Error: Auto-Login + Retry
- `CT_Client::post()` - Bei 401-Error: Auto-Login + Retry

**Code-Pattern:**
```php
if ( $status === 401 ) {
    // Session abgelaufen, neu einloggen
    $this->clear_cookies();
    $login_result = $this->login();
    if ( is_wp_error( $login_result ) ) {
        return $login_result;
    }
    // Retry
    return $this->get( $endpoint, $args );
}
```

## Technische Details

### Cookie-Lebenszyklus

1. **Login** (`login()`):
   - Username/Password-Auth bei ChurchTools
   - Cookies aus Response extrahieren
   - Speichern: `save_cookies()` → setzt `$cookie_created_at = time()`

2. **Verwendung** (`get()`, `post()`):
   - Cookies werden in Request-Headers mitgesendet
   - Bei 401-Error → automatisches Re-Login

3. **Ablauf-Prüfung**:
   - Cookie-Alter: `time() - $cookie_created_at`
   - Verbleibend: `COOKIE_LIFETIME - age`
   - Abgelaufen wenn: `age >= COOKIE_LIFETIME`

### WordPress-Optionen

```php
get_option( 'repro_ct_suite_ct_cookies' )        // Array mit Cookies
get_option( 'repro_ct_suite_ct_cookie_expiry' )  // Timestamp (int)
```

### Debug-Tab Logik

```php
$cookie_data = get_option( 'repro_ct_suite_ct_cookies', array() );
$cookie_created = (int) get_option( 'repro_ct_suite_ct_cookie_expiry', 0 );
$cookie_lifetime = 43200; // 12h
$age = time() - $cookie_created;
$remaining = max( 0, $cookie_lifetime - $age );
```

## Geplante Erweiterungen (v0.9.8+)

### Proaktive Cookie-Erneuerung

```php
public function refresh_cookie_if_needed() {
    $remaining = $this->get_cookie_remaining_time();
    
    // Erneuere wenn weniger als 1 Stunde verbleibend
    if ( $remaining > 0 && $remaining < 3600 ) {
        return $this->login(); // Proaktives Re-Login
    }
    
    return true;
}
```

Kann aufgerufen werden in:
- Sync-Funktionen vor API-Requests
- Cron-Jobs
- Admin-Initialisierung

### Zusätzliche Helper-Methoden

```php
public function is_cookie_expired(): bool
public function get_cookie_remaining_time(): int
public function get_cookie_status(): array
private function format_duration( int $seconds ): string
```

## Testing

1. **Cookie-Status testen:**
   - Plugin installieren
   - Debug-Tab öffnen
   - Cookie-Status-Box prüfen

2. **Auto-Login testen:**
   - Warte bis Cookie abläuft (12h)
   - ODER: Manuell in DB löschen: `DELETE FROM wp_options WHERE option_name = 'repro_ct_suite_ct_cookies';`
   - Sync durchführen
   - → Plugin loggt sich automatisch neu ein

3. **Browser-Console:**
   - Keine 401-Errors sollten sichtbar sein
   - Sync sollte transparent funktionieren

## Changelog v0.9.7.8

✅ Cookie-Status-Anzeige im Debug-Tab  
✅ Ablaufzeit-Tracking (12h Gültigkeit)  
✅ Farbcodierte Warnung bei ablaufenden Cookies  
✅ Automatisches Re-Login bei 401-Fehler (bereits vorhanden)  
✅ Cookie-basierte Session-Verwaltung (bereits vorhanden)

## Dateien geändert

- `admin/views/tabs/tab-debug.php` - Cookie-Status-Anzeige
- `includes/class-repro-ct-suite-ct-client.php` - Ablaufzeit-Tracking
- `repro-ct-suite.php` - Version 0.9.7.8
- `admin/docs/changelog.md` - Dokumentation
