<?php
/**
 * File-based page authoring system. Pages live as PHP files under
 * /content/pages/ and are synced into wp_posts (post_type=page) via
 * `wp dch sync`. This file is also the home of the dch_fse_site() helper
 * because /content/site.php is the closest companion config.
 *
 * ---------------------------------------------------------------------------
 *  Page schema  (returned by each /content/pages/<slug>.php file)
 * ---------------------------------------------------------------------------
 *  slug          (string,  required) URL slug + registry key.
 *  title         (string,  required) Page title.
 *  blocks        (string|callable, required) Serialized block markup, or a
 *                                            callable returning one. Callables
 *                                            let pages build repeating sections
 *                                            with PHP loops.
 *  updated_at    (string,  required) ISO 8601 timestamp. Bump to trigger
 *                                    re-sync. Compared against post_modified;
 *                                    admin edits set post_modified, so they
 *                                    are preserved until updated_at moves.
 *  excerpt       (string,  optional) Used as post_excerpt and SEO fallback.
 *  parent        (string,  optional) Parent page slug. Resolved in pass 2.
 *  order         (int,     optional) menu_order.
 *  status        (string,  optional) publish | draft. Default: publish.
 *  is_front_page (bool,    optional) If true, set as show_on_front=page.
 *  seo           (array,   optional) Stored as _dch_page_seo (json-encoded).
 *      title         (string)
 *      description   (string)
 *      og_image      (string, theme-relative path or absolute URL)
 *      lcp_image     (string, theme-relative path or absolute URL)
 *      robots        (string, e.g. "index,follow")
 *      schema        (array,  e.g. ['FAQPage' => [...]])
 *
 * ---------------------------------------------------------------------------
 *  Navigation schema (returned by each /content/navigation/<name>.php file)
 * ---------------------------------------------------------------------------
 *  An ordered array of items. Each item:
 *      label    (string, required)
 *      url      (string, required) absolute path or full URL
 *      children (array,  optional) nested items for dropdowns
 */

defined( 'ABSPATH' ) || exit;

const DCH_FSE_PAGE_SLUG_META    = '_dch_page_slug';
const DCH_FSE_PAGE_SEO_META     = '_dch_page_seo';
const DCH_FSE_PAGE_FRONT_META   = '_dch_page_is_front';
const DCH_FSE_NAV_SLUG_META     = '_dch_nav_slug';

/* -------------------------------------------------------------------------
 *  Site config helper
 * ------------------------------------------------------------------------- */

/**
 * Load and cache /content/site.php. Returns the full array, or one key, or
 * null if the key is missing.
 *
 * @return mixed
 */
function dch_fse_site( ?string $key = null ) {
	static $cache = null;

	if ( null === $cache ) {
		$path  = get_template_directory() . '/content/site.php';
		$cache = file_exists( $path ) ? require $path : [];
		if ( ! is_array( $cache ) ) {
			$cache = [];
		}
	}

	if ( null === $key ) {
		return $cache;
	}

	return $cache[ $key ] ?? null;
}

/* -------------------------------------------------------------------------
 *  Page loading + validation
 * ------------------------------------------------------------------------- */

/**
 * Scan /content/pages/*.php and return an array keyed by slug.
 * Malformed files are skipped with a logged error in the result array.
 *
 * @return array{0: array<string, array<string, mixed>>, 1: array<string>}
 *         [0] => pages keyed by slug, [1] => collected error messages.
 */
function dch_fse_load_page_files(): array {
	$dir    = get_template_directory() . '/content/pages';
	$pages  = [];
	$errors = [];

	if ( ! is_dir( $dir ) ) {
		return [ $pages, $errors ];
	}

	$files = glob( $dir . '/*.php' ) ?: [];
	foreach ( $files as $file ) {
		if ( '.gitkeep' === basename( $file ) ) {
			continue;
		}

		try {
			$page = include $file;
		} catch ( \Throwable $e ) {
			$errors[] = sprintf( '%s: include threw — %s', basename( $file ), $e->getMessage() );
			continue;
		}

		if ( ! is_array( $page ) ) {
			$errors[] = sprintf( '%s: file must return an array', basename( $file ) );
			continue;
		}

		foreach ( [ 'slug', 'title', 'blocks', 'updated_at' ] as $required ) {
			if ( empty( $page[ $required ] ) ) {
				$errors[] = sprintf( '%s: missing required field "%s"', basename( $file ), $required );
				continue 2;
			}
		}

		$pages[ $page['slug'] ] = $page;
	}

	return [ $pages, $errors ];
}

