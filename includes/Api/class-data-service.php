<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SNIPI_Data_Service
 * Fetches and normalizes timeslots from Snipi API.
 *
 * Key behaviors:
 *  - Always uses Europe/Ljubljana timezone for parsing/normalization.
 *  - Accepts ISO8601 from API; has robust fallback parsing for other formats.
 *  - Removes events that already ended (event_end < now).
 *  - Sorts by start ascending.
 *  - Adds normalized 'start_iso' and 'end_iso' fields (with timezone offset).
 */

class SNIPI_Data_Service {

	/**
	 * Fetch timeslots from remote Snipi API.
	 *
	 * @param string $key      Snipi "key" (zadnji del URLja).
	 * @param string $dateFrom yyyy-MM-dd (ISO).
	 * @param string $dateTo   yyyy-MM-dd (ISO).
	 * @param bool   $include_past Optional. Če je true, vrne tudi dogodke, ki so se že zaključili.
	 *
	 * @return array|WP_Error
	 */
	public static function get_timeslots( $key, $dateFrom, $dateTo, $include_past = false ) {
		$key      = sanitize_text_field( $key );
		$dateFrom = sanitize_text_field( $dateFrom );
		$dateTo   = sanitize_text_field( $dateTo );

		if ( empty( $key ) || empty( $dateFrom ) || empty( $dateTo ) ) {
			return new WP_Error( 'missing_params', 'Manjkajoči parametri za Snipi API.' );
		}

		$endpoint = add_query_arg(
			array(
				'key'      => $key,
				'dateFrom' => $dateFrom,
				'dateTo'   => $dateTo,
			),
			'https://upi.snipi.si/api/Scheduler/GetTimeSlots'
		);

		$args = array(
			'timeout' => 15,
			'headers' => array(
				'Accept' => 'application/json',
			),
		);

		$response = wp_remote_get( $endpoint, $args );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$code = wp_remote_retrieve_response_code( $response );
		$body = wp_remote_retrieve_body( $response );

		if ( $code !== 200 ) {
			return new WP_Error( 'api_error', 'Napaka pri povezavi na Snipi API (HTTP ' . $code . ').' );
		}

		$data = json_decode( $body, true );
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			return new WP_Error( 'json_error', 'Neveljaven JSON iz API-ja' );
		}

		// Normalize, filter and sort
		$normalized = self::normalize_filter_and_sort( $data, $include_past );

		return $normalized;
	}

	/**
	 * Normalize event timestamps, filter out past events (end < now), sort by start asc.
	 *
	 * @param array $items
	 * @return array
	 */
	protected static function normalize_filter_and_sort( $items, $include_past = false ) {
		if ( empty( $items ) || ! is_array( $items ) ) {
			return array();
		}

		$tz  = new DateTimeZone( 'Europe/Ljubljana' );
		$now = new DateTime( 'now', $tz );

		$out = array();

		foreach ( $items as $it ) {
			$raw_start = isset( $it['start'] ) ? trim( $it['start'] ) : '';
			$raw_end   = isset( $it['end'] ) ? trim( $it['end'] ) : '';

			$start_dt = self::parse_datetime_fallback( $raw_start, $tz );
			$end_dt   = self::parse_datetime_fallback( $raw_end, $tz );

			if ( ! $start_dt || ! $end_dt ) {
				// skip invalid entries
				continue;
			}

			// Filter out events that already ended
			if ( $end_dt < $now && ! $include_past ) {
				continue;
			}

			$it['start_iso'] = $start_dt->format( DATE_ATOM );
			$it['end_iso']   = $end_dt->format( DATE_ATOM );

			$out[] = $it;
		}

		// Sort by start ascending
		usort(
			$out,
			function( $a, $b ) {
				$as = isset( $a['start_iso'] ) ? strtotime( $a['start_iso'] ) : 0;
				$bs = isset( $b['start_iso'] ) ? strtotime( $b['start_iso'] ) : 0;
				return $as - $bs;
			}
		);

		return $out;
	}

	/**
	 * Parse a datetime string into DateTime in given timezone.
	 * Tries:
	 *  - direct DateTime($raw, $tz)
	 *  - several common formats (ISO, "d. m. Y H:i", etc.)
	 *
	 * @param string        $raw
	 * @param DateTimeZone  $tz
	 * @return DateTime|false
	 */
	protected static function parse_datetime_fallback( $raw, $tz ) {
		if ( empty( $raw ) ) {
			return false;
		}

		$raw = trim( $raw );

		// First try direct parse (supports full ISO with offset)
		try {
			$dt = new DateTime( $raw, $tz );
			return $dt;
		} catch ( Exception $e ) {
			// fallback below
		}

		$formats = array(
			'Y-m-d H:i:s',
			'Y-m-d\TH:i:s',
			'd. m. Y H:i:s',
			'd. m. Y H:i',
			'd.m.Y H:i',
			'd.m.Y H:i:s',
			'd/m/Y H:i',
			'Y-m-d',
			'H:i',
			'H:i:s',
		);

		foreach ( $formats as $fmt ) {
			$dt = DateTime::createFromFormat( $fmt, $raw, $tz );
			if ( $dt instanceof DateTime ) {
				// If format lacks a full date, assume "today"
				if ( strpos( $fmt, 'Y' ) === false ) {
					$today = new DateTime( 'now', $tz );
					$dt->setDate( intval( $today->format( 'Y' ) ), intval( $today->format( 'm' ) ), intval( $today->format( 'd' ) ) );
				}
				return $dt;
			}
		}

		// Regex for patterns like "13. 11. 2025 16:00-19:00"
		if ( preg_match( '/(\d{1,2}[\.\/\-]\s?\d{1,2}[\.\/\-]\s?\d{2,4})\s+(\d{1,2}:\d{2})/', $raw, $matches ) ) {
			$date_str = preg_replace( '/[\/\-]/', '.', $matches[1] );
			$time     = isset( $matches[2] ) ? $matches[2] : '00:00';

			$dt = DateTime::createFromFormat( 'd.m.Y H:i', trim( $date_str . ' ' . $time ), $tz );
			if ( $dt instanceof DateTime ) {
				return $dt;
			}
		}

		return false;
	}
}
