<?php
/**
 * Single product view.
 *
 * Everything the Avallone product detail needs that WooCommerce does not hand
 * over ready-made: which structured values exist, how they are labelled and
 * iconed, and the few derived figures the design shows (price per litre, the
 * real discount percentage).
 *
 * No value here is duplicated into ACF — each row resolves to a WooCommerce
 * attribute, taxonomy or native field.
 *
 * @package Avallone
 */

defined( 'ABSPATH' ) || exit;

/**
 * Replace WooCommerce's default single-product furniture.
 *
 * The tab block duplicates the Avallone description and data sections, and the
 * stock related/upsell blocks are replaced by "Sarnased tooted". Removed on the
 * single product view only, so shop, cart and account pages are untouched.
 *
 * Product reviews leave the page with the tab block but stay enabled in
 * WordPress — restoring them later is one add_action.
 *
 * @return void
 */
function avallone_product_single_hooks() {
	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return;
	}

	remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );
	remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15 );
	remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );
}
add_action( 'wp', 'avallone_product_single_hooks' );

/**
 * The structured rows of "Toote andmed", in the design's reading order.
 *
 * Each entry names where the value comes from and which icon carries its
 * meaning — icons map to what the row *is*, never to its position.
 *
 * @return array<string, array{label:string, icon:string, source:string}>
 */
function avallone_product_data_map() {
	return array(
		'product_cat'            => array(
			'label'  => __( 'Tootegrupp', 'avallone' ),
			'icon'   => 'layers',
			'source' => 'category',
		),
		'pa_tooteliik'           => array(
			'label'  => __( 'Tooteliik', 'avallone' ),
			'icon'   => 'award',
			'source' => 'attribute',
		),
		'product_brand'          => array(
			'label'  => __( 'Bränd', 'avallone' ),
			'icon'   => 'tag',
			'source' => 'brand',
		),
		'pa_riik'                => array(
			'label'  => __( 'Päritolumaa', 'avallone' ),
			'icon'   => 'globe',
			'source' => 'attribute',
		),
		'pa_piirkond'            => array(
			'label'  => __( 'Piirkond', 'avallone' ),
			'icon'   => 'map-pin',
			'source' => 'attribute',
		),
		'pa_varv'                => array(
			'label'  => __( 'Värvus', 'avallone' ),
			'icon'   => 'droplet',
			'source' => 'attribute',
		),
		'pa_viinamari'           => array(
			'label'  => __( 'Viinamari', 'avallone' ),
			'icon'   => 'grape',
			'source' => 'attribute',
		),
		'pa_maitse'              => array(
			'label'  => __( 'Maitse', 'avallone' ),
			'icon'   => 'wine',
			'source' => 'attribute',
		),
		'serveerimistemperatuur' => array(
			'label'  => __( 'Serveerimistemperatuur', 'avallone' ),
			'icon'   => 'thermometer',
			'source' => 'attribute',
		),
		'pa_stiil'               => array(
			'label'  => __( 'Stiil', 'avallone' ),
			'icon'   => 'bottle',
			'source' => 'attribute',
		),
		'alkoholisisaldus'       => array(
			'label'  => __( 'Alkoholisisaldus', 'avallone' ),
			'icon'   => 'percent',
			'source' => 'attribute',
		),
		'pa_maht'                => array(
			'label'  => __( 'Maht', 'avallone' ),
			'icon'   => 'glass',
			'source' => 'attribute',
		),
		'kogus-kastis'           => array(
			'label'  => __( 'Kogus kastis', 'avallone' ),
			'icon'   => 'package',
			'source' => 'attribute',
		),
		'ean'                    => array(
			'label'  => __( 'EAN', 'avallone' ),
			'icon'   => 'barcode',
			'source' => 'gtin',
		),
	);
}

/**
 * The four values highlighted directly under the purchase controls.
 *
 * @return string[] Keys into avallone_product_data_map().
 */
function avallone_product_quick_keys() {
	return array( 'pa_maitse', 'pa_stiil', 'alkoholisisaldus', 'pa_maht' );
}

/**
 * The product's brand term, if it has one.
 *
 * @param WC_Product $product Product.
 * @return WP_Term|null
 */
