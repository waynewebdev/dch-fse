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
				'lede'    => 'We build custom homes throughout San Antonio, in neighborhoods with their own lot conditions, HOA requirements, and architectural styles. We know each one well.',
				'items'   => [ 'The Dominion', 'Shavano Park', 'Stone Oak', 'Helotes', 'Alamo Heights', 'Terrell Hills' ],
			],
			'split' => [
				'eyebrow'   => 'Why San Antonio',
				'heading'   => 'Why build a custom home in San Antonio?',
				'body'      => '<p>San Antonio has a lot going for it: a growing economy, plenty of culture, and the Hill Country close by. Building custom lets you pick the right lot and design a home that fits how your family lives.</p><p>Whether it&rsquo;s open floor plans for entertaining or private outdoor spaces built for the Texas climate, your home is shaped around how you actually live.</p>',
				'image'     => $theme_img . 'area-san-antonio-home.webp',
				'image_alt' => 'Open-concept living area in a San Antonio custom home by Dynamic Custom Homes',
			],
		],

		'boerne' => [
			'name'          => 'Boerne',
			'card_subtitle' => 'Cordillera Ranch &middot; Fair Oaks Ranch &middot; Tapatio Springs',
			'card_image'    => $theme_img . 'area-boerne.jpg',
			'eyebrow'       => 'Boerne, Texas',
			'title'         => 'Custom Home Builder in Boerne, Texas',
			'lede'          => 'Build your custom home in Boerne, TX with Dynamic Custom Homes. We&rsquo;re an award-winning Hill Country builder with over 17 years of experience, and we build homes that fit Boerne&rsquo;s small-town character without giving up modern comfort.',
			'image'         => $theme_img . 'area-boerne.jpg',
			'image_alt'     => 'Custom home in Boerne, TX by Dynamic Custom Homes',
			'communities'   => [
				'eyebrow' => 'Boerne &amp; Hill Country',
				'heading' => 'Boerne communities we serve',
				'lede'    => 'We build custom homes throughout Boerne and the surrounding Kendall County area, on terrain that ranges from oak-shaded acreage to elevated Hill Country lots with long views.',
				'items'   => [ 'Cordillera Ranch', 'Fair Oaks Ranch', 'Balcones Creek', 'Tapatio Springs', 'Kendall County' ],
			],
			'split' => [
				'eyebrow'   => 'Why Boerne',
				'heading'   => 'Building in Boerne &amp; the Hill Country',
				'body'      => '<p>Boerne is one of the most popular towns in the Texas Hill Country, known for its landscape, its schools, and a lively downtown.</p><p>Building a custom home here means working with varied terrain, mature oaks, and long Hill Country views, all of which call for a builder who understands the land.</p>',
				'image'     => $theme_img . 'area-boerne-home.webp',
				'image_alt' => 'Hill Country custom home exterior in Boerne by Dynamic Custom Homes',
			],
		],

		'new-braunfels' => [
			'name'          => 'New Braunfels',
			'card_subtitle' => 'Gruene &middot; River Road &middot; Canyon Lake',
			'card_image'    => $theme_img . 'area-new-braunfels.jpg',
			'eyebrow'       => 'New Braunfels, Texas',
			'title'         => 'Custom Home Builder in New Braunfels, Texas',
			'lede'          => 'Dynamic Custom Homes builds custom homes in New Braunfels, TX and the surrounding Comal County area. With over 17 years of building experience in Central Texas, we know the appeal and the building requirements of this fast-growing community.',
			'image'         => $theme_img . 'area-new-braunfels.jpg',
			'image_alt'     => 'Custom home in New Braunfels by Dynamic Custom Homes',
			'communities'   => [
				'eyebrow' => 'New Braunfels Areas',
				'heading' => 'Where we build in Comal County',
				'lede'    => 'From historic Gruene to riverfront lots along the Comal and Guadalupe, we build across the New Braunfels footprint, including the master-planned communities ringing Canyon Lake.',
				'items'   => [ 'Gruene', 'River Road', 'Vintage Oaks', 'Canyon Lake', 'Comal County' ],
			],
			'split' => [
				'eyebrow'   => 'Why New Braunfels',
				'heading'   => 'New Braunfels living, custom-built',
				'body'      => '<p>New Braunfels pairs Hill Country scenery with small-town charm and easy access to both San Antonio and Austin. Between the Comal and Guadalupe rivers, the historic Gruene district, and its family-friendly neighborhoods, it&rsquo;s one of the fastest-growing cities in Texas.</p><p>Building a custom home here means designing for the river-and-Hill-Country lifestyle: outdoor living, big windows, and floor plans that flex for quiet evenings and weekend visitors.</p>',
				'image'     => $theme_img . 'area-new-braunfels-home.webp',
				'image_alt' => 'Custom home great room with big windows near New Braunfels by Dynamic Custom Homes',
			],
		],

		'hill-country' => [
			'name'          => 'Texas Hill Country',
			'card_subtitle' => 'Wimberley &middot; Dripping Springs &middot; Fredericksburg',
			'card_image'    => $theme_img . 'area-hill-country.jpg',
			'eyebrow'       => 'Texas Hill Country',
			'title'         => 'Hill Country Custom Home Builder',
			'lede'          => 'Dynamic Custom Homes is your trusted custom home builder in the Texas Hill Country. With over 17 years building across the region, we understand its terrain, climate, and lifestyle.',
			'image'         => $theme_img . 'area-hill-country.jpg',
			'image_alt'     => 'Hill Country custom home by Dynamic Custom Homes',
			'communities'   => [
				'eyebrow' => 'Hill Country Communities',
				'heading' => 'Where we build across the Hill Country',
				'lede'    => 'From the lakes of Wimberley to the wineries of Fredericksburg, we build across the full Hill Country footprint, on lots that demand builders who know rock foundations, slope management, and view-driven design.',
				'items'   => [ 'Wimberley', 'Dripping Springs', 'Fredericksburg', 'Kerrville', 'Bandera', 'Hill Country' ],
			],
			'split' => [
				'eyebrow'   => 'Why the Hill Country',
				'heading'   => 'Building for Hill Country living',
				'body'      => '<p>Hill Country homes demand builders who understand sloped terrain, rock foundations, native landscaping, and designs that maximize views while managing the Texas climate.</p><p>From sprawling ranch-style homes to modern Hill Country estates, we build homes that belong in this landscape, and last on it.</p>',
				'image'     => $theme_img . 'area-hill-country-home.webp',
				'image_alt' => 'Hill Country custom home with an infinity pool and views by Dynamic Custom Homes',
			],
		],

		// Added from 2026-07-30 owner meeting. Copy is a draft for review.
		'marble-falls' => [
			'name'          => 'Marble Falls',
			'card_subtitle' => 'Meadowlakes &middot; Gregg Ranch &middot; The Legends',
			'card_image'    => $theme_img . 'area-marble-falls.webp',
			'eyebrow'       => 'Marble Falls, Texas',
			'title'         => 'Custom Home Builder in Marble Falls, Texas',
			'lede'          => 'Dynamic Custom Homes builds custom homes in Marble Falls and across the Highland Lakes. With over 17 years of experience in the Texas Hill Country, we understand the granite terrain, lakefront lots, and relaxed river-and-lake lifestyle that make this corner of Burnet County special.',
			'image'         => $theme_img . 'area-marble-falls.webp',
			'image_alt'     => 'Lake Marble Falls in the Texas Hill Country',
			'communities'   => [
				'eyebrow' => 'Marble Falls Communities',
				'heading' => 'Where we build around Marble Falls',
				'lede'    => 'From lakefront lots on Lake Marble Falls to gated fairway communities, we build across the Highland Lakes region, on terrain that ranges from granite outcrops to gentle riverfront acreage.',
				'items'   => [ 'Meadowlakes', 'Gregg Ranch', 'The Legends', 'Blue Lake', 'Horseshoe Bay', 'Burnet County' ],
			],
			'split' => [
				'eyebrow'   => 'Why Marble Falls',
				'heading'   => 'Building for Highland Lakes living',
				'body'      => '<p>Marble Falls sits in the middle of the Highland Lakes, where the Colorado River widens into a chain of lakes known for boating, fishing, and waterfront living. It has small-town Hill Country charm within easy reach of both Austin and San Antonio.</p><p>Building here means designing for lake views and outdoor living while accounting for granite bedrock and sloped lots, work that rewards an experienced local builder.</p>',
				'image'     => $theme_img . 'area-marble-falls-home.webp',
				'image_alt' => 'Marble Falls custom home built by Dynamic Custom Homes',
			],
		],

		'horseshoe-bay' => [
			'name'          => 'Horseshoe Bay',
			'card_subtitle' => 'Applehead Island &middot; Escondido &middot; The Waters',
			'card_image'    => $theme_img . 'area-horseshoe-bay.webp',
			'eyebrow'       => 'Horseshoe Bay, Texas',
			'title'         => 'Custom Home Builder in Horseshoe Bay, Texas',
			'lede'          => 'Dynamic Custom Homes builds custom and luxury homes in Horseshoe Bay, the resort community on the shores of Lake LBJ. With over 17 years of Hill Country experience, we specialize in waterfront and golf-course homes built to the exacting standards this community is known for.',
			'image'         => $theme_img . 'area-horseshoe-bay.webp',
			'image_alt'     => 'Waterfront homes on Lake LBJ in Horseshoe Bay, Texas',
			'communities'   => [
				'eyebrow' => 'Horseshoe Bay Communities',
				'heading' => 'Where we build in Horseshoe Bay',
				'lede'    => 'From waterfront lots on constant-level Lake LBJ to the resort&rsquo;s championship golf communities, we build across Horseshoe Bay, where shoreline rules and resort design guidelines call for an experienced builder.',
				'items'   => [ 'Applehead Island', 'Escondido', 'The Waters', 'Tremolo', 'Summit Rock', 'Llano County' ],
			],
			'split' => [
				'eyebrow'   => 'Why Horseshoe Bay',
				'heading'   => 'Waterfront and fairway homes, custom-built',
				'body'      => '<p>Horseshoe Bay is one of Central Texas&rsquo;s best-known resort communities, with constant-level Lake LBJ, championship golf, and a marina lifestyle just an hour from Austin.</p><p>Building here means meeting the community&rsquo;s architectural standards while making the most of waterfront and fairway lots, the kind of detail-driven work that defines a luxury custom home.</p>',
				'image'     => $theme_img . 'area-horseshoe-bay-home.webp',
				'image_alt' => 'Horseshoe Bay custom home built by Dynamic Custom Homes',
			],
		],

		// Canyon Lake also appears as a community under New Braunfels; it has its own page too.
		'canyon-lake' => [
			'name'          => 'Canyon Lake',
			'card_subtitle' => 'Canyon Lake Hills &middot; Cordova Bend &middot; Mystic Shores',
			'card_image'    => $theme_img . 'area-canyon-lake.webp',
			'eyebrow'       => 'Canyon Lake, Texas',
			'title'         => 'Custom Home Builder in Canyon Lake, Texas',
			'lede'          => 'Dynamic Custom Homes builds custom homes around Canyon Lake, the Hill Country reservoir between New Braunfels and San Antonio. With over 17 years of Comal County experience, we build on the wooded, often-sloped lots that surround the lake, with the drainage and foundation know-how they require.',
			'image'         => $theme_img . 'area-canyon-lake.webp',
			'image_alt'     => 'Canyon Lake in the Texas Hill Country',
			'communities'   => [
				'eyebrow' => 'Canyon Lake Communities',
				'heading' => 'Where we build around Canyon Lake',
				'lede'    => 'From gated lakeview subdivisions to acreage in the surrounding hills, we build across the Canyon Lake area, on lots where slope, rock, and tree preservation shape the design.',
				'items'   => [ 'Canyon Lake Hills', 'Cordova Bend', 'Mystic Shores', 'Sattler', 'Startzville', 'Comal County' ],
			],
			'split' => [
				'eyebrow'   => 'Why Canyon Lake',
				'heading'   => 'Lakeside Hill Country living',
				'body'      => '<p>Canyon Lake has miles of shoreline, clear water, and wooded hills within an easy drive of both San Antonio and Austin, a quieter, more natural alternative to the busier lake towns.</p><p>Building here means designing for views and the outdoors while managing sloped, rocky lots and mature trees, exactly the terrain we know from nearly two decades of building across Comal County.</p>',
				'image'     => $theme_img . 'area-canyon-lake-home.webp',
				'image_alt' => 'Canyon Lake custom home built by Dynamic Custom Homes',
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
			<p class="dch-page-intro__lede" data-dch-anim="block">Dynamic Custom Homes builds custom homes throughout Central Texas. With over 17 years of experience, we know the building requirements, lot conditions, and lifestyle of each community we serve, from urban San Antonio to remote Hill Country acreage.</p>
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
