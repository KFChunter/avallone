<?php
/**
 * Avallone theme bootstrap.
 *
 * Defines the shared constants and loads the theme's functionality from /inc.
 * Keep this file small: behaviour belongs in a focused /inc module, not here.
 *
 * @package Avallone
 */

defined( 'ABSPATH' ) || exit;

/**
 * Theme version. Kept in sync with the Version header in style.css and used as
 * the asset cache-busting fallback when a file's mtime is unavailable.
 */
define( 'AVALLONE_VERSION', '0.1.0' );

/** Absolute path to the theme directory, without a trailing slash. */
define( 'AVALLONE_DIR', get_template_directory() );

/** URI of the theme directory, without a trailing slash. */
define( 'AVALLONE_URI', get_template_directory_uri() );

require_once AVALLONE_DIR . '/inc/setup.php';
require_once AVALLONE_DIR . '/inc/enqueue.php';
require_once AVALLONE_DIR . '/inc/icons.php';
require_once AVALLONE_DIR . '/inc/svg.php';
require_once AVALLONE_DIR . '/inc/acf.php';
require_once AVALLONE_DIR . '/inc/newsletter.php';
require_once AVALLONE_DIR . '/inc/catalog.php';
require_once AVALLONE_DIR . '/inc/catalog-vein.php';
require_once AVALLONE_DIR . '/inc/product.php';
require_once AVALLONE_DIR . '/inc/woocommerce.php';
