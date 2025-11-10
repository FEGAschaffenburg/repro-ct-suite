<?php
/**
 * Unit Tests für Input Validator
 * 
 * @package Repro_CT_Suite
 * @subpackage Tests
 */

class Test_Input_Validator extends WP_UnitTestCase {
    
    /**
     * Test String Validation
     */
    public function test_string_validation() {
        // Valid string
        $result = Repro_CT_Suite_Input_Validator::validate('preset_name', 'Mein Preset');
        $this->assertTrue($result['valid']);
        $this->assertEquals('Mein Preset', $result['sanitized']);
        $this->assertEmpty($result['errors']);
        
        // Too short
        $result = Repro_CT_Suite_Input_Validator::validate('preset_name', '');
        $this->assertFalse($result['valid']);
        $this->assertNotEmpty($result['errors']);
        
        // Invalid characters
        $result = Repro_CT_Suite_Input_Validator::validate('preset_name', 'Test<script>');
        $this->assertTrue($result['valid']); // Should sanitize
        $this->assertEquals('Test', $result['sanitized']); // HTML stripped
    }
    
    /**
     * Test ChurchTools Tenant Validation
     */
    public function test_churchtools_tenant_validation() {
        // Valid tenant
        $result = Repro_CT_Suite_Input_Validator::validate('churchtools_tenant', 'gemeinde-test');
        $this->assertTrue($result['valid']);
        $this->assertEquals('gemeinde-test', $result['sanitized']);
        
        // Invalid characters
        $result = Repro_CT_Suite_Input_Validator::validate('churchtools_tenant', 'gemeinde@test');
        $this->assertFalse($result['valid']);
        
        // Too short
        $result = Repro_CT_Suite_Input_Validator::validate('churchtools_tenant', 'ab');
        $this->assertFalse($result['valid']);
        
        // Empty (required field)
        $result = Repro_CT_Suite_Input_Validator::validate('churchtools_tenant', '');
        $this->assertFalse($result['valid']);
    }
    
    /**
     * Test Email or String Validation
     */
    public function test_email_or_string_validation() {
        // Valid email
        $result = Repro_CT_Suite_Input_Validator::validate('churchtools_username', 'user@example.com');
        $this->assertTrue($result['valid']);
        $this->assertEquals('user@example.com', $result['sanitized']);
        
        // Valid username
        $result = Repro_CT_Suite_Input_Validator::validate('churchtools_username', 'username123');
        $this->assertTrue($result['valid']);
        $this->assertEquals('username123', $result['sanitized']);
        
        // Invalid email
        $result = Repro_CT_Suite_Input_Validator::validate('churchtools_username', 'invalid@email');
        $this->assertFalse($result['valid']);
    }
    
    /**
     * Test Integer Validation
     */
    public function test_integer_validation() {
        // Valid integer
        $result = Repro_CT_Suite_Input_Validator::validate('shortcode_limit', '15');
        $this->assertTrue($result['valid']);
        $this->assertEquals(15, $result['sanitized']);
        
        // Below minimum
        $result = Repro_CT_Suite_Input_Validator::validate('shortcode_limit', '0');
        $this->assertFalse($result['valid']);
        
        // Above maximum
        $result = Repro_CT_Suite_Input_Validator::validate('shortcode_limit', '150');
        $this->assertFalse($result['valid']);
        
        // Non-numeric
        $result = Repro_CT_Suite_Input_Validator::validate('shortcode_limit', 'abc');
        $this->assertFalse($result['valid']);
    }
    
