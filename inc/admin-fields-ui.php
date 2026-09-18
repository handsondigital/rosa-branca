<?php
/**
 * Shared render primitives for the native (no ACF) meta boxes in
 * inc/home-fields.php / inc/fale-conosco-fields.php — plain HTML form rows,
 * styled by assets/admin/meta-boxes.css. Kept here once instead of typed
 * out per meta box (Reuse-first, CLAUDE.md).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function rosa_branca_admin_text_row( string $name, string $label, string $value, string $help = '' ): void {
	printf(
		'<p class="rb-field"><label for="%1$s">%2$s</label><input type="text" class="widefat" id="%1$s" name="%1$s" value="%3$s"></p>',
		esc_attr( $name ),
		esc_html( $label ),
		esc_attr( $value )
	);
	if ( $help ) {
		printf( '<p class="description">%s</p>', esc_html( $help ) );
	}
}

function rosa_branca_admin_textarea_row( string $name, string $label, string $value ): void {
	printf(
		'<p class="rb-field"><label for="%1$s">%2$s</label><textarea class="widefat" rows="4" id="%1$s" name="%1$s">%3$s</textarea></p>',
		esc_attr( $name ),
		esc_html( $label ),
		esc_textarea( $value )
	);
}

/** Optional by design (CONTENT_MODEL.md: every button link is optional —
 *  empty means the button is omitted). The help text says so explicitly,
 *  so an editor doesn't mistake the empty field for a bug. */
function rosa_branca_admin_url_row( string $name, string $label, string $value ): void {
	printf(
		'<p class="rb-field"><label for="%1$s">%2$s</label><input type="url" class="widefat" id="%1$s" name="%1$s" value="%3$s" placeholder="https://"></p><p class="description">%4$s</p>',
		esc_attr( $name ),
		esc_html( $label ),
		esc_attr( $value ),
		esc_html__( 'Opcional — deixe em branco para não exibir o botão.', 'rosa-branca' )
	);
}

/**
 * Image picker: a hidden input holding the attachment ID + a preview +
 * Selecionar/Remover buttons wired up by assets/admin/meta-boxes.js
 * (wp.media — WordPress's own native media modal, already loaded on every
 * post-edit screen; no new dependency).
 */
function rosa_branca_admin_image_row( string $name, string $label, int $attachment_id ): void {
	$src = $attachment_id ? wp_get_attachment_image_url( $attachment_id, 'medium' ) : '';
	printf(
		'<div class="rb-field rb-image-field" data-rb-image-field>
			<label>%1$s</label>
			<div class="rb-image-field__preview" %2$s>%3$s</div>
			<input type="hidden" name="%4$s" value="%5$d" data-rb-image-input>
			<p>
				<button type="button" class="button" data-rb-image-select>%6$s</button>
				<button type="button" class="button" data-rb-image-remove %7$s>%8$s</button>
			</p>
		</div>',
		esc_html( $label ),
		$src ? '' : 'hidden',
		$src ? '<img src="' . esc_url( $src ) . '" alt="">' : '',
		esc_attr( $name ),
		(int) $attachment_id,
		esc_html__( 'Selecionar imagem', 'rosa-branca' ),
		$attachment_id ? '' : 'hidden',
		esc_html__( 'Remover', 'rosa-branca' )
	);
}

/** Every meta box save handler in home-fields.php/fale-conosco-fields.php
 *  checks this same nonce — one field name, shared, since they always save
 *  together (one form submit per Page edit screen). */
function rosa_branca_admin_fields_nonce_field(): void {
	wp_nonce_field( 'rosa_branca_save_fields', 'rosa_branca_fields_nonce' );
}

function rosa_branca_admin_fields_nonce_is_valid(): bool {
	return isset( $_POST['rosa_branca_fields_nonce'] )
		&& wp_verify_nonce( wp_unslash( $_POST['rosa_branca_fields_nonce'] ), 'rosa_branca_save_fields' );
}

/**
 * Only on the Page edit screen — these meta boxes only ever appear there
 * (rosa_branca_add_home_meta_boxes()/rosa_branca_add_fale_conosco_meta_boxes()
 * already restrict themselves to the one specific Home/Fale Conosco Page),
 * so there's no reason to load this on every other wp-admin screen.
 */
function rosa_branca_enqueue_admin_field_assets( string $hook ): void {
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) || 'page' !== get_current_screen()->post_type ) {
		return;
	}

	wp_enqueue_media();
	wp_enqueue_script( 'rosa-branca-admin-fields', get_theme_file_uri( 'assets/admin/meta-boxes.js' ), array(), '1.0.0', true );
	wp_enqueue_style( 'rosa-branca-admin-fields', get_theme_file_uri( 'assets/admin/meta-boxes.css' ), array(), '1.0.0' );
}
add_action( 'admin_enqueue_scripts', 'rosa_branca_enqueue_admin_field_assets' );
