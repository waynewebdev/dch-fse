<?php
/**
 * Title: Feature Grid
 * Slug: dch-fse/feature-grid
 * Categories: dch-fse
 * Description: Three-column responsive grid of feature cards (heading, paragraph). Use for differentiators, services, or capabilities.
 * Keywords: features, grid, columns
 * Block Types: core/columns
 * Viewport Width: 1400
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group {"tagName":"section","align":"full","layout":{"type":"constrained"},"style":{"spacing":{"padding":{"top":"var:preset|spacing|3xl","bottom":"var:preset|spacing|3xl","left":"var:preset|spacing|md","right":"var:preset|spacing|md"}}}} -->
<section class="wp-block-group alignfull" style="padding-top:var(--wp--preset--spacing--3xl);padding-right:var(--wp--preset--spacing--md);padding-bottom:var(--wp--preset--spacing--3xl);padding-left:var(--wp--preset--spacing--md)">

<!-- wp:heading {"textAlign":"center","level":2,"style":{"spacing":{"margin":{"bottom":"var:preset|spacing|2xl"}}}} -->
<h2 class="wp-block-heading has-text-align-center" style="margin-bottom:var(--wp--preset--spacing--2xl)"><?php esc_html_e( 'Why build with us', 'dch-fse' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:columns {"verticalAlignment":"top"} -->
<div class="wp-block-columns are-vertically-aligned-top">

<!-- wp:column {"verticalAlignment":"top"} -->
<div class="wp-block-column is-vertically-aligned-top">
<!-- wp:heading {"level":3,"fontSize":"lg"} -->
<h3 class="wp-block-heading has-lg-font-size"><?php esc_html_e( 'Built around how you live', 'dch-fse' ); ?></h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p><?php esc_html_e( 'Every home starts with a careful conversation about routines, hosting, family, and the small daily moments. Architecture follows.', 'dch-fse' ); ?></p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"top"} -->
<div class="wp-block-column is-vertically-aligned-top">
<!-- wp:heading {"level":3,"fontSize":"lg"} -->
<h3 class="wp-block-heading has-lg-font-size"><?php esc_html_e( 'Materials chosen for longevity', 'dch-fse' ); ?></h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p><?php esc_html_e( 'We specify materials that age beautifully and hold up to decades of real use, not the cheapest options that meet code.', 'dch-fse' ); ?></p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"top"} -->
<div class="wp-block-column is-vertically-aligned-top">
<!-- wp:heading {"level":3,"fontSize":"lg"} -->
<h3 class="wp-block-heading has-lg-font-size"><?php esc_html_e( 'A transparent build', 'dch-fse' ); ?></h3>
<!-- /wp:heading -->
<!-- wp:paragraph -->
<p><?php esc_html_e( 'You stay close to the work. Clear schedules, detailed walk-throughs, and no surprises in the final invoice.', 'dch-fse' ); ?></p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:column -->

</div>
<!-- /wp:columns -->

</section>
<!-- /wp:group -->
