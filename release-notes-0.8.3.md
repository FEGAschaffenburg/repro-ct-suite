# Release Notes - Version 0.8.3

**Release-Typ:** BUGFIX  
**Release-Datum:** 2025-01-28  
**Commit:** 9e7486e  
**Tag:** v0.8.3

---

## 🐛 Kritische Bugfixes (2x)

### Problem 1: Kalender-Zuordnung funktionierte nicht (JOIN)

In Version 0.8.2 und älter wurden Kalender-Namen und -Farben nicht korrekt zu den Events zugeordnet. Im Frontend erschienen Events ohne Kalender-Badge oder mit falschen/fehlenden Kalendern.

### Problem 2: Kalender-Filter ignorierte calendar_id Typ (WHERE)

Der Shortcode-Parameter `calendar_ids="1,2,3"` funktionierte nicht korrekt, weil die IDs als Integer statt als String behandelt wurden.

### Ursache 1: Falscher JOIN

Der SQL JOIN in `includes/class-repro-ct-suite-shortcodes.php` verwendete die falsche Spalte:

```php
// FALSCH (v0.8.2 und älter):
LEFT JOIN {$wpdb->prefix}rcts_calendars c ON e.calendar_id = c.id
```

**Problem:** Die Tabelle `wp_rcts_calendars` hat zwei ID-Spalten:
- `id` (BIGINT, auto-increment, Primary Key) - WordPress interne ID
- `calendar_id` (VARCHAR, UNIQUE) - ChurchTools externe ID

Die Tabelle `wp_rcts_events` speichert in `calendar_id` die **ChurchTools-ID als VARCHAR**, nicht die WordPress-ID.

Der JOIN verglich also:
- `e.calendar_id` (VARCHAR, z.B. "123") 
- `c.id` (BIGINT, z.B. 1, 2, 3)

→ **Resultat:** Keine Matches, alle Kalender-Informationen blieben NULL

### Ursache 2: Falscher Typ im Filter

Der Kalender-Filter konvertierte die IDs zu Integer:

```php
// FALSCH (v0.8.2 und älter):
$calendar_ids = array_map( 'intval', explode( ',', $atts['calendar_ids'] ) );
$placeholders = implode( ',', array_fill( 0, count( $calendar_ids ), '%d' ) );
$where[] = $wpdb->prepare( "e.calendar_id IN ($placeholders)", $calendar_ids );
```

**Problem:** `calendar_id` ist VARCHAR, nicht INT
- `intval("123")` → 123 (Integer)
- Query vergleicht: `WHERE calendar_id IN (123)` statt `WHERE calendar_id IN ('123')`

→ **Resultat:** Keine Matches, Filter funktionierte nicht

### Lösung 1: JOIN korrigiert

```php
// RICHTIG (v0.8.3):
LEFT JOIN {$wpdb->prefix}rcts_calendars c ON e.calendar_id = c.calendar_id
```

Jetzt werden beide VARCHAR-Spalten verglichen:
- `e.calendar_id` (VARCHAR, ChurchTools-ID aus Events-Tabelle)
- `c.calendar_id` (VARCHAR, ChurchTools-ID aus Kalendar-Tabelle)

→ **Resultat:** Korrekte Zuordnung, Kalender-Namen und -Farben werden angezeigt

### Lösung 2: Filter korrigiert

```php
// RICHTIG (v0.8.3):
$calendar_ids = array_map( 'sanitize_text_field', explode( ',', $atts['calendar_ids'] ) );
$placeholders = implode( ',', array_fill( 0, count( $calendar_ids ), '%s' ) );
$where[] = $wpdb->prepare( "e.calendar_id IN ($placeholders)", $calendar_ids );
```

Jetzt werden die IDs als Strings behandelt:
- `sanitize_text_field("123")` → "123" (String)
- Query vergleicht: `WHERE calendar_id IN ('123', '456')`

→ **Resultat:** Filter funktioniert korrekt

---

## 📋 Änderungen im Detail

### Geänderte Datei

**`includes/class-repro-ct-suite-shortcodes.php`**

**Zeile 147 - JOIN korrigiert:**
```diff
- LEFT JOIN {$wpdb->prefix}rcts_calendars c ON e.calendar_id = c.id
+ LEFT JOIN {$wpdb->prefix}rcts_calendars c ON e.calendar_id = c.calendar_id
```

**Zeilen 128-130 - Filter korrigiert:**
```diff
- $calendar_ids = array_map( 'intval', explode( ',', $atts['calendar_ids'] ) );
- $placeholders = implode( ',', array_fill( 0, count( $calendar_ids ), '%d' ) );
+ $calendar_ids = array_map( 'sanitize_text_field', explode( ',', $atts['calendar_ids'] ) );
+ $placeholders = implode( ',', array_fill( 0, count( $calendar_ids ), '%s' ) );
```

### Betroffene Funktionalität

- ✅ Frontend-Shortcode `[rcts_events]`
- ✅ Alle Template-Varianten:
  - `list-simple.php`
  - `list-grouped.php`
  - `cards.php`
