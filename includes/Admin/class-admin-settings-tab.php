<?php
/**
 * SNIPI Admin Settings Tab
 * 
 * Vsebina taba "Nastavitve" v edit screenu.
 * Prikazuje polja za:
 * - API ključ
 * - Kratko kodo
 * - Nastavitve prikaza (vrstice, autoplay, prihodnji dnevi)
 * - Vikend način in stolpec PROGRAM
 * - Logotip
 * - Spodnja vrstica
 * 
 * @package SNIPI_Ekrani
 * @since 1.2.0
 */

// Prepoved direktnega dostopa
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SNIPI_Admin_Settings_Tab {

	/**
	 * Renderaj vsebino Nastavitve taba
	 * 
	 * @param int   $post_id ID ekrana
	 * @param array $meta    Asociativni array z meta podatki
	 * @return void
	 */
	public static function render_content( $post_id, $meta ) {
		?>
		<!-- IME EKRANA -->
		<div class="snipi-field-group">
			<label for="snipi_post_title" class="snipi-label">
				<?php esc_html_e( 'Ime ekrana', 'snipi-ekrani' ); ?>
			</label>
			<input 
				type="text" 
				id="snipi_post_title" 
				name="snipi_post_title" 
				class="regular-text" 
				value="<?php echo esc_attr( SNIPI_Admin_Meta::get_screen_title( $post_id ) ); ?>" 
				placeholder="npr. Ekran pri vratarju"
			/>
			<p class="description">
				<?php esc_html_e( 'Interno ime za lažje upravljanje več ekranov.', 'snipi-ekrani' ); ?>
			</p>
		</div>

		<!-- API KLJUČ -->
		<div class="snipi-field-group">
			<label for="snipi_api_key" class="snipi-label">
				<?php esc_html_e( 'API ključ', 'snipi-ekrani' ); ?>
				<span class="snipi-required">*</span>
			</label>
			<input 
				type="text" 
				id="snipi_api_key" 
				name="snipi_api_key" 
				class="regular-text" 
				value="<?php echo esc_attr( $meta['api_key'] ); ?>" 
				placeholder="npr. BdhBcrRm8"
				required
			/>
			<p class="description">
				<?php esc_html_e( 'Zadnji del URL naslova SNIPI ekrana (npr. https://urnik.snipi.si/BdhBcrRm8).', 'snipi-ekrani' ); ?>
			</p>
		</div>

		<!-- KRATKA KODA -->
		<div class="snipi-field-group">
			<label for="snipi_shortcode_field" class="snipi-label">
				<?php esc_html_e( 'Kratka koda', 'snipi-ekrani' ); ?>
			</label>
			<?php
			$shortcode = '[snipi_ekran id="' . intval( $post_id ) . '"]';
			?>
			<div class="snipi-shortcode-wrapper">
				<input 
					type="text" 
					id="snipi_shortcode_field" 
					class="regular-text" 
					value="<?php echo esc_attr( $shortcode ); ?>" 
					readonly
				/>
				<button 
					type="button" 
					id="snipi_copy_shortcode" 
					class="button button-secondary"
					title="<?php esc_attr_e( 'Kopiraj kratko kodo', 'snipi-ekrani' ); ?>"
				>
					<?php esc_html_e( 'Kopiraj', 'snipi-ekrani' ); ?>
				</button>
			</div>
			<p class="description">
				<?php esc_html_e( 'Prilepite to kratko kodo v katerokoli WordPress stran ali prispevek.', 'snipi-ekrani' ); ?>
			</p>
		</div>

		<!-- VRSTIC NA STRAN -->
		<div class="snipi-field-group snipi-field-group--inline">
			<div class="snipi-field-inline">
				<label for="snipi_rows_per_page" class="snipi-label">
					<?php esc_html_e( 'Vrstic na stran', 'snipi-ekrani' ); ?>
				</label>
				<input 
					type="number" 
					id="snipi_rows_per_page" 
					name="snipi_rows_per_page" 
					class="small-text" 
					value="<?php echo esc_attr( $meta['rows_per_page'] ); ?>" 
					min="1" 
					max="50"
				/>
			</div>

			<!-- AUTOPLAY INTERVAL -->
			<div class="snipi-field-inline">
				<label for="snipi_autoplay_interval" class="snipi-label">
					<?php esc_html_e( 'Autoplay interval (s)', 'snipi-ekrani' ); ?>
				</label>
				<input 
					type="number" 
					id="snipi_autoplay_interval" 
					name="snipi_autoplay_interval" 
					class="small-text" 
					value="<?php echo esc_attr( $meta['autoplay_interval'] ); ?>" 
					min="5" 
					max="60"
				/>
			</div>

			<!-- PRIHODNJI DNEVI -->
			<div class="snipi-field-inline">
				<label for="snipi_future_days" class="snipi-label">
					<?php esc_html_e( 'Prihodnji dnevi', 'snipi-ekrani' ); ?>
				</label>
				<input 
					type="number" 
					id="snipi_future_days" 
					name="snipi_future_days" 
					class="small-text" 
					value="<?php echo esc_attr( $meta['future_days'] ); ?>" 
					min="0" 
					max="30"
				/>
			</div>
		</div>

		<p class="description">
			<?php esc_html_e( 'Vrstic na stran: Število dogodkov prikazanih na eni strani. Autoplay: Čas (v sekundah) preden se samodejno prestavi na naslednjo stran. Prihodnji dnevi: Prikaz dogodkov za naslednje dni (0-30).', 'snipi-ekrani' ); ?>
		</p>

		<!-- INFO BOX - Število dogodkov danes -->
		<?php if ( null !== $meta['today_count'] ) : ?>
		<div class="snipi-info-box">
			<h4><?php esc_html_e( 'Informacije o ekranu', 'snipi-ekrani' ); ?></h4>
			<table class="snipi-info-table">
				<tr>
					<td><?php esc_html_e( 'Število dogodkov danes:', 'snipi-ekrani' ); ?></td>
					<td><strong><?php echo intval( $meta['today_count'] ); ?></strong></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Vikend način:', 'snipi-ekrani' ); ?></td>
					<td><?php echo $meta['weekend_mode'] ? esc_html__( 'Vključen', 'snipi-ekrani' ) : esc_html__( 'Izključen', 'snipi-ekrani' ); ?></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Stolpec PROGRAM:', 'snipi-ekrani' ); ?></td>
					<td><?php echo $meta['show_program_column'] ? esc_html__( 'Prikazan', 'snipi-ekrani' ) : esc_html__( 'Skrit', 'snipi-ekrani' ); ?></td>
				</tr>
			</table>
		</div>
		<?php endif; ?>

		<!-- CHECKBOXI: Vikend način in Stolpec PROGRAM -->
		<div class="snipi-field-group">
			<h3><?php esc_html_e( 'Dodatne možnosti', 'snipi-ekrani' ); ?></h3>
			
			<!-- Vikend način -->
			<label class="snipi-checkbox-label">
				<input 
					type="checkbox" 
					id="snipi_weekend_mode" 
					name="snipi_weekend_mode" 
					value="1"
					<?php checked( $meta['weekend_mode'], '1' ); ?>
				/>
				<span><?php esc_html_e( 'Vikend način', 'snipi-ekrani' ); ?></span>
			</label>
			<p class="description">
				<?php esc_html_e( 'Če vikend ni dogodkov, prikaže dogodke za naslednji teden (Danes + 3 dni).', 'snipi-ekrani' ); ?>
			</p>

			<!-- Stolpec PROGRAM -->
			<label class="snipi-checkbox-label">
				<input 
					type="checkbox" 
					id="snipi_show_program_column" 
					name="snipi_show_program_column" 
					value="1"
					<?php checked( $meta['show_program_column'], '1' ); ?>
				/>
				<span><?php esc_html_e( 'Prikaži stolpec PROGRAM', 'snipi-ekrani' ); ?></span>
			</label>
			<p class="description">
				<?php esc_html_e( 'Doda dodaten stolpec med "Izobraževanje" in "Predavatelj" s podatki o programu/projektu.', 'snipi-ekrani' ); ?>
			</p>
		</div>

		<!-- LOGOTIP -->
		<div class="snipi-field-group">
			<h3><?php esc_html_e( 'Logotip', 'snipi-ekrani' ); ?></h3>
			
			<!-- Hidden field za logo ID -->
			<input type="hidden" id="snipi_logo_id" name="snipi_logo_id" value="<?php echo esc_attr( $meta['logo_id'] ); ?>" />
			
			<!-- Logo preview -->
			<div id="snipi_logo_preview" class="snipi-logo-preview">
				<?php
				if ( $meta['logo_id'] ) {
					$logo_url = wp_get_attachment_image_url( $meta['logo_id'], 'full' );
					if ( $logo_url ) {
						echo '<img src="' . esc_url( $logo_url ) . '" style="height:' . intval( $meta['logo_height'] ) . 'px;width:auto;" />';
					}
				}
				?>
			</div>

			<!-- Logo gumbi -->
			<div class="snipi-logo-controls">
				<button type="button" id="snipi_logo_upload" class="button button-secondary">
					<?php esc_html_e( 'Izberi logotip', 'snipi-ekrani' ); ?>
				</button>
				<button type="button" id="snipi_logo_remove" class="button button-link-delete">
					<?php esc_html_e( 'Odstrani logotip', 'snipi-ekrani' ); ?>
				</button>
			</div>

			<!-- Logo višina slider -->
			<div class="snipi-logo-height-control">
				<label for="snipi_logo_height">
					<?php esc_html_e( 'Višina logotipa:', 'snipi-ekrani' ); ?>
					<span id="snipi_logo_height_value"><?php echo intval( $meta['logo_height'] ); ?>px</span>
				</label>
				<input 
					type="range" 
					id="snipi_logo_height" 
					name="snipi_logo_height" 
					min="40" 
					max="120" 
					step="5" 
					value="<?php echo esc_attr( $meta['logo_height'] ); ?>"
					class="snipi-slider"
				/>
			</div>
		</div>

		<!-- SPODNJA VRSTICA -->
		<div class="snipi-field-group">
			<h3><?php esc_html_e( 'Spodnja vrstica', 'snipi-ekrani' ); ?></h3>
			
			<!-- Checkbox za prikaz -->
			<label class="snipi-checkbox-label">
				<input 
					type="checkbox" 
					id="snipi_display_bottom" 
					name="snipi_display_bottom" 
					value="1"
					<?php checked( $meta['display_bottom'], '1' ); ?>
				/>
				<span><?php esc_html_e( 'Prikaži spodnjo vrstico', 'snipi-ekrani' ); ?></span>
			</label>
			<p class="description">
				<?php esc_html_e( 'Fiksna spodnja vrstica za dodatne informacije (kontakt, opombe).', 'snipi-ekrani' ); ?>
			</p>

			<!-- WYSIWYG editor za vsebino -->
			<div class="snipi-bottom-editor" data-snipi-bottom-editor>
				<?php
				$editor_settings = array(
					'textarea_name' => 'snipi_bottom_row',
					'textarea_rows' => 5,
					'media_buttons' => false,
					'teeny'         => true,
					'quicktags'     => true,
				);
				
				wp_editor( $meta['bottom_row'], 'snipi_bottom_row', $editor_settings );
				?>
			</div>
		</div>

		<!-- TV OPTIMIZACIJA (v2.2.0) -->
		<div class="snipi-field-group">
			<h3><?php esc_html_e( 'TV Optimizacija', 'snipi-ekrani' ); ?></h3>
			<p class="description">
				<?php esc_html_e( 'Plugin samodejno zazna Smart TV ekrane (Samsung, LG, Sony, itd.) in optimizira prikaz za zero-scroll izkušnjo.', 'snipi-ekrani' ); ?>
			</p>

			<!-- Avtomatska detekcija -->
			<label class="snipi-checkbox-label">
				<input 
					type="checkbox" 
					id="snipi_enable_tv_detection" 
					name="snipi_enable_tv_detection" 
					value="1"
					<?php checked( $meta['enable_tv_detection'] ?? '1', '1' ); ?>
				/>
				<span><?php esc_html_e( 'Omogoči avtomatsko detekcijo Smart TV ekranov', 'snipi-ekrani' ); ?></span>
			</label>
			<p class="description">
				<?php esc_html_e( 'Priporočeno: VKLJUČENO. Plugin zazna TV brskalnike in optimizira prikaz.', 'snipi-ekrani' ); ?>
			</p>

			<!-- Način prikaza -->
			<div style="margin-top: 15px;">
				<label for="snipi_tv_mode_override" class="snipi-label">
					<?php esc_html_e( 'Način prikaza', 'snipi-ekrani' ); ?>
				</label>
				<select id="snipi_tv_mode_override" name="snipi_tv_mode_override">
					<option value="auto" <?php selected( $meta['tv_mode_override'] ?? 'auto', 'auto' ); ?>>
						<?php esc_html_e( 'Avtomatsko (priporočeno)', 'snipi-ekrani' ); ?>
					</option>
					<option value="tv" <?php selected( $meta['tv_mode_override'] ?? 'auto', 'tv' ); ?>>
						<?php esc_html_e( 'Vedno TV način', 'snipi-ekrani' ); ?>
					</option>
					<option value="desktop" <?php selected( $meta['tv_mode_override'] ?? 'auto', 'desktop' ); ?>>
						<?php esc_html_e( 'Vedno namizni način', 'snipi-ekrani' ); ?>
					</option>
				</select>
				<p class="description">
					<?php esc_html_e( 'Avtomatsko = Plugin sam odloči glede na napravo | Vedno TV = Force TV optimizacijo (za testiranje) | Vedno Desktop = Onemogoči TV optimizacijo', 'snipi-ekrani' ); ?>
				</p>
			</div>

			<!-- Potrditveno okno -->
			<div style="margin-top: 15px;">
				<label class="snipi-checkbox-label">
					<input 
						type="checkbox" 
						id="snipi_tv_confirm_dialog" 
						name="snipi_tv_confirm_dialog" 
						value="1"
						<?php checked( $meta['tv_confirm_dialog'] ?? '1', '1' ); ?>
					/>
					<span><?php esc_html_e( 'Prikaži potrditveno okno pri prvi uporabi', 'snipi-ekrani' ); ?></span>
				</label>
				<p class="description">
					<?php esc_html_e( 'Ko sistem zazna TV, uporabnika vpraša: "Zaznan TV ekran. Optimiziram prikaz za TV?"', 'snipi-ekrani' ); ?>
				</p>
			</div>
		</div>
		<?php
	}
}
