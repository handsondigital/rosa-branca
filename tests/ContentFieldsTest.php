<?php
/**
 * Covers the primitive sanitizers in inc/content-fields.php — the rules
 * documented in CONTENT_MODEL.md's "Decisions" section (text/textarea/
 * optional-url/enum/icon). Written before inc/content-fields.php exists
 * (TDD): this file defines the contract, the implementation is made to
 * satisfy it.
 */

namespace RosaBranca\Tests;

use WP_Mock\Tools\TestCase;

class ContentFieldsTest extends TestCase {

	public function test_sanitize_text_trims_and_strips_tags(): void {
		\WP_Mock::userFunction( 'sanitize_text_field' )
			->once()
			->with( '  <b>Olá</b>  ' )
			->andReturn( 'Olá' );

		$this->assertSame( 'Olá', rosa_branca_sanitize_text( '  <b>Olá</b>  ' ) );
	}

	public function test_sanitize_text_rejects_non_string(): void {
		// A non-string (e.g. an array from a malformed request) must never
		// reach sanitize_text_field() — WP_Mock::userFunction() with no
		// ->times(0) still lets us assert this by simply not stubbing it:
		// if the code under test called it, the test would error on an
		// unexpected call.
		$this->assertSame( '', rosa_branca_sanitize_text( array( 'x' ) ) );
		$this->assertSame( '', rosa_branca_sanitize_text( null ) );
	}

	public function test_sanitize_textarea_delegates_to_core(): void {
		\WP_Mock::userFunction( 'sanitize_textarea_field' )
			->once()
			->with( "line1\nline2" )
			->andReturn( "line1\nline2" );

		$this->assertSame( "line1\nline2", rosa_branca_sanitize_textarea( "line1\nline2" ) );
	}

	public function test_sanitize_textarea_rejects_non_string(): void {
		$this->assertSame( '', rosa_branca_sanitize_textarea( 42 ) );
	}

	public function test_sanitize_optional_url_empty_stays_empty(): void {
		// Empty is the valid "no button" state (CONTENT_MODEL.md: link
		// optional, empty -> button omitted) — must NOT be rejected as
		// invalid, and must never call the URL validator for an empty
		// string (nothing to validate).
		$this->assertSame( '', rosa_branca_sanitize_optional_url( '' ) );
		$this->assertSame( '', rosa_branca_sanitize_optional_url( null ) );
	}

	public function test_sanitize_optional_url_valid_url_is_escaped(): void {
		\WP_Mock::userFunction( 'wp_http_validate_url' )
			->once()
			->with( 'https://example.com/receitas' )
			->andReturn( 'https://example.com/receitas' );

		\WP_Mock::userFunction( 'esc_url_raw' )
			->once()
			->with( 'https://example.com/receitas' )
			->andReturn( 'https://example.com/receitas' );

		$this->assertSame(
			'https://example.com/receitas',
			rosa_branca_sanitize_optional_url( 'https://example.com/receitas' )
		);
	}

	public function test_sanitize_optional_url_invalid_url_becomes_empty(): void {
		\WP_Mock::userFunction( 'wp_http_validate_url' )
			->once()
			->with( 'not a url' )
			->andReturn( false );

		// esc_url_raw must NOT be called once validation already failed.
		$this->assertSame( '', rosa_branca_sanitize_optional_url( 'not a url' ) );
	}

	public function test_sanitize_enum_returns_value_when_allowed(): void {
		$this->assertSame(
			'medio',
			rosa_branca_sanitize_enum( 'medio', array( 'facil', 'medio', 'dificil' ), 'facil' )
		);
	}

	/** @dataProvider provide_invalid_enum_values */
	public function test_sanitize_enum_falls_back_to_default( $value ): void {
		$this->assertSame(
			'facil',
			rosa_branca_sanitize_enum( $value, array( 'facil', 'medio', 'dificil' ), 'facil' )
		);
	}

	public static function provide_invalid_enum_values(): array {
		return array(
			'unknown value'  => array( 'impossivel' ),
			'empty string'   => array( '' ),
			'wrong type'     => array( array( 'facil' ) ),
			'null'           => array( null ),
		);
	}

	public function test_sanitize_icon_name_accepts_known_icons(): void {
		foreach ( array( 'headset-sac', 'envelope', 'contact-whatsapp', 'marker' ) as $icon ) {
			$this->assertSame( $icon, rosa_branca_sanitize_icon_name( $icon ) );
		}
	}

	public function test_sanitize_icon_name_rejects_unknown_icon(): void {
		$this->assertSame( '', rosa_branca_sanitize_icon_name( 'skull-and-crossbones' ) );
	}
}
