=== Repro CT-Suite ===

Contributors: fegaaschaffenburg

Tags: churchtools, calendar, events, appointments, sync

Requires at least: 5.0

Tested up to: 6.4

Requires PHP: 7.4

Stable tag: 0.9.6.6

License: GPLv2 or later

License URI: https://www.gnu.org/licenses/gpl-2.0.html



WordPress-Plugin zur Integration von ChurchTools-Daten. Synchronisiert Termine und Veranstaltungen aus ChurchTools fÃ¼r die Anzeige auf WordPress-Websites.



== Description ==



Repro CT-Suite erstellt eine BrÃ¼cke zwischen ChurchTools und WordPress. Das Plugin synchronisiert automatisch Termin- und Veranstaltungsdaten aus Ihrer ChurchTools-Instanz und macht sie Ã¼ber Shortcodes in WordPress verfÃ¼gbar.



**Begriffsdefinition:**

* **Events**: Veranstaltungen aus ChurchTools Events-API

* **Termine (Appointments)**: Einfache Termine aus ChurchTools Appointments (ohne Event-VerknÃ¼pfung)

* **Terminkalender**: GesamtÃ¼bersicht aller Events und Termine



**Hauptfunktionen:**



* Automatische Synchronisation von ChurchTools Events

* Synchronisation von Appointments (Termine ohne Event-VerknÃ¼pfung)

* GesamtÃ¼bersicht im Terminkalender (Events + Termine)

* Einfache Anzeige via Shortcodes

* Admin-OberflÃ¤che fÃ¼r Konfiguration

* WordPress Cron fÃ¼r automatische Updates

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



Loggen Sie sich in ChurchTools ein und gehen Sie zu Einstellungen > Sicherheit > API-Tokens. Dort kÃ¶nnen Sie einen neuen Token erstellen.



= Wie oft werden die Daten synchronisiert? =



Das Plugin nutzt WordPress Cron und synchronisiert standardmÃ¤ÃŸig alle 6 Stunden. Sie kÃ¶nnen auch manuell synchronisieren.



= Welche ChurchTools-Versionen werden unterstÃ¼tzt? =



Das Plugin ist fÃ¼r aktuelle ChurchTools-Versionen mit REST API entwickelt.



= Was passiert beim Deinstallieren des Plugins? =



Beim Entfernen (LÃ¶schen) des Plugins Ã¼ber die WordPress-Pluginverwaltung werden automatisch alle zugehÃ¶rigen Daten bereinigt:



- Plugin-Optionen (Tenant, Benutzername, Passwort, Session/Cookies, Sync-Einstellungen, DB-Versionsoption)

- Eigene Datenbanktabellen: rcts_calendars, rcts_events, rcts_appointments, rcts_event_services



In Multisite-Installationen werden die Daten auf allen Sites bereinigt.



== Screenshots ==



1. Admin-Einstellungsseite

2. Termine-Anzeige im Frontend

3. Events-Anzeige im Frontend



== Changelog ==


= 0.9.6.6 - 2024-12-08 =
* Fix: KRITISCH - Debug-Buttons "Leeren" und "Anzeigen" funktionierten nicht
* Fix: JavaScript-Initialisierung prüft jetzt auch auf tab=debug URL-Parameter
* Ursache: URL-Check war zu spezifisch (nur 'repro-ct-suite-debug', nicht 'tab=debug')
* Betroffen: Beide Debug-JavaScript-Dateien (debug.js + debug-extensions.js)

= 0.9.6.5 - 2024-12-08 =
* Fix: Gutenberg Block - apiVersion 3 hinzugefügt (behebt WordPress 6.3+ Warnung)
* Fix: Doppelte IDs behoben - select-all-calendars jetzt eindeutig (settings/sync)
* Verbesserung: Browser-Konsolenfehler eliminiert
* Optimierung: Alle Checkbox-IDs sind jetzt eindeutig über alle Tabs hinweg

= 0.9.6.4 - 2024-12-08 =
* Fix: "Leeren"-Button funktioniert jetzt für Shortcode-Vorlagen (rcts_shortcode_presets)
* Fix: "Anzeigen"-Button zeigt jetzt Daten aus Shortcode-Vorlagen
* Verbesserung: Debug-Warnung erscheint nur noch bei UNBEKANNTEN Tabellen (nicht bei allen gefundenen)
* Optimierung: Redundante Warnungen entfernt - bekannte Tabellen werden nicht mehr als "zusätzlich" gemeldet

= 0.9.6.3 - 2024-12-08 =
* Fix: Shortcode-Vorlagen (rcts_shortcode_presets) jetzt im Debug-Tab sichtbar
* Fix: Legacy-Tabelle rcts_appointments wird nur angezeigt wenn vorhanden
* Feature: Legacy-Tabellen werden mit orangem Badge und gelber Hintergrundfarbe markiert
* Verbesserung: Debug-Warnung aktualisiert - 5 Tabellen werden erwartet

= 0.9.6.2 - 2024-12-08 =
* Fix: DB-Management-Features im Debug-Tab jetzt sichtbar
* Fix: "Anzeigen" und "Leeren" Buttons für Datenbank-Tabellen
* Fix: "Vollständiger Plugin-Reset" Button wieder verfügbar
* Verbesserung: Tab-Struktur konsolidiert (tab-debug.php hat jetzt alle Features)
= 0.9.6.1 - 2024-12-08 =
* Update: Lizenz-Tab zeigt Spendenaufruf statt Lizenzverwaltung
* Feature: Plugin ist kostenlos nutzbar ohne Lizenz
* Feature: Direkter Spendenlink zu /spenden/
* Info: Alle Features ohne Lizenz verfügbar

= 0.9.6.0 - 2024-12-08 =
* Fix: Debug-Tab JavaScript wird jetzt korrekt geladen
* Fix: Anzeigen und Löschen von DB-Einträgen funktioniert jetzt
* Fix: Debug-Extensions laden bei tab=debug

= 0.9.5.9 - 2024-12-08 =
* Fix: Updater-Hook nur noch bei privatem Repository registriert
* Fix: Verhindert Konflikte bei öffentlichen Downloads
* Hinweis: Bei Upload-Problemen manuelle FTP-Installation verwenden