/* -------------------------------------------------------------------------
 *  Page sync
 * ------------------------------------------------------------------------- */

/**
 * Sync the file-based page registry into wp_posts.
 *
 * @param array{dry-run?: bool, prune?: bool} $opts
 * @return array{created: array<string>, updated: array<string>, skipped: array<string>, pruned: array<string>, errors: array<string>}
 */
function dch_fse_sync_pages( array $opts = [] ): array {
	$dry_run = ! empty( $opts['dry-run'] );
	$prune   = ! empty( $opts['prune'] );

	$result = [
		'created' => [],
		'updated' => [],
		'skipped' => [],
		'pruned'  => [],
		'errors'  => [],
	];

	[ $pages, $load_errors ] = dch_fse_load_page_files();
	$result['errors']        = array_merge( $result['errors'], $load_errors );

	// Pass 1: create or update each page (without resolving parent).
	$post_ids_by_slug   = [];
	$pending_parents    = [];
	$pending_front_page = null;

	foreach ( $pages as $slug => $page ) {
		$resolved_blocks = $page['blocks'];
		if ( is_callable( $resolved_blocks ) ) {
			try {
				$resolved_blocks = (string) $resolved_blocks();
			} catch ( \Throwable $e ) {
				$result['errors'][] = sprintf( '%s: blocks callable threw — %s', $slug, $e->getMessage() );
				continue;
			}
		}

		if ( ! is_string( $resolved_blocks ) ) {
			$result['errors'][] = sprintf( '%s: blocks must be a string or callable returning a string', $slug );
			continue;
		}

		$existing_id = dch_fse_find_page_by_slug( $slug );

		if ( 0 === $existing_id ) {
			// Create.
			if ( $dry_run ) {
				$result['created'][] = $slug;
				$post_ids_by_slug[ $slug ] = 0;
			} else {
				$insert_id = wp_insert_post( [
					'post_type'    => 'page',
					'post_status'  => $page['status'] ?? 'publish',
					'post_title'   => (string) $page['title'],
					'post_name'    => $slug,
					'post_content' => $resolved_blocks,
					'post_excerpt' => (string) ( $page['excerpt'] ?? '' ),
					'menu_order'   => (int) ( $page['order'] ?? 0 ),
				], true );

				if ( is_wp_error( $insert_id ) ) {
					$result['errors'][] = sprintf( '%s: %s', $slug, $insert_id->get_error_message() );
					continue;
				}

				update_post_meta( $insert_id, DCH_FSE_PAGE_SLUG_META, $slug );
				dch_fse_apply_page_seo( $insert_id, $page['seo'] ?? null );

				$result['created'][] = $slug;
				$post_ids_by_slug[ $slug ] = $insert_id;
			}
		} else {
			$post = get_post( $existing_id );
			if ( ! $post || 'page' !== $post->post_type ) {
				$result['errors'][] = sprintf( '%s: existing post #%d is not a page (guard tripped)', $slug, $existing_id );
				continue;
			}

			$updated_at_ts = strtotime( (string) $page['updated_at'] );
			$modified_ts   = strtotime( $post->post_modified_gmt . ' UTC' );

			if ( $updated_at_ts === false ) {
				$result['errors'][] = sprintf( '%s: invalid updated_at "%s"', $slug, $page['updated_at'] );
				continue;
			}

			if ( $updated_at_ts <= $modified_ts ) {
				$result['skipped'][] = $slug;
				$post_ids_by_slug[ $slug ] = $existing_id;
				continue;
			}

			if ( $dry_run ) {
				$result['updated'][] = $slug;
				$post_ids_by_slug[ $slug ] = $existing_id;
				continue;
			}

			$updated = wp_update_post( [
				'ID'           => $existing_id,
				'post_title'   => (string) $page['title'],
				'post_content' => $resolved_blocks,
				'post_excerpt' => (string) ( $page['excerpt'] ?? '' ),
				'menu_order'   => (int) ( $page['order'] ?? 0 ),
				'post_status'  => $page['status'] ?? 'publish',
			], true );

			if ( is_wp_error( $updated ) ) {
				$result['errors'][] = sprintf( '%s: %s', $slug, $updated->get_error_message() );
				continue;
			}

			dch_fse_apply_page_seo( $existing_id, $page['seo'] ?? null );

			$result['updated'][] = $slug;
			$post_ids_by_slug[ $slug ] = $existing_id;
		}

		// Defer parent + front-page assignment to pass 2.
		if ( ! empty( $page['parent'] ) ) {
			$pending_parents[ $slug ] = (string) $page['parent'];
		}
		if ( ! empty( $page['is_front_page'] ) && null === $pending_front_page ) {
			$pending_front_page = $slug;
		}
	}

	// Pass 2: resolve parent relationships and front-page assignment.
	if ( ! $dry_run ) {
		foreach ( $pending_parents as $child_slug => $parent_slug ) {
			$child_id  = $post_ids_by_slug[ $child_slug ] ?? 0;
			$parent_id = $post_ids_by_slug[ $parent_slug ] ?? dch_fse_find_page_by_slug( $parent_slug );
			if ( $child_id && $parent_id ) {
				wp_update_post( [ 'ID' => $child_id, 'post_parent' => $parent_id ] );
			} elseif ( $child_id ) {
				$result['errors'][] = sprintf( '%s: parent "%s" not found', $child_slug, $parent_slug );
			}
		}

		if ( null !== $pending_front_page ) {
			$front_id = $post_ids_by_slug[ $pending_front_page ] ?? 0;
			if ( $front_id ) {
				update_option( 'show_on_front', 'page' );
				update_option( 'page_on_front', $front_id );
				// Clear stale flags on other pages.
				$marked = get_posts( [
					'post_type'      => 'page',
					'post_status'    => 'any',
					'meta_key'       => DCH_FSE_PAGE_FRONT_META,
					'meta_value'     => '1',
					'fields'         => 'ids',
					'posts_per_page' => -1,
				] );
				foreach ( $marked as $mid ) {
					if ( (int) $mid !== (int) $front_id ) {
						delete_post_meta( $mid, DCH_FSE_PAGE_FRONT_META );
					}
				}
				update_post_meta( $front_id, DCH_FSE_PAGE_FRONT_META, '1' );
			}
		}
	}

	// Optional prune step.
	if ( $prune ) {
		$known_slugs = array_keys( $pages );
		$candidates  = get_posts( [
			'post_type'      => 'page',
			'post_status'    => 'any',
			'meta_key'       => DCH_FSE_PAGE_SLUG_META,
			'fields'         => 'ids',
			'posts_per_page' => -1,
		] );
		foreach ( $candidates as $candidate_id ) {
			$candidate_slug = (string) get_post_meta( $candidate_id, DCH_FSE_PAGE_SLUG_META, true );
			if ( in_array( $candidate_slug, $known_slugs, true ) ) {
				continue;
			}
			$post = get_post( $candidate_id );
			if ( ! $post || 'page' !== $post->post_type ) {
				continue; // Hard guard: never delete posts.
			}
			if ( $dry_run ) {
				$result['pruned'][] = $candidate_slug;
				continue;
			}
			wp_delete_post( $candidate_id, true );
			$result['pruned'][] = $candidate_slug;
		}
	}

	if ( ! $dry_run ) {
		do_action( 'dch_fse_pages_synced', $result );
	}

	return $result;
}

