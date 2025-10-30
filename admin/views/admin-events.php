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
require_once plugin_dir_path( dirname( __DIR__ ) ) . 'includes/repositories/class-repro-ct-suite-appointments-repository.php';
require_once plugin_dir_path( dirname( __DIR__ ) ) . 'includes/repositories/class-repro-ct-suite-calendars-repository.php';

$events_repo = new Repro_CT_Suite_Events_Repository();
$appointments_repo = new Repro_CT_Suite_Appointments_Repository();
$calendars_repo = new Repro_CT_Suite_Calendars_Repository();

// Filter
$from   = isset( $_GET['from'] ) ? sanitize_text_field( wp_unslash( $_GET['from'] ) ) : '';
$to     = isset( $_GET['to'] ) ? sanitize_text_field( wp_unslash( $_GET['to'] ) ) : '';
$calendar_id = isset( $_GET['calendar_id'] ) ? (int) $_GET['calendar_id'] : 0;
$page   = max( 1, (int) ( $_GET['paged'] ?? 1 ) );
$limit  = 50;
$offset = ($page - 1) * $limit;

// Kombinierte Abfrage: Events + Appointments ohne Event
global $wpdb;
$events_table = $wpdb->prefix . 'rcts_events';
$appointments_table = $wpdb->prefix . 'rcts_appointments';

$where_conditions = array();
$params = array();

if ( ! empty( $from ) ) {
	$where_conditions[] = 'start_datetime >= %s';
	$params[] = $from;
}
if ( ! empty( $to ) ) {
	$where_conditions[] = 'start_datetime <= %s';
	$params[] = $to;
}
if ( $calendar_id > 0 ) {
	$where_conditions[] = 'calendar_id = %d';
	$params[] = $calendar_id;
}

$where_clause = count( $where_conditions ) > 0 ? 'WHERE ' . implode( ' AND ', $where_conditions ) : '';

// UNION: Events + Appointments ohne event_id
$sql = "
	SELECT id, external_id, calendar_id, appointment_id, title, description, start_datetime, end_datetime, location_name, status, 'event' AS source
	FROM {$events_table}
	{$where_clause}
	UNION ALL
	SELECT id, external_id, calendar_id, NULL AS appointment_id, title, description, start_datetime, end_datetime, NULL AS location_name, NULL AS status, 'appointment' AS source
	FROM {$appointments_table}
	WHERE event_id IS NULL " . ( $where_clause ? 'AND ' . str_replace( 'WHERE ', '', $where_clause ) : '' ) . "
	ORDER BY start_datetime ASC
	LIMIT %d OFFSET %d
";

$params[] = (int) $limit;
$params[] = (int) $offset;

if ( count( $params ) > 0 ) {
	$sql = $wpdb->prepare( $sql, ...$params );
}
$items = $wpdb->get_results( $sql );

// Gesamtanzahl für Pagination
$count_params = array_slice( $params, 0, -2 ); // ohne LIMIT/OFFSET
$sql_count = "
	SELECT COUNT(*) FROM (
		SELECT id FROM {$events_table} {$where_clause}
		UNION ALL
		SELECT id FROM {$appointments_table} WHERE event_id IS NULL " . ( $where_clause ? 'AND ' . str_replace( 'WHERE ', '', $where_clause ) : '' ) . "
	) AS combined
";
if ( count( $count_params ) > 0 ) {
	$sql_count = $wpdb->prepare( $sql_count, ...$count_params );
}
$total = (int) $wpdb->get_var( $sql_count );
// Kalender für Filter-Dropdown
$calendars = $calendars_repo->get_all();
$total_pages = ceil( $total / $limit );

