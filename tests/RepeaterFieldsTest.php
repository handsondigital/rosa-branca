<?php
/**
 * Covers the two repeater sanitizers (CONTENT_MODEL.md: Banner slides,
 * Canais de contato) — both store one JSON array in a single post meta
 * row, no CPT. Each row is sanitized independently; a row missing a
 * required field is dropped rather than saved half-broken.
 */

namespace RosaBranca\Tests;

use WP_Mock\Tools\TestCase;

class RepeaterFieldsTest extends TestCase {

	private function stub_text_sanitizers(): void {
		\WP_Mock::userFunction( 'sanitize_text_field' )->andReturnUsing(
			static fn( $v ) => is_string( $v ) ? trim( $v ) : ''
		);
		\WP_Mock::userFunction( 'sanitize_textarea_field' )->andReturnUsing(
			static fn( $v ) => is_string( $v ) ? trim( $v ) : ''
		);
	}

	// --- Banner slides ---------------------------------------------------

	public function test_banner_slides_keeps_a_complete_row(): void {
		$this->stub_text_sanitizers();

		$slides = rosa_branca_sanitize_banner_slides(
			array(
				array(
					'title'    => ' Título ',
					'text'     => ' Texto ',
					'link'     => '',
					'image_id' => 12,
				),
			)
		);

		$this->assertCount( 1, $slides );
		$this->assertSame( 'Título', $slides[0]['title'] );
		$this->assertSame( 'Texto', $slides[0]['text'] );
		$this->assertSame( '', $slides[0]['link'] );
		$this->assertSame( 12, $slides[0]['image_id'] );
	}

	/** @dataProvider provide_incomplete_banner_rows */
	public function test_banner_slides_drops_rows_missing_required_fields( array $row ): void {
		$this->stub_text_sanitizers();

		$slides = rosa_branca_sanitize_banner_slides( array( $row ) );

		$this->assertSame( array(), $slides );
	}

	public static function provide_incomplete_banner_rows(): array {
		return array(
			'missing title'    => array( array( 'title' => '', 'text' => 'x', 'image_id' => 1 ) ),
			'missing text'     => array( array( 'title' => 'x', 'text' => '', 'image_id' => 1 ) ),
			'missing image_id' => array( array( 'title' => 'x', 'text' => 'x', 'image_id' => 0 ) ),
			'non-numeric image_id' => array( array( 'title' => 'x', 'text' => 'x', 'image_id' => 'not-an-id' ) ),
		);
	}

	public function test_banner_slides_rejects_non_array_input(): void {
		$this->assertSame( array(), rosa_branca_sanitize_banner_slides( 'not an array' ) );
		$this->assertSame( array(), rosa_branca_sanitize_banner_slides( null ) );
	}

	public function test_banner_slides_preserves_row_order(): void {
		$this->stub_text_sanitizers();

		$slides = rosa_branca_sanitize_banner_slides(
			array(
				array( 'title' => 'First', 'text' => 'x', 'link' => '', 'image_id' => 1 ),
				array( 'title' => 'Second', 'text' => 'x', 'link' => '', 'image_id' => 2 ),
			)
		);

		$this->assertSame( 'First', $slides[0]['title'] );
		$this->assertSame( 'Second', $slides[1]['title'] );
	}

	// --- Contact channels --------------------------------------------------

	public function test_contact_channels_keeps_a_complete_row_with_optional_line2_empty(): void {
		$this->stub_text_sanitizers();

		$channels = rosa_branca_sanitize_contact_channels(
			array(
				array(
					'icon'  => 'envelope',
					'title' => 'E-mail',
					'line1' => 'contato@rosabranca.com.br',
					'line2' => '',
				),
			)
		);

		$this->assertCount( 1, $channels );
		$this->assertSame( 'envelope', $channels[0]['icon'] );
		$this->assertSame( '', $channels[0]['line2'] );
	}

	public function test_contact_channels_drops_row_with_unknown_icon(): void {
		$this->stub_text_sanitizers();

		$channels = rosa_branca_sanitize_contact_channels(
			array(
				array( 'icon' => 'not-a-real-icon', 'title' => 'x', 'line1' => 'x', 'line2' => '' ),
			)
		);

		$this->assertSame( array(), $channels );
	}

	/** @dataProvider provide_incomplete_channel_rows */
	public function test_contact_channels_drops_rows_missing_required_fields( array $row ): void {
		$this->stub_text_sanitizers();

		$this->assertSame( array(), rosa_branca_sanitize_contact_channels( array( $row ) ) );
	}

	public static function provide_incomplete_channel_rows(): array {
		return array(
			'missing title' => array( array( 'icon' => 'marker', 'title' => '', 'line1' => 'x', 'line2' => '' ) ),
			'missing line1' => array( array( 'icon' => 'marker', 'title' => 'x', 'line1' => '', 'line2' => '' ) ),
		);
	}

	public function test_contact_channels_rejects_non_array_input(): void {
		$this->assertSame( array(), rosa_branca_sanitize_contact_channels( 'nope' ) );
	}
}
