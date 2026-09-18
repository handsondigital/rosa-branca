<?php
/**
 * Covers rosa_branca_nav_item_is_current() (inc/nav-menu.php) —
 * CONTENT_MODEL.md's "Menu Principal / Menu Rodapé" section. The only
 * piece of *our* logic in the nav-menu feature (menu item CRUD itself is
 * WP core's, already covered by core's own test suite).
 */

namespace RosaBranca\Tests;

use WP_Mock\Tools\TestCase;

class NavMenuTest extends TestCase {

	public function test_exact_match_is_current(): void {
		$this->assertTrue(
			rosa_branca_nav_item_is_current( 'http://rosa-branca.local/fale-conosco/', 'http://rosa-branca.local/fale-conosco/' )
		);
	}

	public function test_trailing_slash_difference_is_still_current(): void {
		$this->assertTrue(
			rosa_branca_nav_item_is_current( 'http://rosa-branca.local/fale-conosco', 'http://rosa-branca.local/fale-conosco/' )
		);
		$this->assertTrue(
			rosa_branca_nav_item_is_current( 'http://rosa-branca.local/fale-conosco/', 'http://rosa-branca.local/fale-conosco' )
		);
	}

	public function test_query_string_on_current_url_is_ignored(): void {
		$this->assertTrue(
			rosa_branca_nav_item_is_current( 'http://rosa-branca.local/', 'http://rosa-branca.local/?s=farinha' )
		);
	}

	public function test_different_paths_are_not_current(): void {
		$this->assertFalse(
			rosa_branca_nav_item_is_current( 'http://rosa-branca.local/produtos/', 'http://rosa-branca.local/receitas/' )
		);
	}

	public function test_home_url_variants_match(): void {
		$this->assertTrue(
			rosa_branca_nav_item_is_current( 'http://rosa-branca.local/', 'http://rosa-branca.local' )
		);
	}

	// --- rosa_branca_get_nav_items() ---------------------------------------

	public function test_falls_back_to_default_items_when_no_menu_assigned(): void {
		\WP_Mock::userFunction( 'get_nav_menu_locations' )->andReturn( array() );
		\WP_Mock::userFunction( 'home_url' )->andReturnUsing( static fn( $path = '' ) => 'http://rosa-branca.local' . $path );

		$items = rosa_branca_get_nav_items( 'primary', 'http://rosa-branca.local/' );

		$this->assertNotEmpty( $items );
		$this->assertSame( 'Home', $items[0]['label'] );
	}

	public function test_uses_real_menu_items_when_a_menu_is_assigned(): void {
		\WP_Mock::userFunction( 'get_nav_menu_locations' )->andReturn( array( 'primary' => 42 ) );

		$item        = new \stdClass();
		$item->title = 'Blog';
		$item->url   = 'http://rosa-branca.local/blog/';

		\WP_Mock::userFunction( 'wp_get_nav_menu_items' )->with( 42 )->andReturn( array( $item ) );

		$items = rosa_branca_get_nav_items( 'primary', 'http://rosa-branca.local/blog/' );

		$this->assertCount( 1, $items );
		$this->assertSame( 'Blog', $items[0]['label'] );
		$this->assertTrue( $items[0]['current'] );
	}

	public function test_falls_back_when_assigned_menu_has_no_items(): void {
		\WP_Mock::userFunction( 'get_nav_menu_locations' )->andReturn( array( 'primary' => 42 ) );
		\WP_Mock::userFunction( 'wp_get_nav_menu_items' )->with( 42 )->andReturn( array() );
		\WP_Mock::userFunction( 'home_url' )->andReturnUsing( static fn( $path = '' ) => 'http://rosa-branca.local' . $path );

		$items = rosa_branca_get_nav_items( 'primary', 'http://rosa-branca.local/' );

		$this->assertNotEmpty( $items );
		$this->assertSame( 'Home', $items[0]['label'] );
	}
}
