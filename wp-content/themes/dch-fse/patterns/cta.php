<?php
/**
 * Title: Call to Action
 * Slug: dch-fse/cta
 * Categories: dch-fse
 * Description: Centered call-to-action band with headline, subhead, and a button.
 * Keywords: cta, banner, call to action
 * Block Types: core/group
 * Viewport Width: 1400
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group {"tagName":"section","align":"full","backgroundColor":"foreground","textColor":"background","layout":{"type":"constrained"},"style":{"spacing":{"padding":{"top":"var:preset|spacing|3xl","bottom":"var:preset|spacing|3xl","left":"var:preset|spacing|md","right":"var:preset|spacing|md"}}}} -->
<section class="wp-block-group alignfull has-background-color has-foreground-background-color has-text-color has-background" style="padding-top:var(--wp--preset--spacing--3xl);padding-right:var(--wp--preset--spacing--md);padding-bottom:var(--wp--preset--spacing--3xl);padding-left:var(--wp--preset--spacing--md)">

<!-- wp:heading {"textAlign":"center","level":2,"textColor":"background"} -->
<h2 class="wp-block-heading has-text-align-center has-background-color has-text-color"><?php esc_html_e( 'Let\'s talk about your home.', 'dch-fse' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","fontSize":"md","style":{"typography":{"lineHeight":"1.5"}}} -->
<p class="has-text-align-center has-md-font-size" style="line-height:1.5"><?php esc_html_e( 'Tell us about the project. We respond to every inquiry within two business days.', 'dch-fse' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"},"style":{"spacing":{"margin":{"top":"var:preset|spacing|lg"}}}} -->
<div class="wp-block-buttons" style="margin-top:var(--wp--preset--spacing--lg)">
<!-- wp:button {"backgroundColor":"background","textColor":"foreground"} -->
<div class="wp-block-button"><a class="wp-block-button__link has-foreground-color has-background-background-color has-text-color has-background wp-element-button" href="#contact"><?php esc_html_e( 'Get in touch', 'dch-fse' ); ?></a></div>
<!-- /wp:button -->
</div>
<!-- /wp:buttons -->

</section>
<!-- /wp:group -->
