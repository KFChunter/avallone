<?php
/**
 * Block — Globaalsed partnerid.
 *
 * Reads the shared product_brand taxonomy, so a partner's logo is stored once
 * against the brand and never re-uploaded for this block. The editor picks
 * terms; everything shown comes from the term itself.
 *
 * @package Avallone
 *
 * @param array $block The block settings and attributes.
 */

defined( 'ABSPATH' ) || exit;

$avallone_title = trim( (string) get_field( 'partners_title' ) );
$avallone_text  = trim( (string) get_field( 'partners_text' ) );
$avallone_ids   = array_filter( array_map( 'intval', (array) get_field( 'partners_terms' ) ) );

$avallone_partners = array();

foreach ( $avallone_ids as $avallone_id ) {
	$avallone_term = get_term( $avallone_id, 'product_brand' );

	if ( $avallone_term instanceof WP_Term ) {
		$avallone_partners[] = array(
			'term' => $avallone_term,
			'logo' => (int) get_term_meta( $avallone_term->term_id, 'thumbnail_id', true ),
		);
	}
}

$avallone_preview = avallone_block_is_preview( $block );
$avallone_empty   = '' === $avallone_title && '' === $avallone_text && ! $avallone_partners;

if ( $avallone_empty && ! $avallone_preview ) {
	return;
}

$avallone_heading_id = avallone_block_id( $block, 'title' );
?>

<section
	class="<?php echo esc_attr( avallone_block_classes( $block, 'global-partners' ) ); ?>"
	<?php if ( '' !== $avallone_title ) : ?>
		aria-labelledby="<?php echo esc_attr( $avallone_heading_id ); ?>"
	<?php endif; ?>
>
	<div class="container">

		<?php if ( $avallone_empty ) : ?>

			<p class="global-partners__placeholder">
				<?php esc_html_e( 'Globaalsed partnerid — lisa pealkiri ja vali brändid.', 'avallone' ); ?>
			</p>

		<?php else : ?>

			<?php if ( '' !== $avallone_title || '' !== $avallone_text ) : ?>
				<div class="global-partners__head">
					<?php if ( '' !== $avallone_title ) : ?>
						<h2 class="global-partners__title" id="<?php echo esc_attr( $avallone_heading_id ); ?>">
							<?php echo esc_html( $avallone_title ); ?>
						</h2>
					<?php endif; ?>

					<?php if ( '' !== $avallone_text ) : ?>
						<p class="global-partners__text"><?php echo esc_html( $avallone_text ); ?></p>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<?php if ( $avallone_partners ) : ?>
				<ul class="global-partners__grid" role="list">
					<?php foreach ( $avallone_partners as $avallone_partner ) : ?>
						<li class="global-partners__item">
							<?php
							if ( $avallone_partner['logo'] ) {
								/*
								 * The brand name is the logo's accessible name, so the
								 * mark is never announced as an unlabelled image.
								 */
								echo wp_get_attachment_image(
									$avallone_partner['logo'],
									'medium',
									false,
									array(
										'class'   => 'global-partners__logo',
										'alt'     => $avallone_partner['term']->name,
										'loading' => 'lazy',
										'sizes'   => '(min-width: 1280px) 180px, (min-width: 768px) 25vw, 40vw',
									)
								);
							} else {
								?>
								<span class="global-partners__name"><?php echo esc_html( $avallone_partner['term']->name ); ?></span>
								<?php
							}
							?>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>

		<?php endif; ?>

	</div>
</section>
