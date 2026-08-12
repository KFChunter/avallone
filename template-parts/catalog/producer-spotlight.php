<?php
/**
 * Catalogue promo — producer spotlight.
 *
 * A deep-red block that sits inside the product grid, spanning two columns on
 * desktop. Skipped entirely when the editor turns it off or leaves it empty.
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

if ( ! avallone_catalog_flag( 'vein_spotlight_show', $avallone_page ) ) {
	return;
}

$avallone_label = trim( (string) get_field( 'vein_spotlight_label', $avallone_page ) );
$avallone_title = trim( (string) get_field( 'vein_spotlight_title', $avallone_page ) );
$avallone_text  = trim( (string) get_field( 'vein_spotlight_text', $avallone_page ) );
$avallone_link  = get_field( 'vein_spotlight_link', $avallone_page );
$avallone_image = (int) get_field( 'vein_spotlight_image', $avallone_page );

// Nothing to say and nothing to show.
if ( '' === $avallone_title && '' === $avallone_text && ! $avallone_image ) {
	return;
}
?>

<aside class="catalog-promo catalog-promo--spotlight">

	<div class="catalog-promo__text">

		<?php if ( '' !== $avallone_label ) : ?>
			<p class="catalog-promo__label">
				<?php avallone_icon( 'award', array( 'size' => 14 ) ); ?>
				<span><?php echo esc_html( $avallone_label ); ?></span>
			</p>
		<?php endif; ?>

		<?php if ( '' !== $avallone_title ) : ?>
			<h2 class="catalog-promo__title"><?php echo esc_html( $avallone_title ); ?></h2>
		<?php endif; ?>

		<?php if ( '' !== $avallone_text ) : ?>
			<p class="catalog-promo__body"><?php echo esc_html( $avallone_text ); ?></p>
		<?php endif; ?>

		<?php
		/* No link selected means no link rendered — never an empty anchor. */
		if ( ! empty( $avallone_link['url'] ) ) :
			?>
			<a
				class="catalog-promo__cta"
				href="<?php echo esc_url( $avallone_link['url'] ); ?>"
				<?php if ( ! empty( $avallone_link['target'] ) ) : ?>
					target="<?php echo esc_attr( $avallone_link['target'] ); ?>" rel="noopener"
				<?php endif; ?>
			>
				<?php
				echo esc_html(
					! empty( $avallone_link['title'] ) ? $avallone_link['title'] : __( 'Vaata lähemalt', 'avallone' )
				);
				?>
			</a>
		<?php endif; ?>

	</div>

	<?php if ( $avallone_image ) : ?>
		<div class="catalog-promo__media">
			<span class="catalog-promo__glow" aria-hidden="true"></span>
			<?php
			echo wp_get_attachment_image(
				$avallone_image,
				'medium_large',
				false,
				array(
					'class'   => 'catalog-promo__image',
					'alt'     => '',
					'loading' => 'lazy',
					'sizes'   => '(min-width: 1280px) 260px, (min-width: 768px) 30vw, 60vw',
				)
			);
			?>
		</div>
	<?php endif; ?>

</aside>
