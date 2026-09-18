<?php
/**
 * Home hero: full-bleed banner carousel with heading, text, CTA and dots.
 * React (Carousel island) progressively enhances this container — see
 * src/entries/home.js — but every slide below is real, server-rendered
 * markup, so the page works and reads correctly with JS disabled too.
 *
 * Slides come from the "Banner (Hero)" repeater on the Home page
 * (inc/home-fields.php, CONTENT_MODEL.md) via rosa_branca_home_banner_slides()
 * — falls back to the single literal Figma placeholder copy (node 2:6)
 * repeated 5x until an editor configures real slides, same as before this
 * field existed. Each slide's button label stays fixed in code; its link
 * is optional (omits the button, per every other CTA in the project).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rosa_branca_hero_slides = rosa_branca_home_banner_slides();
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
			<div class="home-hero__slide hero-shell">
				<?php if ( ! empty( $slide['image_id'] ) ) : ?>
					<?php
					rosa_branca_dynamic_picture(
						(int) $slide['image_id'],
						'full',
						array(
							'img_class'     => 'home-hero__image hero-shell__image',
							'alt'           => '',
							'loading'       => $is_first ? 'eager' : 'lazy',
							'fetchpriority' => $is_first ? 'high' : '',
						)
					);
					?>
				<?php else : ?>
					<?php
					rosa_branca_picture(
						'banner-home',
						array(
							'img_class'     => 'home-hero__image hero-shell__image',
							'alt'           => '',
							'loading'       => $is_first ? 'eager' : 'lazy',
							'fetchpriority' => $is_first ? 'high' : '',
						)
					);
					?>
				<?php endif; ?>
				<div class="home-hero__container hero-shell__container">
					<div class="home-hero__content hero-shell__content">
						<?php
						// Only one <h1> per page: the first slide gets the real
						// heading tag, the rest use a visually-identical <p> so
						// they don't create extra H1s.
						$heading_tag = $is_first ? 'h1' : 'p';
						?>
						<<?php echo esc_html( $heading_tag ); ?> class="home-hero__title"><?php echo esc_html( $slide['title'] ); ?></<?php echo esc_html( $heading_tag ); ?>>
						<p class="home-hero__text"><?php echo esc_html( $slide['text'] ); ?></p>
						<?php if ( ! empty( $slide['link'] ) ) : ?>
							<a class="btn btn--red" href="<?php echo esc_url( $slide['link'] ); ?>"><?php esc_html_e( 'lorem ipsum', 'rosa-branca' ); ?></a>
						<?php endif; ?>
					</div>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</section>
