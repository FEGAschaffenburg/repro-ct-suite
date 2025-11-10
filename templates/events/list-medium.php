<?php
/**
 * Template für mittlere Event-Liste
 * Balanced Design - Datum, Zeit, Titel, Ort
 * 
 * @var array $events      - Array der Events
 * @var array $atts        - Shortcode Attribute
 * @var array $show_fields - Anzuzeigende Felder
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( empty( $events ) ) {
	echo '<p class="rcts-no-events-medium">Keine Termine gefunden</p>';
	return;
}
?>

<div class="rcts-events-medium">
	<?php foreach ( $events as $event ) : 
		$start_date = $event->start_date ?? $event->start_datetime ?? '';
		$date_full = '';
		$weekday = '';
		if ( !empty( $start_date ) ) {
			$timestamp = is_numeric($start_date) ? $start_date : strtotime($start_date);
			$date_full = date_i18n( 'd.m.Y', $timestamp );
			$weekday = date_i18n( 'D', $timestamp );
		}
		
		$time_display = $event->time_formatted ?? 
						($event->start_time ? $event->start_time : '') ??
						($event->start_datetime ? date('H:i', strtotime($event->start_datetime)) : '');
		$end_time_display = $event->end_time_formatted ?? 
							($event->end_time ? $event->end_time : '') ??
							($event->end_datetime ? date('H:i', strtotime($event->end_datetime)) : '');
		
		$location = $event->location ?? $event->venue ?? '';
	?>
		<div class="rcts-medium-item">
			<div class="rcts-medium-date-col">
				<div class="rcts-medium-weekday"><?php echo esc_html( $weekday ); ?></div>
				<div class="rcts-medium-date"><?php echo esc_html( $date_full ); ?></div>
			</div>
			<div class="rcts-medium-content">
				<h4 class="rcts-medium-title"><?php echo esc_html( $event->title ?? $event->name ?? 'Unbenanntes Event' ); ?></h4>
				<div class="rcts-medium-meta">
					<?php if ( !empty( $time_display ) ) : ?>
						<span class="rcts-medium-time">
							<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
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
					<?php if ( !empty( $location ) ) : ?>
						<span class="rcts-medium-location">
							<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
								<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
								<circle cx="12" cy="10" r="3"></circle>
							</svg>
							<?php echo esc_html( $location ); ?>
						</span>
					<?php endif; ?>
				</div>
			</div>
		</div>
	<?php endforeach; ?>
</div>
