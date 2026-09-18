<?php
/**
 * "Sobre a Marca" teaser: photo + heading/text/outline-button. Fully
 * editable (inc/home-fields.php, CONTENT_MODEL.md) — button label stays
 * fixed in code, link is optional (omits the button when empty).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rosa_branca_home_id    = rosa_branca_home_page_id();
$rosa_branca_about_link = get_post_meta( $rosa_branca_home_id, 'rosa_branca_about_link', true );
$rosa_branca_photo_id   = (int) get_post_meta( $rosa_branca_home_id, 'rosa_branca_about_photo_id', true );
?>
<section class="home-section container" data-reveal>
	<div class="home-section__grid">
		<div class="home-section__media">
			<?php if ( $rosa_branca_photo_id ) : ?>
				<?php
				rosa_branca_dynamic_picture(
					$rosa_branca_photo_id,
					'full',
					array(
						'img_class' => 'home-about__photo',
						'alt'       => __( 'Produção artesanal com farinha Rosa Branca', 'rosa-branca' ),
					)
				);
				?>
			<?php else : ?>
				<?php
				rosa_branca_picture(
					'foto-sobre-a-marca',
					array(
						'img_class' => 'home-about__photo',
						'alt'       => __( 'Produção artesanal com farinha Rosa Branca', 'rosa-branca' ),
					)
				);
				?>
			<?php endif; ?>
		</div>

		<div class="home-section__content">
			<h2 class="home-section__title"><?php echo esc_html( get_post_meta( $rosa_branca_home_id, 'rosa_branca_about_title', true ) ); ?></h2>
			<p class="home-section__text"><?php echo esc_html( get_post_meta( $rosa_branca_home_id, 'rosa_branca_about_text', true ) ); ?></p>
			<?php if ( $rosa_branca_about_link ) : ?>
				<a class="btn btn--outline" href="<?php echo esc_url( $rosa_branca_about_link ); ?>"><?php esc_html_e( 'lorem ipsum', 'rosa-branca' ); ?></a>
			<?php endif; ?>
		</div>
	</div>
</section>
