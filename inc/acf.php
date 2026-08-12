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
 * Which page template each field-key prefix belongs to.
 *
 * A prefix listed here is shown only on pages using that template. Adding a
 * catalogue page means adding one line.
 *
 * @return array<string, string> Key prefix => page template slug.
 */
function avallone_acf_template_prefixes() {
	return array(
		'field_avallone_vein_' => 'page-templates/template-vein.php',
	);
}

/**
 * Which admin screen the current request is editing.
 *
 * The single "Avallone" group loads on Pages, Products and Brand terms, so the
 * visibility filter needs to know which of those it is preparing fields for.
 *
 * @return array{context:string, post_id:int} Context is 'front-page', 'page',
 *         'template:<slug>', 'product', 'brand' or '' when it is none of them.
 */
function avallone_acf_current_context() {
	$post_id = 0;

	if ( isset( $_GET['post'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only screen check.
		$post_id = (int) $_GET['post']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	} elseif ( isset( $_POST['post_ID'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only screen check.
		$post_id = (int) $_POST['post_ID']; // phpcs:ignore WordPress.Security.NonceVerification.Missing
	}

	// Taxonomy term screens carry the taxonomy in the request, not a post ID.
	$taxonomy = '';

	if ( isset( $_GET['taxonomy'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only screen check.
		$taxonomy = sanitize_key( wp_unslash( $_GET['taxonomy'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	} elseif ( isset( $_POST['taxonomy'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only screen check.
		$taxonomy = sanitize_key( wp_unslash( $_POST['taxonomy'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
	} elseif ( function_exists( 'get_current_screen' ) ) {
		$screen = get_current_screen();

		if ( $screen && ! empty( $screen->taxonomy ) ) {
			$taxonomy = $screen->taxonomy;
		}
	}

	if ( 'product_brand' === $taxonomy ) {
		return array(
			'context' => 'brand',
			'post_id' => 0,
		);
	}

	if ( ! $post_id ) {
		return array(
			'context' => '',
			'post_id' => 0,
		);
	}

	$type = get_post_type( $post_id );

	if ( 'product' === $type ) {
		return array(
			'context' => 'product',
			'post_id' => $post_id,
		);
	}

	if ( 'page' !== $type ) {
		return array(
			'context' => '',
			'post_id' => $post_id,
		);
	}

	if ( (int) $post_id === (int) get_option( 'page_on_front' ) ) {
		return array(
			'context' => 'front-page',
			'post_id' => $post_id,
		);
	}

	$template = (string) get_page_template_slug( $post_id );

	return array(
		'context' => $template ? 'template:' . $template : 'page',
		'post_id' => $post_id,
	);
}

/**
 * Scope the single "Avallone" group's fields to where they belong.
 *
 * One group loads on every Page, on Products and on Brand terms, so that the
 * newsletter toggle stays reachable site-wide and the product and producer
 * fields live alongside the rest. Without this filter each of those screens
 * would show all of the others' fields.
 *
 * The rule is the field key:
 *   - `field_avallone_page_*`     general page settings — every Page
 *   - `field_avallone_product_*`  Product editor only
 *   - `field_avallone_brand_*`    product_brand term editor only
 *   - a prefix in avallone_acf_template_prefixes() — that page template only
 *   - every other `field_avallone_*` — front page only
 *
 * Tabs are fields too, so a hidden tab takes its contents with it.
 *
 * Note that ACF renders fields server-side: after switching a page's template
 * the page must be saved once before that template's fields appear.
 *
 * @param array $field The field being prepared.
 * @return array|false The field, or false to hide it.
 */
function avallone_acf_scope_homepage_fields( $field ) {
	if ( ! is_admin() || empty( $field['key'] ) || 0 !== strpos( $field['key'], 'field_avallone_' ) ) {
		return $field;
	}

	$current = avallone_acf_current_context();
	$context = $current['context'];

	// Not one of our screens — ACF's own field-group editor, for instance.
	if ( '' === $context ) {
		return $field;
	}

	// Screens that own exactly one prefix show that prefix and nothing else.
	$exclusive = array(
		'product' => 'field_avallone_product_',
		'brand'   => 'field_avallone_brand_',
	);

	if ( isset( $exclusive[ $context ] ) ) {
		return 0 === strpos( $field['key'], $exclusive[ $context ] ) ? $field : false;
	}

	// From here the screen is a Page. Product and brand fields never belong.
	foreach ( $exclusive as $prefix ) {
		if ( 0 === strpos( $field['key'], $prefix ) ) {
			return false;
		}
	}

	// General page settings are reachable on every Page.
	if ( 0 === strpos( $field['key'], 'field_avallone_page_' ) ) {
		return $field;
	}

	foreach ( avallone_acf_template_prefixes() as $prefix => $slug ) {
		if ( 0 === strpos( $field['key'], $prefix ) ) {
			return 'template:' . $slug === $context ? $field : false;
		}
	}

	// Anything left is homepage-only.
	return 'front-page' === $context ? $field : false;
}
add_filter( 'acf/prepare_field', 'avallone_acf_scope_homepage_fields' );

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
