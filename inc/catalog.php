<?php
/**
 * Product catalogue engine.
 *
 * Shared by every catalogue page template — Vein first, then Vahuvein, Kange
 * alkohol and the rest. A template supplies a configuration array and this file
 * does the rest: resolving which filters are real, sanitising the request,
 * building the query, and rendering the next page for "load more".
 *
 * The URL is the single source of truth for filter state. JavaScript only ever
 * enhances what already works as plain links and forms.
 *
 * Configuration shape:
 *
 *     array(
 *         'category' => 28,                       // product_cat term ID
 *         'filters'  => array( ... ),             // see avallone_catalog_filters()
 *         'per_page' => 12,
 *         'promos'   => array( 5 => 'slug', 10 => 'slug' ),
 *     )
 *
 * @package Avallone
 */

defined( 'ABSPATH' ) || exit;

/**
 * Query variable names, kept in one place so templates and JS agree.
 */
const AVALLONE_CATALOG_SORT_VAR = 'sort';
const AVALLONE_CATALOG_PAGE_VAR = 'lk';
const AVALLONE_CATALOG_OPEN_VAR = 'ava';
const AVALLONE_CATALOG_PRICE_VAR = 'hind';

/**
 * The sort options offered by every catalogue.
 *
 * WooCommerce's own meta keys, addressed directly. wc_get_products() silently
 * ignores orderby => 'price' and 'popularity' — that mapping lives in WC_Query's
 * catalog ordering, which only applies to the shop loop — so ordering runs
 * through WP_Query on `_price` and `total_sales`, which is the same data
 * WooCommerce sorts on.
 *
 * @return array<string, array>
 */
function avallone_catalog_sort_options() {
	return array(
		'populaarsus' => array(
			'label'    => __( 'Populaarsuse järgi', 'avallone' ),
			'orderby'  => 'meta_value_num',
			'meta_key' => 'total_sales', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- WooCommerce's own popularity meta.
			'order'    => 'DESC',
		),
		'hind-asc'    => array(
			'label'    => __( 'Hind: madalam esmalt', 'avallone' ),
			'orderby'  => 'meta_value_num',
			'meta_key' => '_price', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- WooCommerce's own price meta.
			'order'    => 'ASC',
		),
		'hind-desc'   => array(
			'label'    => __( 'Hind: kõrgem esmalt', 'avallone' ),
			'orderby'  => 'meta_value_num',
			'meta_key' => '_price', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- WooCommerce's own price meta.
			'order'    => 'DESC',
		),
		'uusimad'     => array(
			'label'   => __( 'Uusimad esmalt', 'avallone' ),
			'orderby' => 'date',
			'order'   => 'DESC',
		),
	);
}

/**
 * Resolve a configuration's filters against the taxonomies that actually exist.
 *
 * A filter is only rendered when its source is real: the taxonomy is registered
 * and has at least one term. Anything else is dropped silently, so a config may
 * declare filters ahead of the data without producing dead controls. Create the
 * taxonomy and give it terms and the filter appears — no code change.
 *
 * Each filter entry accepts:
 *   'label' string  Visible name. Required.
 *   'tax'   string  Source taxonomy for a term filter.
 *   'type'  string  'price' for the numeric range filter.
 *   'child' string  Optional taxonomy revealed once a term here is selected.
 *
 * @param array $config Catalogue configuration.
 * @return array<string, array> Renderable filters, keyed by query variable.
 */
function avallone_catalog_filters( $config ) {
	$resolved = array();

	foreach ( (array) ( $config['filters'] ?? array() ) as $key => $filter ) {

		// The price filter needs no taxonomy — only products with prices.
		if ( 'price' === ( $filter['type'] ?? '' ) ) {
			$range = avallone_catalog_price_bounds( $config );

			if ( $range['max'] > $range['min'] ) {
				$resolved[ $key ] = $filter + array(
					'key'   => $key,
					'type'  => 'price',
					'range' => $range,
				);
			}

			continue;
		}

		$taxonomy = $filter['tax'] ?? '';

		if ( ! $taxonomy || ! taxonomy_exists( $taxonomy ) ) {
			continue;
		}

		$terms = get_terms(
			array(
				'taxonomy'   => $taxonomy,
				'hide_empty' => true,
			)
		);

		if ( is_wp_error( $terms ) || ! $terms ) {
			continue;
		}

		$resolved[ $key ] = $filter + array(
			'key'   => $key,
			'type'  => 'terms',
			'terms' => $terms,
		);
	}

	return $resolved;
}

