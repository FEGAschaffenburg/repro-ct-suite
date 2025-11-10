<?php
/**
 * Rate Limiter Class
 * Implementiert Rate Limiting für API-Calls zur Sicherheitshärtung
 * 
 * @package Repro_CT_Suite
 * @since 0.9.0
 */

class Repro_CT_Suite_Rate_Limiter {
    
    /**
     * Transient prefix für Rate Limiting
     */
    const TRANSIENT_PREFIX = 'rcts_rate_limit_';
    
    /**
     * Standard Rate Limits (Requests pro Zeitraum)
     */
    const DEFAULT_LIMITS = [
        'api_call' => [
            'requests' => 60,      // 60 requests
            'window' => 3600       // pro Stunde (3600 Sekunden)
        ],
        'sync_action' => [
            'requests' => 10,      // 10 sync requests  
            'window' => 600        // pro 10 Minuten
        ],
        'login_attempt' => [
            'requests' => 5,       // 5 Login-Versuche
            'window' => 900        // pro 15 Minuten
        ]
    ];
    
    /**
     * Prüft ob ein Request erlaubt ist
     * 
     * @param string $action Action-Type (api_call, sync_action, login_attempt)
     * @param string $identifier Eindeutige ID (User-ID, IP, etc.)
     * @return bool True wenn erlaubt, False wenn Rate Limit erreicht
     */
    public static function is_allowed($action, $identifier = null) {
        // Identifier generieren falls nicht angegeben
        if (!$identifier) {
            $identifier = self::get_default_identifier();
        }
        
        // Rate Limit Konfiguration abrufen
        $limits = self::get_limits($action);
        if (!$limits) {
            return true; // Kein Limit definiert = erlaubt
        }
        
        // Transient-Key generieren
        $transient_key = self::get_transient_key($action, $identifier);
        
        // Aktuelle Request-Anzahl abrufen
        $current_requests = get_transient($transient_key);
        if ($current_requests === false) {
            $current_requests = 0;
        }
        
        // Prüfen ob Limit erreicht
        if ($current_requests >= $limits['requests']) {
            self::log_rate_limit_hit($action, $identifier, $current_requests, $limits);
            return false;
        }
        
        return true;
    }
    
    /**
     * Registriert einen Request (erhöht Counter)
     * 
     * @param string $action Action-Type
     * @param string $identifier Eindeutige ID
     * @return bool Success
     */
    public static function register_request($action, $identifier = null) {
        if (!$identifier) {
            $identifier = self::get_default_identifier();
        }
        
        $limits = self::get_limits($action);
        if (!$limits) {
            return true; // Kein Limit = immer erlaubt
        }
        
        $transient_key = self::get_transient_key($action, $identifier);
        
        // Counter erhöhen
        $current_requests = get_transient($transient_key);
        if ($current_requests === false) {
            $current_requests = 0;
        }
        
        $new_count = $current_requests + 1;
        
        // Transient mit neuem Counter setzen
        set_transient($transient_key, $new_count, $limits['window']);
        
        self::log_request($action, $identifier, $new_count, $limits);
        
        return true;
    }
    
    /**
     * Kombinierte Prüfung und Registrierung
     * 
     * @param string $action Action-Type
     * @param string $identifier Eindeutige ID
     * @return bool True wenn Request erlaubt und registriert
     */
    public static function check_and_register($action, $identifier = null) {
        if (!self::is_allowed($action, $identifier)) {
            return false;
        }
        
        return self::register_request($action, $identifier);
    }
    
    /**
     * Holt Rate Limit Konfiguration für Action
     * 
     * @param string $action Action-Type
     * @return array|false Limit-Configuration oder false
     */
    private static function get_limits($action) {
        $all_limits = apply_filters('rcts_rate_limits', self::DEFAULT_LIMITS);
        
        return isset($all_limits[$action]) ? $all_limits[$action] : false;
    }
    
    /**
     * Generiert Standard-Identifier (IP + User-ID)
     * 
     * @return string Eindeutige Identifier
     */
    private static function get_default_identifier() {
        $user_id = get_current_user_id();
        $ip = self::get_client_ip();
        
        return md5($ip . '_' . $user_id);
    }
    
