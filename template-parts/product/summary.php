<?php
/**
 * Single product — purchase column.
 *
 * Badges, title, price, the real WooCommerce add-to-cart form, short
 * description, food pairing and the four highlighted attributes.
 *
 * The cart form is WooCommerce's own template function rather than a rebuilt
 * form, so product type, stock, sold-individually and quantity rules all come
 * from WooCommerce and only the styling is ours.
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

$avallone_id       = $avallone_product->get_id();
$avallone_badges   = avallone_product_badges( $avallone_product );
$avallone_discount = avallone_product_discount_percent( $avallone_product );
$avallone_litre    = avallone_product_price_per_litre( $avallone_product );
$avallone_short    = $avallone_product->get_short_description();
$avallone_pairing  = function_exists( 'get_field' ) ? trim( (string) get_field( 'product_food_pairing', $avallone_id ) ) : '';
$avallone_quick    = avallone_product_data_rows( $avallone_product, avallone_product_quick_keys() );
?>

<div class="product-summary">

	<?php if ( $avallone_badges ) : ?>
		<ul class="product-summary__badges" role="list">
			<?php foreach ( $avallone_badges as $avallone_badge ) : ?>
				<li class="product-summary__badge"><?php echo esc_html( $avallone_badge ); ?></li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>

	<h1 class="product-summary__title"><?php the_title(); ?></h1>

	<div class="product-summary__price-row">

		<?php if ( $avallone_discount > 0 ) : ?>
			<span class="product-summary__discount-chip" aria-hidden="true">
				<?php avallone_icon( 'percent', array( 'size' => 14 ) ); ?>
			</span>
		<?php endif; ?>

		<p class="product-summary__price"><?php echo wp_kses_post( $avallone_product->get_price_html() ); ?></p>

		<?php if ( '' !== $avallone_litre ) : ?>
			<p class="product-summary__per-litre"><?php echo esc_html( $avallone_litre ); ?></p>
		<?php endif; ?>

	</div>

	<?php if ( $avallone_discount > 0 ) : ?>
		<p class="product-summary__discount">
			<?php
			printf(
				/* translators: %s: discount percentage, already formatted. */
				esc_html__( 'Hind on kuvatud Sulle juba %s soodushinnaga.', 'avallone' ),
				'<strong>-' . esc_html( $avallone_discount ) . '%</strong>'
			);
			?>
		</p>
	<?php endif; ?>

	<div class="product-summary__cart">
		<?php
		/*
		 * WooCommerce's own add-to-cart template. It dispatches on product type,
		 * so variations, stock state, sold-individually and min/max/step
		 * quantities are WooCommerce's behaviour, not a reimplementation.
		 */
		woocommerce_template_single_add_to_cart();
		?>
	</div>

	<?php if ( $avallone_short ) : ?>
		<div class="product-summary__short">
			<?php echo wp_kses_post( wpautop( $avallone_short ) ); ?>
		</div>
	<?php endif; ?>

	<?php if ( '' !== $avallone_pairing ) : ?>
		<div class="product-summary__pairing">
			<h2 class="product-summary__pairing-title"><?php esc_html_e( 'Toidusoovitus', 'avallone' ); ?></h2>
			<div class="product-summary__pairing-body"><?php echo wp_kses_post( wpautop( $avallone_pairing ) ); ?></div>
		</div>
	<?php endif; ?>

	<?php if ( $avallone_quick ) : ?>
		<ul class="product-summary__quick" role="list">
			<?php foreach ( $avallone_quick as $avallone_item ) : ?>
				<li class="product-summary__quick-item">
					<span class="product-summary__quick-icon" aria-hidden="true">
						<?php avallone_icon( $avallone_item['icon'], array( 'size' => 18 ) ); ?>
					</span>
					<span class="product-summary__quick-label"><?php echo esc_html( $avallone_item['label'] ); ?></span>
					<span class="product-summary__quick-value"><?php echo esc_html( $avallone_item['value'] ); ?></span>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>

</div>
