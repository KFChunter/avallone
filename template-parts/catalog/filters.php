<?php
/**
 * Catalogue filter bar.
 *
 * Every control is a real link carrying the resulting query string, so the whole
 * filter system works with JavaScript disabled: the open panel, each toggled
 * term, the sort order and "clear all" are all ordinary navigations. catalog.js
 * only makes those transitions nicer.
 *
 * Nothing here is Vein-specific — the filters rendered are whatever the page's
 * configuration resolved to real taxonomy data.
 *
 * @package Avallone
 *
 * @param array $args {
 *     @type array    $config Catalogue configuration.
 *     @type array    $state  Sanitised catalogue state.
 *     @type WP_Query $query  The product query, for the result count.
 * }
 */

defined( 'ABSPATH' ) || exit;

$avallone_config = isset( $args['config'] ) ? $args['config'] : array();
$avallone_state  = isset( $args['state'] ) ? $args['state'] : array();
$avallone_query  = isset( $args['query'] ) ? $args['query'] : null;

$avallone_filters = avallone_catalog_filters( $avallone_config );
$avallone_chips   = avallone_catalog_active_chips( $avallone_config, $avallone_state );
$avallone_sorts   = avallone_catalog_sort_options();
$avallone_total   = $avallone_query instanceof WP_Query ? (int) $avallone_query->found_posts : 0;
$avallone_open    = $avallone_state['open'] ?? '';

$avallone_count_text = sprintf(
	/* translators: %s: number of products. */
	_n( '%s toode', '%s toodet', $avallone_total, 'avallone' ),
	number_format_i18n( $avallone_total )
);
?>

