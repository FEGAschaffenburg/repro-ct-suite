# Frontend-Entwicklung: Termine-Anzeige
## Schrittweise Implementierung

**Ziel:** Flexible, konfigurierbare Termine-Darstellung im Frontend mit Elementor-Integration

---

## 📋 PHASE 1: Basis-Shortcode System (v0.7.0)
**Dauer:** 2-3 Tage

### 1.1 Shortcode-Handler erstellen
**Datei:** `includes/class-repro-ct-suite-shortcodes.php`

```php
// Basis-Shortcode: [rcts_events]
Attribute:
- view="list|cards|calendar"  (Standard: list)
- limit="10"                   (Anzahl Termine)
- calendar_ids="1,2,3"        (Filter nach Kalender)
- show_fields="title,date,time,location"
```

**Aufgaben:**
- [ ] Shortcode-Klasse erstellen
- [ ] Event-Repository Query-Methoden erweitern
- [ ] Template-Loader implementieren
- [ ] Basis-CSS schreiben

**Dateien:**
```
includes/
  ├── class-repro-ct-suite-shortcodes.php
  ├── class-repro-ct-suite-template-loader.php
templates/
  ├── events/
      ├── list-simple.php
      ├── list-with-date.php
      └── cards.php
public/css/
  └── repro-ct-suite-frontend.css
```

---

## 📋 PHASE 2: Template-Varianten (v0.7.1)
**Dauer:** 2-3 Tage

### 2.1 Drei Basis-Ansichten

#### A) Liste Einfach (`list-simple.php`)
```
📅 Gottesdienst - 10.11.2025 10:00
📅 Bibelstunde - 12.11.2025 19:00
```

#### B) Liste mit Datum-Header (`list-with-date.php`)
```
=== Sonntag, 10.11.2025 ===
10:00 Gottesdienst (Hauptraum)
14:00 Gemeindefest (Hof)

=== Dienstag, 12.11.2025 ===
19:00 Bibelstunde (Gemeindesaal)
```

#### C) Kachel-Ansicht (`cards.php`)
```
┌─────────────────┐  ┌─────────────────┐
│ 10. Nov         │  │ 12. Nov         │
│ Gottesdienst    │  │ Bibelstunde     │
│ 10:00-11:30     │  │ 19:00-20:30     │
│ 📍 Hauptraum    │  │ 📍 Gemeindesaal │
└─────────────────┘  └─────────────────┘
```

**Shortcode-Beispiele:**
```
[rcts_events view="list"]
[rcts_events view="list-grouped"]
[rcts_events view="cards" limit="6"]
```

---

## 📋 PHASE 3: Erweiterte Filter & Konfiguration (v0.7.2)
**Dauer:** 3-4 Tage

### 3.1 Filter-Optionen

**Shortcode-Attribute erweitern:**
```php
[rcts_events 
  view="cards"
  limit="12"
  calendar_ids="1,2,3"           // Kalender-Filter
  tag_ids="5,8"                  // Tag-Filter (wenn vorhanden)
  from_days="-7"                 // Vergangene 7 Tage
  to_days="30"                   // Nächste 30 Tage
  order="asc|desc"               // Sortierung
  show_past="true"               // Vergangene anzeigen
]
```

### 3.2 Feld-Konfiguration

**Anzeige-Felder:**
```php
show_fields="title,date,time,location,description,calendar"

Mögliche Felder:
- title           (Event-Titel)
- date            (Datum)
- time            (Uhrzeit)
- datetime        (Datum + Zeit kombiniert)
- location        (Ort)
- description     (Beschreibung)
- calendar        (Kalender-Name)
- image           (Event-Bild, falls vorhanden)
```

**Beispiel:**
```
[rcts_events view="cards" show_fields="title,datetime,location"]
```

### 3.3 Admin-UI für Shortcode-Generator

**Neue Admin-Seite:** Settings Tab erweitern

```
┌─────────────────────────────────────┐
│ Shortcode Generator                 │
├─────────────────────────────────────┤
│ Ansicht:     [x] Liste einfach      │
│              [ ] Liste mit Datum    │
│              [ ] Kacheln            │
│                                     │
│ Kalender:    [x] Hauptkalender      │
│              [x] Veranstaltungen    │
│              [ ] Intern             │
│                                     │
│ Anzahl:      [10]                   │
│ Zeitraum:    Von [-7] bis [30] Tage │
│                                     │
│ Felder:      [x] Titel              │
│              [x] Datum/Zeit         │
│              [x] Ort                │
│              [ ] Beschreibung       │
│                                     │
│ Generierter Shortcode:              │
│ [rcts_events view="list"...]        │
│ [Kopieren]                          │
└─────────────────────────────────────┘
```

