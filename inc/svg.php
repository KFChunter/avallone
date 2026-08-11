<?php
/**
 * SVG upload support.
 *
 * WordPress refuses SVG uploads by design: an SVG is an XML document that can
 * carry <script>, event handlers and external references, so an uploaded file
 * can execute JavaScript in the site's own origin. That risk is real, and the
 * fix here is not simply to allow the MIME type — every uploaded SVG is parsed
 * and rewritten against an allowlist before it is ever stored. A file that
 * cannot be parsed, or that has no <svg> root, is rejected outright.
 *
 * Implemented in the theme rather than by adding a plugin, per this project's
 * minimal-plugin approach. The trade-off is that Safe SVG wraps the widely
 * audited enshrined/svg-sanitize library; if you would rather rely on that,
 * install Safe SVG and delete this file — the two would otherwise overlap.
 *
 * @package Avallone
 */

defined( 'ABSPATH' ) || exit;

/**
 * Elements permitted inside an uploaded SVG.
 *
 * An allowlist, so anything not named here — <script>, <foreignObject>,
 * <handler>, <animate> and every future addition — is removed rather than
 * relied upon to be harmless.
 *
 * @return string[]
 */
function avallone_svg_allowed_elements() {
	return array(
		'svg', 'g', 'defs', 'symbol', 'use', 'switch', 'title', 'desc',
		'path', 'rect', 'circle', 'ellipse', 'line', 'polyline', 'polygon',
		'text', 'tspan', 'textpath',
		'clippath', 'mask', 'pattern', 'image', 'marker', 'style',
		'lineargradient', 'radialgradient', 'stop',
		'filter', 'fegaussianblur', 'feoffset', 'feblend', 'fecolormatrix',
		'femerge', 'femergenode', 'fedropshadow', 'feflood', 'fecomposite',
	);
}

/**
 * Whether a URL reference inside an SVG is safe to keep.
 *
 * Only same-document fragments and embedded raster data survive. That removes
 * javascript: and data:text/html payloads, and stops an uploaded file from
 * pulling in remote content.
 *
 * @param string $value Attribute value.
 * @return bool
 */
function avallone_svg_reference_is_safe( $value ) {
	$value = trim( $value );

	if ( '' === $value || str_starts_with( $value, '#' ) ) {
		return true;
	}

	return (bool) preg_match( '#^data:image/(png|jpeg|jpg|gif|webp);base64,#i', $value );
}

/**
 * Sanitize SVG markup, returning the cleaned document or false if unusable.
 *
 * @param string $markup Raw file contents.
 * @return string|false
 */
