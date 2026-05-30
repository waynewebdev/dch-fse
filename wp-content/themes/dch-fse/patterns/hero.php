<?php
/**
 * Title: Hero
 * Slug: dch-fse/hero
 * Categories: dch-fse
 * Description: Full-width hero with headline, subhead, CTA, and a background image slot. The LCP candidate when used.
 * Keywords: hero, header, landing
 * Block Types: core/cover
 * Viewport Width: 1400
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:cover {"url":"","dimRatio":40,"overlayColor":"foreground","minHeight":75,"minHeightUnit":"vh","contentPosition":"center center","align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|3xl","bottom":"var:preset|spacing|3xl","left":"var:preset|spacing|md","right":"var:preset|spacing|md"}}}} -->
<div class="wp-block-cover alignfull" style="padding-top:var(--wp--preset--spacing--3xl);padding-right:var(--wp--preset--spacing--md);padding-bottom:var(--wp--preset--spacing--3xl);padding-left:var(--wp--preset--spacing--md);min-height:75vh">
<span aria-hidden="true" class="wp-block-cover__background has-foreground-background-color has-background-dim-40 has-background-dim"></span>
<div class="wp-block-cover__inner-container">

<!-- wp:heading {"textAlign":"center","level":1,"textColor":"background","style":{"typography":{"fontSize":"var(--wp--preset--font-size--3xl)"}}} -->
<h1 class="wp-block-heading has-text-align-center has-background-color has-text-color" style="font-size:var(--wp--preset--font-size--3xl)"><?php esc_html_e( 'Custom homes, built for the way you live.', 'dch-fse' ); ?></h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","textColor":"background","fontSize":"md","style":{"typography":{"lineHeight":"1.5"}}} -->
<p class="has-text-align-center has-background-color has-text-color has-md-font-size" style="line-height:1.5"><?php esc_html_e( 'Bespoke residential construction in San Antonio and the Texas Hill Country. Thoughtful design. Unwavering craftsmanship.', 'dch-fse' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"},"style":{"spacing":{"margin":{"top":"var:preset|spacing|lg"}}}} -->
<div class="wp-block-buttons" style="margin-top:var(--wp--preset--spacing--lg)">
<!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="#contact"><?php esc_html_e( 'Start your project', 'dch-fse' ); ?></a></div>
<!-- /wp:button -->
</div>
<!-- /wp:buttons -->

</div></div>
<!-- /wp:cover -->