- ✅ Kalender-Badges (Name + Farbe)
- ✅ Kalender-Filter im Shortcode
- ✅ Multi-Kalender-Setups

### Nicht betroffen

Dieser Bug betraf **nur** die Events-Anzeige. Folgende Bereiche waren nicht betroffen:
- ❌ Sync-Prozess (Daten wurden korrekt gespeichert)
- ❌ Admin-Bereich (Kalender-Listen funktionierten)
- ❌ Datenbank-Struktur (Daten waren vollständig)

---

## 🔍 So erkennst du, ob du betroffen warst

### Symptome des Bugs

1. **Frontend-Anzeige:**
   - Events werden angezeigt, aber **ohne Kalender-Namen**
   - **Keine farbigen Badges** bei den Events
   - Template-Variable `$event->calendar_name` ist leer
   - Template-Variable `$event->calendar_color` ist leer

2. **Multi-Kalender-Setup:**
   - Filter `calendar_ids="1,2"` funktionierte nicht zuverlässig
   - Alle Events sahen gleich aus (keine visuelle Unterscheidung)

3. **Template-Output:**
   ```html
   <!-- BUG: Kalender-Info fehlt -->
   <div class="rcts-event-calendar"></div>
   
   <!-- NACH FIX: Kalender-Badge erscheint -->
   <div class="rcts-event-calendar" style="background-color: #3498db;">
       <span>Gottesdienste</span>
   </div>
   ```

### Prüfung nach Update

1. Frontend-Seite mit Shortcode `[rcts_events]` aufrufen
2. Kalender-Badges sollten jetzt sichtbar sein
3. Bei mehreren Kalendern: Events sind farblich unterscheidbar
4. Template zeigt `$event->calendar_name` korrekt an

---

## 🚀 Upgrade-Anleitung

### Sofort-Update (empfohlen)

```bash
# 1. Plugin deaktivieren (optional, aber sicher)
# 2. Alte Version löschen
# 3. Neue Version hochladen
# 4. Plugin aktivieren
# 5. Frontend testen
```

### ZIP-Installation

1. Download: `repro-ct-suite-0.8.3.zip`
2. WordPress-Admin → Plugins → Installieren → Upload
3. Alte Version wird automatisch überschrieben
4. Keine Datenbank-Migration nötig ✅
5. Keine Einstellungen gehen verloren ✅

### Git-Installation

```bash
cd wp-content/plugins/repro-ct-suite
git fetch origin
git checkout v0.8.3
```

### Keine Breaking Changes

- ✅ Kompatibel mit allen v0.8.x Versionen
- ✅ Keine neuen Abhängigkeiten
- ✅ Keine Datenbankänderungen
- ✅ Bestehende Shortcodes funktionieren weiter
- ✅ Bestehende Presets funktionieren weiter

---

## 🧪 Testing

### Test-Szenario 1: Einfache Event-Liste

**Shortcode:**
```
[rcts_events limit="10"]
```

**Erwartet:**
- ✅ Events werden angezeigt
- ✅ Jedes Event hat Kalender-Badge
- ✅ Kalender-Name ist sichtbar
- ✅ Badge hat Hintergrundfarbe

### Test-Szenario 2: Multi-Kalender mit Filter

**Shortcode:**
```
[rcts_events calendar_ids="1,2,3" view="cards"]
```

**Erwartet:**
- ✅ Nur Events aus Kalendern 1, 2, 3
- ✅ Jeder Kalender hat eigene Farbe
- ✅ Kalender-Namen unterscheiden sich
- ✅ Cards zeigen Kalender-Info korrekt

### Test-Szenario 3: Template-Variablen

**Template:** `list-simple.php`

```php
<?php foreach ($events as $event): ?>
    <div class="event">
        <!-- Diese Variablen müssen jetzt Werte haben: -->
        <p>Kalender: <?php echo esc_html($event->calendar_name); ?></p>
        <span style="background-color: <?php echo esc_attr($event->calendar_color); ?>">
            <?php echo esc_html($event->calendar_name); ?>
        </span>
    </div>
<?php endforeach; ?>
```

**Erwartet:**
- ✅ `$event->calendar_name` ist nicht leer
- ✅ `$event->calendar_color` ist ein Hex-Code (z.B. `#3498db`)

---

## 📊 Technische Details

### Datenbank-Schema

**Tabelle: `wp_rcts_calendars`**
```sql
CREATE TABLE wp_rcts_calendars (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    calendar_id VARCHAR(255) NOT NULL,  -- ← ChurchTools-ID
    name VARCHAR(255) NOT NULL,
    color VARCHAR(7),
    PRIMARY KEY (id),
    UNIQUE KEY calendar_id (calendar_id)  -- ← UNIQUE
);
```

