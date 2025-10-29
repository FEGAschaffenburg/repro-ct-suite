=== Repro CT-Suite ===
Contributors: fegaaschaffenburg
Tags: churchtools, calendar, events, appointments, sync
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 0.2.4.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

WordPress-Plugin zur Integration von ChurchTools-Daten. Synchronisiert Termine und Veranstaltungen aus ChurchTools für die Anzeige auf WordPress-Websites.

== Description ==

Repro CT-Suite erstellt eine Brücke zwischen ChurchTools und WordPress. Das Plugin synchronisiert automatisch Termin- und Veranstaltungsdaten aus Ihrer ChurchTools-Instanz und macht sie über Shortcodes in WordPress verfügbar.

**Hauptfunktionen:**

* Automatische Synchronisation von ChurchTools-Terminen
* Abruf von Event-Daten
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
