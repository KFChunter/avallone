<?php
/**
 * Universal fallback template.
 *
 * PHASE 1 ships no page-specific templates, so WordPress falls back to this
 * file for every request (front page, single, page, archive, search, 404).
 * It renders the loop with no layout design — page templates and their designs
 * arrive with the Figma components in a later phase.
 *
 * @package Avallone
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<div class="container">

	<?php if ( have_posts() ) : ?>

		<?php
		while ( have_posts() ) :
			the_post();
			?>

			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

				<?php if ( is_singular() ) : ?>
					<h1><?php the_title(); ?></h1>
				<?php else : ?>
					<h2>
						<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
					</h2>
				<?php endif; ?>

				<?php
				if ( is_singular() ) {
					the_content();
				} else {
					the_excerpt();
				}
				?>

			</article>

			<?php
		endwhile;

		the_posts_pagination();

	else :
		?>
		<p><?php esc_html_e( 'Nothing found.', 'avallone' ); ?></p>
		<?php
	endif;
	?>

</div>

<?php
get_footer();
