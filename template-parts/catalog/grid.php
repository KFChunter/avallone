<?php
/**
 * Catalogue product grid.
 *
 * One CSS grid holds the products and both promotional blocks, which span
 * columns rather than breaking the flow into separate rows — that is what keeps
 * the Figma composition intact at every width.
 *
 * Promos are part of the first page's composition only. "Load more" appends
 * cards alone, so a promotional block can never be repeated.
 *
 * @package Avallone
 *
 * @param array $args {
 *     @type array    $config Catalogue configuration.
 *     @type array    $state  Sanitised catalogue state.
 *     @type WP_Query $query  The product query.
 *     @type int      $page   Page ID, for the promo field values.
 * }
 */

defined( 'ABSPATH' ) || exit;

$avallone_config = isset( $args['config'] ) ? $args['config'] : array();
$avallone_state  = isset( $args['state'] ) ? $args['state'] : array();
$avallone_query  = isset( $args['query'] ) ? $args['query'] : null;
$avallone_pageid = isset( $args['page'] ) ? (int) $args['page'] : get_queried_object_id();

if ( ! $avallone_query instanceof WP_Query ) {
	return;
}

if ( ! $avallone_query->have_posts() ) {
	?>
	<div class="catalog-empty">
		<p class="catalog-empty__title"><?php esc_html_e( 'Valitud filtritega tooteid ei leitud.', 'avallone' ); ?></p>
		<p class="catalog-empty__text"><?php esc_html_e( 'Proovi filtreid muuta või vaata kogu valikut.', 'avallone' ); ?></p>

		<?php if ( avallone_catalog_has_filters( $avallone_state ) ) : ?>
			<a class="btn btn--primary" href="<?php echo esc_url( avallone_catalog_clear_url( $avallone_config, $avallone_state ) ); ?>">
				<?php esc_html_e( 'Puhasta filtrid', 'avallone' ); ?>
			</a>
		<?php endif; ?>
	</div>
	<?php
	return;
}

/*
 * Promos belong to the unfiltered first page. Once a visitor has filtered, the
 * grid is their result set and an editorial insert would only get in the way —
 * and on later pages they would appear a second time.
 */
$avallone_promos = ( 1 === (int) $avallone_state['page'] && ! avallone_catalog_has_filters( $avallone_state ) )
	? (array) ( $avallone_config['promos'] ?? array() )
	: array();

$avallone_index = 0;
?>

<div class="catalog-grid" data-catalog-grid>
	<?php
	while ( $avallone_query->have_posts() ) :
		$avallone_query->the_post();

		get_template_part(
			'template-parts/catalog/product-card',
			null,
			array( 'id' => get_the_ID() )
		);

		++$avallone_index;

		if ( isset( $avallone_promos[ $avallone_index ] ) ) {
			get_template_part(
				'template-parts/catalog/' . $avallone_promos[ $avallone_index ],
				null,
				array( 'page' => $avallone_pageid )
			);
		}
	endwhile;

	wp_reset_postdata();
	?>
</div>

<?php
/*
 * A real link to the next page, so the catalogue paginates without JavaScript.
 * catalog.js upgrades it into an append-in-place control.
 */
$avallone_next = (int) $avallone_state['page'] + 1;
$avallone_more = $avallone_next <= (int) $avallone_query->max_num_pages;
?>

<div class="catalog-more" data-catalog-more>

	<p class="catalog-more__status" role="status" aria-live="polite" data-catalog-status></p>

	<?php if ( $avallone_more ) : ?>
		<a
			class="catalog-more__button"
			href="<?php echo esc_url( avallone_catalog_url( $avallone_state, array( AVALLONE_CATALOG_PAGE_VAR => $avallone_next ) ) ); ?>"
			data-catalog-load-more
			data-page="<?php echo esc_attr( $avallone_next ); ?>"
			data-max="<?php echo esc_attr( (int) $avallone_query->max_num_pages ); ?>"
		>
			<span data-catalog-more-label><?php esc_html_e( 'Lae rohkem tooteid', 'avallone' ); ?></span>
		</a>
	<?php endif; ?>

</div>
