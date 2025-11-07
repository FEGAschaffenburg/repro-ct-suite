<?php
/**
 * Enhanced Input Validator Class
 * Erweiterte Input-Validierung und Sanitization für Sicherheitshärtung
 * 
 * @package Repro_CT_Suite
 * @since 0.9.0
 */

class Repro_CT_Suite_Input_Validator {
    
    /**
     * Definierte Validierungs-Regeln
     */
    const VALIDATION_RULES = [
        'churchtools_tenant' => [
            'type' => 'string',
            'min_length' => 3,
            'max_length' => 50,
            'pattern' => '/^[a-zA-Z0-9\-_]+$/',
            'required' => true
        ],
        'churchtools_username' => [
            'type' => 'email_or_string',
            'min_length' => 3,
            'max_length' => 100,
            'required' => true
        ],
        'churchtools_password' => [
            'type' => 'string',
            'min_length' => 6,
            'max_length' => 255,
            'required' => true
        ],
        'calendar_ids' => [
            'type' => 'integer_array',
            'min_value' => 1,
            'max_value' => 999999,
            'max_items' => 50
        ],
        'sync_from_date' => [
            'type' => 'date',
            'format' => 'Y-m-d',
            'min_date' => '-1 year',
            'max_date' => '+2 years'
        ],
        'sync_to_date' => [
            'type' => 'date',
            'format' => 'Y-m-d',
            'min_date' => '-1 year',
            'max_date' => '+2 years'
        ],
        'shortcode_limit' => [
            'type' => 'integer',
            'min_value' => 1,
            'max_value' => 100,
            'default' => 10
        ],
        'shortcode_view' => [
            'type' => 'enum',
            'allowed_values' => ['list', 'list-grouped', 'cards'],
            'default' => 'cards'
        ],
        'preset_name' => [
            'type' => 'string',
            'min_length' => 1,
            'max_length' => 100,
            'pattern' => '/^[a-zA-Z0-9\s\-_äöüÄÖÜß]+$/',
            'required' => true
        ]
    ];
    
    /**
     * Validiert Input basierend auf definierten Regeln
     * 
     * @param string $field Field-Name (Regel-Key)
     * @param mixed $value Zu validierender Wert
     * @param array $custom_rules Optionale zusätzliche Regeln
     * @return array ['valid' => bool, 'sanitized' => mixed, 'errors' => array]
     */
    public static function validate($field, $value, $custom_rules = []) {
        $result = [
            'valid' => true,
            'sanitized' => $value,
            'errors' => []
        ];
        
        // Regeln abrufen
        $rules = isset(self::VALIDATION_RULES[$field]) 
            ? array_merge(self::VALIDATION_RULES[$field], $custom_rules)
            : $custom_rules;
            
        if (empty($rules)) {
            // Keine Regeln = Standard-Sanitization
            $result['sanitized'] = self::basic_sanitize($value);
            return $result;
        }
        
        // Required-Check
        if (isset($rules['required']) && $rules['required']) {
            if (self::is_empty_value($value)) {
                $result['valid'] = false;
                $result['errors'][] = sprintf(__('Feld "%s" ist erforderlich.', 'repro-ct-suite'), $field);
                return $result;
            }
        }
        
        // Wenn Wert leer und nicht required, return early
        if (self::is_empty_value($value) && (!isset($rules['required']) || !$rules['required'])) {
            $result['sanitized'] = isset($rules['default']) ? $rules['default'] : '';
            return $result;
        }
        
        // Type-basierte Validierung
        $type = isset($rules['type']) ? $rules['type'] : 'string';
        
        switch ($type) {
            case 'string':
                $result = self::validate_string($value, $rules);
                break;
                
            case 'email_or_string':
                $result = self::validate_email_or_string($value, $rules);
                break;
                
            case 'integer':
                $result = self::validate_integer($value, $rules);
                break;
                
            case 'integer_array':
                $result = self::validate_integer_array($value, $rules);
                break;
                
            case 'date':
                $result = self::validate_date($value, $rules);
                break;
                
            case 'enum':
                $result = self::validate_enum($value, $rules);
                break;
                
            case 'boolean':
                $result = self::validate_boolean($value, $rules);
                break;
                
            default:
                $result['sanitized'] = self::basic_sanitize($value);
        }
        
        // Custom Pattern Check
        if ($result['valid'] && isset($rules['pattern'])) {
            if (!preg_match($rules['pattern'], $result['sanitized'])) {
                $result['valid'] = false;
                $result['errors'][] = sprintf(__('Feld "%s" entspricht nicht dem erwarteten Format.', 'repro-ct-suite'), $field);
            }
        }
        
        return $result;
    }
    
