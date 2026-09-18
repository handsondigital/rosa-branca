<?php
/**
 * Fale Conosco hero: static full-bleed photo with a left-to-right white
 * gradient and a heading/paragraph block. No carousel on this page (unlike
 * the Home hero) — confirmed against the Figma frame 630:6620.
 *
 * Title reuses this Page's own native post_title (CONTENT_MODEL.md — no
 * duplicate title field on a Page that already has one); paragraph/photo
 * are editable via inc/fale-conosco-fields.php.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Explicit lookup (not get_the_ID()/the_title()): this template part runs
// without an explicit have_posts()/the_post() Loop around it (see
// page-fale-conosco.php), so relying on ambient global $post state here
// would be fragile — same defensive style as rosa_branca_home_page_id().
$rosa_branca_fc_page     = get_page_by_path( 'fale-conosco' );
$rosa_branca_fc_photo_id = $rosa_branca_fc_page ? (int) get_post_meta( $rosa_branca_fc_page->ID, 'rosa_branca_contact_hero_photo_id', true ) : 0;
?>
<section class="page-hero hero-shell">
	<?php if ( $rosa_branca_fc_photo_id ) : ?>
		<?php
		rosa_branca_dynamic_picture(
			$rosa_branca_fc_photo_id,
			'hero-bleed',
			array(
				'img_class'     => 'page-hero__image hero-shell__image',
				'alt'           => __( 'Trigo e farinha Rosa Branca', 'rosa-branca' ),
				'loading'       => 'eager',
				'fetchpriority' => 'high',
			)
		);
		?>
	<?php else : ?>
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
	<?php endif; ?>
	<div class="page-hero__scrim" aria-hidden="true"></div>
	<div class="page-hero__container hero-shell__container">
		<div class="page-hero__content hero-shell__content">
			<h1 class="page-hero__title"><?php echo esc_html( $rosa_branca_fc_page ? $rosa_branca_fc_page->post_title : __( 'Fale Conosco', 'rosa-branca' ) ); ?></h1>
			<p class="page-hero__text">
				<?php echo esc_html( $rosa_branca_fc_page ? get_post_meta( $rosa_branca_fc_page->ID, 'rosa_branca_contact_hero_text', true ) : '' ); ?>
			</p>
		</div>
	</div>
</section>
