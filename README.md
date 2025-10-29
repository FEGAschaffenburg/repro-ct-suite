# Repro CT-Suite

Ein modernes WordPress-Plugin mit zeitgemäßer Architektur nach WordPress Best Practices.

## Beschreibung

Repro CT-Suite ist ein WordPress-Plugin, das nach modernen WordPress-Standards entwickelt wurde und eine klare Trennung zwischen Admin- und Public-Funktionalität bietet.

## Features

- ✅ Moderner objektorientierter Aufbau
- ✅ Trennung von Admin- und Public-Funktionalität
- ✅ Internationalisierung (i18n) vorbereitet
- ✅ Hook-basierte Architektur
- ✅ PHPUnit Tests integriert
- ✅ Composer-Unterstützung

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

### Manuell

1. Laden Sie das Plugin herunter
2. Entpacken Sie es in `wp-content/plugins/`
3. Aktivieren Sie es im WordPress-Admin unter "Plugins"

### Via Git

```bash
cd wp-content/plugins/
git clone https://github.com/FEGAschaffenburg/repro-ct-suite.git
cd repro-ct-suite
composer install --no-dev
```

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
- Admin-Bereich implementiert
- Public-Bereich implementiert
- Internationalisierung vorbereitet
- PHPUnit Tests integriert

## Support

Bei Fragen oder Problemen öffnen Sie bitte ein Issue auf GitHub:
https://github.com/FEGAschaffenburg/repro-ct-suite/issues

## Beitragen

Pull Requests sind willkommen! Für größere Änderungen öffnen Sie bitte zuerst ein Issue.

## Autor

**FEGAschaffenburg**
- GitHub: [@FEGAschaffenburg](https://github.com/FEGAschaffenburg)
