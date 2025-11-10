# Phase 1: Basis-Shortcode - Testanleitung

## ✅ Was wurde implementiert

### 1. Shortcode-Handler
- **Datei:** `includes/class-repro-ct-suite-shortcodes.php`
- **Shortcode:** `[rcts_events]`
- **Features:** Flexible Attribute, Event-Query, Template-Rendering

### 2. Template-Loader
- **Datei:** `includes/class-repro-ct-suite-template-loader.php`
- **Features:** Theme-Override-Unterstützung, Template-Lokalisierung

### 3. Templates
- **list-simple.php:** Einfache Liste mit Emojis
- **list-grouped.php:** Nach Datum gruppierte Liste
- **cards.php:** Kachel-Ansicht mit Datum-Badge

### 4. Frontend CSS
- **Datei:** `public/css/repro-ct-suite-frontend.css`
- **Features:** Responsive, 3 Views, Hover-Effekte, Mobile-optimiert

---

## 🚀 Shortcode-Verwendung

### Basis-Beispiele

```php
// Einfache Liste (Standard)
[rcts_events]

// Kachel-Ansicht
[rcts_events view="cards"]

// Gruppierte Liste
[rcts_events view="list-grouped"]

// Nur 5 Termine
[rcts_events limit="5"]

// Bestimmte Kalender
[rcts_events calendar_ids="1,2,3"]
```

### Erweiterte Beispiele

```php
// Kacheln mit custom Feldern
[rcts_events view="cards" show_fields="title,datetime,location"]

// Nächste 14 Tage
[rcts_events to_days="14"]

// Letzte 7 Tage bis nächste 30 Tage
[rcts_events from_days="-7" to_days="30"]

// Vergangene Events anzeigen
[rcts_events show_past="true"]

// Absteigend sortiert
[rcts_events order="desc"]
```

---

## 🧪 Testing

### 1. Seite erstellen
1. WordPress Admin → Seiten → Neu
2. Titel: "Termine"
3. Shortcode einfügen: `[rcts_events view="cards"]`
4. Veröffentlichen
5. Frontend ansehen

### 2. Test-Szenarien

#### A) Liste Einfach
```
[rcts_events view="list" limit="10"]
```
**Erwartung:**
- Bullet-Liste mit Events
- Titel, Datum, Zeit, Ort
- Kalender-Badge (farbig)
- Border-left in blau

#### B) Kacheln
```
[rcts_events view="cards" limit="6"]
```
**Erwartung:**
- Grid-Layout (3 Spalten auf Desktop)
- Datum-Badge (Tag/Monat)
- Kalender-Kategorie oben
- Hover-Effekt (lift + shadow)

#### C) Gruppiert
```
[rcts_events view="list-grouped"]
```
**Erwartung:**
- Datum-Header (z.B. "Sonntag, 10. November 2025")
- Events unter jeweiligem Datum
- Zeit links, Titel rechts

### 3. Filter testen

```
// Nur Kalender-ID 1
[rcts_events calendar_ids="1"]

// Nächste 7 Tage
[rcts_events from_days="0" to_days="7"]
```

### 4. Responsive testen
- Desktop: Grid 3 Spalten
- Tablet: Grid 2 Spalten
- Mobile: Grid 1 Spalte

---

## 🎨 Theme-Override

Themes können Templates überschreiben:

```
/wp-content/themes/mein-theme/
  └─ repro-ct-suite/
      └─ events/
          ├─ list-simple.php
          ├─ list-grouped.php
          └─ cards.php
```

---

## 📝 Shortcode-Attribute (Komplett)

| Attribut | Werte | Standard | Beschreibung |
|----------|-------|----------|--------------|
| `view` | list, list-grouped, cards | list | Ansichts-Typ |
| `limit` | Zahl | 10 | Anzahl Events |
| `calendar_ids` | 1,2,3 | alle | Kalender-Filter |
| `from_days` | -7, 0, 7 | 0 | Relative Tage (Start) |
| `to_days` | 7, 30, 90 | 30 | Relative Tage (Ende) |
| `order` | asc, desc | asc | Sortierung |
| `show_past` | true, false | false | Vergangene anzeigen |
| `show_fields` | title,date,time,... | title,date,time,location | Felder |

### Verfügbare Felder:
- `title` - Event-Titel
- `date` - Nur Datum
- `time` - Nur Uhrzeit
- `datetime` - Datum + Zeit
- `location` - Ort
- `description` - Beschreibung
- `calendar` - Kalender-Name
- `image` - Bild (noch nicht implementiert)

---

## 🐛 Bekannte Einschränkungen

1. **Keine Pagination** - Nur Limit
2. **Keine Tag-Filter** - Nur Kalender
3. **Keine Detail-Seiten** - Nur Liste
4. **Kein AJAX** - Statisches Rendering

→ Wird in Phase 3 & 6 implementiert

---

## 📊 Next Steps (Phase 2)

- [ ] Admin Shortcode-Generator
- [ ] Live-Preview
- [ ] Custom CSS-Variablen
- [ ] Mehr Template-Varianten

---

## 💡 Beispiel-Seiten-Layout

### Haupt-Termine-Seite
```html
<h1>Unsere Termine</h1>
<p>Hier finden Sie alle kommenden Veranstaltungen.</p>

[rcts_events view="cards" limit="12"]
```

### Sidebar Widget (mit Plugin "Shortcode Widget")
```html
<h3>Nächste Termine</h3>
[rcts_events view="list" limit="5" show_fields="title,datetime"]
```

### Kalender-spezifische Seite
```html
<h1>Gottesdienste</h1>
[rcts_events view="list-grouped" calendar_ids="1" to_days="60"]
```
