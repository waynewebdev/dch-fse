<?php
/**
 * Blog loop — renders the standard `post` archive on /blog/ as a single-column
 * stack of large cards (image, category, title, date, excerpt) with classic
 * /blog/page/N/ pagination at the bottom.
 *
 * Mirrors the Infratech post_layout_classic_1 layout.
 */

defined( 'ABSPATH' ) || exit;

const DCH_FSE_BLOG_PER_PAGE = 6;

/**
 * Render a single blog card.
 */
function dch_fse_blog_render_card( WP_Post $post ): string {
	$permalink = get_permalink( $post );
	$title     = get_the_title( $post );

	$thumb_html = get_the_post_thumbnail(
		$post,
		'large',
		[
			'class'    => 'dch-blog-card__img',
			'loading'  => 'lazy',
			'decoding' => 'async',
			'sizes'    => '(max-width: 1023px) 100vw, 850px',
		]
	);

	// Primary category, falling back to the first non-uncategorized term.
	$category_html = '';
	$cats          = get_the_category( $post->ID );
	if ( ! empty( $cats ) ) {
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
		$category_html = sprintf(
			'<a class="dch-blog-card__cat" href="%1$s">%2$s</a>',
			esc_url( get_category_link( $primary ) ),
			esc_html( $primary->name )
		);
	}

	$date  = get_the_date( '', $post );
	$excerpt = get_the_excerpt( $post );

	ob_start();
	?>
	<article class="dch-blog-card" data-dch-anim="block">
		<?php if ( $thumb_html ) : ?>
			<a class="dch-blog-card__media" href="<?php echo esc_url( $permalink ); ?>" aria-label="<?php echo esc_attr( $title ); ?>">
				<?php echo $thumb_html; ?>
			</a>
		<?php endif; ?>
		<div class="dch-blog-card__body">
			<?php if ( '' !== $category_html ) : ?>
				<div class="dch-blog-card__cats"><?php echo $category_html; ?></div>
			<?php endif; ?>
			<h2 class="dch-blog-card__title">
				<a href="<?php echo esc_url( $permalink ); ?>" rel="bookmark"><?php echo esc_html( $title ); ?></a>
			</h2>
			<div class="dch-blog-card__meta">
				<a class="dch-blog-card__date" href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $date ); ?></a>
			</div>
			<?php if ( '' !== $excerpt ) : ?>
				<div class="dch-blog-card__excerpt"><?php echo wp_kses_post( $excerpt ); ?></div>
			<?php endif; ?>
		</div>
	</article>
	<?php
	return (string) ob_get_clean();
}

/**
 * Render WP-style pretty pagination (/blog/page/N/) styled to match the old
 * site's 50×50 square buttons. Uses the main query's paged context so the
 * block plays nicely with WordPress's existing rewrite rules.
 */
function dch_fse_blog_render_pagination( int $current, int $total ): string {
	if ( $total < 2 ) {
		return '';
	}

	$links = paginate_links( [
		'base'      => trailingslashit( get_post_type_archive_link( 'post' ) ?: home_url( '/blog/' ) ) . 'page/%#%/',
		'format'    => '',
		'current'   => $current,
		'total'     => $total,
		'prev_text' => '&lt;',
		'next_text' => '&gt;',
		'type'      => 'array',
		'mid_size'  => 1,
		'end_size'  => 1,
	] );

	if ( empty( $links ) ) {
		return '';
	}

	$out  = '<nav class="dch-blog-pagination" aria-label="Posts pagination">';
	$out .= '<ul class="dch-blog-pagination__list">';
	foreach ( $links as $link ) {
		$is_current = false !== strpos( $link, 'current' );
		$class      = 'dch-blog-pagination__item' . ( $is_current ? ' is-current' : '' );
		$out       .= '<li class="' . esc_attr( $class ) . '">' . $link . '</li>';
	}
	$out .= '</ul>';
	$out .= '</nav>';

	return $out;
}

/**
 * Render the blog loop. Uses the main WP query's paged context so that pretty
 * /blog/page/N/ URLs Just Work via core rewrite rules.
 */
function dch_fse_blog_render_loop(): string {
	$paged = max( 1, (int) ( get_query_var( 'paged' ) ?: get_query_var( 'page' ) ?: 1 ) );

	$query = new WP_Query( [
		'post_type'              => 'post',
		'post_status'            => 'publish',
		'posts_per_page'         => DCH_FSE_BLOG_PER_PAGE,
		'paged'                  => $paged,
		'orderby'                => 'date',
		'order'                  => 'DESC',
		'no_found_rows'          => false,
		'update_post_term_cache' => true,
	] );

	if ( ! $query->have_posts() ) {
		return '<p class="dch-blog-empty">No posts to show yet.</p>';
	}

	$out = '<div class="dch-blog-list">';
	while ( $query->have_posts() ) {
		$query->the_post();
		$out .= dch_fse_blog_render_card( get_post() );
	}
	wp_reset_postdata();
	$out .= '</div>';

	$out .= dch_fse_blog_render_pagination( $paged, (int) $query->max_num_pages );

	return preg_replace( '/>\s+</', '><', $out );
}

/**
 * Register the dynamic block for FSE templates.
 */
add_action( 'init', static function (): void {
	register_block_type( 'dch/blog-loop', [
		'api_version'     => 3,
		'title'           => 'DCH Blog Loop',
		'category'        => 'theme',
		'render_callback' => 'dch_fse_blog_render_loop',
		'supports'        => [ 'html' => false ],
	] );
} );

add_shortcode( 'dch_blog_loop', 'dch_fse_blog_render_loop' );
