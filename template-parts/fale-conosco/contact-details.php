<?php
/**
 * "Título e Leade" intro + 4 contact-detail cards (SAC, e-mail, WhatsApp,
 * endereço). Literal Figma placeholder copy (node 630:6620) — not adapted;
 * every card uses the same "Lorem ipsum dolor" text because that's what the
 * source file has (only one card design was mocked, reused 4x with only
 * the icon actually varying).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rosa_branca_contact_detail_lines = array( __( 'Lorem ipsum dolor sit amet', 'rosa-branca' ), __( 'Lorem ipsum dolor sit amet', 'rosa-branca' ) );
$rosa_branca_contact_details      = array(
	array( 'icon' => 'headset-sac', 'title' => __( 'Lorem ipsum dolor', 'rosa-branca' ), 'lines' => $rosa_branca_contact_detail_lines ),
	array( 'icon' => 'envelope', 'title' => __( 'Lorem ipsum dolor', 'rosa-branca' ), 'lines' => $rosa_branca_contact_detail_lines ),
	array( 'icon' => 'contact-whatsapp', 'title' => __( 'Lorem ipsum dolor', 'rosa-branca' ), 'lines' => $rosa_branca_contact_detail_lines ),
	array( 'icon' => 'marker', 'title' => __( 'Lorem ipsum dolor', 'rosa-branca' ), 'lines' => $rosa_branca_contact_detail_lines ),
);
?>
<div class="contact-details">
	<div class="contact-details__intro">
		<h2 class="contact-details__title"><?php esc_html_e( 'Lorem Ipsum', 'rosa-branca' ); ?></h2>
		<p><?php esc_html_e( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse ut massa neque.', 'rosa-branca' ); ?></p>
	</div>

	<div class="contact-details__list" data-reveal-group>
		<?php foreach ( $rosa_branca_contact_details as $detail ) : ?>
			<div class="contact-detail" data-reveal-item>
				<span class="contact-detail__icon">
					<?php rosa_branca_icon( $detail['icon'] ); ?>
				</span>
				<div>
					<p class="contact-detail__title"><?php echo esc_html( $detail['title'] ); ?></p>
					<?php foreach ( $detail['lines'] as $line ) : ?>
						<span class="contact-detail__text"><?php echo esc_html( $line ); ?></span>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</div>