    /**
     * Validiert Array von Inputs
     * 
     * @param array $inputs Assoziatives Array [field => value]
     * @param array $custom_rules Optionale Regeln [field => rules]
     * @return array ['valid' => bool, 'sanitized' => array, 'errors' => array]
     */
    public static function validate_array($inputs, $custom_rules = []) {
        $result = [
            'valid' => true,
            'sanitized' => [],
            'errors' => []
        ];
        
        foreach ($inputs as $field => $value) {
            $field_rules = isset($custom_rules[$field]) ? $custom_rules[$field] : [];
            $field_result = self::validate($field, $value, $field_rules);
            
            $result['sanitized'][$field] = $field_result['sanitized'];
            
            if (!$field_result['valid']) {
                $result['valid'] = false;
                $result['errors'] = array_merge($result['errors'], $field_result['errors']);
            }
        }
        
        return $result;
    }
    
    /**
     * String-Validierung
     */
    private static function validate_string($value, $rules) {
        $result = ['valid' => true, 'sanitized' => '', 'errors' => []];
        
        // Sanitize
        $sanitized = sanitize_text_field(trim($value));
        
        // Length-Checks
        $length = mb_strlen($sanitized, 'UTF-8');
        
        if (isset($rules['min_length']) && $length < $rules['min_length']) {
            $result['valid'] = false;
            $result['errors'][] = sprintf(__('Mindestens %d Zeichen erforderlich.', 'repro-ct-suite'), $rules['min_length']);
        }
        
        if (isset($rules['max_length']) && $length > $rules['max_length']) {
            $result['valid'] = false;
            $result['errors'][] = sprintf(__('Maximal %d Zeichen erlaubt.', 'repro-ct-suite'), $rules['max_length']);
        }
        
        $result['sanitized'] = $sanitized;
        return $result;
    }
    
    /**
     * E-Mail oder String Validierung
     */
    private static function validate_email_or_string($value, $rules) {
        $result = ['valid' => true, 'sanitized' => '', 'errors' => []];
        
        $sanitized = sanitize_text_field(trim($value));
        
        // Prüfen ob E-Mail
        if (strpos($sanitized, '@') !== false) {
            $email = sanitize_email($sanitized);
            if (!is_email($email)) {
                $result['valid'] = false;
                $result['errors'][] = __('Ungültige E-Mail-Adresse.', 'repro-ct-suite');
            }
            $result['sanitized'] = $email;
        } else {
            // Als String validieren
            $string_result = self::validate_string($sanitized, $rules);
            $result = $string_result;
        }
        
        return $result;
    }
    
    /**
     * Integer-Validierung
     */
    private static function validate_integer($value, $rules) {
        $result = ['valid' => true, 'sanitized' => 0, 'errors' => []];
        
        if (!is_numeric($value)) {
            $result['valid'] = false;
            $result['errors'][] = __('Muss eine Zahl sein.', 'repro-ct-suite');
            return $result;
        }
        
        $sanitized = intval($value);
        
        if (isset($rules['min_value']) && $sanitized < $rules['min_value']) {
            $result['valid'] = false;
            $result['errors'][] = sprintf(__('Minimum-Wert: %d', 'repro-ct-suite'), $rules['min_value']);
        }
        
        if (isset($rules['max_value']) && $sanitized > $rules['max_value']) {
            $result['valid'] = false;
            $result['errors'][] = sprintf(__('Maximum-Wert: %d', 'repro-ct-suite'), $rules['max_value']);
        }
        
        $result['sanitized'] = $sanitized;
        return $result;
    }
    
    /**
     * Integer-Array Validierung
     */
    private static function validate_integer_array($value, $rules) {
        $result = ['valid' => true, 'sanitized' => [], 'errors' => []];
        
        // String zu Array konvertieren
        if (is_string($value)) {
            $array = array_map('trim', explode(',', $value));
        } elseif (is_array($value)) {
            $array = $value;
        } else {
            $result['valid'] = false;
            $result['errors'][] = __('Muss Array oder komma-separierte Liste sein.', 'repro-ct-suite');
            return $result;
        }
        
        // Leere Werte entfernen
        $array = array_filter($array, function($item) {
            return !self::is_empty_value($item);
        });
        
        // Max Items Check
        if (isset($rules['max_items']) && count($array) > $rules['max_items']) {
            $result['valid'] = false;
            $result['errors'][] = sprintf(__('Maximal %d Elemente erlaubt.', 'repro-ct-suite'), $rules['max_items']);
        }
        
        $sanitized = [];
        foreach ($array as $item) {
            $item_result = self::validate_integer($item, $rules);
            if ($item_result['valid']) {
                $sanitized[] = $item_result['sanitized'];
            } else {
                $result['valid'] = false;
                $result['errors'] = array_merge($result['errors'], $item_result['errors']);
            }
        }
        
        $result['sanitized'] = array_unique($sanitized);
        return $result;
    }
    
