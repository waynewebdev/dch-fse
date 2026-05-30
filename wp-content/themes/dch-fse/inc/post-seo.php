<?php
/**
 * SEO meta box for blog posts and accessor functions for stored values.
 * Pages get their SEO from the registry's _dch_page_seo meta; only posts
 * use this UI.
 */

defined( 'ABSPATH' ) || exit;

const DCH_FSE_POST_SEO_TITLE_META  = '_dch_seo_title';
const DCH_FSE_POST_SEO_DESC_META   = '_dch_seo_description';
const DCH_FSE_POST_SEO_OG_META     = '_dch_seo_og_image';
const DCH_FSE_POST_SEO_ROBOTS_META = '_dch_seo_robots';

add_action( 'add_meta_boxes', static function (): void {
	add_meta_box(
		'dch_fse_seo',
		__( 'SEO', 'dch-fse' ),
		'dch_fse_render_post_seo_metabox',
		'post',
		'normal',
		'high'
	);
} );

function dch_fse_render_post_seo_metabox( WP_Post $post ): void {
	wp_nonce_field( 'dch_fse_post_seo_save', 'dch_fse_post_seo_nonce' );
	wp_enqueue_media();

	$title  = (string) get_post_meta( $post->ID, DCH_FSE_POST_SEO_TITLE_META,  true );
	$desc   = (string) get_post_meta( $post->ID, DCH_FSE_POST_SEO_DESC_META,   true );
	$og     = (string) get_post_meta( $post->ID, DCH_FSE_POST_SEO_OG_META,     true );
	$robots = (string) get_post_meta( $post->ID, DCH_FSE_POST_SEO_ROBOTS_META, true );
	if ( '' === $robots ) {
		$robots = 'index,follow';
	}
	?>
	<style>
		.dch-fse-seo-row { margin: 1em 0; }
		.dch-fse-seo-row label { display: block; font-weight: 600; margin-bottom: 0.25em; }
		.dch-fse-seo-row input[type=text], .dch-fse-seo-row textarea, .dch-fse-seo-row select { width: 100%; max-width: 720px; }
		.dch-fse-seo-row .description { color: #646970; font-size: 12px; margin-top: 0.25em; }
		.dch-fse-seo-og-preview { max-width: 240px; height: auto; margin-top: 0.5em; display: block; }
		.dch-fse-seo-og-preview:empty { display: none; }
	</style>

	<div class="dch-fse-seo-row">
		<label for="dch_fse_seo_title"><?php esc_html_e( 'SEO Title', 'dch-fse' ); ?></label>
		<input type="text" id="dch_fse_seo_title" name="dch_fse_seo_title" value="<?php echo esc_attr( $title ); ?>" maxlength="120" />
		<p class="description"><?php esc_html_e( 'Defaults to: post title + site name.', 'dch-fse' ); ?></p>
	</div>

	<div class="dch-fse-seo-row">
		<label for="dch_fse_seo_description"><?php esc_html_e( 'SEO Description', 'dch-fse' ); ?></label>
		<textarea id="dch_fse_seo_description" name="dch_fse_seo_description" rows="3" maxlength="320"><?php echo esc_textarea( $desc ); ?></textarea>
		<p class="description"><?php esc_html_e( 'Defaults to the post excerpt or first paragraph (≈155 chars).', 'dch-fse' ); ?></p>
	</div>

	<div class="dch-fse-seo-row">
		<label for="dch_fse_seo_og_image"><?php esc_html_e( 'OG Image', 'dch-fse' ); ?></label>
		<input type="text" id="dch_fse_seo_og_image" name="dch_fse_seo_og_image" value="<?php echo esc_attr( $og ); ?>" />
		<button type="button" class="button" id="dch_fse_seo_og_image_select"><?php esc_html_e( 'Choose image', 'dch-fse' ); ?></button>
		<button type="button" class="button-link" id="dch_fse_seo_og_image_clear"><?php esc_html_e( 'Clear', 'dch-fse' ); ?></button>
		<img id="dch_fse_seo_og_image_preview" class="dch-fse-seo-og-preview" src="<?php echo esc_url( $og ); ?>" alt="" />
		<p class="description"><?php esc_html_e( 'Defaults to the featured image, then the site default OG image.', 'dch-fse' ); ?></p>
	</div>

	<div class="dch-fse-seo-row">
		<label for="dch_fse_seo_robots"><?php esc_html_e( 'Robots', 'dch-fse' ); ?></label>
		<select id="dch_fse_seo_robots" name="dch_fse_seo_robots">
			<?php foreach ( [ 'index,follow', 'noindex,follow', 'noindex,nofollow' ] as $opt ) : ?>
				<option value="<?php echo esc_attr( $opt ); ?>" <?php selected( $robots, $opt ); ?>><?php echo esc_html( $opt ); ?></option>
			<?php endforeach; ?>
		</select>
	</div>

	<script>
	( function ( $ ) {
		const input   = document.getElementById( 'dch_fse_seo_og_image' );
		const preview = document.getElementById( 'dch_fse_seo_og_image_preview' );
		let frame;
		document.getElementById( 'dch_fse_seo_og_image_select' ).addEventListener( 'click', function ( e ) {
			e.preventDefault();
			if ( frame ) { frame.open(); return; }
			frame = wp.media( { title: '<?php echo esc_js( __( 'Select OG image', 'dch-fse' ) ); ?>', button: { text: '<?php echo esc_js( __( 'Use this image', 'dch-fse' ) ); ?>' }, multiple: false } );
			frame.on( 'select', function () {
				const att = frame.state().get( 'selection' ).first().toJSON();
				input.value   = att.url;
				preview.src   = att.url;
				preview.style.display = 'block';
			} );
			frame.open();
		} );
		document.getElementById( 'dch_fse_seo_og_image_clear' ).addEventListener( 'click', function ( e ) {
			e.preventDefault();
			input.value = '';
			preview.src = '';
			preview.style.display = 'none';
		} );
		if ( ! input.value ) { preview.style.display = 'none'; }
	} )( jQuery );
	</script>
	<?php
}

add_action( 'save_post_post', static function ( int $post_id ): void {
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $post_id ) ) {
		return;
	}
	if ( ! isset( $_POST['dch_fse_post_seo_nonce'] ) ) {
		return;
	}
	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['dch_fse_post_seo_nonce'] ) ), 'dch_fse_post_seo_save' ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$title  = isset( $_POST['dch_fse_seo_title'] )       ? sanitize_text_field( wp_unslash( $_POST['dch_fse_seo_title'] ) )       : '';
	$desc   = isset( $_POST['dch_fse_seo_description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['dch_fse_seo_description'] ) ) : '';
	$og     = isset( $_POST['dch_fse_seo_og_image'] )    ? esc_url_raw( wp_unslash( $_POST['dch_fse_seo_og_image'] ) )            : '';
	$robots = isset( $_POST['dch_fse_seo_robots'] )      ? sanitize_text_field( wp_unslash( $_POST['dch_fse_seo_robots'] ) )      : 'index,follow';
	if ( ! in_array( $robots, [ 'index,follow', 'noindex,follow', 'noindex,nofollow' ], true ) ) {
		$robots = 'index,follow';
	}

	foreach ( [
		DCH_FSE_POST_SEO_TITLE_META  => $title,
		DCH_FSE_POST_SEO_DESC_META   => $desc,
		DCH_FSE_POST_SEO_OG_META     => $og,
		DCH_FSE_POST_SEO_ROBOTS_META => $robots,
	] as $key => $value ) {
		if ( '' === $value ) {
			delete_post_meta( $post_id, $key );
		} else {
			update_post_meta( $post_id, $key, $value );
		}
	}
} );

