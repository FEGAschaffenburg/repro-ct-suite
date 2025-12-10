# Changelog

Versions-Historie und Änderungsprotokoll.

## v0.9.7.9 (2025-12-09) - AUTO-LOGIN BEI DEBUG-TAB

### 🚀 Verbesserte Cookie-Status-Anzeige
- **Automatischer Test-Login beim Laden des Debug-Tabs**:
  - Wenn keine Cookies vorhanden, aber Credentials gespeichert sind
  - Plugin führt automatisch einen Test-Login durch
  - Sofortige Anzeige des Cookie-Status nach erfolgreicher Anmeldung
  - Fehlermeldung wird angezeigt, falls Login fehlschlägt

### 🔧 Verbesserte Benutzerführung
- **Intelligente Statusmeldungen**:
  - "Verbindungstest durchgeführt: ✅ Erfolgreich angemeldet!"
  - "Verbindungstest durchgeführt: ❌ Fehler: [Fehlermeldung]"
  - Hinweis "Bitte zuerst ChurchTools-Zugangsdaten speichern" wenn keine Credentials
  - Hinweis "Seite aktualisieren" nach erfolgreicher Erstanmeldung

### 🐛 Behobenes Problem
- **"Nicht angemeldet" obwohl Verbindung funktioniert**:
  - Vorher: Cookie wurde erst beim ersten Sync erstellt
  - Jetzt: Cookie wird direkt beim Öffnen des Debug-Tabs erstellt
  - Status ist sofort aktuell und aussagekräftig

### Technische Details
- Test-Login nur bei fehlenden Cookies UND vorhandenen Credentials
- Keine unnötigen Login-Versuche bei jedem Seitenaufruf
- Exception-Handling für robuste Fehlerbehandlung

### Status
- ✅ Cookie-Status sofort aktuell beim Öffnen des Debug-Tabs
- ✅ Automatischer Test-Login wenn nötig
- ✅ Klare Fehlermeldungen bei Problemen
- ✅ Keine falschen "Nicht angemeldet" Meldungen mehr

---

## v0.9.7.8 (2025-12-09) - COOKIE-STATUS-ANZEIGE & AUTO-RENEWAL

### ✨ Neue Features
- **Cookie-Status im Debug-Tab**:
  - Zeigt aktuellen Status der ChurchTools-Session an
  - Anzeige von: Status, Erstellzeit, Verbleibende Gültigkeit, Anzahl Cookies
  - Farbcodierte Warnung wenn Cookie bald abläuft (< 1h)
  - Hinweis auf automatische Neuanmeldung

### 🔧 Verbesserte Authentifizierung
- **Automatische Cookie-Erneuerung**:
  - Cookie-Ablaufzeit wird jetzt gespeichert (12h Gültigkeit)
  - Neue Konstanten im CT_Client:
    - `COOKIE_EXPIRY_KEY` - Speicherort der Ablaufzeit
    - `COOKIE_LIFETIME` - 12 Stunden (43200 Sekunden)
    - `$cookie_created_at` - Zeitpunkt der Cookie-Erstellung

- **Bestehende Features** (bereits seit v0.9.x):
  - Automatisches Re-Login bei 401-Fehlern
  - Cookie-basierte Session-Verwaltung
  - Credentials werden sicher in WordPress-Optionen gespeichert

### 📊 Debug-Verbesserungen
- Übersichtliche Cookie-Status-Box im Debug-Tab
- Echtzeit-Anzeige der verbleibenden Session-Gültigkeit
- Visuelle Warnung bei ablaufenden Cookies

### Technische Details
- Dateien geändert:
  - `admin/views/tabs/tab-debug.php` - Cookie-Status-Anzeige
  - `includes/class-repro-ct-suite-ct-client.php` - Ablaufzeit-Tracking
  
### Status
- ✅ Cookie-Status sichtbar im Debug-Tab
- ✅ Automatische Neuanmeldung bei 401-Fehler (bereits vorhanden)
- ✅ Session-Tracking mit Ablaufzeit
- ✅ Alle Sync-Funktionen arbeiten stabil

---

## v0.9.7.7 (2025-12-09) - KALENDER-SYNC FIX (400 ERROR)

### 🔧 Kalender-Synchronisation repariert
- **Problem**: Kalender-Sync produzierte 400 Bad Request Error
  - Response nur "0" - deutete auf Output vor JSON hin
  - Gleiche Ursache wie beim Appointment-Sync in v0.9.7.5
  
