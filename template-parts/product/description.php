<?php
/**
 * Single product — "Toote kirjeldus".
 *
 * WooCommerce's main product description. There is no ACF duplicate of it, and
 * the stock Description tab is unhooked on this view so it appears exactly once.
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

$avallone_description = $avallone_product->get_description();

if ( '' === trim( (string) $avallone_description ) ) {
	return;
}
?>

<section class="product-section product-description" aria-labelledby="product-description-heading">
	<h2 class="product-section__title" id="product-description-heading">
		<?php esc_html_e( 'Toote kirjeldus', 'avallone' ); ?>
	</h2>

	<div class="product-description__body">
		<?php
		/* The editor's rich text, filtered exactly as WordPress would render it. */
		echo wp_kses_post( apply_filters( 'the_content', $avallone_description ) );
		?>
	</div>
</section>
