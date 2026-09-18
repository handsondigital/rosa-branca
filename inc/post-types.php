<?php
/**
 * Recipes ("receita") and Products ("produto") custom post types.
 *
 * Native WordPress CPT + Featured Image, per ARCHITECTURE_PLAN.md's explicit
 * constraint against ACF/Elementor/Gutenberg/Headless as the frontend base
 * (see that file's "A arquitetura deve evitar... não deve utilizar...
 * ACF... como base do frontend"). `receita`'s two custom fields
 * (dificuldade/tempo_preparo — CONTENT_MODEL.md) are the only ones decided
 * so far; `produto` has none beyond core (nothing on the front-end needs
 * more yet). Whatever gets decided later for `produto` is additive (more
 * `supports`, or a `register_post_meta()` call), not a rearchitecture.
 *
 * `public => false` / `show_ui => true`: no single/archive templates or
 * routes exist yet (PAGES_PLAN.md §15 scopes the current delivery to Home +
 * Fale Conosco only — /receitas/ and /produtos/ aren't real pages yet, the
 * Home teasers link to those URLs as placeholders). This still lets editors
 * create posts and upload featured images in wp-admin today — which is what
 * exercises the image pipeline below — without opening front-end routes
 * PAGES_PLAN.md doesn't cover yet. Flip `public` to true and add rewrite/
 * archive support in the same PR that adds real templates for these.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function rosa_branca_register_post_types(): void {
	register_post_type(
		'receita',
		array(
			'labels'       => array(
				'name'          => __( 'Receitas', 'rosa-branca' ),
				'singular_name' => __( 'Receita', 'rosa-branca' ),
				'add_new_item'  => __( 'Adicionar Receita', 'rosa-branca' ),
				'edit_item'     => __( 'Editar Receita', 'rosa-branca' ),
			),
			'public'       => false,
			'show_ui'      => true,
			'show_in_menu' => true,
			'menu_icon'    => 'dashicons-carrot',
			'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
			'has_archive'  => false,
		)
	);

	register_post_type(
		'produto',
		array(
			'labels'       => array(
				'name'          => __( 'Produtos', 'rosa-branca' ),
				'singular_name' => __( 'Produto', 'rosa-branca' ),
				'add_new_item'  => __( 'Adicionar Produto', 'rosa-branca' ),
				'edit_item'     => __( 'Editar Produto', 'rosa-branca' ),
			),
			'public'       => false,
			'show_ui'      => true,
			'show_in_menu' => true,
			'menu_icon'    => 'dashicons-carrot',
			'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
			'has_archive'  => false,
		)
	);
}
add_action( 'init', 'rosa_branca_register_post_types' );

/**
 * Both sanitize callbacks (rosa_branca_sanitize_dificuldade/
 * rosa_branca_sanitize_tempo_preparo) live in inc/content-fields.php, not
 * here — that file has no side-effecting top-level code (no add_action()/
 * register_post_type() calls), so tests/ReceitaMetaTest.php can require it
 * directly without needing WP_Mock stubs for the registration functions
 * this file calls. Only the wiring (register_post_meta() below) lives here.
 */
function rosa_branca_register_post_meta(): void {
	register_post_meta(
		'receita',
		'dificuldade',
		array(
			'type'              => 'string',
			'single'            => true,
			'default'           => 'facil',
			'show_in_rest'      => true,
			'sanitize_callback' => 'rosa_branca_sanitize_dificuldade',
			'auth_callback'     => static fn(): bool => current_user_can( 'edit_posts' ),
		)
	);

	register_post_meta(
		'receita',
		'tempo_preparo',
		array(
			'type'              => 'string',
			'single'            => true,
			'default'           => '',
			'show_in_rest'      => true,
			'sanitize_callback' => 'rosa_branca_sanitize_tempo_preparo',
			'auth_callback'     => static fn(): bool => current_user_can( 'edit_posts' ),
		)
	);
}
add_action( 'init', 'rosa_branca_register_post_meta' );

/**
 * Featured-image crop sizes matching the two real display contexts already
 * live on Home (carousel.css / recipe-card, home-sections.css / product
 * media) — 380px recipe cards, 322px product images (see build-images.mjs's
 * own widths for the current static placeholders, same numbers). `false`
 * (no hard crop): scales to fit the width, preserving the source's own
 * aspect ratio — same behaviour as build-images.mjs's sharp .resize({width}),
 * since neither card design specifies a fixed crop ratio in Figma yet.
 */
function rosa_branca_register_image_sizes(): void {
	add_image_size( 'receita-card', 380, 9999, false );
	add_image_size( 'produto-card', 322, 9999, false );
}
add_action( 'after_setup_theme', 'rosa_branca_register_image_sizes' );
