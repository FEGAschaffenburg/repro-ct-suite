<?php
/**
 * Admin Frontend Display Page
 *
 * @package    Repro_CT_Suite
 * @subpackage Repro_CT_Suite/admin/views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Prüfe Berechtigungen
if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( __( 'Sie haben keine Berechtigung, auf diese Seite zuzugreifen.', 'repro-ct-suite' ) );
}
?>

<div class="wrap repro-ct-suite-admin">
	<h1><?php esc_html_e( 'Anzeige im Frontend', 'repro-ct-suite' ); ?></h1>
	
	<p class="description">
		<?php esc_html_e( 'Konfigurieren Sie, wie Termine auf Ihrer Website angezeigt werden.', 'repro-ct-suite' ); ?>
	</p>

	<!-- Tab-Navigation -->
	<h2 class="nav-tab-wrapper">
		<a href="#shortcode-generator" class="nav-tab nav-tab-active" data-tab="shortcode-generator">
			<?php esc_html_e( 'Shortcode Generator', 'repro-ct-suite' ); ?>
		</a>
		<a href="#template-varianten" class="nav-tab" data-tab="template-varianten">
			<?php esc_html_e( 'Template-Varianten', 'repro-ct-suite' ); ?>
		</a>
		<a href="#styling" class="nav-tab" data-tab="styling">
			<?php esc_html_e( 'Styling', 'repro-ct-suite' ); ?>
		</a>
		<a href="#vorschau" class="nav-tab" data-tab="vorschau">
			<?php esc_html_e( 'Vorschau', 'repro-ct-suite' ); ?>
		</a>
	</h2>

	<!-- Tab: Shortcode Generator -->
	<div id="tab-shortcode-generator" class="repro-tab-content active">
		<?php include 'tabs/tab-frontend-shortcode-generator.php'; ?>
	</div>

	<!-- Tab: Template-Varianten -->
	<div id="tab-template-varianten" class="repro-tab-content" style="display: none;">
		<?php include 'tabs/tab-frontend-templates.php'; ?>
	</div>

	<!-- Tab: Styling -->
	<div id="tab-styling" class="repro-tab-content" style="display: none;">
		<?php include 'tabs/tab-frontend-styling.php'; ?>
	</div>

	<!-- Tab: Vorschau -->
	<div id="tab-vorschau" class="repro-tab-content" style="display: none;">
		<?php include 'tabs/tab-frontend-preview.php'; ?>
	</div>
</div>

<style>
.repro-tab-content {
	background: #fff;
	padding: 20px;
	border: 1px solid #ccd0d4;
	border-top: none;
	margin-bottom: 20px;
}

.repro-tab-content.active {
	display: block !important;
}
</style>

<script>
jQuery(document).ready(function($) {
	// Tab-Switching
	$('.nav-tab').on('click', function(e) {
		e.preventDefault();
		
		var tabId = $(this).data('tab');
		
		// Tabs umschalten
		$('.nav-tab').removeClass('nav-tab-active');
		$(this).addClass('nav-tab-active');
		
		// Content umschalten
		$('.repro-tab-content').removeClass('active').hide();
		$('#tab-' + tabId).addClass('active').show();
		
		// URL aktualisieren (ohne Reload)
		if (history.pushState) {
			history.pushState(null, null, '#' + tabId);
		}
	});
	
	// Tab aus URL-Hash aktivieren
	if (window.location.hash) {
		var hash = window.location.hash.substring(1);
		$('.nav-tab[data-tab="' + hash + '"]').trigger('click');
	}
});
</script>
