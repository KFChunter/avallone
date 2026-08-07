/**
 * Site header interactions.
 *
 * Mobile drawer, desktop search panel and the sticky scroll state. Vanilla, no
 * dependencies. Every element lookup is optional — the header renders with
 * WooCommerce inactive and with no menus assigned, so nothing here assumes a
 * given control exists.
 *
 * Open/closed state is carried by the `hidden` attribute rather than a class,
 * so a closed panel leaves the accessibility tree and the tab order entirely.
 */

( function () {
	'use strict';

	var header = document.querySelector( '[data-site-header]' );

	if ( ! header ) {
		return;
	}

	var menuToggle   = header.querySelector( '[data-menu-toggle]' );
	var menuPanel    = header.querySelector( '[data-menu-panel]' );
	var menuLabel    = header.querySelector( '[data-menu-label]' );
	var iconOpen     = header.querySelector( '[data-menu-icon="open"]' );
	var iconClose    = header.querySelector( '[data-menu-icon="close"]' );
	var searchToggle = header.querySelector( '[data-search-toggle]' );
	var searchPanel  = header.querySelector( '[data-search-panel]' );
	var desktop      = window.matchMedia( '(min-width: 1024px)' );
	var root         = document.documentElement;

	/* ------------------------------------------------------------------
	   Scroll state
	   ------------------------------------------------------------------ */

	function syncScrollState() {
		header.classList.toggle( 'is-scrolled', window.scrollY > 8 );
	}

	window.addEventListener( 'scroll', syncScrollState, { passive: true } );
	syncScrollState();

	/* ------------------------------------------------------------------
	   Background scroll lock

	   Hiding the document scrollbar widens the page by its width. The gap is
	   measured and handed to CSS so the header does not jump sideways as the
	   drawer opens.
	   ------------------------------------------------------------------ */

	function lockScroll( locked ) {
		if ( locked ) {
			var gap = window.innerWidth - root.clientWidth;

			if ( gap > 0 ) {
				root.style.setProperty( '--scrollbar-gap', gap + 'px' );
			}

			root.classList.add( 'has-drawer-open' );
		} else {
			root.classList.remove( 'has-drawer-open' );
			root.style.removeProperty( '--scrollbar-gap' );
		}
	}

	/* ------------------------------------------------------------------
	   Mobile drawer
	   ------------------------------------------------------------------ */

	function menuIsOpen() {
		return !! menuToggle && menuToggle.getAttribute( 'aria-expanded' ) === 'true';
	}

	function setMenu( open ) {
		if ( ! menuToggle || ! menuPanel ) {
			return;
		}

		menuToggle.setAttribute( 'aria-expanded', String( open ) );
		menuPanel.hidden = ! open;

		if ( iconOpen ) {
			iconOpen.hidden = open;
		}

		if ( iconClose ) {
			iconClose.hidden = ! open;
		}

		if ( menuLabel ) {
			var next = open ? menuLabel.dataset.labelClose : menuLabel.dataset.labelOpen;

			if ( next ) {
				menuLabel.textContent = next;
			}
		}

		lockScroll( open );

		// Move focus into the panel rather than onto its first link, so opening
		// the drawer does not raise the on-screen keyboard for the search field.
		if ( open ) {
			menuPanel.focus();
		}
	}

	if ( menuToggle && menuPanel ) {
		menuPanel.setAttribute( 'tabindex', '-1' );

		menuToggle.addEventListener( 'click', function () {
			setMenu( ! menuIsOpen() );
		} );

		// Following a link inside the drawer closes it.
		menuPanel.addEventListener( 'click', function ( event ) {
			if ( event.target.closest( 'a[href]' ) ) {
				setMenu( false );
			}
		} );
	}

	/* ------------------------------------------------------------------
	   Desktop search panel
	   ------------------------------------------------------------------ */

	function searchIsOpen() {
		return !! searchToggle && searchToggle.getAttribute( 'aria-expanded' ) === 'true';
	}

	function setSearch( open ) {
		if ( ! searchToggle || ! searchPanel ) {
			return;
		}

		searchToggle.setAttribute( 'aria-expanded', String( open ) );
		searchPanel.hidden = ! open;

		if ( open ) {
			var field = searchPanel.querySelector( 'input[type="search"]' );

			if ( field ) {
				field.focus();
			}
		}
	}

	if ( searchToggle && searchPanel ) {
		searchToggle.addEventListener( 'click', function () {
			setSearch( ! searchIsOpen() );
		} );
	}

	/* ------------------------------------------------------------------
	   Escape closes whichever panel is open, and returns focus to its trigger
	   ------------------------------------------------------------------ */

	document.addEventListener( 'keydown', function ( event ) {
		if ( 'Escape' !== event.key ) {
			return;
		}

		if ( menuIsOpen() ) {
			setMenu( false );
			menuToggle.focus();
		} else if ( searchIsOpen() ) {
			setSearch( false );
			searchToggle.focus();
		}
	} );

	/* ------------------------------------------------------------------
	   Breakpoint changes

	   Each panel only exists on one side of 1024px. Without this a drawer left
	   open on a phone would keep aria-expanded="true" — and the scroll lock —
	   after a rotation or resize into desktop layout.
	   ------------------------------------------------------------------ */

	function syncBreakpoint( query ) {
		if ( query.matches ) {
			if ( menuIsOpen() ) {
				setMenu( false );
			}
		} else if ( searchIsOpen() ) {
			setSearch( false );
		}
	}

	desktop.addEventListener( 'change', syncBreakpoint );
	syncBreakpoint( desktop );
}() );
