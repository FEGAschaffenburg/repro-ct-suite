# Release 0.4.0.3 - CRITICAL FIX: Events Filter Logic

## 🚨 KRITISCHER BUG-FIX: Events-Import funktioniert wieder

Diese Version behebt ein kritisches Problem, bei dem **alle Events übersprungen** wurden und keine Synchronisation stattfand.

### 🐛 Behobenes Problem

**Symptom**: 
- Events werden gefunden, aber alle übersprungen mit "Event nicht relevant für Kalender"
- Logs zeigen: `"Event 2008 nicht relevant für Kalender 1"`
- Keine Events werden importiert trotz korrekter API-Antworten

**Ursache**: 
Die Events-Filterlogik prüfte falsche/unvollständige Kalender-Strukturen der ChurchTools API.

### ✅ Lösung

**Erweiterte 4-stufige Kalender-Zuordnung**:

1. **`calendar.id`** - Standard Event-Kalender  
2. **`calendarId`** - Direkte Kalender-ID Property
3. **`calendars[]`** - Array mehrerer Kalender
4. **`appointment.calendar.id`** - Kalender über Appointment-Verknüpfung

### 🔍 Debug-Verbesserungen

Das neue Debug-Logging zeigt detailliert:

```
Event 2008 Struktur-Check für Kalender 1
Event calendar.id: 1, Ziel: 1, Match: YES
```

Statt der bisherigen stillen Übersprungung.

### 📋 API-Kompatibilität

Unterstützt verschiedene ChurchTools Event-API Formate:

```json
// Format 1: Direkte calendar property
{"id": 2008, "calendar": {"id": 1}}

// Format 2: calendarId property  
{"id": 2008, "calendarId": 1}

// Format 3: calendars Array
{"id": 2008, "calendars": [{"id": 1}, {"id": 2}]}

// Format 4: Via Appointment
{"id": 2008, "appointment": {"calendar": {"id": 1}}}
```

### 🎯 Sofortige Wirkung

Nach dem Update auf v0.4.0.3:

- ✅ Events werden korrekt importiert
- ✅ Kalender-Filter funktioniert wieder  
- ✅ 2-Phasen-Sync läuft vollständig durch
- ✅ Debug-Logs zeigen detaillierte Zuordnungsschritte

### ⚠️ Wichtiger Hinweis

**Dieses Update ist KRITISCH** - ohne diese Korrektur funktioniert der Events-Import nicht. Alle Benutzer sollten sofort auf v0.4.0.3 aktualisieren.

### 🔄 Nach dem Update

1. **Sofortiger Sync**: Events werden automatisch importiert
2. **Debug-Logs prüfen**: Bei Problemen zeigen Logs jetzt detaillierte Struktur-Informationen
3. **Kalender-Zuordnung**: Funktioniert wieder mit allen Event-Formaten

---

**Betroffen**: Alle v0.4.0.x Versionen mit Unified Sync System  
**Priorität**: KRITISCH - Sofort installieren