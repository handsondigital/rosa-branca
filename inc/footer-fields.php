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

function rosa_branca_register_footer_settings(): void {
	$text_fields = array(
		'rosa_branca_footer_brand_text'      => array(
			'default'  => __( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin maximus magna vel orci iaculis tincidunt. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Cras non mauris turpis.', 'rosa-branca' ),
			'sanitize' => 'rosa_branca_sanitize_textarea',
		),
		'rosa_branca_footer_whatsapp_text'   => array(
			'default'  => __( 'Lorem Ipsum', 'rosa-branca' ),
			'sanitize' => 'rosa_branca_sanitize_text',
		),
		'rosa_branca_footer_phone_text'      => array(
			'default'  => __( 'Lorem Ipsum', 'rosa-branca' ),
			'sanitize' => 'rosa_branca_sanitize_text',
		),
		'rosa_branca_footer_disclaimer_text' => array(
			'default'  => __( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.', 'rosa-branca' ),
			'sanitize' => 'rosa_branca_sanitize_text',
		),
	);

	foreach ( $text_fields as $key => $config ) {
		register_setting(
			ROSA_BRANCA_FOOTER_OPTION_GROUP,
			$key,
			array(
				'type'              => 'string',
				'default'           => $config['default'],
				'sanitize_callback' => $config['sanitize'],
				'show_in_rest'      => false,
			)
		);
	}

	foreach ( array( 'rosa_branca_footer_instagram_url', 'rosa_branca_footer_facebook_url', 'rosa_branca_footer_youtube_url' ) as $key ) {
		register_setting(
			ROSA_BRANCA_FOOTER_OPTION_GROUP,
			$key,
			array(
				'type'              => 'string',
				'default'           => '',
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
			<?php rosa_branca_admin_textarea_row( 'rosa_branca_footer_brand_text', __( 'Texto (aparece 2x, mesmo parágrafo — assim está no Figma)', 'rosa-branca' ), get_option( 'rosa_branca_footer_brand_text' ) ); ?>

			<h2><?php esc_html_e( 'Contato', 'rosa-branca' ); ?></h2>
			<?php rosa_branca_admin_text_row( 'rosa_branca_footer_whatsapp_text', __( 'WhatsApp (texto)', 'rosa-branca' ), get_option( 'rosa_branca_footer_whatsapp_text' ) ); ?>
			<?php rosa_branca_admin_text_row( 'rosa_branca_footer_phone_text', __( 'Telefone (texto)', 'rosa-branca' ), get_option( 'rosa_branca_footer_phone_text' ) ); ?>

			<h2><?php esc_html_e( 'Redes sociais', 'rosa-branca' ); ?></h2>
			<?php rosa_branca_admin_url_row( 'rosa_branca_footer_instagram_url', __( 'Instagram', 'rosa-branca' ), get_option( 'rosa_branca_footer_instagram_url' ) ); ?>
			<?php rosa_branca_admin_url_row( 'rosa_branca_footer_facebook_url', __( 'Facebook', 'rosa-branca' ), get_option( 'rosa_branca_footer_facebook_url' ) ); ?>
			<?php rosa_branca_admin_url_row( 'rosa_branca_footer_youtube_url', __( 'YouTube', 'rosa-branca' ), get_option( 'rosa_branca_footer_youtube_url' ) ); ?>

			<h2><?php esc_html_e( 'Barra legal (rodapé inferior)', 'rosa-branca' ); ?></h2>
			<?php rosa_branca_admin_text_row( 'rosa_branca_footer_disclaimer_text', __( 'Texto', 'rosa-branca' ), get_option( 'rosa_branca_footer_disclaimer_text' ) ); ?>

			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}
