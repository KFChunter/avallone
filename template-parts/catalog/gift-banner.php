<?php
/**
 * Catalogue promo — gift set banner.
 *
 * A wide band spanning the full grid width, between product rows. Skipped
 * entirely when the editor turns it off or leaves it empty.
 *
 * @package Avallone
 *
 * @param array $args {
 *     @type int $page Page ID holding the field values.
 * }
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'get_field' ) ) {
	return;
}

$avallone_page = isset( $args['page'] ) ? (int) $args['page'] : get_queried_object_id();

if ( ! avallone_catalog_flag( 'vein_gift_show', $avallone_page ) ) {
	return;
}

$avallone_label  = trim( (string) get_field( 'vein_gift_label', $avallone_page ) );
$avallone_title  = trim( (string) get_field( 'vein_gift_title', $avallone_page ) );
$avallone_text   = trim( (string) get_field( 'vein_gift_text', $avallone_page ) );
$avallone_link   = get_field( 'vein_gift_link', $avallone_page );
$avallone_images = array_filter( array_map( 'intval', (array) get_field( 'vein_gift_images', $avallone_page ) ) );

if ( '' === $avallone_title && '' === $avallone_text && ! $avallone_images ) {
	return;
}

// The design fans out three; more than that would not read as a group.
$avallone_images = array_slice( $avallone_images, 0, 3 );
?>

<aside class="catalog-promo catalog-promo--gift">

	<div class="catalog-promo__text">

		<?php if ( '' !== $avallone_label ) : ?>
			<p class="catalog-promo__label">
				<?php avallone_icon( 'sparkle', array( 'size' => 14 ) ); ?>
				<span><?php echo esc_html( $avallone_label ); ?></span>
			</p>
		<?php endif; ?>

		<?php if ( '' !== $avallone_title ) : ?>
			<h2 class="catalog-promo__title"><?php echo esc_html( $avallone_title ); ?></h2>
		<?php endif; ?>

		<?php if ( '' !== $avallone_text ) : ?>
			<p class="catalog-promo__body"><?php echo esc_html( $avallone_text ); ?></p>
		<?php endif; ?>

	</div>

	<?php if ( $avallone_images ) : ?>
		<ul class="catalog-promo__gallery" role="list">
			<?php foreach ( $avallone_images as $avallone_image ) : ?>
				<li class="catalog-promo__gallery-item">
					<?php
					echo wp_get_attachment_image(
						$avallone_image,
						'medium',
						false,
						array(
							'class'   => 'catalog-promo__gallery-image',
							'alt'     => '',
							'loading' => 'lazy',
							'sizes'   => '(min-width: 1024px) 120px, 25vw',
						)
					);
					?>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>

	<?php if ( ! empty( $avallone_link['url'] ) ) : ?>
		<a
			class="catalog-promo__cta catalog-promo__cta--button"
			href="<?php echo esc_url( $avallone_link['url'] ); ?>"
			<?php if ( ! empty( $avallone_link['target'] ) ) : ?>
				target="<?php echo esc_attr( $avallone_link['target'] ); ?>" rel="noopener"
			<?php endif; ?>
		>
			<?php
			echo esc_html(
				! empty( $avallone_link['title'] ) ? $avallone_link['title'] : __( 'Vaata kinkekomplekte', 'avallone' )
			);
			?>
		</a>
	<?php endif; ?>

</aside>
