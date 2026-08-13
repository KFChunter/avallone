<?php
/**
 * Block — Lehe bänner.
 *
 * A full-width page hero. Reusable on any Page, which is why it carries the
 * page's H1: a Page whose content opens with this block suppresses its own
 * title (see page.php), so there is exactly one H1 either way.
 *
 * The same template renders the editor preview and the front end.
 *
 * @package Avallone
 *
 * @param array $block The block settings and attributes.
 */

defined( 'ABSPATH' ) || exit;

$avallone_image = (int) get_field( 'pagebanner_image' );
$avallone_badge = trim( (string) get_field( 'pagebanner_badge' ) );
$avallone_title = trim( (string) get_field( 'pagebanner_title' ) );
$avallone_text  = trim( (string) get_field( 'pagebanner_text' ) );

$avallone_preview = avallone_block_is_preview( $block );
$avallone_empty   = ! $avallone_image && '' === $avallone_title && '' === $avallone_text;

// An empty block would collapse to nothing and become unselectable in the editor.
if ( $avallone_empty && ! $avallone_preview ) {
	return;
}

$avallone_heading_id = avallone_block_id( $block, 'title' );

/*
 * Without a photograph there is nothing for the scrim to darken, and light type
 * on the pale surface would be unreadable. The modifier drops the scrim and
 * switches the type to ink.
 */
$avallone_classes = avallone_block_classes( $block, 'page-banner' );

if ( ! $avallone_image ) {
	$avallone_classes .= ' page-banner--plain';
}
?>

<section
	class="<?php echo esc_attr( $avallone_classes ); ?>"
	<?php if ( '' !== $avallone_title ) : ?>
		aria-labelledby="<?php echo esc_attr( $avallone_heading_id ); ?>"
	<?php endif; ?>
>

	<div class="page-banner__bg" aria-hidden="true">
		<?php
		if ( $avallone_image ) {
			echo wp_get_attachment_image(
				$avallone_image,
				'full',
				false,
				array(
					'class'         => 'page-banner__image',
					'alt'           => '',
					'fetchpriority' => 'high',
					'decoding'      => 'async',
					'sizes'         => '100vw',
				)
			);
		}
		?>
	</div>

	<div class="page-banner__inner">
		<?php if ( $avallone_empty ) : ?>

			<p class="page-banner__placeholder">
				<?php esc_html_e( 'Lehe bänner — vali taustapilt ja lisa pealkiri.', 'avallone' ); ?>
			</p>

		<?php else : ?>

			<?php if ( '' !== $avallone_badge ) : ?>
				<p class="page-banner__badge"><?php echo esc_html( $avallone_badge ); ?></p>
			<?php endif; ?>

			<?php if ( '' !== $avallone_title ) : ?>
				<h1 class="page-banner__title" id="<?php echo esc_attr( $avallone_heading_id ); ?>">
					<?php echo esc_html( $avallone_title ); ?>
				</h1>
			<?php endif; ?>

			<?php if ( '' !== $avallone_text ) : ?>
				<p class="page-banner__text"><?php echo esc_html( $avallone_text ); ?></p>
			<?php endif; ?>

		<?php endif; ?>
	</div>

</section>
