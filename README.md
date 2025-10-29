# Repro CT-Suite

WordPress-Plugin zur Integration von ChurchTools-Daten in WordPress.

## Beschreibung

Repro CT-Suite ist ein WordPress-Plugin, das eine Brücke zwischen ChurchTools und Ihrer WordPress-Website herstellt. Es synchronisiert Termin- und Veranstaltungsdaten aus ChurchTools und macht sie in WordPress verfügbar für Kalenderansichten, Widgets und andere Darstellungen.

### Version 1.0 - Hauptfokus

**Termine & Events:**
- Abruf von Appointments aus ChurchTools
- Abruf von Event-Daten aus ChurchTools
- Automatische Synchronisation via Cron
- Einfache Anzeige über Shortcodes

## Features

### ChurchTools-Integration
- ✅ Sichere API-Verbindung zu ChurchTools
- ✅ Abruf von Terminen (Appointments)
- ✅ Abruf von Veranstaltungen (Events)
- ✅ Automatische Synchronisation
- ✅ Shortcodes für Frontend-Anzeige
- ✅ Admin-Oberfläche für Konfiguration

### Technische Features
- ✅ Moderner objektorientierter Aufbau
- ✅ Trennung von Admin- und Public-Funktionalität
- ✅ Internationalisierung (i18n) vorbereitet
- ✅ Hook-basierte Architektur
- ✅ PHPUnit Tests integriert
- ✅ Composer-Unterstützung
- ✅ WordPress Coding Standards

## Ordnerstruktur

```
repro-ct-suite/
├── admin/                      # Admin-spezifische Funktionalität
│   ├── css/                    # Admin CSS
│   ├── js/                     # Admin JavaScript
│   ├── views/                  # Admin-Templates
│   └── class-repro-ct-suite-admin.php
├── public/                     # Public-facing Funktionalität
│   ├── css/                    # Public CSS
│   ├── js/                     # Public JavaScript
│   └── class-repro-ct-suite-public.php
├── includes/                   # Core-Klassen
│   ├── class-repro-ct-suite.php
│   ├── class-repro-ct-suite-loader.php
│   ├── class-repro-ct-suite-i18n.php
│   ├── class-repro-ct-suite-activator.php
│   └── class-repro-ct-suite-deactivator.php
├── languages/                  # Übersetzungsdateien
├── templates/                  # Frontend-Templates
├── assets/                     # Statische Assets (Bilder, etc.)
├── tests/                      # PHPUnit Tests
├── composer.json
├── phpunit.xml.dist
└── repro-ct-suite.php         # Haupt-Plugin-Datei
```

## Installation

### Voraussetzungen
- WordPress 5.0 oder höher
- PHP 7.4 oder höher
- ChurchTools-Instanz mit API-Zugang
- API-Token von ChurchTools

### Manuell

1. Laden Sie das Plugin herunter
2. Entpacken Sie es in `wp-content/plugins/`
3. Aktivieren Sie es im WordPress-Admin unter "Plugins"
4. Gehen Sie zu "Repro CT-Suite" > "Einstellungen"
5. Tragen Sie Ihre ChurchTools-URL und API-Token ein

### Via Git

```bash
cd wp-content/plugins/
git clone https://github.com/FEGAschaffenburg/repro-ct-suite.git
cd repro-ct-suite
composer install --no-dev
```

## Verwendung

### Konfiguration

1. Navigieren Sie zu **WordPress Admin > Repro CT-Suite**
2. Tragen Sie Ihre ChurchTools-Daten ein:
   - **ChurchTools URL**: z.B. `https://ihre-gemeinde.church.tools`
   - **API Token**: Erstellen Sie einen Token in ChurchTools unter Einstellungen
3. Speichern Sie die Einstellungen
4. Klicken Sie auf "Jetzt synchronisieren" für die erste Datenabholung

### Shortcodes

**Termine anzeigen:**
```
[ct_appointments limit="10"]
```

**Events anzeigen:**
```
[ct_events limit="5"]
```

**Parameter:**
- `limit` - Anzahl der anzuzeigenden Einträge (Standard: 10)
- `category` - Filtert nach Kategorie-ID
- `from` - Startdatum (Format: YYYY-MM-DD)
- `to` - Enddatum (Format: YYYY-MM-DD)

## Entwicklung

### Voraussetzungen

- PHP 7.4 oder höher
- WordPress 5.0 oder höher
- Composer

### Setup für Entwicklung

```bash
# Abhängigkeiten installieren
composer install

# Tests ausführen
composer test
# oder
./vendor/bin/phpunit
```

### Coding Standards

Das Plugin folgt den [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/).

## Updates

### Automatische Updates von GitHub

Das Plugin unterstützt automatische Updates direkt von GitHub:

- WordPress prüft alle 12 Stunden auf neue Versionen
- Updates erscheinen auf der Plugin-Seite wie bei WordPress.org-Plugins
- Einfache Installation mit einem Klick

### Neue Version veröffentlichen

1. Aktualisieren Sie die Versionsnummer in:
   - `repro-ct-suite.php` (Plugin Header)
   - `readme.txt` (Stable tag)
2. Committen und pushen Sie die Änderungen
3. Erstellen Sie ein neues Release auf GitHub:
   - Tag: `v1.0.1` (mit "v" Präfix)
   - Release-Titel: z.B. "Version 1.0.1"
   - Beschreibung: Changelog-Eintrag
4. WordPress erkennt das Update automatisch

### Manueller Update-Check

Im WordPress-Admin unter **Repro CT-Suite > Update-Info** können Sie:
- Aktuelle Version einsehen
- Manuell auf Updates prüfen
- Update-Informationen anzeigen

## Architektur

Das Plugin verwendet eine moderne, objektorientierte Architektur:

- **Loader**: Verwaltet alle WordPress Hooks zentral
- **i18n**: Kümmert sich um Internationalisierung
- **Admin**: Alle Admin-spezifischen Funktionen
- **Public**: Alle Frontend-spezifischen Funktionen
- **Activator/Deactivator**: Aktivierungs- und Deaktivierungslogik

## Lizenz

GPL v2 or later

## Changelog

### 1.0.0
- Initiales Release mit moderner Plugin-Architektur
- ChurchTools API-Integration vorbereitet
- GitHub-basierter Update-Mechanismus
- Admin-Bereich implementiert
- Public-Bereich implementiert
- Internationalisierung vorbereitet
- PHPUnit Tests integriert
- Automatische Updates von GitHub
- Update-Info-Seite im Admin

## Support

Bei Fragen oder Problemen öffnen Sie bitte ein Issue auf GitHub:
https://github.com/FEGAschaffenburg/repro-ct-suite/issues

## Beitragen

Pull Requests sind willkommen! Für größere Änderungen öffnen Sie bitte zuerst ein Issue.

## Autor

**FEGAschaffenburg**
- GitHub: [@FEGAschaffenburg](https://github.com/FEGAschaffenburg)
