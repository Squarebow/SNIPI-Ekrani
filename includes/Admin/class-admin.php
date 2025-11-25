<?php
if ( ! defined( 'ABSPATH' ) ) {
exit;
}

/**
 * SNIPI_Admin
 * - Custom admin pages (Nastavitve, Oblikovanje, Navodila)
 * - WYSIWYG za spodnjo vrstico, CSS editor v Oblikovanju
 * - Premik logotipa v Oblikovanje
 */
class SNIPI_Admin {

public static function init() {
add_action( 'init', array( __CLASS__, 'register_cpt' ) );
add_action( 'admin_menu', array( __CLASS__, 'register_pages' ) );
add_action( 'admin_init', array( __CLASS__, 'maybe_redirect_legacy_edit' ) );
add_filter( 'get_edit_post_link', array( __CLASS__, 'filter_edit_link' ), 10, 3 );
add_filter( 'redirect_post_location', array( __CLASS__, 'redirect_after_save' ), 10, 2 );
add_action( 'admin_post_snipi_save_settings', array( __CLASS__, 'handle_custom_save' ) );
add_action( 'admin_post_snipi_save_styling', array( __CLASS__, 'handle_custom_save' ) );
add_action( 'admin_post_snipi_save_navodila', array( __CLASS__, 'handle_custom_save' ) );
add_action( 'save_post', array( __CLASS__, 'save_meta' ) );
add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_assets' ) );
add_filter( 'manage_ekran_posts_columns', array( __CLASS__, 'add_custom_columns' ) );
add_action( 'manage_ekran_posts_custom_column', array( __CLASS__, 'render_custom_columns' ), 10, 2 );
}

public static function register_cpt() {
$labels = array(
'name'               => 'SNIPI ekrani',
'singular_name'      => 'Ekran',
'menu_name'          => 'SNIPI ekrani',
'name_admin_bar'     => 'Ekran',
'add_new'            => 'Dodaj ekran',
'add_new_item'       => 'Dodaj ekran',
'edit_item'          => 'Uredi ekran',
'new_item'           => 'Nov ekran',
'view_item'          => 'Poglej ekran',
'search_items'       => 'Išči ekrane',
'not_found'          => 'Ni najdenih ekranov',
'not_found_in_trash' => 'Ni ekranov v smeteh',
'all_items'          => 'Vsi ekrani',
);

$args = array(
'labels'        => $labels,
'public'        => false,
'show_ui'       => true,
'show_in_menu'  => true,
'menu_position' => 25,
'menu_icon'     => 'dashicons-screenoptions',
'supports'      => array( 'title' ),
'has_archive'   => false,
'rewrite'       => false,
'show_in_rest'  => false,
);

register_post_type( 'ekran', $args );
}

public static function register_pages() {
add_submenu_page(
'edit.php?post_type=ekran',
'Nastavitve',
'Nastavitve',
'edit_posts',
'snipi-nastavitve',
array( __CLASS__, 'render_settings_page' )
);

add_submenu_page(
'edit.php?post_type=ekran',
'Oblikovanje',
'Oblikovanje',
'edit_posts',
'snipi-oblikovanje',
array( __CLASS__, 'render_styling_page' )
);

add_submenu_page(
'edit.php?post_type=ekran',
'Navodila',
'Navodila',
'edit_posts',
'snipi-navodila',
array( __CLASS__, 'render_navodila_page' )
);
}

public static function maybe_redirect_legacy_edit() {
if ( ! is_admin() ) {
return;
}

if ( isset( $_GET['post'], $_GET['action'] ) && 'edit' === $_GET['action'] ) {
$post_id = absint( $_GET['post'] );
$post    = get_post( $post_id );
if ( $post && 'ekran' === $post->post_type && ! isset( $_GET['page'] ) ) {
wp_safe_redirect( self::get_page_url( 'snipi-nastavitve', $post_id ) );
wp_safe_redirect( admin_url( 'admin.php?page=snipi-nastavitve&post=' . $post_id ) );
exit;
}
}
}

public static function filter_edit_link( $link, $post_id, $context ) {
$post = get_post( $post_id );
if ( $post && 'ekran' === $post->post_type ) {
return self::get_page_url( 'snipi-nastavitve', $post_id );
return admin_url( 'admin.php?page=snipi-nastavitve&post=' . $post_id );
}
return $link;
}

public static function redirect_after_save( $location, $post_id ) {
$post = get_post( $post_id );
if ( $post && 'ekran' === $post->post_type ) {
$section  = isset( $_POST['snipi_section'] ) ? sanitize_key( $_POST['snipi_section'] ) : 'snipi-nastavitve';
$location = add_query_arg( 'updated', 1, self::get_page_url( $section, $post_id ) );
$location = admin_url( 'admin.php?page=' . $section . '&post=' . $post_id . '&updated=1' );
}
return $location;
}

public static function add_custom_columns( $columns ) {
$new = array();
foreach ( $columns as $key => $label ) {
if ( 'title' === $key ) {
$new['shortcode'] = 'Kratka koda';
$new['api_key']   = 'API ključ';
}
$new[ $key ] = $label;
}
$new['author'] = __( 'Avtor', 'snipi-ekrani' );
$new['date']   = __( 'Datum', 'snipi-ekrani' );
return $new;
}

public static function render_custom_columns( $column, $post_id ) {
switch ( $column ) {
case 'shortcode':
$shortcode = '[snipi_ekran id="' . intval( $post_id ) . '"]';
echo '<code>' . esc_html( $shortcode ) . '</code> ';
echo '<button type="button" class="snipi-copy-list" data-snipi-copy="' . esc_attr( $shortcode ) . '" title="Kopiraj">';
echo '<img src="' . esc_url( SNIPI_EKRANI_URL . 'assets/Copy_icon_256px.svg' ) . '" alt="Kopiraj" class="snipi-copy-icon" />';
echo '</button>';
break;
case 'api_key':
$k = get_post_meta( $post_id, '_snipi_api_key', true );
echo esc_html( $k );
break;
case 'author':
$author_id = get_post_field( 'post_author', $post_id );
echo esc_html( get_the_author_meta( 'display_name', $author_id ) );
break;
case 'date':
echo esc_html( get_the_date( '', $post_id ) );
break;
}
}

public static function admin_assets( $hook ) {
global $post_type, $pagenow;

if ( isset( $_GET['action'] ) && 'upload-plugin' === $_GET['action'] ) {
return;
}

$screen_page   = isset( $_GET['page'] ) ? sanitize_key( $_GET['page'] ) : '';
$is_snipi_page = in_array( $screen_page, array( 'snipi-nastavitve', 'snipi-oblikovanje', 'snipi-navodila' ), true );

if ( $post_type === 'ekran' || ( $pagenow === 'post-new.php' && isset( $_GET['post_type'] ) && 'ekran' === $_GET['post_type'] ) || $is_snipi_page ) {
wp_enqueue_media();
wp_enqueue_style( 'snipi-admin-css', SNIPI_EKRANI_URL . 'assets/css/admin.css', array(), snipi_ekrani_asset_version( 'assets/css/admin.css' ) );
wp_enqueue_style( 'snipi-admin-styling-css', SNIPI_EKRANI_URL . 'assets/css/admin-styling.css', array(), snipi_ekrani_asset_version( 'assets/css/admin-styling.css' ) );
wp_enqueue_script( 'snipi-admin-js', SNIPI_EKRANI_URL . 'assets/js/admin.js', array(), snipi_ekrani_asset_version( 'assets/js/admin.js' ), true );
wp_enqueue_script( 'snipi-admin-styling-js', SNIPI_EKRANI_URL . 'assets/js/admin-styling.js', array(), snipi_ekrani_asset_version( 'assets/js/admin-styling.js' ), true );

$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;
wp_localize_script(
'snipi-admin-js',
'SNIPI_ADMIN',
array(
'preview_nonce' => wp_create_nonce( 'snipi_preview_nonce' ),
'rest_url'      => esc_url_raw( rest_url( 'snipi/v1/ekrani/preview' ) ),
'post_id'       => $post_id,
)
);
}
}

public static function render_settings_page() {
$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;
$post    = $post_id ? get_post( $post_id ) : null;

if ( ! $post || 'ekran' !== $post->post_type ) {
self::render_missing_post_message();
return;
}

$data          = self::get_meta_data( $post_id );
$shortcode_str = '[snipi_ekran id="' . intval( $post_id ) . '"]';

$info_rows   = array();
$info_rows[] = 'Število danes prikazanih dogodkov na ekranu: ' . ( null !== $data['today_count'] ? intval( $data['today_count'] ) : 0 );
$info_rows[] = 'Vikend način: ' . ( $data['weekend_mode'] ? 'Vključen' : 'Izključen' );
$info_rows[] = 'Spodnja vrstica: ' . ( $data['display_bottom'] ? 'Prikazana' : 'Ni prikazana' );
$info_rows[] = 'Dodatni stolpec: ' . ( $data['show_program_column'] ? 'Program' : 'Ni prikazan' );

$tooltip_icon = '<span class="snipi-info-icon" tabindex="0" aria-label="Informacija">' .
'<svg class="snipi-info-icon__svg" width="16" height="16" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" role="img" aria-hidden="true">' .
'<circle cx="12" cy="12" r="11" fill="#000" />' .
'<path d="M12 6.5a1 1 0 110 2 1 1 0 010-2zm1 11h-2v-7h2v7z" fill="#fff" />' .
'</svg>' .
'<span class="snipi-info-icon__tooltip">Dodaj opis orodja tukaj.</span>' .
'</span>';

echo '<div class="wrap snipi-admin-page">';
echo '<h1 class="wp-heading-inline">' . esc_html( $post->post_title ? $post->post_title : 'Ekran' ) . '</h1>';
self::render_page_nav( $post_id, 'snipi-nastavitve' );

echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" class="snipi-admin-form">';
wp_nonce_field( 'snipi_ekran_save_meta', 'snipi_ekran_nonce' );
echo '<input type="hidden" name="action" value="snipi_save_settings" />';
echo '<input type="hidden" name="snipi_section" value="snipi-nastavitve" />';
echo '<input type="hidden" name="post_id" value="' . intval( $post_id ) . '" />';

echo '<div class="snipi-admin-row snipi-admin-row--full">';
echo '<div class="snipi-admin-col">';
echo '<label class="snipi-admin-label" for="snipi_post_title">Ime ekrana ' . $tooltip_icon . '</label>';
echo '<input type="text" id="snipi_post_title" name="snipi_post_title" class="snipi-admin-input" value="' . esc_attr( $post->post_title ) . '" />';
echo '<p class="description">Poimenuj ekran za lažje prepoznavanje.</p>';
echo '</div>';
echo '</div>';

echo '<div class="snipi-admin-row snipi-admin-row--quarters">';
echo '<div class="snipi-admin-col">';
echo '<label class="snipi-admin-label">API ključ ' . $tooltip_icon . '</label>';
echo '<input type="text" name="snipi_api_key" value="' . esc_attr( $data['api_key'] ) . '" class="snipi-admin-input" placeholder="npr. BdhBcrRm8" />';
echo '<p class="description">Vnesi zadnji del URL-ja (key) za tvoj ekran.</p>';
echo '</div>';

echo '<div class="snipi-admin-col">';
echo '<label class="snipi-admin-label">Vrstic na stran ' . $tooltip_icon . '</label>';
echo '<input type="number" name="snipi_rows_per_page" min="1" value="' . esc_attr( $data['rows_per_page'] ) . '" class="snipi-admin-input" />';
echo '<p class="description">Koliko vrstic naj se prikaže na eni strani tabele.</p>';
echo '</div>';

echo '<div class="snipi-admin-col snipi-admin-col--shortcode">';
echo '<label class="snipi-admin-label">Kratka koda ' . $tooltip_icon . '</label>';
echo '<div class="snipi-shortcode-inline">';
echo '<input readonly id="snipi_shortcode_field" value="' . esc_attr( $shortcode_str ) . '" class="snipi-admin-input snipi-admin-input--readonly" />';
echo '<button type="button" class="snipi-copy-button" id="snipi_copy_shortcode" title="Kopiraj" aria-label="Kopiraj kratko kodo">';
echo '<img src="' . esc_url( SNIPI_EKRANI_URL . 'assets/Copy_icon_256px.svg' ) . '" alt="Kopiraj" class="snipi-copy-icon" />';
echo '</button>';
echo '</div>';
echo '<p class="description">Uporabi kratko kodo kjer koli v urejevalniku.</p>';
echo '</div>';

echo '<div class="snipi-admin-col">';
echo '<label class="snipi-admin-label">Informacije o prikazu ' . $tooltip_icon . '</label>';
echo '<div class="snipi-admin-info-box">';
foreach ( $info_rows as $row ) {
echo '<p class="snipi-admin-info-box__text"><span class="snipi-admin-info-box__value">' . esc_html( $row ) . '</span></p>';
}
echo '</div>';
echo '</div>';
echo '</div>';

echo '<div class="snipi-admin-row snipi-admin-row--thirds">';
echo '<div class="snipi-admin-col">';
echo '<label class="snipi-admin-label">Prikaz dogodkov ' . $tooltip_icon . '</label>';
echo '<select name="snipi_future_days" class="snipi-admin-input">';
for ( $i = 0; $i <= 3; $i++ ) {
if ( 0 === $i ) {
$label = 'Samo danes';
} elseif ( 1 === $i ) {
$label = 'Danes + 1 dan';
} elseif ( 2 === $i ) {
$label = 'Danes + 2 dneva';
} else {
$label = 'Danes + ' . $i . ' dni';
}
echo '<option value="' . esc_attr( $i ) . '" ' . selected( $data['future_days'], $i, false ) . '>' . esc_html( $label ) . '</option>';
}
echo '</select>';
echo '<p class="description">Dogodki se vedno prikazujejo za današnji dan (00:00–23:59:59). Dodaj prikaz do treh prihodnjih dni.</p>';
echo '</div>';

echo '<div class="snipi-admin-col">';
echo '<label class="snipi-admin-label">Autoplay interval (s) ' . $tooltip_icon . '</label>';
echo '<input type="number" name="snipi_autoplay_interval" min="1" value="' . esc_attr( $data['autoplay_interval'] ) . '" class="snipi-admin-input" />';
echo '<p class="description">Koliko sekund naj bo prikaz vsake strani (avtomatsko menjavanje).</p>';
echo '</div>';

echo '<div class="snipi-admin-col">';
echo '<label class="snipi-admin-label">Prikaži spodnjo vrstico ' . $tooltip_icon . '</label>';
echo '<p class="snipi-admin-status">Front-end prikaz: <strong>' . ( $data['display_bottom'] ? 'Prikazana' : 'Ni prikazana' ) . '</strong></p>';
echo '<p class="description">Stanje se posodobi po shranjevanju nastavitev.</p>';
echo '</div>';
echo '</div>';

echo '<div class="snipi-admin-row snipi-admin-row--thirds">';
echo '<div class="snipi-admin-col">';
echo '<label class="snipi-admin-label">Vikend način ' . $tooltip_icon . '</label>';
echo '<label class="snipi-admin-checkbox"><input type="checkbox" name="snipi_weekend_mode" value="1" ' . checked( $data['weekend_mode'], '1', false ) . ' /> <span>Vključi prikaz dogodkov v vikend načinu.</span></label>';
echo '</div>';
echo '<div class="snipi-admin-col">';
echo '<label class="snipi-admin-label">Prikaz dodatnega stolpca v tabeli ' . $tooltip_icon . '</label>';
echo '<label class="snipi-admin-checkbox"><input type="checkbox" name="snipi_show_program_column" value="1" ' . checked( $data['show_program_column'], '1', false ) . ' /> <span>Dodaj stolpec PROGRAM med IZOBRAŽEVANJE in UČITELJ.</span></label>';
echo '</div>';
echo '<div class="snipi-admin-col">';
echo '<label class="snipi-admin-label">Prikaži spodnjo vrstico ' . $tooltip_icon . '</label>';
echo '<label class="snipi-admin-checkbox"><input type="checkbox" name="snipi_display_bottom" value="1" ' . checked( $data['display_bottom'], '1', false ) . ' /> <span>Prikaži dodatno vrstico na dnu tabele.</span></label>';
echo '</div>';
echo '</div>';

echo '<div class="snipi-admin-row snipi-admin-row--full">';
echo '<div class="snipi-admin-col">';
echo '<label class="snipi-admin-label">V spodnjo vrstico lahko vnesete poljubno vsebino (npr. legendo kratic predmetov in programov). Podpira HTML. ' . $tooltip_icon . '</label>';
$editor_settings = array( 'textarea_name' => 'snipi_bottom_row', 'media_buttons' => false, 'textarea_rows' => 4 );
wp_editor( wp_kses_post( $data['bottom_row'] ), 'snipi_bottom_row_editor', $editor_settings );
echo '</div>';
echo '</div>';

echo '<p class="submit"><button type="submit" class="button button-primary">Shrani nastavitve</button></p>';
echo '</form>';
echo '</div>';
}

public static function render_styling_page() {
$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;
$post    = $post_id ? get_post( $post_id ) : null;

if ( ! $post || 'ekran' !== $post->post_type ) {
self::render_missing_post_message();
return;
}

$data = self::get_meta_data( $post_id );

echo '<div class="wrap snipi-admin-page">';
echo '<h1 class="wp-heading-inline">' . esc_html( $post->post_title ? $post->post_title : 'Ekran' ) . '</h1>';
self::render_page_nav( $post_id, 'snipi-oblikovanje' );

echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" class="snipi-admin-form">';
wp_nonce_field( 'snipi_ekran_save_meta', 'snipi_ekran_nonce' );
echo '<input type="hidden" name="action" value="snipi_save_styling" />';
echo '<input type="hidden" name="snipi_section" value="snipi-oblikovanje" />';
echo '<input type="hidden" name="post_id" value="' . intval( $post_id ) . '" />';

echo '<div class="snipi-admin-row snipi-admin-row--logo">';
echo '<div class="snipi-admin-col">';
echo '<label class="snipi-admin-label">Prikaz logotipa</label>';
echo '<p class="description">Dodaj logotip v formatu PNG ali SVG ter prilagodi njegov prikaz.</p>';
echo '<div class="snipi-logo-actions">';
echo '<button type="button" class="button" id="snipi_logo_upload">' . ( $data['logo_id'] ? 'Spremeni logo' : 'Naloži logo' ) . '</button>';
echo '<button type="button" class="button" id="snipi_logo_remove">Odstrani logo</button>';
echo '</div>';
echo '<input type="hidden" id="snipi_logo_id" name="snipi_logo_id" value="' . esc_attr( $data['logo_id'] ) . '" />';
echo '<div class="snipi-logo-slider">';
echo '<label class="snipi-admin-label">Višina logotipa (px)</label>';
echo '<input type="range" id="snipi_logo_height" name="snipi_logo_height" min="40" max="120" value="' . esc_attr( $data['logo_height'] ) . '" />';
echo '<span id="snipi_logo_height_value" class="snipi-logo-slider__value">' . esc_html( $data['logo_height'] ) . 'px</span>';
echo '</div>';
echo '</div>';
echo '<div class="snipi-admin-col">';
echo '<label class="snipi-admin-label">Predogled logotipa</label>';
echo '<div id="snipi_logo_preview" class="snipi-logo-preview">';
if ( $data['logo_id'] ) {
echo wp_get_attachment_image( $data['logo_id'], 'medium', false, array( 'style' => 'height: ' . intval( $data['logo_height'] ) . 'px; width: auto;' ) );
}
echo '</div>';
echo '</div>';
echo '</div>';

echo '<div class="snipi-admin-row">';
echo '<div class="snipi-admin-col">';
echo '<p class="description">Uredi CSS spodaj (priporočen format: čisti CSS). Predogled se prikaže v realnem času.</p>';
echo '<textarea id="snipi_css_editor" name="snipi_custom_css" style="width:100%;min-height:240px;">' . esc_textarea( $data['custom_css'] ) . '</textarea>';
echo '<div style="margin-top:8px;">';
echo '<button type="button" class="button" id="snipi_preview_css">Osveži predogled</button>';
echo '</div>';
echo '<div id="snipi-styling-preview" style="border:1px solid #ddd;padding:10px;background:#fff;min-height:200px;margin-top:12px;"></div>';
echo '</div>';
echo '</div>';

echo '<p class="submit"><button type="submit" class="button button-primary">Shrani oblikovanje</button></p>';
echo '</form>';
echo '</div>';
}

public static function render_navodila_page() {
$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;
$post    = $post_id ? get_post( $post_id ) : null;

if ( ! $post || 'ekran' !== $post->post_type ) {
self::render_missing_post_message();
return;
}

echo '<div class="wrap snipi-admin-page">';
echo '<h1 class="wp-heading-inline">' . esc_html( $post->post_title ? $post->post_title : 'Ekran' ) . '</h1>';
self::render_page_nav( $post_id, 'snipi-navodila' );

echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" class="snipi-admin-form">';
wp_nonce_field( 'snipi_ekran_save_meta', 'snipi_ekran_nonce' );
echo '<input type="hidden" name="action" value="snipi_save_navodila" />';
echo '<input type="hidden" name="snipi_section" value="snipi-navodila" />';
echo '<input type="hidden" name="post_id" value="' . intval( $post_id ) . '" />';

echo '<div class="snipi-admin-row">';
echo '<div class="snipi-admin-col">';
echo '<h4># Namestitev</h4>';
echo '<ul><li>Deaktiviraj prejšnjo verzijo, naloži novo.</li><li>Vnesi API ključ in shrani.</li></ul>';
echo '<h4># Shortcode</h4>';
echo '<p>Uporabi: <code>[snipi_ekran id="' . intval( $post_id ) . '"]</code></p>';
echo '<h4># Oblikovanje tabele</h4>';
echo '<p>Vse prilagoditve videza in logotipa uredi na strani Oblikovanje.</p>';
echo '<h4># CSS classes</h4>';
echo '<p>Glavne CSS razrede lahko prilagodiš preko polja CSS na strani Oblikovanje.</p>';
echo '</div>';
echo '</div>';

echo '<p class="submit"><button type="submit" class="button button-primary">Shrani spremembe</button></p>';
echo '</form>';
echo '</div>';
}

public static function save_meta( $post_id ) {
if ( ! isset( $_POST['snipi_ekran_nonce'] ) ) {
return;
}
if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['snipi_ekran_nonce'] ) ), 'snipi_ekran_save_meta' ) ) {
return;
}
if ( wp_is_post_revision( $post_id ) ) {
return;
}

