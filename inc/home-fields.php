<?php
/**
 * Native (no ACF/Gutenberg) editable fields for the Home page — see
 * CONTENT_MODEL.md's "HOME" section for the full field-by-field rules this
 * file implements. Every meta box here is scoped to whichever Page is set
 * as WordPress's front page (Settings -> Reading), not to Pages in
 * general — front-page.php renders whatever that Page is, so its content
 * fields belong there, not to a fixed hardcoded post ID.
 *
 * Defaults for every plain text field match the copy already hardcoded in
 * template-parts/home/*.php verbatim — so a fresh install (no editor has
 * touched anything yet) renders identically to before this file existed;
 * `get_post_meta()` returns a field's `register_post_meta() 'default'`
 * until a real value is saved.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Identifies the "Home" Page by slug ('home'), NOT `get_option('page_on_front')`
 * — this project's front-page.php already serves "/" unconditionally
 * regardless of the Reading Settings front-page mode (see CLAUDE.md), and
 * in practice Reading Settings here is left at "Your latest posts" with no
 * static front page configured at all. A real Page still needs to exist
 * for these fields to have somewhere to live in wp-admin — same by-slug
 * pattern already used for Fale Conosco
 * (rosa_branca_is_editing_fale_conosco_page(), inc/fale-conosco-fields.php),
 * not tied to Reading Settings either way.
 */
function rosa_branca_home_page_id(): int {
	static $id = null;

	if ( null === $id ) {
		$page = get_page_by_path( 'home' );
		$id   = $page ? $page->ID : 0;
	}

	return $id;
}

function rosa_branca_is_editing_home_page( int $post_id ): bool {
	$home_id = rosa_branca_home_page_id();
	return $home_id > 0 && $home_id === $post_id;
}

/**
 * Registers every Home field as post meta on the `page` post type. Not
 * scoped to the specific Home post ID (register_post_meta() only takes a
 * post *type*) — the meta boxes/save handler below are what actually
 * restrict these to the one Page that's set as the front page; every
 * other Page simply never shows or writes them.
 */
