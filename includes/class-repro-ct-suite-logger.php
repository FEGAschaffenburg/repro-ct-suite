<?php
/**
 * Zentrale Logging-Klasse
 *
 * Schreibt Debug-Informationen ins WordPress Debug-Log.
 * Funktioniert unabhängig von WP_DEBUG - aktiviert sich selbst wenn nötig.
 *
 * @package    Repro_CT_Suite
 * @subpackage Repro_CT_Suite/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Repro_CT_Suite_Logger {

	/**
	 * Debug-Logging
	 * 
	 * Schreibt Debug-Informationen ins WordPress Debug-Log (wp-content/debug.log).
	 * Funktioniert unabhängig von WP_DEBUG - aktiviert Logging wenn nötig.
	 *
	 * @param string $message Die Log-Nachricht
	 * @param string $level   Log-Level: 'info', 'error', 'warning', 'success'
	 */
	public static function log( $message, $level = 'info' ) {
		// Log-Datei: WordPress debug.log
		$log_file = WP_CONTENT_DIR . '/debug.log';
		
		// Prefix mit Icons für bessere Lesbarkeit
		$prefix = '[REPRO CT-SUITE] ';
		switch ( $level ) {
			case 'error':
				$prefix .= '❌ ERROR: ';
				break;
			case 'warning':
				$prefix .= '⚠️  WARNING: ';
				break;
			case 'success':
				$prefix .= '✅ SUCCESS: ';
				break;
			case 'info':
			default:
				$prefix .= 'ℹ️  INFO: ';
		}
		
		// Timestamp mit Millisekunden
		$microtime = microtime( true );
		$datetime = new DateTime();
		$datetime->setTimestamp( (int) $microtime );
		$milliseconds = sprintf( '%03d', ( $microtime - floor( $microtime ) ) * 1000 );
		$timestamp = $datetime->format( 'Y-m-d H:i:s' ) . '.' . $milliseconds;
		
		// Log-Eintrag formatieren
		$entry = '[' . $timestamp . '] ' . $prefix . $message . PHP_EOL;

		// Direkt in die Datei schreiben (funktioniert immer, unabhängig von WP_DEBUG)
		@file_put_contents( $log_file, $entry, FILE_APPEND | LOCK_EX );

		// Optional: auch in den System-Logger (syslog) schreiben, falls aktiviert
		// Aktivierung über Option 'repro_ct_suite_syslog' (bool) oder Konstante REPRO_CT_SUITE_SYSLOG
		$use_syslog = false;
		if ( defined( 'REPRO_CT_SUITE_SYSLOG' ) ) {
			$use_syslog = (bool) REPRO_CT_SUITE_SYSLOG;
		} else {
			$use_syslog = (bool) get_option( 'repro_ct_suite_syslog', false );
		}
		if ( $use_syslog ) {
			// map level to syslog priority
			switch ( $level ) {
				case 'error':
					$prio = LOG_ERR;
					break;
				case 'warning':
					$prio = LOG_WARNING;
					break;
				case 'success':
					$prio = LOG_INFO;
					break;
				case 'info':
				default:
					$prio = LOG_INFO;
			}

			// openlog/syslog are safe to call multiple times; include plugin name
			if ( function_exists( 'openlog' ) && function_exists( 'syslog' ) ) {
				@openlog( 'repro-ct-suite', LOG_PID, LOG_USER );
				@syslog( $prio, trim( strip_tags( $prefix . $message ) ) );
				@closelog();
			}
		}
	}

	/**
	 * Separator-Linie ins Log schreiben
	 *
	 * @param string $char  Zeichen für die Linie (Standard: -)
	 * @param int    $length Länge der Linie (Standard: 60)
	 */
	public static function separator( $char = '-', $length = 60 ) {
		self::log( str_repeat( $char, $length ) );
	}

	/**
	 * Überschrift ins Log schreiben
	 *
	 * @param string $title Die Überschrift
	 * @param string $level Log-Level
	 */
	public static function header( $title, $level = 'info' ) {
		self::separator( '=' );
		self::log( strtoupper( $title ), $level );
		self::separator( '=' );
	}

	/**
	 * Array oder Objekt als formatierten String ins Log schreiben
	 *
	 * @param mixed  $data  Die zu loggenden Daten
	 * @param string $label Optional: Label für die Daten
	 * @param string $level Log-Level
	 */
	public static function dump( $data, $label = null, $level = 'info' ) {
		if ( $label ) {
			self::log( $label . ':', $level );
		}
		
		$formatted = print_r( $data, true );
		$lines = explode( "\n", $formatted );
		
		foreach ( $lines as $line ) {
			if ( ! empty( trim( $line ) ) ) {
				self::log( '  ' . $line, $level );
			}
		}
	}
}
