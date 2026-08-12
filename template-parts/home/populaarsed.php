<?php
/**
 * Homepage — Populaarsed tooted.
 *
 * Four products in a grid. Automatic mode orders by real WooCommerce sales;
 * manual mode preserves the editor's chosen order. Cards are the shared
 * product-card partial, so pricing, sale state and add-to-cart behave exactly
 * as they do in "Uued tooted".
 *
 * @package Avallone
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'wc_get_products' ) ) {
	return;
}

$avallone_pop_page = get_queried_object_id();
$avallone_pop_acf  = function_exists( 'get_field' );
$avallone_pop_mode = $avallone_pop_acf ? (string) get_field( 'popular_mode', $avallone_pop_page ) : 'auto';
$avallone_pop_ids  = array();

if ( 'manual' === $avallone_pop_mode && $avallone_pop_acf ) {
	$avallone_pop_ids = array_map( 'intval', (array) get_field( 'popular_products', $avallone_pop_page ) );
} else {
	$avallone_pop_args = array(
		'status'     => 'publish',
		'visibility' => 'catalog',
		'limit'      => 4,
		'order'      => 'DESC',
		'return'     => 'ids',
	);

	/*
	 * Sorting by sales has to go through the total_sales meta. wc_get_products()
	 * silently ignores orderby => 'popularity' (and 'total_sales'): that mapping
	 * lives in WC_Query's catalog ordering, which only applies to the shop loop.
	 * total_sales is the same meta WooCommerce's own "sort by popularity" reads.
	 */
	$avallone_pop_best = wc_get_products(
		$avallone_pop_args + array(
			'orderby'  => 'meta_value_num',
			'meta_key' => 'total_sales', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- WooCommerce's own popularity meta.
		)
	);

	/*
	 * With no order history every product ties on zero and the order is
	 * arbitrary, so fall back to newest — predictable during development and
	 * self-correcting once real sales exist.
	 */
	$avallone_pop_any_sales = wc_get_products(
		array(
			'status'     => 'publish',
			'visibility' => 'catalog',
			'limit'      => 1,
			'return'     => 'ids',
			'meta_key'   => 'total_sales', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value' => 0,             // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			'meta_compare' => '>',
		)
	);

	$avallone_pop_ids = $avallone_pop_any_sales
		? $avallone_pop_best
		: wc_get_products( $avallone_pop_args + array( 'orderby' => 'date' ) );
}

$avallone_pop_ids = array_slice( array_filter( $avallone_pop_ids ), 0, 4 );

if ( ! $avallone_pop_ids ) {
	return;
}

$avallone_pop_link  = $avallone_pop_acf ? get_field( 'popular_link', $avallone_pop_page ) : null;
$avallone_pop_href  = ! empty( $avallone_pop_link['url'] ) ? $avallone_pop_link['url'] : ( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : '' );
$avallone_pop_label = ! empty( $avallone_pop_link['title'] ) ? $avallone_pop_link['title'] : __( 'Vaata kõiki', 'avallone' );
?>

<section class="home-section home-popular" aria-labelledby="home-popular-heading">
	<div class="container">

		<div class="home-section__head">
			<h2 class="home-section__title" id="home-popular-heading"><?php esc_html_e( 'Populaarsed tooted', 'avallone' ); ?></h2>
			<?php if ( $avallone_pop_href ) : ?>
				<a class="home-section__more" href="<?php echo esc_url( $avallone_pop_href ); ?>">
					<?php echo esc_html( $avallone_pop_label ); ?>
				</a>
			<?php endif; ?>
		</div>

		<div class="home-popular__grid">
			<?php
			foreach ( $avallone_pop_ids as $avallone_pop_card ) {
				get_template_part(
					'template-parts/components/product-card',
					null,
					array(
						'id'      => $avallone_pop_card,
						'variant' => 'grid',
					)
				);
			}
			?>
		</div>

	</div>
</section>
