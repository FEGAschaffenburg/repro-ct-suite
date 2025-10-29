=== Repro CT-Suite ===
Contributors: fegaaschaffenburg
Tags: churchtools, calendar, events, appointments, sync
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 0.1.0
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

= 1.0.0 =
* Initiales Release
* ChurchTools API-Integration
* Appointments-Synchronisation
* Events-Synchronisation
* Admin-Konfigurationsseite
* Shortcodes für Frontend-Anzeige
* Automatische Cron-Synchronisation
