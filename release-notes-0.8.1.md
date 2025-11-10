**FEATURE: Shortcode-Presets** 💾

## Speichern Sie Ihre Lieblings-Konfigurationen!

Mit v0.8.1 können Sie jetzt Ihre häufig verwendeten Shortcode-Konfigurationen als Presets speichern und wiederverwenden.

## 🆕 Neue Features

### Preset-Manager im Shortcode Generator

**Speichern:**
- Button "Als Preset speichern" unterhalb der Konfiguration
- Eingabe eines Preset-Namens (z.B. "Nächste Woche", "Hauptkalender")
- Speichert alle aktuellen Einstellungen

**Laden:**
- Dropdown "Gespeicherte Presets" am Anfang des Generators
- Auswahl eines Presets → Button "Laden" klicken
- Alle Formularfelder werden automatisch gefüllt

**Löschen:**
- Preset aus Dropdown auswählen
- Papierkorb-Icon klicken
- Bestätigung → Preset wird entfernt

### 5 Vordefinierte Standard-Presets

Beim ersten Update werden automatisch 5 nützliche Presets erstellt:

1. **Nächste 10 Events**
   - Ansicht: Liste (einfach)
   - Anzahl: 10
   - Zeitraum: Heute bis +90 Tage

2. **Diese Woche**
   - Ansicht: Liste (gruppiert)
   - Anzahl: 50
   - Zeitraum: Heute bis +7 Tage
   - Felder: Title, Datum, Zeit, Ort, Beschreibung

3. **Monatsübersicht**
   - Ansicht: Kacheln
   - Anzahl: 30
   - Zeitraum: Heute bis +30 Tage

4. **Letzte Veranstaltungen**
   - Ansicht: Liste (einfach)
   - Anzahl: 5
   - Zeitraum: -30 Tage bis Heute
   - Sortierung: Absteigend (neueste zuerst)
   - "Vergangene anzeigen": Ja

5. **Alle Events (Kacheln)**
   - Ansicht: Kacheln
   - Anzahl: 100
   - Zeitraum: Heute bis +365 Tage
   - Alle Felder aktiviert

## 🔧 Technische Details

### Neue Datenbank-Tabelle

**wp_rcts_shortcode_presets:**
- `id` - Preset ID
- `name` - Preset-Name
- `view` - Ansicht (list, list-grouped, cards)
- `limit_count` - Anzahl Termine
- `calendar_ids` - Ausgewählte Kalender (kommasepariert)
- `from_days` / `to_days` - Zeitraum
- `show_past` - Vergangene anzeigen
- `order_dir` - Sortierung (ASC/DESC)
- `show_fields` - Angezeigte Felder (kommasepariert)
- `created_at` / `updated_at` - Timestamps

### Neue Dateien

**Backend:**
- `includes/class-repro-ct-suite-shortcode-presets-repository.php`
  - Repository-Klasse für CRUD-Operationen
  - Methoden: `save()`, `get_all()`, `get_by_id()`, `update()`, `delete()`
  - Name-Duplikat-Check

**Migration:**
- `includes/class-repro-ct-suite-migrations.php`
  - Migration V9: Tabelle + Standard-Presets erstellen
  - DB-Version: 8 → 9

### AJAX-Handler

Neue AJAX-Endpunkte in `admin/class-repro-ct-suite-admin.php`:
- `wp_ajax_repro_ct_suite_get_presets` - Alle Presets abrufen
- `wp_ajax_repro_ct_suite_save_preset` - Neues Preset speichern
- `wp_ajax_repro_ct_suite_update_preset` - Preset aktualisieren
- `wp_ajax_repro_ct_suite_load_preset` - Preset-Daten laden
- `wp_ajax_repro_ct_suite_delete_preset` - Preset löschen

### UI-Erweiterungen

**admin/views/tabs/tab-frontend-shortcode-generator.php:**
- Preset-Manager Sektion oberhalb des Formulars
- Dropdown mit allen gespeicherten Presets
- Laden/Löschen-Buttons (nur aktiv wenn Preset ausgewählt)
- "Als Preset speichern" Button
- JavaScript für AJAX-Kommunikation
- Formular-Füllung beim Laden
- CSS für Preset-Controls

## 💡 Verwendung

### Preset erstellen:

1. Gehe zu **Repro CT-Suite → Anzeige im Frontend**
2. Tab **"Shortcode Generator"** öffnen
3. Konfiguriere deine Einstellungen (Ansicht, Anzahl, Kalender, etc.)
4. Klicke auf **"Als Preset speichern"**
5. Gib einen Namen ein (z.B. "Hauptgottesdienste")
6. Bestätigen

### Preset verwenden:

1. **Dropdown "Gespeicherte Presets"** öffnen
2. Preset auswählen
3. **"Laden"** klicken
4. Alle Felder werden automatisch gefüllt
5. Optional: Anpassungen vornehmen
6. **"Kopieren"** klicken und in Seite/Beitrag einfügen

### Preset bearbeiten:

**Aktuell:** Laden → Ändern → Unter neuem Namen speichern

**Zukünftig (v0.8.2):** Edit-Button zum direkten Überschreiben

## 🎯 Vorteile

✅ **Zeitersparnis** - Häufige Konfigurationen mit 2 Klicks laden  
✅ **Konsistenz** - Gleiche Einstellungen auf mehreren Seiten  
✅ **Einfachheit** - Keine Shortcode-Parameter merken  
✅ **Flexibilität** - Unbegrenzte Anzahl eigener Presets  
✅ **Vordefiniert** - 5 Standard-Presets direkt nutzbar  

## 📋 Bekannte Einschränkungen

- Preset-Namen müssen eindeutig sein
- Kein direkter "Edit"-Button (nur Laden → Neu speichern)
- Keine Import/Export-Funktion (geplant für v0.8.2)

## Enthält auch

Alle Features von v0.8.0:
- Admin-Seite "Anzeige im Frontend"
- Visueller Shortcode Generator
- Live-Vorschau
- Template-Dokumentation
- CSS-Referenz