function rosa_branca_register_home_meta(): void {
	$text_fields = array(
		'rosa_branca_recipes_intro_title'  => __( 'Lorem ipsum dolor sit amet', 'rosa-branca' ),
		'rosa_branca_recipes_intro_text'   => __( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse ut massa neque. Etiam egestas magna sit amet elit accumsan tristique in ut nunc. Sed porta, ex eget ornare facilisis, dui libero bibendum enim, vitae sodales turpis ex vel nisi. Phasellus felis odio, egestas sed elit in, finibus dapibus dui. Integer purus nunc, hendrerit eu odio nec, bibendum fringilla erat. Quisque condimentum lectus nec hendrerit ullamcorper. Proin vestibulum eros sit amet diam feugiat rhoncus.', 'rosa-branca' ),
		'rosa_branca_products_intro_title' => __( 'Lorem ipsum dolor sit amet', 'rosa-branca' ),
		'rosa_branca_products_intro_text'  => __( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse ut massa neque. Etiam egestas magna sit amet elit accumsan tristique in ut nunc. Sed porta, ex eget ornare facilisis, dui libero bibendum enim, vitae sodales turpis ex vel nisi. Phasellus felis odio, egestas sed elit in, finibus dapibus dui. Integer purus nunc, hendrerit eu odio nec, bibendum fringilla erat. Quisque condimentum lectus nec hendrerit ullamcorper. Proin vestibulum eros sit amet diam feugiat rhoncus.', 'rosa-branca' ),
		'rosa_branca_about_title'          => __( 'Lorem ipsum dolor sit amet', 'rosa-branca' ),
		'rosa_branca_about_text'           => __( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse ut massa neque. Etiam egestas magna sit amet elit accumsan tristique in ut nunc. Sed porta, ex eget ornare facilisis, dui libero bibendum enim, vitae sodales turpis ex vel nisi. Phasellus felis odio, egestas sed elit in, finibus dapibus dui. Integer purus nunc, hendrerit eu odio nec, bibendum fringilla erat. Quisque condimentum lectus nec hendrerit ullamcorper. Proin vestibulum eros sit amet diam feugiat rhoncus.', 'rosa-branca' ),
		'rosa_branca_find_us_title'        => __( 'Encontre Rosa Branca', 'rosa-branca' ),
		'rosa_branca_find_us_subtitle'     => __( 'Onde comprar os produtos Rosa Branca?', 'rosa-branca' ),
	);

	foreach ( $text_fields as $key => $default ) {
		$is_textarea = str_ends_with( $key, '_text' );
		register_post_meta(
			'page',
			$key,
			array(
				'type'              => 'string',
				'single'            => true,
				'default'           => $default,
				'show_in_rest'      => true,
				'sanitize_callback' => $is_textarea ? 'rosa_branca_sanitize_textarea' : 'rosa_branca_sanitize_text',
				'auth_callback'     => static fn(): bool => current_user_can( 'edit_pages' ),
			)
		);
	}

	$url_fields = array( 'rosa_branca_recipes_intro_link', 'rosa_branca_products_intro_link', 'rosa_branca_about_link' );
	foreach ( $url_fields as $key ) {
		register_post_meta(
			'page',
			$key,
			array(
				'type'              => 'string',
				'single'            => true,
				'default'           => '',
				'show_in_rest'      => true,
				'sanitize_callback' => 'rosa_branca_sanitize_optional_url',
				'auth_callback'     => static fn(): bool => current_user_can( 'edit_pages' ),
			)
		);
	}

	register_post_meta(
		'page',
		'rosa_branca_about_photo_id',
		array(
			'type'              => 'integer',
			'single'            => true,
			'default'           => 0,
			'show_in_rest'      => true,
			'sanitize_callback' => 'absint',
			'auth_callback'     => static fn(): bool => current_user_can( 'edit_pages' ),
		)
	);

	// No default here: the current 5 identical placeholder slides only
	// make sense as real *content* (an editor's actual banners), not as a
	// meta default — `rosa_branca_home_banner_slides()` below is what
	// falls back to today's hardcoded copy when this is still empty.
	register_post_meta(
		'page',
		'rosa_branca_banner_slides',
		array(
			'type'              => 'array',
			'single'            => true,
			'default'           => array(),
			'show_in_rest'      => false,
			'sanitize_callback' => 'rosa_branca_sanitize_banner_slides',
			'auth_callback'     => static fn(): bool => current_user_can( 'edit_pages' ),
		)
	);
}
add_action( 'init', 'rosa_branca_register_home_meta' );

/**
 * Front-end read helper: real slides if any have been configured, else the
 * single hardcoded placeholder slide (today's exact copy) repeated 5x —
 * same fallback shape hero.php already had, now centralized so the
 * template doesn't need its own placeholder-vs-real branching.
 */
function rosa_branca_home_banner_slides(): array {
	$home_id = rosa_branca_home_page_id();
	$slides  = $home_id ? get_post_meta( $home_id, 'rosa_branca_banner_slides', true ) : array();

	if ( ! empty( $slides ) ) {
		return $slides;
	}

	return array_fill(
		0,
		5,
		array(
			'title'    => __( 'Lorem ipsum dolor sit amet', 'rosa-branca' ),
			'text'     => __( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse ut massa neque. Etiam egestas magna sit amet elit accumsan tristique in ut nunc.', 'rosa-branca' ),
			'link'     => home_url( '/receitas/' ),
			'image_id' => 0, // 0 => rosa_branca_dynamic_picture() falls back; hero.php uses the static 'banner-home' asset when image_id is 0.
		)
	);
}

function rosa_branca_add_home_meta_boxes( string $post_type, \WP_Post $post ): void {
	if ( 'page' !== $post_type || ! rosa_branca_is_editing_home_page( $post->ID ) ) {
		return;
	}

	add_meta_box( 'rb_home_banner', __( 'Home — Banner (Hero)', 'rosa-branca' ), 'rosa_branca_render_home_banner_box', 'page', 'normal', 'high' );
	add_meta_box( 'rb_home_recipes', __( 'Home — Seção Receitas', 'rosa-branca' ), 'rosa_branca_render_home_recipes_box', 'page', 'normal', 'default' );
	add_meta_box( 'rb_home_products', __( 'Home — Seção Produtos', 'rosa-branca' ), 'rosa_branca_render_home_products_box', 'page', 'normal', 'default' );
	add_meta_box( 'rb_home_about', __( 'Home — Sobre a Marca', 'rosa-branca' ), 'rosa_branca_render_home_about_box', 'page', 'normal', 'default' );
	add_meta_box( 'rb_home_find_us', __( 'Home — Encontre Rosa Branca', 'rosa-branca' ), 'rosa_branca_render_home_find_us_box', 'page', 'normal', 'default' );
}
add_action( 'add_meta_boxes', 'rosa_branca_add_home_meta_boxes', 10, 2 );

function rosa_branca_render_home_recipes_box( \WP_Post $post ): void {
	rosa_branca_admin_fields_nonce_field();
	rosa_branca_admin_text_row( 'rosa_branca_recipes_intro_title', __( 'Título da seção', 'rosa-branca' ), get_post_meta( $post->ID, 'rosa_branca_recipes_intro_title', true ) );
	rosa_branca_admin_textarea_row( 'rosa_branca_recipes_intro_text', __( 'Parágrafo', 'rosa-branca' ), get_post_meta( $post->ID, 'rosa_branca_recipes_intro_text', true ) );
	rosa_branca_admin_url_row( 'rosa_branca_recipes_intro_link', __( 'Link do botão', 'rosa-branca' ), get_post_meta( $post->ID, 'rosa_branca_recipes_intro_link', true ) );
}

function rosa_branca_render_home_products_box( \WP_Post $post ): void {
	rosa_branca_admin_fields_nonce_field();
	rosa_branca_admin_text_row( 'rosa_branca_products_intro_title', __( 'Título da seção', 'rosa-branca' ), get_post_meta( $post->ID, 'rosa_branca_products_intro_title', true ) );
	rosa_branca_admin_textarea_row( 'rosa_branca_products_intro_text', __( 'Parágrafo', 'rosa-branca' ), get_post_meta( $post->ID, 'rosa_branca_products_intro_text', true ) );
	rosa_branca_admin_url_row( 'rosa_branca_products_intro_link', __( 'Link do botão', 'rosa-branca' ), get_post_meta( $post->ID, 'rosa_branca_products_intro_link', true ) );
}

function rosa_branca_render_home_about_box( \WP_Post $post ): void {
	rosa_branca_admin_fields_nonce_field();
	rosa_branca_admin_text_row( 'rosa_branca_about_title', __( 'Título', 'rosa-branca' ), get_post_meta( $post->ID, 'rosa_branca_about_title', true ) );
	rosa_branca_admin_textarea_row( 'rosa_branca_about_text', __( 'Parágrafo', 'rosa-branca' ), get_post_meta( $post->ID, 'rosa_branca_about_text', true ) );
	rosa_branca_admin_url_row( 'rosa_branca_about_link', __( 'Link do botão', 'rosa-branca' ), get_post_meta( $post->ID, 'rosa_branca_about_link', true ) );
	rosa_branca_admin_image_row( 'rosa_branca_about_photo_id', __( 'Foto', 'rosa-branca' ), (int) get_post_meta( $post->ID, 'rosa_branca_about_photo_id', true ) );
}

function rosa_branca_render_home_find_us_box( \WP_Post $post ): void {
	rosa_branca_admin_fields_nonce_field();
	rosa_branca_admin_text_row( 'rosa_branca_find_us_title', __( 'Título', 'rosa-branca' ), get_post_meta( $post->ID, 'rosa_branca_find_us_title', true ) );
	rosa_branca_admin_text_row( 'rosa_branca_find_us_subtitle', __( 'Subtítulo', 'rosa-branca' ), get_post_meta( $post->ID, 'rosa_branca_find_us_subtitle', true ) );
}

/**
 * Repeater UI: existing rows + one hidden <template> (assets/admin/
 * meta-boxes.js clones it on "Adicionar slide"). Field names use plain
 * bracket-array notation (`rosa_branca_banner_slides[0][title]`, ...) —
 * PHP's own $_POST parsing turns that into a real nested array with zero
 * custom JS serialization needed; rosa_branca_sanitize_banner_slides()
 * (inc/content-fields.php) is what actually validates/sanitizes it on save.
 */
function rosa_branca_render_home_banner_box( \WP_Post $post ): void {
	rosa_branca_admin_fields_nonce_field();
	$slides = get_post_meta( $post->ID, 'rosa_branca_banner_slides', true );
	if ( ! is_array( $slides ) ) {
		$slides = array();
	}
	?>
	<div data-rb-repeater="rosa_branca_banner_slides">
		<div data-rb-repeater-rows>
			<?php foreach ( $slides as $index => $slide ) : ?>
				<?php rosa_branca_render_banner_slide_row( $index, $slide ); ?>
			<?php endforeach; ?>
		</div>
		<template data-rb-repeater-template>
			<?php rosa_branca_render_banner_slide_row( '__INDEX__', array() ); ?>
		</template>
		<p><button type="button" class="button" data-rb-repeater-add><?php esc_html_e( 'Adicionar slide', 'rosa-branca' ); ?></button></p>
	</div>
	<?php
}

function rosa_branca_render_banner_slide_row( $index, array $slide ): void {
	$prefix = "rosa_branca_banner_slides[{$index}]";
	?>
	<div class="rb-repeater-row" data-rb-repeater-row>
		<button type="button" class="button-link rb-repeater-row__remove" data-rb-repeater-remove><?php esc_html_e( 'Remover slide', 'rosa-branca' ); ?></button>
		<?php rosa_branca_admin_text_row( "{$prefix}[title]", __( 'Título', 'rosa-branca' ), $slide['title'] ?? '' ); ?>
		<?php rosa_branca_admin_textarea_row( "{$prefix}[text]", __( 'Texto', 'rosa-branca' ), $slide['text'] ?? '' ); ?>
		<?php rosa_branca_admin_url_row( "{$prefix}[link]", __( 'Link do botão', 'rosa-branca' ), $slide['link'] ?? '' ); ?>
		<?php rosa_branca_admin_image_row( "{$prefix}[image_id]", __( 'Imagem', 'rosa-branca' ), (int) ( $slide['image_id'] ?? 0 ) ); ?>
	</div>
	<?php
}

function rosa_branca_save_home_meta( int $post_id ): void {
	if ( ! rosa_branca_admin_fields_nonce_is_valid() || ! rosa_branca_is_editing_home_page( $post_id ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_page', $post_id ) ) {
		return;
	}

	$text_keys = array(
		'rosa_branca_recipes_intro_title',
		'rosa_branca_recipes_intro_text',
		'rosa_branca_recipes_intro_link',
		'rosa_branca_products_intro_title',
		'rosa_branca_products_intro_text',
		'rosa_branca_products_intro_link',
		'rosa_branca_about_title',
		'rosa_branca_about_text',
		'rosa_branca_about_link',
		'rosa_branca_about_photo_id',
		'rosa_branca_find_us_title',
		'rosa_branca_find_us_subtitle',
	);

	foreach ( $text_keys as $key ) {
		if ( isset( $_POST[ $key ] ) ) {
			// sanitize_meta() (via each field's own register_post_meta()
			// sanitize_callback) runs automatically inside
			// update_post_meta() — the raw $_POST value is safe to pass
			// through unsanitized here, same as core does for any
			// registered meta field.
			update_post_meta( $post_id, $key, wp_unslash( $_POST[ $key ] ) );
		}
	}

	if ( isset( $_POST['rosa_branca_banner_slides'] ) && is_array( $_POST['rosa_branca_banner_slides'] ) ) {
		update_post_meta( $post_id, 'rosa_branca_banner_slides', wp_unslash( $_POST['rosa_branca_banner_slides'] ) );
	} else {
		update_post_meta( $post_id, 'rosa_branca_banner_slides', array() );
	}
}
add_action( 'save_post_page', 'rosa_branca_save_home_meta' );
