<?php
/**
 * ACF PRO integration.
 *
 * Infrastructure only. No field groups, blocks or options pages are registered
 * in this phase — page components and their fields are built from Figma later.
 *
 * @package Avallone
 */

defined( 'ABSPATH' ) || exit;

/**
 * Absolute path to the theme's ACF JSON directory.
 *
 * @return string
 */
function avallone_acf_json_path() {
	return AVALLONE_DIR . '/acf-json';
}

/**
 * Save field groups into the theme so they are version controlled.
 *
 * ACF already defaults to the *stylesheet* directory. Pinning it to the
 * *template* directory instead means that if a child theme is ever activated,
 * field definitions stay next to the component templates that render them
 * rather than silently splitting across two directories.
 *
 * @param string $path Default save path.
 * @return string
 */
function avallone_acf_json_save_point( $path ) {
	return avallone_acf_json_path();
}
add_filter( 'acf/settings/save_json', 'avallone_acf_json_save_point' );

/**
 * Page templates that give a Page its own editing context.
 *
 * Adding a catalogue page means adding one line here and one entry to
 * avallone_acf_field_contexts().
 *
 * @return array<string, string> Template slug => context name.
 */
function avallone_acf_page_template_contexts() {
	return array(
		'page-templates/template-vein.php' => 'catalog_vein',
	);
}

/**
 * Which contexts each field-key prefix is allowed to appear in.
 *
 * This is a whitelist, deliberately: a prefix that is not listed is hidden
 * everywhere rather than shown everywhere. Nothing appears on a screen unless
 * it was explicitly granted to that screen, so an incomplete detection can
 * never leak the homepage fields onto an ordinary Page.
 *
 * Contexts are those returned by avallone_acf_current_context():
 * front_page, catalog_vein, ordinary_page, product, brand_term, other.
 *
 * When adding a field, add its prefix here. General settings that apply to
 * every Page belong with 'field_avallone_page_'.
 *
 * @return array<string, string[]> Key prefix => allowed contexts.
 */
function avallone_acf_field_contexts() {
	$homepage = array( 'front_page' );

	return array(
		// General page settings — every Page, whatever its template.
		'field_avallone_page_'     => array( 'front_page', 'catalog_vein', 'ordinary_page' ),

		// Homepage sections.
		'field_avallone_hero_'     => $homepage,
		'field_avallone_new_'      => $homepage,
		'field_avallone_banner_'   => $homepage,
		'field_avallone_deal_'     => $homepage,
		'field_avallone_brands_'   => $homepage,
		'field_avallone_popular_'  => $homepage,
		'field_avallone_cocktail_' => $homepage,

		// Catalogue template.
		'field_avallone_vein_'     => array( 'catalog_vein' ),

		// WooCommerce screens.
		'field_avallone_product_'  => array( 'product' ),
		'field_avallone_brand_'    => array( 'brand_term' ),

		/*
		 * Blocks. Their fields belong to the block being edited and never to the
		 * page-level metabox, which is what keeps an ordinary Page showing only
		 * "Lehe seaded".
		 */
		'field_avallone_pagebanner_' => array( 'block:avallone/page-banner' ),
		'field_avallone_about_'      => array( 'block:avallone/about-company' ),
		'field_avallone_partners_'   => array( 'block:avallone/global-partners' ),
		'field_avallone_presence_'   => array( 'block:avallone/our-presence' ),
	);
}

/**
 * The block ACF is currently building a form for, or '' for none.
 *
 * ACF fetches a block's inspector fields over AJAX and sends the block with the
 * request — but sends the *hosting page's* ID as the post ID, and no form data
 * at all. So the block has to be read from the request: inferring it from the
 * post would resolve every block on a Page to that Page's own context.
 *
 * @return string Block name, e.g. avallone/page-banner.
 */
