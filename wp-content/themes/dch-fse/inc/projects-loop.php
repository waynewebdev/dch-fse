<?php
/**
 * Projects loop — renders the child pages of /projects/ as image+content cards
 * via the [dch_projects_loop] shortcode, with pagination at the bottom.
 *
 * The shortcode is used inside templates/page-projects.html so the markup
 * lives next to the rest of the page content while the actual posts come
 * from the database.
 */

defined( 'ABSPATH' ) || exit;

const DCH_FSE_PROJECTS_PAGE_SLUG = 'projects';
const DCH_FSE_PROJECTS_SUBTITLE_KEY = '_dch_project_subtitle';
const DCH_FSE_PROJECTS_PER_PAGE = 6;
const DCH_FSE_PROJECTS_PAGED_KEY = 'p_page';

/**
 * Resolve the parent page id for /projects/. Cached per-request.
 */
function dch_fse_projects_parent_id(): int {
	static $id = null;
	if ( null !== $id ) {
		return $id;
	}
	$page = get_page_by_path( DCH_FSE_PROJECTS_PAGE_SLUG );
	$id = $page ? (int) $page->ID : 0;
	return $id;
}

/**
 * Expose the subtitle field via the REST API and `register_post_meta` so it can
 * be edited in the block editor on each child page.
 */
add_action( 'init', static function (): void {
	register_post_meta( 'page', DCH_FSE_PROJECTS_SUBTITLE_KEY, [
		'type'              => 'string',
		'single'            => true,
		'show_in_rest'      => true,
		'sanitize_callback' => 'sanitize_text_field',
		'auth_callback'     => static function (): bool {
			return current_user_can( 'edit_posts' );
		},
	] );
} );

/**
 * Strip block markup, shortcodes, HTML and trim to ~28 words for the card
 * description. Intentionally short so each card has a consistent silhouette.
 */
function dch_fse_projects_card_excerpt( WP_Post $post ): string {
	$raw = has_excerpt( $post ) ? $post->post_excerpt : $post->post_content;
	$raw = strip_shortcodes( $raw );
	$raw = excerpt_remove_blocks( $raw );
	$raw = wp_strip_all_tags( $raw, true );
	return wp_trim_words( $raw, 28, '&hellip;' );
}

/**
 * Render a single project card matching elementor-element-edda73a.
 */
function dch_fse_projects_render_card( WP_Post $post ): string {
	$permalink = get_permalink( $post );
	$title     = get_the_title( $post );
	$subtitle  = (string) get_post_meta( $post->ID, DCH_FSE_PROJECTS_SUBTITLE_KEY, true );
	$desc      = dch_fse_projects_card_excerpt( $post );

	$thumb_html = get_the_post_thumbnail(
		$post,
		'large',
		[
			'class'         => 'dch-project-card__img',
			'loading'       => 'lazy',
			'decoding'      => 'async',
			'fetchpriority' => 'low',
			'sizes'         => '(max-width: 1023px) 100vw, 66vw',
		]
	);

	ob_start();
	?>
	<article class="dch-project-card" data-dch-anim="block">
		<a class="dch-project-card__media" href="<?php echo esc_url( $permalink ); ?>" aria-label="<?php echo esc_attr( $title ); ?>">
			<?php
			if ( $thumb_html ) {
				echo $thumb_html; // already escaped by WP
			} else {
				echo '<div class="dch-project-card__img dch-project-card__img--placeholder" aria-hidden="true"></div>';
			}
			?>
		</a>
		<div class="dch-project-card__body">
			<div class="dch-project-card__text">
				<h2 class="dch-project-card__title">
					<a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $title ); ?></a>
				</h2>
				<?php if ( '' !== $subtitle ) : ?>
					<p class="dch-project-card__meta"><?php echo esc_html( $subtitle ); ?></p>
				<?php endif; ?>
				<?php if ( '' !== $desc ) : ?>
					<p class="dch-project-card__desc"><?php echo esc_html( $desc ); ?></p>
				<?php endif; ?>
			</div>
			<a class="dch-project-card__cta" href="<?php echo esc_url( $permalink ); ?>">
				<span class="dch-project-card__cta-label">View More</span>
				<svg class="dch-project-card__cta-icon" xmlns="http://www.w3.org/2000/svg" width="22" height="9" viewBox="0 0 21.2 8.837" aria-hidden="true" focusable="false">
					<path fill="currentColor" d="M1,4.4A.6.6,0,1,0,1,5.6ZM21.424,5.424a.6.6,0,0,0,0-.849L17.606.757a.6.6,0,0,0-.848.849L20.152,5,16.757,8.394a.6.6,0,0,0,.848.849ZM1,5.6H21V4.4H1Z" transform="translate(-0.4 -0.582)"/>
				</svg>
			</a>
		</div>
	</article>
	<?php
	return (string) ob_get_clean();
}

