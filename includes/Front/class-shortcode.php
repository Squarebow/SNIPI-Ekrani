<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SNIPI_Shortcode
 * Shortcode: [snipi_ekran id="123"]
 */

class SNIPI_Shortcode {

	public static function init() {
		add_shortcode( 'snipi_ekran', array( __CLASS__, 'render' ) );
	}

	/**
	 * Renders the shortcode output.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public static function render( $atts ) {
		$atts = shortcode_atts(
			array(
				'id' => 0,
			),
			$atts,
			'snipi_ekran'
		);

		$post_id = intval( $atts['id'] );
		if ( ! $post_id ) {
			return '';
		}

		// Front assets.
		wp_enqueue_style(
			'snipi-front-css',
			SNIPI_EKRANI_URL . 'assets/css/front.css',
			array(),
			snipi_ekrani_asset_version( 'assets/css/front.css' )
		);

		wp_enqueue_script(
			'snipi-front-js',
			SNIPI_EKRANI_URL . 'assets/js/front.js',
			array(),
			snipi_ekrani_asset_version( 'assets/js/front.js' ),
			true
		);

		// TV assets (v2.2.0)
		$enable_tv = get_post_meta( $post_id, '_snipi_enable_tv_detection', true );
		if ( $enable_tv !== '0' ) {
			wp_enqueue_style(
				'snipi-tv-css',
				SNIPI_EKRANI_URL . 'assets/css/tv.css',
				array( 'snipi-front-css' ),
				snipi_ekrani_asset_version( 'assets/css/tv.css' )
			);

			wp_enqueue_script(
				'snipi-tv-js',
				SNIPI_EKRANI_URL . 'assets/js/tv.js',
				array(),
				snipi_ekrani_asset_version( 'assets/js/tv.js' ),
				true
			);

			$tv_override = get_post_meta( $post_id, '_snipi_tv_mode_override', true ) ?: 'auto';
			$tv_confirm = get_post_meta( $post_id, '_snipi_tv_confirm_dialog', true );

			wp_localize_script(
				'snipi-tv-js',
				'snipiTVConfig',
				array(
					'screenId'           => $post_id,
					'enableTVDetection'  => $enable_tv !== '0',
					'tvModeOverride'     => $tv_override,
					'tvConfirmDialog'    => $tv_confirm !== '0',
				)
			);
		}

		// Nastavitve za JS (REST root, nastavitve, URL vtičnika).
		$rows_per_page     = get_post_meta( $post_id, '_snipi_rows_per_page', true ) ?: 8;
		$autoplay_interval = get_post_meta( $post_id, '_snipi_autoplay_interval', true ) ?: 10;
		$show_program      = get_post_meta( $post_id, '_snipi_show_program_column', true ) === '1';

		wp_localize_script(
			'snipi-front-js',
			'SNIPI_FRONT_REST',
			array(
				'rest_root'        => esc_url_raw( rest_url() ),
				'post_id'          => $post_id,
				'rowsPerPage'      => intval( $rows_per_page ),
				'autoplayInterval' => intval( $autoplay_interval ),
				'noEventsMessage'  => __( 'Danes ni predvidenih izobraževanj.', 'snipi-ekrani' ),
				'showProgramColumn' => $show_program,
				// Osnovni URL vtičnika za uporabo v front.js (npr. za Live.svg).
				'pluginUrl'        => trailingslashit( SNIPI_EKRANI_URL ),
			)
		);

		$custom_css = get_post_meta( $post_id, '_snipi_custom_css', true );
		if ( is_string( $custom_css ) && '' !== trim( $custom_css ) ) {
			wp_add_inline_style( 'snipi-front-css', sanitize_textarea_field( $custom_css ) );
		}

		// Initial server-side shell.
		$html = SNIPI_Renderer::render_front_shell( $post_id );

		return $html;
	}
}

SNIPI_Shortcode::init();
