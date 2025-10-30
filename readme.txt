=== Repro CT-Suite ===
Contributors: fegaaschaffenburg
Tags: churchtools, calendar, events, appointments, sync
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 0.3.5.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

WordPress-Plugin zur Integration von ChurchTools-Daten. Synchronisiert Termine und Veranstaltungen aus ChurchTools für die Anzeige auf WordPress-Websites.

== Description ==

Repro CT-Suite erstellt eine Brücke zwischen ChurchTools und WordPress. Das Plugin synchronisiert automatisch Termin- und Veranstaltungsdaten aus Ihrer ChurchTools-Instanz und macht sie über Shortcodes in WordPress verfügbar.

**Begriffsdefinition:**
* **Events**: Veranstaltungen aus ChurchTools Events-API
* **Termine (Appointments)**: Einfache Termine aus ChurchTools Appointments (ohne Event-Verknüpfung)
* **Terminkalender**: Gesamtübersicht aller Events und Termine

**Hauptfunktionen:**

* Automatische Synchronisation von ChurchTools Events
* Synchronisation von Appointments (Termine ohne Event-Verknüpfung)
* Gesamtübersicht im Terminkalender (Events + Termine)
* Einfache Anzeige via Shortcodes
* Admin-Oberfläche für Konfiguration
* WordPress Cron für automatische Updates
* Sichere API-Verbindung

**Shortcodes:**

* `[ct_appointments]` - Zeigt Termine an
* `[ct_events]` - Zeigt Events an

== Installation ==

1. Plugin-Ordner in `wp-content/plugins/` hochladen
2. In WordPress-Admin unter "Plugins" aktivieren
3. Zu "Repro CT-Suite" > "Einstellungen" navigieren
4. ChurchTools-URL und API-Token eintragen
5. Speichern und erste Synchronisation starten

== Frequently Asked Questions ==

= Wo bekomme ich einen ChurchTools API-Token? =

Loggen Sie sich in ChurchTools ein und gehen Sie zu Einstellungen > Sicherheit > API-Tokens. Dort können Sie einen neuen Token erstellen.

= Wie oft werden die Daten synchronisiert? =

Das Plugin nutzt WordPress Cron und synchronisiert standardmäßig alle 6 Stunden. Sie können auch manuell synchronisieren.

= Welche ChurchTools-Versionen werden unterstützt? =

Das Plugin ist für aktuelle ChurchTools-Versionen mit REST API entwickelt.

== Screenshots ==

1. Admin-Einstellungsseite
2. Termine-Anzeige im Frontend
3. Events-Anzeige im Frontend

== Changelog ==

= 0.3.5.2 =
* UI: Kalender-Auswahl zurück in Einstellungen-Seite integriert
* UI: Separate Kalender-Seite entfernt (redundant)
* UX: Vereinfachte Navigation - weniger Menüpunkte
* Version: 0.3.5.2

= 0.3.5.1 =
* Fix: GitHub Release ZIP-Struktur korrigiert für WordPress-Installation
* Version: 0.3.5.1

= 0.3.5.0 =
* UI: Komplette Umstrukturierung der Admin-Oberfläche
* Menu: Tabs jetzt als separate Menüpunkte mit bedingter Sichtbarkeit
* Menu: Kalender-Tab erscheint nur bei konfigurierter Verbindung
* Menu: Termine-Sync-Tab erscheint nur bei ausgewählten Kalendern
* Settings: Einstellungen aufgeteilt in 3 Sektionen (Verbindung, Abrufzeitraum, Automatisierung)
* Kalender: Eigene Seite für Kalender-Sync und Auswahl
* Sync: Eigene Seite für Termine-Synchronisation mit Übersicht
* Feature: Letzter Sync-Zeitpunkt wird gespeichert und angezeigt
* Feature: Sync-Statistiken werden persistent gespeichert
* UX: Verbesserte Navigation und klare Trennung der Funktionen
* Version: 0.3.5.0

= 0.3.4.1 =
* Fix: Events-Duplikate behoben - 2-Stufen-Sync-Strategie
* Sync: Events werden zuerst synchronisiert (aus /events)
* Sync: Appointments-Sync überspringt Appointments, die bereits als Event existieren (prüft appointment_id)
* Sync: Events extrahieren appointment_id aus API-Response
* Stats: Neue Statistik 'skipped_has_event' für übersprungene Appointments
* Version: 0.3.4.1

