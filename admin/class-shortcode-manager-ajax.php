<?php
/**
 * Shortcode Manager AJAX Handler - Korrigierte Version v2
 * Backend-Funktionen für den neuen Shortcode Manager
 * 
 * @package Repro_CT_Suite
 * @since 0.9.2.0
 */

class Repro_CT_Suite_Shortcode_Manager_Ajax {
    
    /**
     * Constructor
     */
    public function __construct() {
        // AJAX actions for logged-in users - Namen korrigiert
        add_action('wp_ajax_sm_get_presets', array($this, 'get_presets'));
        add_action('wp_ajax_sm_save_preset', array($this, 'save_preset'));
        add_action('wp_ajax_sm_update_preset', array($this, 'update_preset'));
        add_action('wp_ajax_sm_delete_preset', array($this, 'delete_preset'));
        add_action('wp_ajax_sm_get_preview', array($this, 'preview_preset'));
        
        // Zusätzliche AJAX-Handler für Kalender-Daten
        add_action('wp_ajax_sm_get_calendars', array($this, 'get_calendars'));
    }
    
    /**
     * Get all presets (renamed from get_preset)
     */
    public function get_presets() {
        // Security check
        if (!wp_verify_nonce($_POST['nonce'], 'sm_nonce')) {
            wp_send_json_error(array('message' => 'Security check failed'));
            return;
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Insufficient permissions'));
            return;
        }
        
        try {
            // Repository laden
            require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-repro-ct-suite-shortcode-presets-repository.php';
            $repo = new Repro_CT_Suite_Shortcode_Presets_Repository();
            
            $presets = $repo->get_all();
            
            wp_send_json_success(array(
                'presets' => $presets,
                'count' => count($presets)
            ));
            
        } catch (Exception $e) {
            wp_send_json_error(array(
                'message' => 'Fehler beim Laden der Presets: ' . $e->getMessage()
            ));
        }
    }
    
    /**
     * Get calendars for selection
     */
    public function get_calendars() {
        // Security check
        if (!wp_verify_nonce($_POST['nonce'], 'sm_nonce')) {
            wp_send_json_error(array('message' => 'Security check failed'));
            return;
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Insufficient permissions'));
            return;
        }
        
        try {
            // Repository laden
            require_once plugin_dir_path(dirname(__FILE__)) . 'includes/repositories/class-repro-ct-suite-repository-base.php';
            require_once plugin_dir_path(dirname(__FILE__)) . 'includes/repositories/class-repro-ct-suite-calendars-repository.php';
            $repo = new Repro_CT_Suite_Calendars_Repository();
            
            $calendars = $repo->get_all();
            
            wp_send_json_success(array(
                'calendars' => $calendars,
                'count' => count($calendars)
            ));
            
        } catch (Exception $e) {
            wp_send_json_error(array(
                'message' => 'Fehler beim Laden der Kalender: ' . $e->getMessage()
            ));
        }
    }
    
    /**
     * Save new preset
     */
    public function save_preset() {
        // Security check
        if (!wp_verify_nonce($_POST['nonce'], 'sm_nonce')) {
            wp_send_json_error(array('message' => 'Security check failed'));
            return;
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Insufficient permissions'));
            return;
        }
        
        try {
            // Debug: Log all received data
            error_log('RCTS Debug: Received POST data: ' . print_r($_POST, true));
            
            // Input validieren
            $name = sanitize_text_field($_POST['name'] ?? '');
            $calendar_ids = $_POST['calendar_ids'] ?? array();
            $limit = intval($_POST['limit'] ?? 10);
            $show_time = isset($_POST['show_time']) ? intval($_POST['show_time']) : 0;
            $show_location = isset($_POST['show_location']) ? intval($_POST['show_location']) : 0;
            $show_description = isset($_POST['show_description']) ? intval($_POST['show_description']) : 0;
            
            // Debug: Log parsed data
            error_log('RCTS Debug: Parsed data - Name: ' . $name . ', Calendar IDs: ' . print_r($calendar_ids, true) . ', Limit: ' . $limit);
            
            if (empty($name)) {
                wp_send_json_error(array('message' => 'Preset-Name ist erforderlich'));
                return;
            }
            
            // Kalender-IDs validieren (optional - kann auch leer sein für "alle Kalender")
            if (!is_array($calendar_ids)) {
                $calendar_ids = array();
            }
            
            // Calendar-IDs validieren
            $calendar_ids = array_map('intval', $calendar_ids);
            $calendar_ids = array_filter($calendar_ids, function($id) { return $id > 0; });
            
            // Repository laden
            require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-repro-ct-suite-shortcode-presets-repository.php';
            $repo = new Repro_CT_Suite_Shortcode_Presets_Repository();
            
            // Daten für Repository vorbereiten
            $preset_data = array(
                'name' => $name,
                'limit_count' => $limit,
                'calendar_ids' => $calendar_ids,
                'show_time' => $show_time,
                'show_location' => $show_location,
                'show_description' => $show_description
            );
            
            error_log('RCTS Debug: Calling repo->save with data: ' . print_r($preset_data, true));
            
            $preset_id = $repo->save($preset_data);
            
            if ($preset_id) {
                wp_send_json_success(array(
                    'message' => 'Preset erfolgreich gespeichert',
                    'preset_id' => $preset_id
                ));
            } else {
                wp_send_json_error(array('message' => 'Fehler beim Speichern des Presets'));
            }
            
        } catch (Exception $e) {
            wp_send_json_error(array(
                'message' => 'Fehler beim Speichern: ' . $e->getMessage()
            ));
        }
    }
    
