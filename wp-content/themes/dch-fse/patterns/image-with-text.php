<?php
/**
 * Title: Image with Text
 * Slug: dch-fse/image-with-text
 * Categories: dch-fse
 * Description: Two-column layout with an image on one side and text plus a CTA on the other. Add the class "is-style-reverse" to flip the order on desktop.
 * Keywords: image, text, columns, alternating
 * Block Types: core/columns
 * Viewport Width: 1400
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group {"tagName":"section","align":"full","layout":{"type":"constrained"},"style":{"spacing":{"padding":{"top":"var:preset|spacing|3xl","bottom":"var:preset|spacing|3xl","left":"var:preset|spacing|md","right":"var:preset|spacing|md"}}}} -->
<section class="wp-block-group alignfull" style="padding-top:var(--wp--preset--spacing--3xl);padding-right:var(--wp--preset--spacing--md);padding-bottom:var(--wp--preset--spacing--3xl);padding-left:var(--wp--preset--spacing--md)">

<!-- wp:columns {"verticalAlignment":"center"} -->
<div class="wp-block-columns are-vertically-aligned-center">

<!-- wp:column {"verticalAlignment":"center"} -->
<div class="wp-block-column is-vertically-aligned-center">
<!-- wp:image {"sizeSlug":"dch-card","style":{"border":{"radius":"0"}}} -->
<figure class="wp-block-image size-dch-card has-custom-border"><img alt="" style="border-radius:0"/></figure>
<!-- /wp:image -->
</div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center"} -->
<div class="wp-block-column is-vertically-aligned-center">

<!-- wp:heading {"level":2} -->
<h2 class="wp-block-heading"><?php esc_html_e( 'A process built on collaboration', 'dch-fse' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><?php esc_html_e( 'From the first sketch to the final walk-through, you stay close to the work. Decisions are made together, with full context, never rushed past you.', 'dch-fse' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons">
<!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="#"><?php esc_html_e( 'Learn more', 'dch-fse' ); ?></a></div>
<!-- /wp:button -->
</div>
<!-- /wp:buttons -->

</div>
<!-- /wp:column -->

</div>
<!-- /wp:columns -->

</section>
<!-- /wp:group -->
