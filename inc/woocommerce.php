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
 * Number of items currently in the cart.
 *
 * WC()->cart is null on admin, REST and cron requests, and before WooCommerce
 * has initialised its session — so every caller goes through this guard rather
 * than touching the cart object directly.
 *
 * @return int
 */
function avallone_cart_count() {
	if ( ! function_exists( 'WC' ) ) {
		return 0;
	}

	$woocommerce = WC();

	if ( ! $woocommerce || ! isset( $woocommerce->cart ) || ! $woocommerce->cart ) {
		return 0;
	}

	return (int) $woocommerce->cart->get_cart_contents_count();
}

/**
 * The cart count badge shown on the header cart action.
 *
 * Always rendered, carrying the `hidden` attribute at zero. WooCommerce's
 * fragment refresh replaces this element by CSS selector, so it has to exist in
 * the DOM even when empty — otherwise the badge would never appear after the
 * first AJAX add-to-cart.
 *
 * The count is followed by screen-reader-only text inside the same element, so
 * the visible number and its accessible description update together.
 *
 * @return string
 */
function avallone_get_cart_badge() {
	$count = avallone_cart_count();

	return sprintf(
		'<span class="site-header__cart-count"%1$s>%2$s<span class="screen-reader-text"> %3$s</span></span>',
		$count > 0 ? '' : ' hidden',
		esc_html( $count > 99 ? '99+' : (string) $count ),
		esc_html__( 'toodet ostukorvis', 'avallone' )
	);
}

/**
 * Keep the header cart badge in sync after AJAX add-to-cart.
 *
 * @param array $fragments Fragments to refresh, keyed by CSS selector.
 * @return array
 */
function avallone_cart_badge_fragment( $fragments ) {
	$fragments['span.site-header__cart-count'] = avallone_get_cart_badge();

	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'avallone_cart_badge_fragment' );

/**
 * Enqueue WooCommerce's cart fragments script.
 *
 * WooCommerce registers `wc-cart-fragments` but only enqueues it on its own
 * pages. The header cart badge is site-wide, so without this the badge would go
 * stale until the next full page load.
 *
 * This is the one place jQuery enters the theme, as a dependency of
 * WooCommerce's own script. Our header.js is vanilla.
 *
 * @return void
 */
function avallone_enqueue_cart_fragments() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	wp_enqueue_script( 'wc-cart-fragments' );
}
add_action( 'wp_enqueue_scripts', 'avallone_enqueue_cart_fragments' );

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
