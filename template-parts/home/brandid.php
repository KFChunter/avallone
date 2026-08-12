<?php
/**
 * Homepage — Brändid.
 *
 * Real logos from WooCommerce's native `product_brand` taxonomy. The editor
 * picks the brands; the logo comes from each term's own image, so nothing is
 * duplicated into ACF. With no brands selected the whole section is skipped.
 *
 * @package Avallone
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'get_field' ) || ! taxonomy_exists( 'product_brand' ) ) {
	return;
}

$avallone_brand_ids = array_filter( array_map( 'intval', (array) get_field( 'brands_terms', get_queried_object_id() ) ) );

if ( ! $avallone_brand_ids ) {
	return;
}

$avallone_brands = array();

foreach ( $avallone_brand_ids as $avallone_brand_id ) {
	$avallone_brand_term = get_term( $avallone_brand_id, 'product_brand' );

	if ( ! $avallone_brand_term instanceof WP_Term ) {
		continue;
	}

	$avallone_brands[] = array(
		'term' => $avallone_brand_term,
		'logo' => (int) get_term_meta( $avallone_brand_term->term_id, 'thumbnail_id', true ),
	);
}

if ( ! $avallone_brands ) {
	return;
}
?>

<section class="home-brands" aria-labelledby="home-brands-heading">
	<div class="container">

		<h2 class="home-brands__title" id="home-brands-heading"><?php esc_html_e( 'Brändid', 'avallone' ); ?></h2>

		<ul class="home-brands__list" role="list">
			<?php foreach ( $avallone_brands as $avallone_brand ) : ?>
				<li class="home-brands__item">
					<a class="home-brands__link" href="<?php echo esc_url( get_term_link( $avallone_brand['term'] ) ); ?>">
						<?php
						if ( $avallone_brand['logo'] ) {
							echo wp_get_attachment_image(
								$avallone_brand['logo'],
								'medium',
								false,
								array(
									'class'   => 'home-brands__logo',
									/* The visible mark carries the name, so the image itself is decorative. */
									'alt'     => '',
									'loading' => 'lazy',
								)
							);
							?>
							<span class="screen-reader-text"><?php echo esc_html( $avallone_brand['term']->name ); ?></span>
							<?php
						} else {
							?>
							<span class="home-brands__name"><?php echo esc_html( $avallone_brand['term']->name ); ?></span>
							<?php
						}
						?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>

	</div>
</section>
