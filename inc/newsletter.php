<?php
/**
 * Newsletter band.
 *
 * The band is a per-page opt-in rather than part of the footer, so it hooks the
 * get_footer action instead of being written into footer.php. That keeps
 * footer.php untouched and guarantees the band cannot be rendered twice.
 *
 * @package Avallone
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether the current request should show the newsletter band.
 *
 * Read once per request: both the stylesheet enqueue and the render need the
 * answer, and they run at different times.
 *
 * @return bool
 */
function avallone_show_newsletter() {
	static $show = null;

	if ( null !== $show ) {
		return $show;
	}

	$show = false;

	if ( is_singular() && function_exists( 'get_field' ) ) {
		$show = (bool) get_field( 'page_show_newsletter', get_queried_object_id() );
	}

	return $show;
}

/**
 * Enqueue the newsletter stylesheet only where the band is shown.
 *
 * Registered after the last shared layer so it can build on the components
 * without raising specificity.
 *
 * @return void
 */
function avallone_enqueue_newsletter_styles() {
	if ( ! avallone_show_newsletter() ) {
		return;
	}

	$layers        = avallone_style_layers();
	$relative_path = 'assets/css/components/newsletter.css';

	wp_enqueue_style(
		'avallone-newsletter',
		AVALLONE_URI . '/' . $relative_path,
		array( (string) array_key_last( $layers ) ),
		avallone_asset_version( $relative_path )
	);
}
add_action( 'wp_enqueue_scripts', 'avallone_enqueue_newsletter_styles' );

/**
 * Render the newsletter band immediately before the footer.
 *
 * @return void
 */
function avallone_render_newsletter() {
	if ( ! avallone_show_newsletter() ) {
		return;
	}

	get_template_part( 'template-parts/components/newsletter' );
}
add_action( 'get_footer', 'avallone_render_newsletter' );