/**
 * Look up a page post by its `_dch_page_slug` meta. Returns 0 if none found.
 */
function dch_fse_find_page_by_slug( string $slug ): int {
	$ids = get_posts( [
		'post_type'      => 'page',
		'post_status'    => 'any',
		'meta_key'       => DCH_FSE_PAGE_SLUG_META,
		'meta_value'     => $slug,
		'fields'         => 'ids',
		'posts_per_page' => 1,
	] );
	return (int) ( $ids[0] ?? 0 );
}

/**
 * Persist or clear the SEO meta blob for a page.
 *
 * @param mixed $seo
 */
function dch_fse_apply_page_seo( int $post_id, $seo ): void {
	if ( is_array( $seo ) && ! empty( $seo ) ) {
		update_post_meta( $post_id, DCH_FSE_PAGE_SEO_META, wp_slash( wp_json_encode( $seo ) ) );
	} else {
		delete_post_meta( $post_id, DCH_FSE_PAGE_SEO_META );
	}
}

/**
 * Read a page's stored SEO array. Returns [] if none.
 */
function dch_fse_get_page_seo( int $post_id ): array {
	$raw = get_post_meta( $post_id, DCH_FSE_PAGE_SEO_META, true );
	if ( ! is_string( $raw ) || '' === $raw ) {
		return [];
	}
	$decoded = json_decode( $raw, true );
	return is_array( $decoded ) ? $decoded : [];
}

