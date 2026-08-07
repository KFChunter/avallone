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