function avallone_sanitize_svg( $markup ) {
	if ( '' === trim( $markup ) ) {
		return false;
	}

	// Strip the XML declaration's encoding games and any DOCTYPE before parsing,
	// which is what closes off entity-expansion and XXE attacks.
	$markup = preg_replace( '/<!DOCTYPE[^>]*+>/i', '', $markup );
	$markup = preg_replace( '/<!ENTITY[^>]*+>/i', '', $markup );

	$previous = libxml_use_internal_errors( true );
	$document = new DOMDocument();
	$document->preserveWhiteSpace = false;

	// LIBXML_NONET blocks network access during parsing; entities are not
	// substituted because LIBXML_NOENT is deliberately absent.
	$loaded = $document->loadXML( $markup, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING );

	libxml_clear_errors();
	libxml_use_internal_errors( $previous );

	if ( ! $loaded || ! $document->documentElement ) {
		return false;
	}

	if ( 'svg' !== strtolower( $document->documentElement->nodeName ) ) {
		return false;
	}

	$allowed = avallone_svg_allowed_elements();
	$xpath   = new DOMXPath( $document );

	// Remove disallowed elements, deepest first so the list stays valid as
	// nodes are detached.
	$nodes = $xpath->query( '//*' );

	for ( $i = $nodes->length - 1; $i >= 0; $i-- ) {
		$node = $nodes->item( $i );

		if ( ! in_array( strtolower( $node->nodeName ), $allowed, true ) && $node->parentNode ) {
			$node->parentNode->removeChild( $node );
		}
	}

	// Strip dangerous attributes from whatever survived.
	foreach ( $xpath->query( '//*' ) as $node ) {
		if ( ! $node->hasAttributes() ) {
			continue;
		}

		for ( $i = $node->attributes->length - 1; $i >= 0; $i-- ) {
			$attribute = $node->attributes->item( $i );
			$name      = strtolower( $attribute->nodeName );
			$value     = $attribute->nodeValue;

			// Every event handler, current or future.
			if ( str_starts_with( $name, 'on' ) ) {
				$node->removeAttribute( $attribute->nodeName );
				continue;
			}

			if ( in_array( $name, array( 'href', 'xlink:href', 'src' ), true )
				&& ! avallone_svg_reference_is_safe( $value ) ) {
				$node->removeAttribute( $attribute->nodeName );
				continue;
			}

			// CSS cannot execute script in current browsers, but url() can still
			// pull in remote content.
			if ( 'style' === $name && preg_match( '#url\s*\(|expression\s*\(|@import#i', (string) $value ) ) {
				$node->removeAttribute( $attribute->nodeName );
			}
		}
	}

	$clean = $document->saveXML( $document->documentElement, LIBXML_NOEMPTYTAG );

	return is_string( $clean ) && '' !== $clean ? $clean : false;
}

/**
 * Allow the SVG MIME type for users who may upload media.
 *
 * The sanitiser above is the actual control; this only opens the door.
 *
 * @param array $mimes Allowed MIME types.
 * @return array
 */
function avallone_allow_svg_mime( $mimes ) {
	if ( current_user_can( 'upload_files' ) ) {
		$mimes['svg']  = 'image/svg+xml';
		$mimes['svgz'] = 'image/svg+xml';
	}

	return $mimes;
}
add_filter( 'upload_mimes', 'avallone_allow_svg_mime' );

/**
 * Teach WordPress that a .svg really is an SVG.
 *
 * Without this the real-MIME check returns an empty ext and type, and the media
 * uploader reports "This file cannot be processed by the web server" even once
 * the MIME type is allowed.
 *
 * @param array  $checked  Values for ext, type and proper_filename.
 * @param string $file     Full path to the file.
 * @param string $filename The name of the file.
 * @return array
 */
function avallone_svg_filetype( $checked, $file, $filename ) {
	if ( ! empty( $checked['type'] ) ) {
		return $checked;
	}

	if ( 'svg' === strtolower( (string) pathinfo( $filename, PATHINFO_EXTENSION ) ) ) {
		$checked['ext']  = 'svg';
		$checked['type'] = 'image/svg+xml';
	}

	return $checked;
}
add_filter( 'wp_check_filetype_and_ext', 'avallone_svg_filetype', 10, 3 );

/**
 * Sanitize an uploaded SVG before WordPress stores it.
 *
 * Runs on the temporary file, so nothing unsafe is ever written into uploads.
 *
 * @param array $file Uploaded file data.
 * @return array
 */