= 0.3.4.0 =
* Feature: Eigene Debug-Seite mit erweiterten Funktionen
* Debug: Einzelne oder alle Tabellen zurücksetzen (mit Bestätigung)
* Debug: Debug-Log-Anzeige mit Syntax-Highlighting (letzte 100 Zeilen)
* Debug: Log leeren und aktualisieren
* Debug: Datenbank-Migrationen manuell ausführen
* Debug: System-Informationen (WordPress, PHP, MySQL, Memory Limit)
* Debug: Tabellen-Statistik mit Zählung der Einträge
* UI: Debug-Bereich vom Dashboard in eigene Seite verschoben
* Version: 0.3.4.0

= 0.3.3.8 =
* Schema: `rcts_events` hat jetzt `appointment_id` für Verlinkung zu Appointments (DB_VERSION 3)
* Appointments-Sync: Speichert sowohl Events als auch Appointments-Einträge (vollständige Datenstruktur)
* Terminkalender: Zeigt Events (aus Events-API) + Termine (Appointments ohne Event-Verknüpfung)
* Wording: "Terminkalender" mit Art-Badge: Event (blau) vs Termin (grün)
* UI: Dashboard zeigt "Termine gesamt", Menü "Terminkalender"
* Appointments mit event_id werden in Events gespeichert und mit Appointment verknüpft

= 0.3.3.7 =
* Appointments: Umgestellt auf pro-Kalender-Abruf `GET /calendars/{id}/appointments?from&to` (Aggregation aller ausgewählten Kalender)
* Robust: Besseres Logging je Kalenderabruf; weiche Weiterführung bei 400/404/405

= 0.3.3.6 =
* Fix: Appointments-API nutzt jetzt nur noch GET (kein POST mehr; 405) und testet mehrere Query-Formate
	- Versuche: calendarIds[]=ID…; calendars[]=ID…; calendarIds=1,2,3; calendars=1,2,3; zuletzt Standard-Array via add_query_arg
	- Verbesserte Logs: exakte URL-Ausgabe pro Versuch

= 0.3.3.5 =
* DX: Client sendet jetzt zusätzlich `Accept: application/json` Header
* Events: GET-Aufruf mit `direction=forward` und `include=eventServices` ergänzt (wie API-Beispiel)

= 0.3.3.4 =
* Fix: Appointments-API akzeptiert jetzt JSON-Body mit `calendar_ids` (POST-Fallback), wenn GET-Varianten 400/404 liefern
* Log: Ausführliche Logs für GET/POST-Versuche inkl. Parameternamen

= 0.3.3.3 =
* Fix: "Cannot use object of type WP_Error as array" im Termine-AJAX-Handler behoben (robuste is_wp_error()-Prüfung und strukturierte Fehlerrückgabe)
* DX: Sicherere Erfolgs-Statistik (Default-Werte bei fehlenden Keys)
* Packaging: ZIP-Einträge nutzen konsistent "/"-Pfadtrenner für maximale WP-Kompatibilität

= 0.3.3.2 =
* Diagnostics: Robustere Fehlerbehandlung im Termine-AJAX-Handler (Logger, try/catch, Debug-Kontext)
* Debug: Zusätzliche Log-Ausgaben (Tenant, Kalenderauswahl, Zeitraum) für schnellere Ursachenanalyse
* Maintenance: Bereinigung des Repos – nur runtime-relevante Dateien in Distribution

= 0.3.3.1 =
* Verbesserung: Umfassendes Debug-Logging für Termine-Synchronisation
* Bugfix: AJAX-Fehler bei Sync werden jetzt detailliert im Debug-Panel angezeigt
* UX: Fehlerdetails (Status-Code, Response-Text) in Fehlermeldung inkludiert
* Debug: Console-Ausgabe mit vollständigem XHR-Objekt bei Verbindungsfehlern
* Debug: Stats-Logging auch im Erfolgsfall (Events + Appointments)

