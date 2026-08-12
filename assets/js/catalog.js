/**
 * Product catalogue enhancements.
 *
 * Everything here is an upgrade to markup that already works without it: the
 * filter links navigate, the sort form submits, and "load more" is a real link
 * to the next page. This file makes those transitions happen in place.
 *
 * Vanilla, dependency-free, and tolerant of any element being absent.
 */
( function () {
	'use strict';

	var config = window.avalloneCatalog || {};
	var i18n = config.i18n || {};

	var root = document.querySelector( '.catalog-filters' );
	var grid = document.querySelector( '[data-catalog-grid]' );

	if ( ! root && ! grid ) {
		return;
	}

	/* ------------------------------------------------------------------ *
	 * Filter panels — toggle in place instead of navigating.
	 * ------------------------------------------------------------------ */

	var toggles = root ? root.querySelectorAll( '[data-catalog-toggle]' ) : [];

	function closeAllGroups( except ) {
		Array.prototype.forEach.call( toggles, function ( toggle ) {
			var key = toggle.getAttribute( 'data-catalog-toggle' );

			if ( key === except ) {
				return;
			}

			var group = document.querySelector( '[data-catalog-group="' + key + '"]' );

			toggle.setAttribute( 'aria-expanded', 'false' );
			toggle.classList.remove( 'is-open' );

			if ( group ) {
				group.hidden = true;
			}
		} );
	}

	Array.prototype.forEach.call( toggles, function ( toggle ) {
		toggle.addEventListener( 'click', function ( event ) {
			// Only take over on the desktop bar; the drawer shows every group.
			if ( window.matchMedia( '(max-width: 1023px)' ).matches ) {
				return;
			}

			var key = toggle.getAttribute( 'data-catalog-toggle' );
			var group = document.querySelector( '[data-catalog-group="' + key + '"]' );

			if ( ! group ) {
				return;
			}

			event.preventDefault();

			var willOpen = group.hidden;

			closeAllGroups( willOpen ? key : null );

			group.hidden = ! willOpen;
			toggle.setAttribute( 'aria-expanded', willOpen ? 'true' : 'false' );
			toggle.classList.toggle( 'is-open', willOpen );

			// Keep the URL honest so a refresh reopens the same panel.
			try {
				window.history.replaceState( {}, '', toggle.href );
			} catch ( e ) {
				/* History is unavailable; the panel still works. */
			}
		} );
	} );

	/* ------------------------------------------------------------------ *
	 * Sort — submit on change, so the extra button is not needed.
	 * ------------------------------------------------------------------ */

	var sortSelect = root ? root.querySelector( '[data-catalog-sort]' ) : null;

	if ( sortSelect && sortSelect.form ) {
		var sortSubmit = sortSelect.form.querySelector( '.catalog-filters__sort-submit' );

		if ( sortSubmit ) {
			sortSubmit.hidden = true;
		}

		sortSelect.addEventListener( 'change', function () {
			sortSelect.form.submit();
		} );
	}

	/* ------------------------------------------------------------------ *
	 * Mobile filter drawer.
	 * ------------------------------------------------------------------ */

	var drawer = document.querySelector( '[data-catalog-drawer]' );
	var openers = document.querySelectorAll( '[data-catalog-open-drawer]' );
	var closers = document.querySelectorAll( '[data-catalog-close-drawer]' );
	var backdrop = null;
	var lastFocused = null;

	function focusable() {
		if ( ! drawer ) {
			return [];
		}

		return Array.prototype.filter.call(
			drawer.querySelectorAll( 'a[href], button:not([disabled]), input, select, textarea' ),
			function ( el ) {
				return el.getClientRects().length > 0;
			}
		);
	}

	function openDrawer() {
		if ( ! drawer ) {
			return;
		}

		lastFocused = document.activeElement;

		if ( ! backdrop ) {
			backdrop = document.createElement( 'div' );
			backdrop.className = 'catalog-filters__backdrop';
			backdrop.addEventListener( 'click', closeDrawer );
			drawer.parentNode.insertBefore( backdrop, drawer );
		}

		drawer.classList.add( 'is-open' );
		backdrop.classList.add( 'is-open' );
		drawer.setAttribute( 'aria-modal', 'true' );
		drawer.setAttribute( 'role', 'dialog' );
		document.documentElement.style.overflow = 'hidden';

		Array.prototype.forEach.call( openers, function ( el ) {
			el.setAttribute( 'aria-expanded', 'true' );
		} );

		/*
		 * Next frame: the panel has to be painted visible before anything
		 * inside it can take focus.
		 */
		window.requestAnimationFrame( function () {
			var first = focusable()[ 0 ];

			if ( first ) {
				first.focus();
			}
		} );
	}

	function closeDrawer() {
		if ( ! drawer ) {
			return;
		}

		drawer.classList.remove( 'is-open' );

		if ( backdrop ) {
			backdrop.classList.remove( 'is-open' );
		}

		drawer.removeAttribute( 'aria-modal' );
		drawer.removeAttribute( 'role' );
		document.documentElement.style.overflow = '';

		Array.prototype.forEach.call( openers, function ( el ) {
			el.setAttribute( 'aria-expanded', 'false' );
		} );

		if ( lastFocused && lastFocused.focus ) {
			lastFocused.focus();
		}
	}

	Array.prototype.forEach.call( openers, function ( el ) {
		el.addEventListener( 'click', openDrawer );
	} );

	Array.prototype.forEach.call( closers, function ( el ) {
		el.addEventListener( 'click', closeDrawer );
	} );

	document.addEventListener( 'keydown', function ( event ) {
		if ( ! drawer || ! drawer.classList.contains( 'is-open' ) ) {
			return;
		}

		if ( 'Escape' === event.key ) {
			closeDrawer();
			return;
		}

		if ( 'Tab' !== event.key ) {
			return;
		}

		var items = focusable();

		if ( ! items.length ) {
			return;
		}

		var first = items[ 0 ];
		var last = items[ items.length - 1 ];

		if ( event.shiftKey && document.activeElement === first ) {
			event.preventDefault();
			last.focus();
		} else if ( ! event.shiftKey && document.activeElement === last ) {
			event.preventDefault();
			first.focus();
		}
	} );

	// A drawer left open when the viewport grows would trap focus off-screen.
	window.matchMedia( '(min-width: 1024px)' ).addEventListener( 'change', function ( event ) {
		if ( event.matches ) {
			closeDrawer();
		}
	} );

	/* ------------------------------------------------------------------ *
	 * Load more — append the next page in place.
	 * ------------------------------------------------------------------ */

	var moreButton = document.querySelector( '[data-catalog-load-more]' );
	var status = document.querySelector( '[data-catalog-status]' );

	function setStatus( message, isError ) {
		if ( ! status ) {
			return;
		}

		status.textContent = message || '';
		status.classList.toggle( 'is-error', !! isError );
	}

	if ( moreButton && grid && config.ajaxUrl && config.nonce ) {
		moreButton.addEventListener( 'click', function ( event ) {
			event.preventDefault();

			if ( moreButton.classList.contains( 'is-loading' ) ) {
				return;
			}

			var page = parseInt( moreButton.getAttribute( 'data-page' ), 10 ) || 2;
			var max = parseInt( moreButton.getAttribute( 'data-max' ), 10 ) || page;
			var label = moreButton.querySelector( '[data-catalog-more-label]' );

			moreButton.classList.add( 'is-loading' );
			moreButton.setAttribute( 'aria-busy', 'true' );

			if ( label ) {
				label.textContent = i18n.loading || 'Loading…';
			}

			setStatus( '' );

			// The current query string carries the filters and sort order.
			var body = new URLSearchParams( window.location.search );
			body.set( 'action', 'avallone_catalog_load_more' );
			body.set( 'nonce', config.nonce );
			body.set( 'page_id', config.pageId );
			body.set( 'lk', String( page ) );

			window.fetch( config.ajaxUrl, {
				method: 'POST',
				credentials: 'same-origin',
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
				body: body.toString()
			} )
				.then( function ( response ) {
					if ( ! response.ok ) {
						throw new Error( 'HTTP ' + response.status );
					}

					return response.json();
				} )
				.then( function ( payload ) {
					if ( ! payload || ! payload.success || ! payload.data ) {
						throw new Error( 'Malformed response' );
					}

					var html = ( payload.data.html || '' ).trim();

					if ( ! html ) {
						throw new Error( 'Empty page' );
					}

					var holder = document.createElement( 'div' );
					holder.innerHTML = html;

					var added = holder.children.length;

					while ( holder.firstElementChild ) {
						grid.appendChild( holder.firstElementChild );
					}

					// WooCommerce binds add-to-cart on this event.
					if ( window.jQuery ) {
						window.jQuery( document.body ).trigger( 'wc_fragments_loaded' );
					}

					setStatus( ( i18n.added || '%s added' ).replace( '%s', added ) );

					var next = page + 1;

					if ( next > ( payload.data.pages || max ) ) {
						moreButton.remove();
						setStatus( i18n.end || '' );
						return;
					}

					moreButton.setAttribute( 'data-page', String( next ) );
					moreButton.href = moreButton.href.replace( /([?&]lk=)\d+/, '$1' + next );

					try {
						var url = new URL( window.location.href );
						url.searchParams.set( 'lk', String( page ) );
						window.history.replaceState( {}, '', url.toString() );
					} catch ( e ) {
						/* History is unavailable; loading still worked. */
					}
				} )
				.catch( function () {
					setStatus( i18n.error || 'Loading failed.', true );
				} )
				.finally( function () {
					moreButton.classList.remove( 'is-loading' );
					moreButton.removeAttribute( 'aria-busy' );

					if ( label ) {
						label.textContent = i18n.more || 'Load more';
					}
				} );
		} );
	}
}() );
