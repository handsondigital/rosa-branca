<?php
/**
 * Native (no ACF/Gutenberg) editable fields for the Fale Conosco page —
 * see CONTENT_MODEL.md's "FALE CONOSCO" section. Scoped to the Page whose
 * slug is `fale-conosco` (page-fale-conosco.php's own routing convention —
 * see functions.php's `is_page( 'fale-conosco' )` asset-loading check),
 * not a fixed hardcoded post ID.
 *
 * The hero's title is intentionally NOT one of these fields — it reuses
 * that Page's own native `post_title` (CONTENT_MODEL.md: avoids a
 * duplicate title field on a Page that already has one).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function rosa_branca_is_editing_fale_conosco_page( \WP_Post $post ): bool {
	return 'page' === $post->post_type && 'fale-conosco' === $post->post_name;
}

/** Shared front-end read helper — template-parts/fale-conosco/*.php all
 *  need "this meta key, on the Fale Conosco page, or '' if that page
 *  doesn't exist yet" (kept here, not redeclared per-template, since a
 *  template part can in principle be loaded more than once per request). */
function rosa_branca_fc_meta( int $page_id, string $key ): string {
	return $page_id ? get_post_meta( $page_id, $key, true ) : '';
}

function rosa_branca_register_fale_conosco_meta(): void {
	$text_fields = array(
		'rosa_branca_contact_hero_text'          => __( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse ut massa neque. Etiam egestas magna sit amet elit accumsan tristique in ut nunc. Sed porta, ex eget ornare facilisis, dui libero bibendum enim, vitae sodales turpis ex vel nisi.', 'rosa-branca' ),
		'rosa_branca_contact_form_intro_title'    => __( 'Lorem Ipsum', 'rosa-branca' ),
		'rosa_branca_contact_form_intro_text'     => __( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse ut massa neque. Etiam egestas magna sit amet elit accumsan tristique in ut nunc.', 'rosa-branca' ),
		'rosa_branca_contact_details_intro_title' => __( 'Lorem Ipsum', 'rosa-branca' ),
		'rosa_branca_contact_details_intro_text'  => __( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse ut massa neque.', 'rosa-branca' ),
		'rosa_branca_contact_placeholder_name'    => __( 'Nome completo', 'rosa-branca' ),
		'rosa_branca_contact_placeholder_email'   => __( 'E-mail', 'rosa-branca' ),
		'rosa_branca_contact_placeholder_phone'   => __( 'Telefone / WhatsApp', 'rosa-branca' ),
		'rosa_branca_contact_placeholder_message' => __( 'Mensagem', 'rosa-branca' ),
		'rosa_branca_contact_error_name'           => __( 'Informe seu nome.', 'rosa-branca' ),
		'rosa_branca_contact_error_email'          => __( 'Informe um e-mail válido.', 'rosa-branca' ),
		'rosa_branca_contact_error_phone'          => __( 'Informe um telefone para contato.', 'rosa-branca' ),
		'rosa_branca_contact_error_message'        => __( 'Escreva sua mensagem.', 'rosa-branca' ),
	);

	foreach ( $text_fields as $key => $default ) {
		$is_textarea = str_ends_with( $key, '_text' );
		register_post_meta(
			'page',
			$key,
			array(
				'type'              => 'string',
				'single'            => true,
				'default'           => $default,
				'show_in_rest'      => true,
				'sanitize_callback' => $is_textarea ? 'rosa_branca_sanitize_textarea' : 'rosa_branca_sanitize_text',
				'auth_callback'     => static fn(): bool => current_user_can( 'edit_pages' ),
			)
		);
	}

	register_post_meta(
		'page',
		'rosa_branca_contact_hero_photo_id',
		array(
			'type'              => 'integer',
			'single'            => true,
			'default'           => 0,
			'show_in_rest'      => true,
			'sanitize_callback' => 'absint',
			'auth_callback'     => static fn(): bool => current_user_can( 'edit_pages' ),
		)
	);

	// No default: same reasoning as rosa_branca_banner_slides (inc/home-
	// fields.php) — falls back to today's hardcoded 4 channels via
	// rosa_branca_contact_channels() below, not a meta default.
	register_post_meta(
		'page',
		'rosa_branca_contact_channels',
		array(
			'type'              => 'array',
			'single'            => true,
			'default'           => array(),
			'show_in_rest'      => false,
			'sanitize_callback' => 'rosa_branca_sanitize_contact_channels',
			'auth_callback'     => static fn(): bool => current_user_can( 'edit_pages' ),
		)
	);
}
add_action( 'init', 'rosa_branca_register_fale_conosco_meta' );

/** Front-end read helper — real channels if configured, else today's exact
 *  4 hardcoded ones (same fallback shape as rosa_branca_home_banner_slides()). */
function rosa_branca_contact_channels(): array {
	$page = get_page_by_path( 'fale-conosco' );
	$saved = $page ? get_post_meta( $page->ID, 'rosa_branca_contact_channels', true ) : array();

	if ( ! empty( $saved ) ) {
		return $saved;
	}

	$lines = array( __( 'Lorem ipsum dolor sit amet', 'rosa-branca' ), __( 'Lorem ipsum dolor sit amet', 'rosa-branca' ) );
	return array(
		array( 'icon' => 'headset-sac', 'title' => __( 'Lorem ipsum dolor', 'rosa-branca' ), 'line1' => $lines[0], 'line2' => $lines[1] ),
		array( 'icon' => 'envelope', 'title' => __( 'Lorem ipsum dolor', 'rosa-branca' ), 'line1' => $lines[0], 'line2' => $lines[1] ),
		array( 'icon' => 'contact-whatsapp', 'title' => __( 'Lorem ipsum dolor', 'rosa-branca' ), 'line1' => $lines[0], 'line2' => $lines[1] ),
		array( 'icon' => 'marker', 'title' => __( 'Lorem ipsum dolor', 'rosa-branca' ), 'line1' => $lines[0], 'line2' => $lines[1] ),
	);
}

function rosa_branca_add_fale_conosco_meta_boxes( string $post_type, \WP_Post $post ): void {
	if ( 'page' !== $post_type || ! rosa_branca_is_editing_fale_conosco_page( $post ) ) {
		return;
	}

	add_meta_box( 'rb_fc_hero', __( 'Fale Conosco — Hero', 'rosa-branca' ), 'rosa_branca_render_fc_hero_box', 'page', 'normal', 'high' );
	add_meta_box( 'rb_fc_form_intro', __( 'Fale Conosco — Formulário (intro)', 'rosa-branca' ), 'rosa_branca_render_fc_form_intro_box', 'page', 'normal', 'default' );
	add_meta_box( 'rb_fc_form_fields', __( 'Fale Conosco — Formulário (placeholders e erros)', 'rosa-branca' ), 'rosa_branca_render_fc_form_fields_box', 'page', 'normal', 'default' );
	add_meta_box( 'rb_fc_channels', __( 'Fale Conosco — Canais de contato', 'rosa-branca' ), 'rosa_branca_render_fc_channels_box', 'page', 'normal', 'default' );
}
add_action( 'add_meta_boxes', 'rosa_branca_add_fale_conosco_meta_boxes', 10, 2 );

function rosa_branca_render_fc_hero_box( \WP_Post $post ): void {
	rosa_branca_admin_fields_nonce_field();
	printf( '<p class="description">%s</p>', esc_html__( 'O título usa o título da própria Página (campo no topo desta tela).', 'rosa-branca' ) );
	rosa_branca_admin_textarea_row( 'rosa_branca_contact_hero_text', __( 'Parágrafo', 'rosa-branca' ), get_post_meta( $post->ID, 'rosa_branca_contact_hero_text', true ) );
	rosa_branca_admin_image_row( 'rosa_branca_contact_hero_photo_id', __( 'Foto de fundo', 'rosa-branca' ), (int) get_post_meta( $post->ID, 'rosa_branca_contact_hero_photo_id', true ) );
}

function rosa_branca_render_fc_form_intro_box( \WP_Post $post ): void {
	rosa_branca_admin_fields_nonce_field();
	rosa_branca_admin_text_row( 'rosa_branca_contact_form_intro_title', __( 'Título', 'rosa-branca' ), get_post_meta( $post->ID, 'rosa_branca_contact_form_intro_title', true ) );
	rosa_branca_admin_textarea_row( 'rosa_branca_contact_form_intro_text', __( 'Parágrafo', 'rosa-branca' ), get_post_meta( $post->ID, 'rosa_branca_contact_form_intro_text', true ) );
}

function rosa_branca_render_fc_form_fields_box( \WP_Post $post ): void {
	rosa_branca_admin_fields_nonce_field();
	foreach ( array( 'name' => __( 'Nome', 'rosa-branca' ), 'email' => __( 'E-mail', 'rosa-branca' ), 'phone' => __( 'Telefone', 'rosa-branca' ), 'message' => __( 'Mensagem', 'rosa-branca' ) ) as $field => $label ) {
		rosa_branca_admin_text_row( "rosa_branca_contact_placeholder_{$field}", sprintf( __( 'Placeholder — %s', 'rosa-branca' ), $label ), get_post_meta( $post->ID, "rosa_branca_contact_placeholder_{$field}", true ) );
		rosa_branca_admin_text_row( "rosa_branca_contact_error_{$field}", sprintf( __( 'Mensagem de erro — %s', 'rosa-branca' ), $label ), get_post_meta( $post->ID, "rosa_branca_contact_error_{$field}", true ) );
	}
}

function rosa_branca_render_fc_channels_box( \WP_Post $post ): void {
	rosa_branca_admin_fields_nonce_field();
	rosa_branca_admin_text_row( 'rosa_branca_contact_details_intro_title', __( 'Título (bloco de detalhes de contato)', 'rosa-branca' ), get_post_meta( $post->ID, 'rosa_branca_contact_details_intro_title', true ) );
	rosa_branca_admin_textarea_row( 'rosa_branca_contact_details_intro_text', __( 'Parágrafo', 'rosa-branca' ), get_post_meta( $post->ID, 'rosa_branca_contact_details_intro_text', true ) );
	$channels = get_post_meta( $post->ID, 'rosa_branca_contact_channels', true );
	if ( ! is_array( $channels ) ) {
		$channels = array();
	}
	?>
	<div data-rb-repeater="rosa_branca_contact_channels">
		<div data-rb-repeater-rows>
			<?php foreach ( $channels as $index => $channel ) : ?>
				<?php rosa_branca_render_contact_channel_row( $index, $channel ); ?>
			<?php endforeach; ?>
		</div>
		<template data-rb-repeater-template>
			<?php rosa_branca_render_contact_channel_row( '__INDEX__', array() ); ?>
		</template>
		<p><button type="button" class="button" data-rb-repeater-add><?php esc_html_e( 'Adicionar canal', 'rosa-branca' ); ?></button></p>
	</div>
	<?php
}

function rosa_branca_render_contact_channel_row( $index, array $channel ): void {
	$prefix = "rosa_branca_contact_channels[{$index}]";
	$icon   = $channel['icon'] ?? 'headset-sac';
	?>
	<div class="rb-repeater-row" data-rb-repeater-row>
		<button type="button" class="button-link rb-repeater-row__remove" data-rb-repeater-remove><?php esc_html_e( 'Remover canal', 'rosa-branca' ); ?></button>
		<p class="rb-field">
			<label><?php esc_html_e( 'Ícone', 'rosa-branca' ); ?></label>
			<select name="<?php echo esc_attr( "{$prefix}[icon]" ); ?>">
				<?php foreach ( array( 'headset-sac' => 'SAC', 'envelope' => 'E-mail', 'contact-whatsapp' => 'WhatsApp', 'marker' => 'Endereço' ) as $value => $label ) : ?>
					<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $icon, $value ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		<?php rosa_branca_admin_text_row( "{$prefix}[title]", __( 'Título do canal', 'rosa-branca' ), $channel['title'] ?? '' ); ?>
		<?php rosa_branca_admin_text_row( "{$prefix}[line1]", __( 'Linha 1', 'rosa-branca' ), $channel['line1'] ?? '' ); ?>
		<?php rosa_branca_admin_text_row( "{$prefix}[line2]", __( 'Linha 2 (opcional)', 'rosa-branca' ), $channel['line2'] ?? '' ); ?>
	</div>
	<?php
}

function rosa_branca_save_fale_conosco_meta( int $post_id, \WP_Post $post ): void {
	if ( ! rosa_branca_admin_fields_nonce_is_valid() || ! rosa_branca_is_editing_fale_conosco_page( $post ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_page', $post_id ) ) {
		return;
	}

	$text_keys = array(
		'rosa_branca_contact_hero_text',
		'rosa_branca_contact_hero_photo_id',
		'rosa_branca_contact_form_intro_title',
		'rosa_branca_contact_form_intro_text',
		'rosa_branca_contact_details_intro_title',
		'rosa_branca_contact_details_intro_text',
		'rosa_branca_contact_placeholder_name',
		'rosa_branca_contact_placeholder_email',
		'rosa_branca_contact_placeholder_phone',
		'rosa_branca_contact_placeholder_message',
		'rosa_branca_contact_error_name',
		'rosa_branca_contact_error_email',
		'rosa_branca_contact_error_phone',
		'rosa_branca_contact_error_message',
	);

	foreach ( $text_keys as $key ) {
		if ( isset( $_POST[ $key ] ) ) {
			update_post_meta( $post_id, $key, wp_unslash( $_POST[ $key ] ) );
		}
	}

	if ( isset( $_POST['rosa_branca_contact_channels'] ) && is_array( $_POST['rosa_branca_contact_channels'] ) ) {
		update_post_meta( $post_id, 'rosa_branca_contact_channels', wp_unslash( $_POST['rosa_branca_contact_channels'] ) );
	} else {
		update_post_meta( $post_id, 'rosa_branca_contact_channels', array() );
	}
}
add_action( 'save_post_page', 'rosa_branca_save_fale_conosco_meta', 10, 2 );
