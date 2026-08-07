<?php
/**
 * Document head and opening page structure.
 *
 * PHASE 1: structural only. This file provides the HTML document skeleton so
 * that wp_head() can load the theme's stylesheets — nothing renders without it.
 *
 * The Avallone header design (utility bar, main nav, logo, action icons — CVI
 * §7/§8) is NOT built yet. It will be added as a template part at the marked
 * slot below.
 *
 * @package Avallone
 */

defined( 'ABSPATH' ) || exit;
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link screen-reader-text" href="#site-main">
	<?php esc_html_e( 'Skip to content', 'avallone' ); ?>
</a>

<?php /* Site header component slot — added in a later phase. */ ?>

<main id="site-main" class="site-main">
