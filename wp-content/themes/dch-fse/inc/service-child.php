<?php
/**
 * Service child page renderer (children of /services/) — composes a tailored
 * layout per slug from the shared dch- section vocabulary.
 *
 * Each child page (custom-home-building, home-remodeling, design-build,
 * luxury-homes) is built from the same kit of section types you'll see on
 * the Infratech demo variants — hero, marquee, tile grid, numbered detail,
 * 2-col copy/photo, projects accordion, stats, testimonial, areas, contact.
 * The unique middle content is selected here per slug; everything else is
 * static markup in templates/page-services-child.html so it stays editable
 * and inspectable in the FSE template.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Resolve the parent /services/ page id so we can detect children.
 */
function dch_fse_services_parent_id(): int {
	static $cached = null;
	if ( null !== $cached ) {
		return $cached;
	}
	$page   = get_page_by_path( 'services' );
	$cached = $page ? (int) $page->ID : 0;
	return $cached;
}

/**
 * Decide whether the current request is a child page of /services/.
 */
function dch_fse_is_service_child(): bool {
	if ( ! is_singular( 'page' ) ) {
		return false;
	}
	$post = get_queried_object();
	if ( ! $post || 'page' !== $post->post_type ) {
		return false;
	}
	$parent_id = dch_fse_services_parent_id();
	return $parent_id && (int) $post->post_parent === $parent_id;
}

/**
 * Route service child pages through templates/page-services-child.html before
 * the generic page.html. Same FSE-friendly hook used for project children.
 */
add_filter( 'page_template_hierarchy', static function ( array $templates ): array {
	if ( ! dch_fse_is_service_child() ) {
		return $templates;
	}
	array_unshift( $templates, 'page-services-child' );
	return $templates;
} );

/**
 * Per-slug content map. Centralises hero copy + which middle blocks render.
 *
 * Keys:
 *   eyebrow   — small label above the H1 in the hero
 *   title     — H1 (page title)
 *   lede      — supporting paragraph in the hero
 *   image     — wide hero photo
 *   image_alt — alt text for the hero photo
 *   blocks    — ordered list of middle-section descriptors. Each entry is
 *               an array shaped { type: <renderer name>, ...payload }.
 */
