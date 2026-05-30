<?php
/**
 * /areas-we-serve/ listing + per-area child page renderer.
 *
 * Mirrors the same approach as service-child.php — children of a parent page
 * compose from the shared dch- section vocabulary, with the unique middle
 * (communities pill list, "why build here" split) selected per slug.
 *
 * The listing page itself gets its own renderer that produces a richer area
 * card grid than the small 4-card grid on the front page — bigger photos,
 * a subtitle listing the top neighborhoods, and a CTA arrow per card.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Resolve the /areas-we-serve/ parent page id.
 */
function dch_fse_areas_parent_id(): int {
	static $cached = null;
	if ( null !== $cached ) {
		return $cached;
	}
	$page   = get_page_by_path( 'areas-we-serve' );
	$cached = $page ? (int) $page->ID : 0;
	return $cached;
}

/**
 * Detect whether the current request IS the /areas-we-serve/ listing page.
 */
function dch_fse_is_areas_listing(): bool {
	if ( ! is_singular( 'page' ) ) {
		return false;
	}
	$post = get_queried_object();
	if ( ! $post || 'page' !== $post->post_type ) {
		return false;
	}
	return 'areas-we-serve' === $post->post_name;
}

/**
 * Detect whether the current request is a child page of /areas-we-serve/.
 */
function dch_fse_is_area_child(): bool {
	if ( ! is_singular( 'page' ) ) {
		return false;
	}
	$post = get_queried_object();
	if ( ! $post || 'page' !== $post->post_type ) {
		return false;
	}
	$parent_id = dch_fse_areas_parent_id();
	return $parent_id && (int) $post->post_parent === $parent_id;
}

/**
 * Route the listing page and its children through dedicated FSE templates.
 */
add_filter( 'page_template_hierarchy', static function ( array $templates ): array {
	if ( dch_fse_is_areas_listing() ) {
		array_unshift( $templates, 'page-areas-we-serve' );
		return $templates;
	}
	if ( dch_fse_is_area_child() ) {
		array_unshift( $templates, 'page-areas-child' );
		return $templates;
	}
	return $templates;
} );

/**
 * Per-slug content map for the area card grid + each child page.
 *
 * card_subtitle  — top communities listed under the area-card title
 * eyebrow / title / lede  — hero copy on the child page
 * image / image_alt       — hero photo
 * communities             — pill list shown after the hero
 * communities_eyebrow / heading / lede  — header for the pill list
 * split                   — 2-col "why build here" copy + photo
 */
