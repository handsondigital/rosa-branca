<?php
/**
 * "Encontre Rosa Branca" teaser: background photo + search box. Title/
 * subtitle editable (inc/home-fields.php, CONTENT_MODEL.md) — both
 * required, no optional-field handling needed here. A plain GET form to
 * /onde-comprar/ — that results page is out of the current scope
 * (PAGES_PLAN.md §15), but the form works natively the day it exists, with
 * zero JS needed for this simple case.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rosa_branca_home_id = rosa_branca_home_page_id();
?>
<section class="home-find-us">
	<?php rosa_branca_picture( 'encontre-rosa-branca-bg', array( 'img_class' => 'home-find-us__bg', 'alt' => '' ) ); ?>
	<div class="home-find-us__card container">
		<h2 class="home-find-us__title"><?php echo esc_html( get_post_meta( $rosa_branca_home_id, 'rosa_branca_find_us_title', true ) ); ?></h2>
		<p class="home-find-us__subtitle"><?php echo esc_html( get_post_meta( $rosa_branca_home_id, 'rosa_branca_find_us_subtitle', true ) ); ?></p>

		<form class="home-find-us__search" role="search" method="get" action="<?php echo esc_url( home_url( '/onde-comprar/' ) ); ?>">
			<label class="visually-hidden" for="find-us-search"><?php esc_html_e( 'Buscar loja ou cidade', 'rosa-branca' ); ?></label>
			<input
				type="search"
				id="find-us-search"
				name="s"
				class="home-find-us__input"
				placeholder="<?php esc_attr_e( 'Procurar', 'rosa-branca' ); ?>"
			/>
			<button type="submit" class="btn btn--blue home-find-us__submit">
				<?php esc_html_e( 'Link', 'rosa-branca' ); ?>
				<?php rosa_branca_icon( 'link-arrow' ); ?>
			</button>
		</form>
	</div>
</section>
