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

ob_start();
?>
<div class="snipi snipi--shell">
<div class="snipi__header">
<div class="snipi__header-left snipi__date">
<!-- Datum se dinamično osveži v JS -->
</div>
<div class="snipi__header-center">
<span class="snipi__title">Urnik izobraževanj</span>
<span class="snipi__pagination">1/1</span>
<?php echo $logo_html; ?>
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
$display_bottom = get_post_meta( $post_id, '_snipi_display_bottom', true );
$bottom_row     = get_post_meta( $post_id, '_snipi_bottom_row', true );
$show_program   = get_post_meta( $post_id, '_snipi_show_program_column', true ) === '1';

$logo_url   = $logo_id ? wp_get_attachment_image_url( $logo_id, 'full' ) : '';
$logo_style = 'height:' . intval( $logo_height ) . 'px;';
if ( empty( $logo_url ) ) {
$logo_style .= 'display:none;';
}
$logo_html = '<img src="' . esc_url( $logo_url ) . '" alt="Logo" class="snipi__logo" style="' . esc_attr( $logo_style ) . '" />';

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

ob_start();
?>
<div class="snipi snipi--preview">
<div class="snipi__header">
<div class="snipi__header-left">
<?php
$tz    = new DateTimeZone( 'Europe/Ljubljana' );
$today = new DateTime( 'now', $tz );
echo esc_html( $today->format( 'l, j. F Y' ) );
?>
</div>
<div class="snipi__header-center">
<span class="snipi__title">Urnik izobraževanj</span>
<span class="snipi__pagination">1/1</span>
<?php echo $logo_html; ?>
</div>
<div class="snipi__header-right">
<?php echo esc_html( current_time( 'H:i:s' ) ); ?>
</div>
</div>

<?php if ( ! empty( $custom_css ) ) : ?>
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
$sd = new DateTime( $it['start_iso'] );
$ed = new DateTime( $it['end_iso'] );
$time = $sd->format( 'H:i' ) . ' - ' . $ed->format( 'H:i' );
} catch ( Exception $e ) {
$time = '';
}
}
?>
<tr>
<td><?php echo esc_html( $time ); ?></td>
<td><?php echo esc_html( isset( $it['name'] ) ? $it['name'] : '' ); ?></td>
<?php
$program_value = '';
if ( isset( $it['program_display'] ) && ! empty( $it['program_display'] ) ) {
$program_value = $it['program_display'];
} elseif ( isset( $it['project'] ) && ! empty( $it['project'] ) ) {
$program_value = $it['project'];
} elseif ( isset( $it['subjects'] ) && is_array( $it['subjects'] ) ) {
foreach ( $it['subjects'] as $subject ) {
if ( ! empty( $subject['studyName'] ) ) {
$program_value = $subject['studyName'];
break;
}
}
}
if ( $show_program ) :
?>
<td><?php echo esc_html( $program_value ); ?></td>
<?php endif; ?>
<td><?php echo esc_html( isset( $it['teacher'] ) ? $it['teacher'] : '' ); ?></td>
<td><?php echo esc_html( isset( $it['room'] ) ? $it['room'] : '' ); ?></td>
<td><?php echo esc_html( isset( $it['floor'] ) ? $it['floor'] : '' ); ?></td>
</tr>
<?php endforeach; ?>
<?php endif; ?>
</tbody>
</table>
</div>

<?php if ( $display_bottom === '1' && ! empty( $bottom_row ) ) : ?>
<div class="snipi__bottom-row">
<?php echo wp_kses_post( $bottom_row ); ?>
</div>
<?php endif; ?>
</div>
<?php
return ob_get_clean();
}
}
