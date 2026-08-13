<?php
/**
 * Block — Ettevõttest.
 *
 * Editorial column on the left, up to two service cards on the right. Every
 * part is optional: a section with only a heading, or only cards, renders
 * cleanly rather than leaving empty containers behind.
 *
 * @package Avallone
 *
 * @param array $block The block settings and attributes.
 */

defined( 'ABSPATH' ) || exit;

$avallone_label = trim( (string) get_field( 'about_label' ) );
$avallone_title = trim( (string) get_field( 'about_title' ) );
$avallone_text  = trim( (string) get_field( 'about_text' ) );
$avallone_cards = (array) get_field( 'about_cards' );

$avallone_cards = array_values(
	array_filter(
		$avallone_cards,
		static function ( $card ) {
			return '' !== trim( (string) ( $card['title'] ?? '' ) )
				|| '' !== trim( (string) ( $card['text'] ?? '' ) );
		}
	)
);

$avallone_preview = avallone_block_is_preview( $block );
$avallone_empty   = '' === $avallone_title && '' === $avallone_text && ! $avallone_cards;

if ( $avallone_empty && ! $avallone_preview ) {
	return;
}

$avallone_heading_id = avallone_block_id( $block, 'title' );
?>

<section
	class="<?php echo esc_attr( avallone_block_classes( $block, 'about-company' ) ); ?>"
	<?php if ( '' !== $avallone_title ) : ?>
		aria-labelledby="<?php echo esc_attr( $avallone_heading_id ); ?>"
	<?php endif; ?>
>
	<div class="container about-company__inner">

		<?php if ( $avallone_empty ) : ?>

			<p class="about-company__placeholder">
				<?php esc_html_e( 'Ettevõttest — lisa pealkiri, tekst ja kuni kaks kaarti.', 'avallone' ); ?>
			</p>

		<?php else : ?>

			<div class="about-company__editorial">

				<?php if ( '' !== $avallone_label ) : ?>
					<p class="about-company__label"><?php echo esc_html( $avallone_label ); ?></p>
				<?php endif; ?>

				<?php if ( '' !== $avallone_title ) : ?>
					<h2 class="about-company__title" id="<?php echo esc_attr( $avallone_heading_id ); ?>">
						<?php echo esc_html( $avallone_title ); ?>
					</h2>
				<?php endif; ?>

				<?php if ( '' !== $avallone_text ) : ?>
					<div class="about-company__text"><?php echo wp_kses_post( $avallone_text ); ?></div>
				<?php endif; ?>

			</div>

			<?php if ( $avallone_cards ) : ?>
				<ul class="about-company__cards" role="list">
					<?php foreach ( $avallone_cards as $avallone_card ) : ?>
						<?php
						$avallone_card_icon  = (string) ( $avallone_card['icon'] ?? '' );
						$avallone_card_title = trim( (string) ( $avallone_card['title'] ?? '' ) );
						$avallone_card_text  = trim( (string) ( $avallone_card['text'] ?? '' ) );
						?>
						<li class="about-company__card">

							<?php if ( '' !== $avallone_card_icon ) : ?>
								<span class="about-company__card-icon" aria-hidden="true">
									<?php avallone_icon( $avallone_card_icon, array( 'size' => 32 ) ); ?>
								</span>
							<?php endif; ?>

							<?php if ( '' !== $avallone_card_title ) : ?>
								<h3 class="about-company__card-title"><?php echo esc_html( $avallone_card_title ); ?></h3>
							<?php endif; ?>

							<?php if ( '' !== $avallone_card_text ) : ?>
								<p class="about-company__card-text"><?php echo esc_html( $avallone_card_text ); ?></p>
							<?php endif; ?>

						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>

		<?php endif; ?>

	</div>
</section>
