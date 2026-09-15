<?php
/**
 * "Receitas" teaser: intro text + a carousel of recipe cards. Content below
 * is placeholder pending a real recipes source (out of the current scope —
 * PAGES_PLAN.md §15 restricts this delivery to Home + Fale Conosco only).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Literal Figma placeholder copy (node 2:6) — not adapted. The source file
// shows exactly 3 "Caixa de Receita" instances, so that's what renders here
// (an earlier version padded this to 5 to give the carousel more range to
// click through, but that meant inventing 2 cards not present in Figma).
// With only 3 cards and ~2.5 visible at once, "next" reaches the end after
// one click — that's the real, correct behaviour for this exact content.
// No per-recipe page/slug exists yet (individual recipe pages are out of
// PAGES_PLAN.md's current scope, same as /receitas/ itself) — links to the
// same /receitas/ URL as the section's own CTA button below, as a
// placeholder destination until real recipe content/routing exists.
$rosa_branca_recipe_card = array(
	'eyebrow'    => __( 'receitas', 'rosa-branca' ),
	'title'      => __( 'Lorem Ipsum Dolor', 'rosa-branca' ),
	'excerpt'    => __( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Curabitur congue condimentum erat, at accumsan risus rhoncus id.', 'rosa-branca' ),
	'time'       => '2h 30min.',
	'difficulty' => __( 'Fácil', 'rosa-branca' ),
	'url'        => home_url( '/receitas/' ),
);
$rosa_branca_recipes = array_fill( 0, 3, $rosa_branca_recipe_card );
?>
<section class="home-section home-section--bleed home-section--bleed-left">
	<div class="home-section__row">
		<div class="home-section__media">
			<div
				class="home-recipes__carousel"
				data-carousel-mount
				data-variant="peek"
				data-arrows="true"
				data-dots="true"
				data-controls-target="[data-carousel-controls='recipes']"
				data-gap="50"
				data-label="<?php esc_attr_e( 'Receitas em destaque', 'rosa-branca' ); ?>"
			>
				<?php foreach ( $rosa_branca_recipes as $recipe ) : ?>
					<article class="recipe-card">
						<div class="recipe-card__media">
							<?php rosa_branca_picture( 'recipe-card-placeholder', array( 'alt' => '' ) ); ?>
						</div>
						<div class="recipe-card__body">
							<span class="recipe-card__eyebrow"><?php esc_html_e( 'Receitas', 'rosa-branca' ); ?></span>
							<h3 class="recipe-card__title">
								<a class="recipe-card__link" href="<?php echo esc_url( $recipe['url'] ); ?>"><?php echo esc_html( $recipe['title'] ); ?></a>
							</h3>
							<p class="recipe-card__description"><?php echo esc_html( $recipe['excerpt'] ); ?></p>
							<div class="recipe-card__meta">
								<?php rosa_branca_icon( 'clock' ); ?>
								<span><?php echo esc_html( $recipe['time'] ); ?></span>
								<span class="recipe-card__meta-divider" aria-hidden="true"></span>
								<?php rosa_branca_icon( 'difficulty' ); ?>
								<span><?php echo esc_html( $recipe['difficulty'] ); ?></span>
							</div>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
			<!-- Carousel.jsx portals arrows before dots into this shared target
			     (see Carousel.jsx) — natural row order puts arrows at the
			     media's outer edge (the bleeding side, left here) and dots at
			     its inner edge (nearest the text column, right); mirrored
			     from Products, which reverses the row instead. -->
			<div class="carousel__controls" data-carousel-controls="recipes">
				<div class="carousel__arrows-placeholder" aria-hidden="true"></div>
			</div>
		</div>

		<div class="home-section__content">
			<h2 class="home-section__title"><?php esc_html_e( 'Lorem ipsum dolor sit amet', 'rosa-branca' ); ?></h2>
			<p class="home-section__text"><?php esc_html_e( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse ut massa neque. Etiam egestas magna sit amet elit accumsan tristique in ut nunc. Sed porta, ex eget ornare facilisis, dui libero bibendum enim, vitae sodales turpis ex vel nisi. Phasellus felis odio, egestas sed elit in, finibus dapibus dui. Integer purus nunc, hendrerit eu odio nec, bibendum fringilla erat. Quisque condimentum lectus nec hendrerit ullamcorper. Proin vestibulum eros sit amet diam feugiat rhoncus.', 'rosa-branca' ); ?></p>
			<a class="btn btn--red" href="<?php echo esc_url( home_url( '/receitas/' ) ); ?>"><?php esc_html_e( 'lorem ipsum', 'rosa-branca' ); ?></a>
		</div>
	</div>
</section>
