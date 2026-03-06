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
 * @since   1.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SNIPI_Admin_Core {

	/**
	 * Inicializacija – registracija vseh WordPress hookov
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'init',                    array( __CLASS__, 'register_cpt' ) );
		add_action( 'admin_menu',              array( __CLASS__, 'register_menu' ) );
		add_action( 'admin_init',              array( __CLASS__, 'maybe_redirect_to_custom_edit' ) );
		add_filter( 'get_edit_post_link',      array( __CLASS__, 'filter_edit_link' ), 10, 3 );
		add_action( 'admin_enqueue_scripts',   array( __CLASS__, 'enqueue_admin_assets' ) );
		add_action( 'admin_notices',           array( __CLASS__, 'render_success_notice' ) );
	}

	/**
	 * Registracija Custom Post Type 'ekran'
	 *
	 * @return void
	 */
	public static function register_cpt() {
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

		$args = array(
			'labels'        => $labels,
			'public'        => false,
			'show_ui'       => true,
			'show_in_menu'  => true,
			'menu_position' => 25,
			'menu_icon'     => 'dashicons-screenoptions',
			'supports'      => array( 'title' ),
			'has_archive'   => false,
			'rewrite'       => false,
			'show_in_rest'  => false,
		);

		register_post_type( 'ekran', $args );
	}

	/**
	 * Registracija admin menija
	 *
	 * @return void
	 */
	public static function register_menu() {
		add_submenu_page(
			'edit.php?post_type=ekran',
			'Uredi ekran',
			'Uredi ekran',
			'edit_posts',
			'snipi-edit-screen',
			array( 'SNIPI_Admin_Edit_Screen', 'render' )
		);
	}

	/**
	 * Redirect iz standardnega WP edit.php na našo custom edit stran
	 *
	 * @return void
	 */
	public static function maybe_redirect_to_custom_edit() {
		if ( ! is_admin() ) {
			return;
		}

		if ( ! isset( $_GET['post'], $_GET['action'] ) || 'edit' !== $_GET['action'] ) {
			return;
		}

		$post_id = absint( $_GET['post'] );
		$post    = get_post( $post_id );

		if ( ! $post || 'ekran' !== $post->post_type ) {
			return;
		}

		if ( isset( $_GET['page'] ) ) {
			return;
		}

		$redirect_url = self::get_edit_screen_url( $post_id );
		wp_safe_redirect( $redirect_url );
		exit;
	}

	/**
	 * Filter za edit linke
	 *
	 * @param string $link
	 * @param int    $post_id
	 * @param string $context
	 * @return string
	 */
	public static function filter_edit_link( $link, $post_id, $context ) {
		$post = get_post( $post_id );
		if ( $post && 'ekran' === $post->post_type ) {
			return self::get_edit_screen_url( $post_id );
		}
		return $link;
	}

	/**
	 * Generira URL za edit screen
	 *
	 * @param int $post_id
	 * @return string
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

		$screen_page   = isset( $_GET['page'] ) ? sanitize_key( $_GET['page'] ) : '';
		$is_snipi_page = ( 'snipi-edit-screen' === $screen_page );
		$is_ekran_cpt  = ( 'ekran' === $post_type );
		$is_new_ekran  = ( 'post-new.php' === $pagenow && isset( $_GET['post_type'] ) && 'ekran' === $_GET['post_type'] );

		if ( ! $is_snipi_page && ! $is_ekran_cpt && ! $is_new_ekran ) {
			return;
		}

		// FontAwesome 6 CDN
		wp_enqueue_style(
			'fontawesome',
			'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
			array(),
			'6.4.0'
		);

		// WordPress color picker (Iris) – za styling GUI
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );

		// Admin CSS
		wp_enqueue_style(
			'snipi-admin-css',
			SNIPI_EKRANI_URL . 'assets/css/admin.css',
			array( 'fontawesome' ),
			snipi_ekrani_asset_version( 'assets/css/admin.css' )
		);

		// Styling page CSS
		wp_enqueue_style(
			'snipi-admin-styling-css',
			SNIPI_EKRANI_URL . 'assets/css/admin-styling.css',
			array( 'wp-color-picker' ),
			snipi_ekrani_asset_version( 'assets/css/admin-styling.css' )
		);

		// WordPress media uploader
		wp_enqueue_media();

		// Admin JS (vanilla)
		wp_enqueue_script(
			'snipi-admin-js',
			SNIPI_EKRANI_URL . 'assets/js/admin.js',
			array(),
			snipi_ekrani_asset_version( 'assets/js/admin.js' ),
			true
		);

		// Styling JS (jQuery + wp-color-picker)
		wp_enqueue_script(
			'snipi-admin-styling-js',
			SNIPI_EKRANI_URL . 'assets/js/admin-styling.js',
			array( 'jquery', 'wp-color-picker' ),
			snipi_ekrani_asset_version( 'assets/js/admin-styling.js' ),
			true
		);

		// Localize admin JS
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
	 * WordPress native success notice po shranjevanju
	 *
	 * @return void
	 */
	public static function render_success_notice() {
		if ( ! is_admin() ) {
			return;
		}

		$screen_page = isset( $_GET['page'] ) ? sanitize_key( $_GET['page'] ) : '';
		if ( 'snipi-edit-screen' !== $screen_page ) {
			return;
		}

		if ( ! isset( $_GET['updated'] ) || ! absint( $_GET['updated'] ) ) {
			return;
		}

		echo '<div class="notice notice-success is-dismissible">';
		echo '<p>' . esc_html__( 'Nastavitve so bile uspešno shranjene.', 'snipi-ekrani' ) . '</p>';
		echo '</div>';
	}
}

SNIPI_Admin_Core::init();