= 0.9.5.8 - 2024-12-08 =
* Fix: Kritischer Bugfix im Updater - Updates funktionieren jetzt korrekt
* Fix: Fehlerhafte Bedingung in download_package() korrigiert
* Fix: 'Aktualisierung fehlgeschlagen' Fehler behoben

= 0.9.5.7 - 2024-12-08 =
* Feature: Neues Lizenz-Tab mit Lizenzverwaltung
* Fix: Korrektur der Plugin-Action-Links (Dashboard, Einstellungen, Lizenz)
* Verbesserung: Lizenzaktivierung, Deaktivierung und Prüfung per AJAX

= 0.9.5.6 - 2024-12-08 =
* Feature: Plugin-Action-Links (Dashboard, Einstellungen, Lizenz) im Plugins-Bereich
* Verbesserung: Schnellzugriff auf wichtige Plugin-Bereiche

= 0.9.5.5 - 2024-12-08 =
* Fix: Warning bei unlink() in delete_directory_recursive
* Fix: Zusätzliche Existenzprüfungen vor Datei-/Ordnerlöschung

= 0.9.5.4 - 2024-12-08 =
* Fix: Cache-Versionierung für Debug-JavaScript
* Test: Update-Mechanismus und neue Debug-Funktionen

= 0.9.5.3 - 2024-12-08 =
* Neu: Debug-Funktion zum Anzeigen einzelner Datensätze in Tabellen
* Neu: Einzelne Datensätze können im Debug-Bereich gelöscht werden
* Neu: Vollständiger Plugin-Reset-Button im Debug-Bereich
* Verbesserung: Modal-Dialog für bessere Datenansicht
* UI: Neue Buttons "Anzeigen" für alle Datenbank-Tabellen

= 0.9.5.2 - 2024-12-08 =

* Code-Verbesserungen: PHP 7.4+ Type Hints fÃ¼r bessere Code-QualitÃ¤t

* Refactoring: Repository- und Service-Klassen mit strikter Typisierung

* Wartung: Code-Formatierung und Konsistenz-Verbesserungen

* Optimierung: Verbesserte Fehlerbehandlung in allen Service-Klassen



= 0.9.1.0 =

* **Security Enhancement**: Rate Limiting fÃ¼r API-Calls implementiert

* **Security Enhancement**: Erweiterte Input-Validierung mit XSS-Schutz

* **Quality Assurance**: PHPUnit Test Framework mit WordPress-Integration

* **Documentation**: VollstÃ¤ndiges User Manual und Security-Checkliste

* **Production Ready**: Erste vollstÃ¤ndig produktionsreife Version



= 0.8.5 =

* BUGFIX: Admin-Ãœbersicht zeigt jetzt korrekte Kalendernamen

* Admin-Seite "Termine" (page=repro-ct-suite-events) verwendet jetzt get_by_calendar_id() statt get_by_id()

* Behebt Problem: Kalendernamen wurden in der Admin-Tabelle nicht angezeigt

* Betrifft nur Admin-Bereich, Frontend war bereits mit v0.8.3 korrekt



= 0.8.4 =

* FEATURE: Automatische Update-Benachrichtigungen von GitHub

* WordPress erkennt jetzt automatisch neue Releases

* 1-Klick-Update direkt aus dem WordPress-Admin

* Update-PrÃ¼fung alle 5 Minuten (mit Cache)

* UnterstÃ¼tzt Ã¶ffentliche und private GitHub-Repositories

* Keine zusÃ¤tzlichen Plugins erforderlich

* Optimiertes Cache-Management (keine Ã¼bermÃ¤ÃŸigen API-Anfragen)



= 0.8.3 =

* KRITISCHER FIX: Kalender-Zuordnung zu Events korrigiert (2 Bugs behoben)

* Bug 1: SQL JOIN verwendet jetzt korrekte Spalte: c.calendar_id statt c.id

* Bug 2: Kalender-Filter behandelt calendar_ids jetzt als VARCHAR statt Integer

* Behebt Problem: Kalender-Namen und -Farben wurden nicht angezeigt

* Behebt Problem: Shortcode-Parameter calendar_ids="1,2,3" funktionierte nicht

* Betrifft Frontend-Shortcode [rcts_events]

* Grund: wp_rcts_calendars hat id (BIGINT, auto-increment) UND calendar_id (VARCHAR, ChurchTools-ID)

* Events speichern calendar_id als VARCHAR, daher mÃ¼ssen JOIN und Filter VARCHAR-basiert sein



= 0.8.2 =

* FEATURE: Preset-Shortcode - Verwendung von [rcts_events preset="Name"] statt langer Parameter

* Shortcode-Handler: `preset` Parameter lÃ¤dt gespeicherte Konfiguration

* UI: Checkbox "Preset-Shortcode verwenden" im Generator

* Preset-Werte dienen als Defaults, Parameter-Override mÃ¶glich

* Beispiel: [rcts_events preset="NÃ¤chste 10 Events" limit="20"]

* Fehlerbehandlung: Zeigt Warnung wenn Preset nicht gefunden

* currentPresetName wird beim Speichern/Laden gesetzt



= 0.8.1 =

* FEATURE: Shortcode-Presets - Speichern Sie Ihre Lieblings-Konfigurationen

* Neue Datenbank-Tabelle wp_rcts_shortcode_presets

* Preset-Manager im Shortcode Generator: Speichern, Laden, LÃ¶schen

* 5 vordefinierte Standard-Presets beim ersten Aktivieren

* Repository-Klasse fÃ¼r CRUD-Operationen

* AJAX-Handler fÃ¼r Preset-Verwaltung (save, load, update, delete)

* UI: Preset-Dropdown mit Laden/LÃ¶schen-Buttons

* "Als Preset speichern" Button im Generator

* Migration V9 erstellt Standard-Presets automatisch



= 0.8.0 =

* MAJOR FEATURE: Admin-Seite "Anzeige im Frontend" (Phase 3 des Frontend-Plans)

