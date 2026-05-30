<?php
/**
 * Single post — renders the article header, content, and footer (tags +
 * previous/next navigation) in an 850px centered column matching the
 * Infratech post_layout_classic single-post layout.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Render the primary category as an uppercase badge above the title.
 */
function dch_fse_single_render_category( WP_Post $post ): string {
	$cats = get_the_category( $post->ID );
	if ( empty( $cats ) ) {
		return '';
	}
	$primary = null;
	foreach ( $cats as $c ) {
		if ( 'uncategorized' !== $c->slug ) {
			$primary = $c;
			break;
		}
	}
	if ( ! $primary ) {
		$primary = $cats[0];
	}
	return sprintf(
		'<a class="dch-post__cat" href="%1$s">%2$s</a>',
		esc_url( get_category_link( $primary ) ),
		esc_html( $primary->name )
	);
}

/**
 * Render the comma-separated tag list.
 */
function dch_fse_single_render_tags( WP_Post $post ): string {
	$tags = get_the_tags( $post->ID );
	if ( empty( $tags ) || is_wp_error( $tags ) ) {
		return '';
	}
	$items = [];
	foreach ( $tags as $t ) {
		$items[] = sprintf(
			'<a class="dch-post-tag" href="%1$s">%2$s</a>',
			esc_url( get_tag_link( $t->term_id ) ),
			esc_html( $t->name )
		);
	}
	return '<div class="dch-post-tags"><span class="dch-post-tags__label">Tags:</span> ' . implode( '', $items ) . '</div>';
}

/**
 * Render previous/next post navigation.
 */
function dch_fse_single_render_nav(): string {
	$prev = get_previous_post();
	$next = get_next_post();
	if ( ! $prev && ! $next ) {
		return '';
	}

	ob_start();
	?>
	<nav class="dch-post-nav" aria-label="Post navigation">
		<?php if ( $prev ) : ?>
			<a class="dch-post-nav__link dch-post-nav__link--prev" href="<?php echo esc_url( get_permalink( $prev ) ); ?>">
				<span class="dch-post-nav__arrow" aria-hidden="true">&larr;</span>
				<span class="dch-post-nav__inner">
					<span class="dch-post-nav__label">Previous</span>
					<span class="dch-post-nav__title"><?php echo esc_html( get_the_title( $prev ) ); ?></span>
				</span>
			</a>
		<?php else : ?>
			<span class="dch-post-nav__link dch-post-nav__link--placeholder" aria-hidden="true"></span>
		<?php endif; ?>

		<?php if ( $next ) : ?>
			<a class="dch-post-nav__link dch-post-nav__link--next" href="<?php echo esc_url( get_permalink( $next ) ); ?>">
				<span class="dch-post-nav__inner">
					<span class="dch-post-nav__label">Next</span>
					<span class="dch-post-nav__title"><?php echo esc_html( get_the_title( $next ) ); ?></span>
				</span>
				<span class="dch-post-nav__arrow" aria-hidden="true">&rarr;</span>
			</a>
		<?php else : ?>
			<span class="dch-post-nav__link dch-post-nav__link--placeholder" aria-hidden="true"></span>
		<?php endif; ?>
	</nav>
	<?php
	return (string) ob_get_clean();
}

/**
 * Render the full single-post layout. Used by the `dch/single-post` block in
 * templates/single.html.
 */
function dch_fse_single_render(): string {
	if ( ! is_singular( 'post' ) ) {
		return '';
	}

	$post = get_post();
	if ( ! $post ) {
		return '';
	}

	$category = dch_fse_single_render_category( $post );
	$title    = esc_html( get_the_title( $post ) );
	$date     = esc_html( get_the_date( '', $post ) );
	$thumb    = get_the_post_thumbnail(
		$post,
		'large',
		[
			'class'         => 'dch-post__hero-img',
			'sizes'         => '(max-width: 1023px) 100vw, 850px',
			'fetchpriority' => 'high',
			'decoding'      => 'async',
		]
	);

	// Filtered content (blocks, shortcodes, embeds, oembed, wpautop).
	$content = apply_filters( 'the_content', $post->post_content );
	$content = str_replace( ']]>', ']]&gt;', $content );

	$tags = dch_fse_single_render_tags( $post );
	$nav  = dch_fse_single_render_nav();

	ob_start();
	?>
	<article id="post-<?php echo (int) $post->ID; ?>" class="dch-post" data-dch-anim="block">
		<header class="dch-post__header">
			<?php if ( '' !== $category ) : ?>
				<div class="dch-post__cats"><?php echo $category; ?></div>
			<?php endif; ?>

			<h1 class="dch-post__title"><?php echo $title; ?></h1>

			<div class="dch-post__meta">
				<span class="dch-post__date"><?php echo $date; ?></span>
			</div>

			<?php if ( $thumb ) : ?>
				<figure class="dch-post__hero">
					<?php echo $thumb; ?>
				</figure>
			<?php endif; ?>
		</header>

		<div class="dch-post__content">
			<?php echo $content; ?>
		</div>

		<footer class="dch-post__footer">
			<?php echo $tags; ?>
			<?php echo $nav; ?>
		</footer>
	</article>
	<?php
	return (string) ob_get_clean();
}

add_action( 'init', static function (): void {
	register_block_type( 'dch/single-post', [
		'api_version'     => 3,
		'title'           => 'DCH Single Post',
		'category'        => 'theme',
		'render_callback' => 'dch_fse_single_render',
		'supports'        => [ 'html' => false ],
	] );
} );
