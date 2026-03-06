<?php
/**
 * SNIPI Admin Settings Tab
 *
 * Vsebina taba "Nastavitve" v edit screenu.
 *
 * Struktura:
 *  1. Ime ekrana + API ključ          (50:50)
 *  2. Kratka koda                     (100)
 *  3. Dodatne možnosti
 *     a) Vrstic / Interval / Prihodnji dnevi  (33:33:33)
 *     b) Skaliranje pisave            (50:50 radio)
 *     c) Vikend način + PROGRAM       (50:50 checkbox)
 *  4. Logotip
 *  5. Spodnja vrstica
 *     - checkbox prikaži/skrij
 *     - višina (50:50 radio)
 *     - fiksna višina slider
 *     - naslov + WYSIWYG editor
 *  6. TV optimizacija
 *     - checkbox detekcija (z opisom)
 *     - Način prikaza + Potrditveno okno  (50:50)
 *       Način prikaza: 3 horizontalni radio gumbi (pills)
 *
 * @package SNIPI_Ekrani
 * @since   1.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SNIPI_Admin_Settings_Tab {

	public static function render_content( $post_id, $meta ) {
		?>

		<!-- ================================================================
		     1. IME EKRANA + API KLJUČ  (50:50)
		     ================================================================ -->
		<div class="snipi-field-group">
			<div class="snipi-field-row snipi-field-row--2">

				<div class="snipi-field-col">
					<label for="snipi_post_title" class="snipi-label">
						<?php esc_html_e( 'Ime ekrana', 'snipi-ekrani' ); ?>
					</label>
					<input
						type="text"
						id="snipi_post_title"
						name="snipi_post_title"
						class="regular-text snipi-input-full"
						value="<?php echo esc_attr( SNIPI_Admin_Meta::get_screen_title( $post_id ) ); ?>"
						placeholder="npr. Ekran pri vratarju"
					/>
					<p class="description">
						<?php esc_html_e( 'Interno ime za lažje upravljanje več ekranov.', 'snipi-ekrani' ); ?>
					</p>
				</div>

				<div class="snipi-field-col">
					<label for="snipi_api_key" class="snipi-label">
						<?php esc_html_e( 'API ključ', 'snipi-ekrani' ); ?>
						<span class="snipi-required">*</span>
					</label>
					<input
						type="text"
						id="snipi_api_key"
						name="snipi_api_key"
						class="regular-text snipi-input-full"
						value="<?php echo esc_attr( $meta['api_key'] ); ?>"
						placeholder="npr. BdhBcrRm8"
						required
					/>
					<p class="description">
						<?php esc_html_e( 'Zadnji del URL naslova SNIPI ekrana (npr. /BdhBcrRm8).', 'snipi-ekrani' ); ?>
					</p>
				</div>

			</div>
		</div>

		<!-- ================================================================
		     2. KRATKA KODA  (100)
		     ================================================================ -->
		<div class="snipi-field-group">
			<label for="snipi_shortcode_field" class="snipi-label">
				<?php esc_html_e( 'Kratka koda', 'snipi-ekrani' ); ?>
			</label>
			<?php $shortcode = '[snipi_ekran id="' . intval( $post_id ) . '"]'; ?>
			<div class="snipi-shortcode-wrapper">
				<input
					type="text"
					id="snipi_shortcode_field"
					class="regular-text"
					value="<?php echo esc_attr( $shortcode ); ?>"
					readonly
				/>
				<button type="button" id="snipi_copy_shortcode" class="button button-secondary">
					<?php esc_html_e( 'Kopiraj', 'snipi-ekrani' ); ?>
				</button>
			</div>
			<p class="description">
				<?php esc_html_e( 'Prilepite to kratko kodo v katerokoli WordPress stran ali prispevek.', 'snipi-ekrani' ); ?>
			</p>
		</div>

		<!-- ================================================================
		     3. DODATNE MOŽNOSTI
		     ================================================================ -->
		<div class="snipi-field-group">
			<h3><i class="fas fa-sliders-h"></i> <?php esc_html_e( 'Dodatne možnosti', 'snipi-ekrani' ); ?></h3>

			<!-- 3a. Vrstic / Interval / Prihodnji dnevi  (33:33:33) -->
			<div class="snipi-field-row snipi-field-row--3">

				<div class="snipi-field-col">
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
					<p class="description">
						<?php esc_html_e( 'Število dogodkov na eni strani. Pisava se samodejno prilagodi, da vrstice zapolnijo zaslon.', 'snipi-ekrani' ); ?>
					</p>
				</div>

				<div class="snipi-field-col">
					<label for="snipi_autoplay_interval" class="snipi-label">
						<?php esc_html_e( 'Interval paginacije (s)', 'snipi-ekrani' ); ?>
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
					<p class="description">
						<?php esc_html_e( 'Čas (v sekundah) preden se samodejno prestavi na naslednjo stran paginacije.', 'snipi-ekrani' ); ?>
					</p>
				</div>

				<div class="snipi-field-col">
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
						max="3"
					/>
					<p class="description">
						<?php esc_html_e( 'Prikaz dogodkov za naslednje dni (0 = samo danes, max 3).', 'snipi-ekrani' ); ?>
					</p>
				</div>

			</div>

			<!-- 3b. Skaliranje pisave (50:50 radio) -->
			<div style="margin-top: 20px;">
				<p class="snipi-label" style="margin-bottom: 10px;">
					<?php esc_html_e( 'Skaliranje pisave', 'snipi-ekrani' ); ?>
				</p>
				<div class="snipi-field-row snipi-field-row--2">
					<div class="snipi-field-col">
						<label class="snipi-radio-label snipi-radio-label--block">
							<input
								type="radio"
								name="snipi_row_scale_mode"
								value="fill"
								<?php checked( $meta['row_scale_mode'], 'fill' ); ?>
							/>
							<span>
								<strong><?php esc_html_e( 'Samodejno (priporočeno)', 'snipi-ekrani' ); ?></strong>
								<span class="snipi-radio-desc">
									<?php esc_html_e( 'Pisava in vrstice se skalirajo, da vrstic na stran točno zapolni zaslon.', 'snipi-ekrani' ); ?>
								</span>
							</span>
						</label>
					</div>
					<div class="snipi-field-col">
						<label class="snipi-radio-label snipi-radio-label--block">
							<input
								type="radio"
								name="snipi_row_scale_mode"
								value="free"
								<?php checked( $meta['row_scale_mode'], 'free' ); ?>
							/>
							<span>
								<strong><?php esc_html_e( 'Prosto (privzeta velikost pisave)', 'snipi-ekrani' ); ?></strong>
								<span class="snipi-radio-desc">
									<?php esc_html_e( 'Pisava ostane fiksna, prikaže se toliko vrstic, kolikor se jih fizično ujame na zaslon.', 'snipi-ekrani' ); ?>
								</span>
							</span>
						</label>
					</div>
				</div>
			</div>

			<!-- 3c. Vikend način + Stolpec PROGRAM  (50:50) -->
			<div class="snipi-field-row snipi-field-row--2" style="margin-top: 20px;">

				<div class="snipi-field-col">
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
				</div>

				<div class="snipi-field-col">
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

			</div>
		</div>

		<!-- ================================================================
		     4. LOGOTIP
		     ================================================================ -->
		<div class="snipi-field-group">
			<h3><i class="fas fa-image"></i> <?php esc_html_e( 'Logotip', 'snipi-ekrani' ); ?></h3>

			<input type="hidden" id="snipi_logo_id" name="snipi_logo_id" value="<?php echo esc_attr( $meta['logo_id'] ); ?>" />

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

			<div class="snipi-logo-controls">
				<button type="button" id="snipi_logo_upload" class="button button-secondary">
					<?php esc_html_e( 'Izberi logotip', 'snipi-ekrani' ); ?>
				</button>
				<button type="button" id="snipi_logo_remove" class="button button-link-delete">
					<?php esc_html_e( 'Odstrani logotip', 'snipi-ekrani' ); ?>
				</button>
			</div>

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

		<!-- ================================================================
		     5. SPODNJA VRSTICA
		     ================================================================ -->
		<div class="snipi-field-group">
			<h3><i class="fas fa-thumbtack"></i> <?php esc_html_e( 'Spodnja vrstica', 'snipi-ekrani' ); ?></h3>

			<!-- Prikaži/skrij -->
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
				<?php esc_html_e( 'Fiksna spodnja vrstica za dodatne informacije (kontakt, opombe, logotipi sponzorjev …).', 'snipi-ekrani' ); ?>
			</p>

			<!-- Višina spodnje vrstice (50:50 radio) -->
			<div style="margin-top: 18px; margin-bottom: 14px;">
				<p class="snipi-label" style="margin-bottom: 10px;">
					<?php esc_html_e( 'Višina spodnje vrstice', 'snipi-ekrani' ); ?>
				</p>
				<div class="snipi-field-row snipi-field-row--2">
					<div class="snipi-field-col">
						<label class="snipi-radio-label snipi-radio-label--block">
							<input
								type="radio"
								name="snipi_footer_height_mode"
								value="auto"
								<?php checked( $meta['footer_height_mode'], 'auto' ); ?>
							/>
							<span>
								<strong><?php esc_html_e( 'Samodejno', 'snipi-ekrani' ); ?></strong>
								<span class="snipi-radio-desc">
									<?php esc_html_e( 'Višina se prilagodi vsebini. Tabela se samodejno skrajša, da vsebina ni zakrita.', 'snipi-ekrani' ); ?>
								</span>
							</span>
						</label>
					</div>
					<div class="snipi-field-col">
						<label class="snipi-radio-label snipi-radio-label--block">
							<input
								type="radio"
								name="snipi_footer_height_mode"
								value="fixed"
								<?php checked( $meta['footer_height_mode'], 'fixed' ); ?>
							/>
							<span>
								<strong><?php esc_html_e( 'Fiksna višina', 'snipi-ekrani' ); ?></strong>
								<span class="snipi-radio-desc">
									<?php esc_html_e( 'Priporočeno za promo vsebine (slike, HTML bloki). Tabela rezervira točno toliko prostora.', 'snipi-ekrani' ); ?>
								</span>
							</span>
						</label>
					</div>
				</div>
			</div>

			<!-- Slider za fiksno višino -->
			<div class="snipi-footer-fixed-control" id="snipi_footer_fixed_control"
			     style="<?php echo 'fixed' !== $meta['footer_height_mode'] ? 'display:none;' : ''; ?>margin-bottom: 18px;">
				<label for="snipi_footer_fixed_height">
					<?php esc_html_e( 'Fiksna višina:', 'snipi-ekrani' ); ?>
					<span id="snipi_footer_fixed_height_value"><?php echo intval( $meta['footer_fixed_height'] ); ?>px</span>
				</label>
				<input
					type="range"
					id="snipi_footer_fixed_height"
					name="snipi_footer_fixed_height"
					min="40"
					max="200"
					step="4"
					value="<?php echo esc_attr( $meta['footer_fixed_height'] ); ?>"
					class="snipi-slider"
				/>
			</div>

			<!-- Urejevalnik vsebine spodnje vrstice -->
			<div class="snipi-bottom-editor" data-snipi-bottom-editor>
				<p class="snipi-label" style="margin-bottom: 8px;">
					<?php esc_html_e( 'Urejevalnik vsebine spodnje vrstice', 'snipi-ekrani' ); ?>
				</p>
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

		<!-- ================================================================
		     6. TV OPTIMIZACIJA
		     ================================================================ -->
		<div class="snipi-field-group">
			<h3><i class="fas fa-tv"></i> <?php esc_html_e( 'TV Optimizacija', 'snipi-ekrani' ); ?></h3>

			<p class="description" style="margin-bottom: 16px;">
				<?php esc_html_e( 'Plugin samodejno zazna Smart TV ekrane (Samsung, LG, Sony, itd.) in optimizira prikaz za zero-scroll izkušnjo.', 'snipi-ekrani' ); ?>
			</p>

			<!-- Detekcija -->
			<div class="snipi-tv-option-block">
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
			</div>

			<!-- Način prikaza + Potrditveno okno (50:50) -->
			<div class="snipi-field-row snipi-field-row--2" style="margin-top: 18px;">

				<!-- Način prikaza: 3 horizontalni radio pills -->
				<div class="snipi-field-col">
					<p class="snipi-label" style="margin-bottom: 10px;">
						<?php esc_html_e( 'Način prikaza', 'snipi-ekrani' ); ?>
					</p>
					<div class="snipi-radio-inline" id="snipi_tv_mode_pills">
						<label class="snipi-radio-pill">
							<input
								type="radio"
								name="snipi_tv_mode_override"
								value="auto"
								<?php checked( $meta['tv_mode_override'] ?? 'auto', 'auto' ); ?>
							/>
							<?php esc_html_e( 'Avtomatsko', 'snipi-ekrani' ); ?>
						</label>
						<label class="snipi-radio-pill">
							<input
								type="radio"
								name="snipi_tv_mode_override"
								value="tv"
								<?php checked( $meta['tv_mode_override'] ?? 'auto', 'tv' ); ?>
							/>
							<?php esc_html_e( 'Vedno TV', 'snipi-ekrani' ); ?>
						</label>
						<label class="snipi-radio-pill">
							<input
								type="radio"
								name="snipi_tv_mode_override"
								value="desktop"
								<?php checked( $meta['tv_mode_override'] ?? 'auto', 'desktop' ); ?>
							/>
							<?php esc_html_e( 'Namizni', 'snipi-ekrani' ); ?>
						</label>
					</div>
					<p class="description" style="margin-top: 8px;">
						<?php esc_html_e( 'Avtomatsko = samodejno zazna | Vedno TV = prisilna optimizacija za testiranje | Namizni = brez TV optimizacije.', 'snipi-ekrani' ); ?>
					</p>
				</div>

				<!-- Potrditveno okno -->
				<div class="snipi-field-col">
					<p class="snipi-label" style="margin-bottom: 10px;">
						<?php esc_html_e( 'Potrditveno okno', 'snipi-ekrani' ); ?>
					</p>
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
					<p class="description" style="margin-top: 6px;">
						<?php esc_html_e( 'Ko sistem zazna TV, uporabnika vpraša: "Zaznan TV ekran. Optimiziram prikaz za TV?"', 'snipi-ekrani' ); ?>
					</p>
				</div>

			</div>
		</div>

		<?php
	}
}
