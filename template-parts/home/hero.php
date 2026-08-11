<?php
/**
 * Homepage hero.
 *
 * Content comes from the "Avallone" ACF group (tab: Avaleht — Hero). The
 * featured product is selected by ID; its name, image, price and URL are read
 * from WooCommerce so an editor never retypes them.
 *
 * The background is a layer rather than the container: the <img> sits behind
 * the inner grid and the overlay lives on its ::after, so softening the
 * photograph never fades the heading, card or buttons.
 *
 * Variables are prefixed because a template part shares scope with its parent
 * — in particular $product is a WooCommerce global and must not be shadowed.
 *
 * @package Avallone
 */

defined( 'ABSPATH' ) || exit;

$avallone_page_id = get_queried_object_id();
$avallone_has_acf = function_exists( 'get_field' );

$avallone_bg_id       = $avallone_has_acf ? (int) get_field( 'hero_background', $avallone_page_id ) : 0;
$avallone_badge       = $avallone_has_acf ? (string) get_field( 'hero_badge', $avallone_page_id ) : '';
$avallone_heading     = $avallone_has_acf ? (string) get_field( 'hero_heading', $avallone_page_id ) : '';
$avallone_accent      = $avallone_has_acf ? (string) get_field( 'hero_heading_accent', $avallone_page_id ) : '';
$avallone_description = $avallone_has_acf ? (string) get_field( 'hero_description', $avallone_page_id ) : '';
$avallone_cta_one     = $avallone_has_acf ? get_field( 'hero_cta_primary', $avallone_page_id ) : null;
$avallone_cta_two     = $avallone_has_acf ? get_field( 'hero_cta_secondary', $avallone_page_id ) : null;

/*
 * Resolve the featured product. Anything unexpected — no selection, a deleted
 * or unpublished product, WooCommerce inactive — leaves $avallone_wine null and
 * the card is simply not rendered.
 */
$avallone_wine    = null;
$avallone_wine_id = $avallone_has_acf ? (int) get_field( 'hero_product', $avallone_page_id ) : 0;

if ( $avallone_wine_id && function_exists( 'wc_get_product' ) ) {
	$avallone_candidate = wc_get_product( $avallone_wine_id );

	if ( $avallone_candidate instanceof WC_Product && 'publish' === get_post_status( $avallone_wine_id ) ) {
		$avallone_wine = $avallone_candidate;
	}
}

/*
 * Product metadata. The product's own attributes win; the hero override fields
 * are only consulted when the product carries nothing.
 */
$avallone_origin  = avallone_product_attribute( $avallone_wine, array( 'pa_paritolu', 'Päritolu', 'Origin' ) );
$avallone_vintage = avallone_product_attribute( $avallone_wine, array( 'pa_aastakaik', 'Aastakäik', 'Vintage' ) );
$avallone_volume  = avallone_product_attribute( $avallone_wine, array( 'pa_maht', 'Maht', 'Volume' ) );

if ( $avallone_has_acf ) {
	$avallone_origin  = '' !== $avallone_origin ? $avallone_origin : (string) get_field( 'hero_product_origin', $avallone_page_id );
	$avallone_vintage = '' !== $avallone_vintage ? $avallone_vintage : (string) get_field( 'hero_product_vintage', $avallone_page_id );
	$avallone_volume  = '' !== $avallone_volume ? $avallone_volume : (string) get_field( 'hero_product_volume', $avallone_page_id );
}

/* Joined with a separator only where both halves exist, so no dangling bullet. */
$avallone_wine_meta = implode( ' • ', array_filter( array( $avallone_origin, $avallone_vintage ) ) );
?>

