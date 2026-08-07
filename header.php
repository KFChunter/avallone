<?php
/**
 * Document head and opening page structure.
 *
 * Provides the HTML document skeleton and renders the site header component.
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

<?php get_template_part( 'template-parts/header/site-header' ); ?>

<main id="site-main" class="site-main">
