# Release Notes v0.9.5

**Veröffentlicht:** 10. November 2025  
**Tag:** v0.9.5  
**Download:** [repro-ct-suite-0.9.5.zip](releases/repro-ct-suite-0.9.5.zip)

## 🎉 Highlights

### Gutenberg Block Integration
- **Direkter Block im Editor**: Suche nach "ChurchTools Termine" im Block-Inserter
- **Inspector Controls**: Einfache Konfiguration direkt im Editor
  - View-Auswahl (6 verschiedene Ansichten)
  - Limit-Einstellung mit RangeControl
  - Zeitraum-Filter (fromDays, toDays)
  - Toggle für vergangene Events
  - Kalender-IDs und Custom Fields

### 6 Professionelle Event-Ansichten

#### 1. **Compact** - Ultra kompakt
- Perfekt für Footer-Bereiche
- Einfache Liste mit Border-Accent
- Format: Datum + Zeit + Titel (einzeilig)
- CSS-Klasse: `.rcts-events-compact`

#### 2. **List** (Simple) - Standard-Liste
- Große 70x70px Datum-Boxes mit Gradient
- SVG Icons für Zeit und Ort
- Hover-Effekte mit Transform
- CSS-Klasse: `.rcts-events-list-modern`

#### 3. **Medium** - Ausgewogen
- Zwei-Spalten Layout (Date + Content)
- 80px Datums-Spalte mit Gradient
- Wochentag-Anzeige
- CSS-Klasse: `.rcts-events-medium`

#### 4. **List Grouped** - Timeline
- Vertikale Gradient-Timeline
- Gruppierung nach Datum
- 24x24px Timeline-Marker
- CSS-Klasse: `.rcts-events-timeline`

#### 5. **Cards** - Grid Layout
- Responsive 3-Spalten Grid (auto-fill)
- Date Badge mit Clip-Path Dekoration
- Hover Lift Effect (translateY)
- CSS-Klasse: `.rcts-events-grid`

#### 6. **Sidebar** - Widget-optimiert
- 50x50px kompakte Date Boxes
- Optimiert für 250-300px Breite
- 2-zeilige Titel mit Ellipsis
- CSS-Klasse: `.rcts-events-sidebar`

## ✨ Neue Features

### Shortcode Manager UI
- **Einklappbare Bereiche**: "Standard Shortcodes" und "Eigene Presets"
- **Smooth Animations**: slideToggle(300) mit Icon-Rotation
- **Badge Counter**: Zeigt Anzahl der Layouts/Presets
- **Icon-Only Buttons**: Kompakte Dashicons für Aktionen

### Design System
- **Purple Gradient**: #667eea → #764ba2 durchgängig
- **SVG Icons**: Professionelle Icons für Zeit, Ort, etc.
- **Hover Effects**: Transform, Box-Shadow, Transitions
- **Responsive**: Media Queries @768px und @480px

## 🔧 Technische Verbesserungen

### CSS-Zentralisierung
- **580+ Zeilen** in `repro-ct-suite-public.css`
- Alle Inline-Styles aus Templates entfernt
- Modulare Struktur pro View-Typ
- Performance-Optimierung durch Caching

### Template-Struktur
```
templates/events/
├── list-simple.php      (List View)
├── list-grouped.php     (Timeline View)
├── cards.php            (Grid View)
├── list-compact.php     (Compact View) ⭐ NEU
├── list-medium.php      (Medium View)  ⭐ NEU
└── list-sidebar.php     (Sidebar View) ⭐ NEU
```

### JavaScript-Erweiterungen
- `gutenberg-block.js`: Block-Registrierung mit voller Kontrolle
- `modern-shortcode-manager.js`: Collapsible-Logik (setupCollapsible)

## 📋 Shortcode-Verwendung

```php
// Compact View
[repro_ct_suite_events view="compact" limit="5"]

// List View
[repro_ct_suite_events view="list" limit="10"]

// Medium View
[repro_ct_suite_events view="medium" limit="8"]

// Timeline View
[repro_ct_suite_events view="list-grouped" limit="15"]

// Cards View
[repro_ct_suite_events view="cards" limit="9"]

// Sidebar View
[repro_ct_suite_events view="sidebar" limit="5"]

// Mit Filtern
[repro_ct_suite_events view="cards" limit="12" calendar_ids="1,2,3" from_days="0" to_days="30"]
```

