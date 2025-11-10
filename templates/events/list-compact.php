<?php
/**
 * Template für sehr kompakte Event-Liste
 * Minimal Design - nur Datum, Zeit, Titel
 * 
 * @var array $events      - Array der Events
 * @var array $atts        - Shortcode Attribute
 * @var array $show_fields - Anzuzeigende Felder
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( empty( $events ) ) {
	echo '<p class="rcts-no-events-compact">Keine Termine</p>';
	return;
}
?>

<ul class="rcts-events-compact">
	<?php foreach ( $events as $event ) : 
		$start_date = $event->start_date ?? $event->start_datetime ?? '';
		$date_short = '';
		if ( !empty( $start_date ) ) {
			$timestamp = is_numeric($start_date) ? $start_date : strtotime($start_date);
			$date_short = date_i18n( 'd.m.', $timestamp );
		}
		
		$time_display = $event->time_formatted ?? 
						($event->start_time ? $event->start_time : '') ??
						($event->start_datetime ? date('H:i', strtotime($event->start_datetime)) : '');
		$end_time_display = $event->end_time_formatted ?? 
							($event->end_time ? $event->end_time : '') ??
							($event->end_datetime ? date('H:i', strtotime($event->end_datetime)) : '');
	?>
		<li class="rcts-compact-item">
			<span class="rcts-compact-date"><?php echo esc_html( $date_short ); ?></span>
			<?php if ( !empty( $time_display ) ) : ?>
				<span class="rcts-compact-time">
					<?php echo esc_html( $time_display ); ?>
					<?php if ( !empty( $end_time_display ) && $end_time_display !== $time_display ) : ?>
						-<?php echo esc_html( $end_time_display ); ?>
					<?php endif; ?>
				</span>
			<?php endif; ?>
			<span class="rcts-compact-title"><?php echo esc_html( $event->title ?? $event->name ?? 'Unbenannt' ); ?></span>
		</li>
	<?php endforeach; ?>
</ul>
