<?php
/** Strips WP head bloat: emoji, generator, RSD, oEmbed discovery, XML-RPC, etc. */

defined( 'ABSPATH' ) || exit;

add_action( 'init', static function (): void {
	remove_action( 'wp_head', 'wp_generator' );
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'wp_shortlink_wp_head' );
	remove_action( 'wp_head', 'rest_output_link_wp_head' );
	remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
	remove_action( 'wp_head', 'wp_oembed_add_host_js' );
	remove_action( 'template_redirect', 'rest_output_link_header', 11 );

	remove_action( 'wp_head',             'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles',     'print_emoji_styles' );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'admin_print_styles',  'print_emoji_styles' );
	remove_action( 'embed_head',          'print_emoji_detection_script' );
	remove_filter( 'the_content_feed',    'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss',    'wp_staticize_emoji' );
	remove_filter( 'wp_mail',             'wp_staticize_emoji_for_email' );
}, 11 );

// Suppress emoji entirely — also removes the s.w.org dns-prefetch added via wp_resource_hints.
add_filter( 'emoji_svg_url',   '__return_false' );
add_filter( 'tiny_mce_plugins', static function ( array $plugins ): array {
	return array_values( array_diff( $plugins, [ 'wpemoji' ] ) );
} );

// Disable XML-RPC and the X-Pingback discovery header.
add_filter( 'xmlrpc_enabled', '__return_false' );
add_filter( 'wp_headers', static function ( array $headers ): array {
	unset( $headers['X-Pingback'] );
	return $headers;
} );

// Disable comments and pingbacks site-wide. Admin UI stays accessible.
add_filter( 'comments_open', '__return_false', 20 );
add_filter( 'pings_open',    '__return_false', 20 );

// Disable WP core's automatic XML sitemap; the custom sitemap arrives in Phase 7.
add_filter( 'wp_sitemaps_enabled', '__return_false' );
