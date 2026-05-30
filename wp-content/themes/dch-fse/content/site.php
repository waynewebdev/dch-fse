<?php
/**
 * Site-level configuration. Single source of truth for brand identity,
 * organization data, and per-environment overrides.
 *
 * Consumed by inc/page-registry.php (via dch_fse_site()), the SEO pipeline,
 * the schema builders, and the performance preconnect emitter.
 */

defined( 'ABSPATH' ) || exit;

return [

	'name'    => 'Dynamic Custom Homes',
	'tagline' => 'Custom homes, built for the way you live.',

	// Canonical site URL. Filterable per-environment via dch_fse_site_url.
	'url' => apply_filters( 'dch_fse_site_url', home_url( '/' ) ),

	// Path is theme-relative. Resolved against get_template_directory_uri()
	// when emitted as an absolute URL.
	'default_og_image' => '/assets/images/og-default.jpg',

	'organization' => [
		'legal_name'  => 'Dynamic Custom Homes',
		'logo'        => '/assets/images/dch-logo@2x.webp',
		'address'     => [
			'street'      => '',
			'locality'    => '',
			'region'      => '',
			'postal_code' => '',
			'country'     => 'US',
		],
		'phone'       => '',
		'email'       => '',
		'sameAs'      => [
			// 'https://www.facebook.com/dynamiccustomhomes',
			// 'https://www.instagram.com/dynamiccustomhomes',
		],
	],

	// Third-party origins to <link rel="preconnect">. Empty by default.
	'preconnect_origins' => [],

	// Reserved for future hreflang work. Primary language only for now.
	'languages' => [ 'en-US' ],
];
