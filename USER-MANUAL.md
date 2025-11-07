# User Manual - Repro CT-Suite v0.9.0

## 📖 Komplette Anleitung für Administratoren und Benutzer

---

## 🚀 **Installation & Erstkonfiguration**

### **Schritt 1: Plugin installieren**

#### **Option A: WordPress Admin (empfohlen)**
1. WordPress-Admin → **Plugins** → **Installieren**
2. **Plugin hochladen** klicken
3. `repro-ct-suite-0.9.0.zip` auswählen
4. **Jetzt installieren** → **Aktivieren**

#### **Option B: FTP-Upload**
1. ZIP-Datei entpacken
2. Ordner `repro-ct-suite` nach `/wp-content/plugins/` hochladen
3. WordPress-Admin → **Plugins** → **Repro CT-Suite** aktivieren

### **Schritt 2: ChurchTools-Verbindung einrichten**

1. **Admin-Menü** → **Repro CT-Suite** → **Einstellungen**
2. **ChurchTools-Zugangsdaten** eingeben:
   - **Tenant**: Ihre Subdomain (z.B. `gemeinde` für `gemeinde.church.tools`)
   - **Benutzername**: ChurchTools-Login (E-Mail oder Benutzername)
   - **Passwort**: ChurchTools-Passwort
3. **Änderungen speichern**
4. **Verbindung testen** klicken → Erfolg bestätigen

### **Schritt 3: Erste Synchronisation**

1. **Tab "Sync"** öffnen
2. **Kalender synchronisieren** klicken
3. Gewünschte Kalender **auswählen** und **Speichern**
4. **Termine synchronisieren** klicken
5. Erfolgsmeldung und Statistik prüfen

---

## 🗂️ **Admin-Bereiche Übersicht**

### **Dashboard** 📊
- **System-Status**: Plugin-Version, Datenbankverbindung
- **Sync-Übersicht**: Letzte Synchronisation, nächste geplante Ausführung
- **Schnelle Aktionen**: Sofort-Sync, Verbindungstest

### **Einstellungen** ⚙️
- **ChurchTools-Zugangsdaten**: Tenant, Username, Passwort
- **Cron-Konfiguration**: Automatische Synchronisation ein/aus
- **Debug-Optionen**: Logging-Level, Syslog-Output

### **Sync** 🔄
- **Kalender-Verwaltung**: ChurchTools-Kalender auswählen/abwählen
- **Synchronisation**: Manuelle Termine-Synchronisation
- **Zeitraum-Einstellungen**: Von/Bis-Datum für Sync-Bereich

### **Termine** 📅
- **Übersicht**: Alle synchronisierten Events und Appointments
- **Filter**: Nach Kalender, Datum, Typ filtern
- **Aktionen**: Einzelne Termine bearbeiten/löschen

### **Anzeige im Frontend** 🎨
- **Shortcode Generator**: Visueller Shortcode-Builder
- **Shortcode Manager**: Moderne Preset-Verwaltung (v0.9.0)
- **Templates**: Template-Übersicht und Anpassungsanleitung
- **Styling**: CSS-Referenz für Design-Anpassungen

### **Logs** 📋
- **Debug-Logs**: Detaillierte Sync-Protokolle
- **System-Logs**: Plugin-Aktivitäten und Fehler
- **Log-Verwaltung**: Logs leeren/herunterladen

---

## 🎨 **Frontend: Shortcode Manager (v0.9.0)**

### **Überblick**
Der Shortcode Manager bietet eine moderne Benutzeroberfläche zur Verwaltung Ihrer Event-Anzeigen.

### **Shortcode-Liste**
- **Grid-Ansicht**: Übersichtliche Karten mit Vorschau-Informationen
- **Listen-Ansicht**: Kompakte Tabellenansicht
- **Suche**: Shortcodes in Echtzeit durchsuchen
- **Aktionen**: Bearbeiten, Duplizieren, Löschen

### **Shortcode erstellen/bearbeiten**

