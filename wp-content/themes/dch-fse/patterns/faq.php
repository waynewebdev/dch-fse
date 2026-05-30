<?php
/**
 * Title: FAQ
 * Slug: dch-fse/faq
 * Categories: dch-fse
 * Description: Accordion-style FAQ list using core/details for native disclosure (no JavaScript required).
 * Keywords: faq, questions, accordion
 * Block Types: core/details
 * Viewport Width: 1400
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group {"tagName":"section","align":"full","layout":{"type":"constrained"},"style":{"spacing":{"padding":{"top":"var:preset|spacing|3xl","bottom":"var:preset|spacing|3xl","left":"var:preset|spacing|md","right":"var:preset|spacing|md"}}}} -->
<section class="wp-block-group alignfull" style="padding-top:var(--wp--preset--spacing--3xl);padding-right:var(--wp--preset--spacing--md);padding-bottom:var(--wp--preset--spacing--3xl);padding-left:var(--wp--preset--spacing--md)">

<!-- wp:heading {"textAlign":"center","level":2,"style":{"spacing":{"margin":{"bottom":"var:preset|spacing|2xl"}}}} -->
<h2 class="wp-block-heading has-text-align-center" style="margin-bottom:var(--wp--preset--spacing--2xl)"><?php esc_html_e( 'Frequently asked questions', 'dch-fse' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:details {"summary":"<?php esc_attr_e( 'How long does a custom home take to build?', 'dch-fse' ); ?>","style":{"spacing":{"padding":{"top":"var:preset|spacing|md","bottom":"var:preset|spacing|md"}},"border":{"top":{"color":"var:preset|color|muted","width":"1px"}}}} -->
<details class="wp-block-details" style="border-top-color:var(--wp--preset--color--muted);border-top-width:1px;padding-top:var(--wp--preset--spacing--md);padding-bottom:var(--wp--preset--spacing--md)"><summary><?php esc_html_e( 'How long does a custom home take to build?', 'dch-fse' ); ?></summary>
<!-- wp:paragraph -->
<p><?php esc_html_e( 'Most projects run 12 to 18 months from final design approval to keys, depending on scope, finishes, and lot conditions.', 'dch-fse' ); ?></p>
<!-- /wp:paragraph -->
</details>
<!-- /wp:details -->

<!-- wp:details {"summary":"<?php esc_attr_e( 'Do you work from our plans or yours?', 'dch-fse' ); ?>","style":{"spacing":{"padding":{"top":"var:preset|spacing|md","bottom":"var:preset|spacing|md"}},"border":{"top":{"color":"var:preset|color|muted","width":"1px"}}}} -->
<details class="wp-block-details" style="border-top-color:var(--wp--preset--color--muted);border-top-width:1px;padding-top:var(--wp--preset--spacing--md);padding-bottom:var(--wp--preset--spacing--md)"><summary><?php esc_html_e( 'Do you work from our plans or yours?', 'dch-fse' ); ?></summary>
<!-- wp:paragraph -->
<p><?php esc_html_e( 'Both. Many clients come to us with an architect already engaged; others use our in-house design-build process. Either way, we collaborate closely on every detail.', 'dch-fse' ); ?></p>
<!-- /wp:paragraph -->
</details>
<!-- /wp:details -->

<!-- wp:details {"summary":"<?php esc_attr_e( 'What does a custom build cost?', 'dch-fse' ); ?>","style":{"spacing":{"padding":{"top":"var:preset|spacing|md","bottom":"var:preset|spacing|md"}},"border":{"top":{"color":"var:preset|color|muted","width":"1px"},"bottom":{"color":"var:preset|color|muted","width":"1px"}}}} -->
<details class="wp-block-details" style="border-top-color:var(--wp--preset--color--muted);border-top-width:1px;border-bottom-color:var(--wp--preset--color--muted);border-bottom-width:1px;padding-top:var(--wp--preset--spacing--md);padding-bottom:var(--wp--preset--spacing--md)"><summary><?php esc_html_e( 'What does a custom build cost?', 'dch-fse' ); ?></summary>
<!-- wp:paragraph -->
<p><?php esc_html_e( 'Costs vary widely with scope and finish level. After our initial conversation we provide a transparent budget range, then refine it as the design progresses.', 'dch-fse' ); ?></p>
<!-- /wp:paragraph -->
</details>
<!-- /wp:details -->

</section>
<!-- /wp:group -->
