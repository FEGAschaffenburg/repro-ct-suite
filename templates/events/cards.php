<?php
/**
 * Template: Kachel-Ansicht
 *
 * Zeigt Events als Kacheln/Cards
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

<div class="rcts-events rcts-events-cards">
	<?php if ( empty( $events ) ) : ?>
		<p class="rcts-no-events">
			<?php esc_html_e( 'Keine Termine gefunden.', 'repro-ct-suite' ); ?>
		</p>
	<?php else : ?>
		<div class="rcts-cards-grid">
			<?php foreach ( $events as $event ) : ?>
				<div class="rcts-event-card" data-event-id="<?php echo esc_attr( $event->id ); ?>">
					
					<?php if ( in_array( 'calendar', $show_fields ) && ! empty( $event->calendar_name ) ) : ?>
						<div class="rcts-card-category" style="<?php echo ! empty( $event->calendar_color ) ? 'background-color: ' . esc_attr( $event->calendar_color ) : ''; ?>">
							<?php echo esc_html( $event->calendar_name ); ?>
						</div>
					<?php endif; ?>
					
					<div class="rcts-card-header">
						<?php if ( in_array( 'date', $show_fields ) || in_array( 'datetime', $show_fields ) ) : ?>
							<div class="rcts-card-date">
								<span class="rcts-date-day">
									<?php echo esc_html( wp_date( 'd', strtotime( $event->start_datetime ) ) ); ?>
								</span>
								<span class="rcts-date-month">
									<?php echo esc_html( wp_date( 'M', strtotime( $event->start_datetime ) ) ); ?>
								</span>
							</div>
						<?php endif; ?>
						
						<div class="rcts-card-title-wrap">
							<?php if ( in_array( 'title', $show_fields ) ) : ?>
								<h3 class="rcts-card-title">
									<?php echo esc_html( $event->name ); ?>
								</h3>
							<?php endif; ?>
							
							<?php if ( in_array( 'time', $show_fields ) || in_array( 'datetime', $show_fields ) ) : ?>
								<div class="rcts-card-time">
									🕐 <?php echo esc_html( $event->start_time ); ?>
									<?php if ( ! empty( $event->end_time ) ) : ?>
										- <?php echo esc_html( $event->end_time ); ?>
									<?php endif; ?>
								</div>
							<?php endif; ?>
						</div>
					</div>
					
					<div class="rcts-card-body">
						<?php if ( in_array( 'location', $show_fields ) && ! empty( $event->location ) ) : ?>
							<div class="rcts-card-location">
								<span class="dashicons dashicons-location"></span>
								<?php echo esc_html( $event->location ); ?>
							</div>
						<?php endif; ?>
						
						<?php if ( in_array( 'description', $show_fields ) && ! empty( $event->description ) ) : ?>
							<div class="rcts-card-description">
								<?php 
								// Beschreibung auf 150 Zeichen kürzen
								$description = wp_strip_all_tags( $event->description );
								echo esc_html( mb_substr( $description, 0, 150 ) );
								if ( mb_strlen( $description ) > 150 ) {
									echo '...';
								}
								?>
							</div>
						<?php endif; ?>
					</div>
					
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</div>
