<?php
/**
 * Template Name: Avallone – Vein
 *
 * The wine catalogue. Everything structural lives in the shared catalogue layer
 * (inc/catalog.php and template-parts/catalog/*) — this file only declares what
 * makes Vein different from the catalogue pages that will follow it.
 *
 * @package Avallone
 */

defined( 'ABSPATH' ) || exit;

get_header();

$avallone_page   = get_queried_object_id();
$avallone_config = avallone_vein_catalog_config( $avallone_page );
$avallone_state  = avallone_catalog_state( $avallone_config );
$avallone_query  = avallone_catalog_query( $avallone_config, $avallone_state );
?>

<div class="catalog">
	<div class="container">

		<?php
		get_template_part(
			'template-parts/catalog/hero',
			null,
			array(
				'eyebrow' => (string) get_field( 'vein_eyebrow', $avallone_page ),
				'intro'   => (string) get_field( 'vein_intro', $avallone_page ),
			)
		);

		get_template_part(
			'template-parts/catalog/filters',
			null,
			array(
				'config' => $avallone_config,
				'state'  => $avallone_state,
				'query'  => $avallone_query,
			)
		);

		get_template_part(
			'template-parts/catalog/grid',
			null,
			array(
				'config' => $avallone_config,
				'state'  => $avallone_state,
				'query'  => $avallone_query,
				'page'   => $avallone_page,
			)
		);
		?>

	</div>
</div>

<?php
get_footer();
