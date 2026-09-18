<?php
/**
 * "Título e Leade" intro + contact-detail cards (SAC, e-mail, WhatsApp,
 * endereço by default). Intro title/text editable (inc/fale-conosco-fields.php);
 * the cards themselves come from the "Canais de contato" repeater —
 * rosa_branca_contact_channels() falls back to today's exact 4 hardcoded
 * channels until an editor configures real ones. `line2` is optional per
 * channel (CONTENT_MODEL.md) — omitted entirely when empty, not left as a
 * blank line, so a one-line channel doesn't leave dead vertical space.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rosa_branca_fc_page = get_page_by_path( 'fale-conosco' );
$rosa_branca_fc_id   = $rosa_branca_fc_page ? $rosa_branca_fc_page->ID : 0;
?>
<div class="contact-details">
	<div class="contact-details__intro">
		<h2 class="contact-details__title"><?php echo esc_html( rosa_branca_fc_meta( $rosa_branca_fc_id, 'rosa_branca_contact_details_intro_title' ) ); ?></h2>
		<p><?php echo esc_html( rosa_branca_fc_meta( $rosa_branca_fc_id, 'rosa_branca_contact_details_intro_text' ) ); ?></p>
	</div>

	<div class="contact-details__list" data-reveal-group>
		<?php foreach ( rosa_branca_contact_channels() as $channel ) : ?>
			<div class="contact-detail" data-reveal-item>
				<span class="contact-detail__icon">
					<?php rosa_branca_icon( $channel['icon'] ); ?>
				</span>
				<div>
					<p class="contact-detail__title"><?php echo esc_html( $channel['title'] ); ?></p>
					<span class="contact-detail__text"><?php echo esc_html( $channel['line1'] ); ?></span>
					<?php if ( ! empty( $channel['line2'] ) ) : ?>
						<span class="contact-detail__text"><?php echo esc_html( $channel['line2'] ); ?></span>
					<?php endif; ?>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</div>
