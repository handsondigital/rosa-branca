<?php
/**
 * Fale Conosco hero: static full-bleed photo with a left-to-right white
 * gradient and a heading/paragraph block. No carousel on this page (unlike
 * the Home hero) — confirmed against the Figma frame 630:6620.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<section class="page-hero hero-shell">
	<?php
	rosa_branca_picture(
		'foto-trigo-farinha',
		array(
			'img_class'     => 'page-hero__image hero-shell__image',
			'alt'           => __( 'Trigo e farinha Rosa Branca', 'rosa-branca' ),
			'loading'       => 'eager',
			'fetchpriority' => 'high',
		)
	);
	?>
	<div class="page-hero__scrim" aria-hidden="true"></div>
	<div class="page-hero__container hero-shell__container">
		<div class="page-hero__content hero-shell__content">
			<h1 class="page-hero__title"><?php esc_html_e( 'Fale Conosco', 'rosa-branca' ); ?></h1>
			<p class="page-hero__text">
				<?php esc_html_e( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse ut massa neque. Etiam egestas magna sit amet elit accumsan tristique in ut nunc. Sed porta, ex eget ornare facilisis, dui libero bibendum enim, vitae sodales turpis ex vel nisi.', 'rosa-branca' ); ?>
			</p>
		</div>
	</div>
</section>
