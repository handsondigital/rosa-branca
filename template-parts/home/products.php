<?php
/**
 * "Produtos" teaser: intro text on the left, carousel of product photos on
 * the right (mirrored layout from the recipes section, per Figma).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rosa_branca_products = array(
	array( 'image' => 'farinha-home-1', 'alt' => __( 'Farinha de trigo Rosa Branca', 'rosa-branca' ) ),
	array( 'image' => 'farinha-home-2', 'alt' => __( 'Farinha de trigo Rosa Branca especial', 'rosa-branca' ) ),
	array( 'image' => 'farinha-home-3', 'alt' => __( 'Farinha de trigo Rosa Branca integral', 'rosa-branca' ) ),
);
?>
<section class="home-section home-section--bleed home-section--bleed-right" data-reveal>
	<div class="home-section__row">
		<div class="home-section__content">
			<h2 class="home-section__title"><?php esc_html_e( 'Lorem ipsum dolor sit amet', 'rosa-branca' ); ?></h2>
			<p class="home-section__text"><?php esc_html_e( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse ut massa neque. Etiam egestas magna sit amet elit accumsan tristique in ut nunc. Sed porta, ex eget ornare facilisis, dui libero bibendum enim, vitae sodales turpis ex vel nisi. Phasellus felis odio, egestas sed elit in, finibus dapibus dui. Integer purus nunc, hendrerit eu odio nec, bibendum fringilla erat. Quisque condimentum lectus nec hendrerit ullamcorper. Proin vestibulum eros sit amet diam feugiat rhoncus.', 'rosa-branca' ); ?></p>
			<a class="btn btn--red" href="<?php echo esc_url( home_url( '/produtos/' ) ); ?>"><?php esc_html_e( 'lorem ipsum', 'rosa-branca' ); ?></a>
		</div>

		<div class="home-section__media">
			<div
				class="home-products__carousel"
				data-carousel-mount
				data-variant="peek"
				data-arrows="true"
				data-dots="true"
				data-controls-target="[data-carousel-controls='products']"
				data-gap="0"
				data-label="<?php esc_attr_e( 'Produtos Rosa Branca', 'rosa-branca' ); ?>"
			>
				<?php foreach ( $rosa_branca_products as $product ) : ?>
					<div class="home-products__item">
						<?php rosa_branca_picture( $product['image'], array( 'alt' => $product['alt'] ) ); ?>
					</div>
				<?php endforeach; ?>
			</div>
			<!-- Carousel.jsx always portals arrows before dots into this shared
			     target; .home-section--bleed-right reverses the row (CSS) so
			     that lands as dots at the media's inner edge (nearest the text
			     column, left) and arrows at its outer edge (right) — mirrored
			     from Recipes, where media/text swap sides. -->
			<div class="carousel__controls" data-carousel-controls="products">
				<div class="carousel__arrows-placeholder" aria-hidden="true"></div>
			</div>
		</div>
	</div>
</section>
