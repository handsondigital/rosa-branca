<?php
/**
 * Template Name: Fale Conosco
 *
 * Assign this template to the page with slug "fale-conosco" in
 * wp-admin so it renders at /fale-conosco/ (PAGES_PLAN.md §4).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<?php get_template_part( 'template-parts/fale-conosco/hero' ); ?>

<section class="contact-section container">
	<div class="contact-section__grid">
		<?php get_template_part( 'template-parts/fale-conosco/form' ); ?>
		<?php get_template_part( 'template-parts/fale-conosco/contact-details' ); ?>
	</div>
</section>

<?php get_footer(); ?>
