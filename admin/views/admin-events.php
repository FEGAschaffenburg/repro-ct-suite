<?php
/**
 * Veranstaltungen-Übersicht
 *
 * Zeigt alle Events (Einzeltermine) aus der Veranstaltungen-Gesamtliste.
 * Events können aus /events API oder aus Appointments-Vorlagen stammen.
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
$calendar_id = isset( $_GET['calendar_id'] ) ? (int) $_GET['calendar_id'] : 0;
$page   = max( 1, (int) ( $_GET['paged'] ?? 1 ) );
$limit  = 50;
$offset = ($page - 1) * $limit;

// Events holen
global $wpdb;
$events_table = $wpdb->prefix . 'rcts_events';
$where = 'WHERE 1=1';
$params = array();
if ( ! empty( $from ) ) { $where .= ' AND start_datetime >= %s'; $params[] = $from; }
if ( ! empty( $to ) )   { $where .= ' AND start_datetime <= %s'; $params[] = $to; }
if ( $calendar_id > 0 ) { $where .= ' AND calendar_id = %d'; $params[] = $calendar_id; }
$params[] = (int) $limit;
$params[] = (int) $offset;

$sql_events = "SELECT id, external_id, calendar_id, appointment_id, title, description, start_datetime, end_datetime, location_name, status FROM {$events_table} {$where} ORDER BY start_datetime ASC LIMIT %d OFFSET %d";
if ( count( $params ) > 0 ) {
    $sql_events = $wpdb->prepare( $sql_events, ...$params );
}
$events = $wpdb->get_results( $sql_events );

// Kalender für Filter-Dropdown
$calendars = $calendars_repo->get_all();

// Gesamtanzahl für Pagination
$sql_count = "SELECT COUNT(*) FROM {$events_table} {$where}";
if ( count( $params ) > 2 ) {
    $count_params = array_slice( $params, 0, -2 ); // ohne LIMIT/OFFSET
    $sql_count = $wpdb->prepare( $sql_count, ...$count_params );
}
$total = (int) $wpdb->get_var( $sql_count );
$total_pages = ceil( $total / $limit );

?>
<div class="wrap repro-ct-suite-admin-wrapper">
    <div class="repro-ct-suite-header">
        <h1><span class="dashicons dashicons-calendar-alt"></span> <?php esc_html_e( 'Veranstaltungen', 'repro-ct-suite' ); ?></h1>
        <p><?php esc_html_e( 'Gesamtliste aller Events (Einzeltermine) aus ChurchTools. Events können direkt aus /events API oder aus Appointments-Terminvorlagen stammen.', 'repro-ct-suite' ); ?></p>
    </div>

    <form method="get" class="repro-ct-suite-mt-10">
        <input type="hidden" name="page" value="repro-ct-suite-events" />
        <label>
            <?php esc_html_e( 'Von', 'repro-ct-suite' ); ?>
            <input type="date" name="from" value="<?php echo esc_attr( $from ); ?>" />
        </label>
        <label style="margin-left:10px;">
            <?php esc_html_e( 'Bis', 'repro-ct-suite' ); ?>
            <input type="date" name="to" value="<?php echo esc_attr( $to ); ?>" />
        </label>
        <label style="margin-left:10px;">
            <?php esc_html_e( 'Kalender', 'repro-ct-suite' ); ?>
            <select name="calendar_id">
                <option value="0"><?php esc_html_e( 'Alle', 'repro-ct-suite' ); ?></option>
                <?php foreach ( $calendars as $cal ) : ?>
                    <option value="<?php echo esc_attr( $cal->id ); ?>" <?php selected( $calendar_id, $cal->id ); ?>>
                        <?php echo esc_html( $cal->name ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <button class="repro-ct-suite-btn repro-ct-suite-btn-secondary" style="margin-left:10px;">
            <span class="dashicons dashicons-filter"></span> <?php esc_html_e( 'Filtern', 'repro-ct-suite' ); ?>
        </button>
    </form>

    <div class="repro-ct-suite-card repro-ct-suite-mt-20">
        <div class="repro-ct-suite-card-header">
            <h3><?php esc_html_e( 'Veranstaltungen-Gesamtliste', 'repro-ct-suite' ); ?></h3>
            <span class="repro-ct-suite-badge"><?php printf( esc_html__( '%d Einträge', 'repro-ct-suite' ), $total ); ?></span>
        </div>
        <div class="repro-ct-suite-card-body">
            <table class="widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width:18%;"><?php esc_html_e( 'Datum/Uhrzeit', 'repro-ct-suite' ); ?></th>
                        <th style="width:8%;"><?php esc_html_e( 'Quelle', 'repro-ct-suite' ); ?></th>
                        <th style="width:30%;"><?php esc_html_e( 'Titel', 'repro-ct-suite' ); ?></th>
                        <th style="width:20%;"><?php esc_html_e( 'Ort', 'repro-ct-suite' ); ?></th>
                        <th style="width:16%;"><?php esc_html_e( 'Ende', 'repro-ct-suite' ); ?></th>
                        <th style="width:8%;"><?php esc_html_e( 'Status', 'repro-ct-suite' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                <?php if ( empty( $events ) ) : ?>
                    <tr><td colspan="6" style="text-align:center; padding:30px;">
                        <?php esc_html_e( 'Keine Veranstaltungen gefunden. Führen Sie die Synchronisation aus, um Events zu importieren.', 'repro-ct-suite' ); ?>
                    </td></tr>
                <?php else : foreach ( $events as $event ) : 
                    $source = $event->appointment_id ? 'Appointment' : 'Event';
                    $source_class = $event->appointment_id ? 'repro-ct-suite-badge-warning' : 'repro-ct-suite-badge-info';
                    $calendar = $event->calendar_id ? $calendars_repo->get_by_id( $event->calendar_id ) : null;
                    ?>
                    <tr>
                        <td>
                            <strong><?php echo esc_html( date_i18n( get_option('date_format'), strtotime( $event->start_datetime ) ) ); ?></strong><br>
                            <small><?php echo esc_html( date_i18n( 'H:i', strtotime( $event->start_datetime ) ) ); ?> Uhr</small>
                        </td>
                        <td>
                            <span class="repro-ct-suite-badge <?php echo esc_attr( $source_class ); ?>" title="<?php echo esc_attr( $source === 'Appointment' ? 'Aus Terminvorlage generiert' : 'Direkt aus Events-API' ); ?>">
                                <?php echo esc_html( $source ); ?>
                            </span>
                        </td>
                        <td>
                            <strong><?php echo esc_html( $event->title ); ?></strong>
                            <?php if ( $calendar ) : ?>
                                <br><small style="color:#666;">
                                    <span class="dashicons dashicons-calendar" style="font-size:14px;"></span>
                                    <?php echo esc_html( $calendar->name ); ?>
                                </small>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $event->location_name ? esc_html( $event->location_name ) : '—'; ?></td>
                        <td>
                            <?php if ( ! empty( $event->end_datetime ) ) : ?>
                                <?php echo esc_html( date_i18n( get_option('date_format') . ' H:i', strtotime( $event->end_datetime ) ) ); ?>
                            <?php else : ?>
                                —
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ( $event->status ) : ?>
                                <span class="repro-ct-suite-badge repro-ct-suite-badge-success"><?php echo esc_html( ucfirst( $event->status ) ); ?></span>
                            <?php else : ?>
                                —
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>

            <?php if ( $total_pages > 1 ) : ?>
                <div class="tablenav" style="margin-top:20px;">
                    <div class="tablenav-pages">
                        <span class="displaying-num"><?php printf( esc_html__( '%d Einträge', 'repro-ct-suite' ), $total ); ?></span>
                        <?php
                        $base_url = add_query_arg( array(
                            'page' => 'repro-ct-suite-events',
                            'from' => $from,
                            'to' => $to,
                            'calendar_id' => $calendar_id,
                        ), admin_url( 'admin.php' ) );
                        echo paginate_links( array(
                            'base' => $base_url . '%_%',
                            'format' => '&paged=%#%',
                            'current' => $page,
                            'total' => $total_pages,
                            'prev_text' => '&laquo;',
                            'next_text' => '&raquo;',
                        ) );
                        ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="repro-ct-suite-card repro-ct-suite-mt-20">
        <div class="repro-ct-suite-card-header">
            <h3><?php esc_html_e( 'Legende: Quelle', 'repro-ct-suite' ); ?></h3>
        </div>
        <div class="repro-ct-suite-card-body">
            <p>
                <span class="repro-ct-suite-badge repro-ct-suite-badge-info">Event</span>
                <?php esc_html_e( 'Einzeltermin direkt aus ChurchTools /events API importiert', 'repro-ct-suite' ); ?>
            </p>
            <p style="margin-top:10px;">
                <span class="repro-ct-suite-badge repro-ct-suite-badge-warning">Appointment</span>
                <?php esc_html_e( 'Berechnete Termin-Instanz aus einer Appointment-Terminvorlage (z.B. Serien/Wiederholungen)', 'repro-ct-suite' ); ?>
            </p>
        </div>
    </div>
</div>
