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
			return '<div class="snipi-error">SNIPI: manjka ID ekrana.</div>';
		}

		// Front assets
		wp_enqueue_style( 'snipi-front-css', SNIPI_EKRANI_URL . 'assets/css/front.css', array(), snipi_ekrani_asset_version( 'assets/css/front.css' ) );
		wp_enqueue_script( 'snipi-front-js', SNIPI_EKRANI_URL . 'assets/js/front.js', array(), snipi_ekrani_asset_version( 'assets/js/front.js' ), true );

		// Localize REST root + settings
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
)
);

$custom_css = get_post_meta( $post_id, '_snipi_custom_css', true );
if ( is_string( $custom_css ) && '' !== trim( $custom_css ) ) {
wp_add_inline_style( 'snipi-front-css', sanitize_textarea_field( $custom_css ) );
}

		// Initial server-side shell
		$html = SNIPI_Renderer::render_front_shell( $post_id );

		return $html;
	}
}

SNIPI_Shortcode::init();
