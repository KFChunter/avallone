<?php
/**
 * Homepage — Banner.
 *
 * Full-bleed editorial band: photograph, brand overlay, centred content.
 * The image is a real <img> layer rather than an inline background-image, so it
 * gets srcset and intrinsic dimensions.
 *
 * @package Avallone
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'get_field' ) ) {
	return;
}

$avallone_banner_page  = get_queried_object_id();
$avallone_banner_image = (int) get_field( 'banner_image', $avallone_banner_page );
$avallone_banner_icon  = (string) get_field( 'banner_icon', $avallone_banner_page );
$avallone_banner_title = (string) get_field( 'banner_title', $avallone_banner_page );
$avallone_banner_text  = (string) get_field( 'banner_text', $avallone_banner_page );
$avallone_banner_label = (string) get_field( 'banner_label', $avallone_banner_page );
$avallone_banner_cta   = get_field( 'banner_cta', $avallone_banner_page );

// Nothing to say and nothing to show — skip the band entirely.
if ( '' === $avallone_banner_title && '' === $avallone_banner_text && ! $avallone_banner_image ) {
	return;
}
?>

<section class="home-banner" aria-labelledby="home-banner-heading">

	<div class="home-banner__bg" aria-hidden="true">
		<?php
		if ( $avallone_banner_image ) {
			echo wp_get_attachment_image(
				$avallone_banner_image,
				'full',
				false,
				array(
					'class'   => 'home-banner__image',
					'alt'     => '',
					'loading' => 'lazy',
				)
			);
		}
		?>
	</div>

	<div class="container home-banner__inner">

		<?php if ( '' !== $avallone_banner_icon ) : ?>
			<span class="home-banner__icon" aria-hidden="true">
				<?php avallone_icon( $avallone_banner_icon, array( 'size' => 28 ) ); ?>
			</span>
		<?php endif; ?>

		<?php if ( '' !== $avallone_banner_title ) : ?>
			<h2 class="home-banner__title" id="home-banner-heading"><?php echo esc_html( $avallone_banner_title ); ?></h2>
		<?php endif; ?>

		<?php if ( '' !== $avallone_banner_text ) : ?>
			<p class="home-banner__text"><?php echo esc_html( $avallone_banner_text ); ?></p>
		<?php endif; ?>

		<?php if ( '' !== $avallone_banner_label ) : ?>
			<p class="home-banner__label"><?php echo esc_html( $avallone_banner_label ); ?></p>
		<?php endif; ?>

		<?php if ( ! empty( $avallone_banner_cta['url'] ) ) : ?>
			<a
				class="home-banner__cta"
				href="<?php echo esc_url( $avallone_banner_cta['url'] ); ?>"
				<?php if ( ! empty( $avallone_banner_cta['target'] ) ) : ?>
					target="<?php echo esc_attr( $avallone_banner_cta['target'] ); ?>" rel="noopener"
				<?php endif; ?>
			><?php echo esc_html( $avallone_banner_cta['title'] ); ?></a>
		<?php endif; ?>

	</div>

</section>