function dch_fse_area_data(): array {
	$theme_img = '/wp-content/themes/dch-fse/assets/images/';

	return [
		'san-antonio' => [
			'name'          => 'San Antonio',
			'card_subtitle' => 'The Dominion &middot; Shavano Park &middot; Stone Oak',
			'card_image'    => $theme_img . 'area-san-antonio.jpg',
			'eyebrow'       => 'San Antonio, Texas',
			'title'         => 'Custom Home Builder in San Antonio, Texas',
			'lede'          => 'Dynamic Custom Homes is San Antonio&rsquo;s trusted custom home builder with over 17 years of experience building homes across the city&rsquo;s most desirable neighborhoods. Owner Joshua Maas is a third-generation San Antonio builder who treats every project like his own home.',
			'image'         => $theme_img . 'area-san-antonio.jpg',
			'image_alt'     => 'Custom home in San Antonio by Dynamic Custom Homes',
			'communities'   => [
				'eyebrow' => 'San Antonio Neighborhoods',
				'heading' => 'Building in San Antonio&rsquo;s most desirable communities',
				'lede'    => 'We build custom homes throughout San Antonio in neighborhoods with their own unique lot characteristics, HOA requirements, and architectural styles &mdash; and we know each one intimately.',
				'items'   => [ 'The Dominion', 'Shavano Park', 'Stone Oak', 'Helotes', 'Alamo Heights', 'Terrell Hills' ],
			],
			'split' => [
				'eyebrow'   => 'Why San Antonio',
				'heading'   => 'Why build a custom home in San Antonio?',
				'body'      => '<p>San Antonio offers an exceptional quality of life with a growing economy, rich culture, and beautiful Hill Country proximity. Custom home building lets you choose the perfect lot and design a home that fits your family&rsquo;s lifestyle.</p><p>From open floor plans for entertaining to private outdoor living spaces that take advantage of the Texas climate, your home is shaped to how you actually live.</p>',
				'image'     => $theme_img . 'project-8815-terra-mont-way.jpg',
				'image_alt' => 'San Antonio custom home built by Dynamic Custom Homes',
			],
		],

		'boerne' => [
			'name'          => 'Boerne',
			'card_subtitle' => 'Cordillera Ranch &middot; Fair Oaks Ranch &middot; Tapatio Springs',
			'card_image'    => $theme_img . 'area-boerne.jpg',
			'eyebrow'       => 'Boerne, Texas',
			'title'         => 'Custom Home Builder in Boerne, Texas',
			'lede'          => 'Build your custom dream home in Boerne, TX with Dynamic Custom Homes. As an award-winning Hill Country builder with over 17 years of experience, we specialize in homes that complement Boerne&rsquo;s natural beauty and small-town charm while delivering modern luxury and comfort.',
			'image'         => $theme_img . 'area-boerne.jpg',
			'image_alt'     => 'Custom home in Boerne, TX by Dynamic Custom Homes',
			'communities'   => [
				'eyebrow' => 'Boerne &amp; Hill Country',
				'heading' => 'Boerne communities we serve',
				'lede'    => 'We build custom homes throughout Boerne and the surrounding Kendall County area &mdash; on terrain that ranges from oak-shaded acreage to elevated Hill Country lots with long views.',
				'items'   => [ 'Cordillera Ranch', 'Fair Oaks Ranch', 'Balcones Creek', 'Tapatio Springs', 'Kendall County' ],
			],
			'split' => [
				'eyebrow'   => 'Why Boerne',
				'heading'   => 'Building in Boerne &amp; the Hill Country',
				'body'      => '<p>Boerne is one of the most sought-after communities in the Texas Hill Country, known for its scenic landscapes, excellent schools, and vibrant downtown.</p><p>Building a custom home here means working with varied terrain, mature oak trees, and stunning Hill Country views &mdash; all of which require an experienced builder who understands the land.</p>',
				'image'     => $theme_img . 'project-graystone-circle.jpg',
				'image_alt' => 'Hill Country custom home in Boerne by Dynamic Custom Homes',
			],
		],

		'new-braunfels' => [
			'name'          => 'New Braunfels',
			'card_subtitle' => 'Gruene &middot; River Road &middot; Canyon Lake',
			'card_image'    => $theme_img . 'area-new-braunfels.jpg',
			'eyebrow'       => 'New Braunfels, Texas',
			'title'         => 'Custom Home Builder in New Braunfels, Texas',
			'lede'          => 'Dynamic Custom Homes builds custom homes in New Braunfels, TX and the surrounding Comal County area. With over 17 years of building experience in Central Texas, we understand the unique appeal and building requirements of this rapidly growing community.',
			'image'         => $theme_img . 'area-new-braunfels.jpg',
			'image_alt'     => 'Custom home in New Braunfels by Dynamic Custom Homes',
			'communities'   => [
				'eyebrow' => 'New Braunfels Areas',
				'heading' => 'Where we build in Comal County',
				'lede'    => 'From historic Gruene to riverfront lots along the Comal and Guadalupe, we build across the New Braunfels footprint &mdash; including the master-planned communities ringing Canyon Lake.',
				'items'   => [ 'Gruene', 'River Road', 'Vintage Oaks', 'Canyon Lake', 'Comal County' ],
			],
			'split' => [
				'eyebrow'   => 'Why New Braunfels',
				'heading'   => 'New Braunfels living, custom-built',
				'body'      => '<p>New Braunfels combines Texas Hill Country beauty with small-town charm and easy access to both San Antonio and Austin. The Comal and Guadalupe rivers, historic Gruene district, and family-friendly communities make it one of the fastest-growing cities in Texas.</p><p>Building a custom home here means designing for the river-and-Hill-Country lifestyle &mdash; outdoor living, big windows, and floor plans that flex for both quiet evenings and weekend visitors.</p>',
				'image'     => $theme_img . 'project-southwick-ranch.jpg',
				'image_alt' => 'New Braunfels custom home by Dynamic Custom Homes',
			],
		],

		'hill-country' => [
			'name'          => 'Texas Hill Country',
			'card_subtitle' => 'Wimberley &middot; Dripping Springs &middot; Fredericksburg',
			'card_image'    => $theme_img . 'area-hill-country.jpg',
			'eyebrow'       => 'Texas Hill Country',
			'title'         => 'Hill Country Custom Home Builder',
			'lede'          => 'Dynamic Custom Homes is your trusted custom home builder in the Texas Hill Country. With over 17 years of experience building across this unique region, we understand the terrain, climate, and lifestyle that make Hill Country living special.',
			'image'         => $theme_img . 'area-hill-country.jpg',
			'image_alt'     => 'Hill Country custom home by Dynamic Custom Homes',
			'communities'   => [
				'eyebrow' => 'Hill Country Communities',
				'heading' => 'Where we build across the Hill Country',
				'lede'    => 'From the lakes of Wimberley to the wineries of Fredericksburg, we build across the full Hill Country footprint &mdash; on lots that demand builders who know rock foundations, slope management, and view-driven design.',
				'items'   => [ 'Wimberley', 'Dripping Springs', 'Fredericksburg', 'Kerrville', 'Bandera', 'Hill Country' ],
			],
			'split' => [
				'eyebrow'   => 'Why the Hill Country',
				'heading'   => 'Building for Hill Country living',
				'body'      => '<p>Hill Country homes demand builders who understand sloped terrain, rock foundations, native landscaping, and designs that maximize views while managing the Texas climate.</p><p>From sprawling ranch-style homes to modern Hill Country estates, we build homes that belong in this landscape &mdash; and last on it.</p>',
				'image'     => $theme_img . 'project-8634-terra-mont-way.jpg',
				'image_alt' => 'Hill Country estate built by Dynamic Custom Homes',
			],
		],
	];
}