= 0.3.3.0 =
* Feature: Neuer Sync-Tab mit zentraler Synchronisations-Steuerung
* Feature: Kalenderauswahl direkt im Sync-Tab (mit Select-All-Funktion)
* Feature: Zeitraum-Konfiguration für Sync (Vergangenheit/Zukunft in Tagen)
* Feature: Events-Sync-Service mit Fallback-Endpunkten
* Feature: Appointments-Sync-Service mit Kalendermapping und Event-Verknüpfung
* Refactor: Dashboard zeigt nur noch Status-Informationen (keine Sync-Buttons)
* Verbessert: Zeitraum-Einstellungen persistent in WP-Optionen gespeichert
* Verbessert: Klare UX-Trennung zwischen Status-Anzeige und Sync-Aktionen
* Verbessert: AJAX-Handler nutzt konfigurierte Zeitraum-Einstellungen
* Neu: Repository-Hilfsmethoden für Kalender- und Event-Zuordnung

= 0.3.2.5 =
* Fix: CT_Client wird jetzt korrekt mit Credentials (tenant, username, password) instanziiert
* Fix: Behebt "Too few arguments to function" Fehler bei Kalender-Synchronisation
* Feature: Kopierfunktion für Debug-Log im Admin-Panel hinzugefügt
* Feature: Fallback-Kopiermethode für ältere Browser
* Verbessert: Passwort-Entschlüsselung wird im AJAX-Handler durchgeführt

= 0.3.2.4 =
* Debug: Erweiterte Fehlerbehandlung mit Try-Catch für Dependencies und PHP Errors
* Debug: Detaillierte error_log Ausgaben an jedem Schritt des AJAX-Handlers
* Debug: Separate Fehlerbehandlung für Exception und Error (PHP 7+)
* Debug: File, Line und vollständiger Stack Trace bei Fehlern
* Hilft bei der Diagnose von HTTP 500 Fehlern

= 0.3.2.3 =
* Bugfix: Logger-Klasse wird jetzt explizit im AJAX-Handler geladen
* Bugfix: ABSPATH-Check vor Logger-require in Admin-Klasse
* Behebt HTTP 500 Fehler bei Kalender-Synchronisation

= 0.3.2.2 =
* Feature: Zentrale Logger-Klasse für einheitliches Debug-Logging
* Direktes Schreiben ins wp-content/debug.log (unabhängig von WP_DEBUG)
* Kompatibel mit Debug-Log-Manager-Plugins für einfache Log-Anzeige
* Millisekunden-genaue Timestamps in allen Log-Einträgen
* Farbcodierte Icons (✅ ✗ ⚠️ ℹ️) für bessere Lesbarkeit in Logs
* Strukturierte Logs: header(), separator(), dump() Helper-Methoden
* Logging auf 3 Ebenen: AJAX Handler, Service Layer, CT Client
* Automatische Aktivierung von error_log falls nicht konfiguriert
* Log-Einträge mit Prefix "[REPRO CT-SUITE]" für einfaches Filtern

= 0.3.2.1 =
* Feature: Live-Debug-Panel direkt im Admin-Bereich (Settings-Tab)
* Echtzeit-Anzeige: Debug-Logs werden während der Synchronisation im Browser angezeigt
* Detaillierte Ausgaben: API-Request-URL, Response-Status, Statistiken, Fehler-Details
* Farbcodierte Logs: Info (blau), Erfolg (grün), Warnung (orange), Fehler (rot)
* Timestamp: Jede Log-Nachricht mit Millisekunden-genauem Zeitstempel
* Auto-Scroll: Automatisches Scrollen zu neuesten Einträgen
* Debug-Panel: Ein-/Ausblendbar, löschbar, persistent während der Session
* Keine WP_DEBUG erforderlich: Funktioniert out-of-the-box ohne wp-config.php Änderungen
* Browser-Konsole: Parallele Ausgabe in Browser-Konsole (F12) für technische Details

= 0.3.2.0 =
* DEBUG: Umfangreiche Debug-Ausgaben für Kalender-Synchronisation hinzugefügt
* Browser-Konsole: Detaillierte AJAX-Request/Response-Logs mit Debug-Informationen
* WordPress Debug-Log: Vollständige Protokollierung auf 3 Ebenen (AJAX Handler, Service Layer, CT Client)
* CT Client: HTTP-Request/Response-Details, Status-Codes, Headers, JSON-Decode-Fehler
* Calendar Sync Service: API-Call-Tracking, Response-Struktur, Import-Status pro Kalender
* AJAX Handler: Request-URL, Tenant, Zeitstempel, vollständige Statistiken und Fehler-Traces
* DEBUG.md: Ausführliche Dokumentation für Fehlerdiagnose und Support
* Debug-Informationen werden in AJAX-Response zurückgegeben für Frontend-Anzeige
* Alle Logs verwenden Prefix "[REPRO CT-SUITE DEBUG]" für einfaches Filtern

