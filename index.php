<?php
/**
 * Fallback template (required by WordPress' template hierarchy). This theme
 * is scoped to Home (front-page.php) and Fale Conosco (page-fale-conosco.php)
 * only — see PAGES_PLAN.md §2/§15. Any other URL falls back to a minimal,
 * still-branded page rather than a broken one.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<section class="container" style="padding-block: 100px;">
	<?php if ( have_posts() ) : ?>
		<?php while ( have_posts() ) : the_post(); ?>
			<h1><?php the_title(); ?></h1>
			<div><?php the_content(); ?></div>
		<?php endwhile; ?>
	<?php else : ?>
		<h1><?php esc_html_e( 'Página não encontrada', 'rosa-branca' ); ?></h1>
	<?php endif; ?>
</section>

<?php get_footer(); ?>
