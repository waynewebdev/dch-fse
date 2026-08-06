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
			'title'     => 'Custom Home Builder in San Antonio &amp; Central Texas',
			'lede'      => 'Already have house plans, or know exactly what you want to build? Dynamic Custom Homes builds your home to spec on your lot anywhere in Central Texas. With over 17 years of experience, owner Joshua Maas and our team turn finished plans into a finished home &mdash; with transparent cost-plus pricing and clear communication at every stage.',
			'image'     => '/wp-content/themes/dch-fse/assets/images/svc-custom-hero.webp',
			'image_alt' => 'Custom Hill Country home built to spec by Dynamic Custom Homes',
			'card_desc' => 'Have a plan already? We build your custom home to spec on your lot anywhere in Central Texas &mdash; tailored to your vision, budget, and timeline.',
			'card_image' => '/wp-content/themes/dch-fse/assets/images/project-southwick-ranch.jpg',
			'blocks'    => [
				[
					'type'    => 'process',
					'eyebrow' => 'Our Custom Home Building Process',
					'heading' => 'From your plans to move-in day',
					'steps'   => [
						[ 'title' => 'Consultation &amp; Lot Evaluation', 'desc' => 'We review your plans, vision, budget, and timeline &mdash; and evaluate your lot, whether you already own land or need help finding the right one.' ],
						[ 'title' => 'Plan Review &amp; Engineering',     'desc' => 'We take your existing plans, value-engineer them for your budget, and coordinate the structural and civil engineering needed to build them right.' ],
						[ 'title' => 'Selections &amp; Permitting',       'desc' => 'Choose your finishes alongside our team while we handle every permit, inspection, and approval behind the scenes.' ],
						[ 'title' => 'Construction &amp; Move-In',        'desc' => 'Quality construction with regular progress updates, transparent budgeting, and a final walkthrough before you turn the key.' ],
					],
				],
				[
					'type'    => 'includes',
					'eyebrow' => 'What&rsquo;s included',
					'heading' => 'Everything you need to build with confidence',
					'lede'    => 'No hidden costs. No surprise scopes. Every Dynamic Custom Homes build covers the work that turns a plan into a finished home.',
					'image'   => '/wp-content/themes/dch-fse/assets/images/svc-custom-detail.webp',
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
			'title'     => 'Luxury Home Remodeling in San Antonio &amp; Central Texas',
			'lede'      => 'This isn&rsquo;t your ordinary home remodel. Dynamic Custom Homes brings full custom-home craftsmanship to large-scale renovations across San Antonio and the Hill Country &mdash; high-end kitchens and baths, major additions, and whole-home transformations for established homes that deserve better than a quick refresh.',
			'image'     => '/wp-content/themes/dch-fse/assets/images/svc-remodel-hero.webp',
			'image_alt' => 'Renovated luxury kitchen by Dynamic Custom Homes in San Antonio',
			'card_desc' => 'Not your ordinary remodel &mdash; large-scale renovations and additions built to full custom-home standards for established homes.',
			'card_image' => '/wp-content/themes/dch-fse/assets/images/project-graystone-circle.jpg',
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
					'image'   => '/wp-content/themes/dch-fse/assets/images/svc-remodel-detail.webp',
					'image_alt' => 'Luxury master bathroom remodel by Dynamic Custom Homes',
				],
			],
		],

		'design-build' => [
			'eyebrow'   => 'Design-Build',
			'title'     => 'Design-Build Home Builder in San Antonio &amp; Central Texas',
			'lede'      => 'Starting from a blank page? With our design-build approach, we develop your plans together from the very beginning &mdash; then build them. One team, one contract, and one point of contact from first sketch to move-in day, anywhere in Central Texas.',
			'image'     => '/wp-content/themes/dch-fse/assets/images/svc-designbuild-hero.webp',
			'image_alt' => 'Modern design-build custom home by Dynamic Custom Homes',
			'card_desc' => 'No plans yet? We develop your design together from the start, then build it &mdash; one team, one contract, start to finish.',
			'card_image' => '/wp-content/themes/dch-fse/assets/images/project-8634-terra-mont-way.jpg',
			'blocks'    => [
				[
					'type'    => 'split',
					'eyebrow' => 'What Is Design-Build?',
					'heading' => 'One team for design and construction',
					'body'    => '<p>Design-build means one team handles both the architectural design and construction of your home. Instead of hiring an architect separately and then finding a builder, Dynamic Custom Homes manages the entire process.</p><p>This streamlined approach means better communication, fewer delays, and a final product that matches your original vision &mdash; without the friction of two contracts and two timelines.</p>',
					'image'   => '/wp-content/themes/dch-fse/assets/images/svc-designbuild-detail.webp',
					'image_alt' => 'Open-concept living and dining in a Dynamic Custom Homes design-build',
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
			'eyebrow'   => 'Luxury Homes',
			'title'     => 'Luxury Custom Homes in San Antonio &amp; Central Texas',
			'lede'      => 'Luxury isn&rsquo;t only for the top of the market. Dynamic Custom Homes builds thoughtfully elevated custom homes &mdash; typically in the $500K to $1 million range &mdash; for move-up families across San Antonio and the Hill Country who want premium finishes and real craftsmanship without an ultra-luxury price tag.',
			'image'     => '/wp-content/themes/dch-fse/assets/images/svc-luxury-hero.webp',
			'image_alt' => 'Luxury custom home living room by Dynamic Custom Homes near San Antonio',
			'card_desc' => 'Thoughtfully elevated homes for move-up families &mdash; premium finishes and craftsmanship, typically in the $500K&ndash;$1M range.',
			'card_image' => '/wp-content/themes/dch-fse/assets/images/project-8815-terra-mont-way.jpg',
			'blocks'    => [
				[
					'type'    => 'tiles',
					'eyebrow' => 'What Sets Our Luxury Homes Apart',
					'heading' => 'Elevated craft, end to end',
					'lede'    => 'You don&rsquo;t have to spend millions to get a home that feels custom in every detail. Each home is engineered around its lot, finished to a level that holds up to the closest inspection, and built for the way your family actually lives.',
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
					'heading' => 'Where we build across San Antonio &amp; the Hill Country',
					'lede'    => 'From established San Antonio neighborhoods to Hill Country acreage, we build elevated custom homes where families actually want to put down roots &mdash; with the same quiet, on-schedule execution we&rsquo;re known for.',
					'items'   => [ 'The Dominion', 'Shavano Park', 'Stone Oak', 'Boerne', 'New Braunfels', 'Texas Hill Country' ],
				],
			],
		],

		'semi-custom-homes' => [
			'eyebrow' => 'Semi-Custom Homes',
			'title' => 'Semi-Custom Home Plans in San Antonio &amp; Central Texas',
			'lede' => 'Start from one of our proven floor plans and make it yours. Semi-custom homes from Dynamic Custom Homes give you the personalization of a custom build with the predictable pricing and faster timeline of a refined, ready-to-adapt plan &mdash; ideal for move-up families across San Antonio and the Hill Country.',
			'image' => '/wp-content/themes/dch-fse/assets/images/svc-semicustom-hero.webp',
			'image_alt' => 'Semi-custom home built by Dynamic Custom Homes in the Texas Hill Country',
			'card_desc' => 'Personalize one of our proven floor plans &mdash; the flexibility of custom with predictable pricing and a faster timeline.',
			'card_image' => '/wp-content/themes/dch-fse/assets/images/area-new-braunfels.jpg',
			'blocks'    => [
				[
					'type'    => 'process',
					'eyebrow' => 'How Semi-Custom Works',
					'heading' => 'Your plan, your way &mdash; without starting from scratch',
					'steps'   => [
						[ 'title' => 'Choose a Plan', 'desc' => 'Start with one of our curated floor plans, each already refined for how Central Texas families actually live.' ],
						[ 'title' => 'Personalize the Layout', 'desc' => 'Adjust rooms, ceilings, and elevations to fit your lot and lifestyle, with our team flagging what&rsquo;s simple and what adds cost.' ],
						[ 'title' => 'Select Your Finishes', 'desc' => 'Make it yours with guided selections for cabinetry, flooring, and fixtures at clear, upfront allowances.' ],
						[ 'title' => 'We Build It', 'desc' => 'We build your personalized plan with transparent cost-plus pricing and the same craftsmanship as our full custom homes.' ],
					],
				],
				[
					'type'    => 'tiles',
					'eyebrow' => 'Why Choose Semi-Custom',
					'heading' => 'The sweet spot between spec and full custom',
					'lede'    => 'You get real personalization without the time, cost, and uncertainty of designing a home from a blank page.',
					'tiles'   => [
						[ 'icon' => 'budget', 'title' => 'Predictable Pricing', 'desc' => 'Proven plans mean tighter bids and fewer costly surprises than a one-off custom design.' ],
						[ 'icon' => 'speed', 'title' => 'Faster Timeline', 'desc' => 'Because the plan is already engineered, we break ground sooner and finish faster.' ],
						[ 'icon' => 'plan', 'title' => 'Curated Plans', 'desc' => 'A growing portfolio of floor plans designed for Hill Country lots and Texas living.' ],
						[ 'icon' => 'finishes', 'title' => 'Real Personalization', 'desc' => 'Layout tweaks, elevations, and finish selections make each home distinctly yours.' ],
					],
				],
			],
		],

		'lot-consulting' => [
			'eyebrow' => 'Lot Consulting',
			'title' => 'Lot Consulting &amp; Land Evaluation in Central Texas',
			'lede' => 'Before you buy, know what you&rsquo;re building on. Dynamic Custom Homes evaluates lots across San Antonio and the Hill Country for slope, soil, drainage, utilities, setbacks, and HOA rules &mdash; so you don&rsquo;t inherit a five-figure surprise after closing.',
			'image' => '/wp-content/themes/dch-fse/assets/images/svc-lot-hero.webp',
			'image_alt' => 'Texas Hill Country land with bluebonnets, evaluated for building by Dynamic Custom Homes',
			'card_desc' => 'Thinking about a lot? We evaluate slope, soil, utilities, and restrictions before you buy &mdash; so nothing surprises you later.',
			'card_image' => '/wp-content/themes/dch-fse/assets/images/area-hill-country.jpg',
			'blocks'    => [
				[
					'type'    => 'tiles',
					'eyebrow' => 'What We Evaluate',
					'heading' => 'The questions that decide what a lot really costs to build on',
					'lede'    => 'Two lots at the same price can differ by tens of thousands once you account for what it takes to build. Here&rsquo;s what we check.',
					'tiles'   => [
						[ 'icon' => 'lot', 'title' => 'Slope &amp; Grading', 'desc' => 'Steep or uneven lots drive up excavation, foundation, and drainage costs &mdash; we quantify it early.' ],
						[ 'icon' => 'materials', 'title' => 'Soil &amp; Foundation', 'desc' => 'Rock and expansive clay soils dictate foundation design and budget across the Hill Country.' ],
						[ 'icon' => 'smart', 'title' => 'Utilities &amp; Access', 'desc' => 'Water, septic, electric, and driveway access can add significant cost on rural and acreage lots.' ],
						[ 'icon' => 'vision', 'title' => 'Setbacks &amp; HOA', 'desc' => 'Setbacks, easements, and HOA rules shape what &mdash; and where &mdash; you&rsquo;re actually allowed to build.' ],
					],
				],
				[
					'type'    => 'split',
					'eyebrow' => 'Why It Matters',
					'heading' => 'Lock in the right lot before a competitor does',
					'body'    => '<p>Many move-up buyers have the budget for a custom home but no land &mdash; and no easy way to tell a buildable lot from an expensive one. That&rsquo;s where we come in.</p><p>By evaluating a property before you buy, we help you negotiate with confidence, budget accurately, and avoid the foundation, drainage, and access surprises that derail so many projects.</p>',
					'image'   => '/wp-content/themes/dch-fse/assets/images/svc-lot-detail.webp',
					'image_alt' => 'Custom home on a wooded Hill Country lot by Dynamic Custom Homes',
				],
			],
		],

		'energy-efficient-homes' => [
			'eyebrow' => 'Energy-Efficient Homes',
			'title' => 'Energy-Efficient &amp; High-Performance Homes in Central Texas',
			'lede' => 'Lower bills, quieter rooms, and a healthier home. Dynamic Custom Homes builds high-performance homes across San Antonio and the Hill Country that go beyond code &mdash; with tighter building envelopes, better HVAC, and solar-ready and net-zero options.',
			'image' => '/wp-content/themes/dch-fse/assets/images/svc-energy-hero.webp',
			'image_alt' => 'Bright, high-performance custom home interior by Dynamic Custom Homes',
			'card_desc' => 'Go beyond code with tighter envelopes, high-efficiency HVAC, and solar-ready options &mdash; lower bills and a healthier home.',
			'card_image' => '/wp-content/themes/dch-fse/assets/images/project-8634-terra-mont-way.jpg',
			'blocks'    => [
				[
					'type'    => 'tiles',
					'eyebrow' => 'High-Performance Packages',
					'heading' => 'Comfort and efficiency built in from the framing up',
					'lede'    => 'Performance isn&rsquo;t an upgrade you bolt on later &mdash; it&rsquo;s engineered into the home from the start. Choose the level that fits your goals.',
					'tiles'   => [
						[ 'icon' => 'leaf', 'title' => 'Tight Building Envelope', 'desc' => 'Advanced insulation, air sealing, and high-performance windows keep conditioned air where it belongs.' ],
						[ 'icon' => 'smart', 'title' => 'High-Efficiency HVAC', 'desc' => 'Right-sized, zoned systems and fresh-air ventilation for even temperatures and better indoor air.' ],
						[ 'icon' => 'outdoor', 'title' => 'Solar-Ready &amp; Net-Zero', 'desc' => 'Optional solar pre-wiring, battery readiness, and net-zero tiers for the lowest possible operating cost.' ],
						[ 'icon' => 'budget', 'title' => 'Lower Operating Cost', 'desc' => 'A more efficient home costs less to run every month &mdash; and holds value as energy prices rise.' ],
					],
				],
				[
					'type'    => 'includes',
					'eyebrow' => 'What&rsquo;s Included',
					'heading' => 'Performance you can measure',
					'lede'    => 'Every high-performance build is documented and verified &mdash; not just promised.',
					'image'   => '/wp-content/themes/dch-fse/assets/images/svc-energy-detail.webp',
					'items'   => [
						'Blower-door tested air sealing',
						'Upgraded insulation and radiant barrier',
						'High-performance windows and doors',
						'Right-sized, high-SEER HVAC with fresh-air ventilation',
						'Solar-ready pre-wiring and optional net-zero design',
					],
				],
			],
		],

		'outdoor-living' => [
			'eyebrow' => 'Outdoor Living',
			'title' => 'Custom Outdoor Living, Casitas &amp; ADUs in Central Texas',
			'lede' => 'Extend your home past the back door. Dynamic Custom Homes builds outdoor kitchens, covered patios, casitas, ADUs, and detached shops across San Antonio and the Hill Country &mdash; the spaces that make Texas living year-round.',
			'image' => '/wp-content/themes/dch-fse/assets/images/svc-outdoor-hero.webp',
			'image_alt' => 'Custom pool and outdoor living at twilight by Dynamic Custom Homes',
			'card_desc' => 'Outdoor kitchens, covered patios, casitas, ADUs, and detached shops &mdash; built to the same standard as the main house.',
			'card_image' => '/wp-content/themes/dch-fse/assets/images/area-boerne.jpg',
			'blocks'    => [
				[
					'type'    => 'tiles',
					'eyebrow' => 'What We Build',
					'heading' => 'Living space that doesn&rsquo;t stop at the walls',
					'lede'    => 'Whether you&rsquo;re adding to a home we built or upgrading your current one, we bring full custom-home craft to every structure.',
					'tiles'   => [
						[ 'icon' => 'outdoor', 'title' => 'Outdoor Kitchens &amp; Patios', 'desc' => 'Covered patios, outdoor kitchens, fireplaces, and living areas designed for Texas weather.' ],
						[ 'icon' => 'bath', 'title' => 'Pool &amp; Spa Coordination', 'desc' => 'We coordinate pools, spas, and hardscape so your backyard comes together as one design.' ],
						[ 'icon' => 'whole', 'title' => 'Casitas &amp; ADUs', 'desc' => 'Guest houses and accessory dwelling units for family, guests, or rental income.' ],
						[ 'icon' => 'additions', 'title' => 'Detached Shops &amp; Garages', 'desc' => 'Workshops, oversized garages, and storage buildings that match your home&rsquo;s style.' ],
					],
				],
				[
					'type'    => 'split',
					'eyebrow' => 'Why Add On',
					'heading' => 'More home, more value, without moving',
					'body'    => '<p>Outdoor living and accessory structures are among the highest-impact ways to add usable space and value to a property &mdash; and they let you get more out of the home you already love.</p><p>Because we build them to the same standard as our custom homes, additions look intentional and original, never bolted on.</p>',
					'image'   => '/wp-content/themes/dch-fse/assets/images/svc-outdoor-detail.webp',
					'image_alt' => 'Backyard pool and spa built by Dynamic Custom Homes',
				],
			],
		],

		'warranty-home-care' => [
			'eyebrow' => 'Homeowner Care',
			'title' => 'Home Warranty &amp; Homeowner Care Program in Central Texas',
			'lede' => 'Our relationship doesn&rsquo;t end at handover. Every Dynamic Custom Homes build comes with a structured warranty and homeowner care program &mdash; defined response times, scheduled walkthroughs, and clear maintenance guidance for the life of your home.',
			'image' => '/wp-content/themes/dch-fse/assets/images/svc-warranty-hero.webp',
			'image_alt' => 'Finished custom home living room by Dynamic Custom Homes',
			'card_desc' => 'A formal warranty and care program &mdash; scheduled walkthroughs, defined response times, and maintenance guidance after move-in.',
			'card_image' => '/wp-content/themes/dch-fse/assets/images/about-52.jpg',
			'blocks'    => [
				[
					'type'    => 'process',
					'eyebrow' => 'Your First Year &amp; Beyond',
					'heading' => 'A schedule of check-ins, not a number you hope still works',
					'steps'   => [
						[ 'title' => '30-Day Walkthrough', 'desc' => 'We return a month after move-in to address settling items and answer questions as you live in the home.' ],
						[ 'title' => '6-Month Check-In', 'desc' => 'A mid-year review to catch anything that&rsquo;s shifted with the seasons and Texas weather.' ],
						[ 'title' => '11-Month Review', 'desc' => 'A thorough walkthrough before your first-year warranty period ends, so nothing slips through.' ],
						[ 'title' => 'Ongoing Care', 'desc' => 'Defined response times and maintenance guidance keep your home performing for years to come.' ],
					],
				],
				[
					'type'    => 'tiles',
					'eyebrow' => 'What&rsquo;s Covered',
					'heading' => 'Clear coverage, clear expectations',
					'lede'    => 'You&rsquo;ll know exactly what&rsquo;s covered and how quickly we respond &mdash; in writing, from day one.',
					'tiles'   => [
						[ 'icon' => 'shield', 'title' => 'Structural Warranty', 'desc' => 'Long-term coverage on the structural elements that matter most.' ],
						[ 'icon' => 'smart', 'title' => 'Systems &amp; Workmanship', 'desc' => 'Warranty coverage on the systems and craftsmanship throughout your home.' ],
						[ 'icon' => 'speed', 'title' => 'Defined Response Times', 'desc' => 'Clear expectations for how quickly we respond to warranty requests.' ],
						[ 'icon' => 'finishes', 'title' => 'Maintenance Guidance', 'desc' => 'A homeowner manual and seasonal guidance to protect your investment.' ],
					],
				],
			],
		],

		'selections-management' => [
			'eyebrow' => 'Selections &amp; Allowances',
			'title' => 'Allowance &amp; Selections Management for Custom Homes',
			'lede' => 'The fun part shouldn&rsquo;t be the overwhelming part. Our selections coordinator walks you through every finish, fixture, and upgrade with clear allowances and transparent pricing &mdash; so you always know what a choice costs before you make it.',
			'image' => '/wp-content/themes/dch-fse/assets/images/svc-selections-hero.webp',
			'image_alt' => 'Custom kitchen finishes and selections by Dynamic Custom Homes',
			'card_desc' => 'A guided selections process with clear allowances and real-time pricing &mdash; so finishes stay fun and your budget stays intact.',
			'card_image' => '/wp-content/themes/dch-fse/assets/images/project-graystone-circle.jpg',
			'blocks'    => [
				[
					'type'    => 'process',
					'eyebrow' => 'How Selections Work',
					'heading' => 'Every decision, guided and priced',
					'steps'   => [
						[ 'title' => 'Set Your Allowances', 'desc' => 'We establish clear, realistic allowances for every category up front, so your budget is built on real numbers.' ],
						[ 'title' => 'Guided Appointments', 'desc' => 'Your selections coordinator schedules focused appointments so choices never pile up all at once.' ],
						[ 'title' => 'Real-Time Pricing', 'desc' => 'See how each upgrade affects your budget the moment you choose it &mdash; no end-of-project surprises.' ],
						[ 'title' => 'Locked Selections', 'desc' => 'Once chosen, selections are documented and locked, keeping your schedule and budget on track.' ],
					],
				],
				[
					'type'    => 'split',
					'eyebrow' => 'Why It Matters',
					'heading' => 'Where budgets are usually won or lost',
					'body'    => '<p>Finishes and upgrades are where custom-home budgets most often slip. A structured selections process turns that risk into one of the most enjoyable parts of your build.</p><p>With controlled allowances and transparent pricing, you make confident decisions &mdash; and we protect both your budget and your timeline.</p>',
					'image'   => '/wp-content/themes/dch-fse/assets/images/svc-selections-detail.webp',
					'image_alt' => 'Custom fireplace finish detail in a Dynamic Custom Homes build',
				],
			],
		],

		'project-management' => [
			'eyebrow' => 'Project Management',
			'title' => 'Dedicated Project Management &amp; Client Portal',
			'lede' => 'Every Dynamic Custom Homes build gets a dedicated project manager and an online client portal &mdash; weekly updates, jobsite photos, schedule visibility, and one point of contact from groundbreaking to move-in.',
			'image' => '/wp-content/themes/dch-fse/assets/images/svc-pm-hero.webp',
			'image_alt' => 'Home study in a Dynamic Custom Homes custom build',
			'card_desc' => 'A dedicated PM and online portal for every build &mdash; weekly updates, photos, schedule visibility, and one point of contact.',
			'card_image' => '/wp-content/themes/dch-fse/assets/images/about-52.jpg',
			'blocks'    => [
				[
					'type'    => 'tiles',
					'eyebrow' => 'The Experience',
					'heading' => 'Always know where your home stands',
					'lede'    => 'Building a home shouldn&rsquo;t mean chasing your builder for answers. Our process keeps you informed at every step.',
					'tiles'   => [
						[ 'icon' => 'point', 'title' => 'A Single Point of Contact', 'desc' => 'One dedicated project manager who knows your build inside and out &mdash; no runaround.' ],
						[ 'icon' => 'vision', 'title' => 'Photo Timeline', 'desc' => 'Regular jobsite photos document your home&rsquo;s progress from foundation to finish.' ],
						[ 'icon' => 'speed', 'title' => 'Schedule Visibility', 'desc' => 'See upcoming milestones and what&rsquo;s happening next, updated as the build moves.' ],
						[ 'icon' => 'finishes', 'title' => 'Weekly Updates', 'desc' => 'Consistent written updates so you&rsquo;re never left wondering what&rsquo;s going on.' ],
					],
				],
				[
					'type'    => 'split',
					'eyebrow' => 'Why It Matters',
					'heading' => 'Communication is the difference',
					'body'    => '<p>Ask anyone who has built a home: the difference between a great experience and a stressful one usually comes down to communication.</p><p>Our client portal and dedicated project managers exist to give you visibility and peace of mind &mdash; and to let us take on your build with the organization it deserves.</p>',
					'image'   => '/wp-content/themes/dch-fse/assets/images/svc-pm-detail.webp',
					'image_alt' => 'Great room in a Dynamic Custom Homes custom home',
				],
			],
		],

		'renovation-additions' => [
			'eyebrow' => 'Renovation &amp; Additions',
			'title' => 'Whole-Home Renovations &amp; Large Additions in Central Texas',
			'lede' => 'For major transformations &mdash; whole-home renovations, second-story additions, and large-scale expansions &mdash; Dynamic Custom Homes brings new-build discipline to your existing home across San Antonio and the Hill Country.',
			'image' => '/wp-content/themes/dch-fse/assets/images/svc-renovation-hero.webp',
			'image_alt' => 'Transformed great room in a Dynamic Custom Homes renovation',
			'card_desc' => 'Whole-home renovations, second-story additions, and major expansions &mdash; new-build discipline applied to your existing home.',
			'card_image' => '/wp-content/themes/dch-fse/assets/images/project-graystone-circle.jpg',
			'blocks'    => [
				[
					'type'    => 'tiles',
					'eyebrow' => 'What We Take On',
					'heading' => 'The projects most remodelers won&rsquo;t',
					'lede'    => 'This is the big end of remodeling &mdash; structural, complex work that benefits from a custom-home builder&rsquo;s planning and crews.',
					'tiles'   => [
						[ 'icon' => 'whole', 'title' => 'Whole-Home Renovations', 'desc' => 'Top-to-bottom transformations that reimagine how an entire home lives.' ],
						[ 'icon' => 'additions', 'title' => 'Second-Story &amp; Large Additions', 'desc' => 'Adding square footage up or out &mdash; engineered and integrated to feel original.' ],
						[ 'icon' => 'materials', 'title' => 'Structural Reconfiguration', 'desc' => 'Moving walls, raising ceilings, and re-planning layouts to modern standards.' ],
						[ 'icon' => 'finishes', 'title' => 'Historic &amp; High-End Updates', 'desc' => 'Sensitive, high-quality updates for established and character homes.' ],
					],
				],
				[
					'type'    => 'split',
					'eyebrow' => 'How We&rsquo;re Different',
					'heading' => 'New-build discipline for a bigger remodel',
					'body'    => '<p>Large renovations and additions carry many of the same risks as building new &mdash; structural work, permitting, and long timelines. Treating them like a simple remodel is how projects go sideways.</p><p>Our renovation division applies the same planning, engineering coordination, and project management we bring to ground-up custom homes, so your major project stays on schedule and on budget.</p>',
					'image'   => '/wp-content/themes/dch-fse/assets/images/svc-renovation-detail.webp',
					'image_alt' => 'Open-concept living and kitchen in a Dynamic Custom Homes renovation',
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
		'kitchen' => '<svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="40" height="40" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12h20" /> <path d="M20 12v8a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-8" /> <path d="m4 8 16-4" /> <path d="m8.86 6.78-.45-1.81a2 2 0 0 1 1.45-2.43l1.94-.48a2 2 0 0 1 2.43 1.46l.45 1.8" /></svg>',
		'bath' => '<svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="40" height="40" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 4 8 6" /> <path d="M17 19v2" /> <path d="M2 12h20" /> <path d="M7 19v2" /> <path d="M9 5 7.621 3.621A2.121 2.121 0 0 0 4 5v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-5" /></svg>',
		'additions' => '<svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="40" height="40" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12.35 21H5a2 2 0 0 1-2-2v-9a2 2 0 0 1 .71-1.53l7-6a2 2 0 0 1 2.58 0l7 6A2 2 0 0 1 21 10v2.35" /> <path d="M14.8 12.4A1 1 0 0 0 14 12h-4a1 1 0 0 0-1 1v8" /> <path d="M15 18h6" /> <path d="M18 15v6" /></svg>',
		'whole' => '<svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="40" height="40" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 21v-8a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v8" /> <path d="M3 10a2 2 0 0 1 .709-1.528l7-6a2 2 0 0 1 2.582 0l7 6A2 2 0 0 1 21 10v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" /></svg>',
		'point' => '<svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="40" height="40" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="5" /> <path d="M20 21a8 8 0 0 0-16 0" /></svg>',
		'budget' => '<svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="40" height="40" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 7V4a1 1 0 0 0-1-1H5a2 2 0 0 0 0 4h15a1 1 0 0 1 1 1v4h-3a2 2 0 0 0 0 4h3a1 1 0 0 0 1-1v-2a1 1 0 0 0-1-1" /> <path d="M3 5v14a2 2 0 0 0 2 2h15a1 1 0 0 0 1-1v-4" /></svg>',
		'speed' => '<svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="40" height="40" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 14 4-4" /> <path d="M3.34 19a10 10 0 1 1 17.32 0" /></svg>',
		'vision' => '<svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="40" height="40" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0" /> <circle cx="12" cy="12" r="3" /></svg>',
		'materials' => '<svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="40" height="40" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12.83 2.18a2 2 0 0 0-1.66 0L2.6 6.08a1 1 0 0 0 0 1.83l8.58 3.91a2 2 0 0 0 1.66 0l8.58-3.9a1 1 0 0 0 0-1.83z" /> <path d="M2 12a1 1 0 0 0 .58.91l8.6 3.91a2 2 0 0 0 1.65 0l8.58-3.9A1 1 0 0 0 22 12" /> <path d="M2 17a1 1 0 0 0 .58.91l8.6 3.91a2 2 0 0 0 1.65 0l8.58-3.9A1 1 0 0 0 22 17" /></svg>',
		'finishes' => '<svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="40" height="40" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11.017 2.814a1 1 0 0 1 1.966 0l1.051 5.558a2 2 0 0 0 1.594 1.594l5.558 1.051a1 1 0 0 1 0 1.966l-5.558 1.051a2 2 0 0 0-1.594 1.594l-1.051 5.558a1 1 0 0 1-1.966 0l-1.051-5.558a2 2 0 0 0-1.594-1.594l-5.558-1.051a1 1 0 0 1 0-1.966l5.558-1.051a2 2 0 0 0 1.594-1.594z" /> <path d="M20 2v4" /> <path d="M22 4h-4" /> <circle cx="4" cy="20" r="2" /></svg>',
		'smart' => '<svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="40" height="40" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20v2" /> <path d="M12 2v2" /> <path d="M17 20v2" /> <path d="M17 2v2" /> <path d="M2 12h2" /> <path d="M2 17h2" /> <path d="M2 7h2" /> <path d="M20 12h2" /> <path d="M20 17h2" /> <path d="M20 7h2" /> <path d="M7 20v2" /> <path d="M7 2v2" /> <rect x="4" y="4" width="16" height="16" rx="2" /> <rect x="8" y="8" width="8" height="8" rx="1" /></svg>',
		'outdoor' => '<svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="40" height="40" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 10v.2A3 3 0 0 1 8.9 16H5a3 3 0 0 1-1-5.8V10a3 3 0 0 1 6 0Z" /> <path d="M7 16v6" /> <path d="M13 19v3" /> <path d="M12 19h8.3a1 1 0 0 0 .7-1.7L18 14h.3a1 1 0 0 0 .7-1.7L16 9h.2a1 1 0 0 0 .8-1.7L13 3l-1.4 1.5" /></svg>',
		'plan' => '<svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="40" height="40" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="7" x="3" y="3" rx="1" /> <rect width="9" height="7" x="3" y="14" rx="1" /> <rect width="5" height="7" x="16" y="14" rx="1" /></svg>',
		'lot' => '<svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="40" height="40" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m8 3 4 8 5-5 5 15H2L8 3z" /></svg>',
		'leaf' => '<svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="40" height="40" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z" /> <path d="M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12" /></svg>',
		'shield' => '<svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="40" height="40" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z" /> <path d="m9 12 2 2 4-4" /></svg>',
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
				<img src="<?php echo esc_url( $d['image'] ); ?>" alt="<?php echo esc_attr( $d['image_alt'] ); ?>" loading="eager" decoding="async" width="1600" height="900">
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
							<img src="<?php echo esc_url( $block['image'] ); ?>" alt="" loading="lazy" decoding="async" width="1000" height="1250">
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

/**
 * Render a linked index of every service (used on the /services/ page) so all
 * child pages are discoverable and internally linked. Cards pull the short
 * name (eyebrow) + card_desc from the same per-slug data map.
 */
function dch_fse_services_index_render(): string {
	$data  = dch_fse_service_child_data();
	$order = [
		'custom-home-building', 'design-build', 'semi-custom-homes', 'luxury-homes',
		'home-remodeling', 'renovation-additions', 'outdoor-living', 'energy-efficient-homes',
		'lot-consulting', 'selections-management', 'project-management', 'warranty-home-care',
	];

	ob_start();
	?>
	<section class="dch-services-index">
		<div class="dch-services-index__inner">
			<header class="dch-services-tiles__head" data-dch-anim="block">
				<p class="dch-services-tiles__eyebrow">What We Do</p>
				<h2 class="dch-services-tiles__heading">Explore our services</h2>
				<p class="dch-services-tiles__lede">From custom home building and design-build to remodels, outdoor living, and homeowner care &mdash; every service Dynamic Custom Homes offers across San Antonio and the Texas Hill Country.</p>
			</header>
			<div class="dch-services-index__grid" data-dch-anim="block">
				<?php foreach ( $order as $slug ) : ?>
					<?php if ( ! isset( $data[ $slug ] ) ) { continue; } ?>
					<?php $s = $data[ $slug ]; ?>
					<a class="dch-services-index__card" href="<?php echo esc_url( '/services/' . $slug . '/' ); ?>">
						<h3 class="dch-services-index__name"><?php echo $s['eyebrow']; ?></h3>
						<p class="dch-services-index__desc"><?php echo $s['card_desc'] ?? ''; ?></p>
						<span class="dch-services-index__arrow" aria-hidden="true">
							<svg viewBox="0 0 42 26" width="42" height="26"><line x1="2" y1="13" x2="40" y2="13" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><polyline points="30,3 40,13 30,23" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
						</span>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php
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

	register_block_type( 'dch/services-index', [
		'api_version'     => 3,
		'title'           => 'DCH Services Index',
		'category'        => 'theme',
		'render_callback' => 'dch_fse_services_index_render',
		'supports'        => [ 'html' => false ],
	] );
} );
