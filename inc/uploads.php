<?php
/**
 * AVIF/WebP generation for media-library uploads (Featured Images on
 * `receita`/`produto` posts, and any other WordPress upload) — the runtime
 * counterpart to scripts/build-images.mjs, which only covers static assets
 * checked into the theme. Same performance bar (AVIF -> WebP -> original
 * fallback, matching quality settings), native WordPress mechanism (wp_get_
 * image_editor — Imagick/GD, whichever the server has), no ACF, no external
 * service.
 *
 * Runs automatically on every image upload (wp_generate_attachment_metadata,
 * hooked below) and is also re-runnable on demand for existing attachments
 * via `wp rosa-branca regenerate-images` (bottom of this file) — e.g. after
 * registering a new add_image_size(), or to backfill images uploaded before
 * this pipeline existed.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Saves one AVIF or WebP sibling of $abs_path using WordPress' own image
 * editor. Returns null (skip, not a hard failure) if the server's editor
 * (Imagick/GD) can't encode that mime type — AVIF support in particular
 * varies by server library/version, same caveat noted in CLAUDE.md for the
 * build-time pipeline.
 */
function rosa_branca_save_modern_format( string $abs_path, string $mime ): ?array {
	if ( ! wp_image_editor_supports( array( 'mime_type' => $mime ) ) ) {
		return null;
	}

	$editor = wp_get_image_editor( $abs_path );
	if ( is_wp_error( $editor ) ) {
		return null;
	}

	// Same tuned values as scripts/build-images.mjs's AVIF_QUALITY/WEBP_QUALITY
	// constants (55/75) — without this, Imagick/GD's own default quality is
	// meaningfully higher/larger (measured ~2x bigger AVIF output for the
	// same image), which is what caused the Lighthouse LCP regression this
	// fixes together with the new 'hero-bleed' size (inc/post-types.php).
	$quality = ( 'image/avif' === $mime ) ? 55 : 75;
	$editor->set_quality( $quality );

	$ext  = ( 'image/avif' === $mime ) ? 'avif' : 'webp';
	$dest = preg_replace( '/\.[^.]+$/', '.' . $ext, $abs_path );
	$saved = $editor->save( $dest, $mime );

	if ( is_wp_error( $saved ) || empty( $saved['path'] ) ) {
		return null;
	}

	return array(
		'file'      => basename( $saved['path'] ),
		'mime-type' => $mime,
		'filesize'  => file_exists( $saved['path'] ) ? filesize( $saved['path'] ) : 0,
	);
}

/**
 * For the full-size upload and every registered intermediate size WordPress
 * just generated (core JPEG/PNG only — modern formats can't losslessly
 * re-derive from each other, so this always starts from the original),
 * generates AVIF + WebP siblings and records them under a `sources` key —
 * same schema name/shape WordPress core itself uses for its native "Modern
 * Image Formats" feature, so this stays recognisable even though core's own
 * version of that feature (the `image_editor_output_format` filter) isn't
 * what's driving it here: core's filter only *replaces* the size with a
 * single chosen format (no multi-source <picture> fallback), which doesn't
 * match this project's existing AVIF->WebP->fallback pattern
 * (inc/images.php) — this hook adds siblings instead, so the original
 * JPEG/PNG stays as the universal fallback.
 */
function rosa_branca_add_modern_image_formats( array $metadata, int $attachment_id ): array {
	$mime = get_post_mime_type( $attachment_id );
	if ( ! in_array( $mime, array( 'image/jpeg', 'image/png' ), true ) ) {
		return $metadata;
	}

	if ( empty( $metadata['file'] ) ) {
		return $metadata;
	}

	$upload_dir = wp_get_upload_dir();
	$base_dir   = trailingslashit( $upload_dir['basedir'] ) . trailingslashit( dirname( $metadata['file'] ) );

	$targets = array( '' => basename( $metadata['file'] ) );
	foreach ( (array) ( $metadata['sizes'] ?? array() ) as $size_name => $size_data ) {
		if ( ! empty( $size_data['file'] ) ) {
			$targets[ $size_name ] = $size_data['file'];
		}
	}

	foreach ( $targets as $size_name => $filename ) {
		$abs_path = $base_dir . $filename;
		if ( ! file_exists( $abs_path ) ) {
			continue;
		}

		$sources = array();
		foreach ( array( 'image/avif', 'image/webp' ) as $modern_mime ) {
			$result = rosa_branca_save_modern_format( $abs_path, $modern_mime );
			if ( null !== $result ) {
				$sources[ $modern_mime ] = $result;
			}
		}

		if ( empty( $sources ) ) {
			continue;
		}

		if ( '' === $size_name ) {
			$metadata['sources'] = $sources;
		} else {
			$metadata['sizes'][ $size_name ]['sources'] = $sources;
		}
	}

	return $metadata;
}
add_filter( 'wp_generate_attachment_metadata', 'rosa_branca_add_modern_image_formats', 20, 2 );

/**
 * `wp rosa-branca regenerate-images [--ids=<ids>]` — the on-demand /
 * backfill counterpart to the automatic hook above. WP-CLI has no built-in
 * worker pool; for a large media library, run several invocations with
 * disjoint --ids lists in parallel shells rather than waiting on one big
 * sequential pass.
 */
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	class Rosa_Branca_Image_CLI {
		/**
		 * Regenerates WordPress' native intermediate sizes plus this
		 * theme's AVIF/WebP siblings for existing image attachments.
		 *
		 * ## OPTIONS
		 *
		 * [--ids=<ids>]
		 * : Comma-separated attachment IDs to limit to. Default: every image attachment.
		 *
		 * ## EXAMPLES
		 *
		 *     wp rosa-branca regenerate-images
		 *     wp rosa-branca regenerate-images --ids=12,45,102
		 *
		 * @when after_wp_load
		 */
		public function regenerate_images( $args, $assoc_args ): void {
			require_once ABSPATH . 'wp-admin/includes/image.php';

			if ( ! empty( $assoc_args['ids'] ) ) {
				$ids = array_map( 'intval', explode( ',', $assoc_args['ids'] ) );
			} else {
				$ids = get_posts(
					array(
						'post_type'      => 'attachment',
						'post_mime_type' => 'image',
						'post_status'    => 'inherit',
						'fields'         => 'ids',
						'numberposts'    => -1,
					)
				);
			}

			$progress = \WP_CLI\Utils\make_progress_bar( 'Regenerating images', count( $ids ) );

			foreach ( $ids as $id ) {
				$file = get_attached_file( $id );
				if ( $file && file_exists( $file ) ) {
					$metadata = wp_generate_attachment_metadata( $id, $file );
					wp_update_attachment_metadata( $id, $metadata );
				}
				$progress->tick();
			}

			$progress->finish();
			WP_CLI::success( sprintf( '%d image(s) processed.', count( $ids ) ) );
		}
	}

	WP_CLI::add_command( 'rosa-branca regenerate-images', array( 'Rosa_Branca_Image_CLI', 'regenerate_images' ) );
}
