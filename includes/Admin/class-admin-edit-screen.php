<?php
/**
 * SNIPI Admin Edit Screen
 * 
 * Glavni edit screen za urejanje posameznega ekrana.
 * Uporablja tab navigacijo (Nastavitve | Oblikovanje) in 60:40 layout.
 * 
 * Layout struktura:
 * - Levo (60%): Nastavitve/oblikovanje polja
 * - Desno (40%): Inline navodila in pomoč
 * 
 * @package SNIPI_Ekrani
 * @since 1.2.0
 */

// Prepoved direktnega dostopa
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SNIPI_Admin_Edit_Screen {

	/**
	 * Inicializacija - registracija WordPress hookov
	 * 
	 * @return void
	 */
	public static function init() {
		// Obdelava shranjevanja preko admin_post hook
		add_action( 'admin_post_snipi_save_screen', array( __CLASS__, 'handle_save' ) );
	}

	/**
	 * Renderaj glavni edit screen
	 * 
	 * Prikaže tab navigacijo, form in 60:40 layout.
	 * 
	 * @return void
	 */
	public static function render() {
		// Pridobi post ID iz URL parametra
		$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;
		$post    = $post_id ? get_post( $post_id ) : null;

		// Če post ne obstaja ali ni našega tipa, prikaži napako
		if ( ! $post || 'ekran' !== $post->post_type ) {
			self::render_error_message();
			return;
		}

		// Pridobi vse meta podatke
		$meta = SNIPI_Admin_Meta::get_all( $post_id );

		// Določi aktivni tab (default: nastavitve)
		$active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'nastavitve';
		
		// Validiraj tab - dovoljeni samo nastavitve in oblikovanje
		if ( ! in_array( $active_tab, array( 'nastavitve', 'oblikovanje' ), true ) ) {
			$active_tab = 'nastavitve';
		}

		// Začetek HTML outputa
		?>
		<div class="wrap snipi-edit-screen">
			
			<!-- Header z naslovom -->
			<h1 class="wp-heading-inline">
				<?php echo esc_html( SNIPI_Admin_Meta::get_screen_title( $post_id ) ); ?>
			</h1>
			
			<!-- Tab navigacija (WP native styling) -->
			<?php self::render_tab_navigation( $post_id, $active_tab ); ?>

			<!-- Glavni form -->
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="snipi-edit-form">
				
				<!-- WordPress nonce za varnost -->
				<?php wp_nonce_field( 'snipi_screen_save', 'snipi_nonce' ); ?>
				
				<!-- Hidden polja -->
				<input type="hidden" name="action" value="snipi_save_screen" />
				<input type="hidden" name="post_id" value="<?php echo intval( $post_id ); ?>" />
				<input type="hidden" name="active_tab" value="<?php echo esc_attr( $active_tab ); ?>" />

				<!-- 60:40 Layout wrapper -->
				<div class="snipi-layout snipi-layout--60-40">
					
					<!-- Levi stolpec (60%) - Nastavitve -->
					<div class="snipi-layout__main">
						<?php
						// Renderaj vsebino glede na aktivni tab
						if ( 'nastavitve' === $active_tab ) {
							SNIPI_Admin_Settings_Tab::render_content( $post_id, $meta );
						} elseif ( 'oblikovanje' === $active_tab ) {
							SNIPI_Admin_Styling_Tab::render_content( $post_id, $meta );
						}
						?>
					</div>

					<!-- Desni stolpec (40%) - Navodila -->
					<div class="snipi-layout__sidebar">
						<?php
						// Renderaj navodila glede na aktivni tab
						if ( 'nastavitve' === $active_tab ) {
							self::render_settings_help();
						} elseif ( 'oblikovanje' === $active_tab ) {
							self::render_styling_help();
						}
						?>
					</div>

				</div><!-- .snipi-layout -->

				<!-- Submit gumb (spodaj) -->
				<div class="snipi-submit-wrapper">
					<?php submit_button( 'Shrani spremembe', 'primary', 'submit', false ); ?>
				</div>

			</form>

		</div><!-- .wrap -->
		<?php
	}

	/**
	 * Renderaj tab navigacijo (WP native style)
	 * 
	 * @param int    $post_id    ID ekrana
	 * @param string $active_tab Aktivni tab
	 * @return void
	 */
	protected static function render_tab_navigation( $post_id, $active_tab ) {
		// Definicija tabov
		$tabs = array(
			'nastavitve'  => 'Nastavitve',
			'oblikovanje' => 'Oblikovanje',
		);

		echo '<h2 class="nav-tab-wrapper">';
		
		foreach ( $tabs as $tab_key => $tab_label ) {
			// Generiraj URL za tab
			$tab_url = add_query_arg(
				array(
					'post_type' => 'ekran',
					'page'      => 'snipi-edit-screen',
					'post'      => intval( $post_id ),
					'tab'       => $tab_key,
				),
				admin_url( 'edit.php' )
			);

			// Določi CSS class (active ali ne)
			$tab_class = ( $active_tab === $tab_key ) ? 'nav-tab nav-tab-active' : 'nav-tab';

			// Renderaj tab link
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
	 * Renderaj help box za tab Nastavitve
	 * 
	 * @return void
	 */
	protected static function render_settings_help() {
		?>
		<div class="snipi-help-box">
			<h3><i class="fas fa-clipboard-list"></i> Navodila za nastavitve</h3>
			
			<h4><i class="fas fa-key"></i> API ključ</h4>
			<p>API ključ dobite iz SNIPI sistema. To je zadnji del URL naslova vašega ekrana (npr. <code>BdhBcrRm8</code>).</p>
			
			<h4><i class="fas fa-file-code"></i> Kratka koda</h4>
			<p>Kopirajte prikazano kratko kodo in jo prilepite v katerokoli WordPress stran ali prispevek kjer želite prikazati urnik.</p>
			
			<h4><i class="fas fa-chart-bar"></i> Prikaz dogodkov</h4>
			<ul>
				<li><strong>Vrstic na stran:</strong> Število dogodkov na eni strani (pri paginaciji)</li>
				<li><strong>Autoplay interval:</strong> Čas (v sekundah) preden se samodejno prestavi na naslednjo stran</li>
				<li><strong>Prihodnji dnevi:</strong> Prikaz dogodkov za naslednje dni (0-3 dni)</li>
			</ul>
			
			<h4><i class="fas fa-cog"></i> Dodatne možnosti</h4>
			<ul>
				<li><strong>Vikend način:</strong> Če vikend ni dogodkov, prikaže dogodke naslednjega tedna</li>
				<li><strong>Stolpec PROGRAM:</strong> Prikaži dodaten stolpec s podatki o programu/projektu</li>
			</ul>

			<h4><i class="fas fa-image"></i> Logotip</h4>
			<p>Naložite logotip vaše organizacije. Prikazal se bo v levem zgornjem kotu tabele. Priporočena višina je 60-80px.</p>

			<h4><i class="fas fa-thumbtack"></i> Spodnja vrstica</h4>
			<p>Opcijska spodnja vrstica za dodatne informacije (kontakt, opombe). Podpira HTML formatiranje.</p>
		</div>
		<?php
	}

	/**
	 * Renderaj help box za tab Oblikovanje
	 * 
	 * @return void
	 */
	protected static function render_styling_help() {
		?>
		<div class="snipi-help-box">
			<h3><i class="fas fa-palette"></i> Navodila za oblikovanje</h3>
			
			<h4><i class="fas fa-paint-brush"></i> Custom CSS</h4>
			<p>Tukaj lahko vnesete custom CSS kodo za dodatno prilagoditev izgleda tabele.</p>
			
			<h4><i class="fas fa-bullseye"></i> Uporabne CSS classe</h4>
			<p>Glavne CSS classe ki jih lahko uporabite:</p>
			<ul>
				<li><code>.snipi</code> - Glavni wrapper</li>
				<li><code>.snipi__header</code> - Glava tabele</li>
				<li><code>.snipi__table</code> - Sama tabela</li>
				<li><code>.snipi__row</code> - Posamezna vrstica</li>
				<li><code>.snipi__row--alt</code> - Izmenične vrstice</li>
				<li><code>.snipi__bottom-row</code> - Spodnja vrstica</li>
			</ul>

			<h4><i class="fas fa-ruler-combined"></i> Primeri uporabe</h4>
			<pre><code>/* Spremeni barvo headerja */
.snipi__header {
	background: #2271b1;
	color: white;
}

/* Večji font za naslov */
.snipi__title {
	font-size: 2.5rem;
}

/* Alternirajoče vrstice */
.snipi__row--alt {
	background: #f0f0f1;
}</code></pre>

			<h4><i class="fas fa-search"></i> Predogled</h4>
			<p>Uporabite gumb "Predogled CSS" za ogled sprememb pred shranjevanjem.</p>

			<h4><i class="fas fa-exclamation-triangle"></i> Pomembno</h4>
			<p>CSS se aplicira samo na ta ekran. Če imate več ekranov, morate CSS nastaviti za vsakega posebej.</p>
		</div>
		<?php
	}

	/**
	 * Obdelaj shranjevanje forme
	 * 
	 * Validira nonce, preveri pravice in shrani podatke.
	 * 
	 * @return void
	 */
	public static function handle_save() {
		// Pridobi post ID
		$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
		
		// Preveri da post obstaja
		if ( ! $post_id || ! get_post( $post_id ) ) {
			wp_die( esc_html__( 'Neveljaven ekran.', 'snipi-ekrani' ) );
		}

		// Preveri uporabnikove pravice
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			wp_die( esc_html__( 'Nimate pravic za urejanje tega ekrana.', 'snipi-ekrani' ) );
		}

		// Preveri WordPress nonce
		if ( ! isset( $_POST['snipi_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['snipi_nonce'] ) ), 'snipi_screen_save' ) ) {
			wp_die( esc_html__( 'Neveljaven varnostni žeton.', 'snipi-ekrani' ) );
		}

		// Shrani naslov ekrana (če je poslan)
		if ( isset( $_POST['snipi_post_title'] ) ) {
			$title = sanitize_text_field( wp_unslash( $_POST['snipi_post_title'] ) );
			SNIPI_Admin_Meta::update_screen_title( $post_id, $title );
		}

		// Shrani vse meta podatke
		SNIPI_Admin_Meta::save_from_request( $post_id );

		// Pridobi aktivni tab za redirect
		$active_tab = isset( $_POST['active_tab'] ) ? sanitize_key( $_POST['active_tab'] ) : 'nastavitve';

		// Redirect nazaj na edit screen z success sporočilom
		$redirect_url = add_query_arg(
			array(
				'post_type' => 'ekran',
				'page'      => 'snipi-edit-screen',
				'post'      => intval( $post_id ),
				'tab'       => $active_tab,
				'updated'   => 1,
			),
			admin_url( 'edit.php' )
		);

		wp_safe_redirect( $redirect_url );
		exit;
	}

	/**
	 * Renderaj error sporočilo če post ne obstaja
	 * 
	 * @return void
	 */
	protected static function render_error_message() {
		?>
		<div class="wrap">
			<h1>SNIPI ekrani</h1>
			<div class="notice notice-error">
				<p>
					<?php esc_html_e( 'Izberite ekran za urejanje.', 'snipi-ekrani' ); ?>
					<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=ekran' ) ); ?>">
						<?php esc_html_e( 'Seznam vseh ekranov', 'snipi-ekrani' ); ?>
					</a>
				</p>
			</div>
		</div>
		<?php
	}
}

// Inicializacija razreda
SNIPI_Admin_Edit_Screen::init();
