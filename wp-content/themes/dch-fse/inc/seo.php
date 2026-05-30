<?php
/**
 * Unified SEO output for pages and posts. Sources resolution:
 *
 *   - Front page or registered page (has _dch_page_slug meta) → _dch_page_seo
 *   - Single post                                              → dch_fse_post_seo_*
 *   - Archive / search / 404                                   → query-context defaults
 *
 * Title is set via pre_get_document_title for precise control. All other
 * meta tags emit on wp_head priority 1.
 */

defined( 'ABSPATH' ) || exit;

/* -------------------------------------------------------------------------
 *  URL + image helpers
 * ------------------------------------------------------------------------- */

/**
 * Resolve a theme-relative path or absolute URL to a full URL.
 */
function dch_fse_resolve_image_url( string $path_or_url ): string {
	if ( '' === $path_or_url ) {
		return '';
	}
	if ( preg_match( '#^https?://#i', $path_or_url ) || str_starts_with( $path_or_url, '//' ) ) {
		return $path_or_url;
	}
	$path = '/' . ltrim( $path_or_url, '/' );
	return get_template_directory_uri() . $path;
}

/**
 * Cached image dimension lookup. Falls back to OG default 1200×630.
 *
 * @return array{width:int,height:int}
 */
function dch_fse_image_dimensions( string $url ): array {
	if ( '' === $url ) {
		return [ 'width' => 0, 'height' => 0 ];
	}
	$cache_key = 'dch_fse_imgdim_' . md5( $url );
	$cached    = get_transient( $cache_key );
	if ( is_array( $cached ) ) {
		return $cached;
	}

	$path        = '';
	$upload_dir  = wp_get_upload_dir();
	$theme_uri   = get_template_directory_uri();
	if ( $upload_dir && is_string( $upload_dir['baseurl'] ?? null ) && str_starts_with( $url, $upload_dir['baseurl'] ) ) {
		$path = $upload_dir['basedir'] . substr( $url, strlen( $upload_dir['baseurl'] ) );
	} elseif ( str_starts_with( $url, $theme_uri ) ) {
		$path = get_template_directory() . substr( $url, strlen( $theme_uri ) );
	}

	$result = [ 'width' => 1200, 'height' => 630 ];
	if ( '' !== $path && file_exists( $path ) ) {
		$dims = @getimagesize( $path );
		if ( false !== $dims ) {
			$result = [ 'width' => (int) $dims[0], 'height' => (int) $dims[1] ];
		}
	}

	set_transient( $cache_key, $result, DAY_IN_SECONDS );
	return $result;
}

/* -------------------------------------------------------------------------
 *  Title via pre_get_document_title
 * ------------------------------------------------------------------------- */

add_filter( 'pre_get_document_title', static function ( string $title ): string {
	$resolved = dch_fse_resolve_seo_title();
	return '' !== $resolved ? $resolved : $title;
} );

function dch_fse_resolve_seo_title(): string {
	$site_name = (string) ( dch_fse_site( 'name' ) ?: get_bloginfo( 'name' ) );

	if ( is_404() ) {
		return sprintf( '%s — %s', __( 'Page not found', 'dch-fse' ), $site_name );
	}

	if ( is_search() ) {
		return sprintf(
			'%s "%s" — %s',
			__( 'Search results for', 'dch-fse' ),
			get_search_query(),
			$site_name
		);
	}

	if ( is_singular() ) {
		$post_id = (int) get_the_ID();

		// Page registry SEO
		if ( function_exists( 'dch_fse_get_page_seo' ) ) {
			$page_seo = dch_fse_get_page_seo( $post_id );
			if ( ! empty( $page_seo['title'] ) ) {
				return (string) $page_seo['title'];
			}
		}

		if ( 'post' === get_post_type( $post_id ) ) {
			return dch_fse_post_seo_title( $post_id );
		}

		if ( is_front_page() ) {
			$tagline = (string) ( dch_fse_site( 'tagline' ) ?: get_bloginfo( 'description' ) );
			return '' !== $tagline ? "{$site_name} — {$tagline}" : $site_name;
		}

		return get_the_title( $post_id ) . ' — ' . $site_name;
	}

	if ( is_home() ) {
		return sprintf( '%s — %s', __( 'Blog', 'dch-fse' ), $site_name );
	}

	if ( is_archive() ) {
		$archive_title = wp_strip_all_tags( get_the_archive_title() );
		return $archive_title . ' — ' . $site_name;
	}

	return $site_name;
}

