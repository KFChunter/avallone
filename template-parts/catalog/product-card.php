<?php
/**
 * Catalogue product card.
 *
 * Larger than the homepage card and laid out differently — image panel, region,
 * name, then a footer row pairing price with the cart button. The homepage card
 * stays exactly as it is; only the PHP helpers are shared.
 *
 * The warm grey behind the bottle is generated here, by the theme. Product
 * photography is transparent, and the image is contained rather than covered so
 * no bottle is ever cropped to fill the panel.
 *
 * @package Avallone
 *
 * @param array $args {
 *     @type int $id Product ID. Defaults to the current post.
 * }
 */

defined( 'ABSPATH' ) || exit;

$avallone_id      = isset( $args['id'] ) ? (int) $args['id'] : get_the_ID();
$avallone_product = function_exists( 'wc_get_product' ) ? wc_get_product( $avallone_id ) : null;

if ( ! $avallone_product instanceof WC_Product ) {
	return;
}

$avallone_region = avallone_product_origin( $avallone_product );
$avallone_volume = avallone_product_attribute( $avallone_product, array( 'pa_maht', 'Maht', 'maht', 'Volume' ) );
$avallone_link   = $avallone_product->get_permalink();
$avallone_image  = $avallone_product->get_image_id();

$avallone_can_cart = $avallone_product->supports( 'ajax_add_to_cart' )
	&& $avallone_product->is_purchasable()
	&& $avallone_product->is_in_stock();
?>

<article class="catalog-card">

	<a class="catalog-card__media" href="<?php echo esc_url( $avallone_link ); ?>" tabindex="-1" aria-hidden="true">
		<?php
		if ( $avallone_image ) {
			echo wp_get_attachment_image(
				$avallone_image,
				'woocommerce_thumbnail',
				false,
				array(
					/* The title link below carries the accessible name. */
					'class'   => 'catalog-card__image',
					'alt'     => '',
					'loading' => 'lazy',
					'sizes'   => '(min-width: 1280px) 311px, (min-width: 1024px) 30vw, (min-width: 768px) 44vw, 90vw',
				)
			);
		}
		?>

		<?php if ( ! $avallone_product->is_in_stock() ) : ?>
			<span class="catalog-card__flag"><?php esc_html_e( 'Otsas', 'avallone' ); ?></span>
		<?php endif; ?>
	</a>

	<div class="catalog-card__body">

		<?php if ( '' !== $avallone_region ) : ?>
			<p class="catalog-card__region"><?php echo esc_html( $avallone_region ); ?></p>
		<?php endif; ?>

		<h2 class="catalog-card__title">
			<a href="<?php echo esc_url( $avallone_link ); ?>"><?php echo esc_html( $avallone_product->get_name() ); ?></a>
		</h2>

		<div class="catalog-card__footer">

			<p class="catalog-card__price">
				<span class="catalog-card__price-value"><?php echo wp_kses_post( $avallone_product->get_price_html() ); ?></span>
				<?php if ( '' !== $avallone_volume ) : ?>
					<span class="catalog-card__volume"><?php echo esc_html( $avallone_volume ); ?></span>
				<?php endif; ?>
			</p>

			<?php if ( $avallone_can_cart ) : ?>
				<a
					class="catalog-card__cart ajax_add_to_cart add_to_cart_button"
					href="<?php echo esc_url( $avallone_product->add_to_cart_url() ); ?>"
					data-product_id="<?php echo esc_attr( $avallone_product->get_id() ); ?>"
					data-quantity="1"
					rel="nofollow"
					aria-label="<?php echo esc_attr( sprintf( /* translators: %s: product name. */ __( 'Lisa korvi: %s', 'avallone' ), $avallone_product->get_name() ) ); ?>"
				>
					<?php avallone_icon( 'cart', array( 'size' => 16 ) ); ?>
					<span><?php esc_html_e( 'Lisa', 'avallone' ); ?></span>
				</a>
			<?php else : ?>
				<a class="catalog-card__cart catalog-card__cart--link" href="<?php echo esc_url( $avallone_link ); ?>">
					<?php esc_html_e( 'Vaata', 'avallone' ); ?>
				</a>
			<?php endif; ?>

		</div>

	</div>

</article>
