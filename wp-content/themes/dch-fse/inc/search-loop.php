<?php
/**
 * Search results loop — renders the standard WP search query as a single-column
 * stack of large cards, mirroring the Infratech post_layout_classic_1 layout
 * used on /blog/ and search results pages.
 *
 * Cards reuse `dch_fse_blog_render_card()` so blog posts and pages share the
 * same visual treatment.
 */

defined( 'ABSPATH' ) || exit;

const DCH_FSE_SEARCH_PER_PAGE = 6;

/**
 * Render the WP search query results.
 */
function dch_fse_search_render_loop(): string {
	if ( ! is_search() ) {
		return '';
	}

	$query   = $GLOBALS['wp_query'];
	$paged   = max( 1, (int) ( get_query_var( 'paged' ) ?: 1 ) );
	$total   = (int) $query->max_num_pages;

	if ( ! $query->have_posts() ) {
		ob_start();
		?>
		<div class="dch-blog-empty">
			<p>No results found for &ldquo;<strong><?php echo esc_html( get_search_query() ); ?></strong>&rdquo;.</p>
			<p>Try a different search term, or <a href="<?php echo esc_url( home_url( '/' ) ); ?>">go back to the home page</a>.</p>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	$out = '<div class="dch-blog-list">';
	while ( $query->have_posts() ) {
		$query->the_post();
		$post = get_post();
		// Re-use the blog card renderer for visual parity with /blog/.
		$out .= dch_fse_blog_render_card( $post );
	}
	wp_reset_postdata();
	$out .= '</div>';

	// Pretty pagination: /search/{query}/page/N/ — WP handles this via core
	// rewrite rules. paginate_links() picks up the right base from the URL.
	if ( $total > 1 ) {
		$links = paginate_links( [
			'total'     => $total,
			'current'   => $paged,
			'prev_text' => '&lt;',
			'next_text' => '&gt;',
			'type'      => 'array',
			'mid_size'  => 1,
			'end_size'  => 1,
		] );
		if ( ! empty( $links ) ) {
			$nav  = '<nav class="dch-blog-pagination" aria-label="Search pagination">';
			$nav .= '<ul class="dch-blog-pagination__list">';
			foreach ( $links as $link ) {
				$is_current = false !== strpos( $link, 'current' );
				$class      = 'dch-blog-pagination__item' . ( $is_current ? ' is-current' : '' );
				$nav       .= '<li class="' . esc_attr( $class ) . '">' . $link . '</li>';
			}
			$nav .= '</ul></nav>';
			$out .= $nav;
		}
	}

	return preg_replace( '/>\s+</', '><', $out );
}

/**
 * Render the search results header: page-intro pattern (eyebrow + H1 echoing
 * the search term + result count lede) plus a styled search form so the user
 * can refine their query without bouncing back to a global search.
 */
function dch_fse_search_render_header(): string {
	if ( ! is_search() ) {
		return '';
	}

	$query  = get_search_query();
	$total  = (int) ( $GLOBALS['wp_query']->found_posts ?? 0 );
	$action = esc_url( home_url( '/' ) );
	$value  = esc_attr( $query );

	if ( $total > 0 ) {
		$lede = sprintf(
			/* translators: 1: result count, 2: search query */
			'%1$s %2$s for &ldquo;<strong>%3$s</strong>&rdquo;',
			number_format_i18n( $total ),
			1 === $total ? 'result' : 'results',
			esc_html( $query )
		);
	} else {
		$lede = sprintf(
			'No results for &ldquo;<strong>%s</strong>&rdquo;',
			esc_html( $query )
		);
	}

	$h1 = '' !== $query
		? sprintf( 'Results for &ldquo;%s&rdquo;', esc_html( $query ) )
		: 'Search';

	ob_start();
	?>
	<section class="dch-page-intro dch-page-intro--search">
		<div class="dch-page-intro__inner">

			<p class="dch-page-intro__eyebrow" data-dch-anim="block">Search results</p>

			<h1 class="dch-page-intro__title dch-page-intro__title--search" data-dch-anim="block"><?php echo $h1; ?></h1>

			<p class="dch-page-intro__lede" data-dch-anim="block"><?php echo $lede; ?></p>

			<form class="dch-search-form" role="search" method="get" action="<?php echo $action; ?>" data-dch-anim="block">
				<label class="dch-search-form__label" for="dch-search-input">Search the site</label>
				<div class="dch-search-form__control">
					<span class="dch-search-form__icon" aria-hidden="true">
						<svg width="18" height="18" viewBox="0 0 18 18" focusable="false"><path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M11.5 11.5L17 17M7.5 14a6.5 6.5 0 1 1 0-13 6.5 6.5 0 0 1 0 13Z"/></svg>
					</span>
					<input
						id="dch-search-input"
						class="dch-search-form__input"
						type="search"
						name="s"
						value="<?php echo $value; ?>"
						placeholder="Search for projects, posts, pages&hellip;"
						autocomplete="off"
					/>
					<button class="dch-search-form__submit" type="submit">
						<span class="dch-search-form__submit-text">Search</span>
					</button>
				</div>
			</form>

		</div>
	</section>
	<?php
	return preg_replace( '/>\s+</', '><', (string) ob_get_clean() );
}

add_action( 'init', static function (): void {
	register_block_type( 'dch/search-loop', [
		'api_version'     => 3,
		'title'           => 'DCH Search Loop',
		'category'        => 'theme',
		'render_callback' => 'dch_fse_search_render_loop',
		'supports'        => [ 'html' => false ],
	] );

	register_block_type( 'dch/search-header', [
		'api_version'     => 3,
		'title'           => 'DCH Search Header',
		'category'        => 'theme',
		'render_callback' => 'dch_fse_search_render_header',
		'supports'        => [ 'html' => false ],
	] );
} );

/**
 * Make WP search return more results per page than the default 10. The blog
 * uses 6 — keep search consistent with that visual rhythm.
 */
add_action( 'pre_get_posts', static function ( WP_Query $q ): void {
	if ( $q->is_main_query() && $q->is_search() && ! is_admin() ) {
		$q->set( 'posts_per_page', DCH_FSE_SEARCH_PER_PAGE );
	}
} );
