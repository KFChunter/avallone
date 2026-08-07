<?php
/**
 * Site header.
 *
 * Structure follows CVI §7/§8: a 40px utility bar above an 80px main nav, both
 * sticky, both aligned to the global .container grid.
 *
 * Menu items are never defined here. Each wp_nav_menu() call passes
 * 'fallback_cb' => false so that an unassigned location outputs nothing at all,
 * and each nav block is wrapped in has_nav_menu() so no empty landmark is
 * announced. Assigning menus under Appearance > Menus populates the header with
 * no code change.
 *
 * Below 1024px (CVI §18) the primary nav and utility bar collapse into the
 * drawer, and the action row reduces to cart + hamburger — at 375px there is
 * not room for four 44px targets beside the logo at the CVI's 24px icon gap.
 *
 * @package Avallone
 */

defined( 'ABSPATH' ) || exit;

$avallone_has_primary = has_nav_menu( 'primary' );
$avallone_has_utility = has_nav_menu( 'utility' );
$avallone_cart_url    = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : '';
$avallone_account_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : '';
?>

<header id="site-header" class="site-header" data-site-header>

	<div class="site-header__utility">
		<div class="container site-header__utility-inner">

			<p class="site-header__promo">
				<?php esc_html_e( 'Hea maitse liidab - tasuta tarne tellimuselt üle 80€', 'avallone' ); ?>
			</p>

			<div class="site-header__utility-end">

				<?php if ( $avallone_has_utility ) : ?>
					<nav class="site-header__utility-nav" aria-label="<?php esc_attr_e( 'Lisanavigatsioon', 'avallone' ); ?>">
						<?php
						wp_nav_menu(
							array(
								'theme_location'          => 'utility',
								'container'               => false,
								'menu_id'                 => 'utility-menu-bar',
								'menu_class'              => 'site-header__utility-menu',
								'depth'                   => 1,
								'fallback_cb'             => false,
								'avallone_strip_item_ids' => true,
							)
						);
						?>
					</nav>
				<?php endif; ?>

				<?php
				/*
				 * Language switcher slot (CVI §7.1). Left empty deliberately: no
				 * translation plugin is active, and a switcher that cannot switch
				 * anything would be a lie. It belongs here, after the utility nav.
				 */
				?>

			</div>

		</div>
	</div>

	<div class="site-header__main">
		<div class="container site-header__main-inner">

			<div class="site-header__brand">
				<?php if ( has_custom_logo() ) : ?>
					<?php the_custom_logo(); ?>
				<?php else : ?>
					<?php /* Placeholder until a logo is uploaded under Appearance > Customize. */ ?>
					<a class="site-header__brand-fallback" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
						<?php bloginfo( 'name' ); ?>
					</a>
				<?php endif; ?>
			</div>

			<?php if ( $avallone_has_primary ) : ?>
				<nav class="site-header__nav" aria-label="<?php esc_attr_e( 'Peamine navigatsioon', 'avallone' ); ?>">
					<?php
					wp_nav_menu(
						array(
							'theme_location'          => 'primary',
							'container'               => false,
							'menu_id'                 => 'primary-menu-desktop',
							'menu_class'              => 'site-header__menu',
							'depth'                   => 1,
							'fallback_cb'             => false,
							'avallone_strip_item_ids' => true,
						)
					);
					?>
				</nav>
			<?php endif; ?>

			<div class="site-header__actions">

				<button
					type="button"
					class="site-header__action site-header__action--search"
					aria-expanded="false"
					aria-controls="site-header-search"
					data-search-toggle
				>
					<?php avallone_icon( 'search', array( 'size' => 18 ) ); ?>
					<span class="screen-reader-text"><?php esc_html_e( 'Otsi', 'avallone' ); ?></span>
				</button>

				<?php if ( $avallone_account_url ) : ?>
					<a class="site-header__action site-header__action--account" href="<?php echo esc_url( $avallone_account_url ); ?>">
						<?php avallone_icon( 'user', array( 'size' => 16 ) ); ?>
						<span class="screen-reader-text"><?php esc_html_e( 'Minu konto', 'avallone' ); ?></span>
					</a>
				<?php endif; ?>

				<?php if ( $avallone_cart_url ) : ?>
					<a class="site-header__action site-header__action--cart" href="<?php echo esc_url( $avallone_cart_url ); ?>">
						<?php avallone_icon( 'cart', array( 'size' => 20 ) ); ?>
						<span class="screen-reader-text"><?php esc_html_e( 'Ostukorv', 'avallone' ); ?></span>
						<?php echo avallone_get_cart_badge(); // phpcs:ignore WordPress.Security.EscapingOutput.OutputNotEscaped -- Escaped in avallone_get_cart_badge(). ?>
					</a>
				<?php endif; ?>

				<button
					type="button"
					class="site-header__action site-header__burger"
					aria-expanded="false"
					aria-controls="site-header-drawer"
					data-menu-toggle
				>
					<span class="site-header__burger-icon" data-menu-icon="open">
						<?php avallone_icon( 'menu', array( 'size' => 24 ) ); ?>
					</span>
					<span class="site-header__burger-icon" data-menu-icon="close" hidden>
						<?php avallone_icon( 'close', array( 'size' => 24 ) ); ?>
					</span>
					<span
						class="screen-reader-text"
						data-menu-label
						data-label-open="<?php esc_attr_e( 'Ava menüü', 'avallone' ); ?>"
						data-label-close="<?php esc_attr_e( 'Sulge menüü', 'avallone' ); ?>"
					><?php esc_html_e( 'Ava menüü', 'avallone' ); ?></span>
				</button>

			</div>

		</div>
	</div>

	<?php /* Desktop search panel. The drawer carries its own copy below. */ ?>
	<div class="site-header__search" id="site-header-search" hidden data-search-panel>
		<div class="container site-header__search-inner">
			<?php
			get_template_part(
				'template-parts/header/search-form',
				null,
				array( 'id' => 'site-header-search-field' )
			);
			?>
		</div>
	</div>

	<div class="site-header__drawer" id="site-header-drawer" hidden data-menu-panel>
		<div class="container site-header__drawer-inner">

			<div class="site-header__drawer-search">
				<?php
				get_template_part(
					'template-parts/header/search-form',
					null,
					array( 'id' => 'site-header-drawer-field' )
				);
				?>
			</div>

			<?php if ( $avallone_has_primary ) : ?>
				<nav class="site-header__drawer-nav" aria-label="<?php esc_attr_e( 'Mobiilne navigatsioon', 'avallone' ); ?>">
					<?php
					wp_nav_menu(
						array(
							'theme_location'          => 'primary',
							'container'               => false,
							'menu_id'                 => 'primary-menu-drawer',
							'menu_class'              => 'site-header__drawer-menu',
							'depth'                   => 1,
							'fallback_cb'             => false,
							'avallone_strip_item_ids' => true,
						)
					);
					?>
				</nav>
			<?php endif; ?>

			<div class="site-header__drawer-footer">

				<?php if ( $avallone_account_url ) : ?>
					<a class="site-header__drawer-account" href="<?php echo esc_url( $avallone_account_url ); ?>">
						<?php avallone_icon( 'user', array( 'size' => 16 ) ); ?>
						<?php esc_html_e( 'Minu konto', 'avallone' ); ?>
					</a>
				<?php endif; ?>

				<?php if ( $avallone_has_utility ) : ?>
					<nav class="site-header__drawer-utility" aria-label="<?php esc_attr_e( 'Lisanavigatsioon (mobiil)', 'avallone' ); ?>">
						<?php
						wp_nav_menu(
							array(
								'theme_location'          => 'utility',
								'container'               => false,
								'menu_id'                 => 'utility-menu-drawer',
								'menu_class'              => 'site-header__drawer-utility-menu',
								'depth'                   => 1,
								'fallback_cb'             => false,
								'avallone_strip_item_ids' => true,
							)
						);
						?>
					</nav>
				<?php endif; ?>

			</div>

		</div>
	</div>

</header>
