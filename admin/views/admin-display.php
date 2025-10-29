<?php
/**
 * Provide an admin area view for the plugin
 *
 * @package    Repro_CT_Suite
 * @subpackage Repro_CT_Suite/admin/views
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap repro-ct-suite-admin-wrapper">
	<div class="repro-ct-suite-header">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<p><?php esc_html_e( 'Welcome to Repro CT-Suite settings.', 'repro-ct-suite' ); ?></p>
	</div>

	<div class="repro-ct-suite-content">
		<form method="post" action="options.php">
			<?php
			// Add settings fields here
			settings_fields( 'repro_ct_suite_options' );
			do_settings_sections( 'repro-ct-suite' );
			submit_button();
			?>
		</form>
	</div>
</div>
