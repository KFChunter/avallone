<?php
/**
 * Closing page structure.
 *
 * PHASE 1: structural only. Closes the <main> opened in header.php and fires
 * wp_footer().
 *
 * The Avallone footer design (brand column, link columns, contact block, legal
 * alcohol warning, copyright bar — CVI §9) is NOT built yet. It will be added
 * as a template part at the marked slot below.
 *
 * @package Avallone
 */

defined( 'ABSPATH' ) || exit;
?>
</main><!-- #site-main -->

<?php /* Site footer component slot — added in a later phase. */ ?>

<?php wp_footer(); ?>
</body>
</html>
