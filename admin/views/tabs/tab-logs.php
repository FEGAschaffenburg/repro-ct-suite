<?php
/**
 * Logs Tab Template
 *
 * Zeigt Synchronisations-Protokoll und Debug-Informationen.
 *
 * @package    Repro_CT_Suite
 * @subpackage Repro_CT_Suite/admin/views/tabs
 * @since      0.1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<?php
// Zeigt den Inhalt der plugin-eigenen Debug-Log-Datei an (repro-ct-suite-debug.log)
$log_file = WP_CONTENT_DIR . '/repro-ct-suite-debug.log';
$log_exists = file_exists( $log_file );
$log_size = $log_exists ? filesize( $log_file ) : 0;
$log_lines = 0;
if ( $log_exists && $log_size > 0 ) {
	$log_lines = count( file( $log_file ) );
}
?>

<div class="repro-ct-suite-card repro-ct-suite-mt-20">
	<div class="repro-ct-suite-card-header">
		<span class="dashicons dashicons-media-text"></span>
		<h3><?php esc_html_e( 'Debug-Log', 'repro-ct-suite' ); ?></h3>
	</div>
	<div class="repro-ct-suite-card-body">
		<?php if ( $log_exists && $log_size > 0 ) : ?>
			<div class="repro-ct-suite-flex" style="justify-content: space-between; align-items: center; margin-bottom: 15px;">
				<div>
					<strong><?php esc_html_e( 'Log-Datei:', 'repro-ct-suite' ); ?></strong>
					<code style="margin-left: 10px;"><?php echo esc_html( basename( $log_file ) ); ?></code>
					<br>
					<span class="description">
						<?php
						printf(
							/* translators: 1: number of lines, 2: file size in KB */
							esc_html__( '%1$s Zeilen • %2$s KB', 'repro-ct-suite' ),
							number_format_i18n( $log_lines ),
							number_format_i18n( round( $log_size / 1024, 2 ) )
						);
						?>
					</span>
				</div>
				<div>
					<button
						id="repro-ct-suite-copy-log"
						class="repro-ct-suite-btn repro-ct-suite-btn-sm repro-ct-suite-btn-secondary"
						title="<?php esc_attr_e( 'Logs in die Zwischenablage kopieren', 'repro-ct-suite' ); ?>"
					>
						<span class="dashicons dashicons-clipboard"></span>
						<?php esc_html_e( 'Logs kopieren', 'repro-ct-suite' ); ?>
					</button>
					<button
						id="repro-ct-suite-refresh-log"
						class="repro-ct-suite-btn repro-ct-suite-btn-sm repro-ct-suite-btn-secondary"
					>
						<span class="dashicons dashicons-update"></span>
						<?php esc_html_e( 'Aktualisieren', 'repro-ct-suite' ); ?>
					</button>
					<button
						id="repro-ct-suite-clear-log"
						class="repro-ct-suite-btn repro-ct-suite-btn-sm repro-ct-suite-btn-danger"
						data-nonce="<?php echo esc_attr( wp_create_nonce( 'repro_ct_suite_admin' ) ); ?>"
					>
						<span class="dashicons dashicons-trash"></span>
						<?php esc_html_e( 'Log leeren', 'repro-ct-suite' ); ?>
					</button>
				</div>
			</div>

			<div style="background: #1e1e1e; color: #d4d4d4; padding: 15px; border-radius: 4px; font-family: 'Courier New', monospace; font-size: 12px; max-height: 400px; overflow-y: auto;" id="repro-ct-suite-log-viewer">
				<?php
				// Letzten 100 Zeilen des Logs anzeigen
				$log_content = file( $log_file );
				$log_content = array_slice( $log_content, -100 );

				foreach ( $log_content as $line ) {
					$line = esc_html( $line );

					// Einfaches Syntax-Highlighting nach Level
					if ( strpos( $line, 'ERROR' ) !== false || strpos( $line, '❌' ) !== false ) {
						echo '<div style="color: #f48771;">' . $line . '</div>';
					} elseif ( strpos( $line, 'WARNING' ) !== false || strpos( $line, '⚠️' ) !== false ) {
						echo '<div style="color: #dcdcaa;">' . $line . '</div>';
					} elseif ( strpos( $line, 'SUCCESS' ) !== false || strpos( $line, '✅' ) !== false ) {
						echo '<div style="color: #4ec9b0;">' . $line . '</div>';
					} else {
						echo '<div>' . $line . '</div>';
					}
				}
				?>
			</div>

			<p class="description" style="margin-top: 10px;">
				<?php esc_html_e( 'Zeigt die letzten 100 Log-Einträge. Voller Log-Pfad:', 'repro-ct-suite' ); ?>
				<code><?php echo esc_html( $log_file ); ?></code>
			</p>
		<?php else : ?>
			<div class="notice notice-info inline">
				<p>
					<span class="dashicons dashicons-info"></span>
					<?php esc_html_e( 'Keine Log-Datei vorhanden oder die Datei ist leer.', 'repro-ct-suite' ); ?>
				</p>
			</div>
		<?php endif; ?>
	</div>
</div>