    /**
     * Holt Client-IP-Adresse (berücksichtigt Proxies)
     * 
     * @return string IP-Adresse
     */
    private static function get_client_ip() {
        $ip_keys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];
        
        foreach ($ip_keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                // Bei mehreren IPs (X-Forwarded-For) erste nehmen
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                
                // IP validieren
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'unknown';
    }
    
    /**
     * Generiert Transient-Key
     * 
     * @param string $action Action-Type
     * @param string $identifier Identifier
     * @return string Transient-Key
     */
    private static function get_transient_key($action, $identifier) {
        return self::TRANSIENT_PREFIX . $action . '_' . substr(md5($identifier), 0, 8);
    }
    
    /**
     * Loggt Rate Limit Hit
     * 
     * @param string $action Action-Type
     * @param string $identifier Identifier
     * @param int $current_requests Aktuelle Request-Anzahl
     * @param array $limits Limit-Konfiguration
     */
    private static function log_rate_limit_hit($action, $identifier, $current_requests, $limits) {
        $message = sprintf(
            'Rate Limit erreicht - Action: %s, Identifier: %s, Requests: %d/%d, Window: %d Sekunden',
            $action,
            substr($identifier, 0, 8) . '...',
            $current_requests,
            $limits['requests'],
            $limits['window']
        );
        
        error_log('[RCTS Rate Limiter] ' . $message);
        
        // WordPress-Log falls Debug aktiviert
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log($message);
        }
        
        // Hook für weitere Aktionen (z.B. Admin-Benachrichtigung)
        do_action('rcts_rate_limit_exceeded', $action, $identifier, $current_requests, $limits);
    }
    
    /**
     * Loggt Request (bei Debug)
     * 
     * @param string $action Action-Type
     * @param string $identifier Identifier
     * @param int $new_count Neue Request-Anzahl
     * @param array $limits Limit-Konfiguration
     */
    private static function log_request($action, $identifier, $new_count, $limits) {
        if (!defined('WP_DEBUG') || !WP_DEBUG) {
            return;
        }
        
        $message = sprintf(
            'Rate Limit Request - Action: %s, Requests: %d/%d',
            $action,
            $new_count,
            $limits['requests']
        );
        
        error_log('[RCTS Rate Limiter] ' . $message);
    }
    
    /**
     * Holt aktuelle Rate Limit Statistiken
     * 
     * @param string $action Action-Type
     * @param string $identifier Identifier
     * @return array Statistiken
     */
    public static function get_stats($action, $identifier = null) {
        if (!$identifier) {
            $identifier = self::get_default_identifier();
        }
        
        $limits = self::get_limits($action);
        if (!$limits) {
            return null;
        }
        
        $transient_key = self::get_transient_key($action, $identifier);
        $current_requests = get_transient($transient_key);
        
        if ($current_requests === false) {
            $current_requests = 0;
        }
        
        return [
            'action' => $action,
            'identifier' => substr($identifier, 0, 8) . '...',
            'current_requests' => $current_requests,
            'max_requests' => $limits['requests'],
            'window_seconds' => $limits['window'],
            'remaining_requests' => max(0, $limits['requests'] - $current_requests),
            'is_limited' => $current_requests >= $limits['requests']
        ];
    }
    
    /**
     * Löscht Rate Limit für spezifische Action/Identifier
     * 
     * @param string $action Action-Type
     * @param string $identifier Identifier
     * @return bool Success
     */
    public static function clear_limit($action, $identifier = null) {
        if (!$identifier) {
            $identifier = self::get_default_identifier();
        }
        
        $transient_key = self::get_transient_key($action, $identifier);
        return delete_transient($transient_key);
    }
    
    /**
     * Löscht alle Rate Limits (Emergency-Funktion)
     * 
     * @return bool Success
     */
    public static function clear_all_limits() {
        global $wpdb;
        
        $prefix = self::TRANSIENT_PREFIX;
        
        // Transients aus Datenbank löschen
        $result = $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
                $wpdb->esc_like('_transient_' . $prefix) . '%'
            )
        );
        
        // Transient-Timeouts löschen
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
                $wpdb->esc_like('_transient_timeout_' . $prefix) . '%'
            )
        );
        
        error_log('[RCTS Rate Limiter] Alle Rate Limits zurückgesetzt');
        
        return $result !== false;
    }
}