function dch_fse_service_child_data(): array {
	return [
		'custom-home-building' => [
			'eyebrow'   => 'Custom Home Building',
			'title'     => 'Custom Home Building in San Antonio &amp; Central Texas',
			'lede'      => 'Dynamic Custom Homes specializes in building dream homes from the ground up on your lot anywhere in Central Texas. With over 17 years of experience, owner Joshua Maas and our team deliver personalized craftsmanship and transparent communication at every stage of the build.',
			'image'     => '/wp-content/themes/dch-fse/assets/images/project-southwick-ranch.jpg',
			'image_alt' => 'Custom home built on Southwick Ranch by Dynamic Custom Homes',
			'blocks'    => [
				[
					'type'    => 'process',
					'eyebrow' => 'Our Custom Home Building Process',
					'heading' => 'From first conversation to move-in day',
					'steps'   => [
						[ 'title' => 'Consultation &amp; Lot Evaluation', 'desc' => 'We assess your vision, budget, timeline, and lot &mdash; whether you already own land or need help finding the right one.' ],
						[ 'title' => 'Design &amp; Architecture',         'desc' => 'We work with your architect or our design partners to create detailed plans tailored to your lifestyle and the property.' ],
						[ 'title' => 'Selections &amp; Permitting',       'desc' => 'Choose your finishes alongside our team while we handle every permit, inspection, and approval behind the scenes.' ],
						[ 'title' => 'Construction &amp; Move-In',        'desc' => 'Quality construction with regular progress updates, transparent budgeting, and a final walkthrough before you turn the key.' ],
					],
				],
				[
					'type'    => 'includes',
					'eyebrow' => 'What&rsquo;s included',
					'heading' => 'Everything you need to build with confidence',
					'lede'    => 'No hidden costs. No surprise scopes. Every Dynamic Custom Homes build covers the work that turns a plan into a finished home.',
					'image'   => '/wp-content/themes/dch-fse/assets/images/project-graystone-circle.jpg',
					'items'   => [
						'Site evaluation and lot preparation',
						'Design coordination with architects and engineers',
						'Complete material selection assistance',
						'Full construction management and quality inspections',
						'Transparent budgeting with no hidden costs',
					],
				],
			],
		],

		'home-remodeling' => [
			'eyebrow'   => 'Home Remodeling',
			'title'     => 'Home Remodeling in San Antonio &amp; Central Texas',
			'lede'      => 'Transform your existing home with expert renovations from Dynamic Custom Homes. We bring the same meticulous craftsmanship to remodeling projects that we deliver in our new custom home builds &mdash; kitchens, bathrooms, additions, and whole-home remodels built to the highest standard.',
			'image'     => '/wp-content/themes/dch-fse/assets/images/project-graystone-circle.jpg',
			'image_alt' => 'Home remodeling project by Dynamic Custom Homes',
			'blocks'    => [
				[
					'type'    => 'tiles',
					'eyebrow' => 'Our Remodeling Services',
					'heading' => 'Renovations built to custom-home standards',
					'lede'    => 'Whether you&rsquo;re reimagining a single room or reshaping your entire footprint, we approach every remodel with the same craftsmanship and transparency we bring to ground-up builds.',
					'tiles'   => [
						[ 'icon' => 'kitchen',   'title' => 'Kitchen Renovations', 'desc' => 'Custom cabinetry, countertops, lighting, and layout redesigns built around how your family actually cooks and gathers.' ],
						[ 'icon' => 'bath',      'title' => 'Bathroom Remodels',   'desc' => 'Luxury upgrades including walk-in showers, soaking tubs, and custom tile work tailored to your space.' ],
						[ 'icon' => 'additions', 'title' => 'Home Additions',      'desc' => 'Expand your living space with seamless room additions that match your home&rsquo;s existing style and structure.' ],
						[ 'icon' => 'whole',     'title' => 'Whole-Home Remodels', 'desc' => 'Complete interior and exterior transformations for homes that need a top-to-bottom reset.' ],
					],
				],
				[
					'type'    => 'split',
					'eyebrow' => 'Why Choose Dynamic Custom Homes',
					'heading' => 'Renovation expertise built on 17 years of custom-home craft',
					'body'    => '<p>With over 17 years of experience building and remodeling homes across San Antonio, Boerne, New Braunfels, and the Hill Country, we understand the unique challenges of renovation projects.</p><p>Our approach combines transparent communication, quality materials, and skilled craftsmanship to deliver results that exceed expectations &mdash; on the schedule and the budget we set together.</p>',
					'image'   => '/wp-content/themes/dch-fse/assets/images/about-52.jpg',
					'image_alt' => 'Joshua Maas, owner of Dynamic Custom Homes',
				],
			],
		],

		'design-build' => [
			'eyebrow'   => 'Design-Build',
			'title'     => 'Design-Build Homes in San Antonio &amp; Central Texas',
			'lede'      => 'Streamline your custom home project with the integrated design-build approach from Dynamic Custom Homes. One team, one vision &mdash; from initial blueprint to move-in day.',
			'image'     => '/wp-content/themes/dch-fse/assets/images/project-8634-terra-mont-way.jpg',
			'image_alt' => 'Design-build home by Dynamic Custom Homes',
			'blocks'    => [
				[
					'type'    => 'split',
					'eyebrow' => 'What Is Design-Build?',
					'heading' => 'One team for design and construction',
					'body'    => '<p>Design-build means one team handles both the architectural design and construction of your home. Instead of hiring an architect separately and then finding a builder, Dynamic Custom Homes manages the entire process.</p><p>This streamlined approach means better communication, fewer delays, and a final product that matches your original vision &mdash; without the friction of two contracts and two timelines.</p>',
					'image'   => '/wp-content/themes/dch-fse/assets/images/project-8815-terra-mont-way.jpg',
					'image_alt' => 'Design-build process at Dynamic Custom Homes',
				],
				[
					'type'    => 'tiles',
					'eyebrow' => 'Benefits of Design-Build',
					'heading' => 'Why our clients choose the integrated approach',
					'lede'    => 'Pulling design and construction under one roof saves time, money, and the headache of coordinating between teams that don&rsquo;t share priorities.',
					'tiles'   => [
						[ 'icon' => 'point',    'title' => 'Single Point of Contact', 'desc' => 'One team from start to finish &mdash; no handoffs, no finger-pointing, no gaps between design intent and construction.' ],
						[ 'icon' => 'budget',   'title' => 'Cost Efficiency',         'desc' => 'Integrated budgeting prevents surprises. We price as we design, so you always know what your decisions cost.' ],
						[ 'icon' => 'speed',    'title' => 'Faster Timelines',        'desc' => 'Design and pre-construction happen in parallel, compressing months out of the traditional process.' ],
						[ 'icon' => 'vision',   'title' => 'Consistent Vision',       'desc' => 'Your design intent carries through every construction detail because the same team owns both phases.' ],
					],
				],
			],
		],

		'luxury-homes' => [
			'eyebrow'   => 'Luxury Custom Homes',
			'title'     => 'Luxury Custom Home Builder in San Antonio &amp; Central Texas',
			'lede'      => 'For the most discerning homeowners in Central Texas, Dynamic Custom Homes delivers elevated finishes, premium materials, and meticulous attention to detail. Our luxury custom homes reflect your unique vision with uncompromising quality at every turn.',
			'image'     => '/wp-content/themes/dch-fse/assets/images/project-8815-terra-mont-way.jpg',
			'image_alt' => 'Luxury custom home in San Antonio by Dynamic Custom Homes',
			'blocks'    => [
				[
					'type'    => 'tiles',
					'eyebrow' => 'What Sets Our Luxury Homes Apart',
					'heading' => 'Elevated craft, end to end',
					'lede'    => 'Every luxury home we build is a one-of-one project &mdash; engineered around its lot, finished to a level that holds up to the closest inspection, and integrated with the systems modern living demands.',
					'tiles'   => [
						[ 'icon' => 'materials', 'title' => 'Premium Materials',       'desc' => 'Hand-selected stone, hardwoods, custom millwork, and imported fixtures sourced for both beauty and longevity.' ],
						[ 'icon' => 'finishes',  'title' => 'Elevated Finishes',       'desc' => 'Designer lighting, custom cabinetry, spa-inspired bathrooms, and gourmet kitchens that anchor every room.' ],
						[ 'icon' => 'smart',     'title' => 'Smart Home Integration',  'desc' => 'Whole-home automation, security systems, and energy management built in from the framing stage forward.' ],
						[ 'icon' => 'outdoor',   'title' => 'Outdoor Living',          'desc' => 'Resort-style pools, outdoor kitchens, fire features, and Hill Country views that turn the lot into another room.' ],
					],
				],
				[
					'type'    => 'communities',
					'eyebrow' => 'Luxury Home Communities We Serve',
					'heading' => 'Building in San Antonio&rsquo;s most prestigious neighborhoods',
					'lede'    => 'We build luxury custom homes in the communities where the bar for craftsmanship and design is highest &mdash; and where our reputation for quiet, on-schedule execution holds up.',
					'items'   => [ 'The Dominion', 'Shavano Park', 'Stone Oak', 'Boerne', 'New Braunfels', 'Texas Hill Country' ],
				],
			],
		],
	];
}

