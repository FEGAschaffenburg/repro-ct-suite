<?php
/**
 * Modern Shortcode Manager AJAX Handler - Debug Enhanced Version
 * 
 * Saubere, moderne AJAX-Implementation für den Shortcode Manager
 * Version: 2024-11-07 16:45 - Mit umfassendem Debug-Logging
 * 
 * @package Repro_CT_Suite
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class Repro_CT_Suite_Modern_Shortcode_Manager {
    
    /**
     * Constructor - Register AJAX hooks
     */
    public function __construct() {
        // Logger laden
        if (!class_exists('Repro_CT_Suite_Logger')) {
            require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-repro-ct-suite-logger.php';
        }
        
        Repro_CT_Suite_Logger::log('Modern Shortcode Manager: Konstruktor aufgerufen', 'info');
        
        add_action('wp_ajax_sm_get_presets', array($this, 'get_presets'));
        add_action('wp_ajax_sm_get_preset', array($this, 'get_preset'));
        add_action('wp_ajax_sm_save_preset', array($this, 'save_preset'));
        add_action('wp_ajax_sm_update_preset', array($this, 'update_preset'));
        add_action('wp_ajax_sm_delete_preset', array($this, 'delete_preset'));
        add_action('wp_ajax_sm_get_calendars', array($this, 'get_calendars'));
        add_action('wp_ajax_sm_preview_shortcode', array($this, 'preview_shortcode'));
        
        Repro_CT_Suite_Logger::log('Modern Shortcode Manager: ' . count(debug_backtrace()) . ' AJAX Hooks registriert', 'success');
    }
    
    /**
     * Security check helper
     */
    private function verify_request() {
        Repro_CT_Suite_Logger::log('Security Check: Nonce = ' . ($_POST['nonce'] ?? 'nicht vorhanden'), 'info');
        
        if (!wp_verify_nonce($_POST['nonce'], 'sm_ajax_nonce')) {
            Repro_CT_Suite_Logger::log('Security Check: Nonce-Prüfung fehlgeschlagen', 'error');
            wp_send_json_error(array('message' => 'Sicherheitsprüfung fehlgeschlagen'));
            return false;
        }
        
        if (!current_user_can('manage_options')) {
            Repro_CT_Suite_Logger::log('Security Check: Benutzer hat keine Berechtigung', 'error');
            wp_send_json_error(array('message' => 'Keine Berechtigung'));
            return false;
        }
        
        Repro_CT_Suite_Logger::log('Security Check: Erfolgreich bestanden', 'success');
        return true;
    }
    
    /**
     * Get all shortcode presets
     */
    public function get_presets() {
        Repro_CT_Suite_Logger::log('AJAX get_presets: Request empfangen', 'info');
        
        if (!$this->verify_request()) {
            Repro_CT_Suite_Logger::log('AJAX get_presets: Sicherheitsprüfung fehlgeschlagen', 'error');
            return;
        }
        
        try {
            require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-repro-ct-suite-shortcode-presets-repository.php';
            $repo = new Repro_CT_Suite_Shortcode_Presets_Repository();
            $presets = $repo->get_all();
            
            Repro_CT_Suite_Logger::log('AJAX get_presets: ' . count($presets) . ' Presets gefunden', 'success');
            
            wp_send_json_success(array(
                'presets' => $presets,
                'count' => count($presets)
            ));
            
        } catch (Exception $e) {
            Repro_CT_Suite_Logger::log('AJAX get_presets: Exception - ' . $e->getMessage(), 'error');
            wp_send_json_error(array(
                'message' => 'Fehler beim Laden der Presets: ' . $e->getMessage()
            ));
        }
    }

    /**
     * Get single preset by ID
     */
    public function get_preset() {
        Repro_CT_Suite_Logger::log('AJAX get_preset: Request empfangen', 'info');
        Repro_CT_Suite_Logger::log('AJAX get_preset: POST Data: ' . print_r($_POST, true), 'info');
        
        if (!$this->verify_request()) {
            Repro_CT_Suite_Logger::log('AJAX get_preset: Sicherheitsprüfung fehlgeschlagen', 'error');
            return;
        }
        
        try {
            $preset_id = intval($_POST['preset_id'] ?? 0);
            Repro_CT_Suite_Logger::log('AJAX get_preset: Preset ID: ' . $preset_id, 'info');
            
            if ($preset_id <= 0) {
                Repro_CT_Suite_Logger::log('AJAX get_preset: Ungültige Preset-ID', 'error');
                wp_send_json_error(array('message' => 'Ungültige Preset-ID'));
                return;
            }
            
            require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-repro-ct-suite-shortcode-presets-repository.php';
            $repo = new Repro_CT_Suite_Shortcode_Presets_Repository();
            $preset = $repo->get_by_id($preset_id);
            
            if ($preset) {
                Repro_CT_Suite_Logger::log('AJAX get_preset: Preset gefunden - ' . $preset->name, 'success');
                wp_send_json_success($preset);
            } else {
                Repro_CT_Suite_Logger::log('AJAX get_preset: Preset nicht gefunden', 'error');
                wp_send_json_error(array('message' => 'Preset nicht gefunden'));
            }
            
        } catch (Exception $e) {
            Repro_CT_Suite_Logger::log('AJAX get_preset: Exception - ' . $e->getMessage(), 'error');
            wp_send_json_error(array(
                'message' => 'Fehler beim Laden des Presets: ' . $e->getMessage()
            ));
        }
    }
    
    /**
     * Get available calendars
     */
    public function get_calendars() {
        Repro_CT_Suite_Logger::log('AJAX get_calendars: Request empfangen', 'info');
        Repro_CT_Suite_Logger::log('AJAX get_calendars: POST Data: ' . print_r($_POST, true), 'info');
        
        if (!$this->verify_request()) {
            Repro_CT_Suite_Logger::log('AJAX get_calendars: Sicherheitsprüfung fehlgeschlagen', 'error');
            return;
        }
        
        try {
            // Direkt auf alte rcts_calendars Tabelle zugreifen
            global $wpdb;
            $table_name = $wpdb->prefix . 'rcts_calendars';
            
            Repro_CT_Suite_Logger::log('AJAX get_calendars: Datenbankabfrage auf Tabelle: ' . $table_name, 'info');
            
            $calendars = $wpdb->get_results("
                SELECT calendar_id as id, name, color, is_selected
                FROM {$table_name} 
                WHERE is_selected = 1 
                ORDER BY name ASC
            ", ARRAY_A);
            
            Repro_CT_Suite_Logger::log('AJAX get_calendars: ' . count($calendars ? $calendars : array()) . ' Kalender gefunden', 'success');
            
            wp_send_json_success(array(
                'calendars' => $calendars ? $calendars : array(),
                'count' => count($calendars ? $calendars : array())
            ));
            
        } catch (Exception $e) {
            Repro_CT_Suite_Logger::log('AJAX get_calendars: Exception - ' . $e->getMessage(), 'error');
            wp_send_json_error(array(
                'message' => 'Fehler beim Laden der Kalender: ' . $e->getMessage()
            ));
        }
    }
    
    /**
     * Save new preset
     */
    public function save_preset() {
        Repro_CT_Suite_Logger::log('AJAX save_preset: Request empfangen', 'info');
        Repro_CT_Suite_Logger::log('AJAX save_preset: POST Data: ' . print_r($_POST, true), 'info');
        
        if (!$this->verify_request()) {
            Repro_CT_Suite_Logger::log('AJAX save_preset: Sicherheitsprüfung fehlgeschlagen', 'error');
            return;
        }
        
        try {
            $name = sanitize_text_field($_POST['name'] ?? '');
            $calendar_ids = $_POST['calendar_ids'] ?? array();
            $limit = intval($_POST['limit'] ?? 10);
            $show_time = isset($_POST['show_time']) ? 1 : 0;
            $show_location = isset($_POST['show_location']) ? 1 : 0;
            $show_description = isset($_POST['show_description']) ? 1 : 0;
            
            Repro_CT_Suite_Logger::log('AJAX save_preset: Daten verarbeitet - Name: ' . $name . ', Kalender: ' . count($calendar_ids), 'info');
            
            if (empty($name)) {
                Repro_CT_Suite_Logger::log('AJAX save_preset: Name fehlt', 'error');
                wp_send_json_error(array('message' => 'Name ist erforderlich'));
                return;
            }
            
            // Validate calendar IDs
            if (!is_array($calendar_ids)) {
                $calendar_ids = array();
            }
            $calendar_ids = array_map('intval', $calendar_ids);
            $calendar_ids = array_filter($calendar_ids, function($id) { return $id > 0; });
            
            require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-repro-ct-suite-shortcode-presets-repository.php';
            $repo = new Repro_CT_Suite_Shortcode_Presets_Repository();
            
            $preset_data = array(
                'name' => $name,
                'limit_count' => $limit,
                'calendar_ids' => $calendar_ids,
                'show_time' => $show_time,
                'show_location' => $show_location,
                'show_description' => $show_description
            );
            
            $preset_id = $repo->save($preset_data);
            
            if ($preset_id) {
                wp_send_json_success(array(
                    'message' => 'Preset erfolgreich gespeichert',
                    'preset_id' => $preset_id
                ));
            } else {
                wp_send_json_error(array('message' => 'Fehler beim Speichern'));
            }
            
        } catch (Exception $e) {
            wp_send_json_error(array(
                'message' => 'Fehler: ' . $e->getMessage()
            ));
        }
    }
    
    /**
     * Update existing preset
     */
    public function update_preset() {
        Repro_CT_Suite_Logger::log('AJAX update_preset: Request empfangen', 'info');
        Repro_CT_Suite_Logger::log('AJAX update_preset: POST Data: ' . print_r($_POST, true), 'info');
        
        if (!$this->verify_request()) {
            Repro_CT_Suite_Logger::log('AJAX update_preset: Sicherheitsprüfung fehlgeschlagen', 'error');
            return;
        }
        
        try {
            $preset_id = intval($_POST['preset_id'] ?? 0);
            Repro_CT_Suite_Logger::log('AJAX update_preset: Preset ID: ' . $preset_id, 'info');
            
            if ($preset_id <= 0) {
                Repro_CT_Suite_Logger::log('AJAX update_preset: Ungültige Preset-ID', 'error');
                wp_send_json_error(array('message' => 'Ungültige Preset-ID'));
                return;
            }
            
            $name = sanitize_text_field($_POST['name'] ?? '');
            $calendar_ids = $_POST['calendar_ids'] ?? array();
            $limit = intval($_POST['limit'] ?? 10);
            $show_time = isset($_POST['show_time']) ? 1 : 0;
            $show_location = isset($_POST['show_location']) ? 1 : 0;
            $show_description = isset($_POST['show_description']) ? 1 : 0;
            
            Repro_CT_Suite_Logger::log('AJAX update_preset: Daten verarbeitet - Name: ' . $name . ', Kalender: ' . count($calendar_ids), 'info');
            
            if (empty($name)) {
                Repro_CT_Suite_Logger::log('AJAX update_preset: Name fehlt', 'error');
                wp_send_json_error(array('message' => 'Name ist erforderlich'));
                return;
            }
            
            if (!is_array($calendar_ids)) {
                $calendar_ids = array();
            }
            $calendar_ids = array_map('intval', $calendar_ids);
            $calendar_ids = array_filter($calendar_ids, function($id) { return $id > 0; });
            
            require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-repro-ct-suite-shortcode-presets-repository.php';
            $repo = new Repro_CT_Suite_Shortcode_Presets_Repository();
            
            $preset_data = array(
                'name' => $name,
                'limit_count' => $limit,
                'calendar_ids' => $calendar_ids,
                'show_time' => $show_time,
                'show_location' => $show_location,
                'show_description' => $show_description
            );
            
            $success = $repo->update($preset_id, $preset_data);
            
            if ($success) {
                wp_send_json_success(array('message' => 'Preset erfolgreich aktualisiert'));
            } else {
                wp_send_json_error(array('message' => 'Fehler beim Aktualisieren'));
            }
            
        } catch (Exception $e) {
            wp_send_json_error(array(
                'message' => 'Fehler: ' . $e->getMessage()
            ));
        }
    }
    
    /**
     * Delete preset
     */
    public function delete_preset() {
        Repro_CT_Suite_Logger::log('AJAX delete_preset: Request empfangen', 'info');
        Repro_CT_Suite_Logger::log('AJAX delete_preset: POST Data: ' . print_r($_POST, true), 'info');
        
        if (!$this->verify_request()) {
            Repro_CT_Suite_Logger::log('AJAX delete_preset: Sicherheitsprüfung fehlgeschlagen', 'error');
            return;
        }
        
        try {
            $preset_id = intval($_POST['preset_id'] ?? 0);
            Repro_CT_Suite_Logger::log('AJAX delete_preset: Preset ID: ' . $preset_id, 'info');
            
            if ($preset_id <= 0) {
                Repro_CT_Suite_Logger::log('AJAX delete_preset: Ungültige Preset-ID', 'error');
                wp_send_json_error(array('message' => 'Ungültige Preset-ID'));
                return;
            }
            
            require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-repro-ct-suite-shortcode-presets-repository.php';
            $repo = new Repro_CT_Suite_Shortcode_Presets_Repository();
            
            Repro_CT_Suite_Logger::log('AJAX delete_preset: Repository geladen, starte Löschvorgang', 'info');
            $success = $repo->delete($preset_id);
            
            if ($success) {
                Repro_CT_Suite_Logger::log('AJAX delete_preset: Preset erfolgreich gelöscht', 'success');
                wp_send_json_success(array('message' => 'Preset erfolgreich gelöscht'));
            } else {
                Repro_CT_Suite_Logger::log('AJAX delete_preset: Löschvorgang fehlgeschlagen', 'error');
                wp_send_json_error(array('message' => 'Fehler beim Löschen'));
            }
            
        } catch (Exception $e) {
            wp_send_json_error(array(
                'message' => 'Fehler: ' . $e->getMessage()
            ));
        }
    }
    
    /**
     * Preview shortcode output
     */
    public function preview_shortcode() {
        if (!$this->verify_request()) return;
        
        try {
            $preset_id = intval($_POST['preset_id'] ?? 0);
            
            if ($preset_id > 0) {
                require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-repro-ct-suite-shortcode-presets-repository.php';
                $repo = new Repro_CT_Suite_Shortcode_Presets_Repository();
                $preset = $repo->get_by_id($preset_id);
                
                if (!$preset) {
                    wp_send_json_error(array('message' => 'Preset nicht gefunden'));
                    return;
                }
                
                $shortcode = "[repro_ct_suite_events preset=\"{$preset['name']}\"]";
            } else {
                $calendar_ids = $_POST['calendar_ids'] ?? array();
                $limit = intval($_POST['limit'] ?? 10);
                
                if (empty($calendar_ids)) {
                    $shortcode = "[repro_ct_suite_events limit=\"{$limit}\"]";
                } else {
                    $calendar_ids = array_map('intval', $calendar_ids);
                    $calendar_list = implode(',', $calendar_ids);
                    $shortcode = "[repro_ct_suite_events calendars=\"{$calendar_list}\" limit=\"{$limit}\"]";
                }
            }
            
            $output = do_shortcode($shortcode);
            
            wp_send_json_success(array(
                'shortcode' => $shortcode,
                'preview' => $output
            ));
            
        } catch (Exception $e) {
            wp_send_json_error(array(
                'message' => 'Fehler bei der Vorschau: ' . $e->getMessage()
            ));
        }
    }
}
?>