<section class="home-hero<?php echo $avallone_wine ? '' : ' home-hero--no-product'; ?>">

	<div class="home-hero__bg" aria-hidden="true">
		<?php
		if ( $avallone_bg_id ) {
			echo wp_get_attachment_image(
				$avallone_bg_id,
				'full',
				false,
				array(
					'class'         => 'home-hero__bg-image',
					'alt'           => '',
					'loading'       => 'eager',
					'fetchpriority' => 'high',
					'decoding'      => 'async',
				)
			);
		}
		?>
	</div>

	<div class="container home-hero__inner">

		<div class="home-hero__content">

			<?php if ( '' !== $avallone_badge ) : ?>
				<p class="home-hero__badge"><?php echo esc_html( $avallone_badge ); ?></p>
			<?php endif; ?>

			<?php if ( '' !== $avallone_heading || '' !== $avallone_accent ) : ?>
				<h1 class="home-hero__title">
					<?php if ( '' !== $avallone_heading ) : ?>
						<span class="home-hero__title-main"><?php echo esc_html( $avallone_heading ); ?></span>
					<?php endif; ?>
					<?php if ( '' !== $avallone_accent ) : ?>
						<span class="home-hero__title-accent"><?php echo esc_html( $avallone_accent ); ?></span>
					<?php endif; ?>
				</h1>
			<?php endif; ?>

			<?php if ( '' !== $avallone_description ) : ?>
				<p class="home-hero__description"><?php echo esc_html( $avallone_description ); ?></p>
			<?php endif; ?>

			<?php if ( ! empty( $avallone_cta_one['url'] ) || ! empty( $avallone_cta_two['url'] ) ) : ?>
				<div class="home-hero__actions">
					<?php if ( ! empty( $avallone_cta_one['url'] ) ) : ?>
						<a
							class="btn btn--primary home-hero__cta"
							href="<?php echo esc_url( $avallone_cta_one['url'] ); ?>"
							<?php if ( ! empty( $avallone_cta_one['target'] ) ) : ?>
								target="<?php echo esc_attr( $avallone_cta_one['target'] ); ?>" rel="noopener"
							<?php endif; ?>
						><?php echo esc_html( $avallone_cta_one['title'] ); ?></a>
					<?php endif; ?>

					<?php if ( ! empty( $avallone_cta_two['url'] ) ) : ?>
						<a
							class="btn btn--outline home-hero__cta"
							href="<?php echo esc_url( $avallone_cta_two['url'] ); ?>"
							<?php if ( ! empty( $avallone_cta_two['target'] ) ) : ?>
								target="<?php echo esc_attr( $avallone_cta_two['target'] ); ?>" rel="noopener"
							<?php endif; ?>
						><?php echo esc_html( $avallone_cta_two['title'] ); ?></a>
					<?php endif; ?>
				</div>
			<?php endif; ?>

		</div>

		<?php if ( $avallone_wine ) : ?>
			<div class="home-hero__product">
				<div class="home-hero__card">

					<?php /* Decorative only — this site has no wishlist, so it is not a control. */ ?>
					<span class="home-hero__heart" aria-hidden="true">
						<?php avallone_icon( 'heart', array( 'size' => 20 ) ); ?>
					</span>

					<div class="home-hero__bottle">
						<span class="home-hero__glow" aria-hidden="true"></span>
						<a class="home-hero__bottle-link" href="<?php echo esc_url( $avallone_wine->get_permalink() ); ?>" tabindex="-1" aria-hidden="true">
							<?php
							$avallone_image_id = $avallone_wine->get_image_id();

							if ( $avallone_image_id ) {
								echo wp_get_attachment_image(
									$avallone_image_id,
									'large',
									false,
									array(
										'class' => 'home-hero__bottle-image',
										'alt'   => '',
									)
								);
							}
							?>
						</a>
					</div>

					<div class="home-hero__details">

						<h2 class="home-hero__product-name">
							<a href="<?php echo esc_url( $avallone_wine->get_permalink() ); ?>">
								<?php echo esc_html( $avallone_wine->get_name() ); ?>
							</a>
						</h2>

						<?php if ( '' !== $avallone_wine_meta ) : ?>
							<p class="home-hero__product-meta"><?php echo esc_html( $avallone_wine_meta ); ?></p>
						<?php endif; ?>

						<p class="home-hero__price">
							<span class="home-hero__price-value">
								<?php echo wp_kses_post( $avallone_wine->get_price_html() ); ?>
							</span>
							<?php if ( '' !== $avallone_volume ) : ?>
								<span class="home-hero__volume"><?php echo esc_html( $avallone_volume ); ?></span>
							<?php endif; ?>
						</p>

						<?php
						$avallone_can_ajax = $avallone_wine->supports( 'ajax_add_to_cart' )
							&& $avallone_wine->is_purchasable()
							&& $avallone_wine->is_in_stock();
						?>

						<?php if ( $avallone_can_ajax ) : ?>
							<a
								class="home-hero__cart ajax_add_to_cart add_to_cart_button"
								href="<?php echo esc_url( $avallone_wine->add_to_cart_url() ); ?>"
								data-product_id="<?php echo esc_attr( $avallone_wine->get_id() ); ?>"
								data-quantity="1"
								rel="nofollow"
							>
								<?php avallone_icon( 'cart', array( 'size' => 20 ) ); ?>
								<?php esc_html_e( 'Lisa korvi', 'avallone' ); ?>
							</a>
						<?php else : ?>
							<a class="home-hero__cart" href="<?php echo esc_url( $avallone_wine->get_permalink() ); ?>">
								<?php esc_html_e( 'Vaata toodet', 'avallone' ); ?>
							</a>
						<?php endif; ?>

					</div>

					<?php if ( '' !== $avallone_origin ) : ?>
						<div class="home-hero__origin">
							<span class="home-hero__origin-icon" aria-hidden="true">
								<?php avallone_icon( 'map-pin', array( 'size' => 18 ) ); ?>
							</span>
							<span class="home-hero__origin-text">
								<span class="home-hero__origin-label"><?php esc_html_e( 'Origin', 'avallone' ); ?></span>
								<span class="home-hero__origin-value"><?php echo esc_html( $avallone_origin ); ?></span>
							</span>
						</div>
					<?php endif; ?>

				</div>
			</div>
		<?php endif; ?>

	</div>

</section>
