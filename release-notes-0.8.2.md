**FEATURE: Preset-Shortcode** 🎯

## Kurze, lesbare Shortcodes mit Preset-Namen!

Mit v0.8.2 können Sie jetzt gespeicherte Presets direkt im Shortcode verwenden - **viel kürzer und lesbarer**!

## 🆕 Neue Shortcode-Syntax

### Statt langer Parameter:
```
[rcts_events view="cards" limit="10" calendar_ids="1,2" from_days="0" to_days="90" show_fields="title,date,time,location"]
```

### Jetzt einfach:
```
[rcts_events preset="Nächste 10 Events"]
```

## 💡 Verwendung

### 1. Preset-Shortcode aktivieren

Im **Shortcode Generator**:
1. Konfiguration erstellen
2. **"Als Preset speichern"** klicken
3. Name eingeben (z.B. "Hauptkalender")
4. ✅ **"Preset-Shortcode verwenden"** aktivieren
5. Generierter Code kopieren: `[rcts_events preset="Hauptkalender"]`

### 2. Parameter überschreiben (Optional)

Preset-Werte dienen als **Defaults**, können aber überschrieben werden:

```
[rcts_events preset="Nächste 10 Events" limit="20"]
```
↑ Verwendet das Preset, zeigt aber 20 statt 10 Events

```
[rcts_events preset="Diese Woche" calendar_ids="1,3"]
```
↑ Verwendet das Preset, filtert aber nur Kalender 1 und 3

```
[rcts_events preset="Monatsübersicht" to_days="60"]
```
↑ Verwendet das Preset, erweitert aber auf 60 Tage

### 3. Fehlerbehandlung

Wenn Preset nicht gefunden:
```
Preset "Xyz" nicht gefunden.
```

## 🔧 Technische Details

### Shortcode-Handler Erweiterung

**includes/class-repro-ct-suite-shortcodes.php:**
- Neuer Parameter: `preset="Name"`
- Methode `load_preset_by_name()` lädt Preset aus DB
- Preset-Werte werden als **Default-Attribute** verwendet
- Shortcode-Parameter haben **höchste Priorität** (Override)

**Ablauf:**
1. Prüfe ob `preset` Parameter vorhanden
2. Lade Preset aus `wp_rcts_shortcode_presets` via Repository
3. Konvertiere Preset-Daten in Shortcode-Attribute
4. Merge mit Default-Attributen
5. Überschreibe mit expliziten Shortcode-Parametern
6. Rendere Template mit finalen Attributen

### UI-Erweiterung

**admin/views/tabs/tab-frontend-shortcode-generator.php:**
- Neue Checkbox: **"Preset-Shortcode verwenden"**
- JavaScript Variable: `currentPresetName` wird gesetzt beim Speichern/Laden
- Funktion `generateShortcode()` prüft Checkbox-Status
- Generiert entweder:
  - Preset-Shortcode: `[rcts_events preset="Name"]`
  - Standard-Shortcode: `[rcts_events view="..." limit="..."]`
- Warnung wenn Checkbox aktiviert aber kein Preset gespeichert

### Preset-Mapping

```php
// Preset-DB-Felder → Shortcode-Attribute
view         → view
limit_count  → limit
calendar_ids → calendar_ids  
from_days    → from_days
to_days      → to_days
show_past    → show_past (0/1 → false/true)
order_dir    → order (ASC/DESC → asc/desc)
show_fields  → show_fields
```

## 📋 Beispiele

### Standard-Presets verwenden:

```
[rcts_events preset="Nächste 10 Events"]
```
→ Liste (einfach), 10 Termine, 0-90 Tage

```
[rcts_events preset="Diese Woche"]
```
→ Liste (gruppiert), 50 Termine, 0-7 Tage, alle Felder

```
[rcts_events preset="Monatsübersicht"]
```
→ Kacheln, 30 Termine, 0-30 Tage

```
[rcts_events preset="Letzte Veranstaltungen"]
```
→ Liste, 5 Termine, -30-0 Tage, absteigend, Vergangenheit aktiv

### Mit Parameter-Override:

```
[rcts_events preset="Nächste 10 Events" limit="20" view="cards"]
```
→ Basis-Preset + 20 Termine als Kacheln

```
[rcts_events preset="Diese Woche" calendar_ids="1"]
```
→ Basis-Preset + nur Kalender ID 1

```
[rcts_events preset="Monatsübersicht" show_fields="title,date"]
```
→ Basis-Preset + nur Title und Datum anzeigen

## 🎯 Vorteile

✅ **Lesbarkeit** - Name erklärt den Zweck  
✅ **Wartbarkeit** - Preset ändern statt Shortcode überall anpassen  
✅ **Flexibilität** - Override einzelner Parameter möglich  
✅ **Konsistenz** - Gleiche Config auf mehreren Seiten  
✅ **Einfachheit** - Keine Parameter-Syntax merken  

## 🔄 Kombination mit v0.8.1

Funktioniert perfekt mit den **Preset-Verwaltungsfunktionen**:
- Presets speichern/laden/löschen
- 5 vordefinierte Standard-Presets
- Datenbank-gestützte Verwaltung
- Unbegrenzte eigene Presets

## 📊 Workflow

1. **Konfiguration erstellen** im Generator
2. **Als Preset speichern** mit eindeutigem Namen
3. **Checkbox aktivieren** "Preset-Shortcode verwenden"
4. **Shortcode kopieren**: `[rcts_events preset="Name"]`
5. **In Seite einfügen** - fertig!

**Optional:**
6. **Parameter überschreiben** bei Bedarf
7. **Preset aktualisieren** für alle Shortcodes gleichzeitig

## Enthält auch

Alle Features von v0.8.0 und v0.8.1:
- Admin-Seite "Anzeige im Frontend"
- Visueller Shortcode Generator
- Preset-Verwaltung (Speichern/Laden/Löschen)
- 5 Standard-Presets
- Live-Vorschau
- Template-Dokumentation
