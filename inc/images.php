<?php
/**
 * Responsive image output (ARCHITECTURE_PLAN.md §8 "Imagens"): reads the
 * manifest written by scripts/build-images.mjs and prints <picture> markup
 * with AVIF/WebP sources + a universally-supported fallback, srcset/sizes,
 * explicit intrinsic dimensions, and lazy loading by default. The LCP image
 * on each page opts out of lazy loading and gets a matching <link rel=preload>
 * instead (rosa_branca_preload_image()).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function rosa_branca_image_manifest(): array {
	static $manifest = null;

	if ( null !== $manifest ) {
		return $manifest;
	}

	$path = get_theme_file_path( 'assets/images/generated/manifest.json' );

	if ( ! file_exists( $path ) ) {
		$manifest = array();
		return $manifest;
	}

	$contents = file_get_contents( $path );
	$decoded  = json_decode( $contents, true );
	$manifest = is_array( $decoded ) ? $decoded : array();

	return $manifest;
}

function rosa_branca_image_srcset( array $items, string $base_uri ): string {
	return implode(
		', ',
		array_map(
			static fn( array $item ): string => esc_url( $base_uri . $item['file'] ) . ' ' . $item['width'] . 'w',
			$items
		)
	);
}

/**
 * Prints a <picture> element (AVIF -> WebP -> fallback) for a manifest entry.
 *
 * $args:
 * - alt (string, required for content images; '' for decorative)
 * - sizes (string) overrides the manifest's default `sizes`
 * - class (string) class on the <picture> wrapper
 * - img_class (string) class on the <img> (use this for CSS that targets the img, e.g. object-fit)
 * - loading ('lazy'|'eager'), default 'lazy'
 * - fetchpriority ('high'|'low'), only set this for the real LCP image
 * - attrs (array<string,string>) extra raw attributes on the <img> (e.g. ['width' => '1440'] to override)
 */
function rosa_branca_picture( string $name, array $args = array() ): void {
	$manifest = rosa_branca_image_manifest();

	if ( empty( $manifest[ $name ] ) ) {
		return;
	}

	$entry = $manifest[ $name ];
	$args  = wp_parse_args(
		$args,
		array(
			'alt'           => '',
			'sizes'         => $entry['sizes'],
			'class'         => '',
			'img_class'     => '',
			'loading'       => 'lazy',
			'fetchpriority' => '',
			'attrs'         => array(),
		)
	);

	$base_uri = get_theme_file_uri( 'assets/images/generated/' );
	$fallback = $entry['formats']['fallback'];
	$largest  = end( $fallback );

	printf( '<picture%s>', $args['class'] ? ' class="' . esc_attr( $args['class'] ) . '"' : '' );

	foreach ( array(
		'avif' => 'image/avif',
		'webp' => 'image/webp',
	) as $format => $mime ) {
		if ( empty( $entry['formats'][ $format ] ) ) {
			continue;
		}
		printf(
			'<source type="%s" srcset="%s" sizes="%s">',
			esc_attr( $mime ),
			rosa_branca_image_srcset( $entry['formats'][ $format ], $base_uri ),
			esc_attr( $args['sizes'] )
		);
	}

	$extra_attrs = '';
	foreach ( $args['attrs'] as $attr_name => $attr_value ) {
		$extra_attrs .= sprintf( ' %s="%s"', esc_attr( $attr_name ), esc_attr( $attr_value ) );
	}

	printf(
		'<img src="%s" srcset="%s" sizes="%s" width="%d" height="%d" alt="%s" loading="%s"%s%s%s>',
		esc_url( $base_uri . $largest['file'] ),
		rosa_branca_image_srcset( $fallback, $base_uri ),
		esc_attr( $args['sizes'] ),
		(int) $entry['width'],
		(int) $entry['height'],
		esc_attr( $args['alt'] ),
		esc_attr( $args['loading'] ),
		$args['fetchpriority'] ? ' fetchpriority="' . esc_attr( $args['fetchpriority'] ) . '"' : '',
		$args['img_class'] ? ' class="' . esc_attr( $args['img_class'] ) . '"' : '',
		$extra_attrs
	);

	echo '</picture>';
}

/**
 * Prints a <link rel=preload> for the single best candidate (largest AVIF
 * variant) of a page's real LCP image — pair with fetchpriority="high" +
 * loading="eager" on the matching rosa_branca_picture() call.
 *
 * Must run on the 'wp_head' hook itself (see rosa_branca_preload_lcp_image()
 * in functions.php, which decides *which* image per page via the same
 * conditional-tag pattern as rosa_branca_enqueue_assets()) — get_header()
 * fires wp_head() before any template part runs, so calling add_action()
 * for 'wp_head' from inside a template part is always too late.
 */
function rosa_branca_print_image_preload( string $name ): void {
	$manifest = rosa_branca_image_manifest();

	if ( empty( $manifest[ $name ]['formats']['avif'] ) ) {
		return;
	}

	$entry    = $manifest[ $name ];
	$base_uri = get_theme_file_uri( 'assets/images/generated/' );

	printf(
		'<link rel="preload" as="image" imagesrcset="%s" imagesizes="%s" type="image/avif" fetchpriority="high">' . "\n",
		rosa_branca_image_srcset( $entry['formats']['avif'], $base_uri ),
		esc_attr( $entry['sizes'] )
	);
}
