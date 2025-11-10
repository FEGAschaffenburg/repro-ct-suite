<?php
/**
 * Simple encryption/decryption helper for storing sensitive data (e.g., passwords)
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Repro_CT_Suite_Crypto {
	private static function key() {
		$seed = AUTH_KEY . SECURE_AUTH_KEY;
		return hash( 'sha256', $seed, true );
	}

	private static function iv() {
		return substr( hash( 'sha256', AUTH_SALT . SECURE_AUTH_SALT, true ), 0, 16 );
	}

	public static function encrypt( $plain ) {
		if ( $plain === '' || $plain === null ) {
			return '';
		}
		$encrypted = openssl_encrypt( (string) $plain, 'AES-256-CBC', self::key(), OPENSSL_RAW_DATA, self::iv() );
		return base64_encode( $encrypted );
	}

	public static function decrypt( $encoded ) {
		if ( empty( $encoded ) ) {
			return '';
		}
		$data = base64_decode( (string) $encoded, true );
		if ( $data === false ) {
			return '';
		}
		$decrypted = openssl_decrypt( $data, 'AES-256-CBC', self::key(), OPENSSL_RAW_DATA, self::iv() );
		return $decrypted !== false ? $decrypted : '';
	}
}