?>
<div class="wrap repro-ct-suite-admin-wrapper">
    <div class="repro-ct-suite-header">
        <h1><span class="dashicons dashicons-calendar-alt"></span> <?php esc_html_e( 'Terminkalender', 'repro-ct-suite' ); ?></h1>
        <p><?php esc_html_e( 'Gesamtübersicht aller Termine: Events (aus ChurchTools Events-API) und Termine (aus Appointments ohne Event-Verknüpfung).', 'repro-ct-suite' ); ?></p>
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
            <h3><?php esc_html_e( 'Terminkalender-Übersicht', 'repro-ct-suite' ); ?></h3>
            <span class="repro-ct-suite-badge"><?php printf( esc_html__( '%d Einträge', 'repro-ct-suite' ), $total ); ?></span>
        </div>
        <div class="repro-ct-suite-card-body">
            <table class="widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width:18%;"><?php esc_html_e( 'Datum/Uhrzeit', 'repro-ct-suite' ); ?></th>
                        <th style="width:8%;"><?php esc_html_e( 'Art', 'repro-ct-suite' ); ?></th>
                        <th style="width:30%;"><?php esc_html_e( 'Titel', 'repro-ct-suite' ); ?></th>
                        <th style="width:20%;"><?php esc_html_e( 'Ort', 'repro-ct-suite' ); ?></th>
                        <th style="width:16%;"><?php esc_html_e( 'Ende', 'repro-ct-suite' ); ?></th>
                        <th style="width:8%;"><?php esc_html_e( 'Status', 'repro-ct-suite' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                <?php if ( empty( $items ) ) : ?>
                    <tr><td colspan="6" style="text-align:center; padding:30px;">
                        <?php esc_html_e( 'Keine Termine gefunden. Führen Sie die Synchronisation aus, um Termine zu importieren.', 'repro-ct-suite' ); ?>
                    </td></tr>
                <?php else : foreach ( $items as $item ) : 
                    // Art bestimmen: Event (aus rcts_events) oder Termin (aus rcts_appointments ohne event_id)
                    $type = $item->source === 'event' ? 'Event' : 'Termin';
                    $type_class = $item->source === 'event' ? 'repro-ct-suite-badge-info' : 'repro-ct-suite-badge-success';
                    $tooltip = $item->source === 'event' ? 'Event aus ChurchTools Events-API' : 'Termin aus Appointment (ohne Event-Verknüpfung)';
                    $calendar = $item->calendar_id ? $calendars_repo->get_by_id( $item->calendar_id ) : null;
                    ?>
                    <tr>
                        <td>
                            <strong><?php echo esc_html( date_i18n( get_option('date_format'), strtotime( $item->start_datetime ) ) ); ?></strong><br>
                            <small><?php echo esc_html( date_i18n( 'H:i', strtotime( $item->start_datetime ) ) ); ?> Uhr</small>
                        </td>
                        <td>
                            <span class="repro-ct-suite-badge <?php echo esc_attr( $type_class ); ?>" title="<?php echo esc_attr( $tooltip ); ?>">
                                <?php echo esc_html( $type ); ?>
                            </span>
                        </td>
                        <td>
                            <strong><?php echo esc_html( $item->title ); ?></strong>
                            <?php if ( $calendar ) : ?>
                                <br><small style="color:#666;">
                                    <span class="dashicons dashicons-calendar" style="font-size:14px;"></span>
                                    <?php echo esc_html( $calendar->name ); ?>
                                </small>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $item->location_name ? esc_html( $item->location_name ) : '—'; ?></td>
                        <td>
                            <?php if ( ! empty( $item->end_datetime ) ) : ?>
                                <?php echo esc_html( date_i18n( get_option('date_format') . ' H:i', strtotime( $item->end_datetime ) ) ); ?>
                            <?php else : ?>
                                —
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ( $item->status ) : ?>
                                <span class="repro-ct-suite-badge repro-ct-suite-badge-success"><?php echo esc_html( ucfirst( $item->status ) ); ?></span>
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
            <h3><?php esc_html_e( 'Legende: Art', 'repro-ct-suite' ); ?></h3>
        </div>
        <div class="repro-ct-suite-card-body">
            <p>
                <span class="repro-ct-suite-badge repro-ct-suite-badge-info">Event</span>
                <?php esc_html_e( 'Veranstaltung direkt aus ChurchTools Events-API', 'repro-ct-suite' ); ?>
            </p>
            <p style="margin-top:10px;">
                <span class="repro-ct-suite-badge repro-ct-suite-badge-success">Termin</span>
                <?php esc_html_e( 'Einfacher Termin aus ChurchTools Appointment (ohne Event-Verknüpfung)', 'repro-ct-suite' ); ?>
            </p>
        </div>
    </div>
</div>
