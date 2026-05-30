<?php
/**
 * Custom XML sitemap covering registry-managed pages and published posts.
 *
 * /sitemap.xml is registered as a rewrite rule and served by a custom handler.
 * Output is cached in a transient for 1 hour and busted on save_post,
 * delete_post, and after the page registry sync runs.
 */

defined( 'ABSPATH' ) || exit;

const DCH_FSE_SITEMAP_TRANSIENT = 'dch_fse_sitemap_xml';

/* -------------------------------------------------------------------------
 *  Rewrite rule
 * ------------------------------------------------------------------------- */

add_action( 'init', static function (): void {
	add_rewrite_rule( '^sitemap\.xml/?$', 'index.php?dch_fse_sitemap=1', 'top' );
} );

add_filter( 'query_vars', static function ( array $vars ): array {
	$vars[] = 'dch_fse_sitemap';
	return $vars;
} );

/**
 * Block WP's canonical-redirect from rewriting /sitemap.xml ↔ /sitemap.xml/.
 */
add_filter( 'redirect_canonical', static function ( ?string $redirect_url, string $requested_url ) {
	if ( '1' === (string) get_query_var( 'dch_fse_sitemap' ) ) {
		return false;
	}
	return $redirect_url;
}, 10, 2 );

/**
 * Self-healing rewrite flush. Bump DCH_FSE_REWRITE_VERSION whenever the set of
 * rewrite rules changes; the next request flushes once and stamps the version.
 */
const DCH_FSE_REWRITE_VERSION = '1';

add_action( 'init', static function (): void {
	if ( get_option( 'dch_fse_rewrite_version' ) !== DCH_FSE_REWRITE_VERSION ) {
		flush_rewrite_rules( false );
		update_option( 'dch_fse_rewrite_version', DCH_FSE_REWRITE_VERSION );
	}
}, 99 );

/**
 * Also flush on theme switch — covers fresh installs.
 */
add_action( 'after_switch_theme', static function (): void {
	delete_option( 'dch_fse_rewrite_version' );
} );

/* -------------------------------------------------------------------------
 *  Handler
 * ------------------------------------------------------------------------- */

add_action( 'template_redirect', static function (): void {
	if ( '1' !== (string) get_query_var( 'dch_fse_sitemap' ) ) {
		return;
	}

	$xml = get_transient( DCH_FSE_SITEMAP_TRANSIENT );
	if ( ! is_string( $xml ) || '' === $xml ) {
		$xml = dch_fse_build_sitemap_xml();
		set_transient( DCH_FSE_SITEMAP_TRANSIENT, $xml, HOUR_IN_SECONDS );
	}

	status_header( 200 );
	header( 'Content-Type: application/xml; charset=utf-8' );
	header( 'Cache-Control: public, max-age=3600' );
	header( 'X-Robots-Tag: noindex, follow', true );

	echo $xml; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	exit;
} );

/* -------------------------------------------------------------------------
 *  Builder
 * ------------------------------------------------------------------------- */

function dch_fse_build_sitemap_xml(): string {
	$now    = gmdate( 'c' );
	$urls   = [];

	// Home
	$urls[] = [
		'loc'        => home_url( '/' ),
		'lastmod'    => $now,
		'changefreq' => 'weekly',
		'priority'   => '1.0',
	];

	// Registered pages — only include those actually synced into wp_posts so
	// the sitemap never lists URLs that resolve to a 404.
	if ( function_exists( 'dch_fse_load_page_files' ) && function_exists( 'dch_fse_find_page_by_slug' ) ) {
		[ $pages, ] = dch_fse_load_page_files();
		foreach ( $pages as $slug => $page ) {
			$post_id = dch_fse_find_page_by_slug( $slug );
			if ( ! $post_id ) {
				continue; // not synced yet
			}
			if ( (int) get_option( 'page_on_front' ) === $post_id ) {
				continue; // home already added
			}
			$lastmod_ts = strtotime( (string) ( $page['updated_at'] ?? $now ) ) ?: time();
			$urls[]     = [
				'loc'        => (string) get_permalink( $post_id ),
				'lastmod'    => gmdate( 'c', $lastmod_ts ),
				'changefreq' => 'monthly',
				'priority'   => '0.8',
			];
		}
	}

	// Admin-managed pages NOT in the registry (so coexisting admin pages still get listed).
	$admin_pages = get_posts( [
		'post_type'      => 'page',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'meta_query'     => [
			[ 'key' => '_dch_page_slug', 'compare' => 'NOT EXISTS' ],
		],
		'no_found_rows'  => true,
	] );
	foreach ( $admin_pages as $page_id ) {
		if ( (int) get_option( 'page_on_front' ) === $page_id ) {
			continue;
		}
		$urls[] = [
			'loc'        => (string) get_permalink( $page_id ),
			'lastmod'    => mysql2date( 'c', get_post_field( 'post_modified_gmt', $page_id ) ),
			'changefreq' => 'monthly',
			'priority'   => '0.7',
		];
	}

	// Posts
	$posts = get_posts( [
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'no_found_rows'  => true,
	] );
	foreach ( $posts as $post_id ) {
		$urls[] = [
			'loc'        => (string) get_permalink( $post_id ),
			'lastmod'    => mysql2date( 'c', get_post_field( 'post_modified_gmt', $post_id ) ),
			'changefreq' => 'weekly',
			'priority'   => '0.6',
		];
	}

	$urls = apply_filters( 'dch_fse_sitemap_urls', $urls );

	$xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
	$xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
	foreach ( $urls as $url ) {
		$xml .= "\t<url>\n";
		$xml .= "\t\t<loc>"        . esc_url( $url['loc'] )                 . "</loc>\n";
		if ( ! empty( $url['lastmod'] ) ) {
			$xml .= "\t\t<lastmod>"    . esc_html( $url['lastmod'] )          . "</lastmod>\n";
		}
		if ( ! empty( $url['changefreq'] ) ) {
			$xml .= "\t\t<changefreq>" . esc_html( $url['changefreq'] )       . "</changefreq>\n";
		}
		if ( ! empty( $url['priority'] ) ) {
			$xml .= "\t\t<priority>"   . esc_html( $url['priority'] )         . "</priority>\n";
		}
		$xml .= "\t</url>\n";
	}
	$xml .= '</urlset>' . "\n";

	return $xml;
}

/* -------------------------------------------------------------------------
 *  Cache busting
 * ------------------------------------------------------------------------- */

function dch_fse_bust_sitemap_cache(): void {
	delete_transient( DCH_FSE_SITEMAP_TRANSIENT );
}

add_action( 'save_post',           'dch_fse_bust_sitemap_cache' );
add_action( 'deleted_post',        'dch_fse_bust_sitemap_cache' );
add_action( 'trashed_post',        'dch_fse_bust_sitemap_cache' );
add_action( 'untrashed_post',      'dch_fse_bust_sitemap_cache' );
add_action( 'dch_fse_pages_synced', 'dch_fse_bust_sitemap_cache' );
