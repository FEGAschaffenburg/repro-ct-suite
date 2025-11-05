<?php
/**
 * Frontend Tab: Template-Varianten
 *
 * @package Repro_CT_Suite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<h2><?php esc_html_e( 'Template-Varianten', 'repro-ct-suite' ); ?></h2>
<p class="description">
	<?php esc_html_e( 'Passen Sie die vorhandenen Templates an oder erstellen Sie eigene Varianten.', 'repro-ct-suite' ); ?>
</p>

<div class="template-variants-grid">
	<!-- List Simple -->
	<div class="template-card">
		<div class="template-preview">
			<img src="<?php echo esc_url( plugin_dir_url( dirname( dirname( __FILE__ ) ) ) . 'assets/template-list-simple.png' ); ?>" alt="List Simple">
		</div>
		<h3><?php esc_html_e( 'Liste (Einfach)', 'repro-ct-suite' ); ?></h3>
		<p><?php esc_html_e( 'Kompakte Listenansicht mit Emojis und Metadaten.', 'repro-ct-suite' ); ?></p>
		<p><strong><?php esc_html_e( 'Shortcode:', 'repro-ct-suite' ); ?></strong> <code>[rcts_events view="list"]</code></p>
		<button type="button" class="button" onclick="window.open('<?php echo esc_url( plugin_dir_url( dirname( dirname( dirname( __FILE__ ) ) ) ) . 'templates/events/list-simple.php' ); ?>', '_blank')">
			<?php esc_html_e( 'Template ansehen', 'repro-ct-suite' ); ?>
		</button>
	</div>

	<!-- List Grouped -->
	<div class="template-card">
		<div class="template-preview">
			<img src="<?php echo esc_url( plugin_dir_url( dirname( dirname( __FILE__ ) ) ) . 'assets/template-list-grouped.png' ); ?>" alt="List Grouped">
		</div>
		<h3><?php esc_html_e( 'Liste (Gruppiert)', 'repro-ct-suite' ); ?></h3>
		<p><?php esc_html_e( 'Termine nach Datum gruppiert mit Uhrzeit links.', 'repro-ct-suite' ); ?></p>
		<p><strong><?php esc_html_e( 'Shortcode:', 'repro-ct-suite' ); ?></strong> <code>[rcts_events view="list-grouped"]</code></p>
		<button type="button" class="button" onclick="window.open('<?php echo esc_url( plugin_dir_url( dirname( dirname( dirname( __FILE__ ) ) ) ) . 'templates/events/list-grouped.php' ); ?>', '_blank')">
			<?php esc_html_e( 'Template ansehen', 'repro-ct-suite' ); ?>
		</button>
	</div>

	<!-- Cards -->
	<div class="template-card">
		<div class="template-preview">
			<img src="<?php echo esc_url( plugin_dir_url( dirname( dirname( __FILE__ ) ) ) . 'assets/template-cards.png' ); ?>" alt="Cards">
		</div>
		<h3><?php esc_html_e( 'Kacheln (Grid)', 'repro-ct-suite' ); ?></h3>
		<p><?php esc_html_e( 'Moderne Kachel-Ansicht im Grid-Layout.', 'repro-ct-suite' ); ?></p>
		<p><strong><?php esc_html_e( 'Shortcode:', 'repro-ct-suite' ); ?></strong> <code>[rcts_events view="cards"]</code></p>
		<button type="button" class="button" onclick="window.open('<?php echo esc_url( plugin_dir_url( dirname( dirname( dirname( __FILE__ ) ) ) ) . 'templates/events/cards.php' ); ?>', '_blank')">
			<?php esc_html_e( 'Template ansehen', 'repro-ct-suite' ); ?>
		</button>
	</div>
</div>

<div class="custom-template-info">
	<h3><?php esc_html_e( 'Eigene Templates erstellen', 'repro-ct-suite' ); ?></h3>
	<p><?php esc_html_e( 'Sie können die Templates in Ihrem Theme überschreiben:', 'repro-ct-suite' ); ?></p>
	<ol>
		<li><?php esc_html_e( 'Erstellen Sie folgenden Ordner in Ihrem Theme:', 'repro-ct-suite' ); ?> <code>/wp-content/themes/mein-theme/repro-ct-suite/events/</code></li>
		<li><?php esc_html_e( 'Kopieren Sie ein Template aus dem Plugin-Ordner:', 'repro-ct-suite' ); ?> <code>/wp-content/plugins/repro-ct-suite/templates/events/</code></li>
		<li><?php esc_html_e( 'Passen Sie das Template nach Ihren Wünschen an', 'repro-ct-suite' ); ?></li>
	</ol>
	<p><strong><?php esc_html_e( 'Verfügbare Template-Variablen:', 'repro-ct-suite' ); ?></strong></p>
	<ul>
		<li><code>$events</code> - <?php esc_html_e( 'Array aller Events', 'repro-ct-suite' ); ?></li>
		<li><code>$show_fields</code> - <?php esc_html_e( 'Array der anzuzeigenden Felder', 'repro-ct-suite' ); ?></li>
		<li><code>$event->title</code> - <?php esc_html_e( 'Titel des Events', 'repro-ct-suite' ); ?></li>
		<li><code>$event->time_formatted</code> - <?php esc_html_e( 'Formatierte Uhrzeit (mit "Uhr" oder AM/PM)', 'repro-ct-suite' ); ?></li>
		<li><code>$event->date_formatted</code> - <?php esc_html_e( 'Formatiertes Datum', 'repro-ct-suite' ); ?></li>
		<li><code>$event->location_name</code> - <?php esc_html_e( 'Ortsname', 'repro-ct-suite' ); ?></li>
		<li><code>$event->calendar_name</code> - <?php esc_html_e( 'Kalendername', 'repro-ct-suite' ); ?></li>
		<li><code>$event->calendar_color</code> - <?php esc_html_e( 'Kalenderfarbe', 'repro-ct-suite' ); ?></li>
	</ul>
</div>

<style>
.template-variants-grid {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
	gap: 20px;
	margin: 30px 0;
}

.template-card {
	background: #fff;
	border: 1px solid #ddd;
	padding: 15px;
	border-radius: 4px;
}

.template-preview {
	width: 100%;
	height: 180px;
	background: #f0f0f1;
	border: 1px solid #ddd;
	border-radius: 4px;
	margin-bottom: 15px;
	display: flex;
	align-items: center;
	justify-content: center;
	overflow: hidden;
}

.template-preview img {
	max-width: 100%;
	max-height: 100%;
	object-fit: contain;
}

.template-card h3 {
	margin: 0 0 10px;
	font-size: 16px;
}

.template-card p {
	margin: 8px 0;
	font-size: 13px;
}

.template-card code {
	font-size: 12px;
}

.custom-template-info {
	background: #f9f9f9;
	padding: 20px;
	border-left: 4px solid #2271b1;
	margin-top: 30px;
}

.custom-template-info h3 {
	margin-top: 0;
}

.custom-template-info code {
	background: #fff;
	padding: 2px 6px;
	border: 1px solid #ddd;
	border-radius: 3px;
}

.custom-template-info ul,
.custom-template-info ol {
	margin-left: 20px;
}

.custom-template-info li {
	margin-bottom: 8px;
}
</style>