## 🎨 Design-Inspiration
- **The Events Calendar** - Card-Layouts und Timeline
- **Eventbrite** - Modern UI mit Gradients
- **WordPress Core** - Gutenberg Block-Patterns

## 📦 Release-Informationen

### Datei-Details
- **Größe**: 0.28 MB (293,415 Bytes)
- **Entries**: 138 Dateien
- **Format**: WordPress-kompatible ZIP-Struktur (forward slashes)
- **Hauptdatei**: `repro-ct-suite/repro-ct-suite.php`

### Versions-Konstanten
```php
// Plugin Header
Version: 0.9.5

// PHP Konstante
define('REPRO_CT_SUITE_VERSION', '0.9.5');

// readme.txt
Stable tag: 0.9.5
```

## 🚀 Installation

### WordPress Admin
1. Download `repro-ct-suite-0.9.5.zip`
2. WordPress Admin → Plugins → Installieren → ZIP hochladen
3. Plugin aktivieren
4. ChurchTools API-Daten eingeben

### Gutenberg Block verwenden
1. Neuen Block einfügen
2. Suche nach "ChurchTools Termine"
3. Block konfigurieren im Inspector
4. Fertig!

### Shortcode Manager nutzen
1. Admin → ChurchTools Suite → Shortcodes
2. "Standard Shortcodes" aufklappen
3. Gewünschtes Layout kopieren
4. In Seite/Beitrag einfügen

## 🔄 Upgrade von v0.9.4.x

Keine Breaking Changes! Das Update ist vollständig rückwärtskompatibel.

### Was passiert beim Update?
- Alte View-Parameter funktionieren weiterhin
- Neue Views sind sofort verfügbar
- CSS wird automatisch geladen
- Gutenberg Block erscheint im Editor

### Empfohlene Schritte
1. Plugin über WordPress Admin aktualisieren
2. Admin-Bereich kurz besuchen (Cache-Refresh)
3. Neue Views im Shortcode Manager anschauen
4. Gutenberg Block ausprobieren

## 📊 Statistiken

### Code-Änderungen
- **Dateien geändert**: 2 (repro-ct-suite.php, readme.txt)
- **Insertions**: 1,224 Zeilen
- **Deletions**: 613 Zeilen
- **Neue Templates**: 3 (compact, medium, sidebar)
- **CSS Zeilen**: 580+ (zentralisiert)

### GitHub
- **Commit**: 9f689a8
- **Tag**: v0.9.5
- **Branch**: main
- **Repository**: https://github.com/FEGAschaffenburg/repro-ct-suite

## 🐛 Bug Fixes

Dieses Release enthält hauptsächlich neue Features. Folgende Probleme wurden nebenbei behoben:

- Shortcode View-Parameter funktionieren jetzt korrekt (CSS-Zentralisierung)
- Property-Naming konsistent ($event->title statt ->name)
- Edit/New/Copy/Delete Buttons in Shortcode Manager

## 🔮 Roadmap

### Geplant für v0.9.6+
- [ ] Weitere Template-Varianten
- [ ] Color-Scheme Customizer
- [ ] Event-Detail Popup/Modal
- [ ] Kategorie-Filter UI
- [ ] Export zu iCal/Google Calendar

## 📝 Credits

- **Entwicklung**: FEGAschaffenburg
- **Design-Inspiration**: The Events Calendar, Eventbrite, Modern WordPress Themes
- **Testing**: WordPress 6.4, PHP 7.4-8.2

## 📞 Support

- **GitHub Issues**: https://github.com/FEGAschaffenburg/repro-ct-suite/issues
- **Repository**: https://github.com/FEGAschaffenburg/repro-ct-suite
- **WordPress Plugin**: ChurchTools Suite

---

**🎉 Viel Spaß mit v0.9.5!**