/**
 * Render the /areas-we-serve/ listing page: page-intro header + a richer
 * 2x2 grid of area cards (image + name + top communities + arrow).
 */
function dch_fse_areas_listing_render(): string {
	$data = dch_fse_area_data();
	ob_start();
	?>
	<section class="dch-page-intro">
		<div class="dch-page-intro__inner">
			<p class="dch-page-intro__eyebrow" data-dch-anim="block">Where We Build</p>
			<h1 class="dch-page-intro__title" data-dch-anim="block">Areas we serve across Central Texas</h1>
			<p class="dch-page-intro__lede" data-dch-anim="block">Dynamic Custom Homes builds custom homes throughout Central Texas. With over 17 years of experience, we understand the unique building requirements, lot characteristics, and lifestyle of each community we serve &mdash; from urban San Antonio to remote Hill Country acreage.</p>
		</div>
	</section>

	<section class="dch-areas-list">
		<div class="dch-areas-list__inner">
			<div class="dch-areas-list__grid" data-dch-anim="block">
				<?php foreach ( $data as $slug => $area ) : ?>
					<a class="dch-areas-list__card" href="<?php echo esc_url( '/areas-we-serve/' . $slug . '/' ); ?>">
						<div class="dch-areas-list__media">
							<img src="<?php echo esc_url( $area['card_image'] ); ?>" alt="<?php echo esc_attr( $area['name'] ); ?>" loading="lazy" decoding="async" width="924" height="640">
						</div>
						<div class="dch-areas-list__body">
							<div class="dch-areas-list__copy">
								<h2 class="dch-areas-list__name"><?php echo $area['name']; ?></h2>
								<p class="dch-areas-list__sub"><?php echo $area['card_subtitle']; ?></p>
							</div>
							<span class="dch-areas-list__arrow" aria-hidden="true">
								<svg viewBox="0 0 42 26" width="42" height="26"><line x1="2" y1="13" x2="40" y2="13" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><polyline points="30,3 40,13 30,23" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
							</span>
						</div>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php
	return preg_replace( '/>\s+</', '><', (string) ob_get_clean() );
}

