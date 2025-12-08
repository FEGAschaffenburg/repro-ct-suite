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
        add_action('wp_ajax_sm_get_preset', array($this, 'get_preset')); // Einzelnes Preset laden (für Edit)
        add_action('wp_ajax_sm_get_all_presets', array($this, 'get_all_presets')); // Für Gutenberg Block
        add_action('wp_ajax_sm_save_preset', array($this, 'save_preset'));
        add_action('wp_ajax_sm_update_preset', array($this, 'update_preset'));
        add_action('wp_ajax_sm_delete_preset', array($this, 'delete_preset'));
        add_action('wp_ajax_sm_get_preview', array($this, 'preview_preset'));
        
        // Zusätzliche AJAX-Handler für Kalender-Daten
        add_action('wp_ajax_sm_get_calendars', array($this, 'get_calendars'));
    }
    
    /**
     * Get all presets for Gutenberg Block (public access for editor)
     */
    public function get_all_presets(): void {
        // Security check
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'sm_ajax_nonce')) {
            wp_send_json_error(array('message' => 'Security check failed'));
            return;
        }
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => 'Insufficient permissions'));
            return;
        }
        
        try {
            // Repository laden
            require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-repro-ct-suite-shortcode-presets-repository.php';
            $repo = new Repro_CT_Suite_Shortcode_Presets_Repository();
            
            $presets = $repo->get_all();
            
            wp_send_json_success($presets);
        } catch (Exception $e) {
            wp_send_json_error(array('message' => $e->getMessage()));
        }
    }
    
    /**
     * Get single preset by ID (für Edit-Funktion)
     */
    public function get_preset(): void {
        // Sicherheitsprüfung
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'sm_nonce')) {
            wp_send_json_error(array('message' => 'Security check failed'));
            return;
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Insufficient permissions'));
            return;
        }
        
        $preset_id = isset($_POST['preset_id']) ? intval($_POST['preset_id']) : 0;
        
        if (empty($preset_id)) {
            wp_send_json_error(array('message' => 'Keine Preset-ID angegeben'));
            return;
        }
        
        try {
            // Repository laden
            require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-repro-ct-suite-shortcode-presets-repository.php';
            $repo = new Repro_CT_Suite_Shortcode_Presets_Repository();
            
            $preset = $repo->get_by_id($preset_id);
            
            if (!$preset) {
                wp_send_json_error(array('message' => 'Shortcode nicht gefunden'));
                return;
            }
            
            wp_send_json_success($preset);
            
        } catch (Exception $e) {
            wp_send_json_error(array(
                'message' => 'Fehler beim Laden des Shortcodes: ' . $e->getMessage()
            ));
        }
    }
    
    /**
     * Get all presets (renamed from get_preset)
     */
    public function get_presets(): void {
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
    public function get_calendars(): void {
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
     * Save new preset or update existing
     */
    public function save_preset(): void {
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
            // Check if this is an update (preset_id provided)
            $preset_id = !empty($_POST['preset_id']) ? intval($_POST['preset_id']) : null;
            
            // Debug: Log all received data
            error_log('RCTS Debug: Received POST data: ' . print_r($_POST, true));
            error_log('RCTS Debug: Preset ID: ' . ($preset_id ? $preset_id : 'NULL (new)'));
            
            // Input validieren
            $name = sanitize_text_field($_POST['name'] ?? '');
            $calendar_mode = sanitize_text_field($_POST['calendar_mode'] ?? 'all');
            $calendar_ids = $_POST['calendar_ids'] ?? array();
            $display_mode = sanitize_text_field($_POST['display_mode'] ?? 'list');
            $events_limit = intval($_POST['events_limit'] ?? 10);
            $days_ahead = intval($_POST['days_ahead'] ?? 30);
            $show_descriptions = isset($_POST['show_descriptions']) ? 1 : 0;
            $show_locations = isset($_POST['show_locations']) ? 1 : 0;
            $show_time = isset($_POST['show_time']) ? 1 : 0;
            $show_organizer = isset($_POST['show_organizer']) ? 1 : 0;
            
            // Debug: Log parsed data
            error_log('RCTS Debug: Parsed data - Name: ' . $name . ', Mode: ' . $display_mode . ', Calendar IDs: ' . print_r($calendar_ids, true));
            
            if (empty($name)) {
                wp_send_json_error(array('message' => 'Shortcode-Name ist erforderlich'));
                return;
            }
            
            // Kalender-IDs validieren
            if (!is_array($calendar_ids)) {
                $calendar_ids = array();
            }
            $calendar_ids = array_map('intval', $calendar_ids);
            $calendar_ids = array_filter($calendar_ids, function($id) { return $id > 0; });
            
            // Repository laden
            require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-repro-ct-suite-shortcode-presets-repository.php';
            $repo = new Repro_CT_Suite_Shortcode_Presets_Repository();
            
            // Shortcode-Tag generieren
            $shortcode_tag = 'ct_' . sanitize_title($name);
            
            // Daten für Repository vorbereiten
            $preset_data = array(
                'name' => $name,
                'shortcode_tag' => $shortcode_tag,
                'display_mode' => $display_mode,
                'calendar_ids' => ($calendar_mode === 'specific' && !empty($calendar_ids)) ? implode(',', $calendar_ids) : null,
                'limit_count' => $events_limit,
                'days_ahead' => $days_ahead,
                'show_time' => $show_time,
                'show_location' => $show_locations,
                'show_description' => $show_descriptions,
                'show_organizer' => $show_organizer
            );
            
            error_log('RCTS Debug: Preset data for save/update: ' . print_r($preset_data, true));
            
            // UPDATE existing preset
            if ($preset_id) {
                $success = $repo->update($preset_id, $preset_data);
                
                if ($success) {
                    wp_send_json_success(array(
                        'message' => 'Shortcode erfolgreich aktualisiert',
                        'preset_id' => $preset_id,
                        'shortcode' => '[' . $shortcode_tag . ']'
                    ));
                } else {
                    wp_send_json_error(array('message' => 'Fehler beim Aktualisieren des Shortcodes'));
                }
            }
            // CREATE new preset
            else {
                $new_preset_id = $repo->save($preset_data);
                
                if ($new_preset_id) {
                    wp_send_json_success(array(
                        'message' => 'Shortcode erfolgreich erstellt',
                        'preset_id' => $new_preset_id,
                        'shortcode' => '[' . $shortcode_tag . ']'
                    ));
                } else {
                    wp_send_json_error(array('message' => 'Fehler beim Erstellen des Shortcodes'));
                }
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
    public function update_preset(): void {
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
    public function delete_preset(): void {
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
    public function preview_preset(): void {
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



