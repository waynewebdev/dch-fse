<?php
/**
 * About page. Synced into wp_posts via `wp dch sync`.
 * See inc/page-registry.php for the page schema docblock.
 */

defined( 'ABSPATH' ) || exit;

return [

	'slug'       => 'about',
	'title'      => 'About Dynamic Custom Homes',
	'updated_at' => '2026-05-02T00:00:00Z',
	'order'      => 10,
	'excerpt'    => 'A custom home builder focused on craft, longevity, and a process that respects the people we build for.',

	'blocks' => <<<HTML
<!-- wp:paragraph {"className":"has-md-font-size"} -->
<p class="has-md-font-size">We build custom homes the way they should be built — with patience, with attention to how a family actually lives in a space, and with an obsession over the details that don't show up in the brochure.</p>
<!-- /wp:paragraph -->

<!-- wp:group {"tagName":"section","align":"full","layout":{"type":"constrained"},"style":{"spacing":{"padding":{"top":"var:preset|spacing|2xl","bottom":"var:preset|spacing|2xl"}}}} -->
<section class="wp-block-group alignfull" style="padding-top:var(--wp--preset--spacing--2xl);padding-bottom:var(--wp--preset--spacing--2xl)">
<!-- wp:heading {"level":2} -->
<h2 class="wp-block-heading">Our process</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Every home starts with a conversation about how you live, then moves through design, planning, and a transparent build phase you stay close to from the first stake to the final walk-through.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2} -->
<h2 class="wp-block-heading">Our standards</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Materials chosen for longevity, structural choices that exceed code, and finish work that holds up to decades of use. The goal isn't to build a house. It's to build a home worth keeping.</p>
<!-- /wp:paragraph -->
</section>
<!-- /wp:group -->
HTML,

	'seo' => [
		'title'       => 'About — Dynamic Custom Homes',
		'description' => 'Meet Dynamic Custom Homes — a custom builder focused on craft, longevity, and a transparent process built around the people we build for.',
		'og_image'    => '/assets/images/og-default.jpg',
		'lcp_image'   => null,
		'robots'      => 'index,follow',
	],
];