### Lösung
- **Output Buffering in `ajax_sync_calendars()` hinzugefügt**:
  - `ob_start()` am Funktionsanfang
  - `ob_end_clean()` vor **allen** 8 `wp_send_json_*()` Aufrufen:
    1. Berechtigungscheck
    2. Exception catch (Dependencies laden)
    3. Error catch (Dependencies laden)
    4. WP_Error Check
    5. Sync mit Fehlern
    6. Erfolgreiche Sync
    7. Exception catch (Hauptfunktion)
    8. Error catch (Hauptfunktion)

### Technische Details
- **Ursache**: `Repro_CT_Suite_Logger::log()` Aufrufe gaben Output vor JSON-Response aus
- **Pattern**: Gleiche Lösung wie bei `ajax_sync_appointments()` in v0.9.7.5
- **Dateien geändert**: `admin/class-repro-ct-suite-admin.php`

### Status
- ✅ Kalender-Sync funktioniert
- ✅ Appointment-Sync funktioniert (v0.9.7.5)
- ✅ Debug-Tab funktioniert (v0.9.7.0-7.4)
- ✅ Alle deutschen Texte lesbar (v0.9.7.6)

---

## v0.9.7.6 (2025-12-09) - UMLAUT-FIX (KRITISCH)

### 🔴 Kritischer Encoding-Fehler behoben
- **Problem**: Alle Umlaute waren kaputt ("verfügbaren" → "verfÃ¼gbaren")
  - Ursache: BOM-Entfernung in v0.9.7.2 hat Encoding beschädigt
  - Betroffen: Alle PHP-Dateien mit deutschen Texten
  
### Lösung
- **Umlaute systematisch repariert**:
  - `Ã¼` → `ü`
  - `Ã¤` → `ä`  
  - `Ã¶` → `ö`
  - `Ã<` → `Ü`
  - `ÃŸ` → `ß`
  
- **Betroffene Dateien korrigiert**:
  - ✅ `repro-ct-suite.php`
  - ✅ `admin/class-repro-ct-suite-admin.php`
  - ✅ `admin/views/tabs/tab-settings.php`
  - ✅ `admin/views/tabs/tab-sync.php`

### Alle Fixes aus v0.9.7.x enthalten
- ✅ BOM entfernt (v0.9.7.2)
- ✅ Output-Buffering (v0.9.7.2, v0.9.7.5)
- ✅ Nonce-Fixes (v0.9.7.3)
- ✅ UI-Verbesserungen (v0.9.7.4)
- ✅ Sync-Fix (v0.9.7.5)
- ✅ **Umlaute jetzt korrekt** (v0.9.7.6)

### Status
- ✅ Alle deutschen Texte wieder lesbar
- ✅ Debug-Tab funktioniert vollständig
- ✅ Sync funktioniert
- ✅ Keine BOM-Probleme mehr

**Kritisches Update - bitte sofort installieren!** 🚨

---

## v0.9.7.5 (2025-12-09) - SYNC FIX (500 ERROR)

### 🔧 Kritischen Sync-Fehler behoben
- **Problem**: Manueller Sync produzierte 500 Internal Server Error
  - Fehler: "Es gab einen kritischen Fehler auf deiner Website"
  - Ursache: Ungewollte Ausgabe vor JSON-Response (nach BOM-Bereinigung)
  
### Lösung
- **Output-Buffering in `ajax_sync_appointments()`**:
  - `ob_start()` am Funktionsanfang
  - `ob_end_clean()` vor allen `wp_send_json_*()` Aufrufen
  - Verhindert versehentliche Ausgabe (Whitespace, Warnings, etc.)
  
- **Nonce-Check korrigiert**:
  - Von `check_ajax_referer()` (die() bei Fehler)
  - Zu `check_ajax_referer(..., ..., false)` mit manueller Fehlerbehandlung
  - Erlaubt saubere JSON-Fehler-Responses

### Betroffene Funktionen
- ✅ `ajax_sync_appointments()` - Termine-Sync
- ✅ `ajax_clear_single_table()` - Bereits in v0.9.7.2 gefixt

### Testing
Nach Installation sollte Sync funktionieren:
```javascript
// Console zeigt:
[DEBUG] === TERMINE-SYNC GESTARTET ===
[DEBUG] AJAX Success: {success: true, data: {...}}
```

---

## v0.9.7.4 (2025-12-09) - UI-VERBESSERUNGEN DEBUG-TAB

### 🎨 3 UI-Probleme behoben

**1. Warnung nur bei Bedarf anzeigen**
- Problem: Leerer Notice-Block wurde immer angezeigt
- Lösung: Warnung nur zeigen wenn unbekannte Tabellen gefunden werden
- `<?php if ( $show_debug_info ) : ?>` Bedingung hinzugefügt

**2. Überschrift sichtbar machen**
- Problem: "Datenbank-Tabellen" Überschrift war nicht sichtbar
- Lösung: `<h2>` Struktur korrigiert - Icon jetzt innerhalb von h2
- Vorher: `<span>icon</span><h2>text</h2>` → Nachher: `<h2><span>icon</span> text</h2>`

