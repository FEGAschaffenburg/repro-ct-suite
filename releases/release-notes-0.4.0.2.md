# Release 0.4.0.2 - Debug Table Diagnostic Tool

## 🔍 Debug Release: Diagnose-Tool für Tabellen-Duplikate

Diese Version fügt Debug-Funktionen hinzu, um das Problem mit doppelten Event-Services Tabellen zu diagnostizieren.

### 🔧 Neue Debug-Features

- **Automatische Tabellen-Erkennung**: Erkennt alle Plugin-Tabellen mit `rcts_` Pattern
- **Duplikate-Warnung**: Zeigt Warnung bei ungewöhnlich vielen Plugin-Tabellen (>5)
- **WordPress-Prefix-Anzeige**: Zeigt das aktuelle `$wpdb->prefix` zur Diagnose
- **Vollständige Tabellenliste**: Listet alle gefundenen Plugin-Tabellen auf

### 🐛 Problem-Diagnose

Falls Sie `wp_fegrcts_event_services` doppelt sehen, könnte dies folgende Ursachen haben:

1. **Ungewöhnlicher WordPress-Prefix**: `feg` oder `fegrcts` als Prefix
2. **Mehrere Installationen**: Alte Plugin-Reste mit verschiedenen Prefixen
3. **Migration-Probleme**: Daten aus älteren Plugin-Versionen

### 🎯 Debug-Seite Verbesserungen

Die Debug-Seite zeigt nun automatisch eine **gelbe Warnung**, wenn mehr als die erwarteten 5 Plugin-Tabellen gefunden werden:

```
Debug-Warnung: Zusätzliche Tabellen gefunden

Es wurden mehr Plugin-Tabellen gefunden als erwartet:
- wp_rcts_calendars
- wp_rcts_events  
- wp_rcts_appointments
- wp_rcts_event_services
- wp_rcts_schedule
- wp_fegrcts_event_services    ← Mögliche Duplikat-Quelle
- wp_old_rcts_events           ← Alte Installation

WordPress-Präfix: wp_
Dies könnte die Ursache für doppelte Anzeigen sein...
```

### 📋 Nächste Schritte

1. **Installation**: Update auf v0.4.0.2
2. **Debug-Seite besuchen**: Prüfen Sie, ob die Warnung erscheint
3. **Tabellen analysieren**: Schauen Sie sich die vollständige Liste an
4. **Aufräumen**: Alte/ungewöhnliche Tabellen ggf. manuell entfernen

### 🛠️ Bereinigung (falls erforderlich)

Wenn Sie alte Duplikat-Tabellen finden:

```sql
-- Beispiel: Alte Tabelle entfernen (VORSICHT!)
DROP TABLE IF EXISTS wp_fegrcts_event_services;
```

**⚠️ Wichtig**: Erstellen Sie vor manuellen Datenbankänderungen ein Backup!

---

**Problem melden**: Falls das Debug-Tool zusätzliche Informationen liefert, erstellen Sie bitte ein Issue mit den Details.