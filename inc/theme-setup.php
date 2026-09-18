<?php
/**
 * Core theme supports and registrations.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function rosa_branca_setup(): void {
	load_theme_textdomain( 'rosa-branca', get_template_directory() . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' )
	);
	add_theme_support( 'custom-logo' );

	register_nav_menus(
		array(
			'primary' => __( 'Menu Principal', 'rosa-branca' ),
			'footer'  => __( 'Menu Rodapé', 'rosa-branca' ),
		)
	);
}
add_action( 'after_setup_theme', 'rosa_branca_setup' );

/**
 * WordPress renders block-editor-oriented resets by default; this theme is
 * classic/PHP-first, so the front-end block-library stylesheet is not needed.
 */
function rosa_branca_dequeue_block_library_css(): void {
	wp_dequeue_style( 'wp-block-library' );
	wp_dequeue_style( 'classic-theme-styles' );
}
add_action( 'wp_enqueue_scripts', 'rosa_branca_dequeue_block_library_css', 20 );

/**
 * Pages use the classic editor, not the block editor — same "no Gutenberg
 * as a foundation" stance as the front-end (ARCHITECTURE_PLAN.md), applied
 * to editing too: no template here renders a Page's `post_content` via
 * `the_content()`/blocks (front-page.php and page-fale-conosco.php both
 * hardcode their own layout), so the block editor's canvas would just show
 * an empty, meaningless paragraph block above the real content, which
 * lives entirely in inc/home-fields.php's/inc/fale-conosco-fields.php's
 * native meta boxes — those still work in either editor, this only picks
 * which one wraps them.
 */
function rosa_branca_disable_block_editor_for_pages( bool $use_block_editor, string $post_type ): bool {
	return 'page' === $post_type ? false : $use_block_editor;
}
add_filter( 'use_block_editor_for_post_type', 'rosa_branca_disable_block_editor_for_pages', 10, 2 );
