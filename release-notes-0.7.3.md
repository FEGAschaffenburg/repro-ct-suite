**BUGFIX: Template Property Names** 🐛

## Problem (v0.7.0 - v0.7.2)

Alle drei Templates warfen PHP Warnings:

```
Warning: Undefined property: stdClass::$name in templates/events/list-grouped.php on line 61
Warning: Undefined property: stdClass::$location in templates/events/cards.php on line 70
```

**Ursache:** Templates verwendeten falsche Property-Namen (`$event->name`, `$event->location`), aber die Datenbank-Felder heißen `title` und `location_name`.

## Fix (v0.7.3)

✅ Alle Templates verwenden jetzt die korrekten DB-Feldnamen:

| Template | Vorher | Nachher |
|----------|--------|---------|
| **Title** | `$event->name` | `$event->title` ✅ |
| **Location** | `$event->location` | `$event->location_name` ✅ |

## Geänderte Dateien

**1. templates/events/list-simple.php**
- **Zeile 38:** `$event->name` → `$event->title`
- **Zeile 60:** `$event->location` → `$event->location_name`

**2. templates/events/list-grouped.php**
- **Zeile 61:** `$event->name` → `$event->title`
- **Zeile 69:** `$event->location` → `$event->location_name`

**3. templates/events/cards.php**
- **Zeile 51:** `$event->name` → `$event->title`
- **Zeile 70:** `$event->location` → `$event->location_name`

## Was jetzt funktioniert

✅ Keine PHP Warnings mehr im Frontend  
✅ Event-Titel werden korrekt angezeigt  
✅ Location-Namen werden korrekt angezeigt  
✅ Alle Shortcode-Views funktional: list, list-grouped, cards  

## Datenbank-Schema

Zur Referenz - die korrekten Event-Properties:

```php
$event->id              // int
$event->event_id        // string (externe CT-ID)
$event->calendar_id     // string (ChurchTools Kalender-ID)
$event->appointment_id  // string (optional)
$event->title           // string ← WICHTIG!
$event->description     // text
$event->location_name   // string ← WICHTIG!
$event->start_datetime  // datetime
$event->end_datetime    // datetime (optional)
$event->calendar_name   // string (JOIN)
$event->calendar_color  // string (JOIN)
```

## Enthält auch

Alle Features + Fixes von v0.7.0 - v0.7.2:
- Frontend Shortcode System
- Automatischer Sync funktional
- SQL ambiguous column fix
