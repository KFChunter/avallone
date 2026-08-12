<?php
/**
 * Product card.
 *
 * Shared by the "Uued tooted" stack and the "Populaarsed tooted" grid — the same
 * card with a different width. Everything shown comes from WC_Product.
 *
 * @package Avallone
 *
 * @param array $args {
 *     @type int    $id      Product ID. Required.
 *     @type string $variant 'stack' (narrow, beside the featured product) or
 *                           'grid' (four-up). Default 'grid'.
 * }
 */

defined( 'ABSPATH' ) || exit;

$avallone_card_id = isset( $args['id'] ) ? (int) $args['id'] : 0;
$avallone_variant = isset( $args['variant'] ) ? $args['variant'] : 'grid';
$avallone_item    = $avallone_card_id && function_exists( 'wc_get_product' ) ? wc_get_product( $avallone_card_id ) : null;

if ( ! $avallone_item instanceof WC_Product || 'publish' !== get_post_status( $avallone_card_id ) ) {
	return;
}

$avallone_card_origin = avallone_product_origin( $avallone_item );
$avallone_card_volume = avallone_product_attribute( $avallone_item, array( 'pa_maht', 'Maht', 'Volume' ) );

/*
 * A discount is only ever shown when the product really is on sale and both
 * prices are numeric — never assumed from the presence of a regular price.
 */
$avallone_discount = 0;

if ( $avallone_item->is_on_sale() ) {
	$avallone_regular = (float) $avallone_item->get_regular_price();
	$avallone_now     = (float) $avallone_item->get_price();

	if ( $avallone_regular > 0 && $avallone_now > 0 && $avallone_now < $avallone_regular ) {
		$avallone_discount = (int) round( ( 1 - $avallone_now / $avallone_regular ) * 100 );
	}
}

$avallone_can_cart = $avallone_item->supports( 'ajax_add_to_cart' )
	&& $avallone_item->is_purchasable()
	&& $avallone_item->is_in_stock();
?>

<article class="product-card product-card--<?php echo esc_attr( $avallone_variant ); ?>">

	<a class="product-card__media" href="<?php echo esc_url( $avallone_item->get_permalink() ); ?>" tabindex="-1" aria-hidden="true">
		<?php
		$avallone_card_image = $avallone_item->get_image_id();

		if ( $avallone_card_image ) {
			echo wp_get_attachment_image(
				$avallone_card_image,
				'woocommerce_thumbnail',
				false,
				array(
					'class'   => 'product-card__image',
					'alt'     => '',
					'loading' => 'lazy',
				)
			);
		}
		?>
		<?php if ( $avallone_discount > 0 ) : ?>
			<span class="product-card__badge">-<?php echo esc_html( $avallone_discount ); ?>%</span>
		<?php endif; ?>
	</a>

	<div class="product-card__body">

		<?php if ( '' !== $avallone_card_origin ) : ?>
			<p class="product-card__origin"><?php echo esc_html( $avallone_card_origin ); ?></p>
		<?php endif; ?>

		<h3 class="product-card__title">
			<a href="<?php echo esc_url( $avallone_item->get_permalink() ); ?>">
				<?php echo esc_html( $avallone_item->get_name() ); ?>
			</a>
		</h3>

		<p class="product-card__price">
			<span class="product-card__price-value"><?php echo wp_kses_post( $avallone_item->get_price_html() ); ?></span>
			<?php if ( '' !== $avallone_card_volume ) : ?>
				<span class="product-card__volume"><?php echo esc_html( $avallone_card_volume ); ?></span>
			<?php endif; ?>
		</p>

		<?php if ( $avallone_can_cart ) : ?>
			<a
				class="product-card__cart ajax_add_to_cart add_to_cart_button"
				href="<?php echo esc_url( $avallone_item->add_to_cart_url() ); ?>"
				data-product_id="<?php echo esc_attr( $avallone_item->get_id() ); ?>"
				data-quantity="1"
				rel="nofollow"
				aria-label="<?php echo esc_attr( sprintf( __( 'Lisa korvi: %s', 'avallone' ), $avallone_item->get_name() ) ); ?>"
			>
				<?php avallone_icon( 'cart', array( 'size' => 16 ) ); ?>
				<span class="product-card__cart-label"><?php esc_html_e( 'Lisa', 'avallone' ); ?></span>
			</a>
		<?php else : ?>
			<a class="product-card__cart product-card__cart--link" href="<?php echo esc_url( $avallone_item->get_permalink() ); ?>">
				<?php esc_html_e( 'Vaata', 'avallone' ); ?>
			</a>
		<?php endif; ?>

	</div>

</article>
