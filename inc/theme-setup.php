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
