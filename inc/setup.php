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

	/*
	 * Custom logo, sized to the CVI header spec (§7.2): 40px tall, ~121px wide.
	 * Uploading a logo under Appearance > Customize makes it appear in the
	 * header with no code change; until then the header falls back to the site
	 * name as text.
	 */
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 40,
			'width'       => 121,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

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

/**
 * Register navigation menu locations.
 *
 * Menu items are assigned by an editor under Appearance > Menus; the theme
 * never defines them. The header renders correctly when either location is
 * unassigned.
 *
 * Registered on 'init' rather than 'after_setup_theme': the labels below are
 * translated, and WordPress 6.7+ raises a _doing_it_wrong notice for any
 * translation loaded before 'init'.
 *
 * @return void
 */
function avallone_register_menus() {
	register_nav_menus(
		array(
			'primary' => esc_html__( 'Primary Navigation', 'avallone' ),
			'utility' => esc_html__( 'Utility Navigation', 'avallone' ),
		)
	);
}
add_action( 'init', 'avallone_register_menus' );

/**
 * Drop per-item ids from a menu rendered more than once on a page.
 *
 * The primary menu appears twice in the header — desktop nav and mobile drawer
 * — and WordPress gives every item an `id="menu-item-{ID}"`. Rendering it twice
 * would duplicate those ids, which is invalid HTML. The ids are unused by the
 * theme, so callers opt out by passing 'avallone_strip_item_ids' => true.
 *
 * @param string   $menu_id The menu item's DOM id.
 * @param WP_Post  $item    The menu item.
 * @param stdClass $args    Arguments passed to wp_nav_menu().
 * @return string
 */
function avallone_strip_menu_item_id( $menu_id, $item, $args ) {
	if ( ! empty( $args->avallone_strip_item_ids ) ) {
		return '';
	}

	return $menu_id;
}
add_filter( 'nav_menu_item_id', 'avallone_strip_menu_item_id', 10, 3 );
