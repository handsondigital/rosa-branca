<?php
/**
 * Site footer: brand blurb, menu column, contact/social column, partner
 * logo, and the bottom legal disclaimer bar.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Real menu (Aparência → Menus, location "footer") if an editor has
// assigned one, else today's exact hardcoded 6 items — see
// rosa_branca_get_nav_items() (inc/nav-menu.php, CONTENT_MODEL.md). The
// original hardcoded array's literal "Lorem Ipsum" labels were an
// unadapted Figma placeholder, not a deliberate requirement — real labels
// (from a real menu, or the fallback's own real names) are strictly
// better, so this drops that placeholder rather than reproducing it.
$rosa_branca_footer_menu = rosa_branca_get_nav_items( 'footer', rosa_branca_current_url() );
?>
<footer class="site-footer">
	<div class="site-footer__grid">
		<div class="site-footer__brand">
			<?php rosa_branca_icon( 'footer-logo-big' ); ?>
			<?php
			// CONTENT_MODEL.md "Footer (Options page)": ONE field, rendered
			// twice — Figma's own text layer (607:1901) repeats this same
			// paragraph twice, so two independent fields would just be a way
			// for the two copies to drift apart for no reason.
			$rosa_branca_footer_blurb = get_option( 'rosa_branca_footer_brand_text' );
			?>
			<div class="site-footer__brand-text">
				<p><?php echo esc_html( $rosa_branca_footer_blurb ); ?></p>
				<p><?php echo esc_html( $rosa_branca_footer_blurb ); ?></p>
			</div>
		</div>

		<div class="site-footer__menu">
			<p class="site-footer__heading"><?php esc_html_e( 'Menu', 'rosa-branca' ); ?></p>
			<nav class="site-footer__menu-list" aria-label="<?php esc_attr_e( 'Menu do rodapé', 'rosa-branca' ); ?>">
				<?php foreach ( $rosa_branca_footer_menu as $item ) : ?>
					<a href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['label'] ); ?></a>
				<?php endforeach; ?>
			</nav>
		</div>

		<div class="site-footer__contact">
			<p class="site-footer__heading"><?php esc_html_e( 'Contato', 'rosa-branca' ); ?></p>
			<div class="site-footer__contact-item">
				<?php rosa_branca_icon( 'footer-whatsapp' ); ?>
				<span><?php echo esc_html( get_option( 'rosa_branca_footer_whatsapp_text' ) ); ?></span>
			</div>
			<div class="site-footer__contact-item">
				<?php rosa_branca_icon( 'footer-phone' ); ?>
				<span><?php echo esc_html( get_option( 'rosa_branca_footer_phone_text' ) ); ?></span>
			</div>

			<?php
			// Optional per CONTENT_MODEL.md — each link hides its own icon
			// entirely when empty (today's href="#" was never a real
			// destination either), same "omit, don't render a dead link"
			// rule as every other optional link in this project. The
			// "Redes Sociais" heading is part of that same omission: it's
			// dead weight with nothing under it if every link is empty,
			// so it's gated on the same condition, not just the icons row.
			$rosa_branca_social_links = array(
				'instagram' => get_option( 'rosa_branca_footer_instagram_url' ),
				'facebook'  => get_option( 'rosa_branca_footer_facebook_url' ),
				'youtube'   => get_option( 'rosa_branca_footer_youtube_url' ),
			);
			$rosa_branca_has_social_links = array_filter( $rosa_branca_social_links );
			?>
			<?php if ( $rosa_branca_has_social_links ) : ?>
				<p class="site-footer__heading"><?php esc_html_e( 'Redes Sociais', 'rosa-branca' ); ?></p>
				<div class="site-footer__social">
					<?php foreach ( $rosa_branca_social_links as $rosa_branca_network => $rosa_branca_url ) : ?>
						<?php if ( $rosa_branca_url ) : ?>
							<a href="<?php echo esc_url( $rosa_branca_url ); ?>" aria-label="<?php echo esc_attr( ucfirst( $rosa_branca_network ) ); ?>"><?php rosa_branca_icon( $rosa_branca_network ); ?></a>
						<?php endif; ?>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<div class="site-footer__partner">
				<?php rosa_branca_icon( 'bunge-logo-white' ); ?>
			</div>
		</div>
	</div>
</footer>

<div class="site-footer__disclaimer">
	<p><?php echo esc_html( get_option( 'rosa_branca_footer_disclaimer_text' ) ); ?></p>
</div>
