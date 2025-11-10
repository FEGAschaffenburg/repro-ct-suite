# Security-Checkliste für Repro CT-Suite v1.0.0

## 🔒 Aktuelle Sicherheitsmaßnahmen (✅ Implementiert)

### Authentifizierung & Zugangsdaten
- ✅ **AES-256-CBC Verschlüsselung** für ChurchTools-Passwörter
- ✅ **Sichere Passwort-Speicherung** in WordPress-Datenbank
- ✅ **Session-Cookie Management** mit automatischem Re-Login
- ✅ **Nonce-Schutz** für alle AJAX-Anfragen
- ✅ **Capability-Checks** (`manage_options`) für Admin-Funktionen

### Input-Validierung & Sanitization
- ✅ **WordPress-Sanitization** mit `sanitize_text_field()`, `esc_html()`, etc.
- ✅ **SQL-Prepared Statements** in allen Repository-Klassen
- ✅ **CSRF-Schutz** über WordPress-Nonces
- ✅ **Admin-Referer-Checks** für kritische Aktionen

### Datenzugriff & Berechtigungen
- ✅ **Capability-basierte Autorisierung** für alle Admin-Funktionen
- ✅ **WordPress Uninstall-Hook** für saubere Datenbereinigung
- ✅ **Separate DB-Tabellen** mit Präfix-Schutz
- ✅ **Error-Logging** ohne Credential-Exposure

---

## ⚠️ Sicherheits-Verbesserungen für v1.0.0

### 1. **Input-Validierung verstärken**
```php
// TODO: Implementieren
class Repro_CT_Suite_Validator {
    public static function validate_calendar_ids( $input ) {
        // Strengere Validierung für Kalender-IDs
        if ( ! is_string( $input ) ) return false;
        
        $ids = explode( ',', $input );
        foreach ( $ids as $id ) {
            if ( ! preg_match( '/^[a-zA-Z0-9_-]+$/', trim( $id ) ) ) {
                return false;
            }
        }
        return true;
    }
    
    public static function validate_shortcode_attributes( $atts ) {
        // Umfassende Attribut-Validierung
        $allowed_views = array( 'list', 'list-grouped', 'cards' );
        if ( isset( $atts['view'] ) && ! in_array( $atts['view'], $allowed_views, true ) ) {
            return false;
        }
        return true;
    }
}
```

### 2. **Rate-Limiting implementieren**
```php
// TODO: Implementieren
class Repro_CT_Suite_Rate_Limiter {
    private static $limits = array(
        'api_calls' => array( 'max' => 100, 'window' => 3600 ), // 100 calls/hour
        'login_attempts' => array( 'max' => 5, 'window' => 900 ), // 5 attempts/15min
    );
    
    public static function check_limit( $action, $identifier = null ) {
        $key = 'rcts_rate_limit_' . $action . '_' . ( $identifier ?: get_current_user_id() );
        $count = get_transient( $key );
        
        if ( $count && $count >= self::$limits[ $action ]['max'] ) {
            return false; // Rate limit exceeded
        }
        
        set_transient( $key, ( $count ?: 0 ) + 1, self::$limits[ $action ]['window'] );
        return true;
    }
}
```

### 3. **Error-Sanitization verbessern**
```php
// TODO: Implementieren
class Repro_CT_Suite_Error_Handler {
    public static function sanitize_error( $error_message ) {
        // Entferne potentiell sensitive Informationen
        $patterns = array(
            '/password[=:]\s*[^\s&]+/i',
            '/token[=:]\s*[^\s&]+/i',
            '/key[=:]\s*[^\s&]+/i',
            '/\/wp-content\/[^\s]+/',
            '/in\s+\/[^\s]+\.php/',
        );
        
        foreach ( $patterns as $pattern ) {
            $error_message = preg_replace( $pattern, '[REDACTED]', $error_message );
        }
        
        return $error_message;
    }
}
```

