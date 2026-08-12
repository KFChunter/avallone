<?php
/**
 * Single product — "Toote andmed".
 *
 * Every row resolves to a real WooCommerce attribute, taxonomy or native field.
 * Rows without a value are dropped rather than rendered empty, so the grid
 * reflects what the product actually knows about itself.
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

$avallone_rows = avallone_product_data_rows( $avallone_product );

if ( ! $avallone_rows ) {
	return;
}
?>

<section class="product-section product-data" aria-labelledby="product-data-heading">
	<h2 class="product-section__title" id="product-data-heading">
		<?php esc_html_e( 'Toote andmed', 'avallone' ); ?>
	</h2>

	<dl class="product-data__grid">
		<?php foreach ( $avallone_rows as $avallone_row ) : ?>
			<div class="product-data__cell">
				<span class="product-data__icon" aria-hidden="true">
					<?php avallone_icon( $avallone_row['icon'], array( 'size' => 18 ) ); ?>
				</span>
				<div class="product-data__pair">
					<dt class="product-data__label"><?php echo esc_html( $avallone_row['label'] ); ?></dt>
					<dd class="product-data__value"><?php echo esc_html( $avallone_row['value'] ); ?></dd>
				</div>
			</div>
		<?php endforeach; ?>
	</dl>
</section>
