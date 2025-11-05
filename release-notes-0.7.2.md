**BUGFIX: SQL-Fehler im Shortcode** 🐛

## Problem (v0.7.0 - v0.7.1)

Der Shortcode `[rcts_events]` warf einen SQL-Fehler:

```
WordPress-Datenbank-Fehler: [Column 'calendar_id' in WHERE is ambiguous]
SELECT e.*, c.name as calendar_name, c.color as calendar_color 
FROM wp_fegrcts_events e 
LEFT JOIN wp_fegrcts_calendars c ON e.calendar_id = c.id 
WHERE start_datetime >= '...' 
  AND start_datetime <= '...' 
  AND calendar_id IN (1,2)  ← FEHLER: Welche Tabelle?
```

**Ursache:** WHERE-Bedingungen verwendeten keine Tabellen-Präfixe.

## Fix (v0.7.2)

✅ Alle WHERE-Bedingungen verwenden jetzt `e.` Präfix:

```php
// Vorher
$where[] = "calendar_id IN ($placeholders)";
$where[] = "start_datetime >= %s";

// Nachher  
$where[] = "e.calendar_id IN ($placeholders)";
$where[] = "e.start_datetime >= %s";
```

## Änderungen

**Datei:** `includes/class-repro-ct-suite-shortcodes.php`
- **Zeile 106-107:** `e.start_datetime` statt `start_datetime`
- **Zeile 114:** `e.calendar_id` statt `calendar_id`
- **Zeile 120:** `e.start_datetime` statt `start_datetime`

## Was jetzt funktioniert

✅ Shortcode läuft ohne SQL-Fehler  
✅ Kalender-Filter: `[rcts_events calendar_ids="1,2"]`  
✅ Zeitfilter: `[rcts_events from_days="0" to_days="30"]`  
✅ Vergangenheits-Filter: `[rcts_events show_past="false"]`  

## Enthält auch

Alle Features von v0.7.0 + v0.7.1:
- Frontend Shortcode System
- Automatischer Sync funktional
- 3 Template-Ansichten
