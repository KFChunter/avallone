<?php
/**
 * ACF Blocks.
 *
 * Each block is a self-contained directory under /blocks holding its block.json,
 * its render template and its stylesheet. Registering from block.json is what
 * lets WordPress load a block's CSS only on pages that actually use it, and
 * feed the same file to the editor — no global bundle, no manual enqueue.
 *
 * The render template is shared between the editor preview and the front end,
 * so what an editor sees is what the page ships.
 *
 * @package Avallone
 */

defined( 'ABSPATH' ) || exit;

/**
 * The blocks this theme provides, as directory names under /blocks.
 *
 * @return string[]
 */
function avallone_block_directories() {
	return array(
		'page-banner',
		'about-company',
		'global-partners',
		'our-presence',
	);
}

/**
 * Register the blocks.
 *
 * ACF reads the `acf` key in each block.json for its render template and preview
 * mode, so nothing here needs to know about individual blocks.
 *
 * @return void
 */
function avallone_register_blocks() {
	if ( ! function_exists( 'acf_register_block_type' ) ) {
		return;
	}

	foreach ( avallone_block_directories() as $directory ) {
		$path = AVALLONE_DIR . '/blocks/' . $directory;

		if ( file_exists( $path . '/block.json' ) ) {
			register_block_type( $path );
		}
	}
}
add_action( 'init', 'avallone_register_blocks' );

/**
 * Add the Avallone block category.
 *
 * Placed first so the theme's own blocks are the first thing an editor sees in
 * the inserter.
 *
 * @param array $categories Registered categories.
 * @return array
 */
function avallone_block_category( $categories ) {
	array_unshift(
		$categories,
		array(
			'slug'  => 'avallone',
			'title' => __( 'Avallone', 'avallone' ),
			'icon'  => null,
		)
	);

	return $categories;
}
add_filter( 'block_categories_all', 'avallone_block_category' );

/**
 * Enqueue a block's stylesheet on pages that actually use it.
 *
 * block.json registers the handle and hands the same file to the editor, but
 * WordPress's on-demand enqueue resolves during render — after wp_head — so the
 * style would arrive late or, as here, not at all. Enqueuing it explicitly at
 * wp_enqueue_scripts keeps it conditional while still landing in the head, and
 * matches how the theme's other page stylesheets load.
 *
 * @return void
 */
function avallone_enqueue_block_styles() {
	if ( is_admin() ) {
		return;
	}

	/*
	 * The queried object, not get_post(): this runs during wp_head, before the
	 * loop has set the global post.
	 */
	$post = get_queried_object();

	if ( ! $post instanceof WP_Post || ! has_blocks( $post ) ) {
		return;
	}

	$layers = function_exists( 'avallone_style_layers' ) ? avallone_style_layers() : array();
	$last   = $layers ? array( (string) array_key_last( $layers ) ) : array();

	foreach ( avallone_block_directories() as $directory ) {
		$name = 'avallone/' . $directory;

		if ( ! has_block( $name, $post ) ) {
			continue;
		}

		$handle = 'avallone-' . $directory . '-style';

		if ( ! wp_style_is( $handle, 'registered' ) ) {
			continue;
		}

		// Ordered after the shared layers so a block can build on the components.
		foreach ( $last as $dependency ) {
			wp_styles()->registered[ $handle ]->deps[] = $dependency;
		}

		wp_enqueue_style( $handle );
	}
}
add_action( 'wp_enqueue_scripts', 'avallone_enqueue_block_styles', 20 );

/**
 * A stable, unique HTML id for one block instance.
 *
 * Used for aria-labelledby. The same block may appear more than once on a page,
 * so the id is suffixed with ACF's own per-instance block id rather than the
 * block name alone.
 *
 * @param array  $block  The block array ACF passes to the render template.
 * @param string $suffix Element name within the block.
 * @return string
 */
function avallone_block_id( $block, $suffix = 'title' ) {
	$id = isset( $block['id'] ) ? (string) $block['id'] : uniqid();
	$id = preg_replace( '/[^A-Za-z0-9_-]/', '', $id );

	$name = isset( $block['name'] ) ? (string) $block['name'] : 'block';
	$name = str_replace( '/', '-', $name );

	return sanitize_html_class( $name . '-' . $suffix . '-' . $id );
}

/**
 * Whether the block is being rendered inside the editor.
 *
 * Render templates use this to show a labelled placeholder for an empty block
 * instead of collapsing to nothing, which would leave it unselectable.
 *
 * @param array $block The block array.
 * @return bool
 */
function avallone_block_is_preview( $block ) {
	return ! empty( $block['is_preview'] );
}

/**
 * The wrapper class list for a block's outer element.
 *
 * Carries the block's own base class plus anything Gutenberg wants to add.
 *
 * @param array  $block The block array.
 * @param string $base  Base class name.
 * @return string
 */
function avallone_block_classes( $block, $base ) {
	$classes = array( $base );

	if ( ! empty( $block['className'] ) ) {
		$classes[] = $block['className'];
	}

	if ( avallone_block_is_preview( $block ) ) {
		$classes[] = 'is-editor-preview';
	}

	return implode( ' ', array_map( 'sanitize_html_class', $classes ) );
}
