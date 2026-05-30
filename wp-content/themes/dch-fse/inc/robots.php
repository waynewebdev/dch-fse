<?php
/** Dynamic robots.txt output and wp_robots filter rules. */

defined( 'ABSPATH' ) || exit;

/**
 * Custom robots.txt. Replaces WP's default with a tighter, sitemap-aware version.
 */
add_filter( 'robots_txt', static function ( string $output, $public ): string {
	if ( ! $public ) {
		// Site is set to discourage indexing — keep WP's default disallow-all.
		return $output;
	}

	$lines   = [];
	$lines[] = 'User-agent: *';
	$lines[] = 'Disallow: /wp-admin/';
	$lines[] = 'Allow: /wp-admin/admin-ajax.php';
	$lines[] = '';
	$lines[] = 'Sitemap: ' . home_url( '/sitemap.xml' );

	return implode( "\n", apply_filters( 'dch_fse_robots_txt_lines', $lines ) ) . "\n";
}, 10, 2 );

/**
 * Add noindex to admin-adjacent endpoints. The wp_robots filter is the modern
 * replacement for the old `wp_no_robots` action and emits the meta robots tag
 * for any matching response.
 */
add_filter( 'wp_robots', static function ( array $robots ): array {
	global $pagenow;
	if ( in_array( $pagenow, [ 'wp-login.php', 'wp-register.php' ], true ) ) {
		$robots['noindex']  = true;
		$robots['nofollow'] = true;
	}
	return $robots;
} );