= 0.3.1.3 =
* Fix: Private GitHub-Assets können jetzt mit Token-Authentifizierung heruntergeladen werden
* Automatische Updates funktionieren jetzt vollständig bei privaten Repositories
* Download-Filter mit Authorization-Header für sichere Asset-Downloads

= 0.2.4.2 =
* Auto-Cleanup: Alte Plugin-Installationen werden bei Aktivierung automatisch bereinigt
* Entfernt alte Ordner wie "repro-ct-suite-clean", "-old", "-backup" automatisch
* Sicherheitsprüfungen: Nur inaktive Duplikate werden gelöscht
* Verbesserte Installationsstabilität

= 0.2.4.1 =
* Packaging: ZIP entspricht WP-Vorgaben (Top-Level-Ordner immer "repro-ct-suite")
* Build: Release-Asset ohne Versionsnummer (repro-ct-suite.zip)
* Intern: Versionierung weiterhin im Plugin enthalten (Header + Konstante), kein ?ver in CSS/JS-URLs

= 0.2.4 =
* Fix: GitHub-Token für Updates bei privatem Repository hinzugefügt
* Automatische Updates funktionieren jetzt auch für private GitHub-Repositories
* Hinweis: Dieses Update muss manuell installiert werden, danach funktionieren alle Updates automatisch

= 0.2.3 =
* Bugfix: Redirect nach Connection-Test bleibt nun im Settings-Tab
* Bugfix: Dashboard zeigt korrekten Verbindungsstatus basierend auf gespeicherten Credentials
* UI: Status-Punkt wechselt von gelb (nicht konfiguriert) zu grün (konfiguriert)
* UI: Button-Text passt sich dynamisch an ("Jetzt einrichten" vs "Einstellungen ändern")

= 0.2.2 =
* Bugfix: Headers-Already-Sent-Fehler beim Connection-Test behoben
* Connection-Test nutzt jetzt admin_init Hook statt direkte Template-Ausführung
* Post-Redirect-Get Pattern für Test-Ergebnisse via Transient

= 0.2.1 =
* ChurchTools Login-Service: Authentifizierung via Username/Passwort
* Tenant-basierte URL-Konstruktion (z.B. "gemeinde" → gemeinde.church.tools)
* Cookie-basierte Session-Verwaltung mit automatischem Re-Login
* Settings-UI: Tenant-Eingabe, Verbindungstest-Button
* Passwort-Eingabe: leer lassen behält gespeichertes Passwort bei
* API-Client mit GET-Methode und Fehlerbehandlung (401 → Re-Auth)

= 0.2.0 =
* Datenbankschema: benutzerdefinierte Tabellen für Events, Appointments und Services
* Repository-Pattern: Event, Appointment, EventServices Repositories mit Upsert-Logik
* Sichere Credentials: verschlüsseltes Speichern von ChurchTools-Passwörtern (Crypto-Klasse)
* Admin-Seite "Termine": konsolidierte Übersicht aller Events und Appointments ohne Event-Zuordnung
* Einstellungsseite: ChurchTools Basis-URL, Benutzername und Passwort konfigurierbar
* DB-Migrationen: automatische Schema-Installation und Upgrade-Hooks
* Vorbereitung für Sync-Service: Repository-Schicht implementiert

= 0.1.0.3 =
* Auto-Update-Funktion hinzugefügt (opt-in via Admin-UI)
* Update-Info-Seite mit Statusanzeige und Auto-Update-Toggle
* Versionsnummer-Support für 4-stellige Versionen (Major.Minor.Patch.Build)

= 0.1.0.2 =
* i18n-Kompatibilität mit WordPress 6.7.0 (Textdomain auf init geladen)
* GitHub-Updater: Versionsnormalisierung und Präferenz für Release-ZIP-Assets

= 0.1.0 =
* Initiales Release
* GitHub-Updater für automatische Plugin-Updates
* Material Design-inspirierte Admin-Oberfläche
* Template-basierte View-Architektur
* OOP-Struktur mit Loader-Pattern

= 1.0.0 =
* Legacy-Placeholder (veraltet)
