<?php
/**
 * SNIPI Admin Meta
 * 
 * Centralizirana logika za upravljanje post meta podatkov.
 * Vsebuje helper funkcije za:
 * - Shranjevanje meta podatkov (API ključ, nastavitve, styling)
 * - Pridobivanje meta podatkov z default vrednostmi
 * - Validacijo user inputov
 * 
 * @package SNIPI_Ekrani
 * @since 1.2.0
 */

// Prepoved direktnega dostopa
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SNIPI_Admin_Meta {

	/**
	 * Shrani vse meta podatke iz POST requesta
	 * 
	 * Prebere POST podatke, jih validira in shrani v post_meta.
	 * Uporablja se pri shranjevanju nastavitev in oblikovanja.
	 * 
	 * @param int $post_id ID ekrana
	 * @return void
	 */
	public static function save_from_request( $post_id ) {
		// API ključ - obvezno polje
		if ( isset( $_POST['snipi_api_key'] ) ) {
			$api_key = sanitize_text_field( wp_unslash( $_POST['snipi_api_key'] ) );
			update_post_meta( $post_id, '_snipi_api_key', $api_key );
		}

		// Vrstic na stran - število med 1 in 50
		if ( isset( $_POST['snipi_rows_per_page'] ) ) {
			$rows = max( 1, min( 50, intval( $_POST['snipi_rows_per_page'] ) ) );
			update_post_meta( $post_id, '_snipi_rows_per_page', $rows );
		}

		// Autoplay interval - sekunde med 5 in 60
		if ( isset( $_POST['snipi_autoplay_interval'] ) ) {
			$interval = max( 5, min( 60, intval( $_POST['snipi_autoplay_interval'] ) ) );
			update_post_meta( $post_id, '_snipi_autoplay_interval', $interval );
		}

		// Prihodnji dnevi - število med 0 in 3
		if ( isset( $_POST['snipi_future_days'] ) ) {
			$future_days = max( 0, min( 30, intval( $_POST['snipi_future_days'] ) ) );
			update_post_meta( $post_id, '_snipi_future_days', $future_days );
		}

		// Vikend način - checkbox (1 ali 0)
		$weekend_mode = isset( $_POST['snipi_weekend_mode'] ) ? '1' : '0';
		update_post_meta( $post_id, '_snipi_weekend_mode', $weekend_mode );

		// Prikaži stolpec PROGRAM - checkbox (1 ali 0)
		$show_program = isset( $_POST['snipi_show_program_column'] ) ? '1' : '0';
		update_post_meta( $post_id, '_snipi_show_program_column', $show_program );

		// Logo ID - attachment ID iz media library
		if ( isset( $_POST['snipi_logo_id'] ) ) {
			update_post_meta( $post_id, '_snipi_logo_id', intval( $_POST['snipi_logo_id'] ) );
		}

		// Višina logotipa - px med 40 in 120
		if ( isset( $_POST['snipi_logo_height'] ) ) {
			$logo_height = max( 40, min( 120, intval( $_POST['snipi_logo_height'] ) ) );
			update_post_meta( $post_id, '_snipi_logo_height', $logo_height );
		}

		// Prikaži spodnjo vrstico - checkbox (1 ali 0)
		$display_bottom = isset( $_POST['snipi_display_bottom'] ) ? '1' : '0';
		update_post_meta( $post_id, '_snipi_display_bottom', $display_bottom );

		// Vsebina spodnje vrstice - WYSIWYG HTML
		if ( isset( $_POST['snipi_bottom_row'] ) ) {
			// wp_kses_post dovoli samo varne HTML tage
			$bottom_row = wp_kses_post( wp_unslash( $_POST['snipi_bottom_row'] ) );
			update_post_meta( $post_id, '_snipi_bottom_row', $bottom_row );
		}

		// Custom CSS - textarea za oblikovanje
		if ( isset( $_POST['snipi_custom_css'] ) ) {
			$custom_css = sanitize_textarea_field( wp_unslash( $_POST['snipi_custom_css'] ) );
			update_post_meta( $post_id, '_snipi_custom_css', $custom_css );
		}

		// TV Optimizacija (v2.2.0) - checkbox (1 ali 0)
		$enable_tv_detection = isset( $_POST['snipi_enable_tv_detection'] ) ? '1' : '0';
		update_post_meta( $post_id, '_snipi_enable_tv_detection', $enable_tv_detection );

		// TV način override
		if ( isset( $_POST['snipi_tv_mode_override'] ) ) {
			$tv_mode = in_array( $_POST['snipi_tv_mode_override'], array( 'auto', 'tv', 'desktop' ) ) 
				? sanitize_text_field( $_POST['snipi_tv_mode_override'] ) 
				: 'auto';
			update_post_meta( $post_id, '_snipi_tv_mode_override', $tv_mode );
		}

		// TV potrditveno okno - checkbox (1 ali 0)
		$tv_confirm_dialog = isset( $_POST['snipi_tv_confirm_dialog'] ) ? '1' : '0';
		update_post_meta( $post_id, '_snipi_tv_confirm_dialog', $tv_confirm_dialog );
	}

	/**
	 * Pridobi vse meta podatke za določen ekran
	 * 
	 * Vrne asociativni array z vsemi nastavitvami ekrana.
	 * Vključuje tudi izračunane vrednosti (npr. število dogodkov danes).
	 * 
	 * @param int $post_id ID ekrana
	 * @return array Asociativni array z vsemi nastavitvami
	 */
	public static function get_all( $post_id ) {
		// Pridobi vse shranjene vrednosti
		$api_key           = get_post_meta( $post_id, '_snipi_api_key', true );
		$rows_per_page     = get_post_meta( $post_id, '_snipi_rows_per_page', true );
		$autoplay_interval = get_post_meta( $post_id, '_snipi_autoplay_interval', true );
		$future_days       = get_post_meta( $post_id, '_snipi_future_days', true );
		$logo_id           = get_post_meta( $post_id, '_snipi_logo_id', true );
		$logo_height       = get_post_meta( $post_id, '_snipi_logo_height', true );
		$display_bottom    = get_post_meta( $post_id, '_snipi_display_bottom', true );
		$bottom_row        = get_post_meta( $post_id, '_snipi_bottom_row', true );
		$custom_css        = get_post_meta( $post_id, '_snipi_custom_css', true );
		$weekend_mode      = get_post_meta( $post_id, '_snipi_weekend_mode', true );
		$show_program      = get_post_meta( $post_id, '_snipi_show_program_column', true );
		
		// TV Optimizacija (v2.2.0)
		$enable_tv_detection = get_post_meta( $post_id, '_snipi_enable_tv_detection', true );
		$tv_mode_override    = get_post_meta( $post_id, '_snipi_tv_mode_override', true );
		$tv_confirm_dialog   = get_post_meta( $post_id, '_snipi_tv_confirm_dialog', true );

		// Nastavi default vrednosti če meta ne obstaja
		$rows_per_page     = $rows_per_page ?: 8;
		$autoplay_interval = $autoplay_interval ?: 10;
		$future_days       = max( 0, min( 30, intval( $future_days ) ) );
		$logo_height       = max( 40, min( 120, intval( $logo_height ?: 60 ) ) );
		$custom_css        = is_string( $custom_css ) ? $custom_css : '';
		
		// TV defaults
		$enable_tv_detection = $enable_tv_detection !== '' ? $enable_tv_detection : '1';
		$tv_mode_override    = $tv_mode_override ?: 'auto';
		$tv_confirm_dialog   = $tv_confirm_dialog !== '' ? $tv_confirm_dialog : '1';

		// Izračunaj število dogodkov za danes (če je API ključ nastavljen)
		$today_events_count = null;
		if ( ! empty( $api_key ) ) {
			$today_events_count = self::count_today_events( $api_key );
		}

		// Vrni vse podatke kot array
		return array(
			'api_key'             => $api_key,
			'rows_per_page'       => intval( $rows_per_page ),
			'autoplay_interval'   => intval( $autoplay_interval ),
			'future_days'         => intval( $future_days ),
			'logo_id'             => intval( $logo_id ),
			'logo_height'         => intval( $logo_height ),
			'display_bottom'      => $display_bottom,
			'bottom_row'          => $bottom_row,
			'custom_css'          => $custom_css,
			'today_count'         => $today_events_count,
			'weekend_mode'        => $weekend_mode,
			'show_program_column' => $show_program,
			// TV Optimizacija (v2.2.0)
			'enable_tv_detection' => $enable_tv_detection,
			'tv_mode_override'    => $tv_mode_override,
			'tv_confirm_dialog'   => $tv_confirm_dialog,
		);
	}

	/**
	 * Prešteje število dogodkov za današnji dan
	 * 
	 * Pokliče API in vrne število dogodkov.
	 * Uporablja se za prikaz "X dogodkov danes" v admin panelu.
	 * 
	 * @param string $api_key API ključ ekrana
	 * @return int|null Število dogodkov ali null če ni uspelo
	 */
	protected static function count_today_events( $api_key ) {
		// Pridobi današnji datum v Ljubljana timezone
		$tz    = new DateTimeZone( 'Europe/Ljubljana' );
		$today = new DateTime( 'now', $tz );
		$date  = $today->format( 'Y-m-d' );

		// Pokliči API za današnji dan
		$events = SNIPI_Data_Service::get_timeslots( $api_key, $date, $date, true );

		// Vrni število dogodkov ali null pri napaki
		if ( is_wp_error( $events ) ) {
			return null;
		}

		return is_array( $events ) ? count( $events ) : 0;
	}

	/**
	 * Pridobi naslov ekrana z fallbackom
	 * 
	 * @param int $post_id ID ekrana
	 * @return string Naslov ekrana
	 */
	public static function get_screen_title( $post_id ) {
		$post = get_post( $post_id );
		
		if ( ! $post || 'ekran' !== $post->post_type ) {
			return 'Ekran';
		}

		return $post->post_title ? $post->post_title : 'Ekran';
	}

	/**
	 * Posodobi naslov ekrana
	 * 
	 * @param int    $post_id ID ekrana
	 * @param string $title   Nov naslov
	 * @return void
	 */
	public static function update_screen_title( $post_id, $title ) {
		wp_update_post( array(
			'ID'         => $post_id,
			'post_title' => sanitize_text_field( $title ),
		) );
	}
}