/**
 * SVG icon glyphs used by the tile grid. Returns a self-contained <svg>.
 * Falls back to a plain square if the requested icon name isn't mapped.
 */
function dch_fse_service_child_icon( string $name ): string {
	$icons = [
		'kitchen'   => '<svg viewBox="0 0 50 50" width="40" height="40" aria-hidden="true"><path fill="currentColor" d="M9 4h32v8H9zm0 12h32v30H9zm4 4v22h24V20H13z"/><path fill="currentColor" d="M16 24h6v8h-6zM28 24h6v14h-6z"/></svg>',
		'bath'      => '<svg viewBox="0 0 50 50" width="40" height="40" aria-hidden="true"><path fill="currentColor" d="M6 22h38v4l-3 14a4 4 0 0 1-4 3H13a4 4 0 0 1-4-3L6 26v-4zm4 4l2 13h26l2-13H10z"/><path fill="currentColor" d="M14 6a4 4 0 0 1 8 0v14h-3V6a1 1 0 0 0-2 0v14h-3V6z"/></svg>',
		'additions' => '<svg viewBox="0 0 50 50" width="40" height="40" aria-hidden="true"><path fill="currentColor" d="M3 24h20V4h4v20h20v4H27v20h-4V28H3z"/></svg>',
		'whole'     => '<svg viewBox="0 0 50 50" width="40" height="40" aria-hidden="true"><path fill="currentColor" d="M25 4 4 22h6v22h12V32h6v12h12V22h6L25 4zm0 4.6L41 22v18H32V28H18v12H9V22l16-13.4z"/></svg>',
		'point'     => '<svg viewBox="0 0 50 50" width="40" height="40" aria-hidden="true"><path fill="currentColor" d="M25 4a14 14 0 1 0 14 14A14 14 0 0 0 25 4zm0 22a8 8 0 1 1 8-8 8 8 0 0 1-8 8zm0 4a18 18 0 0 0-18 18h4a14 14 0 0 1 28 0h4a18 18 0 0 0-18-18z"/></svg>',
		'budget'    => '<svg viewBox="0 0 50 50" width="40" height="40" aria-hidden="true"><path fill="currentColor" d="M25 4a21 21 0 1 0 21 21A21 21 0 0 0 25 4zm0 38a17 17 0 1 1 17-17 17 17 0 0 1-17 17z"/><path fill="currentColor" d="M27 14h-4v3h-5v4h5v3h-3a4 4 0 0 0 0 8h3v3h4v-3h5v-4h-5v-3h3a4 4 0 0 0 0-8h-3z"/></svg>',
		'speed'     => '<svg viewBox="0 0 50 50" width="40" height="40" aria-hidden="true"><path fill="currentColor" d="M25 6a19 19 0 0 0-19 19 18.7 18.7 0 0 0 5 12.7l3-3a14.7 14.7 0 0 1-4-9.7 15 15 0 0 1 30 0 14.7 14.7 0 0 1-4 9.7l3 3A18.7 18.7 0 0 0 44 25 19 19 0 0 0 25 6z"/><path fill="currentColor" d="m31.5 16.5-9 9 2.8 2.8 9-9z"/></svg>',
		'vision'    => '<svg viewBox="0 0 50 50" width="40" height="40" aria-hidden="true"><path fill="currentColor" d="M25 12C12 12 4 25 4 25s8 13 21 13 21-13 21-13-8-13-21-13zm0 22a9 9 0 1 1 9-9 9 9 0 0 1-9 9z"/><circle cx="25" cy="25" r="4" fill="currentColor"/></svg>',
		'materials' => '<svg viewBox="0 0 50 50" width="40" height="40" aria-hidden="true"><path fill="currentColor" d="M25 4 4 14v22l21 10 21-10V14L25 4zm0 4.4 16.5 7.9L25 24.2 8.5 16.3 25 8.4zM7 18.3l16 7.6V41L7 33.4V18.3zm20 22.7V25.9l16-7.6v15.1L27 41z"/></svg>',
		'finishes'  => '<svg viewBox="0 0 50 50" width="40" height="40" aria-hidden="true"><path fill="currentColor" d="M14 4h22a4 4 0 0 1 4 4v34a4 4 0 0 1-4 4H14a4 4 0 0 1-4-4V8a4 4 0 0 1 4-4zm0 4v34h22V8H14z"/><path fill="currentColor" d="M18 14h14v3H18zm0 7h14v3H18zm0 7h10v3H18z"/></svg>',
		'smart'     => '<svg viewBox="0 0 50 50" width="40" height="40" aria-hidden="true"><path fill="currentColor" d="M25 6 6 22v22h12V32h14v12h12V22L25 6zm0 5 15 12.4V40H32v-8a4 4 0 0 0-4-4h-6a4 4 0 0 0-4 4v8h-8V23.4L25 11z"/><circle cx="25" cy="22" r="3" fill="currentColor"/></svg>',
		'outdoor'   => '<svg viewBox="0 0 50 50" width="40" height="40" aria-hidden="true"><path fill="currentColor" d="M25 4a13 13 0 0 0-12 18l-9 14h6v6h30v-6h6L37 22A13 13 0 0 0 25 4zm0 4a9 9 0 0 1 9 9 9 9 0 0 1-1.6 5.1L25 33l-7.4-10.9A9 9 0 0 1 16 17a9 9 0 0 1 9-9z"/></svg>',
	];
	return $icons[ $name ] ?? '<svg viewBox="0 0 50 50" width="40" height="40" aria-hidden="true"><rect x="6" y="6" width="38" height="38" fill="currentColor"/></svg>';
}

