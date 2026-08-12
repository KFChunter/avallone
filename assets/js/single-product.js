/**
 * Single product enhancements.
 *
 * Three small jobs, each an upgrade to markup that already works without it:
 * − / + buttons around WooCommerce's real quantity field, swapping the main
 * image from the gallery thumbnails, and a share action.
 *
 * There is deliberately no wishlist logic here. Those controls are inert until
 * a shared wishlist exists; faking state now would be a lie the user could see.
 *
 * Vanilla, dependency-free, and tolerant of any element being absent.
 */
( function () {
	'use strict';

	var config = window.avalloneProduct || {};
	var i18n = config.i18n || {};

	/* ------------------------------------------------------------------ *
	 * Quantity — drive WooCommerce's own input, never a parallel counter.
	 * ------------------------------------------------------------------ */

	function enhanceQuantity( wrapper ) {
		var input = wrapper.querySelector( 'input.qty' );

		// Sold individually, or a product type without a quantity: nothing to do.
		if ( ! input || 'hidden' === input.type ) {
			return;
		}

		function bounds() {
			var step = parseFloat( input.getAttribute( 'step' ) );
			var min = parseFloat( input.getAttribute( 'min' ) );
			var max = parseFloat( input.getAttribute( 'max' ) );

			return {
				step: isNaN( step ) || step <= 0 ? 1 : step,
				min: isNaN( min ) ? 1 : min,
				max: isNaN( max ) ? Infinity : max
			};
		}

		function current() {
			var value = parseFloat( input.value );

			return isNaN( value ) ? bounds().min : value;
		}

		function sync() {
			var b = bounds();
			var value = current();

			minus.disabled = value <= b.min;
			plus.disabled = value >= b.max;
		}

		function nudge( direction ) {
			var b = bounds();
			var next = current() + direction * b.step;

			next = Math.min( b.max, Math.max( b.min, next ) );

			// Round to the step's precision so 0.1 steps do not drift.
			var decimals = ( String( b.step ).split( '.' )[ 1 ] || '' ).length;
			input.value = next.toFixed( decimals );

			// WooCommerce listens for change on this field.
			input.dispatchEvent( new Event( 'change', { bubbles: true } ) );
			sync();
		}

		function button( label, symbol, direction ) {
			var el = document.createElement( 'button' );

			el.type = 'button';
			el.className = 'product-quantity__step';
			el.innerHTML = '<span aria-hidden="true">' + symbol + '</span>';

			var sr = document.createElement( 'span' );
			sr.className = 'screen-reader-text';
			sr.textContent = label;
			el.appendChild( sr );

			el.addEventListener( 'click', function () {
				nudge( direction );
			} );

			return el;
		}

		var minus = button( i18n.decrease || 'Vähenda kogust', '−', -1 );
		var plus = button( i18n.increase || 'Suurenda kogust', '+', 1 );

		wrapper.insertBefore( minus, input );
		wrapper.appendChild( plus );

		input.addEventListener( 'input', sync );
		input.addEventListener( 'change', sync );
		sync();
	}

	Array.prototype.forEach.call(
		document.querySelectorAll( '.product-summary__cart .quantity' ),
		enhanceQuantity
	);

	/*
	 * A variable product rebuilds its cart row when a variation is chosen, so
	 * any freshly inserted quantity field needs the same treatment.
	 */
	document.addEventListener( 'click', function ( event ) {
		if ( ! event.target.closest( '.product-summary__cart' ) ) {
			return;
		}

		window.setTimeout( function () {
			Array.prototype.forEach.call(
				document.querySelectorAll( '.product-summary__cart .quantity' ),
				function ( wrapper ) {
					if ( ! wrapper.querySelector( '.product-quantity__step' ) ) {
						enhanceQuantity( wrapper );
					}
				}
			);
		}, 60 );
	} );

	/* ------------------------------------------------------------------ *
	 * Gallery thumbnails.
	 * ------------------------------------------------------------------ */

	var frame = document.querySelector( '[data-product-frame]' );
	var thumbs = document.querySelectorAll( '[data-product-thumb]' );

	if ( frame && thumbs.length ) {
		Array.prototype.forEach.call( thumbs, function ( thumb ) {
			thumb.addEventListener( 'click', function () {
				var full = thumb.getAttribute( 'data-full' );
				var image = frame.querySelector( 'img' );

				if ( ! full || ! image ) {
					return;
				}

				image.removeAttribute( 'srcset' );
				image.removeAttribute( 'sizes' );
				image.src = full;

				Array.prototype.forEach.call( thumbs, function ( other ) {
					other.classList.toggle( 'is-current', other === thumb );
					other.setAttribute( 'aria-pressed', other === thumb ? 'true' : 'false' );
				} );
			} );
		} );
	}

	/* ------------------------------------------------------------------ *
	 * Share — Web Share where available, clipboard otherwise.
	 * ------------------------------------------------------------------ */

	var shareButton = document.querySelector( '[data-product-share]' );
	var shareStatus = document.querySelector( '[data-product-share-status]' );

	function announce( message ) {
		if ( shareStatus ) {
			shareStatus.textContent = message;
		}
	}

	if ( shareButton ) {
		shareButton.addEventListener( 'click', function () {
			var title = shareButton.getAttribute( 'data-title' ) || document.title;
			var url = shareButton.getAttribute( 'data-url' ) || window.location.href;

			if ( navigator.share ) {
				navigator.share( { title: title, url: url } ).catch( function () {
					/* Dismissing the sheet is not an error worth reporting. */
				} );

				return;
			}

			if ( navigator.clipboard && navigator.clipboard.writeText ) {
				navigator.clipboard.writeText( url ).then( function () {
					announce( i18n.copied || '' );
				} ).catch( function () {
					announce( i18n.copyFailed || '' );
				} );

				return;
			}

			announce( i18n.copyFailed || '' );
		} );
	}
}() );
