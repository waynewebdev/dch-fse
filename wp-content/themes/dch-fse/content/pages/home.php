<?php
/**
 * Home page. Synced into wp_posts via `wp dch sync`.
 * See inc/page-registry.php for the page schema docblock.
 */

defined( 'ABSPATH' ) || exit;

return [

	'slug'          => 'home',
	'title'         => 'Dynamic Custom Homes',
	'updated_at'    => '2026-05-02T00:00:00Z',
	'is_front_page' => true,
	'excerpt'       => 'Custom homes built for the way you live. Bespoke residential construction with uncompromising craftsmanship.',

	'blocks' => <<<HTML
<!-- wp:group {"tagName":"section","align":"full","layout":{"type":"constrained"},"style":{"spacing":{"padding":{"top":"var:preset|spacing|3xl","bottom":"var:preset|spacing|3xl"}}}} -->
<section class="wp-block-group alignfull" style="padding-top:var(--wp--preset--spacing--3xl);padding-bottom:var(--wp--preset--spacing--3xl)">
<!-- wp:heading {"level":1,"textAlign":"center"} -->
<h1 class="wp-block-heading has-text-align-center">Custom homes, built for the way you live.</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center">Dynamic Custom Homes builds bespoke residences that pair thoughtful design with the highest standards of craftsmanship — homes that fit how their owners actually live.</p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons">
<!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="/about">Learn about our process</a></div>
<!-- /wp:button -->
</div>
<!-- /wp:buttons -->
</section>
<!-- /wp:group -->

<!-- wp:group {"tagName":"section","align":"full","layout":{"type":"constrained"},"style":{"spacing":{"padding":{"top":"var:preset|spacing|2xl","bottom":"var:preset|spacing|2xl"}}}} -->
<section class="wp-block-group alignfull" style="padding-top:var(--wp--preset--spacing--2xl);padding-bottom:var(--wp--preset--spacing--2xl)">
<!-- wp:heading {"level":2,"textAlign":"center"} -->
<h2 class="wp-block-heading has-text-align-center">Our work</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center">A small selection of recent custom homes.</p>
<!-- /wp:paragraph -->
</section>
<!-- /wp:group -->
HTML,

	'seo' => [
		'title'       => 'Dynamic Custom Homes — Custom homes built for the way you live',
		'description' => 'Bespoke residential construction. Discover our process and approach to building custom homes designed around how their owners actually live.',
		'og_image'    => '/assets/images/og-default.jpg',
		'lcp_image'   => null,
		'robots'      => 'index,follow',
	],
];