/**
 * Lowest and highest live price within the catalogue's base category.
 *
 * One aggregate query rather than one per filter option.
 *
 * @param array $config Catalogue configuration.
 * @return array{min:int, max:int}
 */
function avallone_catalog_price_bounds( $config ) {
	global $wpdb;

	$category = (int) ( $config['category'] ?? 0 );

	if ( ! $category ) {
		return array(
			'min' => 0,
			'max' => 0,
		);
	}

	$cached = wp_cache_get( "avallone_price_bounds_{$category}", 'avallone' );

	if ( is_array( $cached ) ) {
		return $cached;
	}

	// phpcs:disable WordPress.DB.DirectDatabaseQuery -- Aggregate over indexed meta; cached below.
	$row = $wpdb->get_row(
		$wpdb->prepare(
			"SELECT MIN(CAST(pm.meta_value AS DECIMAL(10,2))) AS lo,
			        MAX(CAST(pm.meta_value AS DECIMAL(10,2))) AS hi
			   FROM {$wpdb->postmeta} pm
			   INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
			   INNER JOIN {$wpdb->term_relationships} tr ON tr.object_id = p.ID
			   INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
			  WHERE pm.meta_key = '_price'
			    AND pm.meta_value != ''
			    AND p.post_type = 'product'
			    AND p.post_status = 'publish'
			    AND tt.taxonomy = 'product_cat'
			    AND tt.term_id = %d",
			$category
		)
	);
	// phpcs:enable WordPress.DB.DirectDatabaseQuery

	$bounds = array(
		'min' => $row ? (int) floor( (float) $row->lo ) : 0,
		'max' => $row ? (int) ceil( (float) $row->hi ) : 0,
	);

	wp_cache_set( "avallone_price_bounds_{$category}", $bounds, 'avallone', HOUR_IN_SECONDS );

	return $bounds;
}

/**
 * The sanitised catalogue state for a request.
 *
 * Everything is validated against reality: term slugs must exist in their own
 * taxonomy, the sort key must be one we offer, prices are clamped to the
 * catalogue's real bounds. Unrecognised input is dropped, never echoed back.
 *
 * @param array      $config  Catalogue configuration.
 * @param array|null $source  Optional request data. Defaults to $_GET.
 * @return array
 */
function avallone_catalog_state( $config, $source = null ) {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Public, read-only catalogue filtering.
	$source  = null === $source ? $_GET : $source;
	$filters = avallone_catalog_filters( $config );

	$state = array(
		'terms' => array(),
		'price' => array(),
		'sort'  => '',
		'page'  => 1,
		'open'  => '',
	);

	foreach ( $filters as $key => $filter ) {

		if ( 'price' === $filter['type'] ) {
			$raw = isset( $source[ AVALLONE_CATALOG_PRICE_VAR ] ) ? sanitize_text_field( wp_unslash( $source[ AVALLONE_CATALOG_PRICE_VAR ] ) ) : '';

			if ( preg_match( '/^(\d+)-(\d+)$/', $raw, $m ) ) {
				$lo = max( (int) $filter['range']['min'], (int) $m[1] );
				$hi = min( (int) $filter['range']['max'], (int) $m[2] );

				if ( $lo < $hi ) {
					$state['price'] = array(
						'min' => $lo,
						'max' => $hi,
					);
				}
			}

			continue;
		}

		if ( empty( $source[ $key ] ) ) {
			continue;
		}

		$raw   = sanitize_text_field( wp_unslash( $source[ $key ] ) );
		$slugs = array_filter( array_map( 'sanitize_title', explode( ',', $raw ) ) );
		$valid = array();

		foreach ( array_unique( $slugs ) as $slug ) {
			$term = get_term_by( 'slug', $slug, $filter['tax'] );

			if ( $term instanceof WP_Term ) {
				$valid[] = $term->slug;
			}
		}

		if ( $valid ) {
			$state['terms'][ $key ] = $valid;
		}
	}

	$sort = isset( $source[ AVALLONE_CATALOG_SORT_VAR ] ) ? sanitize_key( wp_unslash( $source[ AVALLONE_CATALOG_SORT_VAR ] ) ) : '';

	if ( isset( avallone_catalog_sort_options()[ $sort ] ) ) {
		$state['sort'] = $sort;
	}

	$page          = isset( $source[ AVALLONE_CATALOG_PAGE_VAR ] ) ? absint( $source[ AVALLONE_CATALOG_PAGE_VAR ] ) : 1;
	$state['page'] = max( 1, $page );

	$open = isset( $source[ AVALLONE_CATALOG_OPEN_VAR ] ) ? sanitize_key( wp_unslash( $source[ AVALLONE_CATALOG_OPEN_VAR ] ) ) : '';

	if ( isset( $filters[ $open ] ) ) {
		$state['open'] = $open;
	}

	return $state;
}

