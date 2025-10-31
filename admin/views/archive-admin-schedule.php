<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

require_once plugin_dir_path( dirname( __FILE__ ) ) . '../includes/repositories/class-repro-ct-suite-repository-base.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . '../includes/repositories/class-repro-ct-suite-schedule-repository.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . '../includes/repositories/class-repro-ct-suite-calendars-repository.php';

$schedule_repo  = new Repro_CT_Suite_Schedule_Repository();
$calendars_repo = new Repro_CT_Suite_Calendars_Repository();

$from        = isset( $_GET['from'] ) ? sanitize_text_field( wp_unslash( $_GET['from'] ) ) : '';
$to          = isset( $_GET['to'] ) ? sanitize_text_field( wp_unslash( $_GET['to'] ) ) : '';
$calendar_id = isset( $_GET['calendar_id'] ) ? sanitize_text_field( wp_unslash( $_GET['calendar_id'] ) ) : '';
$type        = isset( $_GET['type'] ) ? sanitize_text_field( wp_unslash( $_GET['type'] ) ) : '';
$page        = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1;
$per_page    = 25;
$offset      = ( $page - 1 ) * $per_page;

$args = array(
	'from'        => $from ?: null,
	'to'          => $to ?: null,
	'calendar_id' => $calendar_id ?: null,
	'type'        => $type ?: null,
	'limit'       => $per_page,
	'offset'      => $offset,
);

$total = $schedule_repo->count_list( $args );
$items = $schedule_repo->get_list( $args );
$total_pages = $per_page > 0 ? ceil( $total / $per_page ) : 1;

$calendars = $calendars_repo->get_all();
?>
<div class="wrap">
	<h1><?php esc_html_e( 'Terminübersicht', 'repro-ct-suite' ); ?></h1>

	<form method="get" action="">
		<input type="hidden" name="page" value="repro-ct-suite-schedule" />
		<div class="tablenav top" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
			<label>
				<?php esc_html_e( 'Von', 'repro-ct-suite' ); ?>
				<input type="date" name="from" value="<?php echo esc_attr( $from ); ?>" />
			</label>
			<label>
				<?php esc_html_e( 'Bis', 'repro-ct-suite' ); ?>
				<input type="date" name="to" value="<?php echo esc_attr( $to ); ?>" />
			</label>
			<label>
				<?php esc_html_e( 'Kalender (extern)', 'repro-ct-suite' ); ?>
				<select name="calendar_id">
					<option value="">— <?php esc_html_e( 'alle', 'repro-ct-suite' ); ?> —</option>
					<?php foreach ( $calendars as $cal ) : ?>
						<option value="<?php echo esc_attr( $cal->external_id ); ?>" <?php selected( $calendar_id, $cal->external_id ); ?>>
							<?php echo esc_html( $cal->name ); ?> (<?php echo esc_html( $cal->external_id ); ?>)
						</option>
					<?php endforeach; ?>
				</select>
			</label>
			<label>
				<?php esc_html_e( 'Typ', 'repro-ct-suite' ); ?>
				<select name="type">
					<option value="">— <?php esc_html_e( 'alle', 'repro-ct-suite' ); ?> —</option>
					<option value="event" <?php selected( $type, 'event' ); ?>>Event</option>
					<option value="appointment" <?php selected( $type, 'appointment' ); ?>>Appointment</option>
				</select>
			</label>
			<button class="button button-primary" type="submit"><?php esc_html_e( 'Filtern', 'repro-ct-suite' ); ?></button>
		</div>
	</form>

	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Datum/Zeit', 'repro-ct-suite' ); ?></th>
				<th><?php esc_html_e( 'Titel', 'repro-ct-suite' ); ?></th>
				<th><?php esc_html_e( 'Kalender', 'repro-ct-suite' ); ?></th>
				<th><?php esc_html_e( 'Typ', 'repro-ct-suite' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php if ( empty( $items ) ) : ?>
				<tr><td colspan="4"><?php esc_html_e( 'Keine Einträge gefunden.', 'repro-ct-suite' ); ?></td></tr>
			<?php else : ?>
				<?php foreach ( $items as $item ) : ?>
					<tr>
						<td>
							<?php echo esc_html( date_i18n( 'd.m.Y H:i', strtotime( $item->start_datetime ) ) ); ?>
							<?php if ( ! empty( $item->end_datetime ) ) : ?>
								– <?php echo esc_html( date_i18n( 'H:i', strtotime( $item->end_datetime ) ) ); ?>
							<?php endif; ?>
						</td>
						<td>
							<strong><?php echo esc_html( $item->title ); ?></strong>
							<?php if ( ! empty( $item->description ) ) : ?>
								<div class="description" style="color:#666;max-width:600px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
									<?php echo esc_html( wp_strip_all_tags( $item->description ) ); ?>
								</div>
							<?php endif; ?>
						</td>
						<td>
							<?php echo esc_html( $item->calendar_id ?: '–' ); ?>
						</td>
						<td>
							<?php echo esc_html( $item->source_type ); ?>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
	</table>

	<?php if ( $total_pages > 1 ) : ?>
		<div class="tablenav bottom">
			<div class="tablenav-pages">
				<span class="displaying-num"><?php echo esc_html( sprintf( _n( '%d Eintrag', '%d Einträge', $total, 'repro-ct-suite' ), $total ) ); ?></span>
				<span class="pagination-links">
					<?php
						for ( $p = 1; $p <= $total_pages; $p++ ) {
							$url = add_query_arg( array_merge( $_GET, array( 'paged' => $p ) ) );
							$classes = 'page-numbers' . ( $p === $page ? ' current' : '' );
							echo '<a class="' . esc_attr( $classes ) . '" href="' . esc_url( $url ) . '">' . esc_html( $p ) . '</a> ';
						}
					?>
				</span>
			</div>
		</div>
	<?php endif; ?>
</div>
