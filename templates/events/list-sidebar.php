<?php
/**
 * Template für Sidebar Event-Liste
 * Sidebar Widget Style - ultra kompakt für schmale Bereiche
 * 
 * @var array $events      - Array der Events
 * @var array $atts        - Shortcode Attribute
 * @var array $show_fields - Anzuzeigende Felder
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( empty( $events ) ) {
	echo '<p class="rcts-no-events-sidebar">Keine Termine</p>';
	return;
}
?>

<div class="rcts-events-sidebar">
	<?php foreach ( $events as $event ) : 
		$start_date = $event->start_date ?? $event->start_datetime ?? '';
		$month = '';
		$day = '';
		if ( !empty( $start_date ) ) {
			$timestamp = is_numeric($start_date) ? $start_date : strtotime($start_date);
			$month = date_i18n( 'M', $timestamp );
			$day = date_i18n( 'j', $timestamp );
		}
		
		$time_display = $event->time_formatted ?? 
						($event->start_time ? $event->start_time : '') ??
						($event->start_datetime ? date('H:i', strtotime($event->start_datetime)) : '');
	?>
		<div class="rcts-sidebar-item">
			<div class="rcts-sidebar-date" style="<?php echo !empty($event->calendar_color) ? 'background: linear-gradient(135deg, ' . esc_attr($event->calendar_color) . ' 0%, ' . esc_attr($event->calendar_color) . 'dd 100%);' : ''; ?>">
				<span class="rcts-sidebar-day"><?php echo esc_html( $day ); ?></span>
				<span class="rcts-sidebar-month"><?php echo esc_html( $month ); ?></span>
			</div>
			<div class="rcts-sidebar-info">
				<div class="rcts-sidebar-title"><?php echo esc_html( $event->title ?? $event->name ?? 'Unbenannt' ); ?></div>
				<?php if ( !empty( $time_display ) ) : ?>
					<div class="rcts-sidebar-time"><?php echo esc_html( $time_display ); ?> Uhr</div>
				<?php endif; ?>
			</div>
		</div>
	<?php endforeach; ?>
</div>
