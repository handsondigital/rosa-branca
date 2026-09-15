<?php
/**
 * Site header: logo, primary nav (desktop) / hamburger menu (mobile), search icon.
 * Active-page detection matches the Figma behaviour (current page renders as
 * plain text, not a link).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Only Home and Fale Conosco exist in the current implementation scope
// (PAGES_PLAN.md §2); the rest are real nav targets for future pages, kept
// as plain links with no "current" state until those pages are built.
$rosa_branca_nav_items = array(
	array( 'label' => __( 'Home', 'rosa-branca' ), 'url' => home_url( '/' ), 'current' => is_front_page() ),
	array( 'label' => __( 'Sobre a Marca', 'rosa-branca' ), 'url' => home_url( '/sobre-a-marca/' ), 'current' => false ),
	array( 'label' => __( 'Produtos', 'rosa-branca' ), 'url' => home_url( '/produtos/' ), 'current' => false ),
	array( 'label' => __( 'Receitas', 'rosa-branca' ), 'url' => home_url( '/receitas/' ), 'current' => false ),
	array( 'label' => __( 'Onde Comprar', 'rosa-branca' ), 'url' => home_url( '/onde-comprar/' ), 'current' => false ),
	array( 'label' => __( 'Fale Conosco', 'rosa-branca' ), 'url' => home_url( '/fale-conosco/' ), 'current' => is_page( 'fale-conosco' ) ),
);
?>
<header class="site-header">
	<div class="main-menu">
		<a class="main-menu__logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php esc_attr_e( 'Rosa Branca', 'rosa-branca' ); ?>">
			<?php rosa_branca_icon( 'logo-rosa-branca' ); ?>
		</a>

		<nav class="main-menu__nav" aria-label="<?php esc_attr_e( 'Menu principal', 'rosa-branca' ); ?>">
			<ul class="main-menu__list">
				<?php foreach ( $rosa_branca_nav_items as $item ) : ?>
					<li>
						<?php if ( $item['current'] ) : ?>
							<span class="main-menu__link" aria-current="page"><?php echo esc_html( $item['label'] ); ?></span>
						<?php else : ?>
							<a class="main-menu__link" href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['label'] ); ?></a>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		</nav>

		<div class="main-menu__actions">
			<button type="button" class="main-menu__search" aria-label="<?php esc_attr_e( 'Buscar', 'rosa-branca' ); ?>">
				<?php rosa_branca_icon( 'search' ); ?>
			</button>
			<button
				type="button"
				class="mobile-menu__toggle"
				aria-expanded="false"
				aria-controls="mobile-menu"
				data-mobile-menu-toggle
			>
				<span></span><span></span><span></span>
				<span class="visually-hidden"><?php esc_html_e( 'Abrir menu', 'rosa-branca' ); ?></span>
			</button>
		</div>
	</div>

	<nav id="mobile-menu" class="mobile-menu" aria-label="<?php esc_attr_e( 'Menu mobile', 'rosa-branca' ); ?>" hidden>
		<?php foreach ( $rosa_branca_nav_items as $item ) : ?>
			<a class="mobile-menu__link" href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['label'] ); ?></a>
		<?php endforeach; ?>
	</nav>
</header>
