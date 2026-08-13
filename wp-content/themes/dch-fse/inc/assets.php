<?php
/** Conditional CSS/JS enqueueing and dequeueing of WP defaults. */

defined( 'ABSPATH' ) || exit;

const DCH_FSE_THEME_HANDLE = 'dch-fse-theme';

/**
 * Resolve the current request to a template slug used by the blocks-required
 * allowlist. Mirrors the FSE template hierarchy at a coarse level.
 */
function dch_fse_current_template_slug(): string {
	if ( is_embed() )                  return 'embed';
	if ( is_404() )                    return '404';
	if ( is_search() )                 return 'search';
	if ( is_singular( 'post' ) )       return 'single';
	if ( is_singular( 'page' ) )       return 'page';
	if ( is_home() || is_front_page() ) return 'index';
	if ( is_archive() )                return 'archive';
	return 'index';
}

add_action( 'wp_head', static function (): void {
	echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
	echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
}, 2 );

add_action( 'wp_enqueue_scripts', static function (): void {
	// global-styles is intentionally NOT dequeued: it carries the
	// --wp--preset--* design tokens that theme.css and patterns consume.
	// It's small (a few KB inline) and load-bearing.
	wp_dequeue_style( 'wp-block-library' );
	wp_dequeue_style( 'wp-block-library-theme' );
	wp_dequeue_style( 'classic-theme-styles' );

	// jQuery intentionally retained: plugins like Ninja Forms (Backbone /
	// Marionette / NF runtime) declare jquery as a dependency. Deregistering
	// it here breaks any front-end form, gallery, or accordion plugin.
	// If/when no plugin needs it, dequeue conditionally instead of broadly
	// deregistering.

	$required = (array) apply_filters(
		'dch_fse_blocks_required_templates',
		[ 'single', 'archive', 'search' ]
	);

	if ( in_array( dch_fse_current_template_slug(), $required, true ) ) {
		wp_enqueue_style( 'wp-block-library' );
	}

	wp_enqueue_style(
		'dch-fse-fonts',
		'https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Hanken+Grotesk:wght@400;500;600;700&display=swap',
		[],
		null
	);

	$theme_css = get_template_directory() . '/assets/css/theme.css';
	if ( file_exists( $theme_css ) ) {
		wp_enqueue_style(
			DCH_FSE_THEME_HANDLE,
			get_template_directory_uri() . '/assets/css/theme.css',
			[],
			(string) filemtime( $theme_css )
		);
	}

	$header_js = get_template_directory() . '/assets/js/header.js';
	if ( file_exists( $header_js ) ) {
		wp_enqueue_script(
			'dch-fse-header',
			get_template_directory_uri() . '/assets/js/header.js',
			[],
			(string) filemtime( $header_js ),
			[ 'in_footer' => true, 'strategy' => 'defer' ]
		);
	}

	// Pages that use the animation runtime (data-dch-anim, sliders, counters).
	$anim_pages = is_front_page()
		|| is_page( [ 'projects', 'contact', 'about', 'services' ] )
		|| is_home()
		|| is_404()
		|| is_search()
		|| is_singular( 'post' )
		|| ( function_exists( 'dch_fse_is_project_page' ) && dch_fse_is_project_page() )
		|| ( function_exists( 'dch_fse_is_service_child' ) && dch_fse_is_service_child() )
		|| ( function_exists( 'dch_fse_is_areas_listing' ) && dch_fse_is_areas_listing() )
		|| ( function_exists( 'dch_fse_is_area_child' ) && dch_fse_is_area_child() );
	if ( $anim_pages ) {
		$hero_js = get_template_directory() . '/assets/js/hero.js';
		if ( file_exists( $hero_js ) ) {
			wp_enqueue_script(
				'dch-fse-hero',
				get_template_directory_uri() . '/assets/js/hero.js',
				[],
				(string) filemtime( $hero_js ),
				[ 'in_footer' => true, 'strategy' => 'defer' ]
			);
		}
	}
}, 100 );

/**
 * Inline /assets/css/critical.css inside <style> at the top of <head>.
 * Skips silently if the file does not exist.
 *
 * NOTE: theme.css is loaded render-blocking (a normal <link rel="stylesheet">).
 * A previous non-blocking preload-then-swap was removed because it caused a
 * flash of unstyled content: the page painted before theme.css applied, and
 * the critical.css this pattern depends on was never shipped. If a proper
 * critical.css is added here later, the async swap can be reintroduced.
 */
function dch_fse_inline_critical_css(): void {
	$path = get_template_directory() . '/assets/css/critical.css';
	if ( ! file_exists( $path ) ) {
		return;
	}

	$css = file_get_contents( $path );
	if ( false === $css || '' === $css ) {
		return;
	}

	// Defensive: prevent any embedded </style> from breaking out.
	$css = str_replace( '</style', '<\/style', $css );

	echo "<style id=\"dch-fse-critical\">\n" . $css . "\n</style>\n";
}
add_action( 'wp_head', 'dch_fse_inline_critical_css', 1 );
