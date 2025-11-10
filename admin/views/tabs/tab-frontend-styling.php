<?php
/**
 * Frontend Tab: Styling
 *
 * @package Repro_CT_Suite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<h2><?php esc_html_e( 'Styling-Optionen', 'repro-ct-suite' ); ?></h2>
<p class="description">
	<?php esc_html_e( 'Passen Sie das Aussehen der Frontend-Anzeige an.', 'repro-ct-suite' ); ?>
</p>

<div class="styling-info">
	<h3><?php esc_html_e( 'CSS-Klassen', 'repro-ct-suite' ); ?></h3>
	<p><?php esc_html_e( 'Das Plugin verwendet folgende CSS-Klassen, die Sie in Ihrem Theme überschreiben können:', 'repro-ct-suite' ); ?></p>
	
	<h4><?php esc_html_e( 'Container-Klassen', 'repro-ct-suite' ); ?></h4>
	<ul>
		<li><code>.rcts-events</code> - <?php esc_html_e( 'Haupt-Container', 'repro-ct-suite' ); ?></li>
		<li><code>.rcts-events-list-simple</code> - <?php esc_html_e( 'Liste (einfach)', 'repro-ct-suite' ); ?></li>
		<li><code>.rcts-events-list-grouped</code> - <?php esc_html_e( 'Liste (gruppiert)', 'repro-ct-suite' ); ?></li>
		<li><code>.rcts-events-cards</code> - <?php esc_html_e( 'Kacheln (Grid)', 'repro-ct-suite' ); ?></li>
	</ul>

	<h4><?php esc_html_e( 'Event-Klassen', 'repro-ct-suite' ); ?></h4>
	<ul>
		<li><code>.rcts-event-item</code> - <?php esc_html_e( 'Einzelner Termin', 'repro-ct-suite' ); ?></li>
		<li><code>.rcts-event-title</code> - <?php esc_html_e( 'Titel', 'repro-ct-suite' ); ?></li>
		<li><code>.rcts-event-time</code> - <?php esc_html_e( 'Uhrzeit', 'repro-ct-suite' ); ?></li>
		<li><code>.rcts-event-location</code> - <?php esc_html_e( 'Ort', 'repro-ct-suite' ); ?></li>
		<li><code>.rcts-event-calendar</code> - <?php esc_html_e( 'Kalender-Name', 'repro-ct-suite' ); ?></li>
	</ul>

	<h3><?php esc_html_e( 'Custom CSS hinzufügen', 'repro-ct-suite' ); ?></h3>
	<p><?php esc_html_e( 'Fügen Sie eigenes CSS über:', 'repro-ct-suite' ); ?></p>
	<ul>
		<li><?php esc_html_e( 'WordPress Customizer → Zusätzliches CSS', 'repro-ct-suite' ); ?></li>
		<li><?php esc_html_e( 'Ihr Theme-CSS (style.css)', 'repro-ct-suite' ); ?></li>
		<li><?php esc_html_e( 'Ein Custom CSS Plugin', 'repro-ct-suite' ); ?></li>
	</ul>

	<h3><?php esc_html_e( 'Beispiel: Farben anpassen', 'repro-ct-suite' ); ?></h3>
	<pre><code>/* Primärfarbe ändern */
.rcts-events-cards .rcts-card {
    border-color: #your-color;
}

/* Hover-Effekt anpassen */
.rcts-events-cards .rcts-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    transform: translateY(-2px);
}

/* Kalender-Badge anpassen */
.rcts-calendar-badge {
    background: #your-color;
    color: white;
}</code></pre>
</div>

<style>
.styling-info {
	background: #fff;
	padding: 20px;
	border: 1px solid #ddd;
	border-radius: 4px;
}

.styling-info h3 {
	margin-top: 30px;
	margin-bottom: 15px;
}

.styling-info h3:first-child {
	margin-top: 0;
}

.styling-info h4 {
	margin-top: 20px;
	margin-bottom: 10px;
	font-size: 14px;
}

.styling-info ul {
	margin-left: 20px;
}

.styling-info li {
	margin-bottom: 8px;
}

.styling-info code {
	background: #f5f5f5;
	padding: 2px 6px;
	border-radius: 3px;
	font-size: 13px;
}

.styling-info pre {
	background: #282c34;
	color: #abb2bf;
	padding: 15px;
	border-radius: 4px;
	overflow-x: auto;
}

.styling-info pre code {
	background: transparent;
	color: inherit;
	padding: 0;
}
</style>
