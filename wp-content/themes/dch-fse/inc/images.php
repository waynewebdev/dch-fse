<?php
/**
 * Responsive <picture> helper, AVIF/WebP fallback, and LCP-aware attributes
 * applied to every wp_get_attachment_image() call.
 *
 * AVIF and WebP variants are NOT generated on the fly. Drop pre-encoded
 * sibling files alongside originals (foo.jpg → foo.webp / foo.avif) and they
 * will be picked up automatically.
 */

defined( 'ABSPATH' ) || exit;

/* -------------------------------------------------------------------------
 *  LCP resolution + preload
 * ------------------------------------------------------------------------- */

/**
 * Resolve the LCP image URL for the current request.
 *
 * - Single post → featured image (full size).
 * - Singular page → seo.lcp_image from _dch_page_seo, else featured image.
 * - Front page → seo.lcp_image from the front page's _dch_page_seo.
 */
function dch_fse_resolve_lcp_image_for_request(): string {
	if ( ! is_singular() ) {
		return '';
	}

	$post_id = (int) get_queried_object_id();

	if ( function_exists( 'dch_fse_get_page_seo' ) ) {
		$seo = dch_fse_get_page_seo( $post_id );
		if ( ! empty( $seo['lcp_image'] ) ) {
			return dch_fse_resolve_image_url( (string) $seo['lcp_image'] );
		}
	}

	$thumb_id = get_post_thumbnail_id( $post_id );
	if ( $thumb_id ) {
		// Match the size used by post-featured-image in the templates.
		$size = (string) apply_filters( 'dch_fse_lcp_image_size', 'large', $post_id );
		$url  = wp_get_attachment_image_url( $thumb_id, $size );
		return is_string( $url ) ? $url : '';
	}

	return '';
}

add_action( 'wp', static function (): void {
	if ( is_admin() ) {
		return;
	}
	$url = dch_fse_resolve_lcp_image_for_request();
	if ( '' !== $url && function_exists( 'dch_fse_set_lcp_image' ) ) {
		dch_fse_set_lcp_image( $url );
	}
} );

/**
 * Exclude the current post from the "Recent Posts" tail query on single.html.
 * Triggered by namespace="dch-fse/recent-posts" on the wp:query block.
 */
add_filter( 'query_loop_block_query_vars', static function ( array $query, $block ) {
	$ns = $block->context['query']['namespace'] ?? '';
	if ( 'dch-fse/recent-posts' === $ns && is_singular() ) {
		$query['post__not_in'] = array_merge(
			$query['post__not_in'] ?? [],
			[ (int) get_queried_object_id() ]
		);
	}
	return $query;
}, 10, 2 );

/* -------------------------------------------------------------------------
 *  Attribute filter — applied to every wp_get_attachment_image()
 * ------------------------------------------------------------------------- */

add_filter( 'wp_get_attachment_image_attributes', static function ( array $attr, $attachment, $size ): array {
	// Always force width/height for CLS prevention. WP usually sets these but
	// covers the case where a filter elsewhere stripped them.
	if ( ( empty( $attr['width'] ) || empty( $attr['height'] ) ) && $attachment instanceof WP_Post ) {
		$meta = wp_get_attachment_image_src( $attachment->ID, $size );
		if ( false !== $meta ) {
			$attr['width']  = (int) $meta[1];
			$attr['height'] = (int) $meta[2];
		}
	}

	$lcp_url = (string) ( $GLOBALS['dch_fse_lcp_image'] ?? '' );
	$src     = (string) ( $attr['src'] ?? '' );

	if ( '' !== $lcp_url && '' !== $src && dch_fse_image_matches_lcp( $src, $lcp_url, $attachment ) ) {
		$attr['fetchpriority'] = 'high';
		$attr['decoding']      = 'async';
		unset( $attr['loading'] );
	} elseif ( ! isset( $attr['loading'] ) ) {
		$attr['loading'] = 'lazy';
	}

	return $attr;
}, 10, 3 );

/**
 * Compare a rendered image src against the resolved LCP URL. First match wins —
 * subsequent renders of the same image (e.g. in a "Recent Posts" tail block)
 * fall through to lazy loading. There is exactly one LCP element per page.
 *
 * Matches on full URL OR on the underlying attachment's full-size URL (so a
 * -800x600 thumbnail still matches when the LCP was set as full).
 */