/**
 * Render pagination using paginate_links() with the custom `p_page` query var
 * so the URL stays /projects/?p_page=N (no rewrite required).
 */
function dch_fse_projects_render_pagination( int $current, int $total ): string {
	if ( $total < 2 ) {
		return '';
	}

	$base = remove_query_arg( DCH_FSE_PROJECTS_PAGED_KEY, untrailingslashit( get_permalink() ) . '/' );

	$links = paginate_links( [
		'base'      => add_query_arg( DCH_FSE_PROJECTS_PAGED_KEY, '%#%', $base ),
		'format'    => '',
		'current'   => $current,
		'total'     => $total,
		'prev_text' => '&larr;',
		'next_text' => '&rarr;',
		'type'      => 'array',
		'mid_size'  => 1,
		'end_size'  => 1,
	] );

	if ( empty( $links ) ) {
		return '';
	}

	$out  = '<nav class="dch-projects-pagination" aria-label="Projects pagination">';
	$out .= '<ul class="dch-projects-pagination__list">';
	foreach ( $links as $link ) {
		// paginate_links() returns <a>, <span class="current">, or dots <span class="dots">
		$is_current = false !== strpos( $link, 'current' );
		$class      = 'dch-projects-pagination__item' . ( $is_current ? ' is-current' : '' );
		$out       .= '<li class="' . esc_attr( $class ) . '">' . $link . '</li>';
	}
	$out .= '</ul>';
	$out .= '</nav>';

	return $out;
}

/**
 * Render the full loop — shared by both the dynamic block and shortcode entry
 * points. Returns the cards list followed by pagination, with all
 * inter-tag whitespace collapsed (so wpautop can't slip in stray <p>/<br>).
 */
function dch_fse_projects_render_loop(): string {
	$parent_id = dch_fse_projects_parent_id();
	if ( ! $parent_id ) {
		return '';
	}

	$paged = isset( $_GET[ DCH_FSE_PROJECTS_PAGED_KEY ] )
		? max( 1, absint( $_GET[ DCH_FSE_PROJECTS_PAGED_KEY ] ) )
		: 1;

	$query = new WP_Query( [
		'post_type'              => 'page',
		'post_parent'            => $parent_id,
		'posts_per_page'         => DCH_FSE_PROJECTS_PER_PAGE,
		'paged'                  => $paged,
		'orderby'                => 'menu_order date',
		'order'                  => 'ASC',
		'no_found_rows'          => false,
		'update_post_term_cache' => false,
	] );

	if ( ! $query->have_posts() ) {
		return '<p class="dch-projects-empty">No projects to show yet.</p>';
	}

	$out = '<div class="dch-projects-list">';
	while ( $query->have_posts() ) {
		$query->the_post();
		$out .= dch_fse_projects_render_card( get_post() );
	}
	wp_reset_postdata();
	$out .= '</div>';

	$out .= dch_fse_projects_render_pagination( $paged, (int) $query->max_num_pages );

	return preg_replace( '/>\s+</', '><', $out );
}

/**
 * Register a dynamic block <!-- wp:dch/projects-loop /--> that renders the
 * loop. Using a block (not a shortcode) keeps the output outside of wpautop's
 * paragraph-wrapping path.
 */
add_action( 'init', static function (): void {
	register_block_type( 'dch/projects-loop', [
		'api_version'     => 3,
		'title'           => 'DCH Projects Loop',
		'category'        => 'theme',
		'render_callback' => 'dch_fse_projects_render_loop',
		'supports'        => [ 'html' => false ],
	] );
} );

/**
 * Keep the shortcode form available for any other content area that needs it
 * (e.g. WP admin pages that don't use FSE templates).
 */
add_shortcode( 'dch_projects_loop', 'dch_fse_projects_render_loop' );
