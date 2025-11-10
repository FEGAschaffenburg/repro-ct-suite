**FEATURE: WordPress Zeit-Format mit "Uhr" / AM/PM** 🕐

## Neue Funktionalität (v0.7.4)

Die Templates verwenden jetzt das WordPress Zeit-Format aus **Settings → General → Time Format**:

### 24-Stunden-Format
```
WordPress Einstellung: H:i (z.B. 14:30)
Anzeige im Template: 14:30 Uhr ✅
```

### 12-Stunden-Format
```
WordPress Einstellung: g:i a (z.B. 2:30 pm)
Anzeige im Template: 2:30 PM ✅
```

## Technische Umsetzung

**1. Shortcode-Handler** (`includes/class-repro-ct-suite-shortcodes.php`)

```php
// Zeit-Formatierung mit WordPress-Einstellungen
$time_format = get_option( 'time_format' );
$event->time_formatted = wp_date( $time_format, strtotime( $event->start_datetime ) );
$event->end_time_formatted = $event->end_datetime ? wp_date( $time_format, strtotime( $event->end_datetime ) ) : '';

// "Uhr" bei 24h-Format hinzufügen
$is_24h_format = ( strpos( $time_format, 'a' ) === false && strpos( $time_format, 'A' ) === false );
if ( $is_24h_format ) {
    $event->time_formatted .= ' Uhr';
    if ( $event->end_time_formatted ) {
        $event->end_time_formatted .= ' Uhr';
    }
}
```

**Logik:**
- Wenn Zeit-Format **kein** 'a' oder 'A' enthält → 24h-Format → "Uhr" anhängen
- Wenn Zeit-Format 'a' oder 'A' enthält → 12h-Format → AM/PM ist bereits im Format

**2. Template-Änderungen**

Alle drei Templates verwenden jetzt:

```php
// Vorher (hartkodiert)
<?php echo esc_html( $event->start_time ); ?>  // Immer H:i

// Nachher (WordPress-Format)
<?php echo esc_html( $event->time_formatted ); ?>  // Respektiert Einstellung
```

**Geänderte Dateien:**
- `templates/events/list-simple.php` (Zeile 51, 53)
- `templates/events/list-grouped.php` (Zeile 52, 54)
- `templates/events/cards.php` (Zeile 57, 59)

## Beispiele

### Deutsch (24h)
```
Zeit-Format: H:i
Ausgabe: "14:30 Uhr - 16:00 Uhr"
```

### Englisch (12h)
```
Zeit-Format: g:i a
Ausgabe: "2:30 PM - 4:00 PM"
```

### Custom Format
```
Zeit-Format: H:i:s
Ausgabe: "14:30:00 Uhr"

Zeit-Format: h:i A
Ausgabe: "02:30 PM"
```

## Vorteile

✅ **Mehrsprachig:** Respektiert WordPress Spracheinstellungen  
✅ **Benutzerfreundlich:** Automatische "Uhr" oder AM/PM Ergänzung  
✅ **Flexibel:** Funktioniert mit jedem Zeit-Format  
✅ **Konsistent:** Gleiche Formatierung in allen Templates  

## Enthält auch

Alle Features + Fixes von v0.7.0 - v0.7.3:
- Frontend Shortcode System
- Automatischer Sync funktional
- SQL & Template Fixes