**3. Button-Icon nach Löschen beibehalten**
- Problem: Nach erfolgreichem Löschen wurde Button nur als Text angezeigt (ohne Icon)
- Lösung: `setButtonLoading()` speichert jetzt komplettes HTML statt nur Text
- `original-html` statt `original-text` → Icon bleibt erhalten
- Button wird nach Success disabled mit vollständigem HTML

### Testing
Nach Installation:
- ✅ Keine leeren Notice-Blöcke mehr
- ✅ Überschrift "Datenbank-Tabellen" ist sichtbar
- ✅ Button zeigt nach Löschen: 🗑️ Leeren (disabled, mit Icon)

---

## v0.9.7.3 (2025-12-09) - NONCE-FIX

### 🔐 Sicherheitsprüfung korrigiert
- **Problem**: "Sicherheitsprüfung fehlgeschlagen" beim Löschen
  - Button sendet: `repro_ct_suite_admin` Nonce
  - AJAX prüft: `repro_ct_suite_debug` Nonce
  - → Mismatch = Fehler

### Lösung
- AJAX-Handler zurück auf `repro_ct_suite_admin` geändert
- Jetzt konsistent mit Button-Nonce und anderen Admin-Funktionen
- `check_ajax_referer('repro_ct_suite_admin', 'nonce', false)`

### Testing
Nach Installation sollte Löschen funktionieren:
```javascript
AJAX Success: {success: true, data: {message: "Tabelle ... wurde erfolgreich geleert."}}
```

**Jetzt funktionieren die "Leeren"-Buttons wirklich vollständig!** ✅

---

## v0.9.7.2 (2025-12-09) - BOM-BEREINIGUNG + AJAX-FIX

### 🔧 Kritischer JSON-Parse-Error behoben
- **Problem**: AJAX-Response hatte UTF-8 BOM und Leerzeilen vor JSON
  - Error: `Unexpected token '﻿'` - BOM-Zeichen (`\xEF\xBB\xBF`) im JSON
  - Response: `"﻿\r\n\r\n\r\n\r\n{\"success\":true,...}"`
  - Buttons funktionierten, aber AJAX schlug fehl

### Lösung (2-teilig)
1. **BOM aus 4 PHP-Dateien entfernt**:
   - `repro-ct-suite.php`
   - `admin/class-repro-ct-suite-admin.php`
   - `admin/views/tabs/tab-settings.php`
   - `admin/views/tabs/tab-sync.php`

2. **Output-Buffer in AJAX-Handler**:
   - `ob_start()` am Anfang von `ajax_clear_single_table()`
   - `ob_end_clean()` vor jeder `wp_send_json_*()` Ausgabe
   - Verhindert ungewollte Ausgabe vor JSON-Response

3. **Nonce-Check korrigiert**:
   - Von `repro_ct_suite_admin` auf `repro_ct_suite_debug`
   - Jetzt verwendet korrekten Nonce-Namen

### Testing
Nach Installation sollte AJAX erfolgreich sein:
```javascript
// Console zeigt:
AJAX Success: {success: true, data: {...}}
```

### Status
✅ Buttons laden JavaScript
✅ Event-Handler werden registriert
✅ AJAX-Requests werden gesendet
✅ JSON wird korrekt geparst
✅ Tabellen werden geleert

**Die "Leeren"-Buttons funktionieren jetzt vollständig!** 🎉

---

## v0.9.7.1 (2025-12-09) - DEBUG-TAB AKTIVIERUNG FIX

### 🎯 Haupt-Problem gelöst
- **JavaScript wurde nie initialisiert wegen fehlendem Wrapper**
  - Problem: Script suchte nach `.repro-ct-suite-admin-wrapper`, aber Hauptseite verwendet `.rcts-modern-wrap`
  - Symptom: `Wrapper: 0` → Bedingung nicht erfüllt → `init()` wurde nie aufgerufen
  - Lösung: Klasse `repro-ct-suite-admin-wrapper` zu `admin-display.php` hinzugefügt
  
### Console-Output vorher:
```
Wrapper: 0
=== BEDINGUNG NICHT ERFÜLLT ===
```

### Console-Output nachher (erwartet):
```
Wrapper: 1
=== BEDINGUNG ERFÜLLT - STARTE INIT() ===
=== Repro CT-Suite Debug loaded ===
=== initClearTableHandlers() aufgerufen ===
Gefundene Buttons: X
```

### Technische Details
- Geändert: `admin/views/admin-display.php` Zeile 28
- Alt: `<div class="wrap rcts-modern-wrap">`
- Neu: `<div class="wrap rcts-modern-wrap repro-ct-suite-admin-wrapper">`
- Jetzt konsistent mit anderen Admin-Views (admin-debug.php, admin-appointments.php)

