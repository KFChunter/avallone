<?php
/**
 * Homepage — Avasta kokteile.
 *
 * An editorial mosaic: a wide photograph beside a square one, then a square
 * photograph beside a deep-red quote panel. The two square tiles carry the
 * aspect ratio, so they set each row's height and the wide items stretch to
 * match — which also lets a long quote grow its row rather than overflow.
 *
 * Tile captions and the badge are optional. The design shows bare photographs,
 * so nothing is rendered unless an editor actually fills those fields.
 *
 * @package Avallone
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'get_field' ) ) {
	return;
}

$avallone_ktl_page = get_queried_object_id();

$avallone_ktl_large = (int) get_field( 'cocktail_image_large', $avallone_ktl_page );
$avallone_ktl_one   = (int) get_field( 'cocktail_image_1', $avallone_ktl_page );
$avallone_ktl_two   = (int) get_field( 'cocktail_image_2', $avallone_ktl_page );
$avallone_ktl_quote = trim( (string) get_field( 'cocktail_quote', $avallone_ktl_page ) );

// Nothing to show at all — skip the band rather than render an empty stripe.
if ( ! $avallone_ktl_large && ! $avallone_ktl_one && ! $avallone_ktl_two && '' === $avallone_ktl_quote ) {
	return;
}

$avallone_ktl_author = trim( (string) get_field( 'cocktail_quote_author', $avallone_ktl_page ) );
$avallone_ktl_link   = get_field( 'cocktail_link', $avallone_ktl_page );

/*
 * The wide tile leads, then the two square ones. Only the square tiles carry an
 * aspect ratio, so they are what gives each row its height.
 */
$avallone_ktl_tiles = array(
	array(
		'id'       => $avallone_ktl_large,
		'modifier' => 'wide',
		'title'    => trim( (string) get_field( 'cocktail_title_large', $avallone_ktl_page ) ),
		'badge'    => trim( (string) get_field( 'cocktail_badge_large', $avallone_ktl_page ) ),
	),
	array(
		'id'       => $avallone_ktl_one,
		'modifier' => 'small',
		'title'    => trim( (string) get_field( 'cocktail_title_1', $avallone_ktl_page ) ),
		'badge'    => '',
	),
	array(
		'id'       => $avallone_ktl_two,
		'modifier' => 'small',
		'title'    => trim( (string) get_field( 'cocktail_title_2', $avallone_ktl_page ) ),
		'badge'    => '',
	),
);
?>

<section class="home-section home-cocktails" aria-labelledby="home-cocktails-heading">
	<div class="container">

		<div class="home-section__head">
			<h2 class="home-section__title" id="home-cocktails-heading"><?php esc_html_e( 'Avasta kokteile', 'avallone' ); ?></h2>
			<?php if ( ! empty( $avallone_ktl_link['url'] ) ) : ?>
				<a
					class="home-section__more"
					href="<?php echo esc_url( $avallone_ktl_link['url'] ); ?>"
					<?php if ( ! empty( $avallone_ktl_link['target'] ) ) : ?>
						target="<?php echo esc_attr( $avallone_ktl_link['target'] ); ?>" rel="noopener"
					<?php endif; ?>
				>
					<?php
					echo esc_html(
						! empty( $avallone_ktl_link['title'] )
							? $avallone_ktl_link['title']
							: __( 'Kõik kokteilid', 'avallone' )
					);
					?>
				</a>
			<?php endif; ?>
		</div>

		<div class="home-cocktails__mosaic">
			<?php foreach ( $avallone_ktl_tiles as $avallone_ktl_tile ) : ?>
				<?php if ( $avallone_ktl_tile['id'] ) : ?>
					<figure class="home-cocktails__tile home-cocktails__tile--<?php echo esc_attr( $avallone_ktl_tile['modifier'] ); ?>">
						<?php
						echo wp_get_attachment_image(
							$avallone_ktl_tile['id'],
							'large',
							false,
							array(
								'class'   => 'home-cocktails__image',
								/* The caption, where present, carries the meaning. */
								'alt'     => '',
								'loading' => 'lazy',
							)
						);
						?>

						<?php if ( '' !== $avallone_ktl_tile['badge'] ) : ?>
							<span class="home-cocktails__badge"><?php echo esc_html( $avallone_ktl_tile['badge'] ); ?></span>
						<?php endif; ?>

						<?php if ( '' !== $avallone_ktl_tile['title'] ) : ?>
							<figcaption class="home-cocktails__caption"><?php echo esc_html( $avallone_ktl_tile['title'] ); ?></figcaption>
						<?php endif; ?>
					</figure>
				<?php endif; ?>
			<?php endforeach; ?>

			<?php if ( '' !== $avallone_ktl_quote ) : ?>
				<figure class="home-cocktails__quote">
					<span class="home-cocktails__mark" aria-hidden="true">&rdquo;</span>

					<blockquote class="home-cocktails__quote-text">
						<?php echo wp_kses_post( wpautop( $avallone_ktl_quote ) ); ?>
					</blockquote>

					<?php if ( '' !== $avallone_ktl_author ) : ?>
						<figcaption class="home-cocktails__author">
							<?php echo esc_html( '— ' . $avallone_ktl_author ); ?>
						</figcaption>
					<?php endif; ?>
				</figure>
			<?php endif; ?>
		</div>

	</div>
</section>