---

## 📋 PHASE 4: Elementor-Integration (v0.8.0)
**Dauer:** 4-5 Tage

### 4.1 Elementor Widget erstellen

**Datei:** `includes/elementor/class-repro-ct-suite-elementor-widget.php`

**Features:**
- Visueller Builder für Event-Anzeige
- Live-Vorschau im Editor
- Alle Shortcode-Optionen als Elementor-Controls
- Styling-Optionen (Farben, Abstände, Schriften)

**Elementor Controls:**
```
Content Tab:
  └─ Layout
     ├─ View Type (list/cards)
     ├─ Number of Events
     └─ Date Range
  └─ Filters
     ├─ Calendars (Multiselect)
     ├─ Tags (Multiselect)
     └─ Show Past Events
  └─ Visibility
     └─ Show/Hide Fields (Checkboxes)

Style Tab:
  └─ Card Style
     ├─ Background Color
     ├─ Border
     └─ Border Radius
  └─ Typography
     ├─ Title Font
     ├─ Date Font
     └─ Text Font
  └─ Spacing
     ├─ Card Spacing
     └─ Inner Padding
```

### 4.2 Elementor-Aktivierung

**Prüfung:**
```php
// In repro-ct-suite.php
if ( did_action( 'elementor/loaded' ) ) {
    require_once 'includes/elementor/class-repro-ct-suite-elementor.php';
    new Repro_CT_Suite_Elementor();
}
```

---

## 📋 PHASE 5: Standard-Seiten Generator (v0.8.1)
**Dauer:** 3-4 Tage

### 5.1 Automatische Seiten-Erstellung

**Admin-Tool:** Settings → Frontend → "Seiten erstellen"

**Generierte Seiten:**
```
1. /termine/              (Alle Termine)
2. /kalender/{slug}/      (Pro ausgewähltem Kalender)
```

**Optionen:**
```
┌─────────────────────────────────────┐
│ Standard-Seiten erstellen           │
├─────────────────────────────────────┤
│ [x] Hauptseite "Termine"            │
│     Slug: [termine]                 │
│     Template: [Kacheln]             │
│                                     │
│ [x] Kalender-Seiten erstellen       │
│     └─ [x] Gottesdienste           │
│        Slug: [gottesdienste]        │
│     └─ [x] Veranstaltungen         │
│        Slug: [veranstaltungen]      │
│                                     │
│ [x] Mit Elementor erstellen         │
│                                     │
│ [Seiten erstellen]                  │
└─────────────────────────────────────┘
```

### 5.2 Elementor-Templates

**Wenn Elementor aktiv:**
- Seiten werden mit vorgefertigtem Elementor-Layout erstellt
- Template enthält Events-Widget mit optimalen Einstellungen
- User kann danach anpassen

**Ohne Elementor:**
- Seiten enthalten Shortcode
- Basis-WordPress-Template

---

## 📋 PHASE 6: Erweiterte Features (v0.9.0)
**Dauer:** 5-6 Tage

### 6.1 Zusätzliche Ansichten

**Kalender-Ansicht:**
```
[rcts_events view="calendar"]

┌─────────────────────────────────────┐
│  < Oktober 2025 >                   │
├──────┬──────┬──────┬──────┬────────┤
│ Mo   │ Di   │ Mi   │ Do   │ Fr ... │
├──────┼──────┼──────┼──────┼────────┤
│      │      │  1   │  2   │  3     │
│  6   │  7   │  8•2 │  9   │  10    │
│      │      │      │      │        │
└──────┴──────┴──────┴──────┴────────┘
```

**Timeline-Ansicht:**
```
[rcts_events view="timeline"]

2025
  November
    ├─ 10.11. Gottesdienst
    ├─ 12.11. Bibelstunde
    └─ 17.11. Gemeindefest
  Dezember
    ├─ 01.12. Adventsfeier
    └─ 24.12. Weihnachtsgottesdienst
```

### 6.2 Einzeltermin-Seiten

