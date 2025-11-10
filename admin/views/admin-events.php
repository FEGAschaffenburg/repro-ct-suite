<?php
/**
 * Termine-Übersicht - Modernes Design
 *
 * Zeigt alle Termine aus dem neuen unified sync system mit modernem Design und Filtern.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

require_once plugin_dir_path( dirname( __DIR__ ) ) . 'includes/repositories/class-repro-ct-suite-repository-base.php';
require_once plugin_dir_path( dirname( __DIR__ ) ) . 'includes/repositories/class-repro-ct-suite-events-repository.php';
require_once plugin_dir_path( dirname( __DIR__ ) ) . 'includes/repositories/class-repro-ct-suite-calendars-repository.php';

$events_repo = new Repro_CT_Suite_Events_Repository();
$calendars_repo = new Repro_CT_Suite_Calendars_Repository();

// Filter
$from   = isset( $_GET['from'] ) ? sanitize_text_field( wp_unslash( $_GET['from'] ) ) : '';
$to     = isset( $_GET['to'] ) ? sanitize_text_field( wp_unslash( $_GET['to'] ) ) : '';
$calendar_filter = isset( $_GET['calendar_id'] ) ? sanitize_text_field( wp_unslash( $_GET['calendar_id'] ) ) : '';
$page   = max( 1, (int) ( $_GET['paged'] ?? 1 ) );
$limit  = 50;
$offset = ($page - 1) * $limit;

// Kombinierte Abfrage
global $wpdb;
$events_table = $wpdb->prefix . 'rcts_events';

$sql = "SELECT id, event_id, appointment_id, calendar_id, title, description, start_datetime, end_datetime FROM {$events_table} WHERE 1=1";

if ( ! empty( $from ) ) { 
    $sql .= $wpdb->prepare( ' AND start_datetime >= %s', $from . ' 00:00:00' );
}
if ( ! empty( $to ) ) { 
    $sql .= $wpdb->prepare( ' AND start_datetime <= %s', $to . ' 23:59:59' );
}
if ( ! empty( $calendar_filter ) ) {
    $sql .= $wpdb->prepare( ' AND calendar_id = %s', $calendar_filter );
}

$sql .= ' ORDER BY start_datetime ASC';
$sql .= $wpdb->prepare( ' LIMIT %d OFFSET %d', (int) $limit, (int) $offset );

$items = $wpdb->get_results( $sql );

// Gesamtanzahl
$sql_count = "SELECT COUNT(*) FROM {$events_table} WHERE 1=1";
if ( ! empty( $from ) ) { 
    $sql_count .= $wpdb->prepare( ' AND start_datetime >= %s', $from . ' 00:00:00' );
}
if ( ! empty( $to ) ) { 
    $sql_count .= $wpdb->prepare( ' AND start_datetime <= %s', $to . ' 23:59:59' );
}
if ( ! empty( $calendar_filter ) ) {
    $sql_count .= $wpdb->prepare( ' AND calendar_id = %s', $calendar_filter );
}
$total = (int) $wpdb->get_var( $sql_count );

$calendars = $calendars_repo->get_all();
$total_pages = ceil( $total / $limit );

?>

<div class="wrap rcts-modern-wrap">
    <div class="rcts-header-section">
        <h1>
            <div class="rcts-header-logo">
                <img src="<?php echo esc_url( plugin_dir_url( dirname( dirname( __FILE__ ) ) ) . 'assets/images/churchtools-suite-icon.svg' ); ?>" alt="ChurchTools Suite">
            </div>
            <?php esc_html_e( 'Termine', 'repro-ct-suite' ); ?>
        </h1>
        <p><?php esc_html_e( 'Übersicht aller Termine aus ChurchTools', 'repro-ct-suite' ); ?></p>
    </div>

    <div class="rcts-card">
        <!-- Filter -->
        <form method="get" class="rcts-filter-section">
            <input type="hidden" name="page" value="repro-ct-suite-events" />
            
            <div class="rcts-filter-grid">
                <div class="rcts-form-group">
                    <label>📅 <?php esc_html_e( 'Von', 'repro-ct-suite' ); ?></label>
                    <input type="date" name="from" value="<?php echo esc_attr( $from ); ?>" />
                </div>
                
                <div class="rcts-form-group">
                    <label>📅 <?php esc_html_e( 'Bis', 'repro-ct-suite' ); ?></label>
                    <input type="date" name="to" value="<?php echo esc_attr( $to ); ?>" />
                </div>
                
                <div class="rcts-form-group" style="grid-column: span 2;">
                    <label>🗂️ <?php esc_html_e( 'Kalender', 'repro-ct-suite' ); ?></label>
                    <select name="calendar_id">
                        <option value=""><?php esc_html_e( 'Alle Kalender', 'repro-ct-suite' ); ?></option>
                        <?php foreach ( $calendars as $cal ) : ?>
                            <option value="<?php echo esc_attr( $cal->calendar_id ); ?>" <?php selected( $calendar_filter, $cal->calendar_id ); ?>>
                                <?php echo esc_html( $cal->name ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="rcts-filter-actions">
                <button type="submit" class="rcts-btn rcts-btn-primary">
                    <span class="dashicons dashicons-filter"></span>
                    <?php esc_html_e( 'Filtern', 'repro-ct-suite' ); ?>
                </button>
                <?php if ( ! empty( $from ) || ! empty( $to ) || ! empty( $calendar_filter ) ) : ?>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=repro-ct-suite-events' ) ); ?>" class="rcts-btn rcts-btn-secondary">
                        <span class="dashicons dashicons-dismiss"></span>
                        <?php esc_html_e( 'Zurücksetzen', 'repro-ct-suite' ); ?>
                    </a>
                <?php endif; ?>
                <div class="rcts-filter-count">
                    📊 <?php printf( esc_html__( '%d Termine', 'repro-ct-suite' ), $total ); ?>
                </div>
            </div>
            
            <?php if ( ! empty( $from ) || ! empty( $to ) || ! empty( $calendar_filter ) ) : ?>
                <div class="rcts-active-filters">
                    <strong>✓ <?php esc_html_e( 'Aktive Filter:', 'repro-ct-suite' ); ?></strong>
                    <?php if ( ! empty( $from ) ) : ?>
                        <span>Von: <strong><?php echo esc_html( date_i18n( 'd.m.Y', strtotime( $from ) ) ); ?></strong></span>
                    <?php endif; ?>
                    <?php if ( ! empty( $to ) ) : ?>
                        <span style="margin-left: 12px;">Bis: <strong><?php echo esc_html( date_i18n( 'd.m.Y', strtotime( $to ) ) ); ?></strong></span>
                    <?php endif; ?>
                    <?php if ( ! empty( $calendar_filter ) ) : 
                        foreach ( $calendars as $cal ) {
                            if ( $cal->calendar_id === $calendar_filter ) : ?>
                                <span style="margin-left: 12px;">Kalender: <strong><?php echo esc_html( $cal->name ); ?></strong></span>
                            <?php endif;
                        }
                    endif; ?>
                </div>
            <?php endif; ?>
        </form>
        
        <!-- Moderne Tabelle -->
        <?php if ( empty( $items ) ) : ?>
            <div class="rcts-empty-state">
                <span class="dashicons dashicons-calendar"></span>
                <h3><?php esc_html_e( 'Keine Termine gefunden', 'repro-ct-suite' ); ?></h3>
                <p><?php esc_html_e( 'Versuchen Sie andere Filterwerte oder synchronisieren Sie die Termine erneut.', 'repro-ct-suite' ); ?></p>
            </div>
        <?php else : ?>
            <div class="rcts-table-wrapper">
                <table class="rcts-events-table">
                    <thead>
                        <tr>
                            <th style="width: 12%;">📅 <?php esc_html_e( 'Datum', 'repro-ct-suite' ); ?></th>
                            <th style="width: 8%;">🕐 <?php esc_html_e( 'Von', 'repro-ct-suite' ); ?></th>
                            <th style="width: 8%;">🕐 <?php esc_html_e( 'Bis', 'repro-ct-suite' ); ?></th>
                            <th>📝 <?php esc_html_e( 'Titel', 'repro-ct-suite' ); ?></th>
                            <th style="width: 15%;">🗂️ <?php esc_html_e( 'Kalender', 'repro-ct-suite' ); ?></th>
                            <th style="width: 10%;">🏷️ <?php esc_html_e( 'Typ', 'repro-ct-suite' ); ?></th>
                            <th style="width: 8%;">🔗 <?php esc_html_e( 'ID', 'repro-ct-suite' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $items as $item ) : 
                            $cal_name = '';
                            foreach ( $calendars as $c ) {
                                if ( $c->calendar_id === $item->calendar_id ) {
                                    $cal_name = $c->name;
                                    break;
                                }
                            }
                            
                            $start_date = date_i18n( 'd.m.Y', strtotime( $item->start_datetime ) );
                            $start_time = date_i18n( 'H:i', strtotime( $item->start_datetime ) );
                            $end_time = $item->end_datetime ? date_i18n( 'H:i', strtotime( $item->end_datetime ) ) : '-';
                            
                            $is_appointment = ! empty( $item->appointment_id );
                            $type_label = $is_appointment ? __( 'Termin', 'repro-ct-suite' ) : __( 'Event', 'repro-ct-suite' );
                            $type_class = $is_appointment ? 'rcts-badge-appointment' : 'rcts-badge-event';
                            ?>
                            <tr>
                                <td style="font-weight: 500;"><?php echo esc_html( $start_date ); ?></td>
                                <td><?php echo esc_html( $start_time ); ?></td>
                                <td><?php echo esc_html( $end_time ); ?></td>
                                <td>
                                    <strong><?php echo esc_html( $item->title ); ?></strong>
                                    <?php if ( ! empty( $item->description ) ) : ?>
                                        <div style="color: #646970; font-size: 12px; margin-top: 4px;">
                                            <?php echo esc_html( wp_trim_words( $item->description, 15 ) ); ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td style="color: #646970;"><?php echo esc_html( $cal_name ); ?></td>
                                <td>
                                    <span class="rcts-badge <?php echo esc_attr( $type_class ); ?>">
                                        <?php echo esc_html( $type_label ); ?>
                                    </span>
                                </td>
                                <td style="font-family: monospace; font-size: 11px; color: #646970;">
                                    <?php echo esc_html( $is_appointment ? 'A-' . $item->appointment_id : 'E-' . $item->event_id ); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ( $total_pages > 1 ) : ?>
                <div class="rcts-pagination">
                    <div class="rcts-pagination-info">
                        <?php printf( 
                            esc_html__( 'Seite %1$d von %2$d', 'repro-ct-suite' ), 
                            $page, 
                            $total_pages 
                        ); ?>
                    </div>
                    <div class="rcts-pagination-nav">
                        <?php if ( $page > 1 ) : ?>
                            <a href="<?php echo esc_url( add_query_arg( array_merge( $_GET, ['paged' => $page - 1] ) ) ); ?>" 
                               class="rcts-btn rcts-btn-secondary">
                                ← <?php esc_html_e( 'Zurück', 'repro-ct-suite' ); ?>
                            </a>
                        <?php endif; ?>
                        
                        <?php if ( $page < $total_pages ) : ?>
                            <a href="<?php echo esc_url( add_query_arg( array_merge( $_GET, ['paged' => $page + 1] ) ) ); ?>" 
                               class="rcts-btn rcts-btn-primary">
                                <?php esc_html_e( 'Weiter', 'repro-ct-suite' ); ?> →
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
