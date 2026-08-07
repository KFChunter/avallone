<?php
/**
 * Inline SVG icons.
 *
 * Icons are inlined so they inherit currentColor and need no HTTP request or
 * icon font. Geometry follows the Lucide set to match the CVI's icon language
 * (§11): outlined, 1.5px stroke, round caps and joins, 24px grid.
 *
 * Every icon is rendered aria-hidden — icons here are decorative, and the
 * control that contains one must carry its own accessible name.
 *
 * @package Avallone
 */

defined( 'ABSPATH' ) || exit;

/**
 * The icon set, as inner SVG markup on a 24x24 grid.
 *
 * @return array<string, string>
 */
function avallone_icon_set() {
	return array(
		'search' => '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>',
		'user'   => '<path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
		'cart'   => '<circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/>',
		'menu'   => '<path d="M4 6h16"/><path d="M4 12h16"/><path d="M4 18h16"/>',
		'close'  => '<path d="M18 6 6 18"/><path d="m6 6 12 12"/>',

		// Social marks, drawn to the same 1.5px outlined language as the rest.
		'facebook'  => '<path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>',
		'instagram' => '<rect width="20" height="20" x="2" y="2" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" x2="17.51" y1="6.5" y2="6.5"/>',
	);
}

/**
 * Build an inline SVG icon.
 *
 * @param string $name Icon name from avallone_icon_set().
 * @param array  $args {
 *     Optional.
 *
 *     @type int    $size  Rendered width and height in pixels. Default 24.
 *     @type string $class Extra class names for the <svg> element.
 * }
 * @return string SVG markup, or an empty string if the icon does not exist.
 */
function avallone_get_icon( $name, $args = array() ) {
	$icons = avallone_icon_set();

	if ( ! isset( $icons[ $name ] ) ) {
		return '';
	}

	$args = wp_parse_args(
		$args,
		array(
			'size'  => 24,
			'class' => '',
		)
	);

	$classes = trim( 'avallone-icon ' . $args['class'] );

	/*
	 * The path data comes from the constant set above, never from user input,
	 * so it is concatenated rather than escaped. Attributes are escaped.
	 */
	return sprintf(
		'<svg class="%1$s" width="%2$d" height="%2$d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">%3$s</svg>',
		esc_attr( $classes ),
		(int) $args['size'],
		$icons[ $name ]
	);
}

/**
 * Echo an inline SVG icon.
 *
 * @param string $name Icon name from avallone_icon_set().
 * @param array  $args Optional. See avallone_get_icon().
 * @return void
 */
function avallone_icon( $name, $args = array() ) {
	echo avallone_get_icon( $name, $args ); // phpcs:ignore WordPress.Security.EscapingOutput.OutputNotEscaped -- Theme-controlled SVG markup, escaped in avallone_get_icon().
}
