<?php
/**
 * Rosa Branca theme bootstrap.
 *
 * WordPress monolithic + React/Vite hybrid — see ARCHITECTURE_PLAN.md at the
 * project root for the governing rules (PHP/SSR first, React only where it
 * has a real interactivity payoff, contextual asset loading per page).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require get_theme_file_path( 'inc/theme-setup.php' );
require get_theme_file_path( 'inc/assets.php' );
require get_theme_file_path( 'inc/icons.php' );
require get_theme_file_path( 'inc/images.php' );
require get_theme_file_path( 'inc/post-types.php' );
require get_theme_file_path( 'inc/uploads.php' );

// NOTE: o formulário de Fale Conosco está implementado apenas no front-end
// por ora (ver template-parts/fale-conosco/form.php). O handler de envio
// (wp_mail / REST / integração) ainda depende de decisão de negócio sobre
// destino dos dados — a implementar em inc/contact-form.php quando definido.

/**
 * Enqueues the Vite entries needed for the current request only.
 * "global" (header/footer/mobile menu/tokens) loads everywhere; page-specific
 * entries load only where their markup actually exists.
 */
function rosa_branca_enqueue_assets(): void {
	rosa_branca_enqueue_entry( 'src/entries/global.js' );

	if ( is_front_page() ) {
		rosa_branca_enqueue_entry( 'src/entries/home.js' );
	}

	if ( is_page( 'fale-conosco' ) ) {
		rosa_branca_enqueue_entry( 'src/entries/fale-conosco.js' );
	}
}
add_action( 'wp_enqueue_scripts', 'rosa_branca_enqueue_assets' );

/**
 * Preloads each page's real LCP image (see inc/images.php). Must run on
 * 'wp_head' directly, registered here at include time — by the time a
 * template part runs, get_header() has already fired wp_head().
 */
function rosa_branca_preload_lcp_image(): void {
	if ( is_front_page() ) {
		rosa_branca_print_image_preload( 'banner-home' );
	} elseif ( is_page( 'fale-conosco' ) ) {
		rosa_branca_print_image_preload( 'foto-trigo-farinha' );
	}
}
add_action( 'wp_head', 'rosa_branca_preload_lcp_image', 1 );

/**
 * Preloads the two font files used above the fold on every page (h1 =
 * Montserrat 700, body copy = Nunito Sans 400 — see src/styles/fonts.css).
 * Same early-hook reasoning as rosa_branca_preload_lcp_image() above.
 */
function rosa_branca_preload_critical_fonts(): void {
	rosa_branca_preload_font( 'assets/fonts/montserrat-700-latin.woff2' );
	rosa_branca_preload_font( 'assets/fonts/nunito-sans-400-latin.woff2' );
}
add_action( 'wp_head', 'rosa_branca_preload_critical_fonts', 1 );
