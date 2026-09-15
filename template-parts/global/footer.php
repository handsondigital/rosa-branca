<?php
/**
 * Site footer: brand blurb, menu column, contact/social column, partner
 * logo, and the bottom legal disclaimer bar.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Figma (node 2:6 footer "608:1991") literally shows "Lorem Ipsum" as the
// label for all 6 menu items — not adapted. Destinations stay real/correct;
// aria-label carries the real page name so the link remains usable for
// screen-reader users even though the visible label is the placeholder text.
$rosa_branca_footer_menu = array(
	array( 'label' => __( 'Lorem Ipsum', 'rosa-branca' ), 'aria_label' => __( 'Home', 'rosa-branca' ), 'url' => home_url( '/' ) ),
	array( 'label' => __( 'Lorem Ipsum', 'rosa-branca' ), 'aria_label' => __( 'Sobre a Marca', 'rosa-branca' ), 'url' => home_url( '/sobre-a-marca/' ) ),
	array( 'label' => __( 'Lorem Ipsum', 'rosa-branca' ), 'aria_label' => __( 'Produtos', 'rosa-branca' ), 'url' => home_url( '/produtos/' ) ),
	array( 'label' => __( 'Lorem Ipsum', 'rosa-branca' ), 'aria_label' => __( 'Receitas', 'rosa-branca' ), 'url' => home_url( '/receitas/' ) ),
	array( 'label' => __( 'Lorem Ipsum', 'rosa-branca' ), 'aria_label' => __( 'Onde Comprar', 'rosa-branca' ), 'url' => home_url( '/onde-comprar/' ) ),
	array( 'label' => __( 'Lorem Ipsum', 'rosa-branca' ), 'aria_label' => __( 'Fale Conosco', 'rosa-branca' ), 'url' => home_url( '/fale-conosco/' ) ),
);
?>
<footer class="site-footer">
	<div class="site-footer__grid">
		<div class="site-footer__brand">
			<?php rosa_branca_icon( 'footer-logo-big' ); ?>
			<?php
			// Figma's text layer (607:1901) literally has this paragraph twice —
			// not adapted, per the "follow Figma 100%" instruction.
			$rosa_branca_footer_blurb = __( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin maximus magna vel orci iaculis tincidunt. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Cras non mauris turpis.', 'rosa-branca' );
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
					<a href="<?php echo esc_url( $item['url'] ); ?>" aria-label="<?php echo esc_attr( $item['aria_label'] ); ?>"><?php echo esc_html( $item['label'] ); ?></a>
				<?php endforeach; ?>
			</nav>
		</div>

		<div class="site-footer__contact">
			<p class="site-footer__heading"><?php esc_html_e( 'Contato', 'rosa-branca' ); ?></p>
			<div class="site-footer__contact-item">
				<?php rosa_branca_icon( 'footer-whatsapp' ); ?>
				<span><?php esc_html_e( 'Lorem Ipsum', 'rosa-branca' ); ?></span>
			</div>
			<div class="site-footer__contact-item">
				<?php rosa_branca_icon( 'footer-phone' ); ?>
				<span><?php esc_html_e( 'Lorem Ipsum', 'rosa-branca' ); ?></span>
			</div>

			<p class="site-footer__heading"><?php esc_html_e( 'Redes Sociais', 'rosa-branca' ); ?></p>
			<div class="site-footer__social">
				<a href="#" aria-label="Instagram"><?php rosa_branca_icon( 'instagram' ); ?></a>
				<a href="#" aria-label="Facebook"><?php rosa_branca_icon( 'facebook' ); ?></a>
				<a href="#" aria-label="YouTube"><?php rosa_branca_icon( 'youtube' ); ?></a>
			</div>

			<div class="site-footer__partner">
				<?php rosa_branca_icon( 'bunge-logo-white' ); ?>
			</div>
		</div>
	</div>
</footer>

<div class="site-footer__disclaimer">
	<p><?php esc_html_e( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.', 'rosa-branca' ); ?></p>
</div>
