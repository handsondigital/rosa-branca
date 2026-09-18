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
 * Same <picture> (AVIF -> WebP -> fallback) output as rosa_branca_picture()
 * above, but for a real media-library attachment (e.g. a `receita`/`produto`
 * Featured Image, or a hero background) instead of a build-time static
 * asset — reads the `sources` metadata inc/uploads.php's wp_generate_
 * attachment_metadata hook writes, rather than assets/images/generated/
 * manifest.json.
 *
 * Builds a full responsive srcset (per format) from every real intermediate
 * size WordPress generated for this attachment, same shape as
 * rosa_branca_picture()'s static-pipeline srcset — NOT single-resolution:
 * a real Lighthouse regression on Home's hero (LCP 3.25s, over the 2.5s
 * budget — CLAUDE.md's "Performance measurement" note) was traced to this function always emitting
 * one fixed-size image regardless of viewport, forcing mobile to download
 * the same file as desktop. WP's own 'thumbnail' size is excluded — it's
 * the one default size that's hard-cropped square (crop=true), not
 * proportional like every other size here, so its file isn't a smaller
 * version of the same framing and can't share one width-keyed srcset.
 *
 * $size still selects the single fallback candidate for browsers with no
 * srcset support (via wp_get_attachment_image_src(), also where width/height
 * come from) — pass the size whose max width best matches this image's
 * largest real display context (e.g. 'hero-bleed' for a full-bleed hero).
 *
 * $args: same keys as rosa_branca_picture() (alt/class/img_class/loading/
 * fetchpriority), plus:
 * - sizes (string) the <img>/<source> `sizes` attribute, default '100vw'
 *   (correct for a full-bleed hero; pass a real value for anything narrower).
 */
function rosa_branca_dynamic_picture( int $attachment_id, string $size, array $args = array() ): void {
	$image_src = wp_get_attachment_image_src( $attachment_id, $size );
	$metadata  = wp_get_attachment_metadata( $attachment_id );

	if ( ! $image_src || ! $metadata ) {
		return;
	}

	$args = wp_parse_args(
		$args,
		array(
			'alt'           => '',
			'class'         => '',
			'img_class'     => '',
			'loading'       => 'lazy',
			'fetchpriority' => '',
			'sizes'         => '100vw',
		)
	);

	$upload_dir = wp_get_upload_dir();
	$base_url   = trailingslashit( $upload_dir['baseurl'] ) . trailingslashit( dirname( $metadata['file'] ) );

	$candidates = array(
		array(
			'width'   => (int) $metadata['width'],
			'file'    => basename( $metadata['file'] ),
			'sources' => $metadata['sources'] ?? array(),
		),
	);
	foreach ( (array) ( $metadata['sizes'] ?? array() ) as $size_name => $size_data ) {
		if ( 'thumbnail' === $size_name || empty( $size_data['width'] ) || empty( $size_data['file'] ) ) {
			continue;
		}
		$candidates[] = array(
			'width'   => (int) $size_data['width'],
			'file'    => $size_data['file'],
			'sources' => $size_data['sources'] ?? array(),
		);
	}
	usort( $candidates, static fn( array $a, array $b ): int => $a['width'] <=> $b['width'] );

	$avif_items     = array();
	$webp_items     = array();
	$fallback_items = array();
	foreach ( $candidates as $candidate ) {
		if ( ! empty( $candidate['sources']['image/avif']['file'] ) ) {
			$avif_items[] = array( 'file' => $candidate['sources']['image/avif']['file'], 'width' => $candidate['width'] );
		}
		if ( ! empty( $candidate['sources']['image/webp']['file'] ) ) {
			$webp_items[] = array( 'file' => $candidate['sources']['image/webp']['file'], 'width' => $candidate['width'] );
		}
		$fallback_items[] = array( 'file' => $candidate['file'], 'width' => $candidate['width'] );
	}

	printf( '<picture%s>', $args['class'] ? ' class="' . esc_attr( $args['class'] ) . '"' : '' );

	foreach ( array(
		'image/avif' => $avif_items,
		'image/webp' => $webp_items,
	) as $mime => $items ) {
		if ( empty( $items ) ) {
			continue;
		}
		printf(
			'<source type="%s" srcset="%s" sizes="%s">',
			esc_attr( $mime ),
			rosa_branca_image_srcset( $items, $base_url ),
			esc_attr( $args['sizes'] )
		);
	}

	printf(
		'<img src="%s" srcset="%s" sizes="%s" width="%d" height="%d" alt="%s" loading="%s"%s%s>',
		esc_url( $image_src[0] ),
		rosa_branca_image_srcset( $fallback_items, $base_url ),
		esc_attr( $args['sizes'] ),
		(int) $image_src[1],
		(int) $image_src[2],
		esc_attr( $args['alt'] ),
		esc_attr( $args['loading'] ),
		$args['fetchpriority'] ? ' fetchpriority="' . esc_attr( $args['fetchpriority'] ) . '"' : '',
		$args['img_class'] ? ' class="' . esc_attr( $args['img_class'] ) . '"' : ''
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
