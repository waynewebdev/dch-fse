<?php
/**
 * Dynamic single-testimonial block (dch/testimonial).
 *
 * Renders the `.dch-testimonial` section, but picks WHICH testimonial to show
 * based on the current page, so the same static "Janie Jarrett" quote no longer
 * appears on every page. The pick is deterministic per page (stable across
 * loads — good for caching/SEO) yet spreads the set across the many pages that
 * share a template (12 service children, 7 area children).
 *
 * The homepage keeps its own static testimonial in front-page.html; this block
 * powers About, Services, the Services child template, Areas We Serve, and the
 * Areas child template.
 */

defined( 'ABSPATH' ) || exit;

/**
 * The testimonial pool. Quotes mirror the homepage `.dch-reviews` cards plus the
 * original Janie Jarrett quote; images are real DCH photography under
 * assets/images/ (testimonial-1 was already in use; 2–4 were added for this).
 */
function dch_fse_testimonials(): array {
	$img = '/wp-content/themes/dch-fse/assets/images/';
	return [
		[
			'quote'   => 'Building with Dynamic Custom Homes was an amazing experience. They made me feel comfortable and listened to my ideas, my visions, and executed things beautifully. I couldn’t be happier with our home and would recommend them to anyone.',
			'name'    => 'Janie Jarrett',
			'company' => 'San Antonio Homeowner',
			'image'   => $img . 'testimonial-1.jpg',
			'alt'     => 'Custom home built by Dynamic Custom Homes',
		],
		[
			'quote'   => 'My experience with Dynamic Custom Homes was outstanding. Josh’s integrity, honesty, and work ethic truly sets him apart from other builders. From start to finish, he was transparent, dependable, and committed to doing things the right way. The quality of workmanship and detail exceeded our expectations.',
			'name'    => 'Jason Williams',
			'company' => 'Custom Home Client',
			'image'   => $img . 'testimonial-2.jpg',
			'alt'     => 'Modern Hill Country custom home at dusk by Dynamic Custom Homes',
		],
		[
			'quote'   => 'Definitely the best choice for your next home build! Dynamic Custom Homes built a home for me and made what could have been a stressful event seem effortless. I highly recommend Dynamic Custom Homes to anyone wanting to build their dream home.',
			'name'    => 'Brittany Engelke',
			'company' => 'Custom Home Client',
			'image'   => $img . 'testimonial-3.jpg',
			'alt'     => 'Open-concept great room and kitchen in a Dynamic Custom Homes build',
		],
		[
			'quote'   => 'Dynamic Custom Homes is a very responsible builder who works and communicates very well with its clients. I would recommend them to anyone looking for a quality custom home builder in San Antonio.',
			'name'    => 'Houzz Reviewer',
			'company' => 'Verified Houzz Review',
			'image'   => $img . 'testimonial-4.jpg',
			'alt'     => 'Custom master bedroom by Dynamic Custom Homes',
		],
	];
}

/** Five identical filled stars for the testimonial rating. */
function dch_fse_testimonial_stars(): string {
	$star = '<svg class="dch-testimonial__star" viewBox="0 0 1792 1792" aria-hidden="true"><path fill="currentColor" d="M1728 647q0 22-26 48l-363 354 86 500q1 7 1 20 0 21-10.5 35.5t-30.5 14.5q-19 0-40-12l-449-236-449 236q-22 12-40 12-21 0-31.5-14.5t-10.5-35.5q0-6 2-20l86-500-364-354q-25-27-25-48 0-37 56-46l502-73 225-455q19-41 49-41t49 41l225 455 502 73q56 9 56 46z"/></svg>';
	return str_repeat( $star, 5 );
}

/**
 * Choose a testimonial for the current page and render the section. Uses the
 * queried page ID modulo the pool size for a stable, well-spread assignment;
 * falls back to the first entry when there is no queried object.
 */
function dch_fse_testimonial_render(): string {
	$list  = dch_fse_testimonials();
	$count = count( $list );
	if ( 0 === $count ) {
		return '';
	}
	$id    = (int) get_queried_object_id();
	$index = $id > 0 ? $id % $count : 0;
	$t     = $list[ $index ];

	$stars = dch_fse_testimonial_stars();
	$quote = wp_kses_post( $t['quote'] );
	$name  = esc_html( $t['name'] );
	$comp  = esc_html( $t['company'] );
	$src   = esc_url( $t['image'] );
	$alt   = esc_attr( $t['alt'] );

	return <<<HTML
	<section class="dch-testimonial">
		<div class="dch-testimonial__inner">

			<div class="dch-testimonial__copy" data-dch-anim="block">
				<div class="dch-testimonial__stars" role="img" aria-label="5 out of 5 stars">
					{$stars}
				</div>
				<blockquote class="dch-testimonial__quote">
					{$quote}
				</blockquote>
				<footer class="dch-testimonial__person">
					<p class="dch-testimonial__name">{$name}</p>
					<p class="dch-testimonial__company">{$comp}</p>
				</footer>
			</div>

			<figure class="dch-testimonial__media" data-dch-anim="block">
				<img src="{$src}"
					alt="{$alt}"
					width="1920" height="1230"
					loading="lazy" decoding="async">
			</figure>

		</div>
	</section>
	HTML;
}

add_action( 'init', static function (): void {
	register_block_type( 'dch/testimonial', [
		'api_version'     => 3,
		'title'           => 'DCH Testimonial',
		'category'        => 'theme',
		'render_callback' => 'dch_fse_testimonial_render',
		'supports'        => [ 'html' => false ],
	] );
} );