* Shortcode Generator mit Live-Vorschau und Copy-Button

* Visueller Konfigurator fÃ¼r alle Shortcode-Attribute

* Template-Varianten Ãœbersicht mit Dokumentation

* Styling-Referenz mit CSS-Klassen und Beispielen

* Interaktive Live-Vorschau verschiedener Konfigurationen

* 4 Tabs: Shortcode Generator, Template-Varianten, Styling, Vorschau

* AJAX-basierte Vorschau ohne Seiten-Reload

* Theme-Override Anleitung mit verfÃ¼gbaren Variablen



= 0.7.4 =

* FEATURE: WordPress-Zeitformat-UnterstÃ¼tzung in Templates

* Zeiten zeigen "Uhr" bei 24h-Format (z.B. "14:30 Uhr")

* Zeiten zeigen AM/PM bei 12h-Format (z.B. "2:30 PM")

* Automatische Erkennung Ã¼ber WordPress Einstellung (Settings â†’ General â†’ Time Format)

* Betrifft alle 3 Templates: list-simple, list-grouped, cards

* Verwendet time_formatted statt hartkodiertem H:i Format



= 0.7.3 =

* KRITISCHER FIX: Template-Fehler "Undefined property: $event->name" behoben

* Templates: $event->name â†’ $event->title (DB-Feldname)

* Templates: $event->location â†’ $event->location_name (DB-Feldname)

* Betrifft alle 3 Templates: list-simple.php, list-grouped.php, cards.php

* Behebt PHP Warnings im Frontend-Shortcode



= 0.7.2 =

* KRITISCHER FIX: SQL-Fehler "Column 'calendar_id' in WHERE is ambiguous" behoben

* Shortcode-Query: Alle WHERE-Bedingungen verwenden jetzt Tabellen-PrÃ¤fix (e.calendar_id, e.start_datetime)

* Behebt Fehler beim Shortcode [rcts_events calendar_ids="1,2"]



= 0.7.1 =

* KRITISCHER FIX: Automatischer Sync (Cron-Job) funktional

* Cron-Job lÃ¤dt jetzt korrekt ausgewÃ¤hlte Kalender-IDs aus der Datenbank

* Sync-Zeitraum: 7 Tage Vergangenheit bis 90 Tage Zukunft

* Behebt Fehler: "Keine Kalender fÃ¼r den Import ausgewÃ¤hlt"



= 0.7.0 =

* MAJOR FEATURE: Frontend Events-Anzeige mit Shortcode [rcts_events]

* Template-System: 3 Ansichten (list, list-grouped, cards)

* Template-Loader mit Theme-Override Support (themes/repro-ct-suite/)

* Responsive Frontend CSS (Grid-Layout, Mobile-optimiert)

* Flexible Filter: calendar_ids, from_days, to_days, limit, order

* Konfigurierbare Felder: show_fields="title,date,time,location,description,calendar"

* Shortcode-Beispiele: [rcts_events view="cards" limit="12"]

* Phase 1 von 7 des Frontend-Entwicklungsplans abgeschlossen



= 0.6.1.0 =

* FEATURE: WordPress-Zeitzone fÃ¼r alle Datum/Uhrzeit-Anzeigen

* wp_date() statt date_i18n() fÃ¼r korrekte Zeitzonenkonvertierung

* Dashboard: NÃ¤chster Sync, Letzte AusfÃ¼hrung in lokaler Zeit

* Settings: Cron-Status-Zeiten in WordPress-Zeitzone

* Behebt: Zeiten wurden in UTC statt lokaler Zeitzone angezeigt



= 0.6.0.9 =

* KRITISCHER FIX: Aggressive Update-Cache Clearing fÃ¼r wp-admin/plugins.php

* WordPress Plugin-Seite zeigt Updates jetzt korrekt an

* Cache-Clearing bei jedem Admin-Besuch (update_plugins, plugins_cache)

* Behebt Problem dass Updates nur im Plugin-Tab erkennbar waren



= 0.6.0.8 =

* KRITISCHER FIX: UTF-8 BOM aus 5 PHP-Dateien entfernt (JSON parse error behoben)

* Login-Test: VollstÃ¤ndige Logging-UnterstÃ¼tzung hinzugefÃ¼gt

* Debug: Detaillierte Ausgaben fÃ¼r Login-Ablauf (Credentials, CT-Client, Login, whoami)

* Verbesserte Fehlerdiagnose bei Verbindungsproblemen



= 0.4.0.9 =

* UPDATE-DETECTION: Aggressive Update-PrÃ¼fung mit Admin-Benachrichtigung

* Zeigt aktuelle Plugin-Version im WordPress Admin an

* LÃ¶scht alle Update-Caches bei jedem Admin-Besuch

* Admin-Notice mit direktem Link zur Update-PrÃ¼fung



= 0.4.0.8 =

* AUTO-UPDATE: Erzwungene Update-PrÃ¼fung fÃ¼r bessere Plugin-Erkennung

* Update-Cache wird bei Admin-Besuch geleert

* Verbesserte GitHub-Release-Erkennung

* Behebt Problem dass WordPress Updates nicht anzeigt



= 0.4.0.7 =

* KRITISCHER FIX: Events API-Aufruf korrigiert fÃ¼r ChurchTools

* Abrufen ALLER Events ohne calendar_ids Parameter

* Clientseitige Filterung nach domainIdentifier

* API-Parameter: direction=forward, include=eventServices, page=1

* Behebt Problem dass Events mit falschen calendar_ids abgerufen wurden



= 0.4.0.6 =

* DEBUG: Erweiterte Kalender-Debug-Logs fÃ¼r bessere Diagnose

* Detaillierte Ausgabe der calendar-Objekt Struktur in Events

* Zeigt verfÃ¼gbare Keys und Werte fÃ¼r domainIdentifier/id

* Hilft bei der Identifikation von Struktur-Problemen



= 0.4.0.5 =

* KRITISCHER FIX: Events Filter-Logik korrigiert fÃ¼r ChurchTools API

* Events Kalender-ID wird jetzt korrekt aus calendar.domainIdentifier gelesen

