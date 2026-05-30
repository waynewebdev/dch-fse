<?php
/**
 * Single project page (child of /projects/) — header + body + featured image
 * + "More projects" carousel + Contact section. Mirrors the old Infratech
 * project page layout.
 *
 * The page content stored in WP for each project came from the Elementor
 * export and is not block-pure, but it follows a stable HTML pattern:
 *
 *   <h1>Project Overview</h1>
 *   <p>{intro paragraph}</p>
 *   <h4>{subheading}</h4>
 *   <h6>{label1}</h6><h6>{value1}</h6>
 *   <h6>{label2}</h6><h6>{value2}</h6>
 *   ...
 *
 * We parse it once with DOMDocument and feed the extracted parts into our
 * structured layout. Anything we don't recognise (e.g. the legacy "More
 * projects" stub at the bottom) is dropped.
 */

defined( 'ABSPATH' ) || exit;

const DCH_FSE_MORE_PROJECTS_COUNT = 4;

/**
 * Normalize an attachment basename so equivalent variants compare equal:
 * lowercase, no extension, no `-scaled`, no trailing `-N` duplicate suffix,
 * collapsed dashes/underscores. Lets us detect that
 * `foo-scaled.jpg` and `foo-scaled-1.jpg` are the same picture.
 */
function dch_fse_project_norm_basename( string $filename ): string {
	$base = strtolower( basename( $filename ) );
	$base = preg_replace( '/\.[a-z0-9]+$/', '', $base );
	$base = str_replace( '-scaled', '', $base );
	// Strip size variants like `-1024x682` and trailing `-N` duplicate suffix.
	$base = preg_replace( '/-\d+x\d+/', '', $base );
	$base = preg_replace( '/-\d+$/', '', $base );
	$base = preg_replace( '/[-_]+/', '-', $base );
	return trim( (string) $base, '-' );
}

/**
 * Resolve the projects page id (parent of all project child pages).
 */
function dch_fse_project_parent_id(): int {
	if ( function_exists( 'dch_fse_projects_parent_id' ) ) {
		return dch_fse_projects_parent_id();
	}
	$page = get_page_by_path( 'projects' );
	return $page ? (int) $page->ID : 0;
}

/**
 * Decide whether the current request is a child page of /projects/.
 */
function dch_fse_is_project_page(): bool {
	if ( ! is_singular( 'page' ) ) {
		return false;
	}
	$post = get_queried_object();
	if ( ! $post || 'page' !== $post->post_type ) {
		return false;
	}
	$parent_id = dch_fse_project_parent_id();
	return $parent_id && (int) $post->post_parent === $parent_id;
}

/**
 * Tell WordPress to look for templates/page-projects-child.html before the
 * default page.html. This plugs cleanly into the FSE template hierarchy, so
 * the block renderer (header part, footer part, theme styles) keeps working
 * without a custom shim.
 */
add_filter( 'page_template_hierarchy', static function ( array $templates ): array {
	if ( ! dch_fse_is_project_page() ) {
		return $templates;
	}
	array_unshift( $templates, 'page-projects-child' );
	return $templates;
} );

/**
 * Parse a project page's post_content into structured parts.
 *
 * @return array{intro: string, subheading: string, meta: array<int, array{label: string, value: string}>, body_extras: string}
 */
