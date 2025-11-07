<?php
/**
 * Test case for Repro CT-Suite Crypto class
 *
 * @package Repro_CT_Suite
 * @subpackage Tests
 */

class Test_Repro_CT_Suite_Crypto extends WP_UnitTestCase {

	/**
	 * Test encrypt and decrypt functionality
	 */
	public function test_encrypt_decrypt() {
		$original_text = 'test_password_123';
		$encrypted = Repro_CT_Suite_Crypto::encrypt( $original_text );
		$decrypted = Repro_CT_Suite_Crypto::decrypt( $encrypted );

		$this->assertNotEmpty( $encrypted );
		$this->assertNotEquals( $original_text, $encrypted );
		$this->assertEquals( $original_text, $decrypted );
	}

	/**
	 * Test encryption of empty string
	 */
	public function test_encrypt_empty_string() {
		$encrypted = Repro_CT_Suite_Crypto::encrypt( '' );
		$decrypted = Repro_CT_Suite_Crypto::decrypt( $encrypted );

		$this->assertEquals( '', $decrypted );
	}

	/**
	 * Test decryption with invalid data
	 */
	public function test_decrypt_invalid_data() {
		$result = Repro_CT_Suite_Crypto::decrypt( 'invalid_encrypted_data' );
		$this->assertFalse( $result );
	}

	/**
	 * Test that different inputs produce different encrypted outputs
	 */
	public function test_encrypt_different_inputs() {
		$text1 = 'password1';
		$text2 = 'password2';

		$encrypted1 = Repro_CT_Suite_Crypto::encrypt( $text1 );
		$encrypted2 = Repro_CT_Suite_Crypto::encrypt( $text2 );

		$this->assertNotEquals( $encrypted1, $encrypted2 );
	}

	/**
	 * Test that same input produces different encrypted output (due to random IV)
	 */
	public function test_encrypt_same_input_different_output() {
		$text = 'same_password';

		$encrypted1 = Repro_CT_Suite_Crypto::encrypt( $text );
		$encrypted2 = Repro_CT_Suite_Crypto::encrypt( $text );

		// Should be different due to random IV
		$this->assertNotEquals( $encrypted1, $encrypted2 );

		// But both should decrypt to same original
		$this->assertEquals( $text, Repro_CT_Suite_Crypto::decrypt( $encrypted1 ) );
		$this->assertEquals( $text, Repro_CT_Suite_Crypto::decrypt( $encrypted2 ) );
	}
}