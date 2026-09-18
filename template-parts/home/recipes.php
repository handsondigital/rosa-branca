<?php
/**
 * "Receitas" teaser: intro text + a carousel of recipe cards. Section
 * title/text/button link are editable (inc/home-fields.php,
 * CONTENT_MODEL.md) — button label stays fixed in code either way (same
 * rule for every CTA in the project). Cards come from real `receita`
 * posts once any exist; falls back to the literal Figma placeholder copy
 * (node 2:6, "Caixa de Receita" x3) until an editor adds real recipes —
 * same fallback shape as rosa_branca_home_banner_slides().
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rosa_branca_recipe_posts = get_posts(
	array(
		'post_type'      => 'receita',
		'post_status'    => 'publish',
		'posts_per_page' => 3,
		'orderby'        => 'date',
		'order'          => 'DESC',
	)
);

$rosa_branca_recipes = array();

if ( $rosa_branca_recipe_posts ) {
	foreach ( $rosa_branca_recipe_posts as $recipe_post ) {
		$rosa_branca_recipes[] = array(
			'thumbnail_id' => get_post_thumbnail_id( $recipe_post ),
			'title'        => get_the_title( $recipe_post ),
			'excerpt'      => get_the_excerpt( $recipe_post ),
			// No per-recipe page/slug exists yet (PAGES_PLAN.md §15) — every
			// card links to the section's own placeholder /receitas/ URL
			// until individual recipe routing is in scope.
			'url'          => home_url( '/receitas/' ),
			'time'         => get_post_meta( $recipe_post->ID, 'tempo_preparo', true ),
			'difficulty'   => rosa_branca_dificuldade_label( get_post_meta( $recipe_post->ID, 'dificuldade', true ) ),
		);
	}
} else {
	$rosa_branca_recipes = array_fill(
		0,
		3,
		array(
			'thumbnail_id' => 0,
			'title'        => __( 'Lorem Ipsum Dolor', 'rosa-branca' ),
			'excerpt'      => __( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Curabitur congue condimentum erat, at accumsan risus rhoncus id.', 'rosa-branca' ),
			'url'          => home_url( '/receitas/' ),
			'time'         => '2h 30min.',
			'difficulty'   => __( 'Fácil', 'rosa-branca' ),
		)
	);
}

$rosa_branca_home_id           = rosa_branca_home_page_id();
$rosa_branca_recipes_intro_link = get_post_meta( $rosa_branca_home_id, 'rosa_branca_recipes_intro_link', true );
?>
<section class="home-section home-section--bleed home-section--bleed-left">
	<div class="home-section__row">
		<div class="home-section__media" data-reveal-group>
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
					<article class="recipe-card" data-reveal-item>
						<div class="recipe-card__media">
							<?php if ( $recipe['thumbnail_id'] ) : ?>
								<?php rosa_branca_dynamic_picture( $recipe['thumbnail_id'], 'receita-card', array( 'alt' => '' ) ); ?>
							<?php else : ?>
								<?php rosa_branca_picture( 'recipe-card-placeholder', array( 'alt' => '' ) ); ?>
							<?php endif; ?>
						</div>
						<div class="recipe-card__body">
							<span class="recipe-card__eyebrow"><?php esc_html_e( 'Receitas', 'rosa-branca' ); ?></span>
							<h3 class="recipe-card__title">
								<a class="recipe-card__link" href="<?php echo esc_url( $recipe['url'] ); ?>"><?php echo esc_html( $recipe['title'] ); ?></a>
							</h3>
							<p class="recipe-card__description"><?php echo esc_html( $recipe['excerpt'] ); ?></p>
							<div class="recipe-card__meta">
								<?php if ( $recipe['time'] ) : ?>
									<?php rosa_branca_icon( 'clock' ); ?>
									<span><?php echo esc_html( $recipe['time'] ); ?></span>
									<span class="recipe-card__meta-divider" aria-hidden="true"></span>
								<?php endif; ?>
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
			<h2 class="home-section__title"><?php echo esc_html( get_post_meta( $rosa_branca_home_id, 'rosa_branca_recipes_intro_title', true ) ); ?></h2>
			<p class="home-section__text"><?php echo esc_html( get_post_meta( $rosa_branca_home_id, 'rosa_branca_recipes_intro_text', true ) ); ?></p>
			<?php if ( $rosa_branca_recipes_intro_link ) : ?>
				<a class="btn btn--red" href="<?php echo esc_url( $rosa_branca_recipes_intro_link ); ?>"><?php esc_html_e( 'lorem ipsum', 'rosa-branca' ); ?></a>
			<?php endif; ?>
		</div>
	</div>
</section>
