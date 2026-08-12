<?php
/**
 * Single product — Avallone layout.
 *
 * The one WooCommerce template this theme overrides. WooCommerce's own
 * single-product.php still supplies get_header()/get_footer() and the container
 * wrapper, so the product view applies automatically to every product permalink
 * with nothing to assign.
 *
 * The stock summary hooks are deliberately not fired — their order and markup
 * are what this design replaces. The two that carry behaviour rather than
 * layout are called explicitly: WooCommerce's own add-to-cart template, which
 * dispatches by product type so simple, variable, grouped and external products
 * all keep working, and its structured data.
 *
 * @see woocommerce/templates/content-single-product.php
 * @package Avallone
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( post_password_required() ) {
	echo get_the_password_form(); // phpcs:ignore WordPress.Security.EscapingOutput.OutputNotEscaped -- Core markup.

	return;
}

/**
 * Notices — "product added to cart" lands here after the form posts.
 *
 * @hooked woocommerce_output_all_notices - 10
 */
do_action( 'woocommerce_before_single_product' );

if ( ! $product instanceof WC_Product ) {
	return;
}

// Keeps product schema in the page even though the summary hooks are skipped.
if ( isset( WC()->structured_data ) ) {
	WC()->structured_data->generate_product_data( $product );
}
?>

<div id="product-<?php the_ID(); ?>" <?php wc_product_class( 'product-detail', $product ); ?>>

	<div class="product-detail__top">
		<?php
		get_template_part( 'template-parts/product/gallery', null, array( 'product' => $product ) );
		get_template_part( 'template-parts/product/summary', null, array( 'product' => $product ) );
		?>
	</div>

	<?php
	get_template_part( 'template-parts/product/description', null, array( 'product' => $product ) );
	get_template_part( 'template-parts/product/producer', null, array( 'product' => $product ) );
	get_template_part( 'template-parts/product/data', null, array( 'product' => $product ) );
	get_template_part( 'template-parts/product/related', null, array( 'product' => $product ) );
	?>

</div>

<?php
do_action( 'woocommerce_after_single_product' );