    /**
     * Update existing preset
     */
    public function update_preset() {
        // Security check
        if (!wp_verify_nonce($_POST['nonce'], 'sm_nonce')) {
            wp_send_json_error(array('message' => 'Security check failed'));
            return;
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Insufficient permissions'));
            return;
        }
        
        try {
            $preset_id = intval($_POST['preset_id'] ?? 0);
            if ($preset_id <= 0) {
                wp_send_json_error(array('message' => 'Ungültige Preset-ID'));
                return;
            }
            
            // Input validieren (gleich wie bei save_preset)
            $name = sanitize_text_field($_POST['name'] ?? '');
            $calendar_ids = $_POST['calendar_ids'] ?? array();
            $limit = intval($_POST['limit'] ?? 10);
            $show_time = isset($_POST['show_time']) ? intval($_POST['show_time']) : 0;
            $show_location = isset($_POST['show_location']) ? intval($_POST['show_location']) : 0;
            $show_description = isset($_POST['show_description']) ? intval($_POST['show_description']) : 0;
            
            if (empty($name)) {
                wp_send_json_error(array('message' => 'Preset-Name ist erforderlich'));
                return;
            }
            
            if (!is_array($calendar_ids)) {
                $calendar_ids = array();
            }
            
            $calendar_ids = array_map('intval', $calendar_ids);
            $calendar_ids = array_filter($calendar_ids, function($id) { return $id > 0; });
            
            // Repository laden
            require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-repro-ct-suite-shortcode-presets-repository.php';
            $repo = new Repro_CT_Suite_Shortcode_Presets_Repository();
            
            // Daten für Repository vorbereiten
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
                wp_send_json_error(array('message' => 'Fehler beim Aktualisieren des Presets'));
            }
            
        } catch (Exception $e) {
            wp_send_json_error(array(
                'message' => 'Fehler beim Aktualisieren: ' . $e->getMessage()
            ));
        }
    }
    
    /**
     * Delete preset
     */
    public function delete_preset() {
        // Security check
        if (!wp_verify_nonce($_POST['nonce'], 'sm_nonce')) {
            wp_send_json_error(array('message' => 'Security check failed'));
            return;
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Insufficient permissions'));
            return;
        }
        
        try {
            $preset_id = intval($_POST['preset_id'] ?? 0);
            if ($preset_id <= 0) {
                wp_send_json_error(array('message' => 'Ungültige Preset-ID'));
                return;
            }
            
            // Repository laden
            require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-repro-ct-suite-shortcode-presets-repository.php';
            $repo = new Repro_CT_Suite_Shortcode_Presets_Repository();
            
            $success = $repo->delete($preset_id);
            
            if ($success) {
                wp_send_json_success(array('message' => 'Preset erfolgreich gelöscht'));
            } else {
                wp_send_json_error(array('message' => 'Fehler beim Löschen des Presets'));
            }
            
        } catch (Exception $e) {
            wp_send_json_error(array(
                'message' => 'Fehler beim Löschen: ' . $e->getMessage()
            ));
        }
    }
    
    /**
     * Preview preset (generate shortcode output)
     */
    public function preview_preset() {
        // Security check
        if (!wp_verify_nonce($_POST['nonce'], 'sm_nonce')) {
            wp_send_json_error(array('message' => 'Security check failed'));
            return;
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Insufficient permissions'));
            return;
        }
        
        try {
            // Preset-ID oder direkte Parameter
            $preset_id = intval($_POST['preset_id'] ?? 0);
            
            if ($preset_id > 0) {
                // Preset laden
                require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-repro-ct-suite-shortcode-presets-repository.php';
                $repo = new Repro_CT_Suite_Shortcode_Presets_Repository();
                $preset = $repo->get_by_id($preset_id);
                
                if (!$preset) {
                    wp_send_json_error(array('message' => 'Preset nicht gefunden'));
                    return;
                }
                
                $shortcode = "[repro_ct_suite_events preset=\"{$preset->name}\"]";
            } else {
                // Direkte Parameter
                $calendar_ids = $_POST['calendar_ids'] ?? array();
                $limit = intval($_POST['limit'] ?? 10);
                
                if (empty($calendar_ids)) {
                    wp_send_json_error(array('message' => 'Keine Kalender ausgewählt'));
                    return;
                }
                
                $calendar_ids = array_map('intval', $calendar_ids);
                $calendar_list = implode(',', $calendar_ids);
                
                $shortcode = "[repro_ct_suite_events calendars=\"{$calendar_list}\" limit=\"{$limit}\"]";
            }
            
            // Shortcode ausführen für Vorschau
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