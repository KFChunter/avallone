<?php
/**
 * Homepage — Mega diil.
 *
 * Four WooCommerce product categories as staggered portrait tiles. Name,
 * archive URL, thumbnail and product count all come from the term; the editor
 * only picks which category sits in which slot.
 *
 * @package Avallone
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'get_field' ) || ! taxonomy_exists( 'product_cat' ) ) {
	return;
}

$avallone_deal_page = get_queried_object_id();
$avallone_deal_ids  = array(
	(int) get_field( 'deal_cat_large', $avallone_deal_page ),
	(int) get_field( 'deal_cat_medium', $avallone_deal_page ),
	(int) get_field( 'deal_cat_small_1', $avallone_deal_page ),
	(int) get_field( 'deal_cat_small_2', $avallone_deal_page ),
);

$avallone_deal_terms = array();

foreach ( array_filter( $avallone_deal_ids ) as $avallone_deal_id ) {
	$avallone_term = get_term( $avallone_deal_id, 'product_cat' );

	if ( $avallone_term instanceof WP_Term ) {
		$avallone_deal_terms[] = $avallone_term;
	}
}

if ( ! $avallone_deal_terms ) {
	return;
}

$avallone_deal_link  = get_field( 'deal_link', $avallone_deal_page );
$avallone_deal_href  = ! empty( $avallone_deal_link['url'] ) ? $avallone_deal_link['url'] : ( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : '' );
$avallone_deal_label = ! empty( $avallone_deal_link['title'] ) ? $avallone_deal_link['title'] : __( 'Kõik pakkumised', 'avallone' );
?>

<section class="home-section home-deal" aria-labelledby="home-deal-heading">
	<div class="container">

		<div class="home-section__head">
			<h2 class="home-section__title" id="home-deal-heading"><?php esc_html_e( 'Mega diil', 'avallone' ); ?></h2>
			<?php if ( $avallone_deal_href ) : ?>
				<a class="home-section__more" href="<?php echo esc_url( $avallone_deal_href ); ?>">
					<?php echo esc_html( $avallone_deal_label ); ?>
				</a>
			<?php endif; ?>
		</div>

		<ul class="home-deal__grid" role="list">
			<?php foreach ( $avallone_deal_terms as $avallone_index => $avallone_term ) : ?>
				<?php
				$avallone_thumb = (int) get_term_meta( $avallone_term->term_id, 'thumbnail_id', true );
				$avallone_count = (int) $avallone_term->count;
				?>
				<li class="home-deal__item">
					<a class="home-deal__tile<?php echo $avallone_thumb ? '' : ' home-deal__tile--plain'; ?>" href="<?php echo esc_url( get_term_link( $avallone_term ) ); ?>">

						<?php
						if ( $avallone_thumb ) {
							echo wp_get_attachment_image(
								$avallone_thumb,
								'large',
								false,
								array(
									'class'   => 'home-deal__image',
									'alt'     => '',
									'loading' => 'lazy',
								)
							);
						}
						?>

						<span class="home-deal__body">
							<?php if ( $avallone_count > 0 ) : ?>
								<span class="home-deal__count">
									<?php
									/* translators: %d: number of products in the category. */
									printf( esc_html( _n( '%d toode', '%d toodet', $avallone_count, 'avallone' ) ), (int) $avallone_count );
									?>
								</span>
							<?php endif; ?>
							<span class="home-deal__name"><?php echo esc_html( $avallone_term->name ); ?></span>
						</span>

					</a>
				</li>
			<?php endforeach; ?>
		</ul>

	</div>
</section>