/* -------------------------------------------------------------------------
 *  Context resolver — single source of truth for every SEO field
 * ------------------------------------------------------------------------- */

/**
 * @return array{
 *   title: string,
 *   description: string,
 *   canonical: string,
 *   robots: string,
 *   og_type: string,
 *   og_image: string,
 *   og_image_alt: string,
 *   og_image_width: int,
 *   og_image_height: int,
 *   site_name: string,
 *   locale: string,
 *   is_paginated: bool
 * }
 */
function dch_fse_seo_context(): array {
	static $cache = null;
	if ( null !== $cache ) {
		return $cache;
	}

	$site_name = (string) ( dch_fse_site( 'name' ) ?: get_bloginfo( 'name' ) );
	$ctx = [
		'title'           => dch_fse_resolve_seo_title(),
		'description'     => '',
		'canonical'       => '',
		'robots'          => 'index,follow',
		'og_type'         => 'website',
		'og_image'        => '',
		'og_image_alt'    => '',
		'og_image_width'  => 0,
		'og_image_height' => 0,
		'site_name'       => $site_name,
		'locale'          => get_locale(),
		'is_paginated'    => false,
	];

	$paged = max( (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
	$ctx['is_paginated'] = $paged > 1;

	if ( is_404() ) {
		$ctx['robots'] = 'noindex,follow';
	} elseif ( is_search() ) {
		$ctx['robots']    = 'noindex,follow';
		$ctx['canonical'] = home_url( '/?s=' . rawurlencode( get_search_query() ) );
	} elseif ( is_singular() ) {
		$post_id          = (int) get_the_ID();
		$ctx['canonical'] = (string) get_permalink( $post_id );

		if ( 'post' === get_post_type( $post_id ) ) {
			$ctx['og_type']     = 'article';
			$ctx['description'] = dch_fse_post_seo_description( $post_id );
			$ctx['og_image']    = dch_fse_post_seo_og_image( $post_id );
			$ctx['robots']      = dch_fse_post_seo_robots( $post_id );
		} else {
			// Page (registry-managed or admin-managed)
			$page_seo = function_exists( 'dch_fse_get_page_seo' ) ? dch_fse_get_page_seo( $post_id ) : [];
			$ctx['description'] = (string) ( $page_seo['description'] ?? get_the_excerpt( $post_id ) );
			$ctx['og_image']    = (string) ( $page_seo['og_image'] ?? '' );
			$ctx['robots']      = (string) ( $page_seo['robots']   ?? 'index,follow' );
		}

		if ( is_front_page() ) {
			$ctx['canonical'] = home_url( '/' );
			$ctx['og_type']   = 'website';
		}

		if ( $ctx['is_paginated'] && 'noindex,follow' !== $ctx['robots'] ) {
			$ctx['robots'] = 'noindex,follow';
		}
	} elseif ( is_home() ) {
		$posts_page = (int) get_option( 'page_for_posts' );
		$ctx['canonical'] = $posts_page ? (string) get_permalink( $posts_page ) : home_url( '/' );
		$ctx['description'] = (string) ( dch_fse_site( 'tagline' ) ?: get_bloginfo( 'description' ) );
		if ( $ctx['is_paginated'] ) {
			$ctx['robots'] = 'noindex,follow';
		}
	} elseif ( is_archive() ) {
		$ctx['canonical']   = (string) get_pagenum_link( $paged > 0 ? $paged : 1 );
		$ctx['description'] = wp_strip_all_tags( (string) get_the_archive_description() );
		if ( $ctx['is_paginated'] ) {
			$ctx['robots'] = 'noindex,follow';
		}
	} else {
		$ctx['canonical'] = home_url( '/' );
	}

	// Fallback to site default OG image
	if ( '' === $ctx['og_image'] ) {
		$default = (string) ( dch_fse_site( 'default_og_image' ) ?: '' );
		if ( '' !== $default ) {
			$ctx['og_image'] = $default;
		}
	}

	if ( '' !== $ctx['og_image'] ) {
		$ctx['og_image']     = dch_fse_resolve_image_url( $ctx['og_image'] );
		$dims                = dch_fse_image_dimensions( $ctx['og_image'] );
		$ctx['og_image_width']  = $dims['width'];
		$ctx['og_image_height'] = $dims['height'];
		$ctx['og_image_alt']    = $ctx['title'];
	}

	$cache = apply_filters( 'dch_fse_seo_context', $ctx );
	return $cache;
}

/* -------------------------------------------------------------------------
 *  Head output
 * ------------------------------------------------------------------------- */

/**
 * Merge our resolved index/follow/noindex/nofollow into WP's wp_robots output
 * so a single canonical <meta name="robots"> is emitted (alongside any other
 * directives like max-image-preview).
 */
add_filter( 'wp_robots', static function ( array $robots ): array {
	$ctx    = dch_fse_seo_context();
	$tokens = array_map( 'trim', explode( ',', strtolower( $ctx['robots'] ) ) );

	if ( in_array( 'noindex', $tokens, true ) ) {
		$robots['noindex'] = true;
		unset( $robots['index'] );
	} else {
		$robots['index']   = true;
		unset( $robots['noindex'] );
	}

	if ( in_array( 'nofollow', $tokens, true ) ) {
		$robots['nofollow'] = true;
		unset( $robots['follow'] );
	} else {
		$robots['follow']   = true;
		unset( $robots['nofollow'] );
	}

	return $robots;
}, 20 );

add_action( 'wp_head', 'dch_fse_render_seo_meta', 1 );

function dch_fse_render_seo_meta(): void {
	$ctx = dch_fse_seo_context();

	if ( '' !== $ctx['description'] ) {
		printf( "<meta name=\"description\" content=\"%s\" />\n", esc_attr( $ctx['description'] ) );
	}

	if ( '' !== $ctx['canonical'] ) {
		printf( "<link rel=\"canonical\" href=\"%s\" />\n", esc_url( $ctx['canonical'] ) );
	}

	// Robots is consolidated into WP's single <meta name="robots"> via the
	// wp_robots filter below — no standalone tag emitted here.

	// Open Graph
	printf( "<meta property=\"og:type\" content=\"%s\" />\n",        esc_attr( $ctx['og_type'] ) );
	printf( "<meta property=\"og:title\" content=\"%s\" />\n",       esc_attr( $ctx['title'] ) );
	if ( '' !== $ctx['description'] ) {
		printf( "<meta property=\"og:description\" content=\"%s\" />\n", esc_attr( $ctx['description'] ) );
	}
	if ( '' !== $ctx['canonical'] ) {
		printf( "<meta property=\"og:url\" content=\"%s\" />\n", esc_url( $ctx['canonical'] ) );
	}
	printf( "<meta property=\"og:site_name\" content=\"%s\" />\n", esc_attr( $ctx['site_name'] ) );
	printf( "<meta property=\"og:locale\" content=\"%s\" />\n",    esc_attr( $ctx['locale'] ) );

	if ( '' !== $ctx['og_image'] ) {
		printf( "<meta property=\"og:image\" content=\"%s\" />\n", esc_url( $ctx['og_image'] ) );
		if ( '' !== $ctx['og_image_alt'] ) {
			printf( "<meta property=\"og:image:alt\" content=\"%s\" />\n", esc_attr( $ctx['og_image_alt'] ) );
		}
		if ( $ctx['og_image_width'] > 0 ) {
			printf( "<meta property=\"og:image:width\" content=\"%d\" />\n",  $ctx['og_image_width'] );
			printf( "<meta property=\"og:image:height\" content=\"%d\" />\n", $ctx['og_image_height'] );
		}
	}

	// Twitter Card
	echo "<meta name=\"twitter:card\" content=\"summary_large_image\" />\n";
	printf( "<meta name=\"twitter:title\" content=\"%s\" />\n", esc_attr( $ctx['title'] ) );
	if ( '' !== $ctx['description'] ) {
		printf( "<meta name=\"twitter:description\" content=\"%s\" />\n", esc_attr( $ctx['description'] ) );
	}
	if ( '' !== $ctx['og_image'] ) {
		printf( "<meta name=\"twitter:image\" content=\"%s\" />\n", esc_url( $ctx['og_image'] ) );
	}
}
