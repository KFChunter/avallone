<?php
/**
 * Catalogue hero.
 *
 * Eyebrow, page title and intro. The H1 is the WordPress Page title — there is
 * deliberately no second title field for an editor to keep in sync.
 *
 * @package Avallone
 *
 * @param array $args {
 *     @type string $eyebrow Small label above the title.
 *     @type string $intro   Introductory paragraph.
 * }
 */

defined( 'ABSPATH' ) || exit;

$avallone_eyebrow = isset( $args['eyebrow'] ) ? trim( (string) $args['eyebrow'] ) : '';
$avallone_intro   = isset( $args['intro'] ) ? trim( (string) $args['intro'] ) : '';
?>

<header class="catalog-hero">

	<?php if ( '' !== $avallone_eyebrow ) : ?>
		<p class="catalog-hero__eyebrow"><?php echo esc_html( $avallone_eyebrow ); ?></p>
	<?php endif; ?>

	<h1 class="catalog-hero__title"><?php the_title(); ?></h1>

	<?php if ( '' !== $avallone_intro ) : ?>
		<div class="catalog-hero__intro"><?php echo wp_kses_post( wpautop( $avallone_intro ) ); ?></div>
	<?php endif; ?>

</header>
