<?php
/**
 * Home hero: full-bleed banner carousel with heading, text, CTA and dots.
 * React (Carousel island) progressively enhances this container — see
 * src/entries/home.js — but every slide below is real, server-rendered
 * markup, so the page works and reads correctly with JS disabled too.
 *
 * Figma only has real approved copy for one slide (the 5-dot pagination is
 * shown as UI intent). Per explicit project decision, that single slide is
 * repeated 5x as placeholder content so the carousel is actually navigable
 * (real distinct slide copy is a future content task — replace the array
 * entries below when it exists).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Literal Figma placeholder copy (node 2:6) — not adapted. Repeated (not
// unique per slide) — see note above.
$rosa_branca_hero_slide = array(
	'title' => __( 'Lorem ipsum dolor sit amet', 'rosa-branca' ),
	'text'  => __( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse ut massa neque. Etiam egestas magna sit amet elit accumsan tristique in ut nunc.', 'rosa-branca' ),
	'cta'   => __( 'lorem ipsum', 'rosa-branca' ),
	'url'   => home_url( '/receitas/' ),
	'image' => 'banner-home',
);
$rosa_branca_hero_slides = array_fill( 0, 5, $rosa_branca_hero_slide );
?>
<section class="home-hero">
	<div
		class="home-hero__carousel"
		data-carousel-mount
		data-variant="hero"
		data-arrows="false"
		data-dots="true"
		data-autoplay="6000"
		data-label="<?php esc_attr_e( 'Destaques Rosa Branca', 'rosa-branca' ); ?>"
	>
		<?php foreach ( $rosa_branca_hero_slides as $index => $slide ) : ?>
			<?php $is_first = 0 === $index; ?>
			<div class="home-hero__slide">
				<?php
				rosa_branca_picture(
					$slide['image'],
					array(
						'img_class'     => 'home-hero__image',
						'alt'           => '',
						'loading'       => $is_first ? 'eager' : 'lazy',
						'fetchpriority' => $is_first ? 'high' : '',
					)
				);
				?>
				<div class="home-hero__container">
					<div class="home-hero__content">
						<?php
						// Only one <h1> per page: the first slide gets the real
						// heading tag, the rest (duplicate placeholder copy) use a
						// visually-identical <p> so they don't create extra H1s.
						$heading_tag = $is_first ? 'h1' : 'p';
						?>
						<<?php echo esc_html( $heading_tag ); ?> class="home-hero__title"><?php echo esc_html( $slide['title'] ); ?></<?php echo esc_html( $heading_tag ); ?>>
						<p class="home-hero__text"><?php echo esc_html( $slide['text'] ); ?></p>
						<a class="btn btn--red" href="<?php echo esc_url( $slide['url'] ); ?>"><?php echo esc_html( $slide['cta'] ); ?></a>
					</div>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</section>