#### **Basis-Informationen**
- **Name**: Eindeutiger Name für den Shortcode (z.B. "Gottesdienste")
- **Beschreibung**: Optional für interne Notizen

#### **Darstellung**
- **Ansicht**: 
  - `list` - Einfache Liste
  - `list-grouped` - Nach Datum gruppiert
  - `cards` - Moderne Kachel-Ansicht
- **Anzahl Termine**: 1-100 (empfohlen: 10-20)

#### **Kalender-Filter**
- **Alle Kalender**: Zeigt Events aus allen ausgewählten Kalendern
- **Spezifische Kalender**: Nur ausgewählte Kalender anzeigen
- **Farb-Indikatoren**: Kalender-Farben werden angezeigt

#### **Zeitraum**
- **Von (Tage)**: Tage relativ zu heute (-7 = 7 Tage in Vergangenheit)
- **Bis (Tage)**: Tage relativ zu heute (30 = 30 Tage in Zukunft)
- **Vergangene Termine**: Ein/Aus-Schalter

#### **Sortierung**
- **Aufsteigend**: Älteste Termine zuerst (chronologisch)
- **Absteigend**: Neueste Termine zuerst

#### **Angezeigte Felder**
- **Titel**: Immer angezeigt
- **Datum**: Datum des Events
- **Uhrzeit**: Start-/Endzeit
- **Ort**: Event-Location
- **Beschreibung**: Event-Beschreibung
- **Kalender**: Kalender-Name und -Farbe

### **Live-Vorschau**
- **Automatisch**: Updates alle 1-2 Sekunden nach Änderungen
- **Manuell**: Refresh-Button für sofortige Aktualisierung
- **Loading-State**: Spinner während Lade-Vorgängen

### **Generierter Shortcode**
- **Standard-Format**: `[rcts_events view="cards" limit="10" ...]`
- **Preset-Format**: `[rcts_events preset="Name"]` (kürzer)
- **Copy-Button**: Ein-Klick-Kopieren in Zwischenablage

---

## 🔧 **Shortcodes verwenden**

### **Grundlegende Verwendung**
```
[rcts_events]
```
Zeigt die nächsten 10 Termine in Karten-Ansicht.

### **Erweiterte Parameter**
```
[rcts_events view="list-grouped" limit="20" calendar_ids="1,2" to_days="60"]
```

### **Preset-Shortcodes** (empfohlen)
```
[rcts_events preset="Gottesdienste"]
```
Verwendet gespeicherte Konfiguration namens "Gottesdienste".

### **Parameter-Übersicht**

| Parameter | Werte | Standard | Beschreibung |
|-----------|-------|----------|--------------|
| `view` | `list`, `list-grouped`, `cards` | `cards` | Darstellungsart |
| `limit` | 1-100 | 10 | Anzahl Termine |
| `calendar_ids` | `1,2,3` | alle | Spezifische Kalender |
| `from_days` | `-30` bis `365` | 0 | Start-Zeitraum |
| `to_days` | `1` bis `365` | 30 | End-Zeitraum |
| `show_past` | `true`, `false` | `false` | Vergangene Termine |
| `order` | `asc`, `desc` | `asc` | Sortier-Reihenfolge |
| `show_fields` | `title,date,time,...` | alle | Anzuzeigende Felder |
| `preset` | Preset-Name | - | Gespeicherte Konfiguration |

---

## 🎨 **Design-Anpassungen**

### **CSS-Klassen für Styling**
```css
/* Event-Container */
.rcts-events-container { }

/* Einzelnes Event */
.rcts-event { }

/* Event in Listen-Ansicht */
.rcts-event-list-item { }

/* Event in Karten-Ansicht */
.rcts-event-card { }

/* Event-Titel */
.rcts-event-title { }

/* Event-Datum */
.rcts-event-date { }

/* Event-Zeit */
.rcts-event-time { }

/* Event-Ort */
.rcts-event-location { }

/* Kalender-Badge */
.rcts-event-calendar { }
```