* Fallback-Support fÃ¼r Ã¤ltere calendar.id Struktur beibehalten

* 5-stufige Kalender-PrÃ¼fung: domainIdentifier, id, calendars[], appointment.calendar

* Behebt Problem dass alle Events Ã¼bersprungen wurden



= 0.4.0.4 =

* **SIMPLIFICATION**: Sync-Prozess auf Phase 1 (Events API) beschrÃ¤nkt fÃ¼r bessere Diagnose

* **Change**: Phase 2 (Appointments API) temporÃ¤r deaktiviert - fokussiert auf Events-Import

* **Debug**: Vereinfachter Sync-Workflow fÃ¼r einfachere Fehlerdiagnose und Debugging

* **Logging**: Klarere Log-Ausgaben "EVENTS-ONLY SYNC" mit Hinweis auf deaktivierte Phase 2

* **Performance**: Reduzierter API-Traffic durch Fokus auf Events-Synchronisation

* **Diagnosis**: ErmÃ¶glicht isolierte Analyse der Events-Filterlogik ohne Appointments-KomplexitÃ¤t

* **Temporary**: Appointments-Sync wird in zukÃ¼nftiger Version wieder aktiviert

* Version: 0.4.0.4



= 0.4.0.3 =

* **CRITICAL FIX**: Events-Filterlogik korrigiert - alle Events wurden Ã¼bersprungen

* **Fix**: Erweiterte Kalender-Zuordnung prÃ¼ft multiple Event-Strukturen (`calendar.id`, `calendarId`, `appointment.calendar.id`)

* **Debug**: Umfassendes Debug-Logging fÃ¼r Event-Kalender-Zuordnung zur Diagnose von Sync-Problemen

* **Improvement**: 4-stufige PrÃ¼fung der Event-Kalender-VerknÃ¼pfung fÃ¼r maximale API-KompatibilitÃ¤t

* **Logging**: Detaillierte Struktur-Analyse mit verfÃ¼gbaren Keys bei unbekannten Event-Formaten

* **Critical**: Behebt Problem wo "Event 2008 nicht relevant fÃ¼r Kalender 1" alle Events ausfilterte

* Version: 0.4.0.3



= 0.4.0.2 =

* **Debug**: Debug-Seite zeigt Warnung bei ungewÃ¶hnlichen/doppelten Tabellen

* **Feature**: Automatische Erkennung von Plugin-Tabellen mit ungewÃ¶hnlichen WordPress-Prefixen

* **Fix**: Diagnose-Tool fÃ¼r doppelte Event-Services Anzeige hinzugefÃ¼gt

* **UI**: Debug-Warnung wird nur angezeigt, wenn mehr als 5 Plugin-Tabellen gefunden werden

* **Maintenance**: Verbesserte Debug-Informationen mit Tabellenliste und WordPress-Prefix-Anzeige

* **Investigation**: Hilft bei der Diagnose von Tabellen-Duplikaten durch verschiedene Installationen

* Version: 0.4.0.2



= 0.4.0.1 =

* **Fix**: Debug-Seite zeigt jetzt alle korrekten Tabellen fÃ¼r DB Version 6

* **Feature**: rcts_schedule Tabelle in Debug-Ãœbersicht hinzugefÃ¼gt

* **Fix**: AJAX-Handler fÃ¼r Tabellen-Reset vollstÃ¤ndig mit neuer Schedule-Tabelle kompatibel

* **UI**: Debug-Seite zeigt "Terminkalender (Schedule)" mit admin-page Icon

* **Maintenance**: Konsistente Tabellenliste in Debug-View, AJAX-Handlers und Uninstall-Funktion

* **Verification**: Alle LÃ¶sch-Funktionen (einzeln und komplett) funktionieren mit DB V6

* Version: 0.4.0.1



= 0.4.0.0 =

* **MAJOR UPDATE**: Neues einheitliches Sync-System mit intelligenter 2-Phasen-Architektur

* **BREAKING**: Alte separate Events- und Appointments-Sync-Services durch einheitlichen Sync-Service ersetzt

* **Feature**: Intelligente Duplikats-Vermeidung durch appointment_id-Tracking zwischen Events und Appointments APIs

* **Feature**: Automatische Datenbank-Migration V6 mit Schema-Updates und Orphaned-Appointment-Migration

* **Architecture**: Simplified codebase - von 600+ Zeilen dual sync auf 304 Zeilen unified sync reduziert

* **UX**: Admin-UI-AufrÃ¤umung - redundante Appointment-Handler entfernt, konsolidierte MenÃ¼-Struktur

* **Migration**: Automatische Bereinigung von verwaisten Appointments (bis zu 50 automatisch, manuelle Migration fÃ¼r grÃ¶ÃŸere Datenmengen)

* **Logging**: Umfassendes Logging fÃ¼r beide Sync-Phasen mit detaillierter Statistik

* **Admin**: Neue Admin-Benachrichtigung fÃ¼r V6-Upgrade mit Dismissal-FunktionalitÃ¤t

* **Workflow**: Phase 1 (Events API) sammelt appointment_ids, Phase 2 (Appointments API) importiert zusÃ¤tzliche Termine

* **Performance**: Reduzierte Code-KomplexitÃ¤t und verbesserte Wartbarkeit

* **Safety**: Umfangreiche Migrations-SicherheitsprÃ¼fungen mit automatischem Rollback bei Fehlern

* WICHTIG: Nach Update ersten Sync ausfÃ¼hren, um neues einheitliches System zu aktivieren

* Version: 0.4.0.0



= 0.3.7.0 =

* Feature: Neue DB-Tabelle rcts_schedule als konsolidierte TerminÃ¼bersicht (Events + Appointments)

* Feature: Neue Admin-Seite "TerminÃ¼bersicht" mit Filtern (Zeitraum, Kalender, Typ)

* Ã„nderung: Appointments-Sync setzt calendar_id jetzt strikt aus dem aufrufenden Endpoint (/calendars/{calendarId}/appointments)

* Ã„nderung: Beide Sync-Services (Events & Appointments) befÃ¼llen die neue TerminÃ¼bersicht automatisch