    /**
     * Test Integer Array Validation
     */
    public function test_integer_array_validation() {
        // Valid comma-separated string
        $result = Repro_CT_Suite_Input_Validator::validate('calendar_ids', '1,2,3');
        $this->assertTrue($result['valid']);
        $this->assertEquals([1, 2, 3], $result['sanitized']);
        
        // Valid array
        $result = Repro_CT_Suite_Input_Validator::validate('calendar_ids', [4, 5, 6]);
        $this->assertTrue($result['valid']);
        $this->assertEquals([4, 5, 6], $result['sanitized']);
        
        // With invalid values
        $result = Repro_CT_Suite_Input_Validator::validate('calendar_ids', '1,abc,3');
        $this->assertFalse($result['valid']);
        
        // Empty values should be filtered
        $result = Repro_CT_Suite_Input_Validator::validate('calendar_ids', '1,,3,');
        $this->assertTrue($result['valid']);
        $this->assertEquals([1, 3], $result['sanitized']);
    }
    
    /**
     * Test Date Validation
     */
    public function test_date_validation() {
        // Valid date
        $result = Repro_CT_Suite_Input_Validator::validate('sync_from_date', '2024-01-15');
        $this->assertTrue($result['valid']);
        $this->assertEquals('2024-01-15', $result['sanitized']);
        
        // Invalid format
        $result = Repro_CT_Suite_Input_Validator::validate('sync_from_date', '15.01.2024');
        $this->assertFalse($result['valid']);
        
        // Invalid date
        $result = Repro_CT_Suite_Input_Validator::validate('sync_from_date', '2024-13-45');
        $this->assertFalse($result['valid']);
    }
    
    /**
     * Test Enum Validation
     */
    public function test_enum_validation() {
        // Valid value
        $result = Repro_CT_Suite_Input_Validator::validate('shortcode_view', 'cards');
        $this->assertTrue($result['valid']);
        $this->assertEquals('cards', $result['sanitized']);
        
        // Invalid value should use default
        $result = Repro_CT_Suite_Input_Validator::validate('shortcode_view', 'invalid');
        $this->assertFalse($result['valid']);
        $this->assertEquals('cards', $result['sanitized']); // Default value
    }
    
    /**
     * Test Boolean Validation
     */
    public function test_boolean_validation() {
        $test_cases = [
            [true, true, true],
            [false, true, false],
            ['true', true, true],
            ['false', true, false],
            ['1', true, true],
            ['0', true, false],
            ['yes', true, true],
            ['no', true, false],
            [1, true, true],
            [0, true, false],
            ['invalid', false, false]
        ];
        
        foreach ($test_cases as [$input, $expected_valid, $expected_value]) {
            $result = Repro_CT_Suite_Input_Validator::validate('test_bool', $input, ['type' => 'boolean']);
            $this->assertEquals($expected_valid, $result['valid'], "Failed for input: " . var_export($input, true));
            if ($expected_valid) {
                $this->assertEquals($expected_value, $result['sanitized']);
            }
        }
    }
    
    /**
     * Test Array Validation
     */
    public function test_array_validation() {
        $inputs = [
            'churchtools_tenant' => 'test-gemeinde',
            'churchtools_username' => 'user@example.com',
            'shortcode_limit' => '25',
            'shortcode_view' => 'list'
        ];
        
        $result = Repro_CT_Suite_Input_Validator::validate_array($inputs);
        
        $this->assertTrue($result['valid']);
        $this->assertEquals('test-gemeinde', $result['sanitized']['churchtools_tenant']);
        $this->assertEquals('user@example.com', $result['sanitized']['churchtools_username']);
        $this->assertEquals(25, $result['sanitized']['shortcode_limit']);
        $this->assertEquals('list', $result['sanitized']['shortcode_view']);
    }
    
    /**
     * Test Array Validation with Errors
     */
    public function test_array_validation_with_errors() {
        $inputs = [
            'churchtools_tenant' => 'ab', // Too short
            'shortcode_limit' => '200',    // Too high
            'shortcode_view' => 'invalid'  // Invalid enum
        ];
        
        $result = Repro_CT_Suite_Input_Validator::validate_array($inputs);
        
        $this->assertFalse($result['valid']);
        $this->assertNotEmpty($result['errors']);
        $this->assertGreaterThanOrEqual(3, count($result['errors'])); // At least 3 errors
    }
    
