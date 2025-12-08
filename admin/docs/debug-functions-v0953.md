# Debug-Funktionen v0.9.5.3

## Neue Funktionen

### 1. Einzelne Datensätze anzeigen und löschen

Jede Tabelle in der Datenbank-Übersicht hat nun einen **"Anzeigen"**-Button:

- Zeigt die letzten 100 Einträge der Tabelle in einem Modal-Dialog
- Anzeige der wichtigsten Spalten (ID, CT-ID, Name, Datum)
- Jeder Eintrag kann einzeln gelöscht werden
- Automatisches Neu-Laden nach kompletter Leerung

**Tabellen:**
- `rcts_calendars` - Kalender
- `rcts_events` - Events & Termine
- `rcts_event_services` - Event-Services
- `rcts_schedule` - Terminkalender (Schedule)

**Workflow:**
1. Auf "Anzeigen" klicken
2. Tabelle mit Einträgen wird in Modal angezeigt
3. Bei Bedarf einzelne Einträge über "Löschen"-Button entfernen
4. Modal schließen oder Seite wird automatisch neu geladen

### 2. Vollständiger Plugin-Reset

Neuer Button: **"Vollständiger Plugin-Reset"** (dunkelroter Button)

**Was wird gelöscht:**
- Alle Tabellendaten (Kalender, Events, Services, Schedule)
- ChurchTools Zugangsdaten (Tenant, Username, Password)
- Session-Daten
- Synchronisations-Zeitstempel
- Alle Plugin-Einstellungen

**Sicherheit:**
- Doppelte Bestätigung erforderlich
- Nach Reset: Automatische Weiterleitung zur Hauptseite
- Alle Einstellungen müssen neu vorgenommen werden

**Verwendung:**
Nur verwenden wenn:
- Das Plugin komplett neu konfiguriert werden soll
- Bei Problemen nach Updates
- Beim Wechsel der ChurchTools-Instanz
- Vor Deinstallation (optional)

## Technische Details

### AJAX-Endpunkte

**`repro_ct_suite_get_table_entries`**
- Lädt bis zu 100 Einträge einer Tabelle
- Parameter: `table` (Tabellenname), `nonce`
- Rückgabe: Array mit Einträgen und Spaltennamen

**`repro_ct_suite_delete_single_entry`**
- Löscht einen einzelnen Eintrag
- Parameter: `table`, `entry_id`, `nonce`
- Rückgabe: Erfolgsmeldung

**`repro_ct_suite_full_reset`** (bereits vorhanden, UI hinzugefügt)
- Setzt das gesamte Plugin zurück
- Parameter: `nonce`
- Rückgabe: Erfolgsmeldung

### Neue Dateien

- `admin/js/repro-ct-suite-debug-extensions.js` - JavaScript für neue Funktionen
- `admin/css/repro-ct-suite-modal.css` - Modal-Styles

### Geänderte Dateien

- `admin/class-repro-ct-suite-admin.php` - AJAX-Handler und Enqueues
- `admin/views/admin-debug.php` - UI-Erweiterungen

## Berechtigungen

Alle Debug-Funktionen erfordern die Berechtigung `manage_options` (Administrator).

## Sicherheit

- Nonce-Prüfung bei allen AJAX-Anfragen
- Berechtigungsprüfung vor jeder Aktion
- SQL-Injection-Schutz durch wpdb prepared statements
- XSS-Schutz durch esc_* Funktionen