self::persist_meta_from_request( $post_id );
}

public static function handle_custom_save() {
$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
if ( ! $post_id || ! current_user_can( 'edit_post', $post_id ) ) {
wp_die( esc_html__( 'Nimate pravic za shranjevanje.', 'snipi-ekrani' ) );
}

if ( ! isset( $_POST['snipi_ekran_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['snipi_ekran_nonce'] ) ), 'snipi_ekran_save_meta' ) ) {
wp_die( esc_html__( 'Neveljaven varnostni žeton.', 'snipi-ekrani' ) );
}

self::persist_meta_from_request( $post_id );

if ( isset( $_POST['snipi_post_title'] ) ) {
wp_update_post( array(
'ID'         => $post_id,
'post_title' => sanitize_text_field( wp_unslash( $_POST['snipi_post_title'] ) ),
) );
}

$section = isset( $_POST['snipi_section'] ) ? sanitize_key( $_POST['snipi_section'] ) : 'snipi-nastavitve';
wp_safe_redirect( add_query_arg( 'updated', 1, self::get_page_url( $section, $post_id ) ) );
wp_safe_redirect( admin_url( 'admin.php?page=' . $section . '&post=' . $post_id . '&updated=1' ) );
exit;
}

protected static function persist_meta_from_request( $post_id ) {
if ( isset( $_POST['snipi_api_key'] ) ) {
update_post_meta( $post_id, '_snipi_api_key', sanitize_text_field( wp_unslash( $_POST['snipi_api_key'] ) ) );
}
if ( isset( $_POST['snipi_rows_per_page'] ) ) {
update_post_meta( $post_id, '_snipi_rows_per_page', intval( $_POST['snipi_rows_per_page'] ) );
}
if ( isset( $_POST['snipi_autoplay_interval'] ) ) {
update_post_meta( $post_id, '_snipi_autoplay_interval', intval( $_POST['snipi_autoplay_interval'] ) );
}
$future_days = isset( $_POST['snipi_future_days'] ) ? intval( $_POST['snipi_future_days'] ) : 0;
$future_days = max( 0, min( 3, $future_days ) );
update_post_meta( $post_id, '_snipi_future_days', $future_days );

$weekend_mode = isset( $_POST['snipi_weekend_mode'] ) ? '1' : '0';
update_post_meta( $post_id, '_snipi_weekend_mode', $weekend_mode );

$show_program = isset( $_POST['snipi_show_program_column'] ) ? '1' : '0';
update_post_meta( $post_id, '_snipi_show_program_column', $show_program );

if ( isset( $_POST['snipi_logo_id'] ) ) {
update_post_meta( $post_id, '_snipi_logo_id', intval( $_POST['snipi_logo_id'] ) );
}

$display_bottom = isset( $_POST['snipi_display_bottom'] ) ? '1' : '0';
update_post_meta( $post_id, '_snipi_display_bottom', $display_bottom );

if ( isset( $_POST['snipi_bottom_row'] ) ) {
update_post_meta( $post_id, '_snipi_bottom_row', wp_kses_post( wp_unslash( $_POST['snipi_bottom_row'] ) ) );
}

if ( isset( $_POST['snipi_logo_height'] ) ) {
$logo_height = max( 40, min( 120, intval( $_POST['snipi_logo_height'] ) ) );
update_post_meta( $post_id, '_snipi_logo_height', $logo_height );
}

if ( isset( $_POST['snipi_custom_css'] ) ) {
update_post_meta( $post_id, '_snipi_custom_css', sanitize_textarea_field( wp_unslash( $_POST['snipi_custom_css'] ) ) );
}
}

protected static function get_meta_data( $post_id ) {
$api_key           = get_post_meta( $post_id, '_snipi_api_key', true );
$rows_per_page     = get_post_meta( $post_id, '_snipi_rows_per_page', true ) ?: 8;
$autoplay_interval = get_post_meta( $post_id, '_snipi_autoplay_interval', true ) ?: 10;
$future_days       = intval( get_post_meta( $post_id, '_snipi_future_days', true ) );
$future_days       = max( 0, min( 3, $future_days ) );
$logo_id           = get_post_meta( $post_id, '_snipi_logo_id', true );
$logo_height       = max( 40, min( 120, get_post_meta( $post_id, '_snipi_logo_height', true ) ?: 60 ) );
$display_bottom    = get_post_meta( $post_id, '_snipi_display_bottom', true );
$bottom_row        = get_post_meta( $post_id, '_snipi_bottom_row', true );
$custom_css        = get_post_meta( $post_id, '_snipi_custom_css', true );
$weekend_mode      = get_post_meta( $post_id, '_snipi_weekend_mode', true );
$show_program      = get_post_meta( $post_id, '_snipi_show_program_column', true );

$today_events_count = null;
if ( ! empty( $api_key ) ) {
$tz          = new DateTimeZone( 'Europe/Ljubljana' );
$today       = new DateTime( 'now', $tz );
$current_day = $today->format( 'Y-m-d' );
$events_all  = SNIPI_Data_Service::get_timeslots( $api_key, $current_day, $current_day, true );

if ( ! is_wp_error( $events_all ) ) {
$today_events_count = count( $events_all );
}
}

return array(
'api_key'             => $api_key,
'rows_per_page'       => $rows_per_page,
'autoplay_interval'   => $autoplay_interval,
'future_days'         => $future_days,
'logo_id'             => $logo_id,
'logo_height'         => $logo_height,
'display_bottom'      => $display_bottom,
'bottom_row'          => $bottom_row,
'custom_css'          => is_string( $custom_css ) ? $custom_css : '',
'today_count'         => $today_events_count,
'weekend_mode'        => $weekend_mode,
'show_program_column' => $show_program,
);
}

private static function get_page_url( $slug, $post_id = 0 ) {
$base_url = add_query_arg(
array(
'post_type' => 'ekran',
'page'      => $slug,
),
admin_url( 'edit.php' )
);

if ( $post_id ) {
$base_url = add_query_arg( 'post', intval( $post_id ), $base_url );
}

return $base_url;
}

protected static function render_page_nav( $post_id, $active ) {
$pages = array(
'snipi-nastavitve'  => 'Nastavitve',
'snipi-oblikovanje' => 'Oblikovanje',
'snipi-navodila'    => 'Navodila',
);

echo '<div class="snipi-tabs">';
foreach ( $pages as $slug => $label ) {
$classes = 'button button-secondary snipi-tab-btn';
if ( $active === $slug ) {
$classes .= ' button-primary';
}
echo '<a class="' . esc_attr( $classes ) . '" href="' . esc_url( self::get_page_url( $slug, $post_id ) ) . '">' . esc_html( $label ) . '</a>';
echo '<a class="' . esc_attr( $classes ) . '" href="' . esc_url( admin_url( 'admin.php?page=' . $slug . '&post=' . intval( $post_id ) ) ) . '">' . esc_html( $label ) . '</a>';
}
echo '</div>';
}

protected static function render_missing_post_message() {
echo '<div class="wrap"><h1>SNIPI ekrani</h1><p>Izberite ekran na zaslonu <a href="' . esc_url( admin_url( 'edit.php?post_type=ekran' ) ) . '">Vsi ekrani</a> in kliknite Uredi.</p></div>';
}
}

SNIPI_Admin::init();