* Wartung: Uninstall bereinigt nun auch rcts_schedule-Tabelle

* Version: 0.3.7.0



= 0.3.7.1 =

* Fix: Strengere Kalender-Filterung bei Events - Events ohne `calendar_id` werden beim Einsatz eines Kalender-Filters nun verworfen, um Import von nicht-ausgewÃ¤hlten Kalendern zu vermeiden.

* Fix: Appointments-Statistiken (inserted/updated) werden jetzt korrekt ermittelt (Existenz vor Upsert geprÃ¼ft).

* Wartung: Kleine Logging-Verbesserungen fÃ¼r den Sync-Prozess.

* Version: 0.3.7.1



= 0.3.8.0 =

* Feature: Optionales Syslog-Output fÃ¼r Debug-Logging (aktivierbar in den Plugin-Einstellungen).

* Feature: Logs-Tab zeigt nun den Inhalt von `wp-content/repro-ct-suite-debug.log` (letzte 100 Zeilen) inkl. Clear/Refresh-Buttons.

* Fix: Sicherstellung, dass Debug-JavaScript auch auf dem Logs-Tab geladen wird.

* Wartung: Kleine Verbesserungen am Logger (syslog-Fallback, weiterhin plugin-spezifische Datei als zuverlÃ¤ssiges Log).

* Version: 0.3.8.0



 = 0.3.9.2 =

* Fix: Corrected release asset for v0.3.9.1 (was corrupted/oversized). This is a re-release with the same functionality.

* Maintenance: Patch release to validate online update; contains the schedule-repository syntax fix and logging updates from prior commits.

* Note: Please run a Sync after updating to ensure DB state and OPcache are refreshed.

* Version: 0.3.9.2



 = 0.3.9.1 =

* Maintenance: Patch release to validate online update; contains the schedule-repository syntax fix and logging updates from prior commits.

* Note: Please run a Sync after updating to ensure DB state and OPcache are refreshed.

* Version: 0.3.9.1



= 0.3.9.0 =

* Fix: Syntax error fix â€” `rebuild_from_existing()` method moved into `Repro_CT_Suite_Schedule_Repository` class (stability fix for Termine-Sync).

* Maintenance: Release includes log-viewer and syslog support improvements from previous commit.

* Version: 0.3.9.0



= 0.3.6.2 =

* **CRITICAL FIX**: Events-Sync filtert jetzt nach ausgewÃ¤hlten Kalendern (is_selected)

* **CRITICAL FIX**: calendar_id wird jetzt korrekt bei Events gespeichert

* Fix: Events-Sync akzeptiert calendar_ids Parameter (externe ChurchTools IDs)

* Fix: NachtrÃ¤gliche Filterung von Events nach ausgewÃ¤hlten Kalendern

* Feature: Externe Calendar-IDs werden aus lokalen IDs konvertiert fÃ¼r Events-Filter

* Logging: Neue Log-EintrÃ¤ge zeigen gefilterte vs. verarbeitete Events

* Stats: Events-Sync liefert jetzt total, filtered, processed, inserted, updated, errors

* Performance: Nur Events von ausgewÃ¤hlten Kalendern werden importiert

* WICHTIG: Nach Update Synchronisation ausfÃ¼hren, um alte Events aus nicht-ausgewÃ¤hlten Kalendern zu bereinigen

* Version: 0.3.6.2



= 0.3.6.1 =

* Feature: CRUD-Funktionen fÃ¼r einzelne Events und Appointments

* Feature: LÃ¶sch-Buttons in Terminkalender-Ãœbersicht und Events-Ãœbersicht

* Feature: AJAX-Handler fÃ¼r Delete und Update (ajax_delete_event, ajax_delete_appointment, ajax_update_event, ajax_update_appointment)

* Repository: Neue Basisfunktionen in Repository-Base (get_by_id, delete_by_id, update_by_id, exists)

* UI: Neue "Aktionen"-Spalte mit LÃ¶sch-Button in Events/Appointments-Tabellen

* UX: BestÃ¤tigungsdialog vor dem LÃ¶schen mit Titel-Anzeige

* JavaScript: initDeleteButtons() fÃ¼r Event-Delegation der LÃ¶sch-Buttons

* JavaScript: AJAX-Handler fÃ¼r Migrations- und Calendar-ID-Fix-Buttons hinzugefÃ¼gt

* Sicherheit: Nonce-PrÃ¼fung und BerechtigungsprÃ¼fung fÃ¼r alle AJAX-Handler

* Version: 0.3.6.1



= 0.3.6.0 =

* **BREAKING**: DB-Schema-Update auf Version 4

* Fix: calendar_id Spalte von BIGINT auf VARCHAR(64) geÃ¤ndert

* Fix: Events-Sync setzt jetzt calendar_id (externe ChurchTools Calendar-ID)

* Fix: Appointments-Sync speichert externe Calendar-ID statt interner WordPress-ID

* Fix: Kalenderfilter funktioniert jetzt korrekt (verwendet externe IDs)

* Datenbank: Automatische Migration bei Plugin-Update

* WICHTIG: Nach Update einmal Synchronisation ausfÃ¼hren, um calendar_id zu fÃ¼llen

* Version: 0.3.6.0



= 0.3.5.8 =

* Fix: Kalenderfilter funktioniert jetzt korrekt (verwendet get_by_external_id statt get_by_id)

* Fix: Typ-Anzeige korrigiert - Events und Termine werden jetzt korrekt unterschieden

* UI: Komplett neue Spaltenstruktur in Terminkalender-Ãœbersicht

* UI: Neue Spaltenreihenfolge: Anfang, Ende, Titel, Beschreibung, Kalender, Typ

* UI: Kalender in eigener Spalte (nicht mehr unter Titel)

* UX: Beschreibung wird gekÃ¼rzt angezeigt (10 WÃ¶rter)

* Spaltenbreiten: Anfang (12%), Ende (12%), Titel (25%), Beschreibung (25%), Kalender (16%), Typ (10%)

* Version: 0.3.5.8



= 0.3.5.7 =

* Fix: Translation loading timing korrigiert (WordPress 6.7.0 compatibility)