function avallone_product_brand_term( $product ) {
	if ( ! $product instanceof WC_Product || ! taxonomy_exists( 'product_brand' ) ) {
		return null;
	}

	$terms = wp_get_post_terms( $product->get_id(), 'product_brand' );

	return ( ! is_wp_error( $terms ) && $terms ) ? $terms[0] : null;
}

/**
 * The product's origin, as "Region, Country".
 *
 * Origin used to live in a single local `paritolu` attribute holding a combined
 * string. It is now two shared taxonomies, which is what makes it filterable —
 * so every card composes the display string through here rather than reading an
 * attribute name directly. The old attribute is still honoured as a fallback
 * for any product that has not been migrated.
 *
 * @param WC_Product $product Product.
 * @return string Empty when the product has no origin.
 */
function avallone_product_origin( $product ) {
	if ( ! $product instanceof WC_Product ) {
		return '';
	}

	$parts = array_filter(
		array(
			avallone_product_attribute( $product, array( 'pa_piirkond' ) ),
			avallone_product_attribute( $product, array( 'pa_riik' ) ),
		)
	);

	if ( $parts ) {
		return implode( ', ', $parts );
	}

	return avallone_product_attribute( $product, array( 'pa_paritolu', 'Päritolu', 'paritolu', 'Origin' ) );
}

/**
 * The product's most specific category.
 *
 * Deepest assigned term rather than whichever WordPress returns first, so a
 * product in both "Veinid" and "Punane vein" reports the narrower one.
 *
 * @param WC_Product $product Product.
 * @return WP_Term|null
 */
function avallone_product_primary_category( $product ) {
	if ( ! $product instanceof WC_Product ) {
		return null;
	}

	$terms = wp_get_post_terms( $product->get_id(), 'product_cat' );

	if ( is_wp_error( $terms ) || ! $terms ) {
		return null;
	}

	$best  = null;
	$depth = -1;

	foreach ( $terms as $term ) {
		$ancestors = count( get_ancestors( $term->term_id, 'product_cat' ) );

		if ( $ancestors > $depth ) {
			$depth = $ancestors;
			$best  = $term;
		}
	}

	return $best;
}

/**
 * Resolve one data row's value.
 *
 * @param WC_Product $product Product.
 * @param string     $key     Map key.
 * @param array      $row     Map entry.
 * @return string Empty when the product has no such value.
 */
function avallone_product_data_value( $product, $key, $row ) {
	switch ( $row['source'] ) {
		case 'category':
			$term = avallone_product_primary_category( $product );

			return $term ? $term->name : '';

		case 'brand':
			$term = avallone_product_brand_term( $product );

			return $term ? $term->name : '';

		case 'gtin':
			return method_exists( $product, 'get_global_unique_id' )
				? (string) $product->get_global_unique_id()
				: '';

		case 'attribute':
		default:
			// get_attribute() resolves taxonomy and local attributes alike.
			return (string) $product->get_attribute( $key );
	}
}

/**
 * Every data row the product actually has a value for.
 *
 * Rows without values are dropped, so the grid never renders an empty cell.
 *
 * @param WC_Product $product Product.
 * @param string[]   $only    Optional. Restrict to these keys, in this order.
 * @return array<int, array{key:string, label:string, icon:string, value:string}>
 */
function avallone_product_data_rows( $product, $only = array() ) {
	if ( ! $product instanceof WC_Product ) {
		return array();
	}

	$map  = avallone_product_data_map();
	$keys = $only ? $only : array_keys( $map );
	$rows = array();

	foreach ( $keys as $key ) {
		if ( ! isset( $map[ $key ] ) ) {
			continue;
		}

		$value = trim( avallone_product_data_value( $product, $key, $map[ $key ] ) );

		if ( '' === $value ) {
			continue;
		}

		$rows[] = array(
			'key'   => $key,
			'label' => $map[ $key ]['label'],
			'icon'  => $map[ $key ]['icon'],
			'value' => $value,
		);
	}

	return $rows;
}

/**
 * The product's volume in litres.
 *
 * Accepts the spellings the shop actually uses — "75 cl", "75cl", "750 ml",
 * "0.75 l", "1,5 l". Anything it cannot read confidently returns null, and the
 * caller omits whatever it was going to show rather than guessing.
 *
 * @param WC_Product $product Product.
 * @return float|null Litres.
 */