/* -------------------------------------------------------------------------
 *  Accessors  (used by inc/seo.php)
 * ------------------------------------------------------------------------- */

function dch_fse_post_seo_title( int $post_id ): string {
	$saved = (string) get_post_meta( $post_id, DCH_FSE_POST_SEO_TITLE_META, true );
	if ( '' !== $saved ) {
		return $saved;
	}
	$site_name = (string) ( dch_fse_site( 'name' ) ?: get_bloginfo( 'name' ) );
	return get_the_title( $post_id ) . ' | ' . $site_name;
}

function dch_fse_post_seo_description( int $post_id ): string {
	$saved = (string) get_post_meta( $post_id, DCH_FSE_POST_SEO_DESC_META, true );
	if ( '' !== $saved ) {
		return $saved;
	}

	$post = get_post( $post_id );
	if ( ! $post ) {
		return '';
	}

	if ( '' !== $post->post_excerpt ) {
		return wp_trim_words( wp_strip_all_tags( $post->post_excerpt ), 28, '…' );
	}

	$content = wp_strip_all_tags( strip_shortcodes( $post->post_content ) );
	$paragraphs = preg_split( '/\R{2,}/', trim( $content ) ) ?: [ $content ];
	$first      = trim( $paragraphs[0] ?? '' );
	if ( '' === $first ) {
		return '';
	}
	return wp_trim_words( $first, 28, '…' );
}

function dch_fse_post_seo_og_image( int $post_id ): string {
	$saved = (string) get_post_meta( $post_id, DCH_FSE_POST_SEO_OG_META, true );
	if ( '' !== $saved ) {
		return $saved;
	}
	$thumb_id = get_post_thumbnail_id( $post_id );
	if ( $thumb_id ) {
		$src = wp_get_attachment_image_url( $thumb_id, 'full' );
		if ( $src ) {
			return $src;
		}
	}
	$default = dch_fse_site( 'default_og_image' );
	return is_string( $default ) ? $default : '';
}

function dch_fse_post_seo_robots( int $post_id ): string {
	$saved = (string) get_post_meta( $post_id, DCH_FSE_POST_SEO_ROBOTS_META, true );
	return '' !== $saved ? $saved : 'index,follow';
}
