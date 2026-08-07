<?php
/**
 * Header search form.
 *
 * Rendered twice — once in the desktop search panel, once in the mobile drawer
 * — so the field id is passed in to keep the two instances unique.
 *
 * Submits to the site's native WordPress search. The input inherits its styling
 * from assets/css/components/forms.css.
 *
 * @package Avallone
 *
 * @param array $args {
 *     @type string $id Unique id for the search field. Required.
 * }
 */

defined( 'ABSPATH' ) || exit;

$field_id = isset( $args['id'] ) ? $args['id'] : 'site-search-field';
?>

<form role="search" method="get" class="header-search" action="<?php echo esc_url( home_url( '/' ) ); ?>">

	<label class="screen-reader-text" for="<?php echo esc_attr( $field_id ); ?>">
		<?php esc_html_e( 'Otsi', 'avallone' ); ?>
	</label>

	<input
		type="search"
		class="header-search__field"
		id="<?php echo esc_attr( $field_id ); ?>"
		name="s"
		value="<?php echo get_search_query(); ?>"
		placeholder="<?php esc_attr_e( 'Otsi…', 'avallone' ); ?>"
	>

	<button type="submit" class="header-search__submit">
		<?php avallone_icon( 'search', array( 'size' => 18 ) ); ?>
		<span class="screen-reader-text"><?php esc_html_e( 'Otsi', 'avallone' ); ?></span>
	</button>

</form>
