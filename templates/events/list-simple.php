<?php
/**
 * Template für Event-Liste (einfach)
 * Modern Event Calendar Style - inspiriert von The Events Calendar
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
?>

<div class="rcts-events-list-modern">
	<?php foreach ( $events as $event ) : 
		// Datum-Teile für schöne Anzeige
		$start_date = $event->start_date ?? $event->start_datetime ?? '';
		$month = '';
		$day = '';
		if ( !empty( $start_date ) ) {
			$timestamp = is_numeric($start_date) ? $start_date : strtotime($start_date);
			$month = date_i18n( 'M', $timestamp );
			$day = date_i18n( 'j', $timestamp );
		}
		
		// Zeit formatieren
		$time_display = $event->time_formatted ?? 
						($event->start_time ? $event->start_time : '') ??
						($event->start_datetime ? date('H:i', strtotime($event->start_datetime)) : '');
		$end_time_display = $event->end_time_formatted ?? 
							($event->end_time ? $event->end_time : '') ??
							($event->end_datetime ? date('H:i', strtotime($event->end_datetime)) : '');
	?>
		<article class="rcts-event-item">
			<div class="rcts-event-date-box">
				<span class="rcts-event-month"><?php echo esc_html( $month ); ?></span>
				<span class="rcts-event-day"><?php echo esc_html( $day ); ?></span>
			</div>
			
			<div class="rcts-event-content">
				<h3 class="rcts-event-title">
					<?php echo esc_html( $event->title ?? $event->name ?? 'Unbenanntes Event' ); ?>
				</h3>
				
				<div class="rcts-event-details">
					<?php if ( in_array( 'time', $show_fields ) && !empty( $time_display ) ) : ?>
						<span class="rcts-event-time-badge">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
								<circle cx="12" cy="12" r="10"></circle>
								<polyline points="12 6 12 12 16 14"></polyline>
							</svg>
							<?php echo esc_html( $time_display ); ?>
							<?php if ( !empty( $end_time_display ) && $end_time_display !== $time_display ) : ?>
								- <?php echo esc_html( $end_time_display ); ?> Uhr
							<?php else : ?>
								Uhr
							<?php endif; ?>
						</span>
					<?php endif; ?>
					
					<?php if ( in_array( 'location', $show_fields ) ) : ?>
						<?php $location = $event->location ?? $event->venue ?? ''; ?>
						<?php if ( !empty( $location ) ) : ?>
							<span class="rcts-event-location-badge">
								<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
									<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
									<circle cx="12" cy="10" r="3"></circle>
								</svg>
								<?php echo esc_html( $location ); ?>
							</span>
						<?php endif; ?>
					<?php endif; ?>
				</div>
				
				<?php if ( in_array( 'description', $show_fields ) ) : ?>
					<?php $description = $event->description ?? $event->note ?? $event->notes ?? ''; ?>
					<?php if ( !empty( $description ) ) : ?>
						<div class="rcts-event-excerpt">
							<?php 
							// Kurze Beschreibung (erste 120 Zeichen)
							$short_desc = wp_strip_all_tags( $description );
							if ( strlen( $short_desc ) > 120 ) {
								echo esc_html( substr( $short_desc, 0, 120 ) . '...' );
							} else {
								echo esc_html( $short_desc );
							}
							?>
						</div>
					<?php endif; ?>
				<?php endif; ?>
			</div>
		</article>
	<?php endforeach; ?>
</div>