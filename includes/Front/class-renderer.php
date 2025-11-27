<?php
if ( ! defined( 'ABSPATH' ) ) {
return;
}

/**
 * SNIPI_Renderer
 * - Renders front-end shell
 * - Used also by REST preview to render small HTML fragment
 */
class SNIPI_Renderer {

/**
 * Renders full front-end shell (header + empty table + bottom row container).
 *
 * @param int $post_id Post ID.
 * @return string
 */
public static function render_shell( $post_id ) {
$logo_id        = get_post_meta( $post_id, '_snipi_logo_id', true );
$logo_height    = get_post_meta( $post_id, '_snipi_logo_height', true ) ?: 60;
$display_bottom = get_post_meta( $post_id, '_snipi_display_bottom_row', true );
$bottom_row     = get_post_meta( $post_id, '_snipi_bottom_row', true );
$show_program   = get_post_meta( $post_id, '_snipi_show_program', true ) === '1';

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

ob_start();
?>
<div class="snipi snipi--shell">
<div class="snipi__header">
<div class="snipi__header-left">
<?php echo $logo_html; ?>
</div>
<div class="snipi__header-center">
<span class="snipi__title snipi__title--large">Urnik izobraževanj</span>
<div class="snipi__subheader">
<span class="snipi__date"><!-- Datum se dinamično osveži v JS --></span>
<span class="snipi__pagination">1/1</span>
</div>
</div>
<div class="snipi__header-right snipi__clock">
<!-- Ura se osveži v JS -->
</div>
</div>

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
<?php esc_html_e( 'Nalagam podatke ...', 'snipi-ekrani' ); ?>
</td>
</tr>
</tbody>
</table>
</div>

<div class="<?php echo esc_attr( implode( ' ', $bottom_classes ) ); ?>" data-snipi-bottom-row>
<?php echo $bottom_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
</div>
</div>
<?php
return ob_get_clean();
}

/**
 * Used for admin preview to render full table (with rows).
 *
 * @param array       $items   Normalized items array.
 * @param array|mixed $style   Optional style payload (custom CSS).
 * @param int         $post_id Post ID.
 * @return string
 */
public static function render_preview_fragment( $items, $style, $post_id ) {
$logo_id        = get_post_meta( $post_id, '_snipi_logo_id', true );
$logo_height    = get_post_meta( $post_id, '_snipi_logo_height', true ) ?: 60;
$display_bottom = get_post_meta( $post_id, '_snipi_display_bottom_row', true );
$bottom_row     = get_post_meta( $post_id, '_snipi_bottom_row', true );
$show_program   = get_post_meta( $post_id, '_snipi_show_program', true ) === '1';

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

ob_start();
?>
<div class="snipi snipi--shell">
<div class="snipi__header">
<div class="snipi__header-left">
<?php echo $logo_html; ?>
</div>
<div class="snipi__header-center">
<span class="snipi__title snipi__title--large">Urnik izobraževanj</span>
<div class="snipi__subheader">
<span class="snipi__date"><!-- Datum se dinamično osveži v JS --></span>
<span class="snipi__pagination">1/1</span>
</div>
</div>
<div class="snipi__header-right snipi__clock">
<!-- Ura se osveži v JS -->
</div>
</div>

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
<?php
if ( empty( $items ) ) :
?>
<tr>
<td colspan="<?php echo $show_program ? '6' : '5'; ?>" style="text-align:center;padding:20px;">
<?php esc_html_e( 'Ni podatkov za izbrani dan.', 'snipi-ekrani' ); ?>
</td>
</tr>
<?php
else :
foreach ( $items as $it ) :
$start_iso = isset( $it['start_iso'] ) ? $it['start_iso'] : '';
$end_iso   = isset( $it['end_iso'] ) ? $it['end_iso'] : '';
$name      = isset( $it['name'] ) ? $it['name'] : '';
$program   = isset( $it['program'] ) ? $it['program'] : '';
$teacher   = isset( $it['teacher'] ) ? $it['teacher'] : '';
$room      = isset( $it['room'] ) ? $it['room'] : '';
$floor     = isset( $it['floor'] ) ? $it['floor'] : '';
?>
<tr>
<td>
<span class="snipi__day-label">
<?php echo esc_html( date_i18n( 'D, d.m.', strtotime( $start_iso ) ) ); ?>
</span>
<span class="snipi__time">
<?php
$start_time = date_i18n( 'H:i', strtotime( $start_iso ) );
$end_time   = date_i18n( 'H:i', strtotime( $end_iso ) );
echo esc_html( $start_time . ' - ' . $end_time );
?>
</span>
</td>
<td><?php echo esc_html( $name ); ?></td>
<?php if ( $show_program ) : ?>
<td><?php echo esc_html( $program ); ?></td>
<?php endif; ?>
<td><?php echo esc_html( $teacher ); ?></td>
<td><?php echo esc_html( $room ); ?></td>
<td><?php echo esc_html( $floor ); ?></td>
</tr>
<?php
endforeach;
endif;
?>
</tbody>
</table>
</div>

<div class="<?php echo esc_attr( implode( ' ', $bottom_classes ) ); ?>" data-snipi-bottom-row>
<?php echo $bottom_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
</div>
</div>
<?php
return ob_get_clean();
}
}