### 4. **Content Security Policy Headers**
```php
// TODO: Implementieren in admin/class-repro-ct-suite-admin.php
public function add_security_headers() {
    if ( is_admin() && strpos( $_SERVER['REQUEST_URI'], 'repro-ct-suite' ) !== false ) {
        header( "Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline';" );
        header( "X-Content-Type-Options: nosniff" );
        header( "X-Frame-Options: SAMEORIGIN" );
        header( "X-XSS-Protection: 1; mode=block" );
    }
}
```

### 5. **Audit-Logging für kritische Aktionen**
```php
// TODO: Implementieren
class Repro_CT_Suite_Audit_Logger {
    public static function log_action( $action, $data = array() ) {
        $log_entry = array(
            'timestamp' => current_time( 'mysql' ),
            'user_id' => get_current_user_id(),
            'user_login' => wp_get_current_user()->user_login,
            'action' => $action,
            'ip_address' => self::get_client_ip(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            'data' => wp_json_encode( $data ),
        );
        
        global $wpdb;
        $wpdb->insert( $wpdb->prefix . 'rcts_audit_log', $log_entry );
    }
    
    private static function get_client_ip() {
        $headers = array( 'HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED', 'REMOTE_ADDR' );
        foreach ( $headers as $header ) {
            if ( ! empty( $_SERVER[ $header ] ) ) {
                return sanitize_text_field( $_SERVER[ $header ] );
            }
        }
        return 'Unknown';
    }
}
```

---

## 🛡️ WordPress-Security Best Practices

### Bereits implementiert:
- ✅ Keine `eval()` oder `exec()` Funktionen
- ✅ Keine direkten SQL-Queries ohne Prepared Statements
- ✅ Keine `$_GET`/`$_POST` ohne Validierung
- ✅ WordPress Coding Standards befolgt
- ✅ Keine Hard-coded Credentials
- ✅ Sichere File-Includes (kein User-Input in `require`)

### Zusätzliche Empfehlungen:
- ⚠️ **Plugin-Isolation**: Namespace für alle Klassen konsistent verwenden
- ⚠️ **Database-Indexe**: Für Performance und Security-Queries
- ⚠️ **Backup-Integration**: Mit UpdraftPlus/BackWPup kompatibel
- ⚠️ **Two-Factor-Auth**: Für ChurchTools-Admin-Accounts empfehlen

---

## 🔍 Security-Tests für v1.0.0

### Penetration Testing Checkliste:
- [ ] **SQL-Injection Tests** für alle User-Inputs
- [ ] **XSS-Tests** für alle Output-Funktionen  
- [ ] **CSRF-Tests** für alle Admin-Aktionen
- [ ] **Authentication-Bypass Tests**
- [ ] **Privilege-Escalation Tests**
- [ ] **File-Upload Security** (falls implementiert)
- [ ] **API-Endpoint Security** (ChurchTools-Integration)

### Automated Security Scanning:
```bash
# WP Security Scanner
wp plugin install wordfence --activate

# PHP Security Scanner  
composer require --dev roave/security-advisories

# JavaScript Dependency Audit
npm audit

# WordPress VIP Code Analysis
phpcs --standard=WordPress-VIP-Go
```

---

## 📋 Security-Deployment Checklist

### Pre-Production:
- [ ] Alle TODOs in dieser Checkliste abgearbeitet
- [ ] Security-Tests durchgeführt 
- [ ] Code-Review mit Security-Focus
- [ ] Penetration Testing abgeschlossen
- [ ] Dependency-Security-Audit

### Production-Deployment:
- [ ] Sichere Server-Konfiguration (HTTPS, etc.)
- [ ] WordPress-Hardening aktiviert
- [ ] Security-Plugins installiert (Wordfence/Sucuri)
- [ ] Backup-System konfiguriert
- [ ] Monitoring & Alerting eingerichtet

### Post-Deployment:
- [ ] Security-Monitoring aktiv
- [ ] Regelmäßige Security-Updates geplant
- [ ] Incident-Response-Plan dokumentiert
- [ ] User-Security-Training durchgeführt

---

**Ziel**: 100% sichere erste produktive Version für sensible ChurchTools-Integration.