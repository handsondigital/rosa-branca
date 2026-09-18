<?php
/**
 * Contact form — front-end only for now (per project decision): real fields,
 * client-side validation and success/error UI states are implemented; the
 * submit handler (wp_mail / REST / CRM integration) is intentionally not
 * wired yet, pending a decision on where submissions should go.
 *
 * Intro title/text and each field's placeholder/error message are editable
 * (inc/fale-conosco-fields.php, CONTENT_MODEL.md) — the submit button's
 * label stays fixed in code, same rule as every other CTA in the project.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rosa_branca_fc_page = get_page_by_path( 'fale-conosco' );
$rosa_branca_fc_id   = $rosa_branca_fc_page ? $rosa_branca_fc_page->ID : 0;
?>
<div class="contact-form-card" data-contact-form>
	<div class="contact-form-card__intro">
		<h2 class="contact-form-card__title"><?php echo esc_html( rosa_branca_fc_meta( $rosa_branca_fc_id, 'rosa_branca_contact_form_intro_title' ) ); ?></h2>
		<p><?php echo esc_html( rosa_branca_fc_meta( $rosa_branca_fc_id, 'rosa_branca_contact_form_intro_text' ) ); ?></p>
	</div>

	<form class="contact-form" novalidate>
		<div class="field" data-field>
			<label class="visually-hidden" for="contact-name"><?php esc_html_e( 'Nome completo', 'rosa-branca' ); ?></label>
			<input class="field__control" type="text" id="contact-name" name="name" placeholder="<?php echo esc_attr( rosa_branca_fc_meta( $rosa_branca_fc_id, 'rosa_branca_contact_placeholder_name' ) ); ?>" autocomplete="name" required />
			<span class="field__error"><?php echo esc_html( rosa_branca_fc_meta( $rosa_branca_fc_id, 'rosa_branca_contact_error_name' ) ); ?></span>
		</div>

		<div class="field" data-field>
			<label class="visually-hidden" for="contact-email"><?php esc_html_e( 'E-mail', 'rosa-branca' ); ?></label>
			<input class="field__control" type="email" id="contact-email" name="email" placeholder="<?php echo esc_attr( rosa_branca_fc_meta( $rosa_branca_fc_id, 'rosa_branca_contact_placeholder_email' ) ); ?>" autocomplete="email" required />
			<span class="field__error"><?php echo esc_html( rosa_branca_fc_meta( $rosa_branca_fc_id, 'rosa_branca_contact_error_email' ) ); ?></span>
		</div>

		<div class="field" data-field>
			<label class="visually-hidden" for="contact-phone"><?php esc_html_e( 'Telefone / WhatsApp', 'rosa-branca' ); ?></label>
			<input class="field__control" type="tel" id="contact-phone" name="phone" placeholder="<?php echo esc_attr( rosa_branca_fc_meta( $rosa_branca_fc_id, 'rosa_branca_contact_placeholder_phone' ) ); ?>" autocomplete="tel" required />
			<span class="field__error"><?php echo esc_html( rosa_branca_fc_meta( $rosa_branca_fc_id, 'rosa_branca_contact_error_phone' ) ); ?></span>
		</div>

		<div class="field" data-field>
			<label class="visually-hidden" for="contact-message"><?php esc_html_e( 'Mensagem', 'rosa-branca' ); ?></label>
			<textarea class="field__control" id="contact-message" name="message" rows="4" placeholder="<?php echo esc_attr( rosa_branca_fc_meta( $rosa_branca_fc_id, 'rosa_branca_contact_placeholder_message' ) ); ?>" required></textarea>
			<span class="field__error"><?php echo esc_html( rosa_branca_fc_meta( $rosa_branca_fc_id, 'rosa_branca_contact_error_message' ) ); ?></span>
		</div>

		<p class="contact-form__status" role="status" aria-live="polite" data-contact-form-status hidden></p>

		<button type="submit" class="btn btn--red btn--block"><?php esc_html_e( 'lorem ipsum', 'rosa-branca' ); ?></button>
	</form>
</div>