/**
 * Render the per-slug hero + middle blocks. Bound to the `dch/service-child`
 * dynamic block so the FSE template can drop it in declaratively.
 */
function dch_fse_service_child_render(): string {
	$post = get_queried_object();
	if ( ! $post || 'page' !== $post->post_type ) {
		return '';
	}

	$slug = $post->post_name;
	$data = dch_fse_service_child_data();
	if ( ! isset( $data[ $slug ] ) ) {
		// Unknown child of /services/ — render a graceful fallback so the page
		// still works without a tailored layout.
		ob_start();
		?>
		<section class="dch-services-hero">
			<div class="dch-services-hero__inner">
				<div class="dch-services-hero__head" data-dch-anim="block">
					<p class="dch-services-hero__eyebrow">Our Services</p>
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
				<p class="dch-services-hero__eyebrow"><?php echo $d['eyebrow']; // already-encoded entities ?></p>
				<h1 class="dch-services-hero__title"><?php echo $d['title']; ?></h1>
				<p class="dch-services-hero__lede"><?php echo $d['lede']; ?></p>
			</div>
			<figure class="dch-services-hero__photo" data-dch-anim="block">
				<img src="<?php echo esc_url( $d['image'] ); ?>" alt="<?php echo esc_attr( $d['image_alt'] ); ?>" loading="eager" decoding="async" width="1260" height="1260">
			</figure>
		</div>
	</section>
	<?php

	foreach ( $d['blocks'] as $block ) {
		switch ( $block['type'] ) {

			case 'process':
				?>
				<section class="dch-services-detail">
					<div class="dch-services-detail__inner">
						<header class="dch-services-detail__head" data-dch-anim="block">
							<p class="dch-services-detail__eyebrow"><?php echo $block['eyebrow']; ?></p>
							<h2 class="dch-services-detail__heading"><?php echo $block['heading']; ?></h2>
						</header>
						<ol class="dch-services-detail__list">
							<?php foreach ( $block['steps'] as $i => $step ) : ?>
								<li class="dch-services-detail__item" data-dch-anim="block">
									<span class="dch-services-detail__num"><?php echo str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT ); ?></span>
									<div class="dch-services-detail__body">
										<h3 class="dch-services-detail__title"><?php echo $step['title']; ?></h3>
										<p class="dch-services-detail__desc"><?php echo $step['desc']; ?></p>
									</div>
								</li>
							<?php endforeach; ?>
						</ol>
					</div>
				</section>
				<?php
				break;

			case 'tiles':
				?>
				<section class="dch-services-tiles">
					<div class="dch-services-tiles__inner">
						<header class="dch-services-tiles__head" data-dch-anim="block">
							<p class="dch-services-tiles__eyebrow"><?php echo $block['eyebrow']; ?></p>
							<h2 class="dch-services-tiles__heading"><?php echo $block['heading']; ?></h2>
							<?php if ( ! empty( $block['lede'] ) ) : ?>
								<p class="dch-services-tiles__lede"><?php echo $block['lede']; ?></p>
							<?php endif; ?>
						</header>
						<ul class="dch-services-tiles__grid">
							<?php foreach ( $block['tiles'] as $tile ) : ?>
								<li class="dch-services-tiles__item" data-dch-anim="block">
									<span class="dch-services-tiles__icon" aria-hidden="true">
										<?php echo dch_fse_service_child_icon( $tile['icon'] ); ?>
									</span>
									<h3 class="dch-services-tiles__title"><?php echo $tile['title']; ?></h3>
									<p class="dch-services-tiles__desc"><?php echo $tile['desc']; ?></p>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				</section>
				<?php
				break;

			case 'includes':
				?>
				<section class="dch-includes">
					<div class="dch-includes__inner">
						<div class="dch-includes__copy" data-dch-anim="block">
							<p class="dch-includes__eyebrow"><?php echo $block['eyebrow']; ?></p>
							<h2 class="dch-includes__heading"><?php echo $block['heading']; ?></h2>
							<?php if ( ! empty( $block['lede'] ) ) : ?>
								<p class="dch-includes__lede"><?php echo $block['lede']; ?></p>
							<?php endif; ?>
							<ul class="dch-includes__list">
								<?php foreach ( $block['items'] as $item ) : ?>
									<li class="dch-includes__item">
										<span class="dch-includes__check" aria-hidden="true">
											<svg viewBox="0 0 16 16" width="16" height="16"><path fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" d="M3 8.5l3.5 3.5L13 4"/></svg>
										</span>
										<span class="dch-includes__label"><?php echo $item; ?></span>
									</li>
								<?php endforeach; ?>
							</ul>
						</div>
						<figure class="dch-includes__photo" data-dch-anim="block">
							<img src="<?php echo esc_url( $block['image'] ); ?>" alt="" loading="lazy" decoding="async" width="924" height="1100">
						</figure>
					</div>
				</section>
				<?php
				break;

			case 'split':
				?>
				<section class="dch-about-builder dch-about-builder--service">
					<div class="dch-about-builder__inner">
						<div class="dch-about-builder__copy" data-dch-anim="block">
							<p class="dch-about-builder__eyebrow"><?php echo $block['eyebrow']; ?></p>
							<h2 class="dch-about-builder__heading"><?php echo $block['heading']; ?></h2>
							<div class="dch-about-builder__body">
								<?php echo $block['body']; ?>
							</div>
						</div>
						<figure class="dch-about-builder__photo" data-dch-anim="block">
							<img src="<?php echo esc_url( $block['image'] ); ?>" alt="<?php echo esc_attr( $block['image_alt'] ?? '' ); ?>" loading="lazy" decoding="async">
						</figure>
					</div>
				</section>
				<?php
				break;

			case 'communities':
				?>
				<section class="dch-communities">
					<div class="dch-communities__inner">
						<header class="dch-communities__head" data-dch-anim="block">
							<p class="dch-communities__eyebrow"><?php echo $block['eyebrow']; ?></p>
							<h2 class="dch-communities__heading"><?php echo $block['heading']; ?></h2>
							<?php if ( ! empty( $block['lede'] ) ) : ?>
								<p class="dch-communities__lede"><?php echo $block['lede']; ?></p>
							<?php endif; ?>
						</header>
						<ul class="dch-communities__list" data-dch-anim="block">
							<?php foreach ( $block['items'] as $item ) : ?>
								<li class="dch-communities__pill"><?php echo $item; ?></li>
							<?php endforeach; ?>
						</ul>
					</div>
				</section>
				<?php
				break;
		}
	}

	return preg_replace( '/>\s+</', '><', (string) ob_get_clean() );
}

add_action( 'init', static function (): void {
	register_block_type( 'dch/service-child', [
		'api_version'     => 3,
		'title'           => 'DCH Service Child',
		'category'        => 'theme',
		'render_callback' => 'dch_fse_service_child_render',
		'supports'        => [ 'html' => false ],
	] );
} );
