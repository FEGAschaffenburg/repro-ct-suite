<?php
/**
 * Template für Event-Karten
 * Card Grid Style - inspiriert von Eventbrite & Modern Events Calendar
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

<div class="rcts-events-grid">
	<?php foreach ( $events as $event ) : 
		// Datum-Teile
		$start_date = $event->start_date ?? $event->start_datetime ?? '';
		$month = '';
		$day = '';
		$weekday = '';
		if ( !empty( $start_date ) ) {
			$timestamp = is_numeric($start_date) ? $start_date : strtotime($start_date);
			$month = date_i18n( 'M', $timestamp );
			$day = date_i18n( 'j', $timestamp );
			$weekday = date_i18n( 'D', $timestamp );
		}
		
		// Zeit formatieren
		$time_display = $event->time_formatted ?? 
						($event->start_time ? $event->start_time : '') ??
						($event->start_datetime ? date('H:i', strtotime($event->start_datetime)) : '');
		$end_time_display = $event->end_time_formatted ?? 
							($event->end_time ? $event->end_time : '') ??
							($event->end_datetime ? date('H:i', strtotime($event->end_datetime)) : '');
	?>
		<article class="rcts-event-card-modern">
			<div class="rcts-card-date-badge" style="<?php echo !empty($event->calendar_color) ? 'background: linear-gradient(135deg, ' . esc_attr($event->calendar_color) . ' 0%, ' . esc_attr($event->calendar_color) . 'dd 100%);' : ''; ?>">
				<div class="rcts-badge-content">
					<span class="rcts-badge-month"><?php echo esc_html( $month ); ?></span>
					<span class="rcts-badge-day"><?php echo esc_html( $day ); ?></span>
					<span class="rcts-badge-weekday"><?php echo esc_html( $weekday ); ?></span>
				</div>
			</div>
			
			<div class="rcts-card-body-modern">
				<h3 class="rcts-card-title">
					<?php echo esc_html( $event->title ?? $event->name ?? 'Unbenanntes Event' ); ?>
				</h3>
				
				<div class="rcts-card-meta">
					<?php if ( in_array( 'time', $show_fields ) && !empty( $time_display ) ) : ?>
						<div class="rcts-meta-item rcts-meta-time">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
								<circle cx="12" cy="12" r="10"></circle>
								<polyline points="12 6 12 12 16 14"></polyline>
							</svg>
							<span>
								<?php echo esc_html( $time_display ); ?>
								<?php if ( !empty( $end_time_display ) && $end_time_display !== $time_display ) : ?>
									- <?php echo esc_html( $end_time_display ); ?> Uhr
								<?php else : ?>
									Uhr
								<?php endif; ?>
							</span>
						</div>
					<?php endif; ?>
					
					<?php if ( in_array( 'location', $show_fields ) ) : ?>
						<?php $location = $event->location ?? $event->venue ?? ''; ?>
						<?php if ( !empty( $location ) ) : ?>
							<div class="rcts-meta-item rcts-meta-location">
								<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
									<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
									<circle cx="12" cy="10" r="3"></circle>
								</svg>
								<span><?php echo esc_html( $location ); ?></span>
							</div>
						<?php endif; ?>
					<?php endif; ?>
				</div>
				
				<?php if ( in_array( 'description', $show_fields ) ) : ?>
					<?php $description = $event->description ?? $event->note ?? $event->notes ?? ''; ?>
					<?php if ( !empty( $description ) ) : ?>
						<div class="rcts-card-description">
							<?php 
							$short_desc = wp_strip_all_tags( $description );
							if ( strlen( $short_desc ) > 100 ) {
								echo esc_html( substr( $short_desc, 0, 100 ) . '...' );
							} else {
								echo esc_html( $short_desc );
							}
							?>
						</div>
					<?php endif; ?>
				<?php endif; ?>
			</div>
			
			<div class="rcts-card-footer">
				<span class="rcts-card-category">Veranstaltung</span>
			</div>
		</article>
	<?php endforeach; ?>
</div>