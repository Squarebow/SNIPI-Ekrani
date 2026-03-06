<?php
/**
 * SNIPI Admin Edit Screen
 *
 * Glavni edit screen za urejanje posameznega ekrana.
 * Uporablja tab navigacijo (Nastavitve | Oblikovanje) in 60:40 layout.
 *
 * Layout:
 *  - Levo  (60%): Nastavitve / oblikovanje polja
 *  - Desno (40%): Informacije o ekranu (sticky vrh) + navodila (sticky)
 *
 * @package SNIPI_Ekrani
 * @since   1.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SNIPI_Admin_Edit_Screen {

	/**
	 * Inicializacija – registracija WordPress hookov
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'admin_post_snipi_save_screen', array( __CLASS__, 'handle_save' ) );
	}

	/**
	 * Renderaj glavni edit screen
	 *
	 * @return void
	 */
	public static function render() {
		$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;
		$post    = $post_id ? get_post( $post_id ) : null;

		if ( ! $post || 'ekran' !== $post->post_type ) {
			self::render_error_message();
			return;
		}

		// ── PREDOGLED v ločenem oknu ──────────────────────────────────────
		// Ko je parameter snipi_preview=1, izrišemo samo vsebino ekrana
		// (brez WP admin chrome-a) in izhod iz PHP-ja.
		if ( ! empty( $_GET['snipi_preview'] ) ) {
			self::render_preview_page( $post_id );
			return;
		}

		$meta       = SNIPI_Admin_Meta::get_all( $post_id );
		$active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'nastavitve';

		if ( ! in_array( $active_tab, array( 'nastavitve', 'oblikovanje' ), true ) ) {
			$active_tab = 'nastavitve';
		}
		?>
		<div class="wrap snipi-edit-screen">

			<h1 class="wp-heading-inline">
				<?php echo esc_html( SNIPI_Admin_Meta::get_screen_title( $post_id ) ); ?>
			</h1>

			<?php self::render_tab_navigation( $post_id, $active_tab ); ?>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="snipi-edit-form">

				<?php wp_nonce_field( 'snipi_screen_save', 'snipi_nonce' ); ?>
				<input type="hidden" name="action"     value="snipi_save_screen" />
				<input type="hidden" name="post_id"    value="<?php echo intval( $post_id ); ?>" />
				<input type="hidden" name="active_tab" value="<?php echo esc_attr( $active_tab ); ?>" />

				<div class="snipi-layout snipi-layout--60-40">

					<!-- Levi stolpec (60%) -->
					<div class="snipi-layout__main">
						<?php
						if ( 'nastavitve' === $active_tab ) {
							SNIPI_Admin_Settings_Tab::render_content( $post_id, $meta );
						} elseif ( 'oblikovanje' === $active_tab ) {
							SNIPI_Admin_Styling_Tab::render_content( $post_id, $meta );
						}
						?>
					</div>

					<!-- Desni stolpec (40%) -->
					<div class="snipi-layout__sidebar">
						<?php
						if ( 'nastavitve' === $active_tab ) {
							self::render_settings_help( $meta );
						} elseif ( 'oblikovanje' === $active_tab ) {
							self::render_styling_help();
						}
						?>
					</div>

				</div>

				<div class="snipi-submit-wrapper">
					<?php submit_button( 'Shrani spremembe', 'primary', 'submit', false ); ?>
				</div>

			</form>

		</div>
		<?php
	}

	/**
	 * Tab navigacija
	 *
	 * @param int    $post_id    ID ekrana
	 * @param string $active_tab Aktivni tab
	 * @return void
	 */
	protected static function render_tab_navigation( $post_id, $active_tab ) {
		$tabs = array(
			'nastavitve'  => 'Nastavitve',
			'oblikovanje' => 'Oblikovanje',
		);

		echo '<h2 class="nav-tab-wrapper">';
		foreach ( $tabs as $tab_key => $tab_label ) {
			$tab_url = add_query_arg(
				array(
					'post_type' => 'ekran',
					'page'      => 'snipi-edit-screen',
					'post'      => intval( $post_id ),
					'tab'       => $tab_key,
				),
				admin_url( 'edit.php' )
			);
			$tab_class = ( $active_tab === $tab_key ) ? 'nav-tab nav-tab-active' : 'nav-tab';
			printf(
				'<a href="%s" class="%s">%s</a>',
				esc_url( $tab_url ),
				esc_attr( $tab_class ),
				esc_html( $tab_label )
			);
		}
		echo '</h2>';
	}

	/**
	 * Desni stolpec – Nastavitve tab
	 *
	 * Info box je prikazan na vrhu (nad navodili), ker administrator
	 * najpogosteje želi takoj videti stanje ekrana.
	 *
	 * @param array $meta Meta podatki ekrana (za prikaz info boxa)
	 * @return void
	 */
	protected static function render_settings_help( $meta = array() ) {
		?>
		<div class="snipi-sidebar-sticky">

			<?php if ( ! empty( $meta ) && null !== $meta['today_count'] ) : ?>
			<!-- INFORMACIJE O EKRANU (na vrhu desnega stolpca) -->
			<div class="snipi-info-box snipi-info-box--sidebar">
				<h4><i class="fas fa-chart-bar"></i> <?php esc_html_e( 'Informacije o ekranu', 'snipi-ekrani' ); ?></h4>
				<table class="snipi-info-table">
					<tr>
						<td><?php esc_html_e( 'Dogodki danes:', 'snipi-ekrani' ); ?></td>
						<td><strong><?php echo intval( $meta['today_count'] ); ?></strong></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Vikend način:', 'snipi-ekrani' ); ?></td>
						<td><?php echo $meta['weekend_mode'] ? '<span class="snipi-badge snipi-badge--on">Vključen</span>' : '<span class="snipi-badge snipi-badge--off">Izključen</span>'; ?></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Stolpec PROGRAM:', 'snipi-ekrani' ); ?></td>
						<td><?php echo $meta['show_program_column'] ? '<span class="snipi-badge snipi-badge--on">Prikazan</span>' : '<span class="snipi-badge snipi-badge--off">Skrit</span>'; ?></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Skaliranje pisave:', 'snipi-ekrani' ); ?></td>
						<td><?php echo ( 'fill' === $meta['row_scale_mode'] ) ? '<span class="snipi-badge snipi-badge--on">Samodejno</span>' : '<span class="snipi-badge snipi-badge--off">Prosto</span>'; ?></td>
					</tr>
				</table>
			</div>
			<?php endif; ?>

			<!-- NAVODILA ZA NASTAVITVE -->
			<div class="snipi-help-box">
				<h3><i class="fas fa-clipboard-list"></i> Navodila za nastavitve</h3>

				<h4><i class="fas fa-key"></i> API ključ</h4>
				<p>API ključ dobite iz SNIPI sistema. To je zadnji del URL naslova vašega ekrana (npr. <code>BdhBcrRm8</code>).</p>

				<h4><i class="fas fa-file-code"></i> Kratka koda</h4>
				<p>Kopirajte prikazano kratko kodo in jo prilepite v katerokoli WordPress stran ali prispevek.</p>

				<h4><i class="fas fa-table"></i> Prikaz dogodkov</h4>
				<ul>
					<li><strong>Vrstic na stran:</strong> Ciljna vrednost vrstic. Pri samodejnem skaliranju pisava zapolni zaslon.</li>
					<li><strong>Interval paginacije:</strong> Čas (v sekundah) med samodejnim menjanjem strani.</li>
					<li><strong>Prihodnji dnevi:</strong> 0 = samo danes, 1–3 = prikaz prihodnjih dni.</li>
				</ul>

				<h4><i class="fas fa-text-height"></i> Skaliranje pisave</h4>
				<p><strong>Samodejno</strong> je priporočeno za TV zaslone – pisava se prilagodi tako, da vrstice vedno zapolnijo celoten zaslon.</p>
				<p><strong>Prosto</strong> je primerno za spletne strani ali testiranje z brskalnikom na računalniku.</p>

				<h4><i class="fas fa-cog"></i> Dodatne možnosti</h4>
				<ul>
					<li><strong>Vikend način:</strong> Če vikend ni dogodkov, prikaže prihodnji teden.</li>
					<li><strong>Stolpec PROGRAM:</strong> Doda stolpec s podatki o programu/projektu.</li>
				</ul>

				<h4><i class="fas fa-image"></i> Logotip</h4>
				<p>Naložite logotip. Prikazal se bo v levem zgornjem kotu. Priporočena višina je 60–80 px.</p>

				<h4><i class="fas fa-thumbtack"></i> Spodnja vrstica</h4>
				<p>Fiksna vrstica na dnu zaslona. Višina se samodejno prilagodi vsebini – tabela se temu ustrezno skrajša.</p>
				<p>Izberite <strong>Fiksna višina</strong> za promo vsebine (slike, HTML bloki), kjer vnaprej veste dimenzijo.</p>

				<h4><i class="fas fa-tv"></i> TV Optimizacija</h4>
				<p>Priporočeno pustiti <strong>Avtomatsko</strong>. Za testiranje TV načina na računalniku izberite <em>Vedno TV način</em>.</p>
			</div>

		</div><!-- .snipi-sidebar-sticky -->
		<?php
	}

	/**
	 * Desni stolpec – Oblikovanje tab
	 *
	 * @return void
	 */
	protected static function render_styling_help() {
		?>
		<div class="snipi-sidebar-sticky">
			<div class="snipi-help-box">
				<h3><i class="fas fa-paint-brush"></i> Navodila za oblikovanje</h3>

				<h4><i class="fas fa-desktop"></i> Cel zaslon</h4>
				<p>Nastavite pisavo in barve celotnega zaslona. Te nastavitve so osnova – posamezne sekcije jih lahko preglasijo.</p>

				<h4><i class="fas fa-heading"></i> Glava</h4>
				<p>Barva ozadja, barva naslova, barva datuma in ure. Skaliranje pisave (70–150 %) ne bo nikoli zlomilo layouta.</p>

				<h4><i class="fas fa-table"></i> Tabela</h4>
				<p>Barve glave tabele, vrstic in izmenjevalnih vrstic. <strong>Padding</strong> je omejen na vrednosti, ki ne prekoračijo razpoložljive višine.</p>
				<p>Live indikator (utripajoča ikona) je mogoče skriti.</p>

				<h4><i class="fas fa-thumbtack"></i> Spodnja vrstica</h4>
				<p>Barva ozadja, barva besedila, poravnava in padding. <strong>Padding spodaj ni na voljo</strong> – spodnja vrstica je fiksirana na dno zaslona.</p>

				<h4><i class="fas fa-code"></i> Custom CSS</h4>
				<p>Za naprednejše uporabnike. Custom CSS se aplicira <em>po</em> GUI nastavitvah in jih preglasi.</p>

				<h4><i class="fas fa-list"></i> Razpoložljive CSS klase</h4>
				<ul>
					<li><code>.snipi</code> – glavni wrapper</li>
					<li><code>.snipi__header</code> – glava</li>
					<li><code>.snipi__title</code> – naslov ekrana</li>
					<li><code>.snipi__date</code> – datum</li>
					<li><code>.snipi__clock-value</code> – ura</li>
					<li><code>.snipi__pagination</code> – paginacija</li>
					<li><code>.snipi__table</code> – tabela</li>
					<li><code>.snipi__row--alt</code> – izmenske vrstice</li>
					<li><code>.snipi__live-indicator</code> – live ikona</li>
					<li><code>.snipi__bottom-row</code> – spodnja vrstica</li>
				</ul>
			</div>
		</div>
		<?php
	}

	/**
	 * Shrani nastavitve ekrana
	 *
	 * @return void
	 */
	public static function handle_save() {
		if ( ! isset( $_POST['snipi_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['snipi_nonce'] ), 'snipi_screen_save' ) ) {
			wp_die( 'Varnostna preveritev ni uspela.' );
		}

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die( 'Nimate dovoljenja za to dejanje.' );
		}

		$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
		if ( ! $post_id ) {
			wp_die( 'Neveljaven ID ekrana.' );
		}

		// Posodobi naslov
		if ( isset( $_POST['snipi_post_title'] ) ) {
			SNIPI_Admin_Meta::update_screen_title( $post_id, sanitize_text_field( wp_unslash( $_POST['snipi_post_title'] ) ) );
		}

		// Shrani vse meta podatke
		SNIPI_Admin_Meta::save_from_request( $post_id );

		// Redirect nazaj z obvestilom
		$active_tab = isset( $_POST['active_tab'] ) ? sanitize_key( $_POST['active_tab'] ) : 'nastavitve';
		$redirect   = add_query_arg(
			array(
				'post_type' => 'ekran',
				'page'      => 'snipi-edit-screen',
				'post'      => $post_id,
				'tab'       => $active_tab,
				'updated'   => 1,
			),
			admin_url( 'edit.php' )
		);
		wp_safe_redirect( $redirect );
		exit;
	}

	/**
	 * Napaka pri nalaganju ekrana
	 *
	 * @return void
	 */
	protected static function render_error_message() {
		echo '<div class="wrap"><div class="notice notice-error"><p>';
		esc_html_e( 'Ekran ni bil najden. Preverite URL ali se vrnite na seznam ekranov.', 'snipi-ekrani' );
		echo '</p></div></div>';
	}

	/**
	 * Izriše minimalen full-page predogled ekrana (brez WP admin chrome-a).
	 * Odpre se v ločenem oknu, velikost okna je nastavljiva.
	 *
	 * @param int $post_id  ID ekrana.
	 * @return void
	 */
	protected static function render_preview_page( $post_id ) {
		// Varnostni pregled nonce-a (opcijsko, saj je stran za prijavjene)
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die( esc_html__( 'Nimate dovoljenja za ogled predogleda.', 'snipi-ekrani' ) );
		}

		// Prisilimo WP da izriše shortcode kot na frontend-u
		// Skupaj s font-awesome in front CSS/JS odvisnostmi
		$shortcode_output = do_shortcode( '[snipi_ekran id="' . intval( $post_id ) . '"]' );

		// Zberemo vse enqueued skripte in stile
		ob_start();
		wp_head();
		$head = ob_get_clean();

		ob_start();
		wp_footer();
		$footer = ob_get_clean();

		// Pridobimo morebitni styling CSS iz rendererja
		$styling_css = '';
		if ( class_exists( 'SNIPI_Renderer' ) && method_exists( 'SNIPI_Renderer', 'generate_styling_css' ) ) {
			$styling_css = SNIPI_Renderer::generate_styling_css( $post_id );
		}

		$meta       = SNIPI_Admin_Meta::get_all( $post_id );
		$screen_title = SNIPI_Admin_Meta::get_screen_title( $post_id );

		?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo esc_html( $screen_title ); ?> – <?php esc_html_e( 'Predogled', 'snipi-ekrani' ); ?></title>
<?php echo $head; // phpcs:ignore WordPress.Security.EscapeOutput ?>
<style>
/* Predogled stran – brez margine in scrollbar-a */
html, body {
	margin: 0;
	padding: 0;
	overflow: hidden;
	background: #000;
	height: 100%;
	width: 100%;
}
/* Toolbar za zapiranje predogleda */
.snipi-preview-bar {
	position: fixed;
	top: 0; left: 0; right: 0;
	height: 36px;
	background: rgba(0,0,0,0.85);
	display: flex;
	align-items: center;
	justify-content: space-between;
	padding: 0 14px;
	z-index: 9999;
	font-family: system-ui, sans-serif;
	font-size: 13px;
	color: #ccc;
}
.snipi-preview-bar .snipi-preview-title { font-weight: 600; color: #fff; }
.snipi-preview-bar .snipi-preview-hint  { font-size: 11px; opacity: 0.7; }
.snipi-preview-bar button {
	background: #c0392b;
	border: none;
	color: #fff;
	padding: 4px 12px;
	border-radius: 3px;
	cursor: pointer;
	font-size: 12px;
}
.snipi-preview-bar button:hover { background: #e74c3c; }
/* Vsebina ekrana zapolni prostor pod toolbarom */
.snipi-preview-content {
	position: fixed;
	top: 36px; left: 0; right: 0; bottom: 0;
	overflow: hidden;
}
<?php echo wp_strip_all_tags( $styling_css ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
</style>
</head>
<body>

<div class="snipi-preview-bar">
	<span class="snipi-preview-title">
		<?php echo esc_html( $screen_title ); ?> – <?php esc_html_e( 'Predogled', 'snipi-ekrani' ); ?>
	</span>
	<span class="snipi-preview-hint">
		<?php esc_html_e( 'Povlecite rob okna, da preizkusite skaliranje pisave.', 'snipi-ekrani' ); ?>
	</span>
	<button onclick="window.close()">✕ <?php esc_html_e( 'Zapri', 'snipi-ekrani' ); ?></button>
</div>

<div class="snipi-preview-content">
	<?php echo $shortcode_output; // phpcs:ignore WordPress.Security.EscapeOutput ?>
</div>

<?php echo $footer; // phpcs:ignore WordPress.Security.EscapeOutput ?>
</body>
</html><?php
		exit;
	}
}
