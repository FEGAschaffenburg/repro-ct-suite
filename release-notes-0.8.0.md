**MAJOR FEATURE: Admin-Seite "Anzeige im Frontend"** 🎨

## Phase 3 des Frontend-Plans abgeschlossen! ✅

Eine vollständig neue Admin-Seite für die Konfiguration der Frontend-Anzeige.

## 🆕 Neue Admin-Seite

**Menü:** Repro CT-Suite → **Anzeige im Frontend**

### 4 Tabs

#### 1. Shortcode Generator 🛠️
- **Visueller Konfigurator** für alle Shortcode-Attribute
- **Live-Vorschau** der generierten Termine
- **Copy-Button** zum schnellen Kopieren
- Dropdown-Auswahl für:
  - Ansicht (Liste, Gruppiert, Kacheln)
  - Anzahl Termine (1-100)
  - Kalender (Mehrfachauswahl)
  - Zeitraum (von/bis Tage)
  - Sortierung (auf/absteigend)
  - Angezeigte Felder (Title, Datum, Zeit, Ort, Beschreibung, Kalender)
- **Verwendungsbeispiele** für häufige Szenarien

#### 2. Template-Varianten 📋
- Übersicht aller 3 verfügbaren Templates
- Vorschau-Bilder (Platzhalter für spätere Screenshots)
- Direkte Links zu Template-Dateien
- **Theme-Override Anleitung:**
  - Ordnerstruktur
  - Kopierschritte
  - Verfügbare Template-Variablen ($events, $show_fields, $event->title, etc.)

#### 3. Styling 🎨
- **CSS-Klassen Referenz:**
  - Container-Klassen (.rcts-events, .rcts-events-list-simple, etc.)
  - Event-Klassen (.rcts-event-item, .rcts-event-title, etc.)
- Anleitung zum Hinzufügen von Custom CSS
- **Code-Beispiele** für häufige Anpassungen
  - Farben ändern
  - Hover-Effekte
  - Kalender-Badges

#### 4. Vorschau 👁️
- **Interaktive Live-Vorschau** verschiedener Konfigurationen
- Vordefinierte Templates zum Testen
- Reload-Button für manuelle Aktualisierung
- AJAX-basiert (kein Seiten-Reload)

## 🔧 Technische Details

### Neue Dateien

**Admin-Seite:**
- `admin/views/admin-frontend.php` - Haupt-View mit Tab-Navigation

**Tab-Views:**
- `admin/views/tabs/tab-frontend-shortcode-generator.php` - Shortcode Generator (421 Zeilen)
- `admin/views/tabs/tab-frontend-templates.php` - Template-Varianten (171 Zeilen)
- `admin/views/tabs/tab-frontend-styling.php` - Styling-Referenz (102 Zeilen)
- `admin/views/tabs/tab-frontend-preview.php` - Live-Vorschau (102 Zeilen)

**Admin-Controller:**
- `admin/class-repro-ct-suite-admin.php`:
  - Neue Submenu-Page `repro-ct-suite-frontend`
  - Handler-Methode `display_frontend_page()`
  - AJAX-Handler `ajax_preview_shortcode()`

### Features

✅ **Visueller Shortcode Generator** mit 11 Konfigurationsoptionen  
✅ **Live-Vorschau** mit AJAX (kein Page-Reload)  
✅ **Copy-to-Clipboard** Funktion  
✅ **Kalender-Dropdown** mit farbigen Optionen  
✅ **Responsive Layout** (2-Spalten Desktop, 1-Spalte Mobile)  
✅ **Tab-Navigation** mit URL-Hash Support  
✅ **Theme-Override Dokumentation**  
✅ **CSS-Referenz** mit Code-Beispielen  

## 💡 Verwendung

### Shortcode generieren:

1. Gehe zu **Repro CT-Suite → Anzeige im Frontend**
2. Wähle Ansicht, Anzahl, Kalender, etc.
3. Klicke auf **"Shortcode generieren"**
4. Prüfe die Live-Vorschau
5. Klicke auf **"Kopieren"**
6. Füge den Shortcode in deinen Beitrag/Seite ein

### Beispiel-Outputs:

```
[rcts_events view="cards" limit="12"]
[rcts_events calendar_ids="1,2" to_days="7"]
[rcts_events view="list-grouped" from_days="-7" show_past="true"]
```

## 📋 Frontend-Plan Status

- ✅ **Phase 1:** Basis-Shortcode System (v0.7.0)
- ✅ **Phase 2:** Template-Varianten (3 Views vorhanden)
- ✅ **Phase 3:** Shortcode Generator (v0.8.0) ← **AKTUELL**
- ⏳ **Phase 4:** Elementor-Integration
- ⏳ **Phase 5:** Standard-Seiten Generator
- ⏳ **Phase 6:** Kalender/Timeline-Ansicht
- ⏳ **Phase 7:** Performance & Caching

## 🎯 Nächste Schritte

**Phase 4 (Elementor-Integration):**
- Elementor-Widget für Events-Anzeige
- Live-Editor-Preview
- Visuelle Controls für alle Optionen
- Style-Tab für Farben und Abstände

## Enthält auch

Alle Features von v0.7.0 - v0.7.4:
- Frontend Shortcode System
- 3 Template-Ansichten
- WordPress Zeitformat-Support
- Automatischer Sync funktional