function avallone_acf_block_context() {
	// phpcs:ignore WordPress.Security.NonceVerification -- Read-only screen check.
	if ( ! isset( $_REQUEST['block'] ) ) {
		return '';
	}

	// phpcs:ignore WordPress.Security.NonceVerification
	$block = json_decode( (string) wp_unslash( $_REQUEST['block'] ), true );

	if ( is_array( $block ) && ! empty( $block['name'] ) ) {
		return (string) $block['name'];
	}

	/*
	 * The request said "block" but did not parse. ACF also hands the name to its
	 * block location rule, which runs just before the fields are gathered, so
	 * that is a safe second reading — and it can only be reached inside a block
	 * request, never while the page metabox is being built.
	 */
	return avallone_acf_current_block();
}

/**
 * Remember which block ACF matched its block location rule against.
 *
 * A fallback for avallone_acf_block_context(); see the note there.
 *
 * @param string|null $name Block name to record, or null to read.
 * @return string
 */
function avallone_acf_current_block( $name = null ) {
	static $current = '';

	if ( null !== $name ) {
		$current = (string) $name;
	}

	return $current;
}

/**
 * Record the block name as ACF matches its location rule.
 *
 * @param bool  $result Match result.
 * @param array $rule   The location rule.
 * @param array $screen Screen args, carrying the block name.
 * @return bool Unmodified result.
 */
function avallone_acf_note_block_rule( $result, $rule, $screen ) {
	if ( ! empty( $screen['block'] ) ) {
		avallone_acf_current_block( $screen['block'] );
	}

	return $result;
}
add_filter( 'acf/location/match_rule/type=block', 'avallone_acf_note_block_rule', 10, 3 );

/**
 * The post being edited, including on a brand new Page.
 *
 * post-new.php carries no `post` request variable — WordPress has already
 * created the auto-draft and put it in the global $post instead. Reading only
 * the request is what made a new Page fall through to "context unknown".
 *
 * @param WP_Screen|null $screen Current screen, when available.
 * @return int
 */
function avallone_acf_current_post_id( $screen ) {
	foreach ( array( 'post', 'post_ID', 'post_id' ) as $key ) {
		// phpcs:ignore WordPress.Security.NonceVerification -- Read-only screen check.
		if ( isset( $_REQUEST[ $key ] ) && is_numeric( $_REQUEST[ $key ] ) ) {
			$id = (int) $_REQUEST[ $key ]; // phpcs:ignore WordPress.Security.NonceVerification

			if ( $id > 0 ) {
				return $id;
			}
		}
	}

	if ( $screen && 'post' === $screen->base ) {
		$post = get_post();

		if ( $post instanceof WP_Post ) {
			return (int) $post->ID;
		}
	}

	return 0;
}

/**
 * The template assigned to the Page being edited.
 *
 * ACF re-checks the screen over AJAX when post settings change and sends the
 * live template with it, so switching template updates the metabox without
 * waiting for a save. Otherwise the saved value is authoritative — an unsaved
 * Page has no template and is an ordinary Page.
 *
 * @param int $post_id Page ID.
 * @return string Template slug, or '' for the default template.
 */
function avallone_acf_current_page_template( $post_id ) {
	// phpcs:ignore WordPress.Security.NonceVerification -- Read-only screen check.
	if ( isset( $_REQUEST['page_template'] ) ) {
		// phpcs:ignore WordPress.Security.NonceVerification
		$template = sanitize_text_field( wp_unslash( $_REQUEST['page_template'] ) );

		if ( '' !== $template ) {
			return 'default' === $template ? '' : $template;
		}
	}

	return (string) get_page_template_slug( $post_id );
}

/**
 * Which admin screen the current request is editing.
 *
 * One resolver for every screen the single "Avallone" group loads on, so the
 * visibility rules have exactly one source of truth.
 *
 * Returns 'acf' for ACF's own admin screens, where this filter must not
 * interfere, and 'other' for anything unrecognised — which the whitelist treats
 * as "show none of our fields".
 *
 * @return string front_page|catalog_vein|ordinary_page|product|brand_term|
 *                block:<block-name>|acf|other
 */
