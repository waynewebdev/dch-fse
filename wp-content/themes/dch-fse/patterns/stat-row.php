<?php
/**
 * Title: Stat Row
 * Slug: dch-fse/stat-row
 * Categories: dch-fse
 * Description: A horizontal row of three or four large numbers with descriptive labels (e.g. years building, homes completed, awards earned).
 * Keywords: stats, numbers, metrics
 * Block Types: core/columns
 * Viewport Width: 1400
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group {"tagName":"section","align":"full","layout":{"type":"constrained"},"style":{"spacing":{"padding":{"top":"var:preset|spacing|2xl","bottom":"var:preset|spacing|2xl","left":"var:preset|spacing|md","right":"var:preset|spacing|md"}}}} -->
<section class="wp-block-group alignfull" style="padding-top:var(--wp--preset--spacing--2xl);padding-bottom:var(--wp--preset--spacing--2xl);padding-left:var(--wp--preset--spacing--md);padding-right:var(--wp--preset--spacing--md)">

<!-- wp:columns {"verticalAlignment":"center","style":{"spacing":{"blockGap":{"top":"var:preset|spacing|xl","left":"var:preset|spacing|xl"}}}} -->
<div class="wp-block-columns are-vertically-aligned-center">

<!-- wp:column {"verticalAlignment":"center"} -->
<div class="wp-block-column is-vertically-aligned-center">
<!-- wp:heading {"textAlign":"center","level":3,"style":{"typography":{"fontSize":"var(--wp--preset--font-size--3xl)","lineHeight":"1"}}} -->
<h3 class="wp-block-heading has-text-align-center" style="font-size:var(--wp--preset--font-size--3xl);line-height:1">25</h3>
<!-- /wp:heading -->
<!-- wp:paragraph {"align":"center","fontSize":"sm","textColor":"muted","style":{"typography":{"textTransform":"uppercase","letterSpacing":"0.08em"}}} -->
<p class="has-text-align-center has-muted-color has-text-color has-sm-font-size" style="text-transform:uppercase;letter-spacing:0.08em"><?php esc_html_e( 'Years building', 'dch-fse' ); ?></p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center"} -->
<div class="wp-block-column is-vertically-aligned-center">
<!-- wp:heading {"textAlign":"center","level":3,"style":{"typography":{"fontSize":"var(--wp--preset--font-size--3xl)","lineHeight":"1"}}} -->
<h3 class="wp-block-heading has-text-align-center" style="font-size:var(--wp--preset--font-size--3xl);line-height:1">120+</h3>
<!-- /wp:heading -->
<!-- wp:paragraph {"align":"center","fontSize":"sm","textColor":"muted","style":{"typography":{"textTransform":"uppercase","letterSpacing":"0.08em"}}} -->
<p class="has-text-align-center has-muted-color has-text-color has-sm-font-size" style="text-transform:uppercase;letter-spacing:0.08em"><?php esc_html_e( 'Custom homes', 'dch-fse' ); ?></p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center"} -->
<div class="wp-block-column is-vertically-aligned-center">
<!-- wp:heading {"textAlign":"center","level":3,"style":{"typography":{"fontSize":"var(--wp--preset--font-size--3xl)","lineHeight":"1"}}} -->
<h3 class="wp-block-heading has-text-align-center" style="font-size:var(--wp--preset--font-size--3xl);line-height:1">100%</h3>
<!-- /wp:heading -->
<!-- wp:paragraph {"align":"center","fontSize":"sm","textColor":"muted","style":{"typography":{"textTransform":"uppercase","letterSpacing":"0.08em"}}} -->
<p class="has-text-align-center has-muted-color has-text-color has-sm-font-size" style="text-transform:uppercase;letter-spacing:0.08em"><?php esc_html_e( 'Hill Country', 'dch-fse' ); ?></p>
<!-- /wp:paragraph -->
</div>
<!-- /wp:column -->

</div>
<!-- /wp:columns -->

</section>
<!-- /wp:group -->
