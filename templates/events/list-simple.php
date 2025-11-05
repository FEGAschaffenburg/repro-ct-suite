<?php
/**
 * Template: Liste Einfach
 *
 * Zeigt Events als einfache Liste
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
?>

<div class="rcts-events rcts-events-list-simple">
	<?php if ( empty( $events ) ) : ?>
		<p class="rcts-no-events">
			<?php esc_html_e( 'Keine Termine gefunden.', 'repro-ct-suite' ); ?>
		</p>
	<?php else : ?>
		<ul class="rcts-events-list">
			<?php foreach ( $events as $event ) : ?>
				<li class="rcts-event-item" data-event-id="<?php echo esc_attr( $event->id ); ?>">
					
					<?php if ( in_array( 'calendar', $show_fields ) && ! empty( $event->calendar_name ) ) : ?>
						<span class="rcts-event-calendar" style="<?php echo ! empty( $event->calendar_color ) ? 'color: ' . esc_attr( $event->calendar_color ) : ''; ?>">
							● <?php echo esc_html( $event->calendar_name ); ?>
						</span>
					<?php endif; ?>
					
					<?php if ( in_array( 'title', $show_fields ) ) : ?>
						<strong class="rcts-event-title">
							<?php echo esc_html( $event->title ); ?>
						</strong>
					<?php endif; ?>
					
					<div class="rcts-event-meta">
						<?php if ( in_array( 'date', $show_fields ) || in_array( 'datetime', $show_fields ) ) : ?>
							<span class="rcts-event-date">
								📅 <?php echo esc_html( $event->date_formatted ); ?>
							</span>
						<?php endif; ?>
						
						<?php if ( in_array( 'time', $show_fields ) || in_array( 'datetime', $show_fields ) ) : ?>
							<span class="rcts-event-time">
								🕐 <?php echo esc_html( $event->start_time ); ?>
								<?php if ( ! empty( $event->end_time ) ) : ?>
									- <?php echo esc_html( $event->end_time ); ?>
								<?php endif; ?>
							</span>
						<?php endif; ?>
						
					<?php if ( in_array( 'location', $show_fields ) && ! empty( $event->location_name ) ) : ?>
						<span class="rcts-event-location">
							📍 <?php echo esc_html( $event->location_name ); ?>
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
	<?php endif; ?>
</div>
