<?php
/**
 * Page template.
 *
 * The template-hierarchy home for Pages, which until now fell through to
 * index.php. Two things differ from that fallback.
 *
 * First, the title. index.php prints an <h1> for every singular view, which
 * would give a Page a second H1 whenever its content already opens with a hero.
 * The rule here is content-driven rather than page-specific: a Page whose
 * content carries the page-banner block supplies its own H1, so this template
 * stays quiet. Every other Page keeps its title exactly as before.
 *
 * Second, the wrapper. Avallone blocks each render their own full-width section
 * with an inner .container, the same way the homepage sections do, so a Page
 * built from blocks must not be wrapped in a container of its own — that is
 * what lets the banner run full width without any viewport-width arithmetic.
 * Ordinary editorial Pages keep the container.
 *
 * @package Avallone
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	$avallone_post = get_post();

	// Does the content open with a hero that already carries the H1?
	$avallone_has_banner = has_block( 'avallone/page-banner', $avallone_post );

	/*
	 * Any Avallone block manages its own width. A Page without them is ordinary
	 * editorial content and still needs the theme's container.
	 */
	$avallone_self_width = false;

	if ( has_blocks( $avallone_post ) ) {
		foreach ( parse_blocks( $avallone_post->post_content ) as $avallone_block ) {
			if ( ! empty( $avallone_block['blockName'] ) && 0 === strpos( $avallone_block['blockName'], 'avallone/' ) ) {
				$avallone_self_width = true;
				break;
			}
		}
	}
	?>

	<article id="post-<?php the_ID(); ?>" <?php post_class( 'page-content' ); ?>>

		<?php if ( ! $avallone_has_banner ) : ?>
			<header class="page-content__header">
				<div class="container">
					<h1 class="page-content__title"><?php the_title(); ?></h1>
				</div>
			</header>
		<?php endif; ?>

		<?php if ( $avallone_self_width ) : ?>
			<?php the_content(); ?>
		<?php else : ?>
			<div class="container">
				<?php the_content(); ?>
			</div>
		<?php endif; ?>

	</article>

	<?php
endwhile;

get_footer();