function avallone_acf_current_context() {
	/*
	 * Block fields first, and before any post lookup: a block form's request
	 * carries the hosting page's ID, so resolving the post would send every
	 * block on a Page to that Page's context and hide the block's own fields.
	 */
	$block = avallone_acf_block_context();

	if ( '' !== $block ) {
		return 'block:' . $block;
	}

	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

	// ACF's own editors render our field definitions; never filter those.
	if ( $screen && ! empty( $screen->post_type ) && 0 === strpos( $screen->post_type, 'acf-' ) ) {
		return 'acf';
	}

	// Taxonomy term screens carry a taxonomy rather than a post.
	$taxonomy = '';

	if ( $screen && ! empty( $screen->taxonomy ) ) {
		$taxonomy = $screen->taxonomy;
	} elseif ( isset( $_REQUEST['taxonomy'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		$taxonomy = sanitize_key( wp_unslash( $_REQUEST['taxonomy'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
	}

	if ( 'product_brand' === $taxonomy ) {
		return 'brand_term';
	}

	if ( '' !== $taxonomy ) {
		return 'other';
	}

	$post_id = avallone_acf_current_post_id( $screen );

	if ( ! $post_id ) {
		return 'other';
	}

	$type = get_post_type( $post_id );

	if ( 'product' === $type ) {
		return 'product';
	}

	if ( 'page' !== $type ) {
		return 'other';
	}

	$front = (int) get_option( 'page_on_front' );

	if ( $front && $post_id === $front ) {
		return 'front_page';
	}

	$templates = avallone_acf_page_template_contexts();
	$template  = avallone_acf_current_page_template( $post_id );

	if ( isset( $templates[ $template ] ) ) {
		return $templates[ $template ];
	}

	// A Page with the default template — including a brand new auto-draft.
	return 'ordinary_page';
}

/**
 * Scope the single "Avallone" group's fields to the screen being edited.
 *
 * The group is deliberately one group loaded on Pages, Products and Brand
 * terms, so the newsletter toggle stays reachable site-wide and the product and
 * producer fields live alongside the rest. This filter decides which of its
 * fields belong on the screen in front of the editor.
 *
 * Tabs are fields too, so a tab whose context does not match is hidden along
 * with everything under it — the metabox shows no empty sections.
 *
 * @param array $field The field being prepared.
 * @return array|false The field, or false to hide it.
 */
function avallone_acf_scope_fields( $field ) {
	if ( ! is_admin() || empty( $field['key'] ) || 0 !== strpos( $field['key'], 'field_avallone_' ) ) {
		return $field;
	}

	$context = avallone_acf_current_context();

	if ( 'acf' === $context ) {
		return $field;
	}

	foreach ( avallone_acf_field_contexts() as $prefix => $contexts ) {
		if ( 0 === strpos( $field['key'], $prefix ) ) {
			return in_array( $context, $contexts, true ) ? $field : false;
		}
	}

	/*
	 * An Avallone field whose prefix is not in the map. Hidden rather than
	 * shown, so a forgotten entry can never leak a section onto the wrong
	 * screen — add the prefix to avallone_acf_field_contexts().
	 */
	return false;
}
add_filter( 'acf/prepare_field', 'avallone_acf_scope_fields' );

/**
 * Load field groups from the theme.
 *
 * Appended rather than replacing the defaults, so field groups registered by
 * plugins continue to load. For a parent theme ACF's own default already
 * resolves to this directory, so the guard keeps it from being scanned twice.
 *
 * @param string[] $paths Registered load paths.
 * @return string[]
 */
function avallone_acf_json_load_point( $paths ) {
	$path = avallone_acf_json_path();

	if ( ! in_array( $path, $paths, true ) ) {
		$paths[] = $path;
	}

	return $paths;
}
add_filter( 'acf/settings/load_json', 'avallone_acf_json_load_point' );