/**
 * Normalise the price form's two inputs into the canonical `hind` variable.
 *
 * The panel needs two number fields to be usable without JavaScript, but the
 * catalogue's canonical state is a single `hind=min-max` variable. Rather than
 * teach the parser a second spelling, the submitted form is redirected once to
 * the canonical URL — so what lands in the address bar is always shareable and
 * always parses the same way.
 *
 * @return void
 */
function avallone_catalog_normalise_price_request() {
	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Public, read-only catalogue filtering.
	if ( is_admin() || ! isset( $_GET['catalog_price_min'], $_GET['catalog_price_max'] ) ) {
		return;
	}

	$min = absint( $_GET['catalog_price_min'] );
	$max = absint( $_GET['catalog_price_max'] );

	$args = $_GET;
	// phpcs:enable WordPress.Security.NonceVerification.Recommended

	unset( $args['catalog_price_min'], $args['catalog_price_max'], $args[ AVALLONE_CATALOG_PAGE_VAR ] );

	if ( $min < $max ) {
		$args[ AVALLONE_CATALOG_PRICE_VAR ] = $min . '-' . $max;
	} else {
		unset( $args[ AVALLONE_CATALOG_PRICE_VAR ] );
	}

	$args = array_map( 'sanitize_text_field', wp_unslash( $args ) );
	$url  = $args ? add_query_arg( $args, get_permalink() ) : get_permalink();

	wp_safe_redirect( $url, 302 );
	exit;
}
add_action( 'template_redirect', 'avallone_catalog_normalise_price_request' );

/**
 * Whether any product filter is active.
 *
 * Sorting and the open panel are not filters — clearing filters keeps them.
 *
 * @param array $state Catalogue state.
 * @return bool
 */
function avallone_catalog_has_filters( $state ) {
	return ! empty( $state['terms'] ) || ! empty( $state['price'] );
}

/**
 * Build the product query for a catalogue state.
 *
 * Always paginated, always constrained at the database level. Nothing is
 * filtered in PHP and posts_per_page is never -1.
 *
 * @param array $config Catalogue configuration.
 * @param array $state  Sanitised state.
 * @return WP_Query
 */
function avallone_catalog_query( $config, $state ) {
	$filters  = avallone_catalog_filters( $config );
	$per_page = max( 1, (int) ( $config['per_page'] ?? 12 ) );

	$tax_query = array( 'relation' => 'AND' );

	$category = (int) ( $config['category'] ?? 0 );

	if ( $category ) {
		$tax_query[] = array(
			'taxonomy'         => 'product_cat',
			'field'            => 'term_id',
			'terms'            => array( $category ),
			'include_children' => true,
		);
	}

	foreach ( $state['terms'] as $key => $slugs ) {
		if ( ! isset( $filters[ $key ]['tax'] ) ) {
			continue;
		}

		$tax_query[] = array(
			'taxonomy' => $filters[ $key ]['tax'],
			'field'    => 'slug',
			'terms'    => $slugs,
			'operator' => 'IN',
		);
	}

	// WooCommerce's own visibility rules — hidden and catalog-excluded products.
	if ( function_exists( 'WC' ) && isset( WC()->query ) ) {
		foreach ( (array) WC()->query->get_tax_query() as $clause ) {
			if ( is_array( $clause ) ) {
				$tax_query[] = $clause;
			}
		}
	}

	$args = array(
		'post_type'           => 'product',
		'post_status'         => 'publish',
		'posts_per_page'      => $per_page,
		'paged'               => max( 1, (int) $state['page'] ),
		'ignore_sticky_posts' => true,
		'tax_query'           => $tax_query, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
	);

	if ( ! empty( $state['price'] ) ) {
		$args['meta_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			array(
				'key'     => '_price',
				'value'   => array( $state['price']['min'], $state['price']['max'] ),
				'compare' => 'BETWEEN',
				'type'    => 'NUMERIC',
			),
		);
	}

	$sorts = avallone_catalog_sort_options();
	$sort  = $state['sort'] && isset( $sorts[ $state['sort'] ] ) ? $sorts[ $state['sort'] ] : reset( $sorts );

	$args['orderby'] = $sort['orderby'];
	$args['order']   = $sort['order'];

	if ( ! empty( $sort['meta_key'] ) ) {
		$args['meta_key'] = $sort['meta_key']; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
	}

	return new WP_Query( $args );
}

