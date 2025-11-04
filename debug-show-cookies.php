<?php
/**
 * Debug-Script: Zeigt gespeicherte ChurchTools Session-Cookies
 * 
 * Aufruf: https://DEINE-DOMAIN.de/wp-content/plugins/repro-ct-suite/debug-show-cookies.php
 * WICHTIG: Nach dem Test SOFORT löschen (Sicherheitsrisiko!)
 */

// WordPress laden
require_once dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/wp-load.php';

// Nur für Admins
if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( 'Keine Berechtigung' );
}

header( 'Content-Type: text/plain; charset=utf-8' );

echo "=== ChurchTools Session-Cookies ===\n\n";

$cookies = get_option( 'repro_ct_suite_ct_cookies', array() );

if ( empty( $cookies ) ) {
	echo "❌ Keine Cookies gespeichert.\n";
	echo "Das Plugin hat sich noch nicht eingeloggt.\n";
	exit;
}

echo "✅ Cookies gefunden:\n\n";

foreach ( $cookies as $name => $value ) {
	echo "Name:  {$name}\n";
	echo "Value: {$value}\n";
	echo str_repeat( '-', 80 ) . "\n";
}

echo "\n=== cURL-Header ===\n\n";
echo "-H 'Cookie: ";
$cookie_parts = array();
foreach ( $cookies as $name => $value ) {
	$cookie_parts[] = $name . '=' . $value;
}
echo implode( '; ', $cookie_parts );
echo "'\n\n";

echo "=== Kompletter cURL-Befehl ===\n\n";
echo "curl -X 'GET' \\\n";
echo "  'https://feg-ab.church.tools/api/calendars/2/appointments?from=2025-10-19&to=2025-10-27' \\\n";
echo "  -H 'accept: application/json' \\\n";
echo "  -H 'Content-Type: application/json' \\\n";
echo "  -H 'Cookie: " . implode( '; ', $cookie_parts ) . "'\n\n";

echo "⚠️ WICHTIG: Diese Datei nach dem Test sofort löschen!\n";
