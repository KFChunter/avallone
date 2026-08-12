<?php
/**
 * Single product — producer band.
 *
 * Driven entirely by the shared product_brand taxonomy: the term's name, its
 * WordPress description, and one ACF tagline field on the term. Nothing about
 * the producer is stored per product, so a brand's story is written once and
 * every one of its products shows it.
 *
 * Without a brand, or with nothing to say about it, the band is skipped.
 *
 * @package Avallone
 *
 * @param array $args {
 *     @type WC_Product $product Product.
 * }
 */

defined( 'ABSPATH' ) || exit;

$avallone_product = isset( $args['product'] ) ? $args['product'] : null;

if ( ! $avallone_product instanceof WC_Product ) {
	return;
}

$avallone_brand = avallone_product_brand_term( $avallone_product );

if ( ! $avallone_brand instanceof WP_Term ) {
	return;
}

$avallone_tagline = function_exists( 'get_field' )
	? trim( (string) get_field( 'brand_tagline', 'product_brand_' . $avallone_brand->term_id ) )
	: '';

$avallone_story = trim( (string) $avallone_brand->description );

// A name alone is already in the data grid; the band needs something to say.
if ( '' === $avallone_story && '' === $avallone_tagline ) {
	return;
}
?>

<section class="product-producer" aria-labelledby="product-producer-heading">
	<div class="product-producer__inner">

		<div class="product-producer__intro">
			<p class="product-producer__eyebrow"><?php esc_html_e( 'Tootja kirjeldus', 'avallone' ); ?></p>

			<h2 class="product-producer__name" id="product-producer-heading">
				<?php echo esc_html( $avallone_brand->name ); ?>
			</h2>

			<?php if ( '' !== $avallone_tagline ) : ?>
				<p class="product-producer__tagline"><?php echo esc_html( $avallone_tagline ); ?></p>
			<?php endif; ?>
		</div>

		<?php if ( '' !== $avallone_story ) : ?>
			<div class="product-producer__story">
				<?php echo wp_kses_post( wpautop( $avallone_story ) ); ?>
			</div>
		<?php endif; ?>

	</div>
</section>