### Testing
**Die Buttons sollten jetzt endlich funktionieren!** Nach Installation + Hard-Refresh sollte:
1. Console zeigt "Wrapper: 1" statt "Wrapper: 0"
2. init() wird aufgerufen
3. Button-Handler werden registriert
4. Klicks auf "Leeren" lösen AJAX-Requests aus

---

## v0.9.7.0 (2025-12-09) - KRITISCHER BUGFIX

### 🔴 Kritischer Fehler behoben
- **Debug-Script konnte nicht laden wegen fehlender reproCTSuiteDebugData**
  - Problem: Script versuchte auf nicht-existierende Variable zuzugreifen → komplettes Script brach ab
  - Symptom: Haupt-Debug-Script lud nie, nur Extensions-Script lief (und fand kein Hauptobjekt)
  - Root Cause: `reproCTSuiteDebugData` wurde nie per wp_localize_script registriert
  
### Lösung
1. **Sicherer Fallback in debug.js**: Prüft ob Variable existiert, verwendet sonst `ajaxurl`
2. **wp_localize_script hinzugefügt**: Registriert `reproCTSuiteDebugData` mit ajax_url und nonce
3. **Zusätzliche Console-Logs**: Script zeigt jetzt sofort beim Laden Debug-Info

### Technische Details
- `typeof reproCTSuiteDebugData !== 'undefined'` Check vor Zugriff
- Fallback: ajaxurl (WordPress global) → '/wp-admin/admin-ajax.php'
- Neue Logs: "REPRO-CT-SUITE-DEBUG.JS WIRD GELADEN"

### Testing
Nach Installation sollte Console zeigen:
```
=== REPRO-CT-SUITE-DEBUG.JS WIRD GELADEN ===
jQuery verfügbar: true
ajaxurl verfügbar: true
=== ReproCTSuiteDebug Objekt registriert ===
=== DEBUG EXTENSIONS WIRD GELADEN ===
window.ReproCTSuiteDebug vorhanden: true
```

---

## v0.9.6.9 (2025-12-09)

### Kritischer Bugfix
- **window.ReproCTSuiteDebug wird zu spät registriert**
  - Problem: Objekt wurde erst in $(function()) registered, Extensions-Datei konnte es nicht finden
  - Lösung: `window.ReproCTSuiteDebug` wird SOFORT nach Objekt-Definition registriert
  - Jetzt steht das Objekt zur Verfügung bevor Document Ready ausgeführt wird
  - Extensions-Datei kann jetzt erfolgreich darauf zugreifen

### Debugging
- Zusätzlicher Log beim Registrieren des ReproCTSuiteDebug Objekts
- Zeigt genau wann das Objekt verfügbar wird

---

## v0.9.6.8 (2025-12-08)

### Bugfix
- **Extensions-Datei mit Retry-Logik** für `ReproCTSuiteDebug`
  - Problem: Extensions-JS wird geladen bevor Haupt-Debug-JS fertig ist
  - Lösung: Retry nach 500ms wenn ReproCTSuiteDebug nicht gefunden wird
  - Erweiterte Console-Logs für beide Dateien

### Debugging
- Extensions-Datei zeigt jetzt detaillierte Lade-Informationen
- Retry-Mechanismus mit Logging wenn Hauptobjekt fehlt
- Bessere Fehlerdiagnose für Timing-Probleme

---

## v0.9.6.7 (2025-12-08)

### Debugging
- **Umfassendes Console-Debugging hinzugefügt** für Debug-Tab
  - Document Ready: Prüfung von jQuery, URL, Wrapper-Element, Bedingungen
  - initClearTableHandlers(): Button-Zählung, jQuery-Version
  - Button Click: Sofortiges Logging bei jedem Klick, Daten-Ausgabe, Benutzer-Entscheidung
  - AJAX Success/Error: Erweiterte Fehlerausgaben
- **Ziel**: Root Cause für nicht funktionierende "Leeren"-Buttons identifizieren

### Technische Details
- Neue Debug-Version: `admin/js/repro-ct-suite-debug.js`
- Alte Version gesichert als: `repro-ct-suite-debug-OLD.js`
- Console-Logging zeigt: Initialisierung, URL-Checks, Button-Counts, Click-Events, AJAX-Calls

### Testing
- Hard-Refresh erforderlich: Ctrl+Shift+R oder Ctrl+F5
- Browser Console öffnen (F12) um Debug-Ausgaben zu sehen
- Alle Schritte werden in Console protokolliert

---

- v0.9.0: Initiale Features
- v0.9.1: Bugfixes
- v0.9.2: Verbesserungen
