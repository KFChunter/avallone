<?php
/**
 * Site footer.
 *
 * Structure follows the Avallone footer design and CVI §9: a four-part top grid
 * (brand, products, company, contact) over a bottom bar carrying the copyright,
 * the Marketing Sharks mark and the legal links.
 *
 * Menu items are never defined here. Each column is wrapped in has_nav_menu()
 * and its wp_nav_menu() call passes 'fallback_cb' => false, so an unassigned
 * location produces no markup at all — heading included. A lone "TOOTED"
 * heading above nothing would read as broken rather than empty.
 *
 * @package Avallone
 */

defined( 'ABSPATH' ) || exit;

$avallone_logo_rel = 'assets/images/avallone-logo-footer.svg';

/*
 * Social profiles. No confirmed Avallone Facebook or Instagram URL exists yet,
 * so none is invented and href="#" is never emitted — while this array is empty
 * the whole social block is skipped. Add a real URL to switch it on; the icons
 * already exist in inc/icons.php.
 */
$avallone_socials = array(
	// 'facebook'  => 'https://www.facebook.com/...',
	// 'instagram' => 'https://www.instagram.com/...',
);

$avallone_social_labels = array(
	'facebook'  => __( 'Facebook', 'avallone' ),
	'instagram' => __( 'Instagram', 'avallone' ),
);
?>

<footer class="site-footer">

	<div class="container site-footer__grid">

		<div class="site-footer__brand">

			<img
				class="site-footer__logo"
				src="<?php echo esc_url( AVALLONE_URI . '/' . $avallone_logo_rel ); ?>"
				width="262"
				height="146"
				alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>"
			>

			<?php if ( $avallone_socials ) : ?>
				<ul class="site-footer__social" role="list">
					<?php foreach ( $avallone_socials as $avallone_network => $avallone_url ) : ?>
						<li>
							<a
								class="site-footer__social-link"
								href="<?php echo esc_url( $avallone_url ); ?>"
								rel="noopener"
								target="_blank"
							>
								<?php avallone_icon( $avallone_network, array( 'size' => 18 ) ); ?>
								<span class="screen-reader-text">
									<?php echo esc_html( $avallone_social_labels[ $avallone_network ] ?? $avallone_network ); ?>
								</span>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>

		</div>

		<?php if ( has_nav_menu( 'footer_products' ) ) : ?>
			<nav class="site-footer__col site-footer__col--products" aria-labelledby="footer-products-heading">
				<h2 class="type-h5 site-footer__heading" id="footer-products-heading">
					<?php esc_html_e( 'Tooted', 'avallone' ); ?>
				</h2>
				<?php
				wp_nav_menu(
					array(
						'theme_location'          => 'footer_products',
						'container'               => false,
						'menu_id'                 => 'footer-products-menu',
						'menu_class'              => 'site-footer__menu type-body-inter',
						'depth'                   => 1,
						'fallback_cb'             => false,
						'avallone_strip_item_ids' => true,
					)
				);
				?>
			</nav>
		<?php endif; ?>

		<?php if ( has_nav_menu( 'footer_company' ) ) : ?>
			<nav class="site-footer__col site-footer__col--company" aria-labelledby="footer-company-heading">
				<h2 class="type-h5 site-footer__heading" id="footer-company-heading">
					<?php esc_html_e( 'Ettevõte', 'avallone' ); ?>
				</h2>
				<?php
				wp_nav_menu(
					array(
						'theme_location'          => 'footer_company',
						'container'               => false,
						'menu_id'                 => 'footer-company-menu',
						'menu_class'              => 'site-footer__menu type-body-inter',
						'depth'                   => 1,
						'fallback_cb'             => false,
						'avallone_strip_item_ids' => true,
					)
				);
				?>
			</nav>
		<?php endif; ?>

		<section class="site-footer__col site-footer__contact" aria-labelledby="footer-contact-heading">

			<h2 class="type-h5 site-footer__heading" id="footer-contact-heading">
				<?php esc_html_e( 'AS Avallone', 'avallone' ); ?>
			</h2>

			<address class="site-footer__contact-details type-body-inter">
				<p><?php esc_html_e( 'Remmelga tänav 6-1, 11216, Tallinn', 'avallone' ); ?></p>
				<p><a href="tel:+3726828200">+372 682 8200</a></p>
				<p><a href="mailto:info@avallone.ee">info@avallone.ee</a></p>
			</address>

			<div class="site-footer__warning">
				<p class="type-warning">
					<?php esc_html_e( 'Tähelepanu! Tegemist on alkoholiga. Alkohol võib kahjustada teie tervist.', 'avallone' ); ?>
				</p>
			</div>

		</section>

	</div>

	<div class="container site-footer__bottom">

		<p class="type-small site-footer__copyright">
			<?php esc_html_e( '© 2024 Avallone. Kõik õigused kaitstud.', 'avallone' ); ?>
		</p>

		<?php /* Decorative agency mark. Artwork is masked in from an SVG asset so CSS drives its colour. */ ?>
		<span class="site-footer__credit" aria-hidden="true"></span>

		<?php if ( has_nav_menu( 'footer_legal' ) ) : ?>
			<nav class="site-footer__legal" aria-label="<?php esc_attr_e( 'Õiguslik teave', 'avallone' ); ?>">
				<?php
				wp_nav_menu(
					array(
						'theme_location'          => 'footer_legal',
						'container'               => false,
						'menu_id'                 => 'footer-legal-menu',
						'menu_class'              => 'site-footer__legal-menu type-small',
						'depth'                   => 1,
						'fallback_cb'             => false,
						'avallone_strip_item_ids' => true,
					)
				);
				?>
			</nav>
		<?php endif; ?>

	</div>

</footer>