function dch_fse_project_parse_content( string $html ): array {
	$out = [
		'intro'       => '',
		'subheading'  => '',
		'meta'        => [],
		'body_extras' => '',
	];

	if ( '' === trim( $html ) ) {
		return $out;
	}

	$dom = new DOMDocument();
	libxml_use_internal_errors( true );
	$dom->loadHTML( '<?xml encoding="utf-8" ?><div id="dch-root">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
	libxml_clear_errors();

	$root = $dom->getElementById( 'dch-root' );
	if ( ! $root ) {
		return $out;
	}

	$h6_buffer = [];
	$saw_h1    = false;

	foreach ( iterator_to_array( $root->childNodes ) as $node ) {
		if ( ! $node instanceof DOMElement ) {
			continue;
		}
		$tag = strtolower( $node->tagName );

		if ( 'h1' === $tag ) {
			$saw_h1 = true;
			continue;
		}
		if ( 'p' === $tag && '' === $out['intro'] ) {
			$out['intro'] = trim( $node->textContent );
			continue;
		}
		if ( 'h4' === $tag && '' === $out['subheading'] ) {
			$out['subheading'] = trim( $node->textContent );
			continue;
		}
		if ( 'h6' === $tag ) {
			$h6_buffer[] = trim( $node->textContent );
			continue;
		}
		// Stop at the legacy "More projects" stub or any unrecognised block.
		break;
	}

	// Pair H6s into label/value entries.
	for ( $i = 0; $i + 1 < count( $h6_buffer ); $i += 2 ) {
		$out['meta'][] = [
			'label' => $h6_buffer[ $i ],
			'value' => $h6_buffer[ $i + 1 ],
		];
	}

	return $out;
}

/**
 * Render the project header + body + featured image. Used as the
 * `dch/project-body` block in templates/page-projects-child.html.
 */
function dch_fse_project_render_body(): string {
	$post = get_queried_object();
	if ( ! $post || 'page' !== $post->post_type ) {
		return '';
	}

	$parts    = dch_fse_project_parse_content( (string) $post->post_content );
	$page_title = get_the_title( $post );
	$subtitle = (string) get_post_meta( $post->ID, '_dch_project_subtitle', true );
	$thumb    = get_the_post_thumbnail(
		$post,
		'large',
		[
			'class'         => 'dch-project-single__featured-img',
			'sizes'         => '(max-width: 1023px) 100vw, 1290px',
			'fetchpriority' => 'high',
			'decoding'      => 'async',
		]
	);

	ob_start();
	?>
	<section class="dch-page-intro">
		<div class="dch-page-intro__inner">
			<p class="dch-page-intro__eyebrow" data-dch-anim="block">Featured project</p>
			<h1 class="dch-page-intro__title" data-dch-anim="block"><?php echo esc_html( $page_title ); ?></h1>
			<?php if ( '' !== $subtitle ) : ?>
				<p class="dch-page-intro__lede" data-dch-anim="block"><?php echo esc_html( $subtitle ); ?></p>
			<?php endif; ?>
		</div>
	</section>

	<section class="dch-project-single" data-dch-anim="block">
		<div class="dch-project-single__inner">

			<header class="dch-project-single__header">
				<h2 class="dch-project-single__title">Project Overview</h2>
				<?php if ( '' !== $parts['intro'] ) : ?>
					<p class="dch-project-single__intro"><?php echo esc_html( $parts['intro'] ); ?></p>
				<?php endif; ?>
			</header>

			<?php if ( '' !== $parts['subheading'] || ! empty( $parts['meta'] ) ) : ?>
				<div class="dch-project-single__body">
					<?php if ( '' !== $parts['subheading'] ) : ?>
						<h3 class="dch-project-single__subheading"><?php echo esc_html( $parts['subheading'] ); ?></h3>
					<?php endif; ?>

					<?php if ( ! empty( $parts['meta'] ) ) : ?>
						<dl class="dch-project-single__meta">
							<?php foreach ( $parts['meta'] as $row ) : ?>
								<div class="dch-project-single__meta-row">
									<dt class="dch-project-single__meta-label"><?php echo esc_html( $row['label'] ); ?></dt>
									<dd class="dch-project-single__meta-value"><?php echo esc_html( $row['value'] ); ?></dd>
								</div>
							<?php endforeach; ?>
						</dl>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<?php if ( $thumb ) : ?>
				<figure class="dch-project-single__featured">
					<?php echo $thumb; ?>
				</figure>
			<?php endif; ?>

		</div>
	</section>
	<?php
	return preg_replace( '/>\s+</', '><', (string) ob_get_clean() );
}

/**
 * Render the "More projects" section: 4 random sibling project pages styled
 * as the front-page accordion (`.dch-projects__panel`).
 */
function dch_fse_more_projects_render(): string {
	$parent_id = dch_fse_project_parent_id();
	if ( ! $parent_id ) {
		return '';
	}
	$current_id = (int) get_the_ID();

	$query = new WP_Query( [
		'post_type'              => 'page',
		'post_parent'            => $parent_id,
		'post_status'            => 'publish',
		'posts_per_page'         => DCH_FSE_MORE_PROJECTS_COUNT,
		'post__not_in'           => $current_id ? [ $current_id ] : [],
		'orderby'                => 'rand',
		'no_found_rows'          => true,
		'update_post_term_cache' => false,
	] );

	if ( ! $query->have_posts() ) {
		return '';
	}

	ob_start();
	?>
	<section class="dch-more-projects">
		<div class="dch-more-projects__inner">

			<header class="dch-more-projects__head">
				<p class="dch-more-projects__eyebrow" data-dch-anim="block">More projects</p>
			</header>

			<div class="dch-more-projects__grid" data-dch-anim="block">
				<?php
				while ( $query->have_posts() ) {
					$query->the_post();
					$pid       = (int) get_the_ID();
					$permalink = get_permalink();
					$title     = get_the_title();
					$thumb_url = get_the_post_thumbnail_url( $pid, 'large' );
					$style     = $thumb_url ? "background-image: url('" . esc_url( $thumb_url ) . "');" : '';
					$subtitle  = (string) get_post_meta( $pid, '_dch_project_subtitle', true );
					?>
					<a class="dch-more-projects__panel" href="<?php echo esc_url( $permalink ); ?>">
						<div class="dch-more-projects__media" style="<?php echo esc_attr( $style ); ?>"></div>
						<div class="dch-more-projects__caption">
							<h3 class="dch-more-projects__title"><?php echo esc_html( $title ); ?></h3>
							<?php if ( '' !== $subtitle ) : ?>
								<p class="dch-more-projects__sub"><?php echo esc_html( $subtitle ); ?></p>
							<?php endif; ?>
						</div>
					</a>
					<?php
				}
				wp_reset_postdata();
				?>
			</div>

			<div class="dch-more-projects__cta-wrap" data-dch-anim="block">
				<a class="dch-more-projects__cta" href="/projects/">View All Projects</a>
			</div>

		</div>
	</section>
	<?php
	return preg_replace( '/>\s+</', '><', (string) ob_get_clean() );
}

/**
 * Render a `.dch-gallery` carousel for the current project page, populated
 * with every image associated with that project in the media library.
 *
 * "Associated" means, in priority order:
 *   1. attachments whose post_parent is the project page,
 *   2. the featured image (always included — first slide if present),
 *   3. attachments uploaded in the same /YYYY/MM/ folder as the featured
 *      image whose filename slug overlaps with the project slug.
 *
 * Returns an empty string when there are zero images, so the section drops
 * out of the layout cleanly on data-poor projects.
 */
function dch_fse_project_render_gallery(): string {
	$post = get_queried_object();
	if ( ! $post || 'page' !== $post->post_type ) {
		return '';
	}

	// Featured image is excluded — it already appears at the top of the page.
	// We exclude both by attachment ID and by *normalized basename* so that
	// duplicate attachments (e.g. `foo-scaled.jpg` and `foo-scaled-1.jpg`,
	// same picture, different IDs) don't sneak past the ID check.
	$featured_id   = (int) get_post_thumbnail_id( $post );
	$featured_norm = '';
	if ( $featured_id ) {
		$featured_file = (string) get_post_meta( $featured_id, '_wp_attached_file', true );
		if ( '' !== $featured_file ) {
			$featured_norm = dch_fse_project_norm_basename( $featured_file );
		}
	}

	$attachment_ids = [];

	// Pull every image attachment whose post_parent is the project page.
	$children = get_children( [
		'post_parent'    => $post->ID,
		'post_type'      => 'attachment',
		'post_mime_type' => 'image',
		'numberposts'    => -1,
		'orderby'        => 'menu_order ID',
		'order'          => 'ASC',
		'fields'         => 'ids',
	] );
	foreach ( (array) $children as $cid ) {
		$cid = (int) $cid;
		if ( $cid === $featured_id ) {
			continue;
		}
		if ( '' !== $featured_norm ) {
			$file = (string) get_post_meta( $cid, '_wp_attached_file', true );
			if ( '' !== $file && dch_fse_project_norm_basename( $file ) === $featured_norm ) {
				continue;
			}
		}
		$attachment_ids[ $cid ] = true;
	}

	$ids = array_keys( $attachment_ids );
	if ( empty( $ids ) ) {
		return '';
	}

	ob_start();
	?>
	<section class="dch-gallery" data-dch-gallery data-slide-w="844" data-gap="15" data-speed="600">
		<div class="dch-gallery__viewport" data-dch-gallery-viewport>
			<div class="dch-gallery__track" data-dch-gallery-track>
				<?php
				foreach ( $ids as $aid ) {
					$alt = (string) get_post_meta( $aid, '_wp_attachment_image_alt', true );
					if ( '' === $alt ) {
						$alt = get_the_title( $post );
					}
					$img = wp_get_attachment_image(
						$aid,
						'large',
						false,
						[
							'class'    => 'dch-gallery__img',
							'alt'      => $alt,
							'loading'  => 'lazy',
							'decoding' => 'async',
							'width'    => 844,
							'height'   => 563,
						]
					);
					if ( $img ) {
						echo '<figure class="dch-gallery__slide">' . $img . '</figure>';
					}
				}
				?>
			</div>
		</div>
		<?php if ( count( $ids ) > 1 ) : ?>
			<div class="dch-gallery__controls">
				<button type="button" class="dch-gallery__btn dch-gallery__prev" data-dch-gallery-prev aria-label="Previous slide">
					<svg viewBox="0 0 42 26" aria-hidden="true">
						<line x1="40" y1="13" x2="2" y2="13" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
						<polyline points="12,3 2,13 12,23" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
				</button>
				<button type="button" class="dch-gallery__btn dch-gallery__next" data-dch-gallery-next aria-label="Next slide">
					<svg viewBox="0 0 42 26" aria-hidden="true">
						<line x1="2" y1="13" x2="40" y2="13" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
						<polyline points="30,3 40,13 30,23" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
				</button>
			</div>
		<?php endif; ?>
	</section>
	<?php
	return preg_replace( '/>\s+</', '><', (string) ob_get_clean() );
}

add_action( 'init', static function (): void {
	register_block_type( 'dch/project-body', [
		'api_version'     => 3,
		'title'           => 'DCH Project Body',
		'category'        => 'theme',
		'render_callback' => 'dch_fse_project_render_body',
		'supports'        => [ 'html' => false ],
	] );

	register_block_type( 'dch/project-gallery', [
		'api_version'     => 3,
		'title'           => 'DCH Project Gallery',
		'category'        => 'theme',
		'render_callback' => 'dch_fse_project_render_gallery',
		'supports'        => [ 'html' => false ],
	] );

	register_block_type( 'dch/more-projects', [
		'api_version'     => 3,
		'title'           => 'DCH More Projects',
		'category'        => 'theme',
		'render_callback' => 'dch_fse_more_projects_render',
		'supports'        => [ 'html' => false ],
	] );
} );
