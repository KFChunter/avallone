<?php
/**
 * Homepage — Uued tooted.
 *
 * One large featured product on the left with two stacked cards on the right.
 * Automatic by default (three newest catalogue products); an editor can switch
 * to a manual selection where the chosen order sets the layout.
 *
 * @package Avallone
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'wc_get_products' ) ) {
	return;
}

$avallone_new_page = get_queried_object_id();
$avallone_new_acf  = function_exists( 'get_field' );
$avallone_new_mode = $avallone_new_acf ? (string) get_field( 'new_mode', $avallone_new_page ) : 'auto';
$avallone_new_ids  = array();

if ( 'manual' === $avallone_new_mode && $avallone_new_acf ) {
	$avallone_new_ids = array_map( 'intval', (array) get_field( 'new_products', $avallone_new_page ) );
} else {
	// WooCommerce CRUD rather than a raw WP_Query against product meta.
	$avallone_new_ids = wc_get_products(
		array(
			'status'     => 'publish',
			'visibility' => 'catalog',
			'limit'      => 3,
			'orderby'    => 'date',
			'order'      => 'DESC',
			'return'     => 'ids',
		)
	);
}

$avallone_new_ids = array_slice( array_filter( $avallone_new_ids ), 0, 3 );

if ( ! $avallone_new_ids ) {
	return;
}

$avallone_new_lead  = array_shift( $avallone_new_ids );
$avallone_lead_item = wc_get_product( $avallone_new_lead );

if ( ! $avallone_lead_item instanceof WC_Product ) {
	return;
}

$avallone_new_link  = $avallone_new_acf ? get_field( 'new_link', $avallone_new_page ) : null;
$avallone_new_href  = ! empty( $avallone_new_link['url'] ) ? $avallone_new_link['url'] : ( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : '' );
$avallone_new_label = ! empty( $avallone_new_link['title'] ) ? $avallone_new_link['title'] : __( 'Vaata kõiki', 'avallone' );

$avallone_lead_origin   = avallone_product_attribute( $avallone_lead_item, array( 'pa_paritolu', 'Päritolu', 'Origin' ) );
$avallone_lead_volume   = avallone_product_attribute( $avallone_lead_item, array( 'pa_maht', 'Maht', 'Volume' ) );
$avallone_lead_can_cart = $avallone_lead_item->supports( 'ajax_add_to_cart' )
	&& $avallone_lead_item->is_purchasable()
	&& $avallone_lead_item->is_in_stock();
?>

<section class="home-section home-new" aria-labelledby="home-new-heading">
	<div class="container">

		<div class="home-section__head">
			<h2 class="home-section__title" id="home-new-heading"><?php esc_html_e( 'Uued tooted', 'avallone' ); ?></h2>
			<?php if ( $avallone_new_href ) : ?>
				<a class="home-section__more" href="<?php echo esc_url( $avallone_new_href ); ?>">
					<?php echo esc_html( $avallone_new_label ); ?>
				</a>
			<?php endif; ?>
		</div>

		<div class="home-new__layout">

			<article class="home-new__lead">

				<a class="home-new__lead-media" href="<?php echo esc_url( $avallone_lead_item->get_permalink() ); ?>" tabindex="-1" aria-hidden="true">
					<?php
					$avallone_lead_image = $avallone_lead_item->get_image_id();

					if ( $avallone_lead_image ) {
						echo wp_get_attachment_image(
							$avallone_lead_image,
							'large',
							false,
							array(
								'class'   => 'home-new__lead-image',
								'alt'     => '',
								'loading' => 'lazy',
							)
						);
					}
					?>
				</a>

				<div class="home-new__lead-body">

					<?php if ( '' !== $avallone_lead_origin ) : ?>
						<p class="home-new__lead-origin"><?php echo esc_html( $avallone_lead_origin ); ?></p>
					<?php endif; ?>

					<h3 class="home-new__lead-title">
						<a href="<?php echo esc_url( $avallone_lead_item->get_permalink() ); ?>">
							<?php echo esc_html( $avallone_lead_item->get_name() ); ?>
						</a>
					</h3>

					<p class="home-new__lead-price">
						<span class="home-new__lead-price-value"><?php echo wp_kses_post( $avallone_lead_item->get_price_html() ); ?></span>
						<?php if ( '' !== $avallone_lead_volume ) : ?>
							<span class="home-new__lead-volume"><?php echo esc_html( $avallone_lead_volume ); ?></span>
						<?php endif; ?>
					</p>

					<?php if ( $avallone_lead_can_cart ) : ?>
						<a
							class="btn btn--primary home-new__lead-cart ajax_add_to_cart add_to_cart_button"
							href="<?php echo esc_url( $avallone_lead_item->add_to_cart_url() ); ?>"
							data-product_id="<?php echo esc_attr( $avallone_lead_item->get_id() ); ?>"
							data-quantity="1"
							rel="nofollow"
						>
							<?php avallone_icon( 'cart', array( 'size' => 18 ) ); ?>
							<?php esc_html_e( 'Lisa korvi', 'avallone' ); ?>
						</a>
					<?php else : ?>
						<a class="btn btn--outline home-new__lead-cart" href="<?php echo esc_url( $avallone_lead_item->get_permalink() ); ?>">
							<?php esc_html_e( 'Vaata toodet', 'avallone' ); ?>
						</a>
					<?php endif; ?>

				</div>

			</article>

			<?php if ( $avallone_new_ids ) : ?>
				<div class="home-new__stack">
					<?php
					foreach ( $avallone_new_ids as $avallone_stack_id ) {
						get_template_part(
							'template-parts/components/product-card',
							null,
							array(
								'id'      => $avallone_stack_id,
								'variant' => 'stack',
							)
						);
					}
					?>
				</div>
			<?php endif; ?>

		</div>

	</div>
</section>
