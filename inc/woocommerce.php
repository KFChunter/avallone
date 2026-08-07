<?php
/**
 * WooCommerce integration.
 *
 * Foundation only. The theme declares support, places shop pages inside the
 * theme's own container, and loads a thin compatibility stylesheet.
 *
 * No WooCommerce template is overridden. The shop, product card, archive,
 * single product, cart, checkout and account designs are built from their
 * Figma components in a later phase.
 *
 * @package Avallone
 */

defined( 'ABSPATH' ) || exit;

/**
 * Declare WooCommerce theme support.
 *
 * Without 'woocommerce', WooCommerce falls back to its unsupported-theme shim
 * and warns in the admin. The gallery supports enable zoom, lightbox and the
 * slider on single product pages.
 *
 * Safe to call unconditionally — add_theme_support() has no effect when the
 * plugin is inactive.
 *
 * @return void
 */
function avallone_woocommerce_setup() {
	add_theme_support( 'woocommerce' );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );
}
add_action( 'after_setup_theme', 'avallone_woocommerce_setup' );

/**
 * Whether the current request is a WooCommerce-rendered page.
 *
 * @return bool
 */
function avallone_is_woocommerce_page() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return false;
	}

	return is_woocommerce() || is_cart() || is_checkout() || is_account_page();
}

/**
 * Open the theme's content wrapper around WooCommerce output.
 *
 * Deliberately does not open a <main>: header.php already provides one, and
 * nesting <main> elements is invalid. This replaces WooCommerce's own wrapper
 * so its pages sit inside the theme layout without copying a single template.
 *
 * @return void
 */
function avallone_woocommerce_wrapper_start() {
	echo '<div class="container">';
}

/**
 * Close the theme's content wrapper around WooCommerce output.
 *
 * @return void
 */
function avallone_woocommerce_wrapper_end() {
	echo '</div>';
}

/**
 * Swap WooCommerce's content wrappers for the theme's.
 *
 * @return void
 */
function avallone_woocommerce_hooks() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
	remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );

	add_action( 'woocommerce_before_main_content', 'avallone_woocommerce_wrapper_start', 10 );
	add_action( 'woocommerce_after_main_content', 'avallone_woocommerce_wrapper_end', 10 );

	// The Avallone design has no shop sidebar.
	remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );
}
add_action( 'init', 'avallone_woocommerce_hooks' );

/**
 * Enqueue the WooCommerce compatibility stylesheet.
 *
 * Loaded only on shop pages, so it never reaches the global bundle. It depends
 * on the last theme layer and on WooCommerce's own stylesheets, which puts it
 * last in the cascade by dependency rather than by luck — that is what lets the
 * file avoid !important entirely.
 *
 * Priority 20 so WooCommerce has registered its handles by the time this runs.
 *
 * @return void
 */
function avallone_woocommerce_enqueue_styles() {
	if ( ! avallone_is_woocommerce_page() ) {
		return;
	}

	$layers = avallone_style_layers();
	$deps   = array( (string) array_key_last( $layers ) );

	foreach ( array( 'woocommerce-layout', 'woocommerce-smallscreen', 'woocommerce-general' ) as $handle ) {
		if ( wp_style_is( $handle, 'registered' ) ) {
			$deps[] = $handle;
		}
	}

	$relative_path = 'assets/css/woocommerce/woocommerce.css';

	wp_enqueue_style(
		'avallone-woocommerce',
		AVALLONE_URI . '/' . $relative_path,
		$deps,
		avallone_asset_version( $relative_path )
	);
}
add_action( 'wp_enqueue_scripts', 'avallone_woocommerce_enqueue_styles', 20 );
