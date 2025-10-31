<?php
/**
 * Debug-Seite Template
 *
 * Bietet erweiterte Debug-Funktionen: Tabellen zurücksetzen, Logs anzeigen, DB-Updates
 *
 * @package    Repro_CT_Suite
 * @subpackage Repro_CT_Suite/admin/views
 * @since      0.3.4.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Nur für Admins zugänglich
if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( __( 'Sie haben keine Berechtigung, auf diese Seite zuzugreifen.', 'repro-ct-suite' ) );
}

// Tabellen-Statistiken abrufen
global $wpdb;
$tables_info = array(
	'rcts_calendars' => array(
		'label' => __( 'Kalender', 'repro-ct-suite' ),
		'table' => $wpdb->prefix . 'rcts_calendars',
		'count' => 0,
		'icon'  => 'calendar-alt',
	),
	'rcts_events' => array(
		'label' => __( 'Events', 'repro-ct-suite' ),
		'table' => $wpdb->prefix . 'rcts_events',
		'count' => 0,
		'icon'  => 'megaphone',
	),
	'rcts_appointments' => array(
		'label' => __( 'Termine (Appointments)', 'repro-ct-suite' ),
		'table' => $wpdb->prefix . 'rcts_appointments',
		'count' => 0,
		'icon'  => 'clock',
	),
	'rcts_event_services' => array(
		'label' => __( 'Event-Services', 'repro-ct-suite' ),
		'table' => $wpdb->prefix . 'rcts_event_services',
		'count' => 0,
		'icon'  => 'groups',
	),
	'rcts_schedule' => array(
		'label' => __( 'Terminkalender (Schedule)', 'repro-ct-suite' ),
		'table' => $wpdb->prefix . 'rcts_schedule',
		'count' => 0,
		'icon'  => 'admin-page',
	),
);

// Counts abrufen
foreach ( $tables_info as $key => &$info ) {
	$table_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $info['table'] ) );
	if ( $table_exists ) {
		$info['count'] = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$info['table']}" );
	}
}

// DB-Version abrufen
$current_db_version = get_option( 'repro_ct_suite_db_version', '0' );

// Debug-Log abrufen (falls vorhanden)
$log_file = WP_CONTENT_DIR . '/repro-ct-suite-debug.log';
$log_exists = file_exists( $log_file );
$log_size = $log_exists ? filesize( $log_file ) : 0;
$log_lines = 0;

if ( $log_exists && $log_size > 0 ) {
	$log_lines = count( file( $log_file ) );
}
?>

<div class="wrap repro-ct-suite-admin-wrapper">
	<h1>
		<span class="dashicons dashicons-warning" style="color: #d63638;"></span>
		<?php esc_html_e( 'Debug-Bereich', 'repro-ct-suite' ); ?>
	</h1>
	
	<p class="description">
		<?php esc_html_e( 'Erweiterte Debug- und Wartungsfunktionen für Entwickler und Administratoren. Bitte verwenden Sie diese Funktionen nur, wenn Sie wissen, was Sie tun!', 'repro-ct-suite' ); ?>
	</p>

	<!-- Warnung -->
	<div class="notice notice-error" style="border-left-width: 4px; padding: 12px;">
		<p>
			<strong><?php esc_html_e( 'Achtung:', 'repro-ct-suite' ); ?></strong>
			<?php esc_html_e( 'Das Zurücksetzen von Tabellen löscht alle synchronisierten Daten unwiderruflich. Erstellen Sie vor dem Zurücksetzen ein Backup Ihrer Datenbank!', 'repro-ct-suite' ); ?>
		</p>
	</div>

	<!-- Tabellen-Übersicht & Reset -->
	<div class="repro-ct-suite-card repro-ct-suite-mt-20">
		<div class="repro-ct-suite-card-header">
			<span class="dashicons dashicons-database"></span>
			<h2><?php esc_html_e( 'Datenbank-Tabellen', 'repro-ct-suite' ); ?></h2>
		</div>
		<div class="repro-ct-suite-card-body">
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Tabelle', 'repro-ct-suite' ); ?></th>
						<th><?php esc_html_e( 'Einträge', 'repro-ct-suite' ); ?></th>
						<th><?php esc_html_e( 'Aktionen', 'repro-ct-suite' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $tables_info as $key => $info ) : ?>
					<tr>
						<td>
							<span class="dashicons dashicons-<?php echo esc_attr( $info['icon'] ); ?>"></span>
							<strong><?php echo esc_html( $info['label'] ); ?></strong>
							<br>
							<code style="font-size: 11px; color: #666;"><?php echo esc_html( $info['table'] ); ?></code>
						</td>
						<td>
							<span class="repro-ct-suite-badge <?php echo $info['count'] > 0 ? 'repro-ct-suite-badge-info' : 'repro-ct-suite-badge-secondary'; ?>">
								<?php echo number_format_i18n( $info['count'] ); ?>
							</span>
						</td>
						<td>
							<button 
								class="repro-ct-suite-btn repro-ct-suite-btn-sm repro-ct-suite-btn-danger repro-ct-suite-clear-single-table"
								data-table="<?php echo esc_attr( $key ); ?>"
								data-table-name="<?php echo esc_attr( $info['table'] ); ?>"
								data-label="<?php echo esc_attr( $info['label'] ); ?>"
								data-nonce="<?php echo esc_attr( wp_create_nonce( 'repro_ct_suite_admin' ) ); ?>"
								<?php echo $info['count'] === 0 ? 'disabled' : ''; ?>
							>
								<span class="dashicons dashicons-trash"></span>
								<?php esc_html_e( 'Leeren', 'repro-ct-suite' ); ?>
							</button>
						</td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #f0f0f1;">
				<button 
					id="repro-ct-suite-clear-all-tables" 
					class="repro-ct-suite-btn repro-ct-suite-btn-danger"
					data-nonce="<?php echo esc_attr( wp_create_nonce( 'repro_ct_suite_admin' ) ); ?>"
				>
					<span class="dashicons dashicons-trash"></span>
					<?php esc_html_e( 'Alle Tabellen leeren', 'repro-ct-suite' ); ?>
				</button>
				
				<span id="repro-ct-suite-clear-result" style="margin-left: 15px; display: none;"></span>
			</div>
		</div>
	</div>

	<!-- Datenbank-Update -->
	<div class="repro-ct-suite-card repro-ct-suite-mt-20">
		<div class="repro-ct-suite-card-header">
			<span class="dashicons dashicons-update"></span>
			<h2><?php esc_html_e( 'Datenbank-Schema', 'repro-ct-suite' ); ?></h2>
		</div>
		<div class="repro-ct-suite-card-body">
			<table class="widefat">
				<tbody>
					<tr>
						<td style="width: 200px;"><strong><?php esc_html_e( 'Aktuelle DB-Version:', 'repro-ct-suite' ); ?></strong></td>
						<td>
							<code><?php echo esc_html( $current_db_version ); ?></code>
						</td>
					</tr>
					<tr>
						<td><strong><?php esc_html_e( 'Plugin-Version:', 'repro-ct-suite' ); ?></strong></td>
						<td>
							<code><?php echo esc_html( REPRO_CT_SUITE_VERSION ); ?></code>
						</td>
					</tr>
				</tbody>
			</table>

			<div style="margin-top: 20px;">
				<h3><?php esc_html_e( 'Datenbank-Migrationen', 'repro-ct-suite' ); ?></h3>
				<p class="description">
					<?php esc_html_e( 'Führt die Datenbank-Migrationen manuell aus. Normalerweise geschieht dies automatisch bei Plugin-Updates.', 'repro-ct-suite' ); ?>
				</p>
				<button 
					id="repro-ct-suite-run-migrations" 
					class="repro-ct-suite-btn repro-ct-suite-btn-primary"
					data-nonce="<?php echo esc_attr( wp_create_nonce( 'repro_ct_suite_admin' ) ); ?>"
				>
					<span class="dashicons dashicons-database-import"></span>
					<?php esc_html_e( 'DB-Migrationen ausführen', 'repro-ct-suite' ); ?>
				</button>
				
				<span id="repro-ct-suite-migration-result" style="margin-left: 15px; display: none;"></span>
			</div>

			<div style="margin-top: 25px; padding-top: 20px; border-top: 1px solid #ddd;">
				<h3><?php esc_html_e( 'Calendar-IDs korrigieren', 'repro-ct-suite' ); ?></h3>
				<p class="description">
					<?php esc_html_e( 'Extrahiert Calendar-IDs aus dem raw_payload und aktualisiert die calendar_id Spalte in Events und Appointments. Nützlich nach dem Update auf Version 0.3.6.0.', 'repro-ct-suite' ); ?>
				</p>
				<button 
					id="repro-ct-suite-fix-calendar-ids" 
					class="repro-ct-suite-btn repro-ct-suite-btn-secondary"
					data-nonce="<?php echo esc_attr( wp_create_nonce( 'repro_ct_suite_admin' ) ); ?>"
				>
					<span class="dashicons dashicons-admin-tools"></span>
					<?php esc_html_e( 'Calendar-IDs korrigieren', 'repro-ct-suite' ); ?>
				</button>
				
				<span id="repro-ct-suite-fix-calendar-ids-result" style="margin-left: 15px; display: none;"></span>
			</div>
		</div>
	</div>

	<!-- Debug-Log -->
	<div class="repro-ct-suite-card repro-ct-suite-mt-20">
		<div class="repro-ct-suite-card-header">
			<span class="dashicons dashicons-media-text"></span>
			<h2><?php esc_html_e( 'Debug-Log', 'repro-ct-suite' ); ?></h2>
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
						
						// Syntax-Highlighting
						if ( strpos( $line, '[ERROR]' ) !== false ) {
							echo '<div style="color: #f48771;">' . $line . '</div>';
						} elseif ( strpos( $line, '[WARNING]' ) !== false ) {
							echo '<div style="color: #dcdcaa;">' . $line . '</div>';
						} elseif ( strpos( $line, '[SUCCESS]' ) !== false ) {
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

	<!-- System-Informationen -->
	<div class="repro-ct-suite-card repro-ct-suite-mt-20">
		<div class="repro-ct-suite-card-header">
			<span class="dashicons dashicons-info"></span>
			<h2><?php esc_html_e( 'System-Informationen', 'repro-ct-suite' ); ?></h2>
		</div>
		<div class="repro-ct-suite-card-body">
			<table class="widefat">
				<tbody>
					<tr>
						<td style="width: 250px;"><strong><?php esc_html_e( 'WordPress-Version:', 'repro-ct-suite' ); ?></strong></td>
						<td><?php echo esc_html( get_bloginfo( 'version' ) ); ?></td>
					</tr>
					<tr>
						<td><strong><?php esc_html_e( 'PHP-Version:', 'repro-ct-suite' ); ?></strong></td>
						<td><?php echo esc_html( phpversion() ); ?></td>
					</tr>
					<tr>
						<td><strong><?php esc_html_e( 'MySQL-Version:', 'repro-ct-suite' ); ?></strong></td>
						<td><?php echo esc_html( $wpdb->db_version() ); ?></td>
					</tr>
					<tr>
						<td><strong><?php esc_html_e( 'WordPress Memory Limit:', 'repro-ct-suite' ); ?></strong></td>
						<td><?php echo esc_html( WP_MEMORY_LIMIT ); ?></td>
					</tr>
					<tr>
						<td><strong><?php esc_html_e( 'WordPress Debug-Modus:', 'repro-ct-suite' ); ?></strong></td>
						<td>
							<?php if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) : ?>
								<span class="repro-ct-suite-badge repro-ct-suite-badge-success"><?php esc_html_e( 'Aktiviert', 'repro-ct-suite' ); ?></span>
							<?php else : ?>
								<span class="repro-ct-suite-badge repro-ct-suite-badge-secondary"><?php esc_html_e( 'Deaktiviert', 'repro-ct-suite' ); ?></span>
							<?php endif; ?>
						</td>
					</tr>
					<tr>
						<td><strong><?php esc_html_e( 'Plugin-Verzeichnis:', 'repro-ct-suite' ); ?></strong></td>
						<td><code><?php echo esc_html( REPRO_CT_SUITE_PATH ); ?></code></td>
					</tr>
					<tr>
						<td><strong><?php esc_html_e( 'Debug-Logfile:', 'repro-ct-suite' ); ?></strong></td>
						<td>
							<code><?php echo esc_html( $log_file ); ?></code>
							<?php if ( $log_exists ) : ?>
								<br>
								<span class="description">
									<?php 
									printf( 
										/* translators: 1: file size in KB, 2: number of lines */
										esc_html__( 'Größe: %1$s KB • Zeilen: %2$s', 'repro-ct-suite' ),
										number_format_i18n( round( $log_size / 1024, 2 ) ),
										number_format_i18n( $log_lines )
									); 
									?>
								</span>
							<?php else : ?>
								<br>
								<span class="description" style="color: #999;"><?php esc_html_e( '(noch nicht erstellt)', 'repro-ct-suite' ); ?></span>
							<?php endif; ?>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>