* Fix: Textdomain wird jetzt auf 'plugins_loaded' statt 'init' geladen

* KompatibilitÃ¤t: Behebt "Translation loading triggered too early" Notice

* Version: 0.3.5.7



= 0.3.5.6 =

* Feature: Zweistufiger Reset-Prozess

* UX: Nach LÃ¶schen der Zugangsdaten Abfrage fÃ¼r vollstÃ¤ndigen Reset

* Feature: VollstÃ¤ndiger Reset lÃ¶scht alle Daten (Kalender, Events, Appointments, Services)

* AJAX: Neuer Handler ajax_full_reset() fÃ¼r kompletten Datenbankreset

* Sicherheit: Deutliche Warnhinweise bei vollstÃ¤ndigem Reset

* Version: 0.3.5.6



= 0.3.5.5 =

* Fix: Kalenderfilter in Terminkalender-Ãœbersicht funktioniert jetzt korrekt

* Fix: Termine zeigen jetzt korrekten Typ (Event/Termin) statt nur "event"

* UI: Ort und Status aus Ãœbersicht entfernt (cleaner)

* Feature: Datum/Uhrzeit werden gemÃ¤ÃŸ WordPress-Zeitzone angezeigt

* UX: Spaltenbreiten in Ãœbersicht optimiert

* Version: 0.3.5.5



= 0.3.5.4 =

* UI: Separate "Termine-Sync" Subpage entfernt

* UI: Synchronisation komplett im Dashboard-Tab integriert

* UX: Noch weiter vereinfachte MenÃ¼-Struktur

* Navigation: Nur noch Dashboard (mit Tabs) + Terminkalender + Update + Debug

* Version: 0.3.5.4



= 0.3.5.3 =

* Feature: Reset-Button fÃ¼r Zugangsdaten im Einstellungen-Tab

* UI: Separate Einstellungen-Seite entfernt - alles zurÃ¼ck im Dashboard

* UI: Vereinfachte Navigation - nur noch Dashboard + bedingte Seiten

* AJAX: Neuer Handler ajax_reset_credentials() zum LÃ¶schen aller Login-Daten

* UX: BestÃ¤tigungs-Dialog vor dem LÃ¶schen der Zugangsdaten

* Version: 0.3.5.3



= 0.3.5.2 =

* UI: Kalender-Auswahl zurÃ¼ck in Einstellungen-Seite integriert

* UI: Separate Kalender-Seite entfernt (redundant)

* UX: Vereinfachte Navigation - weniger MenÃ¼punkte

* Version: 0.3.5.2



= 0.3.5.1 =

* Fix: GitHub Release ZIP-Struktur korrigiert fÃ¼r WordPress-Installation

* Version: 0.3.5.1



= 0.3.5.0 =

* UI: Komplette Umstrukturierung der Admin-OberflÃ¤che

* Menu: Tabs jetzt als separate MenÃ¼punkte mit bedingter Sichtbarkeit

* Menu: Kalender-Tab erscheint nur bei konfigurierter Verbindung

* Menu: Termine-Sync-Tab erscheint nur bei ausgewÃ¤hlten Kalendern

* Settings: Einstellungen aufgeteilt in 3 Sektionen (Verbindung, Abrufzeitraum, Automatisierung)

* Kalender: Eigene Seite fÃ¼r Kalender-Sync und Auswahl

* Sync: Eigene Seite fÃ¼r Termine-Synchronisation mit Ãœbersicht

* Feature: Letzter Sync-Zeitpunkt wird gespeichert und angezeigt

* Feature: Sync-Statistiken werden persistent gespeichert

* UX: Verbesserte Navigation und klare Trennung der Funktionen

* Version: 0.3.5.0



= 0.3.4.1 =

* Fix: Events-Duplikate behoben - 2-Stufen-Sync-Strategie

* Sync: Events werden zuerst synchronisiert (aus /events)

* Sync: Appointments-Sync Ã¼berspringt Appointments, die bereits als Event existieren (prÃ¼ft appointment_id)

* Sync: Events extrahieren appointment_id aus API-Response

* Stats: Neue Statistik 'skipped_has_event' fÃ¼r Ã¼bersprungene Appointments

* Version: 0.3.4.1



= 0.3.4.0 =

* Feature: Eigene Debug-Seite mit erweiterten Funktionen

* Debug: Einzelne oder alle Tabellen zurÃ¼cksetzen (mit BestÃ¤tigung)

* Debug: Debug-Log-Anzeige mit Syntax-Highlighting (letzte 100 Zeilen)

* Debug: Log leeren und aktualisieren

* Debug: Datenbank-Migrationen manuell ausfÃ¼hren

* Debug: System-Informationen (WordPress, PHP, MySQL, Memory Limit)

* Debug: Tabellen-Statistik mit ZÃ¤hlung der EintrÃ¤ge

* UI: Debug-Bereich vom Dashboard in eigene Seite verschoben

* Version: 0.3.4.0



= 0.3.3.8 =

* Schema: `rcts_events` hat jetzt `appointment_id` fÃ¼r Verlinkung zu Appointments (DB_VERSION 3)

* Appointments-Sync: Speichert sowohl Events als auch Appointments-EintrÃ¤ge (vollstÃ¤ndige Datenstruktur)

* Terminkalender: Zeigt Events (aus Events-API) + Termine (Appointments ohne Event-VerknÃ¼pfung)

* Wording: "Terminkalender" mit Art-Badge: Event (blau) vs Termin (grÃ¼n)

* UI: Dashboard zeigt "Termine gesamt", MenÃ¼ "Terminkalender"

* Appointments mit event_id werden in Events gespeichert und mit Appointment verknÃ¼pft



= 0.3.3.7 =

* Appointments: Umgestellt auf pro-Kalender-Abruf `GET /calendars/{id}/appointments?from&to` (Aggregation aller ausgewÃ¤hlten Kalender)

* Robust: Besseres Logging je Kalenderabruf; weiche WeiterfÃ¼hrung bei 400/404/405



= 0.3.3.6 =

