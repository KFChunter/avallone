<?php
/**
 * Homepage — Blogi.
 *
 * The three newest posts. Fully automatic — there is nothing here for an editor
 * to choose, so the section has no ACF tab. "Kõik artiklid" points at the Posts
 * page and is omitted when the site has not designated one.
 *
 * @package Avallone
 */

defined( 'ABSPATH' ) || exit;

$avallone_blog_query = new WP_Query(
	array(
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'posts_per_page'      => 3,
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	)
);

if ( ! $avallone_blog_query->have_posts() ) {
	return;
}

$avallone_blog_page = (int) get_option( 'page_for_posts' );
$avallone_blog_href = $avallone_blog_page ? get_permalink( $avallone_blog_page ) : '';
?>

<section class="home-section home-blog" aria-labelledby="home-blog-heading">
	<div class="container">

		<div class="home-section__head">
			<h2 class="home-section__title" id="home-blog-heading"><?php esc_html_e( 'Blogi', 'avallone' ); ?></h2>
			<?php if ( $avallone_blog_href ) : ?>
				<a class="home-section__more" href="<?php echo esc_url( $avallone_blog_href ); ?>">
					<?php esc_html_e( 'Kõik artiklid', 'avallone' ); ?>
				</a>
			<?php endif; ?>
		</div>

		<ul class="home-blog__grid" role="list">
			<?php
			while ( $avallone_blog_query->have_posts() ) :
				$avallone_blog_query->the_post();

				$avallone_blog_cats = get_the_category();
				$avallone_blog_cat  = $avallone_blog_cats ? $avallone_blog_cats[0]->name : '';
				?>
				<li class="home-blog__item">
					<article class="blog-card">

						<a class="blog-card__media<?php echo has_post_thumbnail() ? '' : ' blog-card__media--plain'; ?>" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
							<?php
							if ( has_post_thumbnail() ) {
								the_post_thumbnail(
									'large',
									array(
										'class'   => 'blog-card__image',
										/* The title link right below carries the accessible name. */
										'alt'     => '',
										'loading' => 'lazy',
									)
								);
							}
							?>
						</a>

						<p class="blog-card__meta">
							<?php if ( '' !== $avallone_blog_cat ) : ?>
								<span class="blog-card__category"><?php echo esc_html( $avallone_blog_cat ); ?></span>
							<?php endif; ?>
							<time class="blog-card__date" datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>">
								<?php echo esc_html( get_the_date() ); ?>
							</time>
						</p>

						<h3 class="blog-card__title">
							<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
						</h3>

						<?php if ( has_excerpt() || get_the_excerpt() ) : ?>
							<p class="blog-card__excerpt"><?php echo esc_html( get_the_excerpt() ); ?></p>
						<?php endif; ?>

					</article>
				</li>
				<?php
			endwhile;

			wp_reset_postdata();
			?>
		</ul>

	</div>
</section>