/**
 * Render the per-area child page: hero, communities pill list, and a 2-col
 * "why build here" split. Other shared sections (services grid, projects,
 * stats, testimonial, contact) live in templates/page-areas-child.html.
 */
function dch_fse_area_child_render(): string {
	$post = get_queried_object();
	if ( ! $post || 'page' !== $post->post_type ) {
		return '';
	}

	$slug = $post->post_name;
	$data = dch_fse_area_data();
	if ( ! isset( $data[ $slug ] ) ) {
		// Unknown area — graceful fallback.
		ob_start();
		?>
		<section class="dch-services-hero">
			<div class="dch-services-hero__inner">
				<div class="dch-services-hero__head" data-dch-anim="block">
					<p class="dch-services-hero__eyebrow">Where We Build</p>
					<h1 class="dch-services-hero__title"><?php echo esc_html( get_the_title( $post ) ); ?></h1>
				</div>
			</div>
		</section>
		<?php
		return preg_replace( '/>\s+</', '><', (string) ob_get_clean() );
	}

	$d = $data[ $slug ];

	ob_start();
	?>
	<section class="dch-services-hero">
		<div class="dch-services-hero__inner">
			<div class="dch-services-hero__head" data-dch-anim="block">
				<p class="dch-services-hero__eyebrow"><?php echo $d['eyebrow']; ?></p>
				<h1 class="dch-services-hero__title"><?php echo $d['title']; ?></h1>
				<p class="dch-services-hero__lede"><?php echo $d['lede']; ?></p>
			</div>
			<figure class="dch-services-hero__photo" data-dch-anim="block">
				<img src="<?php echo esc_url( $d['image'] ); ?>" alt="<?php echo esc_attr( $d['image_alt'] ); ?>" loading="eager" decoding="async" width="1260" height="1260">
			</figure>
		</div>
	</section>

	<section class="dch-communities">
		<div class="dch-communities__inner">
			<header class="dch-communities__head" data-dch-anim="block">
				<p class="dch-communities__eyebrow"><?php echo $d['communities']['eyebrow']; ?></p>
				<h2 class="dch-communities__heading"><?php echo $d['communities']['heading']; ?></h2>
				<?php if ( ! empty( $d['communities']['lede'] ) ) : ?>
					<p class="dch-communities__lede"><?php echo $d['communities']['lede']; ?></p>
				<?php endif; ?>
			</header>
			<ul class="dch-communities__list" data-dch-anim="block">
				<?php foreach ( $d['communities']['items'] as $item ) : ?>
					<li class="dch-communities__pill"><?php echo $item; ?></li>
				<?php endforeach; ?>
			</ul>
		</div>
	</section>

	<section class="dch-about-builder dch-about-builder--service">
		<div class="dch-about-builder__inner">
			<div class="dch-about-builder__copy" data-dch-anim="block">
				<p class="dch-about-builder__eyebrow"><?php echo $d['split']['eyebrow']; ?></p>
				<h2 class="dch-about-builder__heading"><?php echo $d['split']['heading']; ?></h2>
				<div class="dch-about-builder__body">
					<?php echo $d['split']['body']; ?>
				</div>
			</div>
			<figure class="dch-about-builder__photo" data-dch-anim="block">
				<img src="<?php echo esc_url( $d['split']['image'] ); ?>" alt="<?php echo esc_attr( $d['split']['image_alt'] ); ?>" loading="lazy" decoding="async">
			</figure>
		</div>
	</section>
	<?php
	return preg_replace( '/>\s+</', '><', (string) ob_get_clean() );
}

add_action( 'init', static function (): void {
	register_block_type( 'dch/areas-listing', [
		'api_version'     => 3,
		'title'           => 'DCH Areas Listing',
		'category'        => 'theme',
		'render_callback' => 'dch_fse_areas_listing_render',
		'supports'        => [ 'html' => false ],
	] );

	register_block_type( 'dch/area-child', [
		'api_version'     => 3,
		'title'           => 'DCH Area Child',
		'category'        => 'theme',
		'render_callback' => 'dch_fse_area_child_render',
		'supports'        => [ 'html' => false ],
	] );
} );