### **Template-Überschreibung**
Kopieren Sie Template-Dateien nach:
```
/wp-content/themes/ihr-theme/repro-ct-suite/events/
├── list-simple.php      # Einfache Liste
├── list-grouped.php     # Gruppierte Liste  
└── cards.php           # Karten-Ansicht
```

---

## 🔧 **Häufige Probleme & Lösungen**

### **Verbindung zu ChurchTools fehlgeschlagen**
```
Lösung:
1. Zugangsdaten überprüfen (Tenant, Username, Passwort)
2. ChurchTools-URL testen: https://IHR-TENANT.church.tools
3. Benutzer-Berechtigung in ChurchTools prüfen
4. Firewall/Server-Einstellungen überprüfen
```

### **Termine werden nicht angezeigt**
```
Lösung:
1. Synchronisation erfolgreich? → Logs prüfen
2. Kalender ausgewählt? → Sync-Tab überprüfen
3. Zeitraum korrekt? → from_days/to_days anpassen
4. Shortcode korrekt? → Generator verwenden
```

### **Shortcode zeigt Fehler**
```
Lösung:
1. Plugin aktiviert? → Plugins-Seite prüfen
2. Syntax korrekt? → [rcts_events] ohne Leerzeichen
3. Parameter gültig? → Generator verwenden
4. Cache leeren → Caching-Plugin oder Browser
```

### **Design passt nicht zum Theme**
```
Lösung:
1. Theme-Templates kopieren und anpassen
2. Custom CSS in Customizer hinzufügen
3. CSS-Klassen des Plugins überschreiben
4. Template-Dateien direkt bearbeiten
```

### **Performance-Probleme**
```
Lösung:
1. Limit reduzieren (max. 50 Termine)
2. Zeitraum eingrenzen (max. 90 Tage)
3. Caching-Plugin aktivieren
4. Sync-Intervall vergrößern
```

---

## 🛠️ **Wartung & Updates**

### **Automatische Updates** (empfohlen)
Das Plugin prüft automatisch auf neue Versionen und zeigt Update-Benachrichtigungen.

### **Manuelle Updates**
1. Neue Version herunterladen
2. Alte Version deaktivieren (Daten bleiben erhalten)
3. Neue Version hochladen und aktivieren
4. Synchronisation testen

### **Daten-Backup**
```
Automatisch gesichert:
- Plugin-Einstellungen
- Gespeicherte Presets
- Kalender-Konfiguration

Manuell sichern:
- Synchronisierte Events (Datenbank-Export)
- Custom-Templates (Theme-Ordner)
- CSS-Anpassungen
```

### **Regelmäßige Wartung**
- **Täglich**: Sync-Status überprüfen
- **Wöchentlich**: Logs durchsehen
- **Monatlich**: Plugin-Updates installieren
- **Quartalsweise**: Vollständige Synchronisation

---

## 📞 **Support & Hilfe**

### **Erste Anlaufstellen**
1. **Plugin-Dokumentation**: Alle Tabs im Admin-Bereich
2. **Live-Vorschau**: Shortcode Generator testen
3. **Debug-Logs**: Detaillierte Fehler-Informationen
4. **WordPress-Community**: Forum-Diskussionen

### **Support-Informationen sammeln**
```
Bei Support-Anfragen bitte bereitstellen:
- WordPress-Version
- Plugin-Version
- PHP-Version
- ChurchTools-Version
- Fehler-Logs (Debug-Tab)
- Screenshots des Problems
- Verwendete Shortcodes
```

### **Erweiterte Hilfe**
- **GitHub-Repository**: Bug-Reports und Feature-Requests
- **Entwickler-Dokumentation**: API-Integration und Customizations
- **Theme-Entwickler**: Template-Override-Anleitungen

---

**🎯 Viel Erfolg mit Repro CT-Suite! Bei Fragen stehen wir gerne zur Verfügung.**