    /**
     * Datum-Validierung
     */
    private static function validate_date($value, $rules) {
        $result = ['valid' => true, 'sanitized' => '', 'errors' => []];
        
        $format = isset($rules['format']) ? $rules['format'] : 'Y-m-d';
        
        $date = DateTime::createFromFormat($format, $value);
        
        if (!$date || $date->format($format) !== $value) {
            $result['valid'] = false;
            $result['errors'][] = sprintf(__('Ungültiges Datumsformat. Erwartet: %s', 'repro-ct-suite'), $format);
            return $result;
        }
        
        // Min/Max Date Checks
        if (isset($rules['min_date'])) {
            $min_date = new DateTime($rules['min_date']);
            if ($date < $min_date) {
                $result['valid'] = false;
                $result['errors'][] = sprintf(__('Datum muss nach %s liegen.', 'repro-ct-suite'), $min_date->format($format));
            }
        }
        
        if (isset($rules['max_date'])) {
            $max_date = new DateTime($rules['max_date']);
            if ($date > $max_date) {
                $result['valid'] = false;
                $result['errors'][] = sprintf(__('Datum muss vor %s liegen.', 'repro-ct-suite'), $max_date->format($format));
            }
        }
        
        $result['sanitized'] = $date->format($format);
        return $result;
    }
    
    /**
     * Enum-Validierung
     */
    private static function validate_enum($value, $rules) {
        $result = ['valid' => true, 'sanitized' => '', 'errors' => []];
        
        $sanitized = sanitize_text_field(trim($value));
        $allowed = isset($rules['allowed_values']) ? $rules['allowed_values'] : [];
        
        if (!in_array($sanitized, $allowed, true)) {
            $result['valid'] = false;
            $result['errors'][] = sprintf(__('Erlaubte Werte: %s', 'repro-ct-suite'), implode(', ', $allowed));
            
            // Default-Wert setzen falls verfügbar
            if (isset($rules['default'])) {
                $result['sanitized'] = $rules['default'];
            }
        } else {
            $result['sanitized'] = $sanitized;
        }
        
        return $result;
    }
    
    /**
     * Boolean-Validierung
     */
    private static function validate_boolean($value, $rules) {
        $result = ['valid' => true, 'sanitized' => false, 'errors' => []];
        
        if (is_bool($value)) {
            $result['sanitized'] = $value;
        } elseif (is_string($value)) {
            $lower = strtolower(trim($value));
            if (in_array($lower, ['true', '1', 'yes', 'on'], true)) {
                $result['sanitized'] = true;
            } elseif (in_array($lower, ['false', '0', 'no', 'off', ''], true)) {
                $result['sanitized'] = false;
            } else {
                $result['valid'] = false;
                $result['errors'][] = __('Muss boolean-Wert sein (true/false).', 'repro-ct-suite');
            }
        } elseif (is_numeric($value)) {
            $result['sanitized'] = intval($value) === 1;
        } else {
            $result['valid'] = false;
            $result['errors'][] = __('Muss boolean-Wert sein (true/false).', 'repro-ct-suite');
        }
        
        return $result;
    }
    
    /**
     * Basic Sanitization für unbekannte Typen
     */
    private static function basic_sanitize($value) {
        if (is_string($value)) {
            return sanitize_text_field($value);
        } elseif (is_array($value)) {
            return array_map([self::class, 'basic_sanitize'], $value);
        } else {
            return $value;
        }
    }
    
    /**
     * Prüft ob Wert als leer gilt
     */
    private static function is_empty_value($value) {
        return $value === null || $value === '' || $value === [] || $value === false;
    }
    
    /**
     * Sanitized Error Messages (XSS-Schutz)
     * 
     * @param array $errors Error-Array
     * @return array Sanitized errors
     */
    public static function sanitize_errors($errors) {
        return array_map('esc_html', $errors);
    }
    
    /**
     * Holt Validierungsregeln für spezifisches Feld
     * 
     * @param string $field Field-Name
     * @return array|null Regeln oder null
     */
    public static function get_field_rules($field) {
        return isset(self::VALIDATION_RULES[$field]) ? self::VALIDATION_RULES[$field] : null;
    }
}

// WordPress-Funktionen für Testing-Environment
if (!function_exists('sanitize_text_field')) {
    function sanitize_text_field($str) {
        return htmlspecialchars(strip_tags(trim($str)), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('sanitize_email')) {
    function sanitize_email($email) {
        return filter_var(trim($email), FILTER_SANITIZE_EMAIL);
    }
}

if (!function_exists('is_email')) {
    function is_email($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}

if (!function_exists('esc_html')) {
    function esc_html($text) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('__')) {
    function __($text, $domain = 'default') {
        return $text; // Simplified for testing
    }
}