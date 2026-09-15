<?php
/**
 * Home (front-page.php maps WordPress to "/" automatically — PAGES_PLAN.md §3).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<?php get_template_part( 'template-parts/home/hero' ); ?>
<?php get_template_part( 'template-parts/home/recipes' ); ?>
<?php get_template_part( 'template-parts/home/products' ); ?>
<?php get_template_part( 'template-parts/home/about' ); ?>
<?php get_template_part( 'template-parts/home/find-us' ); ?>

<?php get_footer(); ?>
