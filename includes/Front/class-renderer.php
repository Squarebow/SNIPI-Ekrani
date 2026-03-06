<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SNIPI_Renderer
 *
 * - Renders front-end shell (full page wrapper)
 * - Generates per-screen scoped CSS from styling GUI data
 * - Used also by REST preview to render admin preview fragment
 *
 * CSS scoping: each screen wrapper gets class `snipi--screen-{post_id}`.
 * Styling CSS targets this class, so multiple screens on the same page
 * can have different styles without conflict.
 */
class SNIPI_Renderer {

	/**
	 * Renders full front-end shell (header + empty table + bottom row container).
	 *
	 * @param int $post_id Post ID.
	 * @return string HTML string.
	 */
	public static function render_front_shell( $post_id ) {

		$logo_id        = get_post_meta( $post_id, '_snipi_logo_id', true );
		$logo_height    = get_post_meta( $post_id, '_snipi_logo_height', true ) ?: 60;
		$display_bottom = get_post_meta( $post_id, '_snipi_display_bottom', true );
		$bottom_row     = get_post_meta( $post_id, '_snipi_bottom_row', true );
		$show_program   = get_post_meta( $post_id, '_snipi_show_program_column', true ) === '1';

		$logo_url   = $logo_id ? wp_get_attachment_image_url( $logo_id, 'full' ) : '';
		$logo_style = 'height:' . intval( $logo_height ) . 'px;';
		if ( empty( $logo_url ) ) {
			$logo_style .= 'display:none;';
		}
		$logo_html = '<img src="' . esc_url( $logo_url ) . '" alt="Logo" class="snipi__logo" style="' . esc_attr( $logo_style ) . '" />';

		$bottom_classes = array( 'snipi__bottom-row' );
		$bottom_content = '';
		if ( $display_bottom === '1' && ! empty( $bottom_row ) ) {
			$bottom_content = wp_kses_post( $bottom_row );
		} else {
			$bottom_classes[] = 'snipi__bottom-row--hidden';
		}

		// Wrapper klasa vključuje snipi--screen-{post_id} za scoped CSS
		$wrapper_class = 'snipi snipi--shell snipi--screen-' . intval( $post_id );

		ob_start();
		?>
		<div class="<?php echo esc_attr( $wrapper_class ); ?>">

			<!-- ==============================
			     HEADER
			     ============================== -->
			<div class="snipi__header">

				<div class="snipi__header-left">
					<?php echo $logo_html; // phpcs:ignore ?>
				</div>

				<div class="snipi__header-center">
					<span class="snipi__title snipi__title--large">Urnik izobraževanj</span>
					<div class="snipi__subheader">
						<span class="snipi__date"><!-- Datum se dinamično osveži v JS --></span>
					</div>
				</div>

				<div class="snipi__header-right">
					<span class="snipi__pagination">stran 1/1</span>
					<span class="snipi__clock-value"><!-- Ura se osveži v JS --></span>
				</div>

			</div>

			<!-- ==============================
			     TABELA
			     ============================== -->
			<div class="snipi__table-wrapper">
				<table class="snipi__table">
					<thead>
						<tr>
							<th data-snipi-col="time">ČAS</th>
							<th data-snipi-col="name">IZOBRAŽEVANJE</th>
							<?php if ( $show_program ) : ?>
								<th data-snipi-col="program" data-snipi-program>PROGRAM</th>
							<?php endif; ?>
							<th data-snipi-col="teacher">PREDAVATELJ</th>
							<th data-snipi-col="room">UČILNICA</th>
							<th data-snipi-col="floor">NADSTROPJE</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td colspan="<?php echo $show_program ? '6' : '5'; ?>" style="text-align:center;padding:20px;">
								<?php esc_html_e( 'Nalagam podatke …', 'snipi-ekrani' ); ?>
							</td>
						</tr>
					</tbody>
				</table>
			</div>

			<!-- ==============================
			     SPODNJA VRSTICA
			     ============================== -->
			<div class="<?php echo esc_attr( implode( ' ', $bottom_classes ) ); ?>" data-snipi-bottom-row>
				<?php echo $bottom_content; // phpcs:ignore ?>
			</div>

		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Generates scoped CSS from the styling GUI data stored in _snipi_styling_data.
	 *
	 * Returns an empty string if no custom styling is set.
	 *
	 * @param int $post_id Post ID.
	 * @return string CSS string (not escaped – use with wp_add_inline_style).
	 */
	public static function generate_styling_css( $post_id ) {

		$raw = get_post_meta( $post_id, '_snipi_styling_data', true );
		if ( empty( $raw ) ) {
			return '';
		}

		$data = json_decode( $raw, true );
		if ( ! is_array( $data ) ) {
			return '';
		}

		// Zapolni z defaults za morebitna manjkajoča polja
		$defaults = SNIPI_Admin_Meta::get_styling_defaults();
		$data     = array_replace_recursive( $defaults, $data );

		$scope  = '.snipi--screen-' . intval( $post_id );
		$css    = '';

		$sc   = $data['screen'];
		$hd   = $data['header'];
		$tb   = $data['table'];
		$ft   = $data['footer'];

		$san = array( 'SNIPI_Admin_Meta', 'sanitize_css_color' );

		// ── A. CEL ZASLON ──────────────────────────────────────────────
		$s_rules = '';
		if ( ! empty( $sc['font_family'] ) ) {
			$s_rules .= 'font-family:' . sanitize_text_field( $sc['font_family'] ) . ';';
		}
		if ( ! empty( $sc['background'] ) ) {
			$c = call_user_func( $san, $sc['background'] );
			if ( $c ) $s_rules .= 'background:' . $c . ';';
		}
		if ( ! empty( $sc['color'] ) ) {
			$c = call_user_func( $san, $sc['color'] );
			if ( $c ) $s_rules .= 'color:' . $c . ';';
		}
		if ( $s_rules ) {
			$css .= $scope . '{' . $s_rules . '}';
		}

		// ── B. GLAVA ───────────────────────────────────────────────────
		if ( ! empty( $hd['background'] ) ) {
			$c = call_user_func( $san, $hd['background'] );
			if ( $c ) $css .= $scope . ' .snipi__header{background:' . $c . ';}';
		}
		if ( ! empty( $hd['title_color'] ) ) {
			$c = call_user_func( $san, $hd['title_color'] );
			if ( $c ) $css .= $scope . ' .snipi__title{color:' . $c . ';}';
		}
		if ( ! empty( $hd['meta_color'] ) ) {
			$c = call_user_func( $san, $hd['meta_color'] );
			if ( $c ) {
				$css .= $scope . ' .snipi__date,' . $scope . ' .snipi__clock-value,' . $scope . ' .snipi__pagination{color:' . $c . ';}';
			}
		}
		$h_scale = intval( $hd['font_scale'] );
		if ( $h_scale !== 100 ) {
			$fs = $h_scale / 100;
			$css .= $scope . ' .snipi__title--large{font-size:' . round( 2 * $fs, 3 ) . 'rem;}';
			$css .= $scope . ' .snipi__date{font-size:' . round( 1.3 * $fs, 3 ) . 'rem;}';
			$css .= $scope . ' .snipi__clock-value,' . $scope . ' .snipi__pagination{font-size:' . round( $fs, 3 ) . 'rem;}';
		}
		$h_pad_top = intval( $hd['padding_top'] );
		$h_pad_h   = intval( $hd['padding_h'] );
		if ( $h_pad_top !== 10 || $h_pad_h !== 16 ) {
			// Patch: header has separate padding – avoid overriding column-gap
			$css .= $scope . ' .snipi__header{padding-top:' . $h_pad_top . 'px;padding-bottom:' . $h_pad_top . 'px;padding-left:' . $h_pad_h . 'px;padding-right:' . $h_pad_h . 'px;}';
		}

		// ── C. TABELA ──────────────────────────────────────────────────
		if ( ! empty( $tb['thead_bg'] ) ) {
			$c = call_user_func( $san, $tb['thead_bg'] );
			if ( $c ) $css .= $scope . ' .snipi__table thead{background:' . $c . ';}';
		}
		if ( ! empty( $tb['thead_color'] ) ) {
			$c = call_user_func( $san, $tb['thead_color'] );
			if ( $c ) $css .= $scope . ' .snipi__table thead th{color:' . $c . ';}';
		}
		if ( ! empty( $tb['row_color'] ) ) {
			$c = call_user_func( $san, $tb['row_color'] );
			if ( $c ) $css .= $scope . ' .snipi__table tbody td{color:' . $c . ';}';
		}
		if ( ! empty( $tb['alt_bg'] ) ) {
			$c = call_user_func( $san, $tb['alt_bg'] );
			if ( $c ) $css .= $scope . ' .snipi__row--alt{background:' . $c . ';}';
		}
		$t_scale = intval( $tb['font_scale'] );
		if ( $t_scale !== 100 ) {
			$tfs = $t_scale / 100;
			$css .= $scope . ' .snipi__table td,' . $scope . ' .snipi__table th{font-size:' . round( 0.95 * $tfs, 3 ) . 'rem;}';
		}
		$t_pad_top = intval( $tb['padding_top'] );
		$t_pad_h   = intval( $tb['padding_h'] );
		if ( $t_pad_top !== 6 || $t_pad_h !== 14 ) {
			$css .= $scope . ' .snipi__table td,' . $scope . ' .snipi__table th{padding-top:' . $t_pad_top . 'px;padding-bottom:' . $t_pad_top . 'px;padding-left:' . $t_pad_h . 'px;padding-right:' . $t_pad_h . 'px;}';
		}
		if ( isset( $tb['show_live'] ) && '0' === $tb['show_live'] ) {
			$css .= $scope . ' .snipi__live-indicator{display:none;}';
		}

		// ── D. SPODNJA VRSTICA ─────────────────────────────────────────
		$f_rules = '';
		if ( ! empty( $ft['background'] ) ) {
			$c = call_user_func( $san, $ft['background'] );
			if ( $c ) $f_rules .= 'background:' . $c . ';';
		}
		if ( ! empty( $ft['color'] ) ) {
			$c = call_user_func( $san, $ft['color'] );
			if ( $c ) $f_rules .= 'color:' . $c . ';';
		}
		$f_scale = intval( $ft['font_scale'] );
		if ( $f_scale !== 100 ) {
			$f_rules .= 'font-size:' . round( 0.9 * $f_scale / 100, 3 ) . 'rem;';
		}
		if ( ! empty( $ft['text_align'] ) && in_array( $ft['text_align'], array( 'left', 'center', 'right' ), true ) ) {
			$f_rules .= 'text-align:' . $ft['text_align'] . ';';
		}
		$f_pad_top = intval( $ft['padding_top'] );
		$f_pad_h   = intval( $ft['padding_h'] );
		if ( $f_pad_top !== 8 || $f_pad_h !== 16 ) {
			$f_rules .= 'padding-top:' . $f_pad_top . 'px;padding-left:' . $f_pad_h . 'px;padding-right:' . $f_pad_h . 'px;';
		}
		if ( $f_rules ) {
			$css .= $scope . ' .snipi__bottom-row{' . $f_rules . '}';
		}

		return $css;
	}

	/**
	 * Used for admin preview to render full table (with rows).
	 *
	 * @param array       $items   Normalized items array.
	 * @param array|mixed $style   Optional style payload (custom_css, styling_data).
	 * @param int         $post_id Post ID.
	 * @return string HTML string.
	 */
	public static function render_preview_fragment( $items, $style, $post_id ) {

		$logo_id        = get_post_meta( $post_id, '_snipi_logo_id', true );
		$logo_height    = get_post_meta( $post_id, '_snipi_logo_height', true ) ?: 60;
		$display_bottom = get_post_meta( $post_id, '_snipi_display_bottom', true );
		$bottom_row     = get_post_meta( $post_id, '_snipi_bottom_row', true );
		$show_program   = get_post_meta( $post_id, '_snipi_show_program_column', true ) === '1';

		$logo_url   = $logo_id ? wp_get_attachment_image_url( $logo_id, 'full' ) : '';
		$logo_style = 'height:' . intval( $logo_height ) . 'px;';
		if ( empty( $logo_url ) ) {
			$logo_style .= 'display:none;';
		}
		$logo_html = '<img src="' . esc_url( $logo_url ) . '" alt="Logo" class="snipi__logo" style="' . esc_attr( $logo_style ) . '" />';

		// Custom CSS iz stylea ali shranjenega
		$custom_css = '';
		if ( is_array( $style ) && isset( $style['custom_css'] ) ) {
			$custom_css = sanitize_textarea_field( $style['custom_css'] );
		} else {
			$custom_css = get_post_meta( $post_id, '_snipi_custom_css', true );
		}
		if ( ! is_string( $custom_css ) ) {
			$custom_css = '';
		}
		$custom_css = trim( $custom_css );

		// Generiran CSS iz styling GUI
		$styling_css = self::generate_styling_css( $post_id );

		ob_start();
		?>
		<div class="snipi snipi--preview snipi--screen-<?php echo intval( $post_id ); ?>">

			<div class="snipi__header">

				<div class="snipi__header-left">
					<?php echo $logo_html; // phpcs:ignore ?>
				</div>

				<div class="snipi__header-center">
					<span class="snipi__title snipi__title--large">Urnik izobraževanj</span>
					<div class="snipi__subheader">
						<span class="snipi__date">
							<?php
							$tz    = new DateTimeZone( 'Europe/Ljubljana' );
							$today = new DateTime( 'now', $tz );
							echo esc_html( $today->format( 'l, j. F Y' ) );
							?>
						</span>
					</div>
				</div>

				<div class="snipi__header-right">
					<span class="snipi__pagination">stran 1/1</span>
					<span class="snipi__clock-value">
						<?php echo esc_html( current_time( 'H:i:s' ) ); ?>
					</span>
				</div>

			</div>

			<?php if ( $styling_css ) : ?>
				<style class="snipi__styling-css"><?php echo esc_html( $styling_css ); ?></style>
			<?php endif; ?>
			<?php if ( $custom_css ) : ?>
				<style class="snipi__custom-css-preview"><?php echo esc_html( $custom_css ); ?></style>
			<?php endif; ?>

			<div class="snipi__table-wrapper">
				<table class="snipi__table">
					<thead>
						<tr>
							<th data-snipi-col="time">ČAS</th>
							<th data-snipi-col="name">IZOBRAŽEVANJE</th>
							<?php if ( $show_program ) : ?>
								<th data-snipi-col="program" data-snipi-program>PROGRAM</th>
							<?php endif; ?>
							<th data-snipi-col="teacher">PREDAVATELJ</th>
							<th data-snipi-col="room">UČILNICA</th>
							<th data-snipi-col="floor">NADSTROPJE</th>
						</tr>
					</thead>
					<tbody>
						<?php if ( empty( $items ) ) : ?>
							<tr>
								<td colspan="<?php echo $show_program ? '6' : '5'; ?>" style="text-align:center;padding:20px;">
									<?php esc_html_e( 'Danes ni predvidenih izobraževanj.', 'snipi-ekrani' ); ?>
								</td>
							</tr>
						<?php else : ?>
							<?php foreach ( $items as $it ) : ?>
								<?php
								$time = '';
								if ( isset( $it['start_iso'], $it['end_iso'] ) ) {
									try {
										$sd   = new DateTime( $it['start_iso'] );
										$ed   = new DateTime( $it['end_iso'] );
										$time = $sd->format( 'H:i' ) . ' – ' . $ed->format( 'H:i' );
									} catch ( Exception $e ) {
										$time = '';
									}
								}
								$program_value = '';
								if ( isset( $it['program_display'] ) && ! empty( $it['program_display'] ) ) {
									$program_value = $it['program_display'];
								} elseif ( isset( $it['project'] ) && ! empty( $it['project'] ) ) {
									$program_value = $it['project'];
								}
								?>
								<tr>
									<td><?php echo esc_html( $time ); ?></td>
									<td><?php echo esc_html( $it['name'] ?? '' ); ?></td>
									<?php if ( $show_program ) : ?>
										<td><?php echo esc_html( $program_value ); ?></td>
									<?php endif; ?>
									<td><?php echo esc_html( $it['teacher'] ?? '' ); ?></td>
									<td><?php echo esc_html( $it['room'] ?? '' ); ?></td>
									<td><?php echo esc_html( $it['floor'] ?? '' ); ?></td>
								</tr>
							<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
				</table>
			</div>

			<?php if ( $display_bottom === '1' && ! empty( $bottom_row ) ) : ?>
				<div class="snipi__bottom-row" style="position:static;width:auto;margin-top:10px;">
					<?php echo wp_kses_post( $bottom_row ); ?>
				</div>
			<?php endif; ?>

		</div>
		<?php
		return ob_get_clean();
	}
}
