<?php
/**
 * Native (no ACF) options page for the site-wide footer — CONTENT_MODEL.md's
 * "Footer (Options page)" section. Not tied to any single Page (unlike
 * inc/home-fields.php/inc/fale-conosco-fields.php) since the footer
 * appears on every page — uses WP's Settings API (register_setting() +
 * add_options_page()), which saves through options.php automatically
 * (no custom save_post handler needed here, unlike the Page-scoped meta
 * boxes). Reuses the exact same field-row renderers from
 * inc/admin-fields-ui.php (Reuse-first, CLAUDE.md).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const ROSA_BRANCA_FOOTER_OPTION_GROUP = 'rosa_branca_footer';

/**
 * Single source of truth for every footer option's default — used both
 * by register_setting() below (so the admin screen pre-fills correctly)
 * AND by rosa_branca_get_footer_option() (so the front-end also falls
 * back correctly). Splitting these was the actual bug: register_setting()'s
 * own 'default' only wires up get_option()'s fallback for requests where
 * admin_init has already fired — real on every wp-admin screen, NEVER
 * true on the front-end, where footer.php's get_option() calls happened
 * to always come back empty ('') instead of this text. Passing the same
 * default as get_option()'s own second argument works in both contexts
 * (that's a plain, context-independent get_option() fallback, unrelated
 * to register_setting() at all) — see rosa_branca_get_footer_option().
 */
function rosa_branca_footer_defaults(): array {
	return array(
		'rosa_branca_footer_brand_text'      => __( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin maximus magna vel orci iaculis tincidunt. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Cras non mauris turpis.', 'rosa-branca' ),
		'rosa_branca_footer_whatsapp_text'   => __( 'Lorem Ipsum', 'rosa-branca' ),
		'rosa_branca_footer_phone_text'      => __( 'Lorem Ipsum', 'rosa-branca' ),
		'rosa_branca_footer_disclaimer_text' => __( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.', 'rosa-branca' ),
		// Instagram/Facebook are real accounts (confirmed via web search —
		// the official "Rosa Branca" wheat-flour brand, @farinharosabranca,
		// now part of Bunge's portfolio after the Viterra merger), not
		// placeholder copy like the fields above. YouTube is the opposite:
		// no official brand channel could be confirmed (only third-party
		// recipe videos mentioning the product), so this is a MOCK handle
		// (same shape as the other two, not a real, verified channel) —
		// same "realistic placeholder, not final" convention as this
		// theme's other placeholder copy (CLAUDE.md's "Copy" item). Swap
		// for the real channel URL once the client confirms one exists.
		'rosa_branca_footer_instagram_url'   => 'https://www.instagram.com/farinharosabranca/',
		'rosa_branca_footer_facebook_url'    => 'https://www.facebook.com/farinharosabranca/',
		'rosa_branca_footer_youtube_url'     => 'https://www.youtube.com/@farinharosabranca',
	);
}

/** Always use this (not a bare get_option()) to read a footer option —
 *  on the front-end AND in wp-admin alike. */
function rosa_branca_get_footer_option( string $key ): string {
	return get_option( $key, rosa_branca_footer_defaults()[ $key ] ?? '' );
}

function rosa_branca_register_footer_settings(): void {
	$defaults      = rosa_branca_footer_defaults();
	$text_fields   = array(
		'rosa_branca_footer_brand_text'      => 'rosa_branca_sanitize_textarea',
		'rosa_branca_footer_whatsapp_text'   => 'rosa_branca_sanitize_text',
		'rosa_branca_footer_phone_text'      => 'rosa_branca_sanitize_text',
		'rosa_branca_footer_disclaimer_text' => 'rosa_branca_sanitize_text',
	);
	$url_fields    = array( 'rosa_branca_footer_instagram_url', 'rosa_branca_footer_facebook_url', 'rosa_branca_footer_youtube_url' );

	foreach ( $text_fields as $key => $sanitize_callback ) {
		register_setting(
			ROSA_BRANCA_FOOTER_OPTION_GROUP,
			$key,
			array(
				'type'              => 'string',
				'default'           => $defaults[ $key ],
				'sanitize_callback' => $sanitize_callback,
				'show_in_rest'      => false,
			)
		);
	}

	foreach ( $url_fields as $key ) {
		register_setting(
			ROSA_BRANCA_FOOTER_OPTION_GROUP,
			$key,
			array(
				'type'              => 'string',
				'default'           => $defaults[ $key ],
				'sanitize_callback' => 'rosa_branca_sanitize_optional_url',
				'show_in_rest'      => false,
			)
		);
	}
}
add_action( 'admin_init', 'rosa_branca_register_footer_settings' );

function rosa_branca_add_footer_options_page(): void {
	add_options_page(
		__( 'Rodapé', 'rosa-branca' ),
		__( 'Rodapé', 'rosa-branca' ),
		'manage_options',
		'rosa-branca-footer',
		'rosa_branca_render_footer_options_page'
	);
}
add_action( 'admin_menu', 'rosa_branca_add_footer_options_page' );

function rosa_branca_render_footer_options_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Rodapé', 'rosa-branca' ); ?></h1>
		<form method="post" action="options.php">
			<?php settings_fields( ROSA_BRANCA_FOOTER_OPTION_GROUP ); ?>
			<h2><?php esc_html_e( 'Marca', 'rosa-branca' ); ?></h2>
			<?php rosa_branca_admin_textarea_row( 'rosa_branca_footer_brand_text', __( 'Texto (aparece 2x, mesmo parágrafo — assim está no Figma)', 'rosa-branca' ), rosa_branca_get_footer_option( 'rosa_branca_footer_brand_text' ) ); ?>

			<h2><?php esc_html_e( 'Contato', 'rosa-branca' ); ?></h2>
			<?php rosa_branca_admin_text_row( 'rosa_branca_footer_whatsapp_text', __( 'WhatsApp (texto)', 'rosa-branca' ), rosa_branca_get_footer_option( 'rosa_branca_footer_whatsapp_text' ) ); ?>
			<?php rosa_branca_admin_text_row( 'rosa_branca_footer_phone_text', __( 'Telefone (texto)', 'rosa-branca' ), rosa_branca_get_footer_option( 'rosa_branca_footer_phone_text' ) ); ?>

			<h2><?php esc_html_e( 'Redes sociais', 'rosa-branca' ); ?></h2>
			<?php rosa_branca_admin_url_row( 'rosa_branca_footer_instagram_url', __( 'Instagram', 'rosa-branca' ), rosa_branca_get_footer_option( 'rosa_branca_footer_instagram_url' ) ); ?>
			<?php rosa_branca_admin_url_row( 'rosa_branca_footer_facebook_url', __( 'Facebook', 'rosa-branca' ), rosa_branca_get_footer_option( 'rosa_branca_footer_facebook_url' ) ); ?>
			<?php rosa_branca_admin_url_row( 'rosa_branca_footer_youtube_url', __( 'YouTube', 'rosa-branca' ), rosa_branca_get_footer_option( 'rosa_branca_footer_youtube_url' ) ); ?>

			<h2><?php esc_html_e( 'Barra legal (rodapé inferior)', 'rosa-branca' ); ?></h2>
			<?php rosa_branca_admin_text_row( 'rosa_branca_footer_disclaimer_text', __( 'Texto', 'rosa-branca' ), rosa_branca_get_footer_option( 'rosa_branca_footer_disclaimer_text' ) ); ?>

			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}
