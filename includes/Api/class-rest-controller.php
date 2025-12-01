<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SNIPI_REST_Controller
 * Routes:
 *  - GET  /wp-json/snipi/v1/ekrani/timeslots?post_id=ID
 *  - POST /wp-json/snipi/v1/ekrani/preview  (admin-only)
 *
 * This controller:
 *  - requests normalized events from SNIPI_Data_Service
 *  - applies strict server-side timezone-aware filtering:
 *      * includes events scheduled for the requested date (dateFrom)
 *      * hides only events whose end < now (Europe/Ljubljana)
 *  - sorts by start ascending and returns safe JSON
 */

class SNIPI_REST_Controller {

	/**
	 * Allow public access to the public timeslots endpoint even if a site-wide
	 * REST authentication block is present.
	 *
	 * @param mixed $result Current authentication error/result.
	 * @return mixed
	 */
	public static function allow_public_timeslots( $result ) {
		if ( true === $result || is_wp_error( $result ) ) {
			return $result;
		}

		$route = '';

		if ( isset( $GLOBALS['wp']->query_vars['rest_route'] ) ) {
			$route = sanitize_text_field( wp_unslash( $GLOBALS['wp']->query_vars['rest_route'] ) );
		} elseif ( isset( $_GET['rest_route'] ) ) {
			$route = sanitize_text_field( wp_unslash( $_GET['rest_route'] ) );
		}

		if ( is_string( $route ) && strpos( $route, '/snipi/v1/ekrani/timeslots' ) === 0 ) {
			return true;
		}

		return $result;
	}

	public static function register_routes() {
		add_filter( 'rest_authentication_errors', array( __CLASS__, 'allow_public_timeslots' ) );

		register_rest_route( 'snipi/v1', '/ekrani/timeslots', array(
			'methods'  => 'GET',
			'callback' => array( __CLASS__, 'handle_get_timeslots' ),
			'permission_callback' => '__return_true',
			'args' => array(
				'post_id' => array(
					'required' => true,
					'sanitize_callback' => 'absint',
				),
			),
		) );

		register_rest_route( 'snipi/v1', '/ekrani/preview', array(
			'methods'  => 'POST',
			'callback' => array( __CLASS__, 'handle_preview' ),
			'permission_callback' => function() {
				return current_user_can( 'edit_posts' );
			},
			'args' => array(
				'post_id' => array(
					'required' => true,
					'sanitize_callback' => 'absint',
				),
				'style' => array(
					'required' => false,
				),
			),
		) );
	}

