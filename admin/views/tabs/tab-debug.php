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
// Zeigt den Inhalt der WordPress Debug-Log-Datei an (wp-content/debug.log)
$log_file = WP_CONTENT_DIR . '/debug.log';
$log_exists = file_exists( $log_file );
$log_size = $log_exists ? filesize( $log_file ) : 0;
$log_lines = 0;
if ( $log_exists && $log_size > 0 ) {
	$log_lines = count( file( $log_file ) );
}
?>

<!-- Debug-Status-Karte -->
<div class="repro-ct-suite-card">
	<div class="repro-ct-suite-card-header">
		<span class="dashicons dashicons-admin-tools"></span>
		<h3><?php esc_html_e( 'Debug-Status', 'repro-ct-suite' ); ?></h3>
	</div>
	<div class="repro-ct-suite-card-body">
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><?php esc_html_e( 'WP_DEBUG', 'repro-ct-suite' ); ?></th>
				<td>
					<?php if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) : ?>
						<span style="color: #46b450; font-weight: 600;">
							<span class="dashicons dashicons-yes-alt"></span>
							<?php esc_html_e( 'Aktiviert', 'repro-ct-suite' ); ?>
						</span>
						<p class="description"><?php esc_html_e( 'WordPress Debug-Modus ist aktiv. Fehler werden angezeigt.', 'repro-ct-suite' ); ?></p>
					<?php else : ?>
						<span style="color: #dc3232; font-weight: 600;">
							<span class="dashicons dashicons-dismiss"></span>
							<?php esc_html_e( 'Deaktiviert', 'repro-ct-suite' ); ?>
						</span>
						<p class="description"><?php esc_html_e( 'WordPress Debug-Modus ist nicht aktiv.', 'repro-ct-suite' ); ?></p>
					<?php endif; ?>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'WP_DEBUG_LOG', 'repro-ct-suite' ); ?></th>
				<td>
					<?php if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) : ?>
						<span style="color: #46b450; font-weight: 600;">
							<span class="dashicons dashicons-yes-alt"></span>
							<?php esc_html_e( 'Aktiviert', 'repro-ct-suite' ); ?>
						</span>
						<p class="description"><?php esc_html_e( 'WordPress schreibt Fehler ins debug.log.', 'repro-ct-suite' ); ?></p>
					<?php else : ?>
						<span style="color: #dc3232; font-weight: 600;">
							<span class="dashicons dashicons-dismiss"></span>
							<?php esc_html_e( 'Deaktiviert', 'repro-ct-suite' ); ?>
						</span>
						<p class="description"><?php esc_html_e( 'WordPress schreibt keine Fehler ins debug.log.', 'repro-ct-suite' ); ?></p>
					<?php endif; ?>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'WP_DEBUG_DISPLAY', 'repro-ct-suite' ); ?></th>
				<td>
					<?php if ( defined( 'WP_DEBUG_DISPLAY' ) && WP_DEBUG_DISPLAY ) : ?>
						<span style="color: #dba617; font-weight: 600;">
							<span class="dashicons dashicons-visibility"></span>
							<?php esc_html_e( 'Aktiviert', 'repro-ct-suite' ); ?>
						</span>
						<p class="description"><?php esc_html_e( 'Fehler werden im Frontend angezeigt. Nicht für Produktiv-Umgebung empfohlen!', 'repro-ct-suite' ); ?></p>
					<?php else : ?>
						<span style="color: #46b450; font-weight: 600;">
							<span class="dashicons dashicons-hidden"></span>
							<?php esc_html_e( 'Deaktiviert', 'repro-ct-suite' ); ?>
						</span>
						<p class="description"><?php esc_html_e( 'Fehler werden nicht im Frontend angezeigt (empfohlen).', 'repro-ct-suite' ); ?></p>
					<?php endif; ?>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Plugin-Logging', 'repro-ct-suite' ); ?></th>
				<td>
					<span style="color: #46b450; font-weight: 600;">
						<span class="dashicons dashicons-yes-alt"></span>
						<?php esc_html_e( 'Immer aktiv', 'repro-ct-suite' ); ?>
					</span>
					<p class="description"><?php esc_html_e( 'Dieses Plugin schreibt eigene Logs direkt ins debug.log - unabhängig von WP_DEBUG.', 'repro-ct-suite' ); ?></p>
				</td>
			</tr>
		</table>

		<?php if ( ! ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ) : ?>
		<div class="notice notice-info inline" style="margin-top: 15px;">
			<p>
				<strong><?php esc_html_e( 'Tipp:', 'repro-ct-suite' ); ?></strong>
				<?php esc_html_e( 'Um WordPress Debug-Modus zu aktivieren, fügen Sie folgende Zeilen in Ihre wp-config.php ein:', 'repro-ct-suite' ); ?>
			</p>
			<pre style="background: #f0f0f1; padding: 10px; border-radius: 4px; overflow-x: auto;">define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );</pre>
		</div>
		<?php endif; ?>
	</div>
</div>

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
				// Letzten 100 Zeilen des Logs anzeigen, aber nur Zeilen vom Plugin
				$log_content = file( $log_file );
				$log_content = array_reverse( $log_content ); // Neueste zuerst
				
				$plugin_lines = array();
				foreach ( $log_content as $line ) {
					// Nur Zeilen vom Plugin anzeigen
					if ( strpos( $line, '[REPRO CT-SUITE]' ) !== false ) {
						$plugin_lines[] = $line;
						if ( count( $plugin_lines ) >= 100 ) {
							break;
						}
					}
				}
				
				$plugin_lines = array_reverse( $plugin_lines ); // Wieder chronologisch

				foreach ( $plugin_lines as $line ) {
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
				<?php esc_html_e( 'Zeigt die letzten 100 Plugin-Einträge aus dem WordPress Debug-Log. Voller Log-Pfad:', 'repro-ct-suite' ); ?>
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