function avallone_sanitize_svg_upload( $file ) {
	if ( empty( $file['tmp_name'] ) || ! empty( $file['error'] ) ) {
		return $file;
	}

	$is_svg = 'image/svg+xml' === ( $file['type'] ?? '' )
		|| 'svg' === strtolower( (string) pathinfo( $file['name'] ?? '', PATHINFO_EXTENSION ) );

	if ( ! $is_svg ) {
		return $file;
	}

	$markup = file_get_contents( $file['tmp_name'] ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local temp file.
	$clean  = false === $markup ? false : avallone_sanitize_svg( $markup );

	if ( false === $clean ) {
		$file['error'] = __( 'Seda SVG-faili ei õnnestunud turvaliselt töödelda. Salvesta see uuesti puhta SVG-na ja proovi uuesti.', 'avallone' );

		return $file;
	}

	file_put_contents( $file['tmp_name'], $clean ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents -- Local temp file.

	return $file;
}
add_filter( 'wp_handle_upload_prefilter', 'avallone_sanitize_svg_upload' );
// Sideloads cover programmatic additions such as WP-CLI imports, which do not
// pass through the upload filter above.
add_filter( 'wp_handle_sideload_prefilter', 'avallone_sanitize_svg_upload' );

/**
 * Intrinsic dimensions of an SVG, read from width/height or the viewBox.
 *
 * @param string $path Absolute path to the file.
 * @return array{width:int,height:int}|false
 */
function avallone_svg_dimensions( $path ) {
	if ( ! is_readable( $path ) ) {
		return false;
	}

	$previous = libxml_use_internal_errors( true );
	$svg      = simplexml_load_file( $path, 'SimpleXMLElement', LIBXML_NONET );
	libxml_clear_errors();
	libxml_use_internal_errors( $previous );

	if ( false === $svg ) {
		return false;
	}

	$attributes = $svg->attributes();
	$width      = (float) preg_replace( '/[^0-9.]/', '', (string) ( $attributes->width ?? '' ) );
	$height     = (float) preg_replace( '/[^0-9.]/', '', (string) ( $attributes->height ?? '' ) );

	if ( $width <= 0 || $height <= 0 ) {
		$box = preg_split( '/[\s,]+/', trim( (string) ( $attributes->viewBox ?? '' ) ) );

		if ( is_array( $box ) && 4 === count( $box ) ) {
			$width  = (float) $box[2];
			$height = (float) $box[3];
		}
	}

	if ( $width <= 0 || $height <= 0 ) {
		return false;
	}

	return array(
		'width'  => (int) round( $width ),
		'height' => (int) round( $height ),
	);
}

/**
 * Give SVG attachments real dimensions.
 *
 * SVGs carry no metadata WordPress can read, so wp_get_attachment_image() would
 * otherwise emit no width or height — which matters here because the header
 * logo goes through the_custom_logo().
 *
 * @param array|false  $image         Array of image data, or false.
 * @param int          $attachment_id Attachment ID.
 * @return array|false
 */
function avallone_svg_image_src( $image, $attachment_id ) {
	if ( 'image/svg+xml' !== get_post_mime_type( $attachment_id ) ) {
		return $image;
	}

	$url  = wp_get_attachment_url( $attachment_id );
	$path = get_attached_file( $attachment_id );
	$size = $path ? avallone_svg_dimensions( $path ) : false;

	if ( ! $url || ! $size ) {
		return $image;
	}

	return array( $url, $size['width'], $size['height'], false );
}
add_filter( 'wp_get_attachment_image_src', 'avallone_svg_image_src', 10, 2 );

/**
 * Show SVGs in the media library grid and the attachment details panel.
 *
 * @param array   $response   Prepared attachment data.
 * @param WP_Post $attachment Attachment object.
 * @return array
 */
function avallone_svg_prepare_attachment( $response, $attachment ) {
	if ( 'image/svg+xml' !== $response['mime'] ) {
		return $response;
	}

	$url  = wp_get_attachment_url( $attachment->ID );
	$path = get_attached_file( $attachment->ID );
	$size = $path ? avallone_svg_dimensions( $path ) : false;

	if ( ! $url ) {
		return $response;
	}

	$response['image'] = array( 'src' => $url );
	$response['sizes'] = array(
		'full' => array(
			'url'         => $url,
			'width'       => $size ? $size['width'] : 150,
			'height'      => $size ? $size['height'] : 150,
			'orientation' => 'landscape',
		),
	);

	return $response;
}
add_filter( 'wp_prepare_attachment_for_js', 'avallone_svg_prepare_attachment', 10, 2 );