function dch_fse_image_matches_lcp( string $src, string $lcp, $attachment ): bool {
	static $already_applied = false;
	if ( $already_applied ) {
		return false;
	}

	$matches = false;
	if ( $src === $lcp ) {
		$matches = true;
	} elseif ( $attachment instanceof WP_Post ) {
		$full = wp_get_attachment_image_url( $attachment->ID, 'full' );
		if ( is_string( $full ) && $full === $lcp ) {
			$matches = true;
		}
	}
	if ( ! $matches ) {
		// Strip WP's -WxH suffix and compare base names.
		$src_base = preg_replace( '/-\d+x\d+(?=\.[a-z0-9]+$)/i', '', $src );
		$lcp_base = preg_replace( '/-\d+x\d+(?=\.[a-z0-9]+$)/i', '', $lcp );
		$matches  = $src_base === $lcp_base;
	}

	if ( $matches ) {
		$already_applied = true;
	}
	return $matches;
}

/* -------------------------------------------------------------------------
 *  <picture> helper (manual usage in patterns)
 * ------------------------------------------------------------------------- */

/**
 * Render a <picture> element with optional AVIF / WebP sources. Required
 * args: src, alt, width, height. Returns '' on missing required args.
 *
 * @param array{
 *   src: string, alt: string, width: int, height: int,
 *   loading?: string, fetchpriority?: string, sizes?: string, class?: string
 * } $args
 */
function dch_fse_picture( array $args ): string {
	$defaults = [
		'src'           => '',
		'alt'           => '',
		'width'         => 0,
		'height'        => 0,
		'loading'       => 'lazy',
		'fetchpriority' => 'auto',
		'sizes'         => '',
		'class'         => '',
	];

	$args = apply_filters( 'dch_fse_default_image_args', array_merge( $defaults, $args ) );

	if ( '' === $args['src'] || '' === $args['alt'] || 0 === (int) $args['width'] || 0 === (int) $args['height'] ) {
		return '';
	}

	$sources = '';
	foreach ( [ 'avif' => 'image/avif', 'webp' => 'image/webp' ] as $ext => $mime ) {
		$sibling_url = dch_fse_sibling_image_url( $args['src'], $ext );
		if ( '' !== $sibling_url ) {
			$sources .= sprintf(
				'<source srcset="%s" type="%s" />',
				esc_attr( $sibling_url ),
				esc_attr( $mime )
			);
		}
	}

	$img = sprintf(
		'<img src="%s" alt="%s" width="%d" height="%d"',
		esc_attr( $args['src'] ),
		esc_attr( $args['alt'] ),
		(int) $args['width'],
		(int) $args['height']
	);
	if ( '' !== $args['loading'] ) {
		$img .= sprintf( ' loading="%s"', esc_attr( $args['loading'] ) );
	}
	if ( '' !== $args['fetchpriority'] ) {
		$img .= sprintf( ' fetchpriority="%s"', esc_attr( $args['fetchpriority'] ) );
	}
	if ( '' !== $args['sizes'] ) {
		$img .= sprintf( ' sizes="%s"', esc_attr( $args['sizes'] ) );
	}
	if ( '' !== $args['class'] ) {
		$img .= sprintf( ' class="%s"', esc_attr( $args['class'] ) );
	}
	$img .= ' />';

	return '<picture>' . $sources . $img . '</picture>';
}

/**
 * Look for a same-name sibling image in a different format. Returns the URL of
 * the sibling if it exists on disk, '' otherwise.
 */
function dch_fse_sibling_image_url( string $src_url, string $target_ext ): string {
	$local_path = dch_fse_url_to_local_path( $src_url );
	if ( '' === $local_path ) {
		return '';
	}
	$sibling_local = preg_replace( '#\.(jpe?g|png)$#i', '.' . $target_ext, $local_path );
	if ( $sibling_local === $local_path || ! is_string( $sibling_local ) ) {
		return '';
	}
	if ( ! file_exists( $sibling_local ) ) {
		return '';
	}
	$sibling_url = preg_replace( '#\.(jpe?g|png)$#i', '.' . $target_ext, $src_url );
	return is_string( $sibling_url ) ? $sibling_url : '';
}

/**
 * Translate a URL into its local filesystem path. Handles uploads + theme dirs.
 */
function dch_fse_url_to_local_path( string $url ): string {
	$upload_dir = wp_get_upload_dir();
	if ( is_array( $upload_dir ) && is_string( $upload_dir['baseurl'] ?? null ) && str_starts_with( $url, $upload_dir['baseurl'] ) ) {
		return $upload_dir['basedir'] . substr( $url, strlen( $upload_dir['baseurl'] ) );
	}
	$theme_uri = get_template_directory_uri();
	if ( str_starts_with( $url, $theme_uri ) ) {
		return get_template_directory() . substr( $url, strlen( $theme_uri ) );
	}
	return '';
}
