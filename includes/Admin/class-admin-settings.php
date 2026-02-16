<?php
/**
 * SNIPI Admin Settings API
 * 
 * WordPress Settings API integracija za boljšo WP native podporo.
 * Registrira vse nastavitve ekrana preko WP Settings API.
 * 
 * @package SNIPI_Ekrani
 * @since 2.1.0
 */

// Prepoved direktnega dostopa
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SNIPI_Admin_Settings {

	/**
	 * Inicializacija - registracija WordPress hookov
	 * 
	 * @return void
	 */
	public static function init() {
		// Registracija nastavitev
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
	}

	/**
	 * Registracija vseh nastavitev preko WordPress Settings API
	 * 
	 * Uporablja register_setting(), add_settings_section() in add_settings_field()
	 * za registracijo vseh polj ekrana.
	 * 
	 * @return void
	 */
	public static function register_settings() {
		// Pridobi trenutni screen ID
		$screen = get_current_screen();
		
		// Registriraj samo če smo na SNIPI edit screenu
		if ( ! $screen || false === strpos( $screen->id, 'snipi-edit-screen' ) ) {
			return;
		}

		// Pridobi post ID iz URL parametra
		$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;
		
		if ( ! $post_id ) {
			return;
		}

		// Ustvarimo unique page slug za ta ekran
		$page_slug = 'snipi_screen_' . $post_id;

		/**
		 * Registracija posameznih nastavitev
		 * 
		 * Vsaka nastavitev se registrira z register_setting()
		 * kar omogoča avtomatično sanitizacijo in shranjevanje.
		 */

		// API ključ
		register_setting(
			$page_slug,                              // Option group
			'_snipi_api_key_' . $post_id,            // Option name
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => '',
			)
		);

		// Vrstic na stran
		register_setting(
			$page_slug,
			'_snipi_rows_per_page_' . $post_id,
			array(
				'type'              => 'integer',
				'sanitize_callback' => array( __CLASS__, 'sanitize_rows_per_page' ),
				'default'           => 8,
			)
		);

		// Autoplay interval
		register_setting(
			$page_slug,
			'_snipi_autoplay_interval_' . $post_id,
			array(
				'type'              => 'integer',
				'sanitize_callback' => array( __CLASS__, 'sanitize_autoplay_interval' ),
				'default'           => 10,
			)
		);

		// Prihodnji dnevi
		register_setting(
			$page_slug,
			'_snipi_future_days_' . $post_id,
			array(
				'type'              => 'integer',
				'sanitize_callback' => array( __CLASS__, 'sanitize_future_days' ),
				'default'           => 0,
			)
		);

		// Vikend način
		register_setting(
			$page_slug,
			'_snipi_weekend_mode_' . $post_id,
			array(
				'type'              => 'boolean',
				'sanitize_callback' => array( __CLASS__, 'sanitize_checkbox' ),
				'default'           => false,
			)
		);

		// Prikaži stolpec PROGRAM
		register_setting(
			$page_slug,
			'_snipi_show_program_column_' . $post_id,
			array(
				'type'              => 'boolean',
				'sanitize_callback' => array( __CLASS__, 'sanitize_checkbox' ),
				'default'           => false,
			)
		);

		// Logo ID
		register_setting(
			$page_slug,
			'_snipi_logo_id_' . $post_id,
			array(
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
				'default'           => 0,
			)
		);

		// Logo višina
		register_setting(
			$page_slug,
			'_snipi_logo_height_' . $post_id,
			array(
				'type'              => 'integer',
				'sanitize_callback' => array( __CLASS__, 'sanitize_logo_height' ),
				'default'           => 60,
			)
		);

		// Prikaži spodnjo vrstico
		register_setting(
			$page_slug,
			'_snipi_display_bottom_' . $post_id,
			array(
				'type'              => 'boolean',
				'sanitize_callback' => array( __CLASS__, 'sanitize_checkbox' ),
				'default'           => false,
			)
		);

		// Vsebina spodnje vrstice
		register_setting(
			$page_slug,
			'_snipi_bottom_row_' . $post_id,
			array(
				'type'              => 'string',
				'sanitize_callback' => 'wp_kses_post',
				'default'           => '',
			)
		);

		// Custom CSS
		register_setting(
			$page_slug,
			'_snipi_custom_css_' . $post_id,
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_textarea_field',
				'default'           => '',
			)
		);
	}

	/**
	 * Sanitizacija: Vrstic na stran (1-50)
	 * 
	 * @param mixed $value Input vrednost
	 * @return int Sanitizirana vrednost
	 */
	public static function sanitize_rows_per_page( $value ) {
		$value = intval( $value );
		return max( 1, min( 50, $value ) );
	}

	/**
	 * Sanitizacija: Autoplay interval (5-60 sekund)
	 * 
	 * @param mixed $value Input vrednost
	 * @return int Sanitizirana vrednost
	 */
	public static function sanitize_autoplay_interval( $value ) {
		$value = intval( $value );
		return max( 5, min( 60, $value ) );
	}

	/**
	 * Sanitizacija: Prihodnji dnevi (0-3)
	 * 
	 * @param mixed $value Input vrednost
	 * @return int Sanitizirana vrednost
	 */
	public static function sanitize_future_days( $value ) {
		$value = intval( $value );
		return max( 0, min( 3, $value ) );
	}

	/**
	 * Sanitizacija: Logo višina (40-120px)
	 * 
	 * @param mixed $value Input vrednost
	 * @return int Sanitizirana vrednost
	 */
	public static function sanitize_logo_height( $value ) {
		$value = intval( $value );
		return max( 40, min( 120, $value ) );
	}

	/**
	 * Sanitizacija: Checkbox (1 ali 0)
	 * 
	 * @param mixed $value Input vrednost
	 * @return string '1' ali '0'
	 */
	public static function sanitize_checkbox( $value ) {
		return ( $value && '1' === $value ) ? '1' : '0';
	}

	/**
	 * Pridobi page slug za določen ekran
	 * 
	 * @param int $post_id ID ekrana
	 * @return string Page slug
	 */
	public static function get_page_slug( $post_id ) {
		return 'snipi_screen_' . intval( $post_id );
	}
}

// Inicializacija razreda
SNIPI_Admin_Settings::init();