* Fix: Appointments-API nutzt jetzt nur noch GET (kein POST mehr; 405) und testet mehrere Query-Formate

	- Versuche: calendarIds[]=IDâ€¦; calendars[]=IDâ€¦; calendarIds=1,2,3; calendars=1,2,3; zuletzt Standard-Array via add_query_arg

	- Verbesserte Logs: exakte URL-Ausgabe pro Versuch



= 0.3.3.5 =

* DX: Client sendet jetzt zusÃ¤tzlich `Accept: application/json` Header

* Events: GET-Aufruf mit `direction=forward` und `include=eventServices` ergÃ¤nzt (wie API-Beispiel)



= 0.3.3.4 =

* Fix: Appointments-API akzeptiert jetzt JSON-Body mit `calendar_ids` (POST-Fallback), wenn GET-Varianten 400/404 liefern

* Log: AusfÃ¼hrliche Logs fÃ¼r GET/POST-Versuche inkl. Parameternamen



= 0.3.3.3 =

* Fix: "Cannot use object of type WP_Error as array" im Termine-AJAX-Handler behoben (robuste is_wp_error()-PrÃ¼fung und strukturierte FehlerrÃ¼ckgabe)

* DX: Sicherere Erfolgs-Statistik (Default-Werte bei fehlenden Keys)

* Packaging: ZIP-EintrÃ¤ge nutzen konsistent "/"-Pfadtrenner fÃ¼r maximale WP-KompatibilitÃ¤t



= 0.3.3.2 =

* Diagnostics: Robustere Fehlerbehandlung im Termine-AJAX-Handler (Logger, try/catch, Debug-Kontext)

* Debug: ZusÃ¤tzliche Log-Ausgaben (Tenant, Kalenderauswahl, Zeitraum) fÃ¼r schnellere Ursachenanalyse

* Maintenance: Bereinigung des Repos â€“ nur runtime-relevante Dateien in Distribution



= 0.3.3.1 =

* Verbesserung: Umfassendes Debug-Logging fÃ¼r Termine-Synchronisation

* Bugfix: AJAX-Fehler bei Sync werden jetzt detailliert im Debug-Panel angezeigt

* UX: Fehlerdetails (Status-Code, Response-Text) in Fehlermeldung inkludiert

* Debug: Console-Ausgabe mit vollstÃ¤ndigem XHR-Objekt bei Verbindungsfehlern

* Debug: Stats-Logging auch im Erfolgsfall (Events + Appointments)



= 0.3.3.0 =

* Feature: Neuer Sync-Tab mit zentraler Synchronisations-Steuerung

* Feature: Kalenderauswahl direkt im Sync-Tab (mit Select-All-Funktion)

* Feature: Zeitraum-Konfiguration fÃ¼r Sync (Vergangenheit/Zukunft in Tagen)

* Feature: Events-Sync-Service mit Fallback-Endpunkten

* Feature: Appointments-Sync-Service mit Kalendermapping und Event-VerknÃ¼pfung

* Refactor: Dashboard zeigt nur noch Status-Informationen (keine Sync-Buttons)

* Verbessert: Zeitraum-Einstellungen persistent in WP-Optionen gespeichert

* Verbessert: Klare UX-Trennung zwischen Status-Anzeige und Sync-Aktionen

* Verbessert: AJAX-Handler nutzt konfigurierte Zeitraum-Einstellungen

* Neu: Repository-Hilfsmethoden fÃ¼r Kalender- und Event-Zuordnung



= 0.3.2.5 =

* Fix: CT_Client wird jetzt korrekt mit Credentials (tenant, username, password) instanziiert

* Fix: Behebt "Too few arguments to function" Fehler bei Kalender-Synchronisation

* Feature: Kopierfunktion fÃ¼r Debug-Log im Admin-Panel hinzugefÃ¼gt

* Feature: Fallback-Kopiermethode fÃ¼r Ã¤ltere Browser

* Verbessert: Passwort-EntschlÃ¼sselung wird im AJAX-Handler durchgefÃ¼hrt



= 0.3.2.4 =

* Debug: Erweiterte Fehlerbehandlung mit Try-Catch fÃ¼r Dependencies und PHP Errors

* Debug: Detaillierte error_log Ausgaben an jedem Schritt des AJAX-Handlers

* Debug: Separate Fehlerbehandlung fÃ¼r Exception und Error (PHP 7+)

* Debug: File, Line und vollstÃ¤ndiger Stack Trace bei Fehlern

* Hilft bei der Diagnose von HTTP 500 Fehlern



= 0.3.2.3 =

* Bugfix: Logger-Klasse wird jetzt explizit im AJAX-Handler geladen

* Bugfix: ABSPATH-Check vor Logger-require in Admin-Klasse

* Behebt HTTP 500 Fehler bei Kalender-Synchronisation



= 0.3.2.2 =

* Feature: Zentrale Logger-Klasse fÃ¼r einheitliches Debug-Logging

* Direktes Schreiben ins wp-content/debug.log (unabhÃ¤ngig von WP_DEBUG)

* Kompatibel mit Debug-Log-Manager-Plugins fÃ¼r einfache Log-Anzeige

* Millisekunden-genaue Timestamps in allen Log-EintrÃ¤gen

* Farbcodierte Icons (âœ… âœ— âš ï¸ â„¹ï¸) fÃ¼r bessere Lesbarkeit in Logs

* Strukturierte Logs: header(), separator(), dump() Helper-Methoden

* Logging auf 3 Ebenen: AJAX Handler, Service Layer, CT Client

* Automatische Aktivierung von error_log falls nicht konfiguriert

* Log-EintrÃ¤ge mit Prefix "[REPRO CT-SUITE]" fÃ¼r einfaches Filtern



= 0.3.2.1 =

* Feature: Live-Debug-Panel direkt im Admin-Bereich (Settings-Tab)

* Echtzeit-Anzeige: Debug-Logs werden wÃ¤hrend der Synchronisation im Browser angezeigt

* Detaillierte Ausgaben: API-Request-URL, Response-Status, Statistiken, Fehler-Details

* Farbcodierte Logs: Info (blau), Erfolg (grÃ¼n), Warnung (orange), Fehler (rot)

