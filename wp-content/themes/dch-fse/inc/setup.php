<?php
/** Theme support, image sizes, nav locations, content width, text domain. */

defined( 'ABSPATH' ) || exit;

add_action( 'after_setup_theme', static function (): void {
	load_theme_textdomain( 'dch-fse', get_template_directory() . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'html5', [
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
		'style',
		'script',
		'navigation-widgets',
	] );

	add_image_size( 'dch-hero',  1920, 1080, true );
	add_image_size( 'dch-card',   800,  600, true );
	add_image_size( 'dch-thumb',  400,  300, true );

	register_nav_menus( [
		'primary' => __( 'Primary Navigation', 'dch-fse' ),
		'footer'  => __( 'Footer Navigation',  'dch-fse' ),
	] );

	if ( ! isset( $GLOBALS['content_width'] ) ) {
		$GLOBALS['content_width'] = 1200;
	}
} );

add_filter( 'image_size_names_choose', static function ( array $sizes ): array {
	return $sizes + [
		'dch-hero'  => __( 'DCH Hero (1920×1080)', 'dch-fse' ),
		'dch-card'  => __( 'DCH Card (800×600)',   'dch-fse' ),
		'dch-thumb' => __( 'DCH Thumb (400×300)',  'dch-fse' ),
	];
} );

/**
 * Templates and parts can reference a synced wp_navigation post by slug rather
 * than by post ID:
 *
 *     <!-- wp:navigation {"dchFseNavSlug":"primary"} /-->
 *
 * At render time we resolve the slug to the matching wp_navigation post ID and
 * inject it as the standard `ref` attribute. This keeps templates portable
 * across environments where IDs differ.
 */
add_filter( 'render_block_data', static function ( array $parsed_block ): array {
	if ( 'core/navigation' !== ( $parsed_block['blockName'] ?? '' ) ) {
		return $parsed_block;
	}
	if ( empty( $parsed_block['attrs']['dchFseNavSlug'] ) ) {
		return $parsed_block;
	}

	$slug = (string) $parsed_block['attrs']['dchFseNavSlug'];
	unset( $parsed_block['attrs']['dchFseNavSlug'] );

	if ( function_exists( 'dch_fse_get_navigation_id' ) ) {
		$ref = dch_fse_get_navigation_id( $slug );
		if ( $ref > 0 ) {
			$parsed_block['attrs']['ref'] = $ref;
		}
	}

	return $parsed_block;
} );

/**
 * Register the dch-fse pattern category. Pattern files in /patterns/*.php are
 * auto-discovered by WordPress in block themes; we just need to provide the
 * category label so they group together in the inserter.
 */
add_action( 'init', static function (): void {
	if ( function_exists( 'register_block_pattern_category' ) ) {
		register_block_pattern_category( 'dch-fse', [
			'label'       => __( 'DCH FSE', 'dch-fse' ),
			'description' => __( 'Patterns for the Dynamic Custom Homes theme.', 'dch-fse' ),
		] );
	}

	// [dch_year] — current year, for copyright lines in template parts.
	add_shortcode( 'dch_year', static function (): string {
		return gmdate( 'Y' );
	} );
} );