<section class="catalog-filters" aria-label="<?php esc_attr_e( 'Toodete filtrid', 'avallone' ); ?>">

	<?php
	/*
	 * Mobile control row. Hidden from assistive tech on desktop via CSS display,
	 * which removes it from the accessibility tree along with the visual layout.
	 */
	?>
	<div class="catalog-filters__mobile">
		<button
			class="catalog-filters__trigger"
			type="button"
			data-catalog-open-drawer
			aria-expanded="false"
			aria-controls="catalog-filter-drawer"
		>
			<?php esc_html_e( 'Filtreeri', 'avallone' ); ?>
			<?php if ( $avallone_chips ) : ?>
				<span class="catalog-filters__trigger-count"><?php echo esc_html( count( $avallone_chips ) ); ?></span>
			<?php endif; ?>
		</button>

		<p class="catalog-filters__count" data-catalog-count><?php echo esc_html( $avallone_count_text ); ?></p>
	</div>

	<div class="catalog-filters__panel" id="catalog-filter-drawer" data-catalog-drawer>

		<div class="catalog-filters__drawer-head">
			<h2 class="catalog-filters__drawer-title"><?php esc_html_e( 'Filtreeri', 'avallone' ); ?></h2>
			<button class="catalog-filters__drawer-close" type="button" data-catalog-close-drawer>
				<span class="screen-reader-text"><?php esc_html_e( 'Sulge filtrid', 'avallone' ); ?></span>
				<?php avallone_icon( 'close', array( 'size' => 20 ) ); ?>
			</button>
		</div>

		<div class="catalog-filters__scroll">

			<?php if ( $avallone_filters ) : ?>
				<div class="catalog-filters__bar">
					<ul class="catalog-filters__dropdowns" role="list">
						<?php foreach ( $avallone_filters as $avallone_key => $avallone_filter ) : ?>
							<?php
							$avallone_is_open   = $avallone_open === $avallone_key;
							$avallone_is_active = ! empty( $avallone_state['terms'][ $avallone_key ] )
								|| ( 'price' === $avallone_filter['type'] && ! empty( $avallone_state['price'] ) );

							$avallone_toggle_url = avallone_catalog_url(
								$avallone_state,
								array( AVALLONE_CATALOG_OPEN_VAR => $avallone_is_open ? null : $avallone_key )
							);
							?>
							<li class="catalog-filters__dropdown-item">
								<a
									class="catalog-filters__dropdown<?php echo $avallone_is_active ? ' is-active' : ''; ?><?php echo $avallone_is_open ? ' is-open' : ''; ?>"
									href="<?php echo esc_url( $avallone_toggle_url ); ?>"
									data-catalog-toggle="<?php echo esc_attr( $avallone_key ); ?>"
									aria-expanded="<?php echo $avallone_is_open ? 'true' : 'false'; ?>"
									aria-controls="catalog-group-<?php echo esc_attr( $avallone_key ); ?>"
								>
									<?php echo esc_html( $avallone_filter['label'] ); ?>
									<?php avallone_icon( 'chevron-down', array( 'size' => 16 ) ); ?>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>

					<p class="catalog-filters__count catalog-filters__count--bar" data-catalog-count>
						<?php echo esc_html( $avallone_count_text ); ?>
					</p>
				</div>

				<?php foreach ( $avallone_filters as $avallone_key => $avallone_filter ) : ?>
					<?php $avallone_is_open = $avallone_open === $avallone_key; ?>
					<div
						class="catalog-filters__group"
						id="catalog-group-<?php echo esc_attr( $avallone_key ); ?>"
						data-catalog-group="<?php echo esc_attr( $avallone_key ); ?>"
						<?php echo $avallone_is_open ? '' : 'hidden'; ?>
					>
						<h3 class="catalog-filters__group-title"><?php echo esc_html( $avallone_filter['label'] ); ?></h3>

						<?php if ( 'price' === $avallone_filter['type'] ) : ?>

							<form class="catalog-filters__price" method="get" action="<?php echo esc_url( get_permalink() ); ?>">
								<?php
								/*
								 * Carry the rest of the state through the form, so
								 * submitting a price range keeps the other filters.
								 */
								foreach ( $avallone_state['terms'] as $avallone_hidden_key => $avallone_hidden_slugs ) :
									?>
									<input type="hidden" name="<?php echo esc_attr( $avallone_hidden_key ); ?>" value="<?php echo esc_attr( implode( ',', $avallone_hidden_slugs ) ); ?>">
									<?php
								endforeach;

								if ( $avallone_state['sort'] ) :
									?>
									<input type="hidden" name="<?php echo esc_attr( AVALLONE_CATALOG_SORT_VAR ); ?>" value="<?php echo esc_attr( $avallone_state['sort'] ); ?>">
									<?php
								endif;
								?>
								<input type="hidden" name="<?php echo esc_attr( AVALLONE_CATALOG_OPEN_VAR ); ?>" value="<?php echo esc_attr( $avallone_key ); ?>">

								<div class="catalog-filters__price-field">
									<label for="catalog-price-min"><?php esc_html_e( 'Alates', 'avallone' ); ?></label>
									<input
										type="number"
										id="catalog-price-min"
										name="catalog_price_min"
										inputmode="numeric"
										min="<?php echo esc_attr( $avallone_filter['range']['min'] ); ?>"
										max="<?php echo esc_attr( $avallone_filter['range']['max'] ); ?>"
										value="<?php echo esc_attr( $avallone_state['price']['min'] ?? $avallone_filter['range']['min'] ); ?>"
									>
								</div>

								<div class="catalog-filters__price-field">
									<label for="catalog-price-max"><?php esc_html_e( 'Kuni', 'avallone' ); ?></label>
									<input
										type="number"
										id="catalog-price-max"
										name="catalog_price_max"
										inputmode="numeric"
										min="<?php echo esc_attr( $avallone_filter['range']['min'] ); ?>"
										max="<?php echo esc_attr( $avallone_filter['range']['max'] ); ?>"
										value="<?php echo esc_attr( $avallone_state['price']['max'] ?? $avallone_filter['range']['max'] ); ?>"
									>
								</div>

								<button class="catalog-filters__price-submit" type="submit"><?php esc_html_e( 'Rakenda', 'avallone' ); ?></button>
							</form>

						<?php else : ?>

							<ul class="catalog-filters__options" role="list">
								<?php foreach ( $avallone_filter['terms'] as $avallone_term ) : ?>
									<?php
									$avallone_selected = in_array(
										$avallone_term->slug,
										(array) ( $avallone_state['terms'][ $avallone_key ] ?? array() ),
										true
									);
									?>
									<li>
										<a
											class="catalog-filters__option<?php echo $avallone_selected ? ' is-selected' : ''; ?>"
											href="<?php echo esc_url( avallone_catalog_toggle_url( $avallone_config, $avallone_state, $avallone_key, $avallone_term->slug ) ); ?>"
											aria-pressed="<?php echo $avallone_selected ? 'true' : 'false'; ?>"
										>
											<?php echo esc_html( $avallone_term->name ); ?>
										</a>
									</li>
								<?php endforeach; ?>
							</ul>

						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			<?php endif; ?>

		</div>

		<div class="catalog-filters__drawer-foot">
			<?php if ( $avallone_chips ) : ?>
				<a class="catalog-filters__drawer-clear" href="<?php echo esc_url( avallone_catalog_clear_url( $avallone_config, $avallone_state ) ); ?>">
					<?php esc_html_e( 'Puhasta kõik', 'avallone' ); ?>
				</a>
			<?php endif; ?>
			<button class="catalog-filters__drawer-apply" type="button" data-catalog-close-drawer>
				<?php echo esc_html( sprintf( /* translators: %s: product count text. */ __( 'Vaata %s', 'avallone' ), $avallone_count_text ) ); ?>
			</button>
		</div>

	</div>

	<div class="catalog-filters__active">

		<div class="catalog-filters__active-left">
			<?php if ( $avallone_chips ) : ?>
				<p class="catalog-filters__active-label"><?php esc_html_e( 'Aktiivsed filtrid:', 'avallone' ); ?></p>

				<ul class="catalog-filters__chips" role="list">
					<?php foreach ( $avallone_chips as $avallone_chip ) : ?>
						<li>
							<a class="catalog-filters__chip" href="<?php echo esc_url( $avallone_chip['remove'] ); ?>">
								<span><?php echo esc_html( $avallone_chip['label'] ); ?></span>
								<span class="screen-reader-text"><?php esc_html_e( '— eemalda filter', 'avallone' ); ?></span>
								<?php avallone_icon( 'close', array( 'size' => 12 ) ); ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>

				<a class="catalog-filters__clear" href="<?php echo esc_url( avallone_catalog_clear_url( $avallone_config, $avallone_state ) ); ?>">
					<?php esc_html_e( 'Puhasta kõik', 'avallone' ); ?>
				</a>
			<?php endif; ?>
		</div>

		<form class="catalog-filters__sort" method="get" action="<?php echo esc_url( get_permalink() ); ?>">
			<?php
			foreach ( $avallone_state['terms'] as $avallone_hidden_key => $avallone_hidden_slugs ) :
				?>
				<input type="hidden" name="<?php echo esc_attr( $avallone_hidden_key ); ?>" value="<?php echo esc_attr( implode( ',', $avallone_hidden_slugs ) ); ?>">
				<?php
			endforeach;

			if ( ! empty( $avallone_state['price'] ) ) :
				?>
				<input type="hidden" name="<?php echo esc_attr( AVALLONE_CATALOG_PRICE_VAR ); ?>" value="<?php echo esc_attr( $avallone_state['price']['min'] . '-' . $avallone_state['price']['max'] ); ?>">
				<?php
			endif;
			?>

			<label class="catalog-filters__sort-label" for="catalog-sort"><?php esc_html_e( 'Sorteeri:', 'avallone' ); ?></label>

			<select class="catalog-filters__sort-select" id="catalog-sort" name="<?php echo esc_attr( AVALLONE_CATALOG_SORT_VAR ); ?>" data-catalog-sort>
				<?php foreach ( $avallone_sorts as $avallone_sort_key => $avallone_sort ) : ?>
					<option value="<?php echo esc_attr( $avallone_sort_key ); ?>" <?php selected( $avallone_state['sort'] ? $avallone_state['sort'] : array_key_first( $avallone_sorts ), $avallone_sort_key ); ?>>
						<?php echo esc_html( $avallone_sort['label'] ); ?>
					</option>
				<?php endforeach; ?>
			</select>

			<?php // Without JS the select needs a submit; catalog.js hides it and submits on change. ?>
			<button class="catalog-filters__sort-submit" type="submit"><?php esc_html_e( 'Sorteeri', 'avallone' ); ?></button>
		</form>

	</div>

</section>
