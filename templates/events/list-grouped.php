<?php
/**
 * Template: Liste mit Datum-Gruppierung
 *
 * Zeigt Events gruppiert nach Datum
 *
 * @package    Repro_CT_Suite
 * @subpackage Repro_CT_Suite/templates
 *
 * @var array $events       Events-Array
 * @var array $atts         Shortcode-Attribute
 * @var array $show_fields  Anzuzeigende Felder
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Events nach Datum gruppieren
$events_by_date = array();
foreach ( $events as $event ) {
	$date_key = $event->start_date;
	if ( ! isset( $events_by_date[ $date_key ] ) ) {
		$events_by_date[ $date_key ] = array(
			'date_formatted' => $event->date_formatted,
			'events'         => array(),
		);
	}
	$events_by_date[ $date_key ]['events'][] = $event;
}
?>

<div class="rcts-events rcts-events-list-grouped">
	<?php if ( empty( $events ) ) : ?>
		<p class="rcts-no-events">
			<?php esc_html_e( 'Keine Termine gefunden.', 'repro-ct-suite' ); ?>
		</p>
	<?php else : ?>
		<?php foreach ( $events_by_date as $date => $group ) : ?>
			<div class="rcts-date-group">
				<h3 class="rcts-date-header">
					<?php echo esc_html( wp_date( 'l, j. F Y', strtotime( $date ) ) ); ?>
				</h3>
				
				<ul class="rcts-events-list">
					<?php foreach ( $group['events'] as $event ) : ?>
						<li class="rcts-event-item" data-event-id="<?php echo esc_attr( $event->id ); ?>">
							
							<div class="rcts-event-time-title">
								<?php if ( in_array( 'time', $show_fields ) || in_array( 'datetime', $show_fields ) ) : ?>
									<span class="rcts-event-time">
										<?php echo esc_html( $event->start_time ); ?>
										<?php if ( ! empty( $event->end_time ) ) : ?>
											- <?php echo esc_html( $event->end_time ); ?>
										<?php endif; ?>
									</span>
								<?php endif; ?>
								
							<?php if ( in_array( 'title', $show_fields ) ) : ?>
								<strong class="rcts-event-title">
									<?php echo esc_html( $event->title ); ?>
								</strong>
							<?php endif; ?>
						</div>
						
						<div class="rcts-event-meta">
							<?php if ( in_array( 'location', $show_fields ) && ! empty( $event->location_name ) ) : ?>
								<span class="rcts-event-location">
									📍 <?php echo esc_html( $event->location_name ); ?>
								</span>
							<?php endif; ?>								<?php if ( in_array( 'calendar', $show_fields ) && ! empty( $event->calendar_name ) ) : ?>
									<span class="rcts-event-calendar" style="<?php echo ! empty( $event->calendar_color ) ? 'color: ' . esc_attr( $event->calendar_color ) : ''; ?>">
										● <?php echo esc_html( $event->calendar_name ); ?>
									</span>
								<?php endif; ?>
							</div>
							
							<?php if ( in_array( 'description', $show_fields ) && ! empty( $event->description ) ) : ?>
								<div class="rcts-event-description">
									<?php echo wp_kses_post( wpautop( $event->description ) ); ?>
								</div>
							<?php endif; ?>
							
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endforeach; ?>
	<?php endif; ?>
</div>
