<?php
/**
 * Termine-Übersicht
 *
 * Zeigt alle synchronisierten Events (aus Events API und Appointments API).
 * Optional mit Datumsfilter und Paginierung.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

require_once plugin_dir_path( dirname( __DIR__ ) ) . 'includes/repositories/class-repro-ct-suite-repository-base.php';
require_once plugin_dir_path( dirname( __DIR__ ) ) . 'includes/repositories/class-repro-ct-suite-events-repository.php';
require_once plugin_dir_path( dirname( __DIR__ ) ) . 'includes/repositories/class-repro-ct-suite-event-services-repository.php';

$events_repo = new Repro_CT_Suite_Events_Repository();
$svc_repo    = new Repro_CT_Suite_Event_Services_Repository();

// Filter
$from   = isset( $_GET['from'] ) ? sanitize_text_field( wp_unslash( $_GET['from'] ) ) : '';
$to     = isset( $_GET['to'] ) ? sanitize_text_field( wp_unslash( $_GET['to'] ) ) : '';
$page   = max( 1, (int) ( $_GET['paged'] ?? 1 ) );
$limit  = 25;
$offset = ($page - 1) * $limit;

// Events holen
global $wpdb;
$events_table = $wpdb->prefix . 'rcts_events';

// SQL mit prepare() korrekt bauen
$sql = "SELECT id, event_id, appointment_id, calendar_id, title, description, start_datetime, end_datetime, location_name FROM {$events_table} WHERE 1=1";

// Filter hinzufügen
if ( ! empty( $from ) ) { 
    $sql .= $wpdb->prepare( ' AND start_datetime >= %s', $from . ' 00:00:00' );
}
if ( ! empty( $to ) ) { 
    $sql .= $wpdb->prepare( ' AND start_datetime <= %s', $to . ' 23:59:59' );
}

// Sortierung und Paginierung
$sql .= ' ORDER BY start_datetime ASC';
$sql .= $wpdb->prepare( ' LIMIT %d OFFSET %d', (int) $limit, (int) $offset );

$items = $wpdb->get_results( $sql );

// Debug: SQL-Query und Anzahl loggen
error_log( 'Termine-Filter SQL: ' . $sql );
error_log( 'Termine gefunden: ' . count( $items ) );

?>
<div class="wrap repro-ct-suite-admin-wrapper">
    <div class="repro-ct-suite-header">
        <h1><span class="dashicons dashicons-calendar-alt"></span> <?php esc_html_e( 'Termine', 'repro-ct-suite' ); ?></h1>
        <p><?php esc_html_e( 'Übersicht aller synchronisierten Events (aus Events API und Appointments API).', 'repro-ct-suite' ); ?></p>
    </div>

    <form method="get" class="repro-ct-suite-mt-10">
        <input type="hidden" name="page" value="repro-ct-suite-appointments" />
        <label>
            <?php esc_html_e( 'Von', 'repro-ct-suite' ); ?>
            <input type="date" name="from" value="<?php echo esc_attr( $from ); ?>" />
        </label>
        <label style="margin-left:10px;">
            <?php esc_html_e( 'Bis', 'repro-ct-suite' ); ?>
            <input type="date" name="to" value="<?php echo esc_attr( $to ); ?>" />
        </label>
        <button type="submit" class="repro-ct-suite-btn repro-ct-suite-btn-secondary" style="margin-left:10px;">
            <span class="dashicons dashicons-filter"></span> <?php esc_html_e( 'Filtern', 'repro-ct-suite' ); ?>
        </button>
        <?php if ( ! empty( $from ) || ! empty( $to ) ) : ?>
            <a href="?page=repro-ct-suite-appointments" class="repro-ct-suite-btn" style="margin-left:10px; text-decoration:none;">
                <span class="dashicons dashicons-dismiss"></span> <?php esc_html_e( 'Filter entfernen', 'repro-ct-suite' ); ?>
            </a>
        <?php endif; ?>
    </form>

    <div class="repro-ct-suite-card repro-ct-suite-mt-20">
        <div class="repro-ct-suite-card-header">
            <h3><?php esc_html_e( 'Übersicht', 'repro-ct-suite' ); ?></h3>
        </div>
        <div class="repro-ct-suite-card-body">
            <table class="widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width:12%;"><?php esc_html_e( 'Datum', 'repro-ct-suite' ); ?></th>
                        <th style="width:8%;"><?php esc_html_e( 'Typ', 'repro-ct-suite' ); ?></th>
                        <th style="width:8%;"><?php esc_html_e( 'ID', 'repro-ct-suite' ); ?></th>
                        <th style="width:30%;"><?php esc_html_e( 'Titel', 'repro-ct-suite' ); ?></th>
                        <th style="width:17%;"><?php esc_html_e( 'Ort', 'repro-ct-suite' ); ?></th>
                        <th style="width:12%;"><?php esc_html_e( 'Ende', 'repro-ct-suite' ); ?></th>
                        <th style="width:8%;"><?php esc_html_e( 'Aktionen', 'repro-ct-suite' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                <?php if ( empty( $items ) ) : ?>
                    <tr><td colspan="7"><?php esc_html_e( 'Keine Einträge gefunden.', 'repro-ct-suite' ); ?></td></tr>
                <?php else : foreach ( $items as $row ) : 
                    // Typ bestimmen: 
                    // - Event: Hat appointment_id gesetzt (Event aus dem ein Appointment entstanden ist)
                    // - Appointment: Hat keine appointment_id (reines Appointment ohne Event-Verknüpfung)
                    $is_appointment = empty( $row->appointment_id );
                    $type_label = $is_appointment ? 'Appointment' : 'Event';
                    
                    // ChurchTools-IDs extrahieren:
                    // Event-ID ist immer in external_id enthalten (Format: EventID_Timestamp)
                    $parts = explode( '_', $row->event_id );
                    $event_ct_id = $parts[0];
                    
                    // Appointment-ID ist nur bei Events gesetzt (nicht bei reinen Appointments)
                    $appointment_ct_id = ! $is_appointment ? $row->appointment_id : null;
                ?>
                    <tr>
                        <td><?php echo esc_html( date_i18n( get_option('date_format') . ' H:i', strtotime( $row->start_datetime ) ) ); ?></td>
                        <td>
                            <?php if ( $is_appointment ) : ?>
                                <span class="repro-ct-suite-badge" style="background:#667eea; color:#fff;"><?php echo esc_html( $type_label ); ?></span>
                            <?php else : ?>
                                <span class="repro-ct-suite-badge repro-ct-suite-badge-info"><?php echo esc_html( $type_label ); ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <small>
                                <?php if ( $appointment_ct_id ) : ?>
                                    <strong>A:</strong> <?php echo esc_html( $appointment_ct_id ); ?><br>
                                <?php endif; ?>
                                <strong>E:</strong> <?php echo esc_html( $event_ct_id ); ?>
                            </small>
                        </td>
                        <td><?php echo esc_html( $row->title ); ?></td>
                        <td><?php echo isset( $row->location_name ) ? esc_html( $row->location_name ) : ''; ?></td>
                        <td><?php echo ! empty( $row->end_datetime ) ? esc_html( date_i18n( get_option('date_format') . ' H:i', strtotime( $row->end_datetime ) ) ) : '—'; ?></td>
                        <td>
                            <button 
                                class="repro-ct-suite-delete-item-btn" 
                                data-id="<?php echo esc_attr( $row->id ); ?>" 
                                data-type="event"
                                data-title="<?php echo esc_attr( $row->title ); ?>"
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
</div>