/**
 * Build a catalogue URL from a state plus a set of changes.
 *
 * Changes are applied on top of the current state, so removing one filter
 * always preserves the others and the chosen sort order.
 *
 * @param array  $state   Current state.
 * @param array  $changes Keys to override. Null or '' removes a variable.
 * @param string $base    Optional base URL. Defaults to the current page.
 * @return string
 */
function avallone_catalog_url( $state, $changes = array(), $base = '' ) {
	$base = $base ? $base : get_permalink();
	$args = array();

	foreach ( $state['terms'] as $key => $slugs ) {
		$args[ $key ] = implode( ',', $slugs );
	}

	if ( ! empty( $state['price'] ) ) {
		$args[ AVALLONE_CATALOG_PRICE_VAR ] = $state['price']['min'] . '-' . $state['price']['max'];
	}

	if ( $state['sort'] ) {
		$args[ AVALLONE_CATALOG_SORT_VAR ] = $state['sort'];
	}

	if ( $state['open'] ) {
		$args[ AVALLONE_CATALOG_OPEN_VAR ] = $state['open'];
	}

	foreach ( $changes as $key => $value ) {
		if ( null === $value || '' === $value ) {
			unset( $args[ $key ] );
			continue;
		}

		$args[ $key ] = is_array( $value ) ? implode( ',', $value ) : $value;
	}

	// Page 1 is the default and never needs to appear.
	if ( isset( $args[ AVALLONE_CATALOG_PAGE_VAR ] ) && 1 >= (int) $args[ AVALLONE_CATALOG_PAGE_VAR ] ) {
		unset( $args[ AVALLONE_CATALOG_PAGE_VAR ] );
	}

	return $args ? add_query_arg( $args, $base ) : $base;
}

/**
 * Toggle one term in a filter and return the resulting URL.
 *
 * @param array  $config Catalogue configuration.
 * @param array  $state  Current state.
 * @param string $key    Filter key.
 * @param string $slug   Term slug.
 * @return string
 */
function avallone_catalog_toggle_url( $config, $state, $key, $slug ) {
	$current = $state['terms'][ $key ] ?? array();

	$next = in_array( $slug, $current, true )
		? array_values( array_diff( $current, array( $slug ) ) )
		: array_merge( $current, array( $slug ) );

	return avallone_catalog_url(
		$state,
		array(
			$key                            => $next ? $next : null,
			AVALLONE_CATALOG_PAGE_VAR       => null,
		)
	);
}

/**
 * The active filter chips, each with the URL that removes it.
 *
 * @param array $config Catalogue configuration.
 * @param array $state  Current state.
 * @return array<int, array{label:string, remove:string}>
 */
function avallone_catalog_active_chips( $config, $state ) {
	$filters = avallone_catalog_filters( $config );
	$chips   = array();

	foreach ( $state['terms'] as $key => $slugs ) {
		if ( ! isset( $filters[ $key ] ) ) {
			continue;
		}

		foreach ( $slugs as $slug ) {
			$term = get_term_by( 'slug', $slug, $filters[ $key ]['tax'] );

			if ( ! $term instanceof WP_Term ) {
				continue;
			}

			$chips[] = array(
				'label'  => $term->name,
				'remove' => avallone_catalog_toggle_url( $config, $state, $key, $slug ),
			);
		}
	}

	if ( ! empty( $state['price'] ) ) {
		$chips[] = array(
			'label'  => sprintf(
				/* translators: 1: lowest price, 2: highest price. */
				__( '%1$s – %2$s', 'avallone' ),
				wp_strip_all_tags( wc_price( $state['price']['min'] ) ),
				wp_strip_all_tags( wc_price( $state['price']['max'] ) )
			),
			'remove' => avallone_catalog_url(
				$state,
				array(
					AVALLONE_CATALOG_PRICE_VAR => null,
					AVALLONE_CATALOG_PAGE_VAR  => null,
				)
			),
		);
	}

	return $chips;
}

