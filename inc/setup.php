<?php
/**
 * Theme setup.
 *
 * Registers the WordPress features the theme relies on. WooCommerce theme
 * support lives in inc/woocommerce.php so that all shop concerns stay together.
 *
 * @package Avallone
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register theme support.
 *
 * Note on translations: the theme declares "Text Domain: avallone" in
 * style.css, which is all WordPress 6.7+ needs to just-in-time load a
 * translation file. load_theme_textdomain() is intentionally not called — it
 * would be a no-op until we actually ship a /languages directory.
 *
 * @return void
 */
function avallone_setup() {

	// Let WordPress manage the document title.
	add_theme_support( 'title-tag' );

	// Featured images, used by posts and WooCommerce products.
	add_theme_support( 'post-thumbnails' );

	add_theme_support( 'automatic-feed-links' );

	// Modern, valid markup from core-generated forms and galleries.
	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
			'navigation-widgets',
		)
	);

	// Block editor.
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'editor-styles' );

	/*
	 * Feed the editor the exact same CSS layers the front end uses, in the same
	 * order, so the two cannot drift apart. The Google Fonts stylesheet is not
	 * included here on purpose: add_editor_style() fetches remote URLs
	 * server-side on every editor load. It is enqueued into the editor iframe
	 * by avallone_enqueue_editor_fonts() in inc/enqueue.php instead.
	 */
	add_editor_style( array_values( avallone_style_layers() ) );
}
add_action( 'after_setup_theme', 'avallone_setup' );
