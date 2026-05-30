<?php
/**
 * Title: Prose
 * Slug: dch-fse/prose
 * Categories: dch-fse
 * Description: A content-width text block for long-form copy with proper heading hierarchy and link styles. Wraps content in the .prose utility class.
 * Keywords: prose, article, text
 * Block Types: core/group
 * Viewport Width: 800
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group {"tagName":"section","layout":{"type":"constrained"},"className":"prose","style":{"spacing":{"padding":{"top":"var:preset|spacing|2xl","bottom":"var:preset|spacing|2xl"}}}} -->
<section class="wp-block-group prose" style="padding-top:var(--wp--preset--spacing--2xl);padding-bottom:var(--wp--preset--spacing--2xl)">

<!-- wp:heading {"level":2} -->
<h2 class="wp-block-heading"><?php esc_html_e( 'Section heading', 'dch-fse' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><?php esc_html_e( 'Use this pattern for long-form content sections — about pages, process descriptions, project narratives. The .prose class adds vertical rhythm, link styling, and constrains the measure to a comfortable reading width.', 'dch-fse' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading"><?php esc_html_e( 'Subsection heading', 'dch-fse' ); ?></h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><?php esc_html_e( 'Lists, blockquotes, code samples, and inline links all inherit consistent styling from the prose utility. Edit this text to match the page.', 'dch-fse' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul class="wp-block-list">
<!-- wp:list-item --><li><?php esc_html_e( 'First point worth making', 'dch-fse' ); ?></li><!-- /wp:list-item -->
<!-- wp:list-item --><li><?php esc_html_e( 'Second point worth making', 'dch-fse' ); ?></li><!-- /wp:list-item -->
<!-- wp:list-item --><li><?php esc_html_e( 'Third point worth making', 'dch-fse' ); ?></li><!-- /wp:list-item -->
</ul>
<!-- /wp:list -->

</section>
<!-- /wp:group -->
