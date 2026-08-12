<?php
/**
 * Front page.
 *
 * The homepage is assembled section by section here rather than through
 * Gutenberg blocks or an ACF flexible-content builder; each section is a
 * template part under template-parts/home/ and reads its content from the
 * "Avallone" ACF field group.
 *
 * @package Avallone
 */

defined( 'ABSPATH' ) || exit;

get_header();

get_template_part( 'template-parts/home/hero' );
get_template_part( 'template-parts/home/uued-tooted' );
get_template_part( 'template-parts/home/banner' );
get_template_part( 'template-parts/home/mega-diil' );

get_footer();
