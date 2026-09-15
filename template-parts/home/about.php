<?php
/**
 * "Sobre a Marca" teaser: photo + heading/text/outline-button. Fully static
 * — no interactivity, so no JS at all (PHP/HTML only).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<section class="home-section container" data-reveal>
	<div class="home-section__grid">
		<div class="home-section__media">
			<?php
			rosa_branca_picture(
				'foto-sobre-a-marca',
				array(
					'img_class' => 'home-about__photo',
					'alt'       => __( 'Produção artesanal com farinha Rosa Branca', 'rosa-branca' ),
				)
			);
			?>
		</div>

		<div class="home-section__content">
			<h2 class="home-section__title"><?php esc_html_e( 'Lorem ipsum dolor sit amet', 'rosa-branca' ); ?></h2>
			<p class="home-section__text"><?php esc_html_e( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse ut massa neque. Etiam egestas magna sit amet elit accumsan tristique in ut nunc. Sed porta, ex eget ornare facilisis, dui libero bibendum enim, vitae sodales turpis ex vel nisi. Phasellus felis odio, egestas sed elit in, finibus dapibus dui. Integer purus nunc, hendrerit eu odio nec, bibendum fringilla erat. Quisque condimentum lectus nec hendrerit ullamcorper. Proin vestibulum eros sit amet diam feugiat rhoncus.', 'rosa-branca' ); ?></p>
			<a class="btn btn--outline" href="<?php echo esc_url( home_url( '/sobre-a-marca/' ) ); ?>"><?php esc_html_e( 'lorem ipsum', 'rosa-branca' ); ?></a>
		</div>
	</div>
</section>