    /**
     * Test Custom Rules
     */
    public function test_custom_rules() {
        $custom_rules = [
            'type' => 'string',
            'min_length' => 5,
            'max_length' => 15,
            'pattern' => '/^[A-Z]+$/'
        ];
        
        // Valid
        $result = Repro_CT_Suite_Input_Validator::validate('custom_field', 'HELLO', $custom_rules);
        $this->assertTrue($result['valid']);
        
        // Too short
        $result = Repro_CT_Suite_Input_Validator::validate('custom_field', 'HI', $custom_rules);
        $this->assertFalse($result['valid']);
        
        // Wrong pattern
        $result = Repro_CT_Suite_Input_Validator::validate('custom_field', 'hello', $custom_rules);
        $this->assertFalse($result['valid']);
    }
    
    /**
     * Test Required Fields
     */
    public function test_required_fields() {
        // Required field empty
        $result = Repro_CT_Suite_Input_Validator::validate('churchtools_tenant', '');
        $this->assertFalse($result['valid']);
        $this->assertContains('erforderlich', implode(' ', $result['errors']));
        
        // Non-required field empty (should get default if available)
        $result = Repro_CT_Suite_Input_Validator::validate('shortcode_view', '');
        $this->assertTrue($result['valid']);
        $this->assertEquals('cards', $result['sanitized']); // Default value
    }
    
    /**
     * Test Error Sanitization
     */
    public function test_error_sanitization() {
        $errors = [
            'Normal error message',
            '<script>alert("xss")</script>Error with HTML',
            'Error with "quotes" and \'apostrophes\''
        ];
        
        $sanitized = Repro_CT_Suite_Input_Validator::sanitize_errors($errors);
        
        $this->assertEquals('Normal error message', $sanitized[0]);
        $this->assertStringNotContainsString('<script>', $sanitized[1]);
        $this->assertStringContainsString('&lt;script&gt;', $sanitized[1]);
        $this->assertStringContainsString('&quot;', $sanitized[2]);
    }
    
    /**
     * Test Get Field Rules
     */
    public function test_get_field_rules() {
        $rules = Repro_CT_Suite_Input_Validator::get_field_rules('churchtools_tenant');
        $this->assertIsArray($rules);
        $this->assertEquals('string', $rules['type']);
        $this->assertEquals(3, $rules['min_length']);
        
        $non_existent = Repro_CT_Suite_Input_Validator::get_field_rules('non_existent_field');
        $this->assertNull($non_existent);
    }
    
    /**
     * Test XSS Protection in Input
     */
    public function test_xss_protection() {
        $malicious_inputs = [
            '<script>alert("xss")</script>',
            'javascript:alert(1)',
            '<img src="x" onerror="alert(1)">',
            '"><script>alert(1)</script>',
            '\';DROP TABLE users;--'
        ];
        
        foreach ($malicious_inputs as $input) {
            $result = Repro_CT_Suite_Input_Validator::validate('preset_name', $input);
            
            // Should sanitize the input
            $this->assertStringNotContainsString('<script>', $result['sanitized']);
            $this->assertStringNotContainsString('javascript:', $result['sanitized']);
            $this->assertStringNotContainsString('onerror=', $result['sanitized']);
            $this->assertStringNotContainsString('DROP TABLE', $result['sanitized']);
        }
    }
    
    /**
     * Test Performance with Large Arrays
     */
    public function test_performance_large_arrays() {
        $start_time = microtime(true);
        
        // Test mit 1000 Calendar IDs
        $large_array = range(1, 1000);
        $result = Repro_CT_Suite_Input_Validator::validate('calendar_ids', $large_array);
        
        $end_time = microtime(true);
        $execution_time = $end_time - $start_time;
        
        // Sollte in unter 1 Sekunde fertig sein
        $this->assertLessThan(1.0, $execution_time, 'Performance issue with large arrays');
        
        // Array sollte korrekt validiert werden
        $this->assertTrue($result['valid']);
        $this->assertEquals(1000, count($result['sanitized']));
    }
}