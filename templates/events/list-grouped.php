<?php
/**
 * Template für gruppierte Event-Liste
 * Timeline Style - inspiriert von Modern Events Calendar
 * 
 * @var array $events      - Array der Events
 * @var array $atts        - Shortcode Attribute
 * @var array $show_fields - Anzuzeigende Felder
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( empty( $events ) ) {
	echo '<div class="rcts-no-events"><svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg><p>Keine Termine gefunden</p></div>';
	return;
}

// Events nach Datum gruppieren
$grouped_events = array();
foreach ( $events as $event ) {
	$date_key = $event->start_date;
	if ( !isset( $grouped_events[ $date_key ] ) ) {
		$grouped_events[ $date_key ] = array();
	}
	$grouped_events[ $date_key ][] = $event;
}
?>

<div class="rcts-events-timeline">
	<?php foreach ( $grouped_events as $date => $date_events ) : 
		$timestamp = strtotime( $date );
		$weekday = date_i18n( 'l', $timestamp );
		$day_month = date_i18n( 'd. F Y', $timestamp );
	?>
		<div class="rcts-timeline-date-group">
			<div class="rcts-timeline-date-header">
				<div class="rcts-timeline-marker"></div>
				<h3 class="rcts-timeline-date">
					<span class="rcts-timeline-weekday"><?php echo esc_html( $weekday ); ?></span>
					<span class="rcts-timeline-daymonth"><?php echo esc_html( $day_month ); ?></span>
				</h3>
			</div>
			
			<div class="rcts-timeline-events">
				<?php foreach ( $date_events as $event ) : 
					// Zeit formatieren
					$time_display = $event->time_formatted ?? 
									($event->start_time ? $event->start_time : '') ??
									($event->start_datetime ? date('H:i', strtotime($event->start_datetime)) : '');
					$end_time_display = $event->end_time_formatted ?? 
										($event->end_time ? $event->end_time : '') ??
										($event->end_datetime ? date('H:i', strtotime($event->end_datetime)) : '');
				?>
					<div class="rcts-timeline-event">
						<?php if ( in_array( 'time', $show_fields ) && !empty( $time_display ) ) : ?>
							<div class="rcts-timeline-time">
								<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
									<circle cx="12" cy="12" r="10"></circle>
									<polyline points="12 6 12 12 16 14"></polyline>
								</svg>
								<span class="rcts-time-range">
									<?php echo esc_html( $time_display ); ?>
									<?php if ( !empty( $end_time_display ) && $end_time_display !== $time_display ) : ?>
										- <?php echo esc_html( $end_time_display ); ?>
									<?php endif; ?>
								</span>
							</div>
						<?php endif; ?>
						
						<div class="rcts-timeline-event-content">
							<h4 class="rcts-timeline-event-title">
								<?php echo esc_html( $event->title ?? $event->name ?? 'Unbenanntes Event' ); ?>
							</h4>
							
							<?php if ( in_array( 'location', $show_fields ) ) : ?>
								<?php $location = $event->location ?? $event->venue ?? ''; ?>
								<?php if ( !empty( $location ) ) : ?>
									<div class="rcts-timeline-location">
										<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
											<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
											<circle cx="12" cy="10" r="3"></circle>
										</svg>
										<?php echo esc_html( $location ); ?>
									</div>
								<?php endif; ?>
							<?php endif; ?>
							
							<?php if ( in_array( 'description', $show_fields ) ) : ?>
								<?php $description = $event->description ?? $event->note ?? $event->notes ?? ''; ?>
								<?php if ( !empty( $description ) ) : ?>
									<div class="rcts-timeline-description">
										<?php 
										$short_desc = wp_strip_all_tags( $description );
										echo esc_html( strlen( $short_desc ) > 100 ? substr( $short_desc, 0, 100 ) . '...' : $short_desc );
										?>
									</div>
								<?php endif; ?>
							<?php endif; ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endforeach; ?>
</div>