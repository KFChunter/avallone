<?php
/**
 * Asset loading.
 *
 * The theme has no build step. Instead of concatenating, each CSS layer is
 * registered as its own handle with the previous layer as its dependency, so
 * WordPress — not luck or source order — enforces the cascade.
 *
 * @package Avallone
 */

defined( 'ABSPATH' ) || exit;

/**
 * The theme's CSS layers, in cascade order.
 *
 * This is the single list of stylesheets. It is consumed by both the front-end
 * enqueue below and add_editor_style() in inc/setup.php. Component stylesheets
 * are appended here as components are built.
 *
 * The WooCommerce layer is deliberately absent: it is conditional and is
 * enqueued by inc/woocommerce.php only on shop pages.
 *
 * @return array<string, string> Handle => path relative to the theme root.
 */
function avallone_style_layers() {
	return array(
		'avallone-tokens'     => 'assets/css/settings/tokens.css',
		'avallone-reset'      => 'assets/css/base/reset.css',
		'avallone-typography' => 'assets/css/base/typography.css',
		'avallone-global'     => 'assets/css/base/global.css',
		'avallone-layout'     => 'assets/css/layout/layout.css',
		'avallone-buttons'    => 'assets/css/components/buttons.css',
		'avallone-forms'      => 'assets/css/components/forms.css',
		'avallone-header'     => 'assets/css/components/header.css',
		'avallone-footer'     => 'assets/css/components/footer.css',
	);
}

/**
 * Cache-busting version for a theme asset.
 *
 * Uses the file's modification time so each file busts independently — the
 * benefit a build step would otherwise provide, without the build step.
 *
 * @param string $relative_path Path relative to the theme root.
 * @return string
 */
function avallone_asset_version( $relative_path ) {
	$file = AVALLONE_DIR . '/' . $relative_path;

	if ( file_exists( $file ) ) {
		return (string) filemtime( $file );
	}

	return AVALLONE_VERSION;
}

/**
 * The Google Fonts stylesheet URL for the four CVI families.
 *
 * Weights and styles are exactly those the CVI requires (§3.1). Verified
 * against the CSS2 API: returns all four families across their subsets.
 *
 * @return string
 */
function avallone_google_fonts_url() {
	return 'https://fonts.googleapis.com/css2'
		. '?family=Playfair+Display:ital,wght@0,700;1,400;1,700'
		. '&family=Libre+Caslon+Text:ital,wght@0,400;1,400'
		. '&family=Work+Sans:wght@400;500;600;700'
		. '&family=Inter:ital,wght@0,400;0,500;0,600;0,700;1,600'
		. '&display=swap';
}

/**
 * Enqueue front-end styles.
 *
 * @return void
 */
function avallone_enqueue_styles() {

	// Fonts first, so the type layers resolve against loaded faces.
	// A null version keeps WordPress from appending ?ver= to a third-party URL.
	wp_enqueue_style( 'avallone-fonts', avallone_google_fonts_url(), array(), null );

	$previous = 'avallone-fonts';

	foreach ( avallone_style_layers() as $handle => $relative_path ) {
		wp_enqueue_style(
			$handle,
			AVALLONE_URI . '/' . $relative_path,
			array( $previous ),
			avallone_asset_version( $relative_path )
		);

		$previous = $handle;
	}
}
add_action( 'wp_enqueue_scripts', 'avallone_enqueue_styles' );

/**
 * Enqueue page-specific stylesheets.
 *
 * Page styles load only where they are used, so the global bundle stays the
 * same size on every other template. Registered after the last shared layer so
 * a page can build on the components without raising specificity.
 *
 * @return void
 */
function avallone_enqueue_page_styles() {
	$layers = avallone_style_layers();
	$last   = (string) array_key_last( $layers );

	if ( is_front_page() ) {
		$relative_path = 'assets/css/pages/front-page.css';

		wp_enqueue_style(
			'avallone-front-page',
			AVALLONE_URI . '/' . $relative_path,
			array( $last ),
			avallone_asset_version( $relative_path )
		);
	}

	/*
	 * The catalogue layer is shared by every catalogue template — Vein first,
	 * then the rest — so it loads for any of them rather than for one slug.
	 */
	if ( avallone_is_catalog_page() ) {
		$relative_path = 'assets/css/pages/catalog.css';

		wp_enqueue_style(
			'avallone-catalog',
			AVALLONE_URI . '/' . $relative_path,
			array( $last ),
			avallone_asset_version( $relative_path )
		);

		$script_path = 'assets/js/catalog.js';

		wp_enqueue_script(
			'avallone-catalog',
			AVALLONE_URI . '/' . $script_path,
			array(),
			avallone_asset_version( $script_path ),
			array(
				'strategy'  => 'defer',
				'in_footer' => true,
			)
		);

		wp_localize_script(
			'avallone-catalog',
			'avalloneCatalog',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'avallone_catalog' ),
				'pageId'  => get_queried_object_id(),
				'i18n'    => array(
					'loading' => __( 'Laadin…', 'avallone' ),
					'more'    => __( 'Lae rohkem tooteid', 'avallone' ),
					'error'   => __( 'Laadimine ebaõnnestus. Proovi uuesti.', 'avallone' ),
					/* translators: %s: number of products added. */
					'added'   => __( 'Lisatud %s toodet.', 'avallone' ),
					'end'     => __( 'Kõik tooted on kuvatud.', 'avallone' ),
				),
			)
		);
	}
}
add_action( 'wp_enqueue_scripts', 'avallone_enqueue_page_styles' );

/**
 * Enqueue front-end scripts.
 *
 * Vanilla JavaScript with no dependencies, deferred so it never blocks render.
 * Component scripts are added to $scripts as components are built.
 *
 * @return void
 */
function avallone_enqueue_scripts() {
	$scripts = array(
		'avallone-header' => 'assets/js/header.js',
	);

	foreach ( $scripts as $handle => $relative_path ) {
		wp_enqueue_script(
			$handle,
			AVALLONE_URI . '/' . $relative_path,
			array(),
			avallone_asset_version( $relative_path ),
			array(
				'strategy'  => 'defer',
				'in_footer' => true,
			)
		);
	}
}
add_action( 'wp_enqueue_scripts', 'avallone_enqueue_scripts' );

/**
 * Load the brand fonts inside the block editor.
 *
 * The editor canvas is iframed, so styles must be enqueued on
 * enqueue_block_assets to reach it. That hook also fires on the front end,
 * where avallone_enqueue_styles() already handles fonts — hence the is_admin()
 * guard.
 *
 * @return void
 */
function avallone_enqueue_editor_fonts() {
	if ( ! is_admin() ) {
		return;
	}

	wp_enqueue_style( 'avallone-fonts', avallone_google_fonts_url(), array(), null );
}
add_action( 'enqueue_block_assets', 'avallone_enqueue_editor_fonts' );

/**
 * Preconnect to the Google Fonts file host.
 *
 * WordPress already emits a dns-prefetch for fonts.googleapis.com. The font
 * files themselves come from fonts.gstatic.com, so without this hint they wait
 * on a second connection handshake.
 *
 * @param string[] $urls          URLs to print for the given relation.
 * @param string   $relation_type The relation type being printed.
 * @return array
 */
function avallone_resource_hints( $urls, $relation_type ) {
	if ( 'preconnect' === $relation_type && wp_style_is( 'avallone-fonts', 'enqueued' ) ) {
		$urls[] = array(
			'href'        => 'https://fonts.gstatic.com',
			'crossorigin' => 'anonymous',
		);
	}

	return $urls;
}
add_filter( 'wp_resource_hints', 'avallone_resource_hints', 10, 2 );
