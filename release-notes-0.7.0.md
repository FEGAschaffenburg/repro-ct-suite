**MAJOR UPDATE: Frontend Events-Anzeige** 🎉

## ✨ Neue Features

### 📝 Shortcode System
- **[rcts_events]** - Vollständiger Shortcode für Event-Anzeige
- Template-Loader mit Theme-Override Support
- Flexible Filter und Konfiguration

### 🎨 3 Ansichten
1. **List Simple** - Einfache Liste mit Emojis
2. **List Grouped** - Nach Datum gruppiert
3. **Cards** - Moderne Kachel-Ansicht (Grid)

### 🔧 Shortcode-Attribute

```
[rcts_events view="cards" limit="12"]
[rcts_events view="list-grouped" calendar_ids="1,2"]
[rcts_events from_days="0" to_days="30" show_past="false"]
```

### 📱 Responsive Design
- Grid-Layout (3/2/1 Spalten)
- Mobile-optimiert
- Hover-Effekte
- Accessibility-Ready

## 📋 Attribute (Komplett)

| Attribut | Werte | Standard |
|----------|-------|----------|
| view | list, list-grouped, cards | list |
| limit | Zahl | 10 |
| calendar_ids | 1,2,3 | alle |
| from_days | 0, 7, 30 | 0 |
| to_days | 7, 30, 90 | 30 |
| show_fields | title,date,time,... | title,date,time,location |
| order | asc, desc | asc |
| show_past | true, false | false |

## 🎯 Theme-Override

Themes können Templates anpassen:

```
/wp-content/themes/mein-theme/
  └─ repro-ct-suite/
      └─ events/
          ├─ list-simple.php
          ├─ list-grouped.php
          └─ cards.php
```

## 📦 Phase 1 von 7

Dies ist Phase 1 des 7-Phasen Frontend-Plans:
- ✅ Phase 1: Basis-Shortcode ← **AKTUELL**
- ⏳ Phase 2: Template-Varianten
- ⏳ Phase 3: Filter & Shortcode-Generator
- ⏳ Phase 4: Elementor-Integration
- ⏳ Phase 5: Standard-Seiten Generator
- ⏳ Phase 6: Kalender/Timeline-Ansicht
- ⏳ Phase 7: Performance & Caching

## 📖 Dokumentation

Siehe PHASE-1-TESTING.md für:
- Verwendungsbeispiele
- Test-Szenarien
- Responsive-Tests
- Theme-Override-Guide

## 🔄 Enthält auch

Alle Features von v0.6.x:
- WordPress-Zeitzone Support
- Update-Cache Clearing
- BOM-Fix + Login-Test Logging
