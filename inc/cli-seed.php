<?php
/**
 * `wp rosa-branca seed` — CONTENT_MODEL.md's "Seed command": makes a fresh
 * install (new server, or a local dev environment right after `wp core
 * install`) immediately look like the Figma reference, without any manual
 * data entry. Idempotent — safe to run more than once; it only creates
 * what's missing, never duplicates or overwrites existing content.
 *
 * Deliberately does NOT touch any of the plain text fields in
 * inc/home-fields.php / inc/fale-conosco-fields.php — those already ship
 * with today's placeholder copy as their register_post_meta() `default`,
 * so they render correctly with zero seeding. This command only creates
 * things that need a real database row: the Home page itself (see its own
 * note in CONTENT_MODEL.md — required for those meta fields to have
 * somewhere to live), and a handful of `receita`/`produto` sample posts
 * with real Featured Images (imported from the theme's own existing
 * assets/images/ placeholders — CONTENT_MODEL.md's explicit instruction
 * not to invent new photography for this).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	return;
}

class Rosa_Branca_Seed_CLI {

	/**
	 * Seeds the Home page and sample receita/produto posts. Safe to
	 * re-run — skips anything that already exists.
	 *
	 * ## OPTIONS
	 *
	 * [--force]
	 * : Also re-import sample images even if matching receita/produto
	 * posts already exist (does not delete/duplicate posts either way,
	 * only affects whether sample posts get (re-)created when missing).
	 *
	 * ## EXAMPLES
	 *
	 *     wp rosa-branca seed
	 *
	 * @when after_wp_load
	 */
	public function seed( $args, $assoc_args ): void {
		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';

		$this->seed_home_page();
		$this->seed_receitas();
		$this->seed_produtos();
		$this->seed_banners();
		$this->seed_nav_menu( 'primary', __( 'Menu Principal', 'rosa-branca' ) );
		$this->seed_nav_menu( 'footer', __( 'Menu Rodapé', 'rosa-branca' ) );

		WP_CLI::success( 'Seed completo.' );
	}

	/**
	 * Creates a real WP menu with today's default items and assigns it to
	 * $location, if that location has no menu assigned yet — CONTENT_MODEL.md
	 * "Menu Principal / Menu Rodapé". Safe to re-run: does nothing once a
	 * menu is assigned, even an empty one an editor is still building.
	 */
	private function seed_nav_menu( string $location, string $menu_name ): void {
		$locations = get_nav_menu_locations();
		if ( ! empty( $locations[ $location ] ) ) {
			WP_CLI::log( "Já existe um menu atribuído para \"{$location}\" — pulando." );
			return;
		}

		$existing = wp_get_nav_menu_object( $menu_name );
		$menu_id  = $existing ? $existing->term_id : wp_create_nav_menu( $menu_name );

		if ( is_wp_error( $menu_id ) ) {
			WP_CLI::warning( "Falha ao criar o menu \"{$menu_name}\": " . $menu_id->get_error_message() );
			return;
		}

		if ( empty( wp_get_nav_menu_items( $menu_id ) ) ) {
			foreach ( rosa_branca_default_nav_items() as $item ) {
				wp_update_nav_menu_item(
					$menu_id,
					0,
					array(
						'menu-item-title'  => $item['label'],
						'menu-item-url'    => $item['url'],
						'menu-item-status' => 'publish',
					)
				);
			}
		}

		$locations[ $location ] = $menu_id;
		set_theme_mod( 'nav_menu_locations', $locations );

		WP_CLI::log( "Menu \"{$menu_name}\" criado e atribuído a \"{$location}\"." );
	}

	private function seed_home_page(): void {
		if ( get_page_by_path( 'home' ) ) {
			WP_CLI::log( 'Página "Home" já existe — pulando.' );
			return;
		}

		$id = wp_insert_post(
			array(
				'post_type'   => 'page',
				'post_title'  => 'Home',
				'post_name'   => 'home',
				'post_status' => 'publish',
			)
		);

		if ( is_wp_error( $id ) ) {
			WP_CLI::warning( 'Falha ao criar a página Home: ' . $id->get_error_message() );
			return;
		}

		WP_CLI::log( "Página \"Home\" criada (ID {$id})." );
	}

	private function seed_receitas(): void {
		if ( get_posts( array( 'post_type' => 'receita', 'numberposts' => 1, 'post_status' => 'any' ) ) ) {
			WP_CLI::log( 'Já existem posts de receita — pulando.' );
			return;
		}

		$sample_image = get_theme_file_path( 'assets/images/recipe-card-placeholder.png' );
		$recipes      = array(
			array( 'title' => 'Bolo de Fubá Cremoso', 'tempo' => '1h 15min.', 'dificuldade' => 'facil' ),
			array( 'title' => 'Pão Caseiro Tradicional', 'tempo' => '2h 30min.', 'dificuldade' => 'medio' ),
			array( 'title' => 'Torta de Frango com Massa Podre', 'tempo' => '1h 45min.', 'dificuldade' => 'dificil' ),
		);

		foreach ( $recipes as $recipe ) {
			$post_id = wp_insert_post(
				array(
					'post_type'    => 'receita',
					'post_title'   => $recipe['title'],
					'post_excerpt' => 'Uma receita simples e deliciosa com farinha Rosa Branca, perfeita para o dia a dia.',
					'post_status'  => 'publish',
				)
			);

			if ( is_wp_error( $post_id ) ) {
				WP_CLI::warning( "Falha ao criar receita \"{$recipe['title']}\": " . $post_id->get_error_message() );
				continue;
			}

			update_post_meta( $post_id, 'tempo_preparo', $recipe['tempo'] );
			update_post_meta( $post_id, 'dificuldade', $recipe['dificuldade'] );
			$this->attach_sample_image( $post_id, $sample_image, $recipe['title'] );
		}

		WP_CLI::log( count( $recipes ) . ' receita(s) de exemplo criada(s).' );
	}

	private function seed_produtos(): void {
		if ( get_posts( array( 'post_type' => 'produto', 'numberposts' => 1, 'post_status' => 'any' ) ) ) {
			WP_CLI::log( 'Já existem posts de produto — pulando.' );
			return;
		}

		$products = array(
			array( 'title' => 'Farinha de Trigo Rosa Branca', 'image' => 'farinha-home-1.png' ),
			array( 'title' => 'Farinha de Trigo Rosa Branca Especial', 'image' => 'farinha-home-2.png' ),
			array( 'title' => 'Farinha de Trigo Rosa Branca Integral', 'image' => 'farinha-home-3.png' ),
		);

		foreach ( $products as $product ) {
			$post_id = wp_insert_post(
				array(
					'post_type'   => 'produto',
					'post_title'  => $product['title'],
					'post_status' => 'publish',
				)
			);

			if ( is_wp_error( $post_id ) ) {
				WP_CLI::warning( "Falha ao criar produto \"{$product['title']}\": " . $post_id->get_error_message() );
				continue;
			}

			$this->attach_sample_image( $post_id, get_theme_file_path( "assets/images/{$product['image']}" ), $product['title'] );
		}

		WP_CLI::log( count( $products ) . ' produto(s) de exemplo criado(s).' );
	}

	/**
	 * Imports a theme asset image into the media library and sets it as
	 * $post_id's Featured Image — thin wrapper around import_sample_image()
	 * for the receita/produto case specifically (a banner slide's image
	 * isn't a post thumbnail, so it uses the shared import step directly —
	 * see seed_banners() below).
	 */
	private function attach_sample_image( int $post_id, string $source_path, string $title ): void {
		$attachment_id = $this->import_sample_image( $source_path, $title, $post_id );
		if ( $attachment_id ) {
			set_post_thumbnail( $post_id, $attachment_id );
		}
	}

	/**
	 * Imports a theme asset image (assets/images/*, the same static files
	 * build-images.mjs already optimizes at build time) into the media
	 * library — copying it in as a real upload, not just referencing the
	 * static file, is what triggers inc/uploads.php's wp_generate_
	 * attachment_metadata hook, the SAME AVIF/WebP conversion pipeline any
	 * real editor upload goes through. Returns the new attachment ID, or
	 * null if the source file is missing or the import failed.
	 */
	private function import_sample_image( string $source_path, string $title, int $parent_post_id = 0 ): ?int {
		if ( ! file_exists( $source_path ) ) {
			WP_CLI::warning( "Imagem de exemplo não encontrada: {$source_path}" );
			return null;
		}

		$upload_dir = wp_upload_dir();
		$filename   = wp_unique_filename( $upload_dir['path'], basename( $source_path ) );
		$dest_path  = trailingslashit( $upload_dir['path'] ) . $filename;

		if ( ! copy( $source_path, $dest_path ) ) {
			WP_CLI::warning( "Falha ao copiar imagem de exemplo para {$dest_path}" );
			return null;
		}

		$attachment_id = wp_insert_attachment(
			array(
				'post_title'     => $title,
				'post_mime_type' => wp_check_filetype( $dest_path )['type'],
				'post_status'    => 'inherit',
			),
			$dest_path,
			$parent_post_id
		);

		if ( is_wp_error( $attachment_id ) ) {
			WP_CLI::warning( "Falha ao registrar anexo para \"{$title}\": " . $attachment_id->get_error_message() );
			return null;
		}

		wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $dest_path ) );

		return $attachment_id;
	}

	/**
	 * Fills the Home page's Banner (Hero) repeater (inc/home-fields.php,
	 * CONTENT_MODEL.md) with today's exact mock copy — same title/text/link
	 * hero.php's own hardcoded fallback already uses — but with a REAL
	 * imported image (banner-home.png, the theme's existing static hero
	 * asset) instead of image_id 0, specifically so this exercises the
	 * same AVIF/WebP pipeline the receita/produto seeding already does.
	 * Goes through rosa_branca_sanitize_banner_slides() exactly like a
	 * real wp-admin save would (update_post_meta() on a registered meta
	 * key always runs its sanitize_callback), not a raw DB write.
	 */
	private function seed_banners(): void {
		$home_id = rosa_branca_home_page_id();
		if ( ! $home_id ) {
			WP_CLI::warning( 'Página "Home" não encontrada — pulando banners.' );
			return;
		}

		if ( ! empty( get_post_meta( $home_id, 'rosa_branca_banner_slides', true ) ) ) {
			WP_CLI::log( 'Banners do Hero já configurados — pulando.' );
			return;
		}

		$attachment_id = $this->import_sample_image(
			get_theme_file_path( 'assets/images/banner-home.png' ),
			'Banner Home',
			$home_id
		);

		if ( ! $attachment_id ) {
			WP_CLI::warning( 'Falha ao importar a imagem do banner — banners não configurados.' );
			return;
		}

		$mock_slide = array(
			'title'    => __( 'Lorem ipsum dolor sit amet', 'rosa-branca' ),
			'text'     => __( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse ut massa neque. Etiam egestas magna sit amet elit accumsan tristique in ut nunc.', 'rosa-branca' ),
			'link'     => home_url( '/receitas/' ),
			'image_id' => $attachment_id,
		);

		update_post_meta( $home_id, 'rosa_branca_banner_slides', array_fill( 0, 5, $mock_slide ) );

		WP_CLI::log( '5 slide(s) do Banner (Hero) configurados com a imagem otimizada (ID ' . $attachment_id . ').' );
	}
}

WP_CLI::add_command( 'rosa-branca seed', array( new Rosa_Branca_Seed_CLI(), 'seed' ) );
