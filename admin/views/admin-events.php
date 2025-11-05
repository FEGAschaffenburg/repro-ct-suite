<?php
/**
 * Termine-Übersicht
 *
 * Zeigt alle Termine aus dem neuen unified sync system.
 * Nach dem Rebuild werden alle Termine als Events verwaltet.
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

// Kombinierte Abfrage: Alle Events (inkl. Appointments als Events)
global $wpdb;
$events_table = $wpdb->prefix . 'rcts_events';

// SQL mit prepare() korrekt bauen
$sql = "SELECT id, event_id, appointment_id, calendar_id, title, description, start_datetime, end_datetime FROM {$events_table} WHERE 1=1";

// Filter hinzufügen
if ( ! empty( $from ) ) { 
    $sql .= $wpdb->prepare( ' AND start_datetime >= %s', $from . ' 00:00:00' );
}
if ( ! empty( $to ) ) { 
    $sql .= $wpdb->prepare( ' AND start_datetime <= %s', $to . ' 23:59:59' );
}
if ( ! empty( $calendar_filter ) ) {
    $sql .= $wpdb->prepare( ' AND calendar_id = %s', $calendar_filter );
}

// Sortierung und Paginierung
$sql .= ' ORDER BY start_datetime ASC';
$sql .= $wpdb->prepare( ' LIMIT %d OFFSET %d', (int) $limit, (int) $offset );

$items = $wpdb->get_results( $sql );

// Gesamtanzahl für Pagination
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

// Kalender für Filter-Dropdown
$calendars = $calendars_repo->get_all();
$total_pages = ceil( $total / $limit );

?>
<div class="wrap repro-ct-suite-admin-wrapper">
    <div class="repro-ct-suite-header">
        <h1><span class="dashicons dashicons-calendar-alt"></span> <?php esc_html_e( 'Termine', 'repro-ct-suite' ); ?></h1>
        <p><?php esc_html_e( 'Übersicht aller Termine aus ChurchTools über das neue unified sync system.', 'repro-ct-suite' ); ?></p>
    </div>

    <div class="repro-ct-suite-card repro-ct-suite-mt-20">
        <div class="repro-ct-suite-card-body" style="padding:0;">
            
            <!-- Filter in Tabbar-Form integriert -->
            <form method="get" style="padding: 15px; background: #f9f9f9; border-bottom: 1px solid #ddd;">
                <input type="hidden" name="page" value="repro-ct-suite-events" />
                <div style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
                    <label style="display: flex; flex-direction: column; min-width: 150px;">
                        <span style="font-size: 11px; font-weight: 600; margin-bottom: 4px; color: #666;">
                            <?php esc_html_e( 'Von', 'repro-ct-suite' ); ?>
                        </span>
                        <input type="date" name="from" value="<?php echo esc_attr( $from ); ?>" style="padding: 6px;" />
                    </label>
                    <label style="display: flex; flex-direction: column; min-width: 150px;">
                        <span style="font-size: 11px; font-weight: 600; margin-bottom: 4px; color: #666;">
                            <?php esc_html_e( 'Bis', 'repro-ct-suite' ); ?>
                        </span>
                        <input type="date" name="to" value="<?php echo esc_attr( $to ); ?>" style="padding: 6px;" />
                    </label>
                    <label style="display: flex; flex-direction: column; min-width: 200px; flex: 1;">
                        <span style="font-size: 11px; font-weight: 600; margin-bottom: 4px; color: #666;">
                            <?php esc_html_e( 'Kalender', 'repro-ct-suite' ); ?>
                        </span>
                        <select name="calendar_id" style="padding: 6px;">
                            <option value=""><?php esc_html_e( 'Alle Kalender', 'repro-ct-suite' ); ?></option>
                            <?php foreach ( $calendars as $cal ) : ?>
                                <option value="<?php echo esc_attr( $cal->calendar_id ); ?>" <?php selected( $calendar_filter, $cal->calendar_id ); ?>>
                                    <?php echo esc_html( $cal->name ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <div style="display: flex; gap: 10px; align-items: flex-end;">
                        <button type="submit" class="repro-ct-suite-btn repro-ct-suite-btn-primary" style="white-space: nowrap;">
                            <span class="dashicons dashicons-filter"></span> <?php esc_html_e( 'Filtern', 'repro-ct-suite' ); ?>
                        </button>
                        <?php if ( ! empty( $from ) || ! empty( $to ) || ! empty( $calendar_filter ) ) : ?>
                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=repro-ct-suite-events' ) ); ?>" 
                               class="repro-ct-suite-btn repro-ct-suite-btn-secondary" 
                               style="white-space: nowrap;">
                                <span class="dashicons dashicons-dismiss"></span> <?php esc_html_e( 'Zurücksetzen', 'repro-ct-suite' ); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if ( ! empty( $from ) || ! empty( $to ) || ! empty( $calendar_filter ) ) : ?>
                    <div style="margin-top: 10px; padding: 8px 12px; background: #fff; border-radius: 4px; font-size: 12px; color: #666;">
                        <strong><?php esc_html_e( 'Aktive Filter:', 'repro-ct-suite' ); ?></strong>
                        <?php if ( ! empty( $from ) ) : ?>
                            <span style="margin-left: 10px;">Von: <strong><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $from ) ) ); ?></strong></span>
                        <?php endif; ?>
                        <?php if ( ! empty( $to ) ) : ?>
                            <span style="margin-left: 10px;">Bis: <strong><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $to ) ) ); ?></strong></span>
                        <?php endif; ?>
                        <?php if ( ! empty( $calendar_filter ) ) : 
                            $selected_cal = null;
                            foreach ( $calendars as $cal ) {
                                if ( $cal->calendar_id === $calendar_filter ) {
                                    $selected_cal = $cal;
                                    break;
                                }
                            }
                            if ( $selected_cal ) : ?>
                                <span style="margin-left: 10px;">Kalender: <strong><?php echo esc_html( $selected_cal->name ); ?></strong></span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </form>
            
            <!-- Tabelle -->
            <table class="widefat fixed striped" style="margin: 0;">
                <thead>
                    <tr>
                        <th style="width:10%;"><?php esc_html_e( 'Datum', 'repro-ct-suite' ); ?></th>
                        <th style="width:8%;"><?php esc_html_e( 'Von', 'repro-ct-suite' ); ?></th>
                        <th style="width:8%;"><?php esc_html_e( 'Bis', 'repro-ct-suite' ); ?></th>
                        <th style="width:8%;"><?php esc_html_e( 'Typ', 'repro-ct-suite' ); ?></th>
                        <th style="width:8%;"><?php esc_html_e( 'ID', 'repro-ct-suite' ); ?></th>
                        <th style="width:22%;"><?php esc_html_e( 'Titel', 'repro-ct-suite' ); ?></th>
                        <th style="width:22%;"><?php esc_html_e( 'Beschreibung', 'repro-ct-suite' ); ?></th>
                        <th style="width:12%;"><?php esc_html_e( 'Kalender', 'repro-ct-suite' ); ?></th>
                        <th style="width:6%;"><?php esc_html_e( 'Aktionen', 'repro-ct-suite' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                <?php if ( empty( $items ) ) : ?>
                    <tr><td colspan="9" style="text-align:center; padding:30px;">
                        <?php esc_html_e( 'Keine Termine gefunden. Führen Sie die Synchronisation aus, um Termine zu importieren.', 'repro-ct-suite' ); ?>
                    </td></tr>
                <?php else : foreach ( $items as $item ) : 
                    // Typ bestimmen anhand der event_id-Struktur:
                    // - Event: event_id ist eine reine Zahl (z.B. "2011") - ChurchTools Event-ID
                    // - Termin: event_id ist zusammengesetzt mit Unterstrich (z.B. "5011_20251109_103000")
                    $is_appointment = strpos( $item->event_id, '_' ) !== false;
                    $type_label = $is_appointment ? 'Termin' : 'Event';
                    
                    // ChurchTools-IDs extrahieren:
                    if ( $is_appointment ) {
                        // Bei Appointments:
                        // - appointment_id enthält die ChurchTools Appointment-ID
                        // - event_id ist zusammengesetzt: AppointmentID_Timestamp
                        $appointment_ct_id = $item->appointment_id;
                        $event_ct_id = null; // Appointments haben keine Event-ID
                    } else {
                        // Bei Events:
                        // - event_id enthält die ChurchTools Event-ID
                        // - appointment_id ist NULL
                        $event_ct_id = $item->event_id;
                        $appointment_ct_id = null;
                    }
                    
                    // Kalender holen über calendar_id (calendar_id in Events ist die ChurchTools Kalender-ID)
                    $calendar = $item->calendar_id ? $calendars_repo->get_by_calendar_id( $item->calendar_id ) : null;
                    
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
                    
                    // Kalender holen
                    $calendar = $calendars_repo->get_by_id( $item->calendar_id );
                    
                    // Type badge - Termin (Appointment) = Grün, Event = Blau
                    $type_class = $is_appointment ? 'repro-ct-suite-badge-success' : 'repro-ct-suite-badge-info';
                    $tooltip = $is_appointment ? 'Termin aus ChurchTools Appointments-API' : 'Event aus ChurchTools Events-API';
                    ?>
                    <tr>
                        <td>
                            <strong><?php echo esc_html( $start_dt->format( 'd.m.Y' ) ); ?></strong>
                        </td>
                        <td>
                            <strong><?php echo esc_html( $start_dt->format( 'H:i' ) ); ?></strong> Uhr
                        </td>
                        <td>
                            <?php if ( $end_dt ) : ?>
                                <strong><?php echo esc_html( $end_dt->format( 'H:i' ) ); ?></strong> Uhr
                            <?php else : ?>
                                <span style="color: #999;">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="repro-ct-suite-badge <?php echo esc_attr( $type_class ); ?>" title="<?php echo esc_attr( $tooltip ); ?>">
                                <?php echo esc_html( $type_label ); ?>
                            </span>
                        </td>
                        <td>
                            <?php if ( $is_appointment ) : ?>
                                <strong>A:</strong> <?php echo esc_html( $appointment_ct_id ); ?>
                            <?php else : ?>
                                <strong>E:</strong> <?php echo esc_html( $event_ct_id ); ?>
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
                            <button 
                                class="repro-ct-suite-delete-item-btn" 
                                data-id="<?php echo esc_attr( $item->id ); ?>" 
                                data-type="event"
                                data-title="<?php echo esc_attr( $item->title ); ?>"
                                title="<?php esc_attr_e( 'Löschen', 'repro-ct-suite' ); ?>"
                                style="cursor:pointer; background:#dc3232; color:#fff; border:none; padding:4px 8px; border-radius:3px; font-size:12px;"
                            >
                                <span class="dashicons dashicons-trash" style="font-size:14px; vertical-align:middle;"></span>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if ( $total_pages > 1 ) : ?>
        <div class="tablenav repro-ct-suite-mt-20">
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
