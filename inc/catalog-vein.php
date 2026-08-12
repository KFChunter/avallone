<?php
/**
 * Vein catalogue configuration.
 *
 * The whole Vein-specific surface of the catalogue. A future Vahuvein or Kange
 * alkohol page is a copy of this file with a different filter list, plus one
 * line in avallone_catalog_config_for_page() and one in
 * avallone_acf_template_prefixes().
 *
 * It lives in an include rather than in the page template because the
 * load-more endpoint has to resolve the same configuration, and page templates
 * are not loaded during an AJAX request.
 *
 * @package Avallone
 */

defined( 'ABSPATH' ) || exit;

/**
 * Where the promotional blocks sit in the product flow.
 *
 * Keyed by how many products precede the block. Taken from the Figma frame:
 * the spotlight opens the second row, and the gift banner follows the row after
 * it. Implementation constants, deliberately not editor-facing — the client
 * chooses the content, not the grid position.
 */
const AVALLONE_VEIN_PROMO_POSITIONS = array(
	5  => 'producer-spotlight',
	10 => 'gift-banner',
);

/**
 * Products shown before "load more" appears.
 */
const AVALLONE_VEIN_PER_PAGE = 12;

/**
 * The Vein filter set.
 *
 * Declared against the taxonomies the shop should eventually carry. A filter
 * whose taxonomy does not exist, or exists but has no terms, is silently
 * dropped by avallone_catalog_filters() — so this list can describe the intent
 * while only the filters with real data behind them ever render.
 *
 * As of writing, the shop has no global attribute taxonomies at all: origin,
 * volume and vintage exist only as per-product local attributes, which cannot
 * back a tax_query. Bränd and Hind are therefore the two live filters. Create
 * pa_varv (etc.) and give it terms and its filter appears with no code change.
 *
 * @return array<string, array>
 */
function avallone_vein_filter_config() {
	return array(
		'varv'        => array(
			'label' => __( 'Värv', 'avallone' ),
			'tax'   => 'pa_varv',
		),
		'stiil'       => array(
			'label' => __( 'Stiil', 'avallone' ),
			'tax'   => 'pa_stiil',
		),
		'maitse'      => array(
			'label' => __( 'Maitse', 'avallone' ),
			'tax'   => 'pa_maitse',
		),
		'paritolumaa' => array(
			'label' => __( 'Päritolumaa', 'avallone' ),
			'tax'   => 'pa_riik',
			'child' => 'pa_piirkond',
		),
		'viinamari'   => array(
			'label' => __( 'Viinamari', 'avallone' ),
			'tax'   => 'pa_viinamari',
		),
		'maht'        => array(
			'label' => __( 'Maht', 'avallone' ),
			'tax'   => 'pa_maht',
		),
		'brand'       => array(
			'label' => __( 'Bränd', 'avallone' ),
			'tax'   => 'product_brand',
		),
		'hind'        => array(
			'label' => __( 'Hind', 'avallone' ),
			'type'  => 'price',
		),
	);
}

/**
 * The complete catalogue configuration for a Vein page.
 *
 * @param int $page_id Page ID.
 * @return array
 */
function avallone_vein_catalog_config( $page_id ) {
	return array(
		'page'     => (int) $page_id,
		'category' => function_exists( 'get_field' ) ? (int) get_field( 'vein_category', $page_id ) : 0,
		'filters'  => avallone_vein_filter_config(),
		'per_page' => AVALLONE_VEIN_PER_PAGE,
		'promos'   => AVALLONE_VEIN_PROMO_POSITIONS,
	);
}