* Timestamp: Jede Log-Nachricht mit Millisekunden-genauem Zeitstempel

* Auto-Scroll: Automatisches Scrollen zu neuesten EintrÃ¤gen

* Debug-Panel: Ein-/Ausblendbar, lÃ¶schbar, persistent wÃ¤hrend der Session

* Keine WP_DEBUG erforderlich: Funktioniert out-of-the-box ohne wp-config.php Ã„nderungen

* Browser-Konsole: Parallele Ausgabe in Browser-Konsole (F12) fÃ¼r technische Details



= 0.3.2.0 =

* DEBUG: Umfangreiche Debug-Ausgaben fÃ¼r Kalender-Synchronisation hinzugefÃ¼gt

* Browser-Konsole: Detaillierte AJAX-Request/Response-Logs mit Debug-Informationen

* WordPress Debug-Log: VollstÃ¤ndige Protokollierung auf 3 Ebenen (AJAX Handler, Service Layer, CT Client)

* CT Client: HTTP-Request/Response-Details, Status-Codes, Headers, JSON-Decode-Fehler

* Calendar Sync Service: API-Call-Tracking, Response-Struktur, Import-Status pro Kalender

* AJAX Handler: Request-URL, Tenant, Zeitstempel, vollstÃ¤ndige Statistiken und Fehler-Traces

* DEBUG.md: AusfÃ¼hrliche Dokumentation fÃ¼r Fehlerdiagnose und Support

* Debug-Informationen werden in AJAX-Response zurÃ¼ckgegeben fÃ¼r Frontend-Anzeige

* Alle Logs verwenden Prefix "[REPRO CT-SUITE DEBUG]" fÃ¼r einfaches Filtern



= 0.3.1.3 =

* Fix: Private GitHub-Assets kÃ¶nnen jetzt mit Token-Authentifizierung heruntergeladen werden

* Automatische Updates funktionieren jetzt vollstÃ¤ndig bei privaten Repositories

* Download-Filter mit Authorization-Header fÃ¼r sichere Asset-Downloads



= 0.2.4.2 =

* Auto-Cleanup: Alte Plugin-Installationen werden bei Aktivierung automatisch bereinigt

* Entfernt alte Ordner wie "repro-ct-suite-clean", "-old", "-backup" automatisch

* SicherheitsprÃ¼fungen: Nur inaktive Duplikate werden gelÃ¶scht

* Verbesserte InstallationsstabilitÃ¤t



= 0.2.4.1 =

* Packaging: ZIP entspricht WP-Vorgaben (Top-Level-Ordner immer "repro-ct-suite")

* Build: Release-Asset ohne Versionsnummer (repro-ct-suite.zip)

* Intern: Versionierung weiterhin im Plugin enthalten (Header + Konstante), kein ?ver in CSS/JS-URLs



= 0.2.4 =

* Fix: GitHub-Token fÃ¼r Updates bei privatem Repository hinzugefÃ¼gt

* Automatische Updates funktionieren jetzt auch fÃ¼r private GitHub-Repositories

* Hinweis: Dieses Update muss manuell installiert werden, danach funktionieren alle Updates automatisch



= 0.2.3 =

* Bugfix: Redirect nach Connection-Test bleibt nun im Settings-Tab

* Bugfix: Dashboard zeigt korrekten Verbindungsstatus basierend auf gespeicherten Credentials

* UI: Status-Punkt wechselt von gelb (nicht konfiguriert) zu grÃ¼n (konfiguriert)

* UI: Button-Text passt sich dynamisch an ("Jetzt einrichten" vs "Einstellungen Ã¤ndern")



= 0.2.2 =

* Bugfix: Headers-Already-Sent-Fehler beim Connection-Test behoben

* Connection-Test nutzt jetzt admin_init Hook statt direkte Template-AusfÃ¼hrung

* Post-Redirect-Get Pattern fÃ¼r Test-Ergebnisse via Transient



= 0.2.1 =

* ChurchTools Login-Service: Authentifizierung via Username/Passwort

* Tenant-basierte URL-Konstruktion (z.B. "gemeinde" â†’ gemeinde.church.tools)

* Cookie-basierte Session-Verwaltung mit automatischem Re-Login

* Settings-UI: Tenant-Eingabe, Verbindungstest-Button

* Passwort-Eingabe: leer lassen behÃ¤lt gespeichertes Passwort bei

* API-Client mit GET-Methode und Fehlerbehandlung (401 â†’ Re-Auth)



= 0.2.0 =

* Datenbankschema: benutzerdefinierte Tabellen fÃ¼r Events, Appointments und Services

* Repository-Pattern: Event, Appointment, EventServices Repositories mit Upsert-Logik

* Sichere Credentials: verschlÃ¼sseltes Speichern von ChurchTools-PasswÃ¶rtern (Crypto-Klasse)

* Admin-Seite "Termine": konsolidierte Ãœbersicht aller Events und Appointments ohne Event-Zuordnung

* Einstellungsseite: ChurchTools Basis-URL, Benutzername und Passwort konfigurierbar

* DB-Migrationen: automatische Schema-Installation und Upgrade-Hooks

* Vorbereitung fÃ¼r Sync-Service: Repository-Schicht implementiert



= 0.1.0.3 =

* Auto-Update-Funktion hinzugefÃ¼gt (opt-in via Admin-UI)

* Update-Info-Seite mit Statusanzeige und Auto-Update-Toggle

* Versionsnummer-Support fÃ¼r 4-stellige Versionen (Major.Minor.Patch.Build)



= 0.1.0.2 =

* i18n-KompatibilitÃ¤t mit WordPress 6.7.0 (Textdomain auf init geladen)

* GitHub-Updater: Versionsnormalisierung und PrÃ¤ferenz fÃ¼r Release-ZIP-Assets



= 0.1.0 =

* Initiales Release

* GitHub-Updater fÃ¼r automatische Plugin-Updates

* Material Design-inspirierte Admin-OberflÃ¤che

* Template-basierte View-Architektur

* OOP-Struktur mit Loader-Pattern



= 1.0.0 =

* Legacy-Placeholder (veraltet)











