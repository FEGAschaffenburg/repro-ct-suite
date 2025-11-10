<!DOCTYPE html>
<html>
<head>
    <title>RCTS Migration V10</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 8px; max-width: 800px; margin: 0 auto; }
        h1 { color: #333; }
        .success { color: #28a745; padding: 10px; background: #d4edda; border-radius: 4px; margin: 10px 0; }
        .error { color: #dc3545; padding: 10px; background: #f8d7da; border-radius: 4px; margin: 10px 0; }
        .info { color: #0c5460; padding: 10px; background: #d1ecf1; border-radius: 4px; margin: 10px 0; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 4px; overflow-x: auto; }
        button { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        button:hover { background: #0056b3; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 RCTS Migration V10 - Shortcode Manager</h1>
        
        <?php
        // WordPress laden
        require_once('../../../wp-load.php');
        
        if (!current_user_can('manage_options')) {
            echo '<div class="error">⛔ Keine Berechtigung! Nur Administratoren können diese Migration ausführen.</div>';
            exit;
        }
        
        if (isset($_POST['run_migration'])) {
            global $wpdb;
            $presets_table = $wpdb->prefix . 'rcts_shortcode_presets';
            
            echo '<h2>Migration wird ausgeführt...</h2>';
            
            // Prüfe ob Tabelle existiert
            $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$presets_table}'");
            if (!$table_exists) {
                echo '<div class="error">❌ Tabelle ' . $presets_table . ' existiert nicht!</div>';
                exit;
            }
            
            echo '<div class="info">✓ Tabelle gefunden: ' . $presets_table . '</div>';
            
            // Spalten hinzufügen
            $columns_to_add = array(
                'shortcode_tag' => "VARCHAR(100) NULL",
                'display_mode' => "VARCHAR(50) NULL",
                'days_ahead' => "INT(11) NULL",
                'show_time' => "TINYINT(1) DEFAULT 1",
                'show_location' => "TINYINT(1) DEFAULT 1",
                'show_description' => "TINYINT(1) DEFAULT 1",
                'show_organizer' => "TINYINT(1) DEFAULT 0"
            );
            
            foreach ($columns_to_add as $column => $definition) {
                $column_exists = $wpdb->get_results(
                    $wpdb->prepare("SHOW COLUMNS FROM {$presets_table} LIKE %s", $column)
                );
                
                if (empty($column_exists)) {
                    $result = $wpdb->query("ALTER TABLE {$presets_table} ADD COLUMN {$column} {$definition}");
                    if ($result !== false) {
                        echo '<div class="success">✓ Spalte hinzugefügt: ' . $column . '</div>';
                    } else {
                        echo '<div class="error">❌ Fehler beim Hinzufügen von: ' . $column . '</div>';
                    }
                } else {
                    echo '<div class="info">ℹ️ Spalte existiert bereits: ' . $column . '</div>';
                }
            }
            
            // Migriere view -> display_mode
            $updated = $wpdb->query("UPDATE {$presets_table} SET display_mode = view WHERE display_mode IS NULL AND view IS NOT NULL");
            echo '<div class="success">✓ ' . $updated . ' Datensätze migriert (view → display_mode)</div>';
            
            // Generiere shortcode_tags
            $presets = $wpdb->get_results("SELECT id, name FROM {$presets_table} WHERE shortcode_tag IS NULL OR shortcode_tag = ''");
            $generated = 0;
            foreach ($presets as $preset) {
                $shortcode_tag = 'ct_' . sanitize_title($preset->name);
                $wpdb->update(
                    $presets_table,
                    array('shortcode_tag' => $shortcode_tag),
                    array('id' => $preset->id)
                );
                $generated++;
            }
            echo '<div class="success">✓ ' . $generated . ' Shortcode-Tags generiert</div>';
            
            // Zeige Ergebnis
            echo '<h2>Migration abgeschlossen!</h2>';
            echo '<div class="success">✅ Alle Schritte erfolgreich ausgeführt</div>';
            
            // Zeige aktuelle Daten
            $all_presets = $wpdb->get_results("SELECT id, name, shortcode_tag, display_mode, days_ahead FROM {$presets_table}");
            if (!empty($all_presets)) {
                echo '<h3>Aktuelle Shortcodes:</h3>';
                echo '<pre>';
                foreach ($all_presets as $p) {
                    echo sprintf("ID: %d | Name: %s | Tag: [%s] | Mode: %s | Days: %s\n", 
                        $p->id, 
                        $p->name, 
                        $p->shortcode_tag, 
                        $p->display_mode ?: 'nicht gesetzt',
                        $p->days_ahead ?: 'nicht gesetzt'
                    );
                }
                echo '</pre>';
            }
            
            echo '<div class="info">💡 Du kannst diese Datei jetzt löschen: ' . basename(__FILE__) . '</div>';
        } else {
            ?>
            <p>Diese Migration fügt neue Felder zur Shortcode-Presets-Tabelle hinzu:</p>
            <ul>
                <li><strong>shortcode_tag</strong> - Eindeutiger Shortcode-Identifier</li>
                <li><strong>display_mode</strong> - Anzeigemodus (compact, list, cards, etc.)</li>
                <li><strong>days_ahead</strong> - Tage in die Zukunft</li>
                <li><strong>show_time</strong> - Zeit anzeigen</li>
                <li><strong>show_location</strong> - Ort anzeigen</li>
                <li><strong>show_description</strong> - Beschreibung anzeigen</li>
                <li><strong>show_organizer</strong> - Veranstalter anzeigen</li>
            </ul>
            
            <form method="post">
                <button type="submit" name="run_migration">🚀 Migration jetzt ausführen</button>
            </form>
            <?php
        }
        ?>
    </div>
</body>
</html>
