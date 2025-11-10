-- Migration V10: Shortcode Manager Erweiterungen
-- Fügt neue Felder zur wp_rcts_shortcode_presets Tabelle hinzu
-- WICHTIG: wp_ durch dein Präfix ersetzen!

-- 1. Neue Spalten hinzufügen
ALTER TABLE wp_rcts_shortcode_presets ADD COLUMN IF NOT EXISTS shortcode_tag VARCHAR(100) NULL AFTER name;
ALTER TABLE wp_rcts_shortcode_presets ADD COLUMN IF NOT EXISTS display_mode VARCHAR(50) NULL AFTER view;
ALTER TABLE wp_rcts_shortcode_presets ADD COLUMN IF NOT EXISTS days_ahead INT(11) NULL AFTER to_days;
ALTER TABLE wp_rcts_shortcode_presets ADD COLUMN IF NOT EXISTS show_time TINYINT(1) DEFAULT 1 AFTER show_past;
ALTER TABLE wp_rcts_shortcode_presets ADD COLUMN IF NOT EXISTS show_location TINYINT(1) DEFAULT 1 AFTER show_time;
ALTER TABLE wp_rcts_shortcode_presets ADD COLUMN IF NOT EXISTS show_description TINYINT(1) DEFAULT 1 AFTER show_location;
ALTER TABLE wp_rcts_shortcode_presets ADD COLUMN IF NOT EXISTS show_organizer TINYINT(1) DEFAULT 0 AFTER show_description;

-- 2. Migriere bestehende Daten: view -> display_mode
UPDATE wp_rcts_shortcode_presets SET display_mode = view WHERE display_mode IS NULL AND view IS NOT NULL;

-- 3. Generiere shortcode_tags für bestehende Presets
-- Diese Abfrage müsste für jedes Preset einzeln ausgeführt werden
-- Beispiel: UPDATE wp_rcts_shortcode_presets SET shortcode_tag = 'ct_timeline' WHERE id = 1 AND name = 'Timeline';
