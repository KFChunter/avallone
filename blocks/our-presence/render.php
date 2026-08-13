<?php
/**
 * Block — Meie asukoht.
 *
 * Editorial column with opening hours and address, beside a pair of interior
 * photographs. Every part is optional, so a section with one image, or with no
 * hours, still renders cleanly.
 *
 * @package Avallone
 *
 * @param array $block The block settings and attributes.
 */

defined( 'ABSPATH' ) || exit;

$avallone_title       = trim( (string) get_field( 'presence_title' ) );
$avallone_text        = trim( (string) get_field( 'presence_text' ) );
$avallone_hours_label = trim( (string) get_field( 'presence_hours_label' ) );
$avallone_hours       = trim( (string) get_field( 'presence_hours' ) );
$avallone_address     = trim( (string) get_field( 'presence_address' ) );

$avallone_images = array_values(
	array_filter(
		array(
			(int) get_field( 'presence_image_1' ),
			(int) get_field( 'presence_image_2' ),
		)
	)
);

$avallone_has_details = '' !== $avallone_hours_label || '' !== $avallone_hours || '' !== $avallone_address;

$avallone_preview = avallone_block_is_preview( $block );
$avallone_empty   = '' === $avallone_title && '' === $avallone_text && ! $avallone_has_details && ! $avallone_images;

if ( $avallone_empty && ! $avallone_preview ) {
	return;
}

$avallone_heading_id = avallone_block_id( $block, 'title' );

/* One image should fill the column rather than sit in a half-width gap. */
$avallone_media_class = 'our-presence__media';

if ( 1 === count( $avallone_images ) ) {
	$avallone_media_class .= ' our-presence__media--single';
}
?>

<section
	class="<?php echo esc_attr( avallone_block_classes( $block, 'our-presence' ) ); ?>"
	<?php if ( '' !== $avallone_title ) : ?>
		aria-labelledby="<?php echo esc_attr( $avallone_heading_id ); ?>"
	<?php endif; ?>
>
	<div class="container our-presence__inner">

		<?php if ( $avallone_empty ) : ?>

			<p class="our-presence__placeholder">
				<?php esc_html_e( 'Meie asukoht — lisa pealkiri, lahtiolekuajad, aadress ja pildid.', 'avallone' ); ?>
			</p>

		<?php else : ?>

			<div class="our-presence__editorial">

				<?php if ( '' !== $avallone_title ) : ?>
					<h2 class="our-presence__title" id="<?php echo esc_attr( $avallone_heading_id ); ?>">
						<?php echo esc_html( $avallone_title ); ?>
					</h2>
				<?php endif; ?>

				<?php if ( '' !== $avallone_text ) : ?>
					<p class="our-presence__text"><?php echo esc_html( $avallone_text ); ?></p>
				<?php endif; ?>

				<?php if ( $avallone_has_details ) : ?>
					<div class="our-presence__details">

						<?php if ( '' !== $avallone_hours_label ) : ?>
							<p class="our-presence__hours-label"><?php echo esc_html( $avallone_hours_label ); ?></p>
						<?php endif; ?>

						<?php if ( '' !== $avallone_hours ) : ?>
							<p class="our-presence__hours"><?php echo esc_html( $avallone_hours ); ?></p>
						<?php endif; ?>

						<?php if ( '' !== $avallone_address ) : ?>
							<?php
							/*
							 * A postal address is address content, not a byline; the
							 * field stores its own line breaks.
							 */
							?>
							<address class="our-presence__address"><?php echo wp_kses_post( $avallone_address ); ?></address>
						<?php endif; ?>

					</div>
				<?php endif; ?>

			</div>

			<?php if ( $avallone_images ) : ?>
				<div class="<?php echo esc_attr( $avallone_media_class ); ?>">
					<?php foreach ( $avallone_images as $avallone_image ) : ?>
						<?php
						/*
						 * cover, not contain: these are environmental photographs, so
						 * filling the frame is right — unlike bottles and wordmarks.
						 */
						echo wp_get_attachment_image(
							$avallone_image,
							'large',
							false,
							array(
								'class'   => 'our-presence__image',
								'loading' => 'lazy',
								'sizes'   => '(min-width: 1024px) 25vw, (min-width: 768px) 45vw, 45vw',
							)
						);
						?>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

		<?php endif; ?>

	</div>
</section>
