<?php
/**
 * Single product — image panel and its utility row.
 *
 * The warm grey behind the bottle is drawn here, by the theme. Product
 * photography is transparent and is contained inside the panel, never cropped
 * to fill it — the same rule the catalogue uses.
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

$avallone_id    = $avallone_product->get_id();
$avallone_image = $avallone_product->get_image_id();
$avallone_note  = function_exists( 'get_field' ) ? trim( (string) get_field( 'product_image_note', $avallone_id ) ) : '';
$avallone_pdf   = function_exists( 'get_field' ) ? get_field( 'product_datasheet', $avallone_id ) : null;

// Additional gallery images become selectable thumbnails; one image shows none.
$avallone_gallery = array_filter( array_map( 'intval', (array) $avallone_product->get_gallery_image_ids() ) );
?>

<div class="product-gallery">

	<div class="product-gallery__stage">

		<?php
		/*
		 * Wishlist is not implemented yet. The control is real and reachable but
		 * announces itself as unavailable — it stores nothing and claims nothing.
		 * A future shared wishlist binds this, the "Salvesta" action below and
		 * the homepage hero heart through [data-avallone-wishlist].
		 */
		?>
		<button
			class="product-gallery__wishlist"
			type="button"
			aria-disabled="true"
			data-avallone-wishlist
			data-product-id="<?php echo esc_attr( $avallone_id ); ?>"
			title="<?php esc_attr_e( 'Lemmikute nimekiri on peagi saadaval', 'avallone' ); ?>"
		>
			<?php avallone_icon( 'heart', array( 'size' => 20 ) ); ?>
			<span class="screen-reader-text"><?php esc_html_e( 'Lisa lemmikutesse — funktsioon on peagi saadaval', 'avallone' ); ?></span>
		</button>

		<div class="product-gallery__frame" data-product-frame>
			<?php
			if ( $avallone_image ) {
				echo wp_get_attachment_image(
					$avallone_image,
					'large',
					false,
					array(
						'class'         => 'product-gallery__image',
						'alt'           => esc_attr( $avallone_product->get_name() ),
						'fetchpriority' => 'high',
						'decoding'      => 'async',
						'sizes'         => '(min-width: 1280px) 500px, (min-width: 768px) 45vw, 80vw',
					)
				);
			} else {
				echo wp_kses_post( wc_placeholder_img( 'large', array( 'class' => 'product-gallery__image' ) ) );
			}
			?>
		</div>

	</div>

	<?php if ( $avallone_gallery ) : ?>
		<ul class="product-gallery__thumbs" role="list">
			<?php
			$avallone_all = array_merge( $avallone_image ? array( (int) $avallone_image ) : array(), $avallone_gallery );

			foreach ( $avallone_all as $avallone_index => $avallone_thumb ) :
				$avallone_full = wp_get_attachment_image_url( $avallone_thumb, 'large' );
				?>
				<li>
					<button
						class="product-gallery__thumb<?php echo 0 === $avallone_index ? ' is-current' : ''; ?>"
						type="button"
						data-product-thumb
						data-full="<?php echo esc_url( (string) $avallone_full ); ?>"
						aria-pressed="<?php echo 0 === $avallone_index ? 'true' : 'false'; ?>"
					>
						<?php
						echo wp_get_attachment_image(
							$avallone_thumb,
							'woocommerce_gallery_thumbnail',
							false,
							array(
								'alt'     => '',
								'loading' => 'lazy',
							)
						);
						?>
						<span class="screen-reader-text">
							<?php
							/* translators: %d: image number. */
							printf( esc_html__( 'Vaata pilti %d', 'avallone' ), (int) $avallone_index + 1 );
							?>
						</span>
					</button>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>

	<div class="product-gallery__utils">

		<?php if ( '' !== $avallone_note ) : ?>
			<p class="product-gallery__note"><?php echo esc_html( $avallone_note ); ?></p>
		<?php endif; ?>

		<div class="product-gallery__actions">

			<?php if ( ! empty( $avallone_pdf['url'] ) ) : ?>
				<a
					class="product-gallery__action"
					href="<?php echo esc_url( $avallone_pdf['url'] ); ?>"
					download
					<?php if ( ! empty( $avallone_pdf['filename'] ) ) : ?>
						title="<?php echo esc_attr( $avallone_pdf['filename'] ); ?>"
					<?php endif; ?>
				>
					<?php avallone_icon( 'download', array( 'size' => 14 ) ); ?>
					<span><?php esc_html_e( 'Lae alla PDF', 'avallone' ); ?></span>
				</a>
			<?php endif; ?>

			<button
				class="product-gallery__action"
				type="button"
				aria-disabled="true"
				data-avallone-wishlist
				data-product-id="<?php echo esc_attr( $avallone_id ); ?>"
				title="<?php esc_attr_e( 'Lemmikute nimekiri on peagi saadaval', 'avallone' ); ?>"
			>
				<?php avallone_icon( 'heart', array( 'size' => 14 ) ); ?>
				<span><?php esc_html_e( 'Salvesta', 'avallone' ); ?></span>
			</button>

			<button
				class="product-gallery__action"
				type="button"
				data-product-share
				data-title="<?php echo esc_attr( $avallone_product->get_name() ); ?>"
				data-url="<?php echo esc_url( $avallone_product->get_permalink() ); ?>"
			>
				<?php avallone_icon( 'share', array( 'size' => 14 ) ); ?>
				<span><?php esc_html_e( 'Jaga', 'avallone' ); ?></span>
			</button>

		</div>

		<p class="product-gallery__status" role="status" aria-live="polite" data-product-share-status></p>

	</div>

</div>
