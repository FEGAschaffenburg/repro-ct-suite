# Debug-Anleitung für Kalender-Synchronisation

## Übersicht

Das Plugin verfügt jetzt über umfangreiche Debug-Funktionen, um Probleme bei der Kalender-Synchronisation zu diagnostizieren.

## Debug-Ausgaben

### 1. Browser-Konsole (Frontend)

**Öffnen:** Drücken Sie `F12` in Ihrem Browser und wechseln Sie zum Tab "Console"

**Was wird angezeigt:**
- AJAX-Request-Start
- Vollständige AJAX-Response mit allen Daten
- Erfolgs-/Fehlermeldungen
- Debug-Informationen (URL, Tenant, Zeitstempel)
- Bei Fehlern: Detaillierte Fehlerinformationen

**Beispiel-Ausgabe:**
```
[DEBUG] Kalender-Synchronisation gestartet...
[DEBUG] AJAX Response: {success: true, data: {...}}
[DEBUG] Erfolgreiche Synchronisation:
- Statistik: {total: 5, inserted: 2, updated: 3, errors: 0}
- Debug-Info: {url: "https://beispiel.church.tools/api/calendars", ...}
```

### 2. WordPress Debug-Log (Backend)

**Aktivierung:** Fügen Sie in Ihrer `wp-config.php` hinzu:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

**Log-Datei:** `wp-content/debug.log`

**Was wird protokolliert:**

#### AJAX Handler (`admin/class-repro-ct-suite-admin.php`)
- Request-URL und Tenant
- Vollständige Result-Statistiken
- Fehler mit Stack Traces

#### Calendar Sync Service (`includes/services/class-repro-ct-suite-calendar-sync-service.php`)
- API-Call Start
- Response-Typ und -Struktur
- Anzahl gefundener Kalender
- Import-Status für jeden Kalender (inserted/updated)
- Abschluss-Statistiken

#### CT Client (`includes/class-repro-ct-suite-ct-client.php`)
- Endpoint und Parameter
- Authentifizierungs-Status
- Login-Versuche
- Vollständige Request-URL
- Request-Headers (inkl. Cookies)
- HTTP-Status-Codes
- Response-Body (erste 500 Zeichen)
- JSON-Decode-Fehler
- Response-Struktur (Keys)

**Beispiel-Ausgabe:**
```
[REPRO CT-SUITE DEBUG] Calendar Sync Request: Array (
    [tenant] => beispiel
    [url] => https://beispiel.church.tools/api/calendars
    [timestamp] => 2025-10-29 15:30:00
)

[REPRO CT-SUITE DEBUG] CT_Client::get() called
[REPRO CT-SUITE DEBUG] - Endpoint: /calendars
[REPRO CT-SUITE DEBUG] - Is Authenticated: yes
[REPRO CT-SUITE DEBUG] Full Request URL: https://beispiel.church.tools/api/calendars
[REPRO CT-SUITE DEBUG] Response Status Code: 200
[REPRO CT-SUITE DEBUG] Response Body Length: 2458 bytes
[REPRO CT-SUITE DEBUG] Response has keys: Array ( [0] => data [1] => meta )

[REPRO CT-SUITE DEBUG] Found 5 calendars in response
[REPRO CT-SUITE DEBUG] Processing calendar 1: Gottesdienst
[REPRO CT-SUITE DEBUG] Calendar inserted with ID: 123
[REPRO CT-SUITE DEBUG] Sync completed - Stats: Array (
    [total] => 5
    [inserted] => 2
    [updated] => 3
    [errors] => 0
)
```

## Debug-Workflow

### Schritt 1: Kalender-Sync durchführen
1. Gehen Sie zu **ChurchTools** → **Einstellungen**
2. Klicken Sie auf **"Kalender jetzt synchronisieren"**
3. Bestätigen Sie den Dialog

### Schritt 2: Browser-Konsole prüfen
1. Drücken Sie `F12`
2. Wechseln Sie zum Tab "Console"
3. Suchen Sie nach `[DEBUG]` Einträgen
4. Prüfen Sie auf Fehler (rot markiert)

### Schritt 3: WordPress Debug-Log prüfen
1. Öffnen Sie `wp-content/debug.log`
2. Suchen Sie nach `[REPRO CT-SUITE DEBUG]`
3. Finden Sie die neuesten Einträge (sortiert nach Zeitstempel)

## Häufige Probleme und Lösungen

### Problem: 401 Unauthorized
**Log-Ausgabe:** `Response Status Code: 401`
**Ursache:** Session abgelaufen oder falsche Credentials
**Lösung:** 
- Testen Sie die Verbindung unter Einstellungen
- Prüfen Sie Tenant, Username und Passwort
- Log zeigt automatischen Re-Login-Versuch

### Problem: Keine Kalender gefunden
**Log-Ausgabe:** `Found 0 calendars in response`
**Ursache:** ChurchTools liefert keine Kalender
**Lösung:**
- Prüfen Sie ob Ihr User in ChurchTools Kalender-Rechte hat
- Prüfen Sie ob überhaupt Kalender angelegt sind

### Problem: Invalid Response
**Log-Ausgabe:** `Invalid response structure - data array missing`
**Ursache:** ChurchTools-API hat sich geändert oder liefert unerwartete Daten
**Lösung:**
- Prüfen Sie die "Full Response" im Log
- Kontaktieren Sie den Support mit der Log-Ausgabe

### Problem: JSON Decode Error
**Log-Ausgabe:** `JSON Decode Error: ...`
**Ursache:** ChurchTools liefert kein valides JSON
**Lösung:**
- Prüfen Sie "Response Body" im Log
- Möglicherweise ChurchTools-Server-Fehler

## Debug-Informationen sammeln

Wenn Sie Support benötigen, sammeln Sie bitte folgende Informationen:

1. **Browser-Konsole:**
   - Screenshot oder Copy/Paste aller `[DEBUG]` Meldungen

2. **WordPress Debug-Log:**
   - Alle `[REPRO CT-SUITE DEBUG]` Einträge vom aktuellen Sync-Versuch

3. **System-Informationen:**
   - WordPress-Version
   - PHP-Version
   - Plugin-Version
   - ChurchTools-Tenant-Name (ohne URL)

## Debug-Modus deaktivieren

Die Debug-Ausgaben haben minimalen Performance-Impact, können aber bei Bedarf entfernt werden:

1. In Produktionsumgebungen sollte `WP_DEBUG_LOG` nur bei Bedarf aktiviert werden
2. Browser-Konsolen-Logs können ignoriert werden (nur für Entwickler sichtbar)
3. Die Debug-Ausgaben werden in einer späteren Version über ein Setting steuerbar sein

## Technische Details

### Logging-Ebenen

1. **AJAX Handler:** High-Level (Request/Response, Stats)
2. **Service Layer:** Business-Logic (Verarbeitung, Import-Status)
3. **CT Client:** Low-Level (HTTP-Details, API-Kommunikation)

### Log-Prefix

Alle Logs verwenden das Prefix `[REPRO CT-SUITE DEBUG]` für einfaches Filtern:

```bash
# In debug.log nach Plugin-Logs suchen:
grep "REPRO CT-SUITE DEBUG" wp-content/debug.log

# Nur letzte 50 Einträge:
grep "REPRO CT-SUITE DEBUG" wp-content/debug.log | tail -50
```

## Support

Bei Problemen erstellen Sie bitte ein Issue auf GitHub mit:
- Detaillierter Fehlerbeschreibung
- Browser-Konsolen-Output
- WordPress Debug-Log Auszug
- System-Informationen
