# Release 0.4.0.1 - Debug Page DB V6 Fix

## 🔧 Bug Fix Release: Debug-Seite DB Version 6 Kompatibilität

Diese Version behebt Probleme mit der Debug-Seite nach dem großen Unified Sync System Update.

### 🐛 Behobene Probleme

- **Debug-Seite Tabellen-Anzeige**: Alle 5 Tabellen werden jetzt korrekt angezeigt
- **Fehlende Schedule-Tabelle**: `rcts_schedule` Tabelle in Debug-Übersicht hinzugefügt
- **AJAX-Handler Kompatibilität**: Vollständige Unterstützung für neue Schedule-Tabelle

### 📋 Korrigierte Debug-Funktionen

- **Tabellen-Übersicht**: Zeigt alle DB V6 Tabellen (calendars, events, appointments, event_services, schedule)
- **Einzeltabelle leeren**: Funktioniert jetzt für alle Tabellen inkl. Schedule
- **Alle Tabellen leeren**: Kompletter Reset berücksichtigt neue Tabellen-Struktur
- **Konsistente Behandlung**: Alle Komponenten verwenden dieselbe Tabellenliste

### 🎯 Was funktioniert jetzt

| Tabelle | Debug-Anzeige | Einzeln leeren | Komplett leeren | Uninstall |
|---------|---------------|----------------|-----------------|-----------|
| `rcts_calendars` | ✅ | ✅ | ✅ | ✅ |
| `rcts_events` | ✅ | ✅ | ✅ | ✅ |
| `rcts_appointments` | ✅ | ✅ | ✅ | ✅ |
| `rcts_event_services` | ✅ | ✅ | ✅ | ✅ |
| `rcts_schedule` | ✅ | ✅ | ✅ | ✅ |

### 🔧 Technische Details

- **UI**: Schedule-Tabelle wird als "Terminkalender (Schedule)" mit `admin-page` Icon angezeigt
- **Backend**: AJAX-Handler `ajax_clear_tables()` und `ajax_clear_single_table()` komplett aktualisiert
- **Cleanup**: `uninstall.php` berücksichtigt alle neuen Tabellen
- **Sicherheit**: Nur existierende Tabellen werden verarbeitet

### 📦 Installation

Dieses Update kann automatisch über den GitHub-Updater installiert werden oder manuell:

1. Plugin deaktivieren
2. Alte Version entfernen
3. Neue Version hochladen
4. Plugin aktivieren

Keine Datenbank-Migration erforderlich - alle Daten bleiben erhalten.

---

**Vorheriges Release**: [v0.4.0.0 - Unified Sync System](https://github.com/FEGAschaffenburg/repro-ct-suite/releases/tag/v0.4.0.0)