function avallone_product_litres( $product ) {
	if ( ! $product instanceof WC_Product ) {
		return null;
	}

	$raw = trim( (string) $product->get_attribute( 'pa_maht' ) );

	if ( '' === $raw ) {
		$raw = trim( (string) $product->get_attribute( 'maht' ) );
	}

	if ( '' === $raw ) {
		return null;
	}

	// A multi-value attribute is ambiguous — do not guess which one applies.
	if ( false !== strpos( $raw, ',' ) && ! preg_match( '/^\s*\d+,\d+\s*[a-zA-Z]/', $raw ) ) {
		return null;
	}

	if ( ! preg_match( '/([\d]+(?:[.,][\d]+)?)\s*(cl|ml|dl|l)\b/i', $raw, $m ) ) {
		return null;
	}

	$number = (float) str_replace( ',', '.', $m[1] );

	if ( $number <= 0 ) {
		return null;
	}

	switch ( strtolower( $m[2] ) ) {
		case 'ml':
			return $number / 1000;
		case 'cl':
			return $number / 100;
		case 'dl':
			return $number / 10;
		case 'l':
			return $number;
	}

	return null;
}

/**
 * Formatted price per litre, or an empty string.
 *
 * Uses the price actually being charged, tax display included, so it always
 * agrees with the price shown beside it.
 *
 * @param WC_Product $product Product.
 * @return string
 */
function avallone_product_price_per_litre( $product ) {
	$litres = avallone_product_litres( $product );

	if ( ! $litres || ! $product instanceof WC_Product ) {
		return '';
	}

	// A range cannot produce one honest figure.
	if ( $product->is_type( 'variable' ) || $product->is_type( 'grouped' ) ) {
		return '';
	}

	$price = (float) wc_get_price_to_display( $product );

	if ( $price <= 0 ) {
		return '';
	}

	return sprintf(
		/* translators: %s: formatted price per litre. */
		__( '%s/L', 'avallone' ),
		wp_strip_all_tags( wc_price( $price / $litres ) )
	);
}

/**
 * The product's real discount percentage.
 *
 * Only returned when the product is genuinely on sale and both prices resolve
 * to a meaningful figure. Variable products span a range, where a single
 * percentage would misrepresent most of it, so they return 0.
 *
 * @param WC_Product $product Product.
 * @return int Percentage, or 0 when none can be stated honestly.
 */
function avallone_product_discount_percent( $product ) {
	if ( ! $product instanceof WC_Product || ! $product->is_on_sale() ) {
		return 0;
	}

	if ( $product->is_type( 'variable' ) || $product->is_type( 'grouped' ) ) {
		return 0;
	}

	$regular = (float) wc_get_price_to_display( $product, array( 'price' => $product->get_regular_price() ) );
	$now     = (float) wc_get_price_to_display( $product );

	if ( $regular <= 0 || $now <= 0 || $now >= $regular ) {
		return 0;
	}

	$percent = (int) round( ( 1 - $now / $regular ) * 100 );

	return ( $percent > 0 && $percent < 100 ) ? $percent : 0;
}

/**
 * The badges shown above the product title.
 *
 * Real structured values only — vintage and product type. A product without
 * them simply has no badge row.
 *
 * @param WC_Product $product Product.
 * @return string[]
 */
function avallone_product_badges( $product ) {
	if ( ! $product instanceof WC_Product ) {
		return array();
	}

	$badges  = array();
	$vintage = trim( (string) $product->get_attribute( 'aastakaik' ) );

	if ( '' !== $vintage ) {
		$badges[] = sprintf(
			/* translators: %s: vintage year. */
			__( '%s aastakäik', 'avallone' ),
			$vintage
		);
	}

	$type = trim( (string) $product->get_attribute( 'pa_tooteliik' ) );

	if ( '' !== $type ) {
		$badges[] = $type;
	}

	return $badges;
}

/**
 * Products for the "Sarnased tooted" row.
 *
 * WooCommerce's own up-sells are the editorial override — they are defined as
 * the products to show on this product's page, which is exactly this block.
 * Cross-sells are cart-page items and are deliberately not repurposed. With no
 * up-sells set, WooCommerce's related-products logic supplies the row.
 *
 * @param WC_Product $product Product.
 * @param int        $limit   How many to return.
 * @return int[]
 */
