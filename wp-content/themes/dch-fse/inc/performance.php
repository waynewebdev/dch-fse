<?php
/** Cache-Control headers, preconnect hints, LCP preload helper. */

defined( 'ABSPATH' ) || exit;

/**
 * Set a public Cache-Control header on logged-out HTML responses. Skipped for
 * admin, REST, feeds, and authenticated users so per-user content stays fresh.
 */
add_action( 'send_headers', static function (): void {
	if ( is_admin() || is_feed() || is_user_logged_in() ) {
		return;
	}
	if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
		return;
	}
	if ( defined( 'DOING_CRON' ) && DOING_CRON ) {
		return;
	}

	$value = apply_filters(
		'dch_fse_html_cache_control',
		'public, max-age=300, s-maxage=3600'
	);

	if ( false === $value || '' === $value ) {
		return;
	}

	header( 'Cache-Control: ' . $value );
} );

/**
 * Emit <link rel="preconnect"> for each origin declared in site.php.
 * site.php arrives in Phase 5; this guard keeps the theme bootable until then.
 */
add_action( 'wp_head', static function (): void {
	if ( ! function_exists( 'dch_fse_site' ) ) {
		return;
	}

	$origins = dch_fse_site( 'preconnect_origins' );
	if ( ! is_array( $origins ) || empty( $origins ) ) {
		return;
	}

	foreach ( $origins as $origin ) {
		printf(
			'<link rel="preconnect" href="%s" crossorigin />' . "\n",
			esc_url( $origin )
		);
	}
}, 2 );

/**
 * Record the LCP image URL for the current request. Should be called from a
 * template, pattern, or page registry entry before wp_head fires.
 */
function dch_fse_set_lcp_image( string $url ): void {
	$GLOBALS['dch_fse_lcp_image'] = $url;
}

/**
 * Read the LCP URL set above and emit a high-priority preload for it.
 * Runs at wp_head priority 3 so it lands above the theme stylesheet preload.
 */
add_action( 'wp_head', static function (): void {
	$url = $GLOBALS['dch_fse_lcp_image'] ?? null;
	if ( ! is_string( $url ) || '' === $url ) {
		return;
	}

	printf(
		'<link rel="preload" as="image" href="%s" fetchpriority="high" />' . "\n",
		esc_url( $url )
	);
}, 3 );
