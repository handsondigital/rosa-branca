<?php
/**
 * Reads the `primary`/`footer` nav menu locations (already registered by
 * `register_nav_menus()` in inc/theme-setup.php) for header.php/footer.php
 * — CONTENT_MODEL.md's "Menu Principal / Menu Rodapé" section. Falls back
 * to today's exact hardcoded 6 items (rosa_branca_default_nav_items())
 * when no menu is assigned to a location, same fallback shape as
 * rosa_branca_home_banner_slides() (inc/home-fields.php).
 *
 * No custom sanitizer here — menu item CRUD (Aparência → Menus) is entirely
 * WP core's own, already covered by core's test suite. The only piece of
 * *our* logic is deciding whether a given item is the current page, kept
 * as a small pure function so it's unit-testable without a real request
 * (tests/NavMenuTest.php).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Normalizes both URLs (strips query string, trailing slash) before
 * comparing — a menu item's stored URL and the current request URL
 * legitimately differ by exactly those two things even when they mean
 * the same page (e.g. a search submitted from a page still on that page,
 * or a site emitting URLs with vs. without a trailing slash).
 */
function rosa_branca_nav_item_is_current( string $item_url, string $current_url ): bool {
	$normalize = static function ( string $url ): string {
		return rtrim( strtok( $url, '?' ), '/' );
	};

	return $normalize( $item_url ) === $normalize( $current_url );
}

/** Today's exact hardcoded items (header.php's/footer.php's own arrays
 *  before this file existed) — the fallback when no menu is assigned to
 *  a location yet. Shared between `primary` and `footer`: both locations
 *  showed the same 6 destinations, just styled differently. */
function rosa_branca_default_nav_items(): array {
	return array(
		array( 'label' => __( 'Home', 'rosa-branca' ), 'url' => home_url( '/' ) ),
		array( 'label' => __( 'Sobre a Marca', 'rosa-branca' ), 'url' => home_url( '/sobre-a-marca/' ) ),
		array( 'label' => __( 'Produtos', 'rosa-branca' ), 'url' => home_url( '/produtos/' ) ),
		array( 'label' => __( 'Receitas', 'rosa-branca' ), 'url' => home_url( '/receitas/' ) ),
		array( 'label' => __( 'Onde Comprar', 'rosa-branca' ), 'url' => home_url( '/onde-comprar/' ) ),
		array( 'label' => __( 'Fale Conosco', 'rosa-branca' ), 'url' => home_url( '/fale-conosco/' ) ),
	);
}

/**
 * $current_url is a parameter (not computed inside) deliberately — that
 * keeps this function pure/unit-testable; the one-liner that determines
 * "what is the current request's URL" lives at the template call site
 * instead (see rosa_branca_current_url() below), where it doesn't need a
 * test of its own (it's a direct read of $_SERVER, nothing to get wrong).
 */
function rosa_branca_get_nav_items( string $location, string $current_url ): array {
	$locations = get_nav_menu_locations();
	$menu_id   = $locations[ $location ] ?? 0;
	$menu_items = $menu_id ? wp_get_nav_menu_items( $menu_id ) : false;

	if ( empty( $menu_items ) ) {
		return array_map(
			static function ( array $item ) use ( $current_url ) {
				$item['current'] = rosa_branca_nav_item_is_current( $item['url'], $current_url );
				return $item;
			},
			rosa_branca_default_nav_items()
		);
	}

	return array_map(
		static function ( $item ) use ( $current_url ) {
			return array(
				'label'   => $item->title,
				'url'     => $item->url,
				'current' => rosa_branca_nav_item_is_current( $item->url, $current_url ),
			);
		},
		$menu_items
	);
}

/** The one non-pure piece (reads $_SERVER) — kept to a single line so
 *  there's nothing here worth a test of its own. */
function rosa_branca_current_url(): string {
	return home_url( wp_unslash( $_SERVER['REQUEST_URI'] ?? '/' ) );
}