function avallone_product_related_ids( $product, $limit = 4 ) {
	if ( ! $product instanceof WC_Product ) {
		return array();
	}

	$ids = array_map( 'intval', $product->get_upsell_ids() );

	if ( ! $ids ) {
		$ids = array_map( 'intval', wc_get_related_products( $product->get_id(), $limit ) );
	}

	$ids = array_diff( $ids, array( $product->get_id() ) );

	// A linked product that has since been unpublished must not 404.
	$ids = array_filter(
		$ids,
		static function ( $id ) {
			$related = wc_get_product( $id );

			return $related instanceof WC_Product && $related->is_visible();
		}
	);

	return array_slice( array_values( $ids ), 0, $limit );
}

/**
 * The add-to-cart label, in the design's language.
 *
 * WooCommerce ships "Add to cart"; the site is Estonian throughout.
 *
 * @return string
 */
function avallone_product_add_to_cart_text() {
	return __( 'Lisa korvi', 'avallone' );
}
add_filter( 'woocommerce_product_single_add_to_cart_text', 'avallone_product_add_to_cart_text' );

/**
 * The variation dropdown's placeholder, in the design's language.
 *
 * @param array $args Dropdown arguments.
 * @return array
 */
function avallone_product_variation_dropdown_args( $args ) {
	$args['show_option_none'] = __( 'Vali', 'avallone' );

	return $args;
}
add_filter( 'woocommerce_dropdown_variation_attribute_options_args', 'avallone_product_variation_dropdown_args' );

/**
 * Estonian breadcrumb root, and the design's separator.
 *
 * @param array $defaults WooCommerce breadcrumb defaults.
 * @return array
 */
function avallone_product_breadcrumb_defaults( $defaults ) {
	$defaults['home']      = __( 'Avaleht', 'avallone' );
	$defaults['delimiter'] = '<span class="woocommerce-breadcrumb__sep" aria-hidden="true">&rsaquo;</span>';
	$defaults['wrap_before'] = '<nav class="woocommerce-breadcrumb" aria-label="' . esc_attr__( 'Asukoht lehel', 'avallone' ) . '">';
	$defaults['wrap_after']  = '</nav>';

	return $defaults;
}
add_filter( 'woocommerce_breadcrumb_defaults', 'avallone_product_breadcrumb_defaults' );

/**
 * Enqueue the single-product assets.
 *
 * @return void
 */
function avallone_product_enqueue() {
	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return;
	}

	$layers = avallone_style_layers();
	$deps   = array( (string) array_key_last( $layers ) );

	foreach ( array( 'woocommerce-layout', 'woocommerce-smallscreen', 'woocommerce-general' ) as $handle ) {
		if ( wp_style_is( $handle, 'registered' ) ) {
			$deps[] = $handle;
		}
	}

	/*
	 * "Sarnased tooted" renders the catalogue's own product card, so the
	 * catalogue layer has to be present. Registered as a dependency rather than
	 * copied, so the card stays defined in exactly one place.
	 */
	$catalog = 'assets/css/pages/catalog.css';

	wp_enqueue_style(
		'avallone-catalog',
		AVALLONE_URI . '/' . $catalog,
		$deps,
		avallone_asset_version( $catalog )
	);

	$deps[] = 'avallone-catalog';

	$style = 'assets/css/pages/single-product.css';

	wp_enqueue_style(
		'avallone-single-product',
		AVALLONE_URI . '/' . $style,
		$deps,
		avallone_asset_version( $style )
	);

	$script = 'assets/js/single-product.js';

	wp_enqueue_script(
		'avallone-single-product',
		AVALLONE_URI . '/' . $script,
		array(),
		avallone_asset_version( $script ),
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);

	wp_localize_script(
		'avallone-single-product',
		'avalloneProduct',
		array(
			'i18n' => array(
				'copied'     => __( 'Link kopeeritud.', 'avallone' ),
				'copyFailed' => __( 'Linki ei õnnestunud kopeerida.', 'avallone' ),
				'decrease'   => __( 'Vähenda kogust', 'avallone' ),
				'increase'   => __( 'Suurenda kogust', 'avallone' ),
			),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'avallone_product_enqueue', 20 );