**Tabelle: `wp_rcts_events`**
```sql
CREATE TABLE wp_rcts_events (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    calendar_id VARCHAR(255) NOT NULL,  -- ← ChurchTools-ID
    title TEXT NOT NULL,
    start_datetime DATETIME NOT NULL,
    -- ... weitere Felder
    PRIMARY KEY (id),
    KEY calendar_id (calendar_id)
);
```

### SQL Query (vor und nach Fix)

**VORHER (v0.8.2):**
```sql
SELECT 
    e.*,
    c.name AS calendar_name,
    c.color AS calendar_color
FROM wp_rcts_events e
LEFT JOIN wp_rcts_calendars c ON e.calendar_id = c.id  -- ❌ VARCHAR = BIGINT
WHERE e.start_datetime >= NOW()
ORDER BY e.start_datetime ASC
LIMIT 10;
```

**Ergebnis:** `calendar_name` und `calendar_color` immer NULL

**NACHHER (v0.8.3):**
```sql
SELECT 
    e.*,
    c.name AS calendar_name,
    c.color AS calendar_color
FROM wp_rcts_events e
LEFT JOIN wp_rcts_calendars c ON e.calendar_id = c.calendar_id  -- ✅ VARCHAR = VARCHAR
WHERE e.start_datetime >= NOW()
ORDER BY e.start_datetime ASC
LIMIT 10;
```

**Ergebnis:** `calendar_name` und `calendar_color` korrekt gefüllt

### Performance-Impact

- ✅ **Keine Performance-Verschlechterung**
- ✅ JOIN auf indexed Spalte (`calendar_id` hat UNIQUE KEY)
- ✅ Query-Plan bleibt identisch
- ✅ Keine zusätzlichen Queries

---

## 🔗 Zusammenhang mit anderen Versionen

### v0.8.0 - v0.8.2
- **Problem existierte:** Ja, seit Einführung des Shortcode-Generators
- **Warum nicht früher aufgefallen:** Oft werden nur einzelne Kalender verwendet
- **Multi-Kalender:** Bug war dort sofort sichtbar

### v0.8.1 - Presets
- **Betroffen:** Ja, gespeicherte Presets zeigten keine Kalender
- **Nach Fix:** Bestehende Presets funktionieren automatisch korrekt
- **Keine Änderung nötig:** Presets müssen nicht neu erstellt werden

### v0.8.2 - Preset-Shortcodes
- **Betroffen:** Ja, `[rcts_events preset="Name"]` hatte gleichen Bug
- **Nach Fix:** Preset-Parameter funktioniert mit Kalender-Info
- **Kompatibel:** Alle Preset-Shortcodes funktionieren weiter

---

## 📝 Changelog-Eintrag

```
= 0.8.3 =
* KRITISCHER FIX: Kalender-Zuordnung zu Events korrigiert
* SQL JOIN verwendet jetzt korrekte Spalte: c.calendar_id statt c.id
* Behebt Problem: Kalender-Namen und -Farben wurden nicht angezeigt
* Betrifft Frontend-Shortcode [rcts_events]
* Grund: wp_rcts_calendars hat id (BIGINT, auto-increment) UND calendar_id (VARCHAR, ChurchTools-ID)
* Events speichern calendar_id als VARCHAR, daher muss JOIN auf VARCHAR-Spalten erfolgen
```

---

## 🎯 Empfehlung

**Alle Nutzer sollten auf v0.8.3 updaten**, besonders wenn:

1. ✅ Multi-Kalender-Setup verwendet wird
2. ✅ Kalender-Badges im Frontend fehlen
3. ✅ Template-Customizations `$event->calendar_name` verwenden
4. ✅ Visuelle Unterscheidung zwischen Kalendern gewünscht ist

**Update ist sicher:**
- Keine Datenbankänderungen
- Keine Konfigurationsänderungen
- Keine Breaking Changes
- Nur Bugfix, keine neuen Features

---

## 📦 Download

- **GitHub Release:** [v0.8.3](https://github.com/FEGAschaffenburg/repro-ct-suite/releases/tag/v0.8.3)
- **ZIP-Datei:** `repro-ct-suite-0.8.3.zip` (0.76 MB)
- **Commit:** [7fe8476](https://github.com/FEGAschaffenburg/repro-ct-suite/commit/7fe8476)

---

## 🙏 Credits

Vielen Dank an die Community für die Meldung dieses Bugs!

**Entdeckt von:** User Testing  
**Gemeldet:** 2025-01-28  
**Behoben:** 2025-01-28  
**Zeit bis Fix:** < 1 Stunde

---

## 📞 Support

Bei Problemen:
1. [GitHub Issues](https://github.com/FEGAschaffenburg/repro-ct-suite/issues)
2. Prüfe Console auf SQL-Errors
3. Teste mit Shortcode `[rcts_events limit="5"]`
4. Prüfe ob Kalender-Daten in DB vorhanden sind

---

**Version:** 0.8.3  
**Typ:** Bugfix Release  
**Priorität:** Hoch (betrifft Frontend-Darstellung)  
**Update empfohlen:** Ja, für alle Nutzer