**URL-Struktur:**
```
/termine/gottesdienst-2025-11-10/
```

**Template:**
```
templates/
  └── events/
      └── single.php
```

**Shortcode für Details:**
```
[rcts_event_details id="123"]
```

### 6.3 Filter-Widget

**Sidebar/Frontend-Filter:**
```
┌─────────────────────┐
│ Termine filtern     │
├─────────────────────┤
│ Kalender:           │
│ [x] Gottesdienste   │
│ [ ] Veranstaltungen │
│ [ ] Intern          │
│                     │
│ Zeitraum:           │
│ Von: [01.11.2025]   │
│ Bis: [30.11.2025]   │
│                     │
│ [Filtern]           │
└─────────────────────┘
```

---

## 📋 PHASE 7: Performance & Caching (v0.9.1)
**Dauer:** 2-3 Tage

### 7.1 Transient-Caching

```php
// Cache für Shortcode-Ausgabe
$cache_key = 'rcts_events_' . md5( serialize( $atts ) );
$output = get_transient( $cache_key );

if ( false === $output ) {
    $output = $this->render_events( $atts );
    set_transient( $cache_key, $output, HOUR_IN_SECONDS );
}
```

### 7.2 Lazy Loading

- Bilder mit loading="lazy"
- Pagination für große Event-Listen
- AJAX-Nachlade-Funktion

---

## 🗂️ Dateistruktur (Komplett)

```
repro-ct-suite/
├── includes/
│   ├── class-repro-ct-suite-shortcodes.php
│   ├── class-repro-ct-suite-template-loader.php
│   ├── elementor/
│   │   ├── class-repro-ct-suite-elementor.php
│   │   └── widgets/
│   │       └── class-events-widget.php
│   └── admin/
│       └── class-page-generator.php
├── templates/
│   ├── events/
│   │   ├── list-simple.php
│   │   ├── list-grouped.php
│   │   ├── cards.php
│   │   ├── calendar.php
│   │   ├── timeline.php
│   │   └── single.php
│   └── elementor/
│       ├── events-main.json
│       └── events-calendar.json
├── public/
│   ├── css/
│   │   ├── repro-ct-suite-frontend.css
│   │   ├── view-list.css
│   │   ├── view-cards.css
│   │   └── view-calendar.css
│   └── js/
│       ├── events-filter.js
│       └── calendar-navigation.js
└── admin/
    └── views/
        └── tabs/
            └── tab-frontend.php  (Shortcode Generator)
```

---

## 📊 Zusammenfassung

| Phase | Version | Features | Aufwand |
|-------|---------|----------|---------|
| 1 | v0.7.0 | Basis-Shortcode, Template-Loader | 2-3 Tage |
| 2 | v0.7.1 | 3 Ansichten (Liste, Cards) | 2-3 Tage |
| 3 | v0.7.2 | Filter, Feld-Config, Shortcode-Generator | 3-4 Tage |
| 4 | v0.8.0 | Elementor Widget | 4-5 Tage |
| 5 | v0.8.1 | Standard-Seiten Generator | 3-4 Tage |
| 6 | v0.9.0 | Kalender-Ansicht, Timeline, Single-Pages | 5-6 Tage |
| 7 | v0.9.1 | Performance, Caching | 2-3 Tage |

**Gesamt:** ~21-28 Tage (ca. 4-6 Wochen)

---

## 🎯 Empfohlene Reihenfolge

### Quick Win (Woche 1-2):
- Phase 1: Basis-Shortcode ✓
- Phase 2: Template-Varianten ✓
→ **Ergebnis:** Funktionierende Termine-Anzeige im Frontend

### Elementor-Ready (Woche 3-4):
- Phase 3: Filter & Generator ✓
- Phase 4: Elementor Widget ✓
→ **Ergebnis:** Professionelle, visuell konfigurierbare Anzeige

### Automation (Woche 5):
- Phase 5: Standard-Seiten Generator ✓
→ **Ergebnis:** One-Click Setup für komplette Termine-Seiten

### Optional/Later:
- Phase 6: Erweiterte Views
- Phase 7: Performance-Optimierung

---

## 🚀 Nächste Schritte

**Sofort starten mit Phase 1?**
1. Shortcode-Handler implementieren
2. Template-Loader erstellen
3. Erste Liste-Ansicht bauen
4. Basis-CSS schreiben

**Soll ich beginnen?** 🤔
