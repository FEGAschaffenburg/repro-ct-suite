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
$calendar_filter = isset( $_GET['calendar_id'] ) ? sanitize_text_field( wp_unslash( $_GET['calendar_id'] ) ) : '';
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
if ( ! empty( $calendar_filter ) ) {
	$where_conditions[] = 'calendar_id = %s';
	$params[] = $calendar_filter;
}

$where_clause = count( $where_conditions ) > 0 ? 'WHERE ' . implode( ' AND ', $where_conditions ) : '';

// UNION: Events + Appointments ohne event_id
// WICHTIG: calendar_id in beiden Tabellen ist die externe ChurchTools Calendar-ID
$sql = "
	SELECT id, external_id, calendar_id, appointment_id, title, description, start_datetime, end_datetime, 'event' AS source
	FROM {$events_table}
	{$where_clause}
	UNION ALL
	SELECT id, external_id, calendar_id, NULL AS appointment_id, title, description, start_datetime, end_datetime, 'appointment' AS source
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
                <option value=""><?php esc_html_e( 'Alle', 'repro-ct-suite' ); ?></option>
                <?php foreach ( $calendars as $cal ) : ?>
                    <option value="<?php echo esc_attr( $cal->external_id ); ?>" <?php selected( $calendar_filter, $cal->external_id ); ?>>
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
                        <th style="width:12%;"><?php esc_html_e( 'Anfang', 'repro-ct-suite' ); ?></th>
                        <th style="width:12%;"><?php esc_html_e( 'Ende', 'repro-ct-suite' ); ?></th>
                        <th style="width:25%;"><?php esc_html_e( 'Titel', 'repro-ct-suite' ); ?></th>
                        <th style="width:25%;"><?php esc_html_e( 'Beschreibung', 'repro-ct-suite' ); ?></th>
                        <th style="width:16%;"><?php esc_html_e( 'Kalender', 'repro-ct-suite' ); ?></th>
                        <th style="width:10%;"><?php esc_html_e( 'Typ', 'repro-ct-suite' ); ?></th>
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
                    
                    // Kalender holen über external_id (calendar_id in Events/Appointments ist die externe ChurchTools ID)
                    $calendar = $item->calendar_id ? $calendars_repo->get_by_external_id( $item->calendar_id ) : null;
                    
                    // WordPress-Zeitzone berücksichtigen
                    $wp_timezone = wp_timezone();
                    $start_dt = new DateTime( $item->start_datetime, new DateTimeZone('UTC') );
                    $start_dt->setTimezone( $wp_timezone );
                    
                    $end_dt = null;
                    if ( ! empty( $item->end_datetime ) ) {
                        $end_dt = new DateTime( $item->end_datetime, new DateTimeZone('UTC') );
                        $end_dt->setTimezone( $wp_timezone );
                    }
                    
                    // Beschreibung kürzen
                    $description_preview = ! empty( $item->description ) ? wp_trim_words( strip_tags( $item->description ), 10, '...' ) : '—';
                    ?>
                    <tr>
                        <td>
                            <strong><?php echo esc_html( $start_dt->format( get_option('date_format') ) ); ?></strong><br>
                            <small><?php echo esc_html( $start_dt->format( 'H:i' ) ); ?> Uhr</small>
                        </td>
                        <td>
                            <?php if ( $end_dt ) : ?>
                                <strong><?php echo esc_html( $end_dt->format( get_option('date_format') ) ); ?></strong><br>
                                <small><?php echo esc_html( $end_dt->format( 'H:i' ) ); ?> Uhr</small>
                            <?php else : ?>
                                —
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?php echo esc_html( $item->title ); ?></strong>
                        </td>
                        <td>
                            <small><?php echo esc_html( $description_preview ); ?></small>
                        </td>
                        <td>
                            <?php if ( $calendar ) : ?>
                                <span class="dashicons dashicons-calendar" style="font-size:14px; color:#666;"></span>
                                <?php echo esc_html( $calendar->name ); ?>
                            <?php else : ?>
                                <span style="color:#999;">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="repro-ct-suite-badge <?php echo esc_attr( $type_class ); ?>" title="<?php echo esc_attr( $tooltip ); ?>">
                                <?php echo esc_html( $type ); ?>
                            </span>
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
                            'calendar_id' => $calendar_filter,
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
