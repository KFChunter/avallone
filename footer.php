<?php
/**
 * Closing page structure.
 *
 * Closes the <main> opened in header.php, renders the site footer component and
 * fires wp_footer() immediately before </body>.
 *
 * @package Avallone
 */

defined( 'ABSPATH' ) || exit;
?>
</main><!-- #site-main -->

<?php get_template_part( 'template-parts/footer/site-footer' ); ?>

<?php wp_footer(); ?>
</body>
</html>
