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
 * @since   1.2.0
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

		// API ključ – obvezno polje
		if ( isset( $_POST['snipi_api_key'] ) ) {
			$api_key = sanitize_text_field( wp_unslash( $_POST['snipi_api_key'] ) );
			update_post_meta( $post_id, '_snipi_api_key', $api_key );
		}

		// Vrstic na stran – število med 1 in 50
		if ( isset( $_POST['snipi_rows_per_page'] ) ) {
			$rows = max( 1, min( 50, intval( $_POST['snipi_rows_per_page'] ) ) );
			update_post_meta( $post_id, '_snipi_rows_per_page', $rows );
		}

		// Autoplay interval – sekunde med 5 in 60
		if ( isset( $_POST['snipi_autoplay_interval'] ) ) {
			$interval = max( 5, min( 60, intval( $_POST['snipi_autoplay_interval'] ) ) );
			update_post_meta( $post_id, '_snipi_autoplay_interval', $interval );
		}

		// Prihodnji dnevi – število med 0 in 3
		if ( isset( $_POST['snipi_future_days'] ) ) {
			$future_days = max( 0, min( 3, intval( $_POST['snipi_future_days'] ) ) );
			update_post_meta( $post_id, '_snipi_future_days', $future_days );
		}

		// Samodejno skaliranje pisave – 'fill' (privzeto) ali 'free'
		$row_scale_mode = isset( $_POST['snipi_row_scale_mode'] )
			? ( 'free' === $_POST['snipi_row_scale_mode'] ? 'free' : 'fill' )
			: 'fill';
		update_post_meta( $post_id, '_snipi_row_scale_mode', $row_scale_mode );

		// Vikend način – checkbox (1 ali 0)
		$weekend_mode = isset( $_POST['snipi_weekend_mode'] ) ? '1' : '0';
		update_post_meta( $post_id, '_snipi_weekend_mode', $weekend_mode );

		// Prikaži stolpec PROGRAM – checkbox (1 ali 0)
		$show_program = isset( $_POST['snipi_show_program_column'] ) ? '1' : '0';
		update_post_meta( $post_id, '_snipi_show_program_column', $show_program );

		// Logo ID – attachment ID iz media library
		if ( isset( $_POST['snipi_logo_id'] ) ) {
			update_post_meta( $post_id, '_snipi_logo_id', intval( $_POST['snipi_logo_id'] ) );
		}

		// Višina logotipa – px med 40 in 120
		if ( isset( $_POST['snipi_logo_height'] ) ) {
			$logo_height = max( 40, min( 120, intval( $_POST['snipi_logo_height'] ) ) );
			update_post_meta( $post_id, '_snipi_logo_height', $logo_height );
		}

		// Prikaži spodnjo vrstico – checkbox (1 ali 0)
		$display_bottom = isset( $_POST['snipi_display_bottom'] ) ? '1' : '0';
		update_post_meta( $post_id, '_snipi_display_bottom', $display_bottom );

		// Vsebina spodnje vrstice – WYSIWYG HTML
		if ( isset( $_POST['snipi_bottom_row'] ) ) {
			$bottom_row = wp_kses_post( wp_unslash( $_POST['snipi_bottom_row'] ) );
			update_post_meta( $post_id, '_snipi_bottom_row', $bottom_row );
		}

		// Višina spodnje vrstice – 'auto' (privzeto) ali 'fixed'
		$footer_height_mode = isset( $_POST['snipi_footer_height_mode'] )
			? ( 'fixed' === $_POST['snipi_footer_height_mode'] ? 'fixed' : 'auto' )
			: 'auto';
		update_post_meta( $post_id, '_snipi_footer_height_mode', $footer_height_mode );

		// Fiksna višina spodnje vrstice – px med 40 in 200
		if ( isset( $_POST['snipi_footer_fixed_height'] ) ) {
			$footer_fixed_height = max( 40, min( 200, intval( $_POST['snipi_footer_fixed_height'] ) ) );
			update_post_meta( $post_id, '_snipi_footer_fixed_height', $footer_fixed_height );
		}

		// Custom CSS – textarea za oblikovanje
		if ( isset( $_POST['snipi_custom_css'] ) ) {
			$custom_css = sanitize_textarea_field( wp_unslash( $_POST['snipi_custom_css'] ) );
			update_post_meta( $post_id, '_snipi_custom_css', $custom_css );
		}

		// Styling GUI data – JSON z vizualnimi nastavitvami
		self::save_styling_data( $post_id );

		// TV Optimizacija – checkbox (1 ali 0)
		$enable_tv_detection = isset( $_POST['snipi_enable_tv_detection'] ) ? '1' : '0';
		update_post_meta( $post_id, '_snipi_enable_tv_detection', $enable_tv_detection );

		// TV način override
		if ( isset( $_POST['snipi_tv_mode_override'] ) ) {
			$tv_mode = in_array( $_POST['snipi_tv_mode_override'], array( 'auto', 'tv', 'desktop' ), true )
				? sanitize_text_field( $_POST['snipi_tv_mode_override'] )
				: 'auto';
			update_post_meta( $post_id, '_snipi_tv_mode_override', $tv_mode );
		}

		// TV potrditveno okno – checkbox (1 ali 0)
		$tv_confirm_dialog = isset( $_POST['snipi_tv_confirm_dialog'] ) ? '1' : '0';
		update_post_meta( $post_id, '_snipi_tv_confirm_dialog', $tv_confirm_dialog );
	}

	/**
	 * Shrani styling GUI podatke kot JSON
	 *
	 * Zbere vse snipi_style_* POST polja in jih shrani kot eno JSON vrednost.
	 * Shranjevanje je idempotentno – manjkajoča polja dobijo privzete vrednosti.
	 *
	 * @param int $post_id ID ekrana
	 * @return void
	 */
	protected static function save_styling_data( $post_id ) {

		// Privzete vrednosti – ne smejo povzročiti vidne spremembe na frontendu
		$defaults = self::get_styling_defaults();

		// Helper: preberi in sanitiziraj eno styling vrednost iz POST
		$p = function( $key, $type = 'text' ) use ( $defaults ) {
			// Razstavi ključ v sekcijo in polje: 'screen_bg' → $defaults['screen']['bg']
			if ( ! isset( $_POST[ 'snipi_style_' . $key ] ) ) {
				return null; // bo nastavil privzeto vrednost v caller
			}
			$raw = wp_unslash( $_POST[ 'snipi_style_' . $key ] );
			if ( 'color' === $type ) {
				return self::sanitize_css_color( $raw );
			}
			if ( 'int' === $type ) {
				return intval( $raw );
			}
			if ( 'bool' === $type ) {
				return isset( $_POST[ 'snipi_style_' . $key ] ) ? '1' : '0';
			}
			return sanitize_text_field( $raw );
		};

		// Checkbox show_live je posebej – ni v $_POST ko ni označen
		$show_live = isset( $_POST['snipi_style_table_show_live'] ) ? '1' : '0';

		$data = array(
			'screen' => array(
				'font_family' => $p( 'screen_font_family' ) ?? $defaults['screen']['font_family'],
				'background'  => $p( 'screen_bg', 'color' ) ?? $defaults['screen']['background'],
				'color'       => $p( 'screen_color', 'color' ) ?? $defaults['screen']['color'],
			),
			'header' => array(
				'background'  => $p( 'header_bg', 'color' ) ?? $defaults['header']['background'],
				'title_color' => $p( 'header_title_color', 'color' ) ?? $defaults['header']['title_color'],
				'meta_color'  => $p( 'header_meta_color', 'color' ) ?? $defaults['header']['meta_color'],
				'font_scale'  => isset( $_POST['snipi_style_header_font_scale'] )
					? max( 70, min( 150, intval( $_POST['snipi_style_header_font_scale'] ) ) )
					: $defaults['header']['font_scale'],
				'padding_top' => isset( $_POST['snipi_style_header_padding_top'] )
					? max( 0, min( 60, intval( $_POST['snipi_style_header_padding_top'] ) ) )
					: $defaults['header']['padding_top'],
				'padding_h'   => isset( $_POST['snipi_style_header_padding_h'] )
					? max( 0, min( 60, intval( $_POST['snipi_style_header_padding_h'] ) ) )
					: $defaults['header']['padding_h'],
			),
			'table' => array(
				'thead_bg'    => $p( 'table_thead_bg', 'color' ) ?? $defaults['table']['thead_bg'],
				'thead_color' => $p( 'table_thead_color', 'color' ) ?? $defaults['table']['thead_color'],
				'row_color'   => $p( 'table_row_color', 'color' ) ?? $defaults['table']['row_color'],
				'alt_bg'      => $p( 'table_alt_bg', 'color' ) ?? $defaults['table']['alt_bg'],
				'font_scale'  => isset( $_POST['snipi_style_table_font_scale'] )
					? max( 70, min( 150, intval( $_POST['snipi_style_table_font_scale'] ) ) )
					: $defaults['table']['font_scale'],
				'padding_top' => isset( $_POST['snipi_style_table_padding_top'] )
					? max( 0, min( 40, intval( $_POST['snipi_style_table_padding_top'] ) ) )
					: $defaults['table']['padding_top'],
				'padding_h'   => isset( $_POST['snipi_style_table_padding_h'] )
					? max( 0, min( 60, intval( $_POST['snipi_style_table_padding_h'] ) ) )
					: $defaults['table']['padding_h'],
				'show_live'   => $show_live,
			),
			'footer' => array(
				'background' => $p( 'footer_bg', 'color' ) ?? $defaults['footer']['background'],
				'color'      => $p( 'footer_color', 'color' ) ?? $defaults['footer']['color'],
				'font_scale' => isset( $_POST['snipi_style_footer_font_scale'] )
					? max( 70, min( 150, intval( $_POST['snipi_style_footer_font_scale'] ) ) )
					: $defaults['footer']['font_scale'],
				'text_align' => isset( $_POST['snipi_style_footer_text_align'] )
					? ( in_array( $_POST['snipi_style_footer_text_align'], array( 'left', 'center', 'right' ), true )
						? $_POST['snipi_style_footer_text_align']
						: 'center' )
					: $defaults['footer']['text_align'],
				'padding_top' => isset( $_POST['snipi_style_footer_padding_top'] )
					? max( 0, min( 60, intval( $_POST['snipi_style_footer_padding_top'] ) ) )
					: $defaults['footer']['padding_top'],
				'padding_h'   => isset( $_POST['snipi_style_footer_padding_h'] )
					? max( 0, min( 60, intval( $_POST['snipi_style_footer_padding_h'] ) ) )
					: $defaults['footer']['padding_h'],
			),
		);

		update_post_meta( $post_id, '_snipi_styling_data', wp_json_encode( $data ) );
	}

	/**
	 * Privzete vrednosti za styling GUI
	 *
	 * Vrednosti, ki odražajo obstoječ izgled brez sprememb.
	 *
	 * @return array
	 */
	public static function get_styling_defaults() {
		return array(
			'screen' => array(
				'font_family' => 'system-ui',
				'background'  => '',
				'color'       => '',
			),
			'header' => array(
				'background'  => '',
				'title_color' => '',
				'meta_color'  => '',
				'font_scale'  => 100,
				'padding_top' => 10,
				'padding_h'   => 16,
			),
			'table' => array(
				'thead_bg'    => '',
				'thead_color' => '',
				'row_color'   => '',
				'alt_bg'      => '',
				'font_scale'  => 100,
				'padding_top' => 6,
				'padding_h'   => 14,
				'show_live'   => '1',
			),
			'footer' => array(
				'background'  => '',
				'color'       => '',
				'font_scale'  => 100,
				'text_align'  => 'center',
				'padding_top' => 8,
				'padding_h'   => 16,
			),
		);
	}

	/**
	 * Sanitizira CSS barvno vrednost
	 *
	 * Dovoli hex barve (#RGB, #RRGGBB), rgba() in rgb() vrednosti.
	 *
	 * @param string $color
	 * @return string Sanitizirana barva ali prazen niz
	 */
	public static function sanitize_css_color( $color ) {
		$color = trim( (string) $color );
		if ( '' === $color ) {
			return '';
		}
		// Hex barve
		if ( preg_match( '/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $color ) ) {
			return $color;
		}
		// rgb() in rgba()
		if ( preg_match( '/^rgba?\(\s*\d{1,3}\s*,\s*\d{1,3}\s*,\s*\d{1,3}(\s*,\s*[\d.]+)?\s*\)$/', $color ) ) {
			return $color;
		}
		return '';
	}

	/**
	 * Pridobi vse meta podatke za določen ekran
	 *
	 * @param int $post_id ID ekrana
	 * @return array Asociativni array z vsemi nastavitvami
	 */
	public static function get_all( $post_id ) {

		$api_key           = get_post_meta( $post_id, '_snipi_api_key', true );
		$rows_per_page     = get_post_meta( $post_id, '_snipi_rows_per_page', true );
		$autoplay_interval = get_post_meta( $post_id, '_snipi_autoplay_interval', true );
		$future_days       = get_post_meta( $post_id, '_snipi_future_days', true );
		$row_scale_mode    = get_post_meta( $post_id, '_snipi_row_scale_mode', true );
		$logo_id           = get_post_meta( $post_id, '_snipi_logo_id', true );
		$logo_height       = get_post_meta( $post_id, '_snipi_logo_height', true );
		$display_bottom    = get_post_meta( $post_id, '_snipi_display_bottom', true );
		$bottom_row        = get_post_meta( $post_id, '_snipi_bottom_row', true );
		$footer_height_mode  = get_post_meta( $post_id, '_snipi_footer_height_mode', true );
		$footer_fixed_height = get_post_meta( $post_id, '_snipi_footer_fixed_height', true );
		$custom_css        = get_post_meta( $post_id, '_snipi_custom_css', true );
		$styling_raw       = get_post_meta( $post_id, '_snipi_styling_data', true );
		$weekend_mode      = get_post_meta( $post_id, '_snipi_weekend_mode', true );
		$show_program      = get_post_meta( $post_id, '_snipi_show_program_column', true );

		// TV Optimizacija
		$enable_tv_detection = get_post_meta( $post_id, '_snipi_enable_tv_detection', true );
		$tv_mode_override    = get_post_meta( $post_id, '_snipi_tv_mode_override', true );
		$tv_confirm_dialog   = get_post_meta( $post_id, '_snipi_tv_confirm_dialog', true );

		// Privzete vrednosti
		$rows_per_page       = $rows_per_page ? intval( $rows_per_page ) : 8;
		$autoplay_interval   = $autoplay_interval ? intval( $autoplay_interval ) : 10;
		$future_days         = max( 0, min( 3, intval( $future_days ) ) );
		$row_scale_mode      = ( 'free' === $row_scale_mode ) ? 'free' : 'fill';
		$logo_height         = max( 40, min( 120, intval( $logo_height ?: 60 ) ) );
		$footer_height_mode  = ( 'fixed' === $footer_height_mode ) ? 'fixed' : 'auto';
		$footer_fixed_height = max( 40, min( 200, intval( $footer_fixed_height ?: 80 ) ) );
		$custom_css          = is_string( $custom_css ) ? $custom_css : '';

		// Styling GUI – razstavi JSON ali nastavi privzete vrednosti
		$styling_data = array();
		if ( ! empty( $styling_raw ) ) {
			$decoded = json_decode( $styling_raw, true );
			if ( is_array( $decoded ) ) {
				$styling_data = $decoded;
			}
		}
		// Dopolni z defaults za morebitna manjkajoča polja
		$defaults     = self::get_styling_defaults();
		$styling_data = array_replace_recursive( $defaults, $styling_data );

		// TV privzete vrednosti
		$enable_tv_detection = ( '' !== $enable_tv_detection ) ? $enable_tv_detection : '1';
		$tv_mode_override    = $tv_mode_override ?: 'auto';
		$tv_confirm_dialog   = ( '' !== $tv_confirm_dialog ) ? $tv_confirm_dialog : '1';

		// Število dogodkov danes (samo če je API ključ znan)
		$today_events_count = null;
		if ( ! empty( $api_key ) ) {
			$today_events_count = self::count_today_events( $api_key );
		}

		return array(
			'api_key'             => $api_key,
			'rows_per_page'       => $rows_per_page,
			'autoplay_interval'   => $autoplay_interval,
			'future_days'         => $future_days,
			'row_scale_mode'      => $row_scale_mode,
			'logo_id'             => intval( $logo_id ),
			'logo_height'         => $logo_height,
			'display_bottom'      => $display_bottom,
			'bottom_row'          => $bottom_row,
			'footer_height_mode'  => $footer_height_mode,
			'footer_fixed_height' => $footer_fixed_height,
			'custom_css'          => $custom_css,
			'styling_data'        => $styling_data,
			'today_count'         => $today_events_count,
			'weekend_mode'        => $weekend_mode,
			'show_program_column' => $show_program,
			// TV Optimizacija
			'enable_tv_detection' => $enable_tv_detection,
			'tv_mode_override'    => $tv_mode_override,
			'tv_confirm_dialog'   => $tv_confirm_dialog,
		);
	}

	/**
	 * Prešteje število dogodkov za današnji dan
	 *
	 * @param string $api_key API ključ ekrana
	 * @return int|null
	 */
	protected static function count_today_events( $api_key ) {
		$tz    = new DateTimeZone( 'Europe/Ljubljana' );
		$today = new DateTime( 'now', $tz );
		$date  = $today->format( 'Y-m-d' );

		$events = SNIPI_Data_Service::get_timeslots( $api_key, $date, $date, true );

		if ( is_wp_error( $events ) ) {
			return null;
		}

		return is_array( $events ) ? count( $events ) : 0;
	}

	/**
	 * Pridobi naslov ekrana z fallbackom
	 *
	 * @param int $post_id ID ekrana
	 * @return string
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