	/**
	 * GET timeslots endpoint
	 *
	 * Query params:
	 *  - post_id (required)
	 *
	 * Returns:
	 *  {
	 *    items: [ ... ],
	 *    logo_url: "...",
	 *    bottom_row: "...",
	 *    display_bottom: true/false
	 *  }
	 */
public static function handle_get_timeslots( $request ) {
$post_id = $request->get_param( 'post_id' );
if ( ! $post_id ) {
return new WP_REST_Response( array( 'error' => 'Manjka post_id' ), 400 );
}

$show_program = get_post_meta( $post_id, '_snipi_show_program_column', true ) === '1';
$rows_per_page = intval( get_post_meta( $post_id, '_snipi_rows_per_page', true ) ?: 8 );
$autoplay_interval = intval( get_post_meta( $post_id, '_snipi_autoplay_interval', true ) ?: 10 );

$api_key = get_post_meta( $post_id, '_snipi_api_key', true );
$range   = self::resolve_date_range( $post_id );
$date_from = $range['from'];
$date_to   = $range['to'];
		
		if ( ! class_exists( 'SNIPI_Data_Service' ) ) {
			return new WP_REST_Response( array( 'error' => 'Data service missing' ), 500 );
		}
		
		$result = SNIPI_Data_Service::get_timeslots( $api_key, $date_from, $date_to );
		
		if ( is_wp_error( $result ) ) {
			return new WP_REST_Response( array( 'error' => $result->get_error_message() ), 500 );
		}
		
		$tz         = new DateTimeZone( 'Europe/Ljubljana' );
		$now        = new DateTime( 'now', $tz );
		$range_from = DateTime::createFromFormat( 'Y-m-d H:i:s', $date_from . ' 00:00:00', $tz );
		$range_to   = DateTime::createFromFormat( 'Y-m-d H:i:s', $date_to . ' 23:59:59', $tz );
		
		$items = array();
		foreach ( $result as $ev ) {
		$start_iso = isset( $ev['start_iso'] ) ? $ev['start_iso'] : ( isset( $ev['start'] ) ? $ev['start'] : '' );
		$end_iso   = isset( $ev['end_iso'] ) ? $ev['end_iso'] : ( isset( $ev['end'] ) ? $ev['end'] : '' );
		$project   = isset( $ev['project'] ) ? $ev['project'] : '';
		$subjects  = isset( $ev['subjects'] ) && is_array( $ev['subjects'] ) ? $ev['subjects'] : array();
		
		$program_value = $project;
		if ( empty( $program_value ) && ! empty( $subjects ) ) {
		foreach ( $subjects as $subject ) {
		if ( ! empty( $subject['studyName'] ) ) {
		$program_value = $subject['studyName'];
		break;
		}
		}
		}
		
			try {
				$start_dt = new DateTime( $start_iso, $tz );
			} catch ( Exception $e ) {
				$start_dt = false;
			}
			try {
				$end_dt = new DateTime( $end_iso, $tz );
			} catch ( Exception $e ) {
				$end_dt = false;
			}
		
			if ( ! $start_dt || ! $end_dt ) {
				continue;
			}
		
			if ( $range_from && $start_dt < $range_from ) {
				continue;
			}
			if ( $range_to && $start_dt > $range_to ) {
				continue;
			}
		
			if ( $end_dt < $now ) {
				continue;
			}
		
		$items[] = array(
		'objectId' => isset( $ev['objectId'] ) ? $ev['objectId'] : null,
		'name' => isset( $ev['name'] ) ? $ev['name'] : '',
		'teacher' => isset( $ev['teacher'] ) ? $ev['teacher'] : '',
		'room' => isset( $ev['room'] ) ? $ev['room'] : '',
		'floor' => isset( $ev['floor'] ) ? $ev['floor'] : '',
		'project' => $project,
		'program_display' => $program_value,
		'subjects' => $subjects,
		'start_iso' => $start_dt->format( DATE_ATOM ),
		'end_iso' => $end_dt->format( DATE_ATOM ),
		'timeDisplay' => isset( $ev['timeDisplay'] ) ? $ev['timeDisplay'] : '',
		'timeDisplayTimeOnly' => isset( $ev['timeDisplayTimeOnly'] ) ? $ev['timeDisplayTimeOnly'] : '',
		'subjectText' => isset( $ev['subjectText'] ) ? $ev['subjectText'] : '',
			);
		}
		
		usort( $items, function( $a, $b ) {
			$as = isset( $a['start_iso'] ) ? strtotime( $a['start_iso'] ) : 0;
			$bs = isset( $b['start_iso'] ) ? strtotime( $b['start_iso'] ) : 0;
			return $as - $bs;
		} );
		
		$logo_id = get_post_meta( $post_id, '_snipi_logo_id', true );
		$logo_url = $logo_id ? wp_get_attachment_image_url( $logo_id, 'full' ) : '';
		
		$bottom_row     = get_post_meta( $post_id, '_snipi_bottom_row', true );
$display_bottom = get_post_meta( $post_id, '_snipi_display_bottom', true ) === '1';

return new WP_REST_Response( array(
'items' => array_values( $items ),
'logo_url' => $logo_url,
'bottom_row' => $bottom_row,
'display_bottom' => $display_bottom,
'show_program_column' => $show_program,
'rows_per_page' => $rows_per_page,
'autoplay_interval' => $autoplay_interval,
), 200 );
		}
	public static function handle_preview( $request ) {
		$params = $request->get_json_params();
		$post_id = isset( $params['post_id'] ) ? intval( $params['post_id'] ) : 0;
		$style = isset( $params['style'] ) && is_array( $params['style'] ) ? $params['style'] : array();

		if ( ! $post_id ) {
			return new WP_REST_Response( array( 'error' => 'Manjka post_id' ), 400 );
		}

		$api_key = get_post_meta( $post_id, '_snipi_api_key', true );
		$range   = self::resolve_date_range( $post_id );
		$date_from = $range['from'];
		$date_to   = $range['to'];

		$result = SNIPI_Data_Service::get_timeslots( $api_key, $date_from, $date_to );
		if ( is_wp_error( $result ) ) {
			return new WP_REST_Response( array( 'error' => $result->get_error_message() ), 500 );
		}

		if ( class_exists( 'SNIPI_Renderer' ) && method_exists( 'SNIPI_Renderer', 'render_preview_fragment' ) ) {
			$html = SNIPI_Renderer::render_preview_fragment( $result, $style, $post_id );
		} else {
			$html = '<div style="color:#a00;padding:8px;">Renderer ni na voljo</div>';
		}

		return new WP_REST_Response( array( 'html' => $html ), 200 );
	}

	protected static function resolve_date_range( $post_id ) {
		$tz    = new DateTimeZone( 'Europe/Ljubljana' );
		$today = new DateTime( 'now', $tz );

		$future_days = intval( get_post_meta( $post_id, '_snipi_future_days', true ) );
		$future_days = max( 0, min( 3, $future_days ) );
		$weekend_mode = get_post_meta( $post_id, '_snipi_weekend_mode', true ) === '1';

		$date_from = $today->format( 'Y-m-d' );
		$date_to_dt = clone $today;

		if ( $weekend_mode ) {
			$day_of_week = intval( $today->format( 'N' ) );

			// Petek (5), sobota (6) in nedelja (7) vedno raztegnejo časovno
			// okno do naslednjega torka, da se na zaslonu vidijo ponedeljkovi
			// in torkovi dogodki. V ponedeljek ob polnoči se obnašanje
			// vrne na običajni razpon prihodnjih dni.
			if ( $day_of_week >= 5 ) {
				$date_to_dt->modify( 'next tuesday' );
			} else {
				$date_to_dt->modify( '+' . $future_days . ' day' );
			}
		} else {
			$date_to_dt->modify( '+' . $future_days . ' day' );
		}

		$date_to = $date_to_dt->format( 'Y-m-d' );

		return array(
			'from' => $date_from,
			'to'   => $date_to,
		);
	}

}

add_action( 'rest_api_init', array( 'SNIPI_REST_Controller', 'register_routes' ) );
