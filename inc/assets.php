<?php
/**
 * Resolves and enqueues Vite-built assets, contextually per page/template.
 *
 * Dev mode (Vite dev server running, dist/.hot present): assets are loaded
 * straight from the dev server so HMR/Fast Refresh works.
 * Build mode: assets are resolved from dist/.vite/manifest.json and enqueued
 * as WordPress script modules (native ESM, available since WP 6.5) + styles.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const ROSA_BRANCA_DEV_SERVER = 'http://localhost:5173';

function rosa_branca_is_vite_dev(): bool {
	return file_exists( get_theme_file_path( 'dist/.hot' ) );
}

function rosa_branca_vite_manifest(): array {
	static $manifest = null;

	if ( null !== $manifest ) {
		return $manifest;
	}

	$path = get_theme_file_path( 'dist/.vite/manifest.json' );

	if ( ! file_exists( $path ) ) {
		$manifest = array();
		return $manifest;
	}

	$contents = file_get_contents( $path );
	$manifest = json_decode( $contents, true );

	if ( ! is_array( $manifest ) ) {
		$manifest = array();
	}

	return $manifest;
}

/**
 * Enqueues one Vite entry (e.g. "src/entries/home.js") for the current request.
 * Safe to call multiple times with different entries on the same page.
 */
function rosa_branca_enqueue_entry( string $entry_relative_path ): void {
	$handle = 'rosa-branca-' . sanitize_key( basename( $entry_relative_path, '.js' ) );

	if ( rosa_branca_is_vite_dev() ) {
		add_action(
			'wp_head',
			function () use ( $entry_relative_path ) {
				rosa_branca_print_dev_client_once();
				printf(
					'<script type="module" src="%s"></script>' . "\n",
					esc_url( ROSA_BRANCA_DEV_SERVER . '/' . ltrim( $entry_relative_path, '/' ) )
				);
			}
		);
		return;
	}

	$manifest = rosa_branca_vite_manifest();

	if ( empty( $manifest[ $entry_relative_path ] ) ) {
		return;
	}

	$entry = $manifest[ $entry_relative_path ];
	$version = wp_get_theme()->get( 'Version' );

	if ( ! empty( $entry['file'] ) ) {
		wp_enqueue_script_module(
			$handle,
			get_theme_file_uri( 'dist/' . $entry['file'] ),
			array(),
			$version
		);
	}

	if ( ! empty( $entry['css'] ) ) {
		foreach ( $entry['css'] as $index => $css_file ) {
			wp_enqueue_style(
				$handle . '-css-' . $index,
				get_theme_file_uri( 'dist/' . $css_file ),
				array(),
				$version
			);
		}
	}
}

/**
 * Preloads one self-hosted font file (src/styles/fonts.css) by its Vite
 * manifest key, e.g. "assets/fonts/montserrat-700-latin.woff2".
 *
 * Added after self-hosting the fonts regressed Home's LCP (2.3s -> 2.7s,
 * confirmed reproducible across repeated npm run lighthouse runs): with
 * font-display:swap the browser never blocks paint waiting on a font, but
 * on a throttled connection the extra font requests still compete with the
 * preloaded hero image for the same early bandwidth, pushing out when the
 * image actually paints. Preloading only the two fonts actually used above
 * the fold (h1 + body copy) — not all 8 weight/subset files — restores
 * their priority without adding back that contention.
 */
function rosa_branca_preload_font( string $manifest_key ): void {
	if ( rosa_branca_is_vite_dev() ) {
		return;
	}

	$manifest = rosa_branca_vite_manifest();

	if ( empty( $manifest[ $manifest_key ]['file'] ) ) {
		return;
	}

	printf(
		'<link rel="preload" as="font" type="font/woff2" href="%s" crossorigin>' . "\n",
		esc_url( get_theme_file_uri( 'dist/' . $manifest[ $manifest_key ]['file'] ) )
	);
}

function rosa_branca_print_dev_client_once(): void {
	static $printed = false;

	if ( $printed ) {
		return;
	}
	$printed = true;

	printf(
		'<script type="module">
import RefreshRuntime from "%1$s/@react-refresh";
RefreshRuntime.injectIntoGlobalHook(window);
window.$RefreshReg$ = () => {};
window.$RefreshSig$ = () => (type) => type;
window.__vite_plugin_react_preamble_installed__ = true;
</script>
<script type="module" src="%1$s/@vite/client"></script>' . "\n",
		esc_url( ROSA_BRANCA_DEV_SERVER )
	);
}
