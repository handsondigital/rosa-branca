<?php
/**
 * Covers the `receita` CPT's two custom meta fields (CONTENT_MODEL.md):
 * `dificuldade` (required, whitelist, defaults to "facil") and
 * `tempo_preparo` (optional free text).
 */

namespace RosaBranca\Tests;

use WP_Mock\Tools\TestCase;

class ReceitaMetaTest extends TestCase {

	public function test_dificuldade_accepts_allowed_values(): void {
		foreach ( array( 'facil', 'medio', 'dificil' ) as $value ) {
			$this->assertSame( $value, rosa_branca_sanitize_dificuldade( $value ) );
		}
	}

	/** @dataProvider provide_invalid_dificuldade */
	public function test_dificuldade_falls_back_to_facil( $value ): void {
		$this->assertSame( 'facil', rosa_branca_sanitize_dificuldade( $value ) );
	}

	public static function provide_invalid_dificuldade(): array {
		return array(
			'unknown'  => array( 'impossivel' ),
			'empty'    => array( '' ),
			'wrong type' => array( array() ),
		);
	}

	public function test_tempo_preparo_is_optional_and_sanitized(): void {
		\WP_Mock::userFunction( 'sanitize_text_field' )
			->once()
			->with( '2h 30min.' )
			->andReturn( '2h 30min.' );

		$this->assertSame( '2h 30min.', rosa_branca_sanitize_tempo_preparo( '2h 30min.' ) );
	}

	public function test_tempo_preparo_empty_stays_empty(): void {
		$this->assertSame( '', rosa_branca_sanitize_tempo_preparo( '' ) );
	}

	public function test_dificuldade_label_maps_known_values(): void {
		$this->assertSame( 'Fácil', rosa_branca_dificuldade_label( 'facil' ) );
		$this->assertSame( 'Médio', rosa_branca_dificuldade_label( 'medio' ) );
		$this->assertSame( 'Difícil', rosa_branca_dificuldade_label( 'dificil' ) );
	}

	public function test_dificuldade_label_falls_back_for_unknown_value(): void {
		$this->assertSame( 'Fácil', rosa_branca_dificuldade_label( 'nao-existe' ) );
	}
}
