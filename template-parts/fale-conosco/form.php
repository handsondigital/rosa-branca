<?php
/**
 * Contact form — front-end only for now (per project decision): real fields,
 * client-side validation and success/error UI states are implemented; the
 * submit handler (wp_mail / REST / CRM integration) is intentionally not
 * wired yet, pending a decision on where submissions should go.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="contact-form-card" data-contact-form>
	<div class="contact-form-card__intro">
		<h2 class="contact-form-card__title"><?php esc_html_e( 'Lorem Ipsum', 'rosa-branca' ); ?></h2>
		<p><?php esc_html_e( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse ut massa neque. Etiam egestas magna sit amet elit accumsan tristique in ut nunc.', 'rosa-branca' ); ?></p>
	</div>

	<form class="contact-form" novalidate>
		<div class="field" data-field>
			<label class="visually-hidden" for="contact-name"><?php esc_html_e( 'Nome completo', 'rosa-branca' ); ?></label>
			<input class="field__control" type="text" id="contact-name" name="name" placeholder="<?php esc_attr_e( 'Default', 'rosa-branca' ); ?>" autocomplete="name" required />
			<span class="field__error"><?php esc_html_e( 'Informe seu nome.', 'rosa-branca' ); ?></span>
		</div>

		<div class="field" data-field>
			<label class="visually-hidden" for="contact-email"><?php esc_html_e( 'E-mail', 'rosa-branca' ); ?></label>
			<input class="field__control" type="email" id="contact-email" name="email" placeholder="<?php esc_attr_e( 'Default', 'rosa-branca' ); ?>" autocomplete="email" required />
			<span class="field__error"><?php esc_html_e( 'Informe um e-mail válido.', 'rosa-branca' ); ?></span>
		</div>

		<div class="field" data-field>
			<label class="visually-hidden" for="contact-phone"><?php esc_html_e( 'Telefone / WhatsApp', 'rosa-branca' ); ?></label>
			<input class="field__control" type="tel" id="contact-phone" name="phone" placeholder="<?php esc_attr_e( 'Default', 'rosa-branca' ); ?>" autocomplete="tel" required />
			<span class="field__error"><?php esc_html_e( 'Informe um telefone para contato.', 'rosa-branca' ); ?></span>
		</div>

		<div class="field" data-field>
			<label class="visually-hidden" for="contact-message"><?php esc_html_e( 'Mensagem', 'rosa-branca' ); ?></label>
			<textarea class="field__control" id="contact-message" name="message" rows="4" placeholder="<?php esc_attr_e( 'Default', 'rosa-branca' ); ?>" required></textarea>
			<span class="field__error"><?php esc_html_e( 'Escreva sua mensagem.', 'rosa-branca' ); ?></span>
		</div>

		<p class="contact-form__status" role="status" aria-live="polite" data-contact-form-status hidden></p>

		<button type="submit" class="btn btn--red btn--block"><?php esc_html_e( 'lorem ipsum', 'rosa-branca' ); ?></button>
	</form>
</div>