/* -------------------------------------------------------------------------
 *  Navigation sync
 * ------------------------------------------------------------------------- */

/**
 * Sync /content/navigation/*.php files into wp_navigation posts.
 *
 * @param array{dry-run?: bool} $opts
 * @return array{created: array<string>, updated: array<string>, skipped: array<string>, errors: array<string>}
 */
function dch_fse_sync_navigation( array $opts = [] ): array {
	$dry_run = ! empty( $opts['dry-run'] );
	$result  = [
		'created' => [],
		'updated' => [],
		'skipped' => [],
		'errors'  => [],
	];

	$dir = get_template_directory() . '/content/navigation';
	if ( ! is_dir( $dir ) ) {
		return $result;
	}

	$files = glob( $dir . '/*.php' ) ?: [];
	foreach ( $files as $file ) {
		$name = basename( $file, '.php' );
		if ( '.gitkeep' === $name ) {
			continue;
		}

		try {
			$items = include $file;
		} catch ( \Throwable $e ) {
			$result['errors'][] = sprintf( 'navigation/%s: include threw — %s', $name, $e->getMessage() );
			continue;
		}

		if ( ! is_array( $items ) ) {
			$result['errors'][] = sprintf( 'navigation/%s: must return an array', $name );
			continue;
		}

		$blocks  = dch_fse_nav_items_to_blocks( $items );
		$title   = ucfirst( $name ) . ' Navigation';
		$post_id = dch_fse_find_navigation_by_slug( $name );

		if ( 0 === $post_id ) {
			if ( $dry_run ) {
				$result['created'][] = $name;
				continue;
			}
			$insert_id = wp_insert_post( [
				'post_type'    => 'wp_navigation',
				'post_status'  => 'publish',
				'post_title'   => $title,
				'post_content' => $blocks,
			], true );
			if ( is_wp_error( $insert_id ) ) {
				$result['errors'][] = sprintf( 'navigation/%s: %s', $name, $insert_id->get_error_message() );
				continue;
			}
			update_post_meta( $insert_id, DCH_FSE_NAV_SLUG_META, $name );
			$result['created'][] = $name;
			continue;
		}

		$existing = get_post( $post_id );
		if ( $existing && trim( $existing->post_content ) === trim( $blocks ) && $existing->post_title === $title ) {
			$result['skipped'][] = $name;
			continue;
		}

		if ( $dry_run ) {
			$result['updated'][] = $name;
			continue;
		}

		$updated = wp_update_post( [
			'ID'           => $post_id,
			'post_title'   => $title,
			'post_content' => $blocks,
		], true );

		if ( is_wp_error( $updated ) ) {
			$result['errors'][] = sprintf( 'navigation/%s: %s', $name, $updated->get_error_message() );
			continue;
		}

		$result['updated'][] = $name;
	}

	return $result;
}

/**
 * Find a wp_navigation post ID by its `_dch_nav_slug` meta. Returns 0 if none.
 */
