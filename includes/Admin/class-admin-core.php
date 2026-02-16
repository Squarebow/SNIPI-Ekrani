<?php
/**
 * SNIPI Admin Core
 * 
 * Jedro admin funkcionalnosti za SNIPI Ekrani plugin.
 * Odgovoren za:
 * - Registracijo Custom Post Type (CPT) 'ekran'
 * - Registracijo admin menijev in strani
 * - Enqueue admin CSS in JS datotek
 * - Redirect logiko za edit povezave
 * 
 * @package SNIPI_Ekrani
 * @since 1.2.0
 */

// Prepoved direktnega dostopa
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SNIPI_Admin_Core {

	/**
	 * Inicializacija - registracija vseh WordPress hookov
	 * 
	 * @return void
	 */
	public static function init() {
		// Registracija Custom Post Type ob init
		add_action( 'init', array( __CLASS__, 'register_cpt' ) );
		
		// Registracija admin menu strani
		add_action( 'admin_menu', array( __CLASS__, 'register_menu' ) );
		
		// Redirect iz standardnega WP edit screena na našo custom stran
		add_action( 'admin_init', array( __CLASS__, 'maybe_redirect_to_custom_edit' ) );
		
		// Filter za spreminjanje edit linkov
		add_filter( 'get_edit_post_link', array( __CLASS__, 'filter_edit_link' ), 10, 3 );
		
		// Enqueue admin assetov (CSS, JS)
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_assets' ) );
		
		// WordPress native success notice po shranjevanju
		add_action( 'admin_notices', array( __CLASS__, 'render_success_notice' ) );
	}

	/**
	 * Registracija Custom Post Type 'ekran'
	 * 
	 * Registrira CPT za upravljanje SNIPI ekranov.
	 * Vsak ekran predstavlja en zaslon/urnik z lastnim API ključem in nastavitvami.
	 * 
	 * @return void
	 */
	public static function register_cpt() {
		// Naslovi za CPT v slovenščini
		$labels = array(
			'name'               => 'SNIPI ekrani',
			'singular_name'      => 'Ekran',
			'menu_name'          => 'SNIPI ekrani',
			'name_admin_bar'     => 'Ekran',
			'add_new'            => 'Dodaj ekran',
			'add_new_item'       => 'Dodaj nov ekran',
			'edit_item'          => 'Uredi ekran',
			'new_item'           => 'Nov ekran',
			'view_item'          => 'Poglej ekran',
			'search_items'       => 'Išči ekrane',
			'not_found'          => 'Ni najdenih ekranov',
			'not_found_in_trash' => 'Ni ekranov v smeteh',
			'all_items'          => 'Vsi ekrani',
		);

		// Argumenti za registracijo CPT
		$args = array(
			'labels'        => $labels,
			'public'        => false,        // Ne prikazuj v frontend navigaciji
			'show_ui'       => true,         // Prikaži v WP adminu
			'show_in_menu'  => true,         // Prikaži v glavnem meniju
			'menu_position' => 25,           // Pozicija v meniju (pod Comments)
			'menu_icon'     => 'dashicons-screenoptions', // Ikona ekrana
			'supports'      => array( 'title' ),          // Samo naslov, brez vsebine
			'has_archive'   => false,        // Brez arhivske strani
			'rewrite'       => false,        // Brez custom URL strukture
			'show_in_rest'  => false,        // Brez Gutenberg editorja
		);

		// Registracija CPT
		register_post_type( 'ekran', $args );
	}

	/**
	 * Registracija admin menija
	 * 
	 * Kreira custom submenu stran pod "SNIPI ekrani" menijem.
	 * Uporabljamo submenu namesto meta boxov za boljšo preglednost.
	 * 
	 * @return void
	 */
	public static function register_menu() {
		// Glavna edit stran za posamezen ekran
		// Skrita iz menija (dostopna samo preko edit linkov)
		add_submenu_page(
			'edit.php?post_type=ekran',           // Parent page
			'Uredi ekran',                         // Page title
			'Uredi ekran',                         // Menu title
			'edit_posts',                          // Capability
			'snipi-edit-screen',                   // Menu slug
			array( 'SNIPI_Admin_Edit_Screen', 'render' ) // Callback funkcija
		);
	}

	/**
	 * Redirect iz standardnega WP edit.php na našo custom edit stran
	 * 
	 * Ko uporabnik klikne "Uredi" pri ekranu, ga preusmerimo na našo
	 * custom admin stran namesto standardnega WP post editorja.
	 * 
	 * @return void
	 */
	public static function maybe_redirect_to_custom_edit() {
		// Preveri da smo v adminu
		if ( ! is_admin() ) {
			return;
		}

		// Preveri da je to edit akcija
		if ( ! isset( $_GET['post'], $_GET['action'] ) || 'edit' !== $_GET['action'] ) {
			return;
		}

		$post_id = absint( $_GET['post'] );
		$post    = get_post( $post_id );

		// Preveri da je to naš CPT
		if ( ! $post || 'ekran' !== $post->post_type ) {
			return;
		}

		// Preveri da ni že redirect (prepreči loop)
		if ( isset( $_GET['page'] ) ) {
			return;
		}

		// Redirect na našo custom edit stran
		$redirect_url = self::get_edit_screen_url( $post_id );
		wp_safe_redirect( $redirect_url );
		exit;
	}

	/**
	 * Filter za edit linke - spremeni v linke na našo custom stran
	 * 
	 * @param string $link    Originalni edit link
	 * @param int    $post_id Post ID
	 * @param string $context Kontekst (display ali ne)
	 * @return string Modificiran link
	 */
	public static function filter_edit_link( $link, $post_id, $context ) {
		$post = get_post( $post_id );
		
		// Če je to naš CPT, vrni link na našo edit stran
		if ( $post && 'ekran' === $post->post_type ) {
			return self::get_edit_screen_url( $post_id );
		}
		
		return $link;
	}

	/**
	 * Generira URL za edit screen določenega ekrana
	 * 
	 * @param int $post_id Post ID ekrana
	 * @return string URL do edit screena
	 */
	public static function get_edit_screen_url( $post_id ) {
		return add_query_arg(
			array(
				'post_type' => 'ekran',
				'page'      => 'snipi-edit-screen',
				'post'      => intval( $post_id ),
			),
			admin_url( 'edit.php' )
		);
	}

	/**
	 * Enqueue admin CSS in JavaScript datotek
	 * 
	 * Naloži vse potrebne admin assete samo na SNIPI admin straneh.
	 * To zagotavlja optimalno hitrost - asseti se ne naložijo nepotrebno.
	 * 
	 * @param string $hook Current admin page hook
	 * @return void
	 */
	public static function enqueue_admin_assets( $hook ) {
		global $post_type, $pagenow;

		// Prepreči nalaganje med plugin upload procesom
		if ( isset( $_GET['action'] ) && 'upload-plugin' === $_GET['action'] ) {
			return;
		}

		// Določi ali smo na SNIPI strani
		$screen_page   = isset( $_GET['page'] ) ? sanitize_key( $_GET['page'] ) : '';
		$is_snipi_page = ( 'snipi-edit-screen' === $screen_page );
		$is_ekran_cpt  = ( 'ekran' === $post_type );
		$is_new_ekran  = ( 'post-new.php' === $pagenow && isset( $_GET['post_type'] ) && 'ekran' === $_GET['post_type'] );

		// Naloži assete samo na relevantnih straneh
		if ( ! $is_snipi_page && ! $is_ekran_cpt && ! $is_new_ekran ) {
			return;
		}

		// FontAwesome 6 CDN - ikone namesto emojijev
		wp_enqueue_style(
			'fontawesome',
			'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
			array(),
			'6.4.0'
		);

		// CSS - admin styling
		wp_enqueue_style(
			'snipi-admin-css',
			SNIPI_EKRANI_URL . 'assets/css/admin.css',
			array( 'fontawesome' ),
			snipi_ekrani_asset_version( 'assets/css/admin.css' )
		);

		// CSS - styling page specific (color pickers, preview)
		wp_enqueue_style(
			'snipi-admin-styling-css',
			SNIPI_EKRANI_URL . 'assets/css/admin-styling.css',
			array(),
			snipi_ekrani_asset_version( 'assets/css/admin-styling.css' )
		);

		// WordPress media uploader (za logo upload)
		wp_enqueue_media();

		// JavaScript - admin functionality
		wp_enqueue_script(
			'snipi-admin-js',
			SNIPI_EKRANI_URL . 'assets/js/admin.js',
			array(),
			snipi_ekrani_asset_version( 'assets/js/admin.js' ),
			true // V footerju
		);

		// JavaScript - styling page functionality
		wp_enqueue_script(
			'snipi-admin-styling-js',
			SNIPI_EKRANI_URL . 'assets/js/admin-styling.js',
			array(),
			snipi_ekrani_asset_version( 'assets/js/admin-styling.js' ),
			true // V footerju
		);

		// Localize script - pošlji podatke v JavaScript
		$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;
		wp_localize_script(
			'snipi-admin-js',
			'SNIPI_ADMIN',
			array(
				'preview_nonce' => wp_create_nonce( 'snipi_preview_nonce' ),
				'rest_url'      => esc_url_raw( rest_url( 'snipi/v1/ekrani/preview' ) ),
				'post_id'       => $post_id,
			)
		);
	}

	/**
	 * Prikaže WordPress native success notice po shranjevanju
	 * 
	 * Uporabnik vidi zeleno obvestilo "Nastavitve so bile shranjene"
	 * po uspešnem shranjevanju nastavitev ekrana.
	 * 
	 * @return void
	 */
	public static function render_success_notice() {
		// Preveri da smo v adminu
		if ( ! is_admin() ) {
			return;
		}

		// Preveri da smo na SNIPI strani
		$screen_page = isset( $_GET['page'] ) ? sanitize_key( $_GET['page'] ) : '';
		if ( 'snipi-edit-screen' !== $screen_page ) {
			return;
		}

		// Preveri da je bil setiran 'updated' parameter
		if ( ! isset( $_GET['updated'] ) || ! absint( $_GET['updated'] ) ) {
			return;
		}

		// Prikaži success notice
		echo '<div class="notice notice-success is-dismissible">';
		echo '<p>' . esc_html__( 'Nastavitve so bile uspešno shranjene.', 'snipi-ekrani' ) . '</p>';
		echo '</div>';
	}
}

// Inicializacija razreda
SNIPI_Admin_Core::init();
