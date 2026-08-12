<?php
/**
 * Single product — "Sarnased tooted".
 *
 * WooCommerce's own up-sells when the shop has set them, its related-products
 * logic otherwise. The current product is excluded and unpublished links are
 * dropped, so the row never points at a dead page.
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

$avallone_ids = avallone_product_related_ids( $avallone_product, 4 );

if ( ! $avallone_ids ) {
	return;
}

$avallone_category = avallone_product_primary_category( $avallone_product );
$avallone_archive  = $avallone_category ? get_term_link( $avallone_category ) : '';
?>

<section class="product-section product-related" aria-labelledby="product-related-heading">

	<div class="product-related__head">
		<h2 class="product-section__title" id="product-related-heading">
			<?php esc_html_e( 'Sarnased tooted', 'avallone' ); ?>
		</h2>

		<?php if ( $avallone_archive && ! is_wp_error( $avallone_archive ) ) : ?>
			<a class="product-related__more" href="<?php echo esc_url( $avallone_archive ); ?>">
				<?php esc_html_e( 'Vaata kõiki', 'avallone' ); ?>
			</a>
		<?php endif; ?>
	</div>

	<div class="product-related__grid">
		<?php
		foreach ( $avallone_ids as $avallone_related ) {
			get_template_part(
				'template-parts/catalog/product-card',
				null,
				array( 'id' => $avallone_related )
			);
		}
		?>
	</div>

</section>
