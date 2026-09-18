<?php
/**
 * Primitive sanitizers for the native (no ACF, no Gutenberg/FSE — see
 * ARCHITECTURE_PLAN.md) meta boxes and repeater fields documented in
 * CONTENT_MODEL.md. Kept as small, pure functions (no WP_Query/DB calls)
 * specifically so they're unit-testable with WP_Mock without a real
 * WordPress bootstrap — see tests/ContentFieldsTest.php and
 * tests/RepeaterFieldsTest.php, which assert every rule this file
 * implements.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Single-line text. Rejects (returns '') anything that isn't already a
 * string — a malformed request (e.g. an array where text was expected)
 * must never reach sanitize_text_field(), which only accepts strings.
 */
function rosa_branca_sanitize_text( $value ): string {
	if ( ! is_string( $value ) || '' === $value ) {
		return '';
	}
	return sanitize_text_field( $value );
}

/** Multi-line text (paragraphs) — same non-string/empty guard as the text
 *  field (an empty value sanitizes to itself either way — short-circuiting
 *  just skips the round trip through core). */
function rosa_branca_sanitize_textarea( $value ): string {
	if ( ! is_string( $value ) || '' === $value ) {
		return '';
	}
	return sanitize_textarea_field( $value );
}

/**
 * A button/CTA link that's allowed to be empty (CONTENT_MODEL.md: every
 * button's link is optional — empty means the button doesn't render at
 * all, not a dead href). Empty input short-circuits before validation, so
 * "no link" is never mistaken for "invalid link". A non-empty value that
 * fails URL validation also becomes '' (same "omit the button" outcome) —
 * a request with a broken URL doesn't need a separate error path here,
 * the button just doesn't show, which is exactly what a missing link does
 * too.
 */
function rosa_branca_sanitize_optional_url( $value ): string {
	if ( ! is_string( $value ) || '' === $value ) {
		return '';
	}
	if ( ! wp_http_validate_url( $value ) ) {
		return '';
	}
	return esc_url_raw( $value );
}

/**
 * A value constrained to a fixed whitelist (dificuldade, icon names, ...),
 * falling back to $default on anything not in $allowed — including wrong
 * types, since a strict in_array( ..., true ) check on a non-string never
 * matches a whitelist of strings anyway.
 */
function rosa_branca_sanitize_enum( $value, array $allowed, string $default ): string {
	if ( is_string( $value ) && in_array( $value, $allowed, true ) ) {
		return $value;
	}
	return $default;
}

/**
 * The theme's fixed set of contact-channel icons (assets/icons/) — see
 * CONTENT_MODEL.md's "Canais de contato" row. Empty string (not a
 * default icon) on an unknown name: an icon field has no sensible visual
 * fallback the way dificuldade's "facil" does, so the repeater sanitizer
 * (rosa_branca_sanitize_contact_channels()) treats an empty icon as
 * "drop this row" instead.
 */
function rosa_branca_sanitize_icon_name( $value ): string {
	return rosa_branca_sanitize_enum(
		$value,
		array( 'headset-sac', 'envelope', 'contact-whatsapp', 'marker' ),
		''
	);
}

/**
 * Banner (hero) slides repeater — one JSON array in a single post meta
 * row (CONTENT_MODEL.md: repeater, not a CPT — these never need their own
 * URL/date/revisions). Each row needs title + text + a real image; link is
 * optional (empty -> that slide's button is omitted at render time, same
 * rule as every other section CTA). A row missing a required field is
 * dropped entirely rather than saved half-broken, so the render side never
 * has to guess what a partial row means.
 */
function rosa_branca_sanitize_banner_slides( $value ): array {
	if ( ! is_array( $value ) ) {
		return array();
	}

	$slides = array();

	foreach ( $value as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}

		$title    = rosa_branca_sanitize_text( $row['title'] ?? '' );
		$text     = rosa_branca_sanitize_textarea( $row['text'] ?? '' );
		$image_id = isset( $row['image_id'] ) ? (int) $row['image_id'] : 0;

		if ( '' === $title || '' === $text || $image_id <= 0 ) {
			continue;
		}

		$slides[] = array(
			'title'    => $title,
			'text'     => $text,
			'link'     => rosa_branca_sanitize_optional_url( $row['link'] ?? '' ),
			'image_id' => $image_id,
		);
	}

	return $slides;
}

/** CONTENT_MODEL.md ("receita" CPT): "dificuldade" is required with a
 *  default, not a hard validation failure — an invalid/missing value
 *  silently becomes "facil" rather than blocking the save. Registered as
 *  this field's sanitize_callback in inc/post-types.php. */
function rosa_branca_sanitize_dificuldade( $value ): string {
	return rosa_branca_sanitize_enum( $value, array( 'facil', 'medio', 'dificil' ), 'facil' );
}

/** CONTENT_MODEL.md ("receita" CPT): optional — empty means the card's
 *  time/clock row is omitted at render time, not a validation error.
 *  Registered as this field's sanitize_callback in inc/post-types.php. */
function rosa_branca_sanitize_tempo_preparo( $value ): string {
	return rosa_branca_sanitize_text( $value );
}

/** Display label for a `dificuldade` value (template-parts/home/
 *  recipes.php) — same whitelist/fallback as rosa_branca_sanitize_dificuldade(),
 *  just mapped to the Portuguese label shown on the card instead of the
 *  stored slug. */
function rosa_branca_dificuldade_label( $value ): string {
	$labels = array(
		'facil'   => __( 'Fácil', 'rosa-branca' ),
		'medio'   => __( 'Médio', 'rosa-branca' ),
		'dificil' => __( 'Difícil', 'rosa-branca' ),
	);
	$key = rosa_branca_sanitize_dificuldade( $value );
	return $labels[ $key ];
}

/**
 * Contact-channel repeater (CONTENT_MODEL.md: "Canais de contato") — icon
 * + título + linha 1 are required, linha 2 is optional (an empty line2
 * means the card renders with one line, not an empty second line). A row
 * with an unrecognized icon is dropped the same way a row missing a
 * required text field is: there's no safe rendering for either case.
 */
function rosa_branca_sanitize_contact_channels( $value ): array {
	if ( ! is_array( $value ) ) {
		return array();
	}

	$channels = array();

	foreach ( $value as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}

		$icon  = rosa_branca_sanitize_icon_name( $row['icon'] ?? '' );
		$title = rosa_branca_sanitize_text( $row['title'] ?? '' );
		$line1 = rosa_branca_sanitize_text( $row['line1'] ?? '' );

		if ( '' === $icon || '' === $title || '' === $line1 ) {
			continue;
		}

		$channels[] = array(
			'icon'  => $icon,
			'title' => $title,
			'line1' => $line1,
			'line2' => rosa_branca_sanitize_text( $row['line2'] ?? '' ),
		);
	}

	return $channels;
}
