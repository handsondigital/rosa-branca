<?php
/**
 * Inline SVG output. Icons are echoed as raw <svg> markup instead of via
 * <img src="...svg">, so the exact source paths/gradients render without
 * going through the <img> element (and are stylable via CSS from the page,
 * e.g. currentColor, instead of being opaque to it).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Echoes the raw contents of assets/icons/{$name}.svg.
 *
 * Raw (unescaped) output is safe here: $name is always a literal string at
 * the call site (never user input), and the files themselves are static
 * theme assets under our own assets/icons/ directory — not user-supplied
 * or remotely-fetched content.
 */
function rosa_branca_icon( string $name ): void {
	static $cache = array();

	if ( ! array_key_exists( $name, $cache ) ) {
		$path           = get_theme_file_path( "assets/icons/{$name}.svg" );
		$cache[ $name ] = file_exists( $path ) ? file_get_contents( $path ) : '';
	}

	echo $cache[ $name ]; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
