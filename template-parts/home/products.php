<?php
/**
 * "Produtos" teaser: intro text on the left, carousel of product photos on
 * the right (mirrored layout from the recipes section, per Figma). Section
 * title/text/button link editable (inc/home-fields.php, CONTENT_MODEL.md).
 * Cards come from real `produto` posts once any exist; falls back to the
 * theme's static placeholder images otherwise — same fallback shape as
 * recipes.php.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rosa_branca_product_posts = get_posts(
	array(
		'post_type'      => 'produto',
		'post_status'    => 'publish',
		'posts_per_page' => 3,
		'orderby'        => 'date',
		'order'          => 'DESC',
	)
);

$rosa_branca_products = array();

if ( $rosa_branca_product_posts ) {
	foreach ( $rosa_branca_product_posts as $product_post ) {
		$rosa_branca_products[] = array(
			'thumbnail_id' => get_post_thumbnail_id( $product_post ),
			'alt'          => get_the_title( $product_post ),
		);
	}
} else {
	$rosa_branca_products = array(
		array( 'image' => 'farinha-home-1', 'alt' => __( 'Farinha de trigo Rosa Branca', 'rosa-branca' ) ),
		array( 'image' => 'farinha-home-2', 'alt' => __( 'Farinha de trigo Rosa Branca especial', 'rosa-branca' ) ),
		array( 'image' => 'farinha-home-3', 'alt' => __( 'Farinha de trigo Rosa Branca integral', 'rosa-branca' ) ),
	);
}

$rosa_branca_home_id            = rosa_branca_home_page_id();
$rosa_branca_products_intro_link = get_post_meta( $rosa_branca_home_id, 'rosa_branca_products_intro_link', true );
?>
<section class="home-section home-section--bleed home-section--bleed-right">
	<div class="home-section__row">
		<div class="home-section__content">
			<h2 class="home-section__title"><?php echo esc_html( get_post_meta( $rosa_branca_home_id, 'rosa_branca_products_intro_title', true ) ); ?></h2>
			<p class="home-section__text"><?php echo esc_html( get_post_meta( $rosa_branca_home_id, 'rosa_branca_products_intro_text', true ) ); ?></p>
			<?php if ( $rosa_branca_products_intro_link ) : ?>
				<a class="btn btn--red" href="<?php echo esc_url( $rosa_branca_products_intro_link ); ?>"><?php esc_html_e( 'lorem ipsum', 'rosa-branca' ); ?></a>
			<?php endif; ?>
		</div>

		<div class="home-section__media" data-reveal-group>
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
					<div class="home-products__item" data-reveal-item>
						<?php if ( ! empty( $product['thumbnail_id'] ) ) : ?>
							<?php rosa_branca_dynamic_picture( $product['thumbnail_id'], 'produto-card', array( 'alt' => $product['alt'] ) ); ?>
						<?php else : ?>
							<?php rosa_branca_picture( $product['image'], array( 'alt' => $product['alt'] ) ); ?>
						<?php endif; ?>
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
