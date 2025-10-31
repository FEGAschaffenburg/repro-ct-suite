# Debug-Seite DB Version 6 Update

## ✅ Probleme behoben

### 1. Tabellen-Anzeige korrigiert
Die Debug-Seite zeigt jetzt alle korrekten Tabellen für DB Version 6:

- ✅ `rcts_calendars` - Kalender
- ✅ `rcts_events` - Events  
- ✅ `rcts_appointments` - Termine (Appointments)
- ✅ `rcts_event_services` - Event-Services
- ✅ `rcts_schedule` - **NEU**: Terminkalender (Schedule)

### 2. AJAX-Handler aktualisiert
Alle Lösch-Funktionen unterstützen die neuen Tabellen:

#### `ajax_clear_tables()` - Alle Tabellen leeren
```php
$tables = array(
    $wpdb->prefix . 'rcts_event_services',
    $wpdb->prefix . 'rcts_appointments', 
    $wpdb->prefix . 'rcts_events',
    $wpdb->prefix . 'rcts_schedule',        // ← NEU
    $wpdb->prefix . 'rcts_calendars',
);
```

#### `ajax_clear_single_table()` - Einzelne Tabelle leeren
```php
$table_mapping = array(
    'rcts_calendars'       => $wpdb->prefix . 'rcts_calendars',
    'rcts_events'          => $wpdb->prefix . 'rcts_events', 
    'rcts_appointments'    => $wpdb->prefix . 'rcts_appointments',
    'rcts_event_services'  => $wpdb->prefix . 'rcts_event_services',
    'rcts_schedule'        => $wpdb->prefix . 'rcts_schedule',  // ← NEU
);
```

### 3. Uninstall-Funktion aktualisiert
Die `uninstall.php` berücksichtigt auch die neue Tabelle:

```php
$tables = array(
    $wpdb->prefix . 'rcts_calendars',
    $wpdb->prefix . 'rcts_events',
    $wpdb->prefix . 'rcts_appointments', 
    $wpdb->prefix . 'rcts_event_services',
    $wpdb->prefix . 'rcts_schedule',        // ← NEU
);
```

## 🎯 Was funktioniert jetzt

1. **Debug-Seite**: Zeigt alle 5 Tabellen mit korrekten Zählerständen
2. **Einzeltabelle leeren**: Jede Tabelle kann einzeln geleert werden
3. **Alle Tabellen leeren**: Kompletter Reset aller Daten
4. **Sicherheit**: Nur existierende Tabellen werden verarbeitet
5. **Konsistenz**: Alle Komponenten verwenden dieselbe Tabellenliste

## 📋 UI-Verbesserungen

- **Neue Tabelle**: `rcts_schedule` wird als "Terminkalender (Schedule)" angezeigt
- **Icon**: Verwendet `admin-page` Icon für Schedule-Tabelle
- **Zählerstand**: Zeigt korrekte Anzahl der Einträge
- **Lösch-Button**: Funktioniert für alle Tabellen inkl. Schedule

Das Debug-System ist jetzt vollständig mit DB Version 6 kompatibel! 🚀