/**
 * The URL that clears every filter but keeps the catalogue and its sorting.
 *
 * @param array $config Catalogue configuration.
 * @param array $state  Current state.
 * @return string
 */
function avallone_catalog_clear_url( $config, $state ) {
	$changes = array(
		AVALLONE_CATALOG_PRICE_VAR => null,
		AVALLONE_CATALOG_PAGE_VAR  => null,
	);

	foreach ( array_keys( avallone_catalog_filters( $config ) ) as $key ) {
		$changes[ $key ] = null;
	}

	return avallone_catalog_url( $state, $changes );
}

/**
 * Render one page of product cards.
 *
 * Shared by the template and the load-more endpoint so both produce identical
 * markup. Promotional blocks are deliberately not rendered here — they belong to
 * the first page's composition only, which is what stops load-more repeating
 * them.
 *
 * @param WP_Query $query Product query.
 * @return string
 */
function avallone_catalog_render_cards( $query ) {
	if ( ! $query->have_posts() ) {
		return '';
	}

	ob_start();

	while ( $query->have_posts() ) {
		$query->the_post();

		get_template_part(
			'template-parts/catalog/product-card',
			null,
			array( 'id' => get_the_ID() )
		);
	}

	wp_reset_postdata();

	return (string) ob_get_clean();
}

/**
 * Load-more endpoint.
 *
 * Re-runs the same sanitised query for the requested page and returns only the
 * new cards. The catalogue's configuration is resolved from the page ID, never
 * from the request, so a crafted payload cannot widen the query.
 *
 * @return void
 */
function avallone_catalog_load_more() {
	$page_id = isset( $_POST['page_id'] ) ? absint( $_POST['page_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Public read-only endpoint; verified below.

	check_ajax_referer( 'avallone_catalog', 'nonce' );

	$config = $page_id ? avallone_catalog_config_for_page( $page_id ) : array();

	if ( ! $config ) {
		wp_send_json_error( array( 'message' => __( 'Kataloogi ei leitud.', 'avallone' ) ), 404 );
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified above.
	$state = avallone_catalog_state( $config, wp_unslash( $_POST ) );
	$query = avallone_catalog_query( $config, $state );

	wp_send_json_success(
		array(
			'html'  => avallone_catalog_render_cards( $query ),
			'page'  => (int) $state['page'],
			'pages' => (int) $query->max_num_pages,
			'total' => (int) $query->found_posts,
		)
	);
}
add_action( 'wp_ajax_avallone_catalog_load_more', 'avallone_catalog_load_more' );
add_action( 'wp_ajax_nopriv_avallone_catalog_load_more', 'avallone_catalog_load_more' );

/**
 * Read a catalogue toggle, honouring the field's default before a first save.
 *
 * get_field() returns null when a page has never been saved with the field, so
 * a plain truthiness test would hide a block the editor never chose to hide.
 * Only an explicit false turns something off.
 *
 * @param string $name    Field name.
 * @param int    $page_id Page ID.
 * @param bool   $default Value to use when the field has never been saved.
 * @return bool
 */
function avallone_catalog_flag( $name, $page_id, $default = true ) {
	if ( ! function_exists( 'get_field' ) ) {
		return $default;
	}

	$value = get_field( $name, $page_id );

	return null === $value ? (bool) $default : (bool) $value;
}

/**
 * Whether the current request is a catalogue page.
 *
 * Used by the enqueue layer so the catalogue assets load for every catalogue
 * template rather than being tied to one page slug.
 *
 * @return bool
 */
function avallone_is_catalog_page() {
	if ( ! is_page() ) {
		return false;
	}

	return (bool) avallone_catalog_config_for_page( get_queried_object_id() );
}

/**
 * The catalogue configuration for a page, resolved from its template.
 *
 * One place maps templates to configurations, so the load-more endpoint and the
 * template itself can never disagree. Future catalogue templates register here.
 *
 * @param int $page_id Page ID.
 * @return array Empty when the page is not a catalogue.
 */
function avallone_catalog_config_for_page( $page_id ) {
	$template = get_page_template_slug( $page_id );

	$builders = array(
		'page-templates/template-vein.php' => 'avallone_vein_catalog_config',
	);

	if ( ! isset( $builders[ $template ] ) || ! function_exists( $builders[ $template ] ) ) {
		return array();
	}

	return call_user_func( $builders[ $template ], $page_id );
}
