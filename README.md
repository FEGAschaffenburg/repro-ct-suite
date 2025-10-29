# Repro CT-Suite

[![Version](https://img.shields.io/github/v/release/FEGAschaffenburg/repro-ct-suite)](https://github.com/FEGAschaffenburg/repro-ct-suite/releases)
[![WordPress](https://img.shields.io/badge/WordPress-5.0%2B-blue)](https://wordpress.org/)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple)](https://www.php.net/)
[![License](https://img.shields.io/github/license/FEGAschaffenburg/repro-ct-suite)](LICENSE)

WordPress-Plugin zur Integration von ChurchTools-Daten in WordPress.

## 📋 Beschreibung

Repro CT-Suite ist ein WordPress-Plugin, das eine Brücke zwischen ChurchTools und Ihrer WordPress-Website herstellt. Es synchronisiert Termin- und Veranstaltungsdaten aus ChurchTools und macht sie in WordPress verfügbar für Kalenderansichten, Widgets und andere Darstellungen.

## ✨ Features

### ChurchTools-Integration
- ✅ **Sichere API-Verbindung** zu ChurchTools mit Cookie-basierter Authentifizierung
- ✅ **Kalender-Verwaltung**: Auswahl der zu synchronisierenden Kalender
- ✅ **Termine (Appointments)**: Abruf und Synchronisation von Terminen
- ✅ **Veranstaltungen (Events)**: Abruf und Synchronisation von Events
- ✅ **AJAX-Synchronisation**: Sync ohne Seitenneuladen
- ✅ **Kombinierte Ansicht**: Termine und Events in einer Übersicht
- ✅ **Dashboard**: Übersichtliche Anzeige der nächsten Termine

### Technische Features
- ✅ **Moderner Code**: Objektorientierte PHP-Architektur
- ✅ **Repository Pattern**: Saubere Datenbankabstraktion
- ✅ **Service Layer**: Geschäftslogik getrennt von Präsentation
- ✅ **Migrationen**: Automatische Datenbank-Updates
- ✅ **AJAX-API**: Asynchrone Datenaktualisierung
- ✅ **GitHub Updater**: Automatische Updates direkt von GitHub
- ✅ **Internationalisierung**: i18n-ready
- ✅ **WordPress Standards**: Folgt WordPress Coding Standards

## 🚀 Installation

### Voraussetzungen
- WordPress 5.0 oder höher
- PHP 7.4 oder höher  
- ChurchTools-Instanz mit Zugang
- MySQL/MariaDB

### Automatische Installation (empfohlen)

1. Laden Sie die neueste Version von [Releases](https://github.com/FEGAschaffenburg/repro-ct-suite/releases) herunter
2. Gehen Sie zu **WordPress Admin → Plugins → Installieren**
3. Klicken Sie auf **Plugin hochladen**
4. Wählen Sie die ZIP-Datei aus
5. Aktivieren Sie das Plugin

### Manuelle Installation

```bash
cd wp-content/plugins/
git clone https://github.com/FEGAschaffenburg/repro-ct-suite.git
cd repro-ct-suite
```

## ⚙️ Konfiguration

### Erste Schritte

1. Navigieren Sie zu **WordPress Admin → Repro CT-Suite**
2. Tragen Sie Ihre ChurchTools-Zugangsdaten ein:
   - **Tenant**: Ihr ChurchTools-Subdomain (z.B. `ihre-gemeinde` für `ihre-gemeinde.church.tools`)
   - **Benutzername**: Ihr ChurchTools-Benutzername
   - **Passwort**: Ihr ChurchTools-Passwort (wird verschlüsselt gespeichert)
3. Klicken Sie auf **Verbindung testen**
4. Wechseln Sie zum Tab **Settings**
5. Klicken Sie auf **Kalender jetzt synchronisieren**
6. Wählen Sie die gewünschten Kalender aus
7. Speichern Sie die Auswahl
8. Synchronisieren Sie die Termine im **Dashboard**

### Kalender-Auswahl

Im Settings-Tab können Sie:
- Alle verfügbaren Kalender aus ChurchTools sehen
- Kalender auswählen/abwählen für die Synchronisation
- Status (Öffentlich/Privat) einsehen
- Kalenderfarben sehen
- Auswahl speichern und Kalender neu laden

### Synchronisation

**Manuell:**
- Dashboard → **Jetzt synchronisieren** (für Termine)
- Settings → **Kalender jetzt synchronisieren** (für Kalender)

**Automatisch:**
- Implementierung via WP-Cron geplant (zukünftige Version)

## 📊 Dashboard

Das Dashboard zeigt:

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
## 📊 Dashboard

Das Dashboard zeigt:
- **Termine-Statistik**: Anzahl synchronisierter Termine
- **Verbindungsstatus**: Status der ChurchTools-Verbindung
- **Nächste Termine**: Die 5 nächsten anstehenden Termine mit:
  - Datum & Uhrzeit
  - Titel
  - Ort
  - Quelle (Event/Termin Badge)
  - Kalenderfarbe

## 🗂️ Ordnerstruktur

```
repro-ct-suite/
├── .github/
│   └── workflows/
│       └── release.yml          # GitHub Actions für Releases
├── admin/                        # Admin-Bereich
│   ├── css/                      # Admin-Styles
│   ├── js/                       # Admin-JavaScript (AJAX)
│   ├── views/
│   │   ├── admin.php            # Haupt-Admin-Template
│   │   └── tabs/                # Tab-Templates
│   │       ├── tab-dashboard.php
│   │       ├── tab-settings.php
│   │       └── tab-test-connection.php
│   └── class-repro-ct-suite-admin.php
├── includes/                     # Core-Klassen
│   ├── repositories/            # Repository Pattern
│   │   ├── class-repro-ct-suite-repository-base.php
│   │   ├── class-repro-ct-suite-calendars-repository.php
│   │   ├── class-repro-ct-suite-events-repository.php
│   │   └── class-repro-ct-suite-appointments-repository.php
│   ├── services/                # Business Logic
│   │   ├── class-repro-ct-suite-calendar-sync-service.php
│   │   ├── class-repro-ct-suite-events-sync-service.php
│   │   └── class-repro-ct-suite-appointments-sync-service.php
│   ├── class-repro-ct-suite.php
│   ├── class-repro-ct-suite-ct-client.php  # ChurchTools API Client
│   ├── class-repro-ct-suite-crypto.php     # Verschlüsselung
│   ├── class-repro-ct-suite-migrations.php # DB-Migrationen
│   └── class-repro-ct-suite-updater.php    # GitHub Updater
├── public/                       # Frontend
│   ├── css/
│   ├── js/
│   └── class-repro-ct-suite-public.php
├── tests/                        # PHPUnit Tests
├── scripts/                      # Build-Scripts
│   └── create-plugin-zip.ps1    # ZIP-Erstellung
├── composer.json
├── phpunit.xml.dist
└── repro-ct-suite.php           # Haupt-Plugin-Datei
```

## 🔧 Entwicklung

### Setup

```bash
# Repository clonen
git clone https://github.com/FEGAschaffenburg/repro-ct-suite.git
cd repro-ct-suite

# Abhängigkeiten installieren (optional, für Tests)
composer install
```

### Datenbank-Schema

**Tabellen:**
- `wp_rcts_calendars` - ChurchTools-Kalender
- `wp_rcts_events` - Events aus ChurchTools
- `wp_rcts_appointments` - Termine aus ChurchTools

**Migrationen:** Automatisch bei Plugin-Aktivierung via `class-repro-ct-suite-migrations.php`

### Tests ausführen

```bash
composer test
# oder
./vendor/bin/phpunit
```

### Neue Version veröffentlichen

1. Versionsnummer in `repro-ct-suite.php` aktualisieren
2. Änderungen committen
3. Git-Tag erstellen:
   ```bash
   git tag v0.3.1
   git push origin v0.3.1
   ```
4. GitHub Actions erstellt automatisch:
   - Release
   - Plugin-ZIP
   - Release Notes

## 📡 API-Endpunkte (AJAX)

Das Plugin stellt folgende AJAX-Endpunkte bereit:

- `wp_ajax_repro_ct_suite_sync_calendars` - Kalender synchronisieren
- `wp_ajax_repro_ct_suite_sync_appointments` - Termine synchronisieren

Alle Endpunkte sind durch Nonces gesichert.

## 🔐 Sicherheit

- **Passwort-Verschlüsselung**: ChurchTools-Passwörter werden verschlüsselt gespeichert
- **Nonce-Validierung**: Alle AJAX-Requests sind CSRF-geschützt
- **Capability-Checks**: Nur Admins (`manage_options`) können Einstellungen ändern
- **Prepared Statements**: Alle DB-Queries nutzen `$wpdb->prepare()`
- **Input-Sanitization**: Alle Eingaben werden validiert und bereinigt

## 🔄 Automatische Updates

Das Plugin unterstützt automatische Updates direkt von GitHub:

1. WordPress prüft auf neue Versionen
2. Updates erscheinen wie bei WordPress.org-Plugins
3. Ein-Klick-Installation

**Hinweis**: Bei öffentlichem Repository funktioniert dies ohne zusätzliche Konfiguration.

## 📝 Changelog

### 0.3.0.1 (2025-10-29)
- **Bugfix**: Download-Filter für öffentliche Repositories entfernt
- Token aus Code entfernt (vorbereitet für öffentliches Repo)

### 0.3.0 (2025-10-29)
- **Feature**: AJAX-Integration für Kalender-Sync
- **Feature**: AJAX-Integration für Termine-Sync  
- **Feature**: Sync-Button im Dashboard
- **Feature**: Bestätigungsdialog für Kalender-Sync
- **UI**: Loading-State während Synchronisation
- **UI**: Auto-Reload nach erfolgreicher Sync
- **UI**: Detaillierte Sync-Statistiken

### 0.2.4.3 (2025-10-29)
- **Feature**: Kalender-Verwaltung im Settings-Tab
- **Feature**: Kalender-Auswahl für gefilterte Synchronisation
- **Feature**: Dashboard-Konsolidierung (kombinierte Termine-Ansicht)
- **Database**: Schema v2 mit Calendars-Tabelle
- **Repository**: Calendars Repository mit full CRUD
- **Service**: Calendar Sync Service

## 🤝 Beitragen

Pull Requests sind willkommen! Für größere Änderungen:

1. Forken Sie das Repository
2. Erstellen Sie einen Feature-Branch (`git checkout -b feature/AmazingFeature`)
3. Committen Sie Ihre Änderungen (`git commit -m 'Add: AmazingFeature'`)
4. Pushen Sie zum Branch (`git push origin feature/AmazingFeature`)
5. Öffnen Sie einen Pull Request

## 📄 Lizenz

GPL v2 or later - siehe [LICENSE](LICENSE) Datei

## 👥 Autor

**FEGAschaffenburg**
- GitHub: [@FEGAschaffenburg](https://github.com/FEGAschaffenburg)
- Repository: [repro-ct-suite](https://github.com/FEGAschaffenburg/repro-ct-suite)

## 🆘 Support

Bei Fragen oder Problemen:
- [Issue erstellen](https://github.com/FEGAschaffenburg/repro-ct-suite/issues)
- [Releases ansehen](https://github.com/FEGAschaffenburg/repro-ct-suite/releases)

---

Made with ❤️ for ChurchTools & WordPress