function dch_fse_find_navigation_by_slug( string $slug ): int {
	$ids = get_posts( [
		'post_type'      => 'wp_navigation',
		'post_status'    => 'any',
		'meta_key'       => DCH_FSE_NAV_SLUG_META,
		'meta_value'     => $slug,
		'fields'         => 'ids',
		'posts_per_page' => 1,
	] );
	return (int) ( $ids[0] ?? 0 );
}

/**
 * Public accessor for templates / patterns that need a navigation post ID.
 */
function dch_fse_get_navigation_id( string $slug ): int {
	return dch_fse_find_navigation_by_slug( $slug );
}

/**
 * Render an array of navigation items to wp_navigation block markup.
 *
 * @param array<int, array{label?: string, url?: string, children?: array}> $items
 */
function dch_fse_nav_items_to_blocks( array $items ): string {
	$out = '';
	foreach ( $items as $item ) {
		$label    = (string) ( $item['label'] ?? '' );
		$url      = (string) ( $item['url'] ?? '' );
		$children = $item['children'] ?? [];

		$attrs = [
			'label' => $label,
			'url'   => $url,
		];
		$encoded_attrs = wp_json_encode( $attrs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );

		if ( is_array( $children ) && ! empty( $children ) ) {
			$inner = dch_fse_nav_items_to_blocks( $children );
			$out  .= sprintf(
				"<!-- wp:navigation-submenu %s -->\n%s<!-- /wp:navigation-submenu -->\n",
				$encoded_attrs,
				$inner
			);
		} else {
			$out .= sprintf( "<!-- wp:navigation-link %s /-->\n", $encoded_attrs );
		}
	}
	return $out;
}

/* -------------------------------------------------------------------------
 *  WP-CLI: `wp dch sync [--dry-run] [--prune]`
 * ------------------------------------------------------------------------- */

if ( defined( 'WP_CLI' ) && WP_CLI ) {

	WP_CLI::add_command( 'dch sync', static function ( array $args, array $assoc ): void {
		$opts = [
			'dry-run' => isset( $assoc['dry-run'] ),
			'prune'   => isset( $assoc['prune'] ),
		];

		$prefix = $opts['dry-run'] ? '[DRY RUN] ' : '';

		WP_CLI::log( $prefix . 'Syncing pages...' );
		$pages = dch_fse_sync_pages( $opts );
		dch_fse_cli_print_section( 'Pages', $pages );

		WP_CLI::log( $prefix . 'Syncing navigation...' );
		$nav = dch_fse_sync_navigation( $opts );
		dch_fse_cli_print_section( 'Navigation', $nav );

		$errors = array_merge( $pages['errors'], $nav['errors'] );
		if ( ! empty( $errors ) ) {
			foreach ( $errors as $err ) {
				WP_CLI::warning( $err );
			}
			WP_CLI::halt( 1 );
		}

		WP_CLI::success( $opts['dry-run'] ? 'Dry run complete.' : 'Sync complete.' );
	}, [
		'shortdesc' => 'Sync the file-based page and navigation registries into WordPress.',
		'synopsis'  => [
			[
				'type'        => 'flag',
				'name'        => 'dry-run',
				'description' => 'Show planned changes without writing to the database.',
				'optional'    => true,
			],
			[
				'type'        => 'flag',
				'name'        => 'prune',
				'description' => 'Delete any registry-managed page no longer in /content/pages/.',
				'optional'    => true,
			],
		],
	] );
}

function dch_fse_cli_print_section( string $label, array $r ): void {
	if ( ! ( defined( 'WP_CLI' ) && WP_CLI ) ) {
		return;
	}

	$summary = sprintf(
		'%s: created=%d updated=%d skipped=%d pruned=%d errors=%d',
		$label,
		count( $r['created'] ?? [] ),
		count( $r['updated'] ?? [] ),
		count( $r['skipped'] ?? [] ),
		count( $r['pruned']  ?? [] ),
		count( $r['errors']  ?? [] )
	);
	WP_CLI::log( '  ' . $summary );

	foreach ( [ 'created', 'updated', 'skipped', 'pruned' ] as $bucket ) {
		if ( ! empty( $r[ $bucket ] ) ) {
			WP_CLI::log( '    ' . $bucket . ': ' . implode( ', ', $r[ $bucket ] ) );
		}
	}
}
