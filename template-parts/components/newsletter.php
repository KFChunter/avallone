<?php
/**
 * Newsletter sign-up band.
 *
 * Rendered immediately before the footer on any page whose "Näita uudiskirja
 * plokki" toggle is on. See avallone_render_newsletter() in inc/newsletter.php.
 *
 * The form is deliberately not wired to a provider yet. It is a complete,
 * accessible form — labelled input, real submit — but there is no handler, so
 * submitting simply reloads the page. Nothing here fakes a successful
 * subscription. Integration is pending.
 *
 * @package Avallone
 */

defined( 'ABSPATH' ) || exit;

$avallone_news_text = __( 'Uued veinid, hoolikalt valitud joogid ja inspiratsioon otse Avallonelt sinu postkasti.', 'avallone' );
$avallone_news_id   = 'avallone-newsletter-email';
?>

<section class="newsletter" aria-labelledby="newsletter-heading">
	<div class="container newsletter__inner">

		<h2 class="newsletter__text" id="newsletter-heading"><?php echo esc_html( $avallone_news_text ); ?></h2>

		<form class="newsletter__form" method="post">
			<div class="newsletter__field">
				<label class="screen-reader-text" for="<?php echo esc_attr( $avallone_news_id ); ?>">
					<?php esc_html_e( 'E-posti aadress', 'avallone' ); ?>
				</label>

				<input
					class="newsletter__input"
					id="<?php echo esc_attr( $avallone_news_id ); ?>"
					type="email"
					name="avallone_newsletter_email"
					autocomplete="email"
					placeholder="<?php esc_attr_e( 'Emaili aadress', 'avallone' ); ?>"
					required
				>

				<button class="newsletter__submit" type="submit">
					<?php esc_html_e( 'Liitu', 'avallone' ); ?>
				</button>
			</div>
		</form>

	</div>
</section>
