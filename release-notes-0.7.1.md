**BUGFIX: Automatischer Sync** 🐛

## Problem (v0.7.0)

Der automatische Cron-Job schlug mit folgendem Fehler fehl:

```
[2025-11-05 12:38:26] ℹ️ INFO: 🔄 Automatischer Sync gestartet
[2025-11-05 12:38:26] ℹ️ INFO: Starte Synchronisation...
[2025-11-05 12:38:26] ❌ ERROR: Sync fehlgeschlagen: Keine Kalender für den Import ausgewählt.
```

**Ursache:** Der Cron-Job rief `sync_events()` ohne Parameter auf, die Funktion erwartet aber `calendar_ids` im Array.

## Fix (v0.7.1)

✅ Cron-Job lädt jetzt automatisch die ausgewählten Kalender aus der Datenbank:

```php
// Ausgewählte Kalender-IDs aus der Datenbank laden
$selected_calendars = $wpdb->get_results( 
    "SELECT calendar_id FROM {$calendars_table} WHERE is_selected = 1", 
    ARRAY_A 
);
$calendar_ids = array_column( $selected_calendars, 'calendar_id' );

$result = $sync_service->sync_events( array(
    'calendar_ids' => $calendar_ids,
    'from'         => gmdate( 'Y-m-d', current_time('timestamp') - 7 * DAY_IN_SECONDS ),
    'to'           => gmdate( 'Y-m-d', current_time('timestamp') + 90 * DAY_IN_SECONDS ),
) );
```

## Änderungen

**Datei:** `includes/class-repro-ct-suite-cron.php`
- **Zeilen 229-246:** Neue Logik zum Laden der Kalender-IDs
- **Sync-Zeitraum:** 7 Tage Vergangenheit bis 90 Tage Zukunft
- **Logging:** Zeigt jetzt die verwendeten Kalender-IDs

## Was jetzt funktioniert

✅ Automatischer Sync läuft ohne Fehler  
✅ Kalender werden aus `wp_rcts_calendars` geladen (WHERE `is_selected = 1`)  
✅ Zeitraum-Parameter werden korrekt übergeben  
✅ Log zeigt: "Starte Synchronisation mit Kalendern: 1, 2, 3"  

## Enthält auch

Alle Features von v0.7.0:
- Frontend Shortcode System
- 3 Template-Ansichten
- Responsive Design
