<?php
/**
 * JSON-LD schema builders. Each builder returns an associative array; the
 * collected set is wrapped in @graph and emitted as one <script> in wp_head.
 *
 * Stable @id URIs let entities cross-reference (e.g. WebSite.publisher → Organization).
 */

defined( 'ABSPATH' ) || exit;

/* -------------------------------------------------------------------------
 *  ID helpers
 * ------------------------------------------------------------------------- */

function dch_fse_schema_id( string $fragment ): string {
	$base = (string) ( dch_fse_site( 'url' ) ?: home_url( '/' ) );
	return rtrim( $base, '/' ) . '/#' . ltrim( $fragment, '#' );
}

/* -------------------------------------------------------------------------
 *  Builders
 * ------------------------------------------------------------------------- */

function dch_fse_schema_organization(): array {
	$site = dch_fse_site() ?: [];
	$org  = $site['organization'] ?? [];

	$node = [
		'@type' => 'Organization',
		'@id'   => dch_fse_schema_id( 'organization' ),
		'name'  => (string) ( $org['legal_name'] ?? ( $site['name'] ?? get_bloginfo( 'name' ) ) ),
		'url'   => (string) ( $site['url'] ?? home_url( '/' ) ),
	];

	if ( ! empty( $org['logo'] ) ) {
		$node['logo'] = [
			'@type' => 'ImageObject',
			'@id'   => dch_fse_schema_id( 'logo' ),
			'url'   => dch_fse_resolve_image_url( (string) $org['logo'] ),
		];
		$node['image'] = [ '@id' => dch_fse_schema_id( 'logo' ) ];
	}

	$address = $org['address'] ?? [];
	if ( is_array( $address ) && array_filter( $address ) ) {
		$node['address'] = array_filter( [
			'@type'           => 'PostalAddress',
			'streetAddress'   => $address['street']      ?? null,
			'addressLocality' => $address['locality']    ?? null,
			'addressRegion'   => $address['region']      ?? null,
			'postalCode'      => $address['postal_code'] ?? null,
			'addressCountry'  => $address['country']     ?? null,
		] );
	}

	if ( ! empty( $org['phone'] ) ) {
		$node['telephone'] = (string) $org['phone'];
	}
	if ( ! empty( $org['email'] ) ) {
		$node['email'] = (string) $org['email'];
	}
	if ( ! empty( $org['sameAs'] ) && is_array( $org['sameAs'] ) ) {
		$node['sameAs'] = array_values( array_filter( $org['sameAs'] ) );
	}

	return $node;
}

function dch_fse_schema_website(): array {
	$site = dch_fse_site() ?: [];
	return [
		'@type'         => 'WebSite',
		'@id'           => dch_fse_schema_id( 'website' ),
		'url'           => (string) ( $site['url'] ?? home_url( '/' ) ),
		'name'          => (string) ( $site['name'] ?? get_bloginfo( 'name' ) ),
		'description'   => (string) ( $site['tagline'] ?? get_bloginfo( 'description' ) ),
		'publisher'     => [ '@id' => dch_fse_schema_id( 'organization' ) ],
		'inLanguage'    => str_replace( '_', '-', get_locale() ),
		'potentialAction' => [
			'@type'       => 'SearchAction',
			'target'      => [
				'@type'       => 'EntryPoint',
				'urlTemplate' => home_url( '/?s={search_term_string}' ),
			],
			'query-input' => 'required name=search_term_string',
		],
	];
}

function dch_fse_schema_breadcrumbs(): array {
	$items = [];
	$pos   = 1;

	$items[] = [
		'@type'    => 'ListItem',
		'position' => $pos++,
		'name'     => __( 'Home', 'dch-fse' ),
		'item'     => home_url( '/' ),
	];

	if ( is_singular() ) {
		$post_id = (int) get_the_ID();
		$ancestors = array_reverse( get_post_ancestors( $post_id ) );
		foreach ( $ancestors as $ancestor_id ) {
			$items[] = [
				'@type'    => 'ListItem',
				'position' => $pos++,
				'name'     => get_the_title( $ancestor_id ),
				'item'     => (string) get_permalink( $ancestor_id ),
			];
		}
		$items[] = [
			'@type'    => 'ListItem',
			'position' => $pos++,
			'name'     => get_the_title( $post_id ),
			'item'     => (string) get_permalink( $post_id ),
		];
	} elseif ( is_archive() ) {
		$items[] = [
			'@type'    => 'ListItem',
			'position' => $pos++,
			'name'     => wp_strip_all_tags( get_the_archive_title() ),
			'item'     => (string) get_pagenum_link( 1 ),
		];
	} elseif ( is_search() ) {
		$items[] = [
			'@type'    => 'ListItem',
			'position' => $pos++,
			'name'     => sprintf( __( 'Search: %s', 'dch-fse' ), get_search_query() ),
			'item'     => home_url( '/?s=' . rawurlencode( get_search_query() ) ),
		];
	}

	return [
		'@type'           => 'BreadcrumbList',
		'@id'             => dch_fse_schema_id( 'breadcrumbs' ),
		'itemListElement' => $items,
	];
}

