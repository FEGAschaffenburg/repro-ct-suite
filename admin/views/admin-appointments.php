<?php
/**
 * Konsolidierte Übersicht "Termine"
 *
 * Zeigt alle Events sowie Appointments ohne Event-Verknüpfung in einer Liste.
 * Optional mit Datumsfilter und einfacher Paginierung.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

require_once plugin_dir_path( dirname( __DIR__ ) ) . 'includes/repositories/class-repro-ct-suite-repository-base.php';
require_once plugin_dir_path( dirname( __DIR__ ) ) . 'includes/repositories/class-repro-ct-suite-events-repository.php';
require_once plugin_dir_path( dirname( __DIR__ ) ) . 'includes/repositories/class-repro-ct-suite-appointments-repository.php';
require_once plugin_dir_path( dirname( __DIR__ ) ) . 'includes/repositories/class-repro-ct-suite-event-services-repository.php';

$events_repo = new Repro_CT_Suite_Events_Repository();
$apps_repo   = new Repro_CT_Suite_Appointments_Repository();
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
$where = 'WHERE 1=1';
$params = array();
if ( ! empty( $from ) ) { $where .= ' AND start_datetime >= %s'; $params[] = $from; }
if ( ! empty( $to ) )   { $where .= ' AND start_datetime <= %s'; $params[] = $to; }
$params[] = (int) $limit;
$params[] = (int) $offset;
$sql_events = $wpdb->prepare(
    "SELECT id, external_id, title, description, start_datetime, end_datetime, location_name, 'event' AS kind FROM {$events_table} {$where} ORDER BY start_datetime ASC LIMIT %d OFFSET %d",
    ...$params
);
$events = $wpdb->get_results( $sql_events );

// Appointments ohne Event holen
$apps = $apps_repo->query_without_event( array(
    'from'   => $from ?: null,
    'to'     => $to ?: null,
    'limit'  => $limit,
    'offset' => 0, // wir mischen unten; für echte Paginierung müsste man UNION/SQL bauen
) );
foreach ( $apps as $a ) {
    $a->kind = 'appointment';
}

// Mischen und Sortieren (einfach, clientseitig)
$items = array_merge( $events, $apps );
usort( $items, function( $a, $b ) {
    return strcmp( $a->start_datetime, $b->start_datetime );
});
$items = array_slice( $items, 0, $limit );

?>
<div class="wrap repro-ct-suite-admin-wrapper">
    <div class="repro-ct-suite-header">
        <h1><span class="dashicons dashicons-calendar-alt"></span> <?php esc_html_e( 'Termine', 'repro-ct-suite' ); ?></h1>
        <p><?php esc_html_e( 'Konsolidierte Übersicht aller Events sowie Appointments ohne Event-Eintrag.', 'repro-ct-suite' ); ?></p>
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
        <button class="repro-ct-suite-btn repro-ct-suite-btn-secondary" style="margin-left:10px;">
            <span class="dashicons dashicons-filter"></span> <?php esc_html_e( 'Filtern', 'repro-ct-suite' ); ?>
        </button>
    </form>

    <div class="repro-ct-suite-card repro-ct-suite-mt-20">
        <div class="repro-ct-suite-card-header">
            <h3><?php esc_html_e( 'Übersicht', 'repro-ct-suite' ); ?></h3>
        </div>
        <div class="repro-ct-suite-card-body">
            <table class="widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width:15%;"><?php esc_html_e( 'Datum', 'repro-ct-suite' ); ?></th>
                        <th style="width:12%;"><?php esc_html_e( 'Typ', 'repro-ct-suite' ); ?></th>
                        <th style="width:30%;"><?php esc_html_e( 'Titel', 'repro-ct-suite' ); ?></th>
                        <th style="width:20%;"><?php esc_html_e( 'Ort', 'repro-ct-suite' ); ?></th>
                        <th style="width:15%;"><?php esc_html_e( 'Ende', 'repro-ct-suite' ); ?></th>
                        <th style="width:8%;"><?php esc_html_e( 'Aktionen', 'repro-ct-suite' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                <?php if ( empty( $items ) ) : ?>
                    <tr><td colspan="6"><?php esc_html_e( 'Keine Einträge gefunden.', 'repro-ct-suite' ); ?></td></tr>
                <?php else : foreach ( $items as $row ) : ?>
                    <tr>
                        <td><?php echo esc_html( date_i18n( get_option('date_format') . ' H:i', strtotime( $row->start_datetime ) ) ); ?></td>
                        <td>
                            <?php if ( $row->kind === 'event' ) : ?>
                                <span class="repro-ct-suite-badge repro-ct-suite-badge-info"><?php esc_html_e( 'Event', 'repro-ct-suite' ); ?></span>
                            <?php else : ?>
                                <span class="repro-ct-suite-badge"><?php esc_html_e( 'Appointment', 'repro-ct-suite' ); ?></span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo esc_html( $row->title ); ?></td>
                        <td><?php echo isset( $row->location_name ) ? esc_html( $row->location_name ) : ''; ?></td>
                        <td><?php echo ! empty( $row->end_datetime ) ? esc_html( date_i18n( get_option('date_format') . ' H:i', strtotime( $row->end_datetime ) ) ) : '—'; ?></td>
                        <td>
                            <button 
                                class="repro-ct-suite-delete-item-btn" 
                                data-id="<?php echo esc_attr( $row->id ); ?>" 
                                data-type="<?php echo esc_attr( $row->kind ); ?>"
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
