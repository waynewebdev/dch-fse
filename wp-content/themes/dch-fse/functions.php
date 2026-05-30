<?php
/**
 * Theme bootstrap. Loads each module under /inc/ in a fixed, predictable order.
 */

defined( 'ABSPATH' ) || exit;

$dch_fse_modules = [
	'setup',
	'cleanup',
	'assets',
	'performance',
	'images',
	'page-registry',
	'projects-loop',
	'project-page',
	'service-child',
	'area-page',
	'blog-loop',
	'search-loop',
	'single-post',
	'post-seo',
	'seo',
	'schema',
	'sitemap',
	'robots',
];

foreach ( $dch_fse_modules as $dch_fse_module ) {
	$dch_fse_path = __DIR__ . '/inc/' . $dch_fse_module . '.php';
	if ( file_exists( $dch_fse_path ) ) {
		require_once $dch_fse_path;
	}
}

unset( $dch_fse_modules, $dch_fse_module, $dch_fse_path );