function dch_fse_schema_blogposting( int $post_id ): array {
	$post = get_post( $post_id );
	if ( ! $post ) {
		return [];
	}

	$image_url = dch_fse_post_seo_og_image( $post_id );
	if ( '' !== $image_url ) {
		$image_url = dch_fse_resolve_image_url( $image_url );
	}

	$author = get_userdata( (int) $post->post_author );

	$node = [
		'@type'            => 'BlogPosting',
		'@id'              => (string) get_permalink( $post_id ) . '#blogposting',
		'mainEntityOfPage' => (string) get_permalink( $post_id ),
		'headline'         => get_the_title( $post_id ),
		'datePublished'    => mysql2date( 'c', $post->post_date_gmt, false ),
		'dateModified'     => mysql2date( 'c', $post->post_modified_gmt, false ),
		'author'           => [
			'@type' => 'Person',
			'name'  => $author ? $author->display_name : '',
		],
		'publisher'        => [ '@id' => dch_fse_schema_id( 'organization' ) ],
	];

	if ( '' !== $image_url ) {
		$dims = dch_fse_image_dimensions( $image_url );
		$node['image'] = [
			'@type'  => 'ImageObject',
			'url'    => $image_url,
			'width'  => $dims['width'],
			'height' => $dims['height'],
		];
	}

	$desc = dch_fse_post_seo_description( $post_id );
	if ( '' !== $desc ) {
		$node['description'] = $desc;
	}

	return $node;
}

function dch_fse_schema_faqpage( array $faq ): array {
	$entities = [];
	foreach ( $faq as $item ) {
		$q = (string) ( $item['question'] ?? $item[0] ?? '' );
		$a = (string) ( $item['answer']   ?? $item[1] ?? '' );
		if ( '' === $q || '' === $a ) {
			continue;
		}
		$entities[] = [
			'@type'          => 'Question',
			'name'           => $q,
			'acceptedAnswer' => [
				'@type' => 'Answer',
				'text'  => $a,
			],
		];
	}
	return [
		'@type'      => 'FAQPage',
		'mainEntity' => $entities,
	];
}

/* -------------------------------------------------------------------------
 *  Output
 * ------------------------------------------------------------------------- */

add_action( 'wp_head', 'dch_fse_render_schema', 5 );

function dch_fse_render_schema(): void {
	if ( is_404() ) {
		return;
	}

	$graph = [];

	if ( is_front_page() ) {
		$graph[] = dch_fse_schema_organization();
		$graph[] = dch_fse_schema_website();
	} else {
		// Reference Organization on every other view too — single canonical entity.
		$graph[] = dch_fse_schema_organization();
	}

	if ( ! is_front_page() ) {
		$graph[] = dch_fse_schema_breadcrumbs();
	}

	if ( is_singular( 'post' ) ) {
		$bp = dch_fse_schema_blogposting( (int) get_the_ID() );
		if ( ! empty( $bp ) ) {
			$graph[] = $bp;
		}
	}

	// Page registry can declare extra schemas: 'seo' => ['schema' => ['FAQPage' => [...]]]
	if ( is_singular() && function_exists( 'dch_fse_get_page_seo' ) ) {
		$page_seo = dch_fse_get_page_seo( (int) get_the_ID() );
		$extra    = $page_seo['schema'] ?? [];
		if ( is_array( $extra ) ) {
			foreach ( $extra as $type => $payload ) {
				if ( 'FAQPage' === $type && is_array( $payload ) ) {
					$graph[] = dch_fse_schema_faqpage( $payload );
				}
			}
		}
	}

	$graph = apply_filters( 'dch_fse_schema_graph', $graph );

	if ( empty( $graph ) ) {
		return;
	}

	$payload = [
		'@context' => 'https://schema.org',
		'@graph'   => array_values( $graph ),
	];

	$json = wp_json_encode( $payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
	if ( false === $json ) {
		return;
	}

	echo "<script type=\"application/ld+json\">\n" . $json . "\n</script>\n";
}
