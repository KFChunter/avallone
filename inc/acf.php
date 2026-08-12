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
 * A prefix listed here is shown only on pages using that template. Everything
 * else under `field_avallone_` (except the `_page_` settings prefix) belongs to
 * the front page. Adding a catalogue page means adding one line.
 *
 * @return array<string, string> Key prefix => page template slug.
 */
function avallone_acf_template_prefixes() {
	return array(
		'field_avallone_vein_' => 'page-templates/template-vein.php',
	);
}

/**
 * Scope the single "Avallone" group's fields to where they belong.
 *
 * The group is deliberately one group loaded on every Page, so that the
 * newsletter toggle under "Lehe seaded" is reachable site-wide. Without this
 * filter an ordinary Page would also show every hero, banner, cocktail and
 * catalogue field.
 *
 * The rule is the field key:
 *   - `field_avallone_page_*`  general page settings — always shown
 *   - a prefix in avallone_acf_template_prefixes() — only on that template
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
	if ( ! is_admin() || empty( $field['key'] ) ) {
		return $field;
	}

	// General page settings, and anything not ours, are always left alone.
	if ( 0 !== strpos( $field['key'], 'field_avallone_' )
		|| 0 === strpos( $field['key'], 'field_avallone_page_' ) ) {
		return $field;
	}

	$post_id = 0;

	if ( isset( $_GET['post'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only screen check.
		$post_id = (int) $_GET['post']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	} elseif ( isset( $_POST['post_ID'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only screen check.
		$post_id = (int) $_POST['post_ID']; // phpcs:ignore WordPress.Security.NonceVerification.Missing
	}

	/*
	 * Only page edit screens are scoped. This also keeps the filter clear of
	 * ACF's own field-group editor, which runs on post.php too.
	 */
	if ( ! $post_id || 'page' !== get_post_type( $post_id ) ) {
		return $field;
	}

	$template = (string) get_page_template_slug( $post_id );

	foreach ( avallone_acf_template_prefixes() as $prefix => $slug ) {
		if ( 0 === strpos( $field['key'], $prefix ) ) {
			return $template === $slug ? $field : false;
		}
	}

	// Anything left is homepage-only, and never belongs on a template page.
	if ( isset( array_flip( avallone_acf_template_prefixes() )[ $template ] ) ) {
		return false;
	}

	return (int) $post_id === (int) get_option( 'page_on_front' ) ? $field : false;
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
