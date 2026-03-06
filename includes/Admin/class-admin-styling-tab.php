<?php
/**
 * SNIPI Admin Styling Tab
 *
 * Vsebina taba "Oblikovanje" v edit screenu.
 *
 * Struktura (4 sekcije + custom CSS):
 *  A. Cel zaslon   – pisava, barve
 *  B. Glava        – ozadje, barve, velikost pisave, padding
 *  C. Tabela       – barve glave/vrstic, pisava, padding, live indikator
 *  D. Spodnja vrstica – barve, poravnava, padding
 *  +  Custom CSS   – napredni uporabniki
 *
 * Styling podatki se shranjujejo kot JSON v _snipi_styling_data.
 * GUI nastavitve se generirajo v CSS pri prikazu na frontendu.
 *
 * @package SNIPI_Ekrani
 * @since   2.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SNIPI_Admin_Styling_Tab {

	/**
	 * Renderaj vsebino Oblikovanje taba
	 *
	 * @param int   $post_id ID ekrana
	 * @param array $meta    Asociativni array z meta podatki
	 * @return void
	 */
	public static function render_content( $post_id, $meta ) {
		$s = $meta['styling_data']; // convenience alias
		?>

		<!-- ================================================================
		     A. CEL ZASLON
		     ================================================================ -->
		<div class="snipi-style-section" id="snipi-style-section-screen">
			<div class="snipi-style-section__header">
				<h3>
					<i class="fas fa-desktop"></i>
					<?php esc_html_e( 'Cel zaslon', 'snipi-ekrani' ); ?>
				</h3>
				<button type="button" class="snipi-style-section__toggle" aria-expanded="true">
					<span class="snipi-toggle-icon">▼</span>
				</button>
			</div>
			<div class="snipi-style-section__body">
				<div class="snipi-field-row snipi-field-row--3">

					<!-- Pisava -->
					<div class="snipi-field-col">
						<label for="snipi_style_screen_font_family" class="snipi-label">
							<?php esc_html_e( 'Pisava', 'snipi-ekrani' ); ?>
						</label>
						<select
							id="snipi_style_screen_font_family"
							name="snipi_style_screen_font_family"
							class="snipi-style-select"
						>
							<?php
							$fonts = array(
								'system-ui'                                    => 'Sistemska (privzeto)',
								'Arial, sans-serif'                            => 'Arial',
								"'Helvetica Neue', Helvetica, sans-serif"      => 'Helvetica Neue',
								"'Segoe UI', Tahoma, sans-serif"               => 'Segoe UI',
								'Roboto, sans-serif'                           => 'Roboto',
								"'Open Sans', sans-serif"                      => 'Open Sans',
								"'Noto Sans', sans-serif"                      => 'Noto Sans',
								'Georgia, serif'                               => 'Georgia (serif)',
							);
							foreach ( $fonts as $value => $label ) {
								printf(
									'<option value="%s" %s>%s</option>',
									esc_attr( $value ),
									selected( $s['screen']['font_family'], $value, false ),
									esc_html( $label )
								);
							}
							?>
						</select>
						<p class="description"><?php esc_html_e( 'Velja za vse besedilo na zaslonu.', 'snipi-ekrani' ); ?></p>
					</div>

					<!-- Barva ozadja -->
					<div class="snipi-field-col">
						<label for="snipi_style_screen_bg" class="snipi-label">
							<?php esc_html_e( 'Barva ozadja', 'snipi-ekrani' ); ?>
						</label>
						<input
							type="text"
							id="snipi_style_screen_bg"
							name="snipi_style_screen_bg"
							class="snipi-color-picker"
							value="<?php echo esc_attr( $s['screen']['background'] ); ?>"
							data-default-color=""
						/>
						<p class="description"><?php esc_html_e( 'Prazno = WordPress privzeto.', 'snipi-ekrani' ); ?></p>
					</div>

					<!-- Barva besedila -->
					<div class="snipi-field-col">
						<label for="snipi_style_screen_color" class="snipi-label">
							<?php esc_html_e( 'Barva besedila', 'snipi-ekrani' ); ?>
						</label>
						<input
							type="text"
							id="snipi_style_screen_color"
							name="snipi_style_screen_color"
							class="snipi-color-picker"
							value="<?php echo esc_attr( $s['screen']['color'] ); ?>"
							data-default-color=""
						/>
						<p class="description"><?php esc_html_e( 'Osnovna barva besedila.', 'snipi-ekrani' ); ?></p>
					</div>

				</div>
			</div>
		</div>

		<!-- ================================================================
		     B. GLAVA
		     ================================================================ -->
		<div class="snipi-style-section" id="snipi-style-section-header">
			<div class="snipi-style-section__header">
				<h3>
					<i class="fas fa-heading"></i>
					<?php esc_html_e( 'Glava', 'snipi-ekrani' ); ?>
				</h3>
				<button type="button" class="snipi-style-section__toggle" aria-expanded="true">
					<span class="snipi-toggle-icon">▼</span>
				</button>
			</div>
			<div class="snipi-style-section__body">

				<!-- Vrstica 1: barve -->
				<div class="snipi-field-row snipi-field-row--3">
					<div class="snipi-field-col">
						<label for="snipi_style_header_bg" class="snipi-label">
							<?php esc_html_e( 'Ozadje glave', 'snipi-ekrani' ); ?>
						</label>
						<input type="text" id="snipi_style_header_bg" name="snipi_style_header_bg"
							class="snipi-color-picker" value="<?php echo esc_attr( $s['header']['background'] ); ?>"
							data-default-color="" />
					</div>
					<div class="snipi-field-col">
						<label for="snipi_style_header_title_color" class="snipi-label">
							<?php esc_html_e( 'Barva naslova', 'snipi-ekrani' ); ?>
						</label>
						<input type="text" id="snipi_style_header_title_color" name="snipi_style_header_title_color"
							class="snipi-color-picker" value="<?php echo esc_attr( $s['header']['title_color'] ); ?>"
							data-default-color="" />
					</div>
					<div class="snipi-field-col">
						<label for="snipi_style_header_meta_color" class="snipi-label">
							<?php esc_html_e( 'Barva datuma / ure', 'snipi-ekrani' ); ?>
						</label>
						<input type="text" id="snipi_style_header_meta_color" name="snipi_style_header_meta_color"
							class="snipi-color-picker" value="<?php echo esc_attr( $s['header']['meta_color'] ); ?>"
							data-default-color="" />
					</div>
				</div>

				<!-- Vrstica 2: skaliranje + padding -->
				<div class="snipi-field-row snipi-field-row--3" style="margin-top: 16px;">
					<div class="snipi-field-col">
						<label class="snipi-label">
							<?php esc_html_e( 'Velikost pisave', 'snipi-ekrani' ); ?>
							<span class="snipi-range-display"><?php echo intval( $s['header']['font_scale'] ); ?>%</span>
						</label>
						<input type="range" name="snipi_style_header_font_scale" id="snipi_style_header_font_scale"
							class="snipi-style-range snipi-slider" min="70" max="150" step="5"
							value="<?php echo esc_attr( $s['header']['font_scale'] ); ?>"
							data-suffix="%" />
						<p class="description"><?php esc_html_e( '100% = privzeta velikost.', 'snipi-ekrani' ); ?></p>
					</div>
					<div class="snipi-field-col">
						<label class="snipi-label">
							<?php esc_html_e( 'Padding zgoraj (px)', 'snipi-ekrani' ); ?>
							<span class="snipi-range-display"><?php echo intval( $s['header']['padding_top'] ); ?>px</span>
						</label>
						<input type="range" name="snipi_style_header_padding_top" id="snipi_style_header_padding_top"
							class="snipi-style-range snipi-slider" min="0" max="40" step="2"
							value="<?php echo esc_attr( $s['header']['padding_top'] ); ?>"
							data-suffix="px" />
					</div>
					<div class="snipi-field-col">
						<label class="snipi-label">
							<?php esc_html_e( 'Padding levo/desno (px)', 'snipi-ekrani' ); ?>
							<span class="snipi-range-display"><?php echo intval( $s['header']['padding_h'] ); ?>px</span>
						</label>
						<input type="range" name="snipi_style_header_padding_h" id="snipi_style_header_padding_h"
							class="snipi-style-range snipi-slider" min="0" max="60" step="4"
							value="<?php echo esc_attr( $s['header']['padding_h'] ); ?>"
							data-suffix="px" />
					</div>
				</div>

			</div>
		</div>

		<!-- ================================================================
		     C. TABELA
		     ================================================================ -->
		<div class="snipi-style-section" id="snipi-style-section-table">
			<div class="snipi-style-section__header">
				<h3>
					<i class="fas fa-table"></i>
					<?php esc_html_e( 'Tabela', 'snipi-ekrani' ); ?>
				</h3>
				<button type="button" class="snipi-style-section__toggle" aria-expanded="true">
					<span class="snipi-toggle-icon">▼</span>
				</button>
			</div>
			<div class="snipi-style-section__body">

				<!-- Vrstica 1: barve glave tabele -->
				<p class="snipi-style-section__sublabel">
					<i class="fas fa-minus-circle"></i>
					<?php esc_html_e( 'Glava tabele (ČAS, IZOBRAŽEVANJE …)', 'snipi-ekrani' ); ?>
				</p>
				<div class="snipi-field-row snipi-field-row--2">
					<div class="snipi-field-col">
						<label for="snipi_style_table_thead_bg" class="snipi-label">
							<?php esc_html_e( 'Ozadje glave', 'snipi-ekrani' ); ?>
						</label>
						<input type="text" id="snipi_style_table_thead_bg" name="snipi_style_table_thead_bg"
							class="snipi-color-picker" value="<?php echo esc_attr( $s['table']['thead_bg'] ); ?>"
							data-default-color="" />
					</div>
					<div class="snipi-field-col">
						<label for="snipi_style_table_thead_color" class="snipi-label">
							<?php esc_html_e( 'Barva besedila glave', 'snipi-ekrani' ); ?>
						</label>
						<input type="text" id="snipi_style_table_thead_color" name="snipi_style_table_thead_color"
							class="snipi-color-picker" value="<?php echo esc_attr( $s['table']['thead_color'] ); ?>"
							data-default-color="" />
					</div>
				</div>

				<!-- Vrstica 2: barve vrstic -->
				<p class="snipi-style-section__sublabel" style="margin-top: 14px;">
					<i class="fas fa-list"></i>
					<?php esc_html_e( 'Vrstice podatkov', 'snipi-ekrani' ); ?>
				</p>
				<div class="snipi-field-row snipi-field-row--2">
					<div class="snipi-field-col">
						<label for="snipi_style_table_row_color" class="snipi-label">
							<?php esc_html_e( 'Barva besedila', 'snipi-ekrani' ); ?>
						</label>
						<input type="text" id="snipi_style_table_row_color" name="snipi_style_table_row_color"
							class="snipi-color-picker" value="<?php echo esc_attr( $s['table']['row_color'] ); ?>"
							data-default-color="" />
					</div>
					<div class="snipi-field-col">
						<label for="snipi_style_table_alt_bg" class="snipi-label">
							<?php esc_html_e( 'Ozadje izmenjevalnih vrstic', 'snipi-ekrani' ); ?>
						</label>
						<input type="text" id="snipi_style_table_alt_bg" name="snipi_style_table_alt_bg"
							class="snipi-color-picker" value="<?php echo esc_attr( $s['table']['alt_bg'] ); ?>"
							data-default-color="" />
					</div>
				</div>

				<!-- Vrstica 3: pisava + padding -->
				<div class="snipi-field-row snipi-field-row--3" style="margin-top: 16px;">
					<div class="snipi-field-col">
						<label class="snipi-label">
							<?php esc_html_e( 'Velikost pisave', 'snipi-ekrani' ); ?>
							<span class="snipi-range-display"><?php echo intval( $s['table']['font_scale'] ); ?>%</span>
						</label>
						<input type="range" name="snipi_style_table_font_scale" id="snipi_style_table_font_scale"
							class="snipi-style-range snipi-slider" min="70" max="150" step="5"
							value="<?php echo esc_attr( $s['table']['font_scale'] ); ?>"
							data-suffix="%" />
						<p class="description"><?php esc_html_e( '100% = privzeto (~0.95rem).', 'snipi-ekrani' ); ?></p>
					</div>
					<div class="snipi-field-col">
						<label class="snipi-label">
							<?php esc_html_e( 'Padding zgoraj (px)', 'snipi-ekrani' ); ?>
							<span class="snipi-range-display"><?php echo intval( $s['table']['padding_top'] ); ?>px</span>
						</label>
						<input type="range" name="snipi_style_table_padding_top" id="snipi_style_table_padding_top"
							class="snipi-style-range snipi-slider" min="0" max="30" step="2"
							value="<?php echo esc_attr( $s['table']['padding_top'] ); ?>"
							data-suffix="px" />
						<p class="description"><?php esc_html_e( 'Padding spodaj se prilagaja samodejno.', 'snipi-ekrani' ); ?></p>
					</div>
					<div class="snipi-field-col">
						<label class="snipi-label">
							<?php esc_html_e( 'Padding levo/desno (px)', 'snipi-ekrani' ); ?>
							<span class="snipi-range-display"><?php echo intval( $s['table']['padding_h'] ); ?>px</span>
						</label>
						<input type="range" name="snipi_style_table_padding_h" id="snipi_style_table_padding_h"
							class="snipi-style-range snipi-slider" min="0" max="40" step="2"
							value="<?php echo esc_attr( $s['table']['padding_h'] ); ?>"
							data-suffix="px" />
					</div>
				</div>

				<!-- Live indikator toggle -->
				<div style="margin-top: 16px;">
					<label class="snipi-checkbox-label">
						<input
							type="checkbox"
							id="snipi_style_table_show_live"
							name="snipi_style_table_show_live"
							value="1"
							<?php checked( $s['table']['show_live'], '1' ); ?>
							class="snipi-style-check"
						/>
						<span><?php esc_html_e( 'Prikaži live indikator (utripajoča ikona) pri tekočih dogodkih', 'snipi-ekrani' ); ?></span>
					</label>
				</div>

			</div>
		</div>

		<!-- ================================================================
		     D. SPODNJA VRSTICA
		     ================================================================ -->
		<div class="snipi-style-section" id="snipi-style-section-footer">
			<div class="snipi-style-section__header">
				<h3>
					<i class="fas fa-thumbtack"></i>
					<?php esc_html_e( 'Spodnja vrstica', 'snipi-ekrani' ); ?>
				</h3>
				<button type="button" class="snipi-style-section__toggle" aria-expanded="true">
					<span class="snipi-toggle-icon">▼</span>
				</button>
			</div>
			<div class="snipi-style-section__body">

				<!-- Barve -->
				<div class="snipi-field-row snipi-field-row--2">
					<div class="snipi-field-col">
						<label for="snipi_style_footer_bg" class="snipi-label">
							<?php esc_html_e( 'Barva ozadja', 'snipi-ekrani' ); ?>
						</label>
						<input type="text" id="snipi_style_footer_bg" name="snipi_style_footer_bg"
							class="snipi-color-picker" value="<?php echo esc_attr( $s['footer']['background'] ); ?>"
							data-default-color="#222222" />
					</div>
					<div class="snipi-field-col">
						<label for="snipi_style_footer_color" class="snipi-label">
							<?php esc_html_e( 'Barva besedila', 'snipi-ekrani' ); ?>
						</label>
						<input type="text" id="snipi_style_footer_color" name="snipi_style_footer_color"
							class="snipi-color-picker" value="<?php echo esc_attr( $s['footer']['color'] ); ?>"
							data-default-color="#ffffff" />
					</div>
				</div>

				<!-- Pisava + poravnava + padding -->
				<div class="snipi-field-row snipi-field-row--3" style="margin-top: 16px;">
					<div class="snipi-field-col">
						<label class="snipi-label">
							<?php esc_html_e( 'Velikost pisave', 'snipi-ekrani' ); ?>
							<span class="snipi-range-display"><?php echo intval( $s['footer']['font_scale'] ); ?>%</span>
						</label>
						<input type="range" name="snipi_style_footer_font_scale" id="snipi_style_footer_font_scale"
							class="snipi-style-range snipi-slider" min="70" max="150" step="5"
							value="<?php echo esc_attr( $s['footer']['font_scale'] ); ?>"
							data-suffix="%" />
					</div>
					<div class="snipi-field-col">
						<label for="snipi_style_footer_text_align" class="snipi-label">
							<?php esc_html_e( 'Poravnava besedila', 'snipi-ekrani' ); ?>
						</label>
						<select id="snipi_style_footer_text_align" name="snipi_style_footer_text_align"
							class="snipi-style-select">
							<option value="left"   <?php selected( $s['footer']['text_align'], 'left' ); ?>><?php esc_html_e( 'Levo', 'snipi-ekrani' ); ?></option>
							<option value="center" <?php selected( $s['footer']['text_align'], 'center' ); ?>><?php esc_html_e( 'Sredina', 'snipi-ekrani' ); ?></option>
							<option value="right"  <?php selected( $s['footer']['text_align'], 'right' ); ?>><?php esc_html_e( 'Desno', 'snipi-ekrani' ); ?></option>
						</select>
					</div>
					<div class="snipi-field-col">
						<label class="snipi-label">
							<?php esc_html_e( 'Padding levo/desno (px)', 'snipi-ekrani' ); ?>
							<span class="snipi-range-display"><?php echo intval( $s['footer']['padding_h'] ); ?>px</span>
						</label>
						<input type="range" name="snipi_style_footer_padding_h" id="snipi_style_footer_padding_h"
							class="snipi-style-range snipi-slider" min="0" max="60" step="4"
							value="<?php echo esc_attr( $s['footer']['padding_h'] ); ?>"
							data-suffix="px" />
					</div>
				</div>

				<!-- Padding zgoraj -->
				<div class="snipi-field-row snipi-field-row--3" style="margin-top: 8px;">
					<div class="snipi-field-col">
						<label class="snipi-label">
							<?php esc_html_e( 'Padding zgoraj (px)', 'snipi-ekrani' ); ?>
							<span class="snipi-range-display"><?php echo intval( $s['footer']['padding_top'] ); ?>px</span>
						</label>
						<input type="range" name="snipi_style_footer_padding_top" id="snipi_style_footer_padding_top"
							class="snipi-style-range snipi-slider" min="0" max="40" step="2"
							value="<?php echo esc_attr( $s['footer']['padding_top'] ); ?>"
							data-suffix="px" />
						<p class="description"><?php esc_html_e( 'Padding spodaj ni nastavljivt – vrstica je fiksirana na dno.', 'snipi-ekrani' ); ?></p>
					</div>
				</div>

			</div>
		</div>

		<!-- ================================================================
		     PREDOGLED
		     ================================================================ -->
		<div class="snipi-field-group" style="margin-top: 24px;">
			<h3><i class="fas fa-eye"></i> <?php esc_html_e( 'Predogled', 'snipi-ekrani' ); ?></h3>
			<p class="description">
				<?php esc_html_e( 'Predogled se posodablja sproti ob spremembi nastavitev.', 'snipi-ekrani' ); ?>
			</p>
			<div id="snipi-styling-preview" class="snipi-styling-preview">
				<?php self::render_initial_preview( $post_id, $meta ); ?>
			</div>
		</div>

		<!-- ================================================================
		     CUSTOM CSS (napredni uporabniki – accordion)
		     ================================================================ -->
		<div class="snipi-style-section snipi-style-section--secondary" id="snipi-style-section-css">
			<div class="snipi-style-section__header">
				<h3>
					<i class="fas fa-code"></i>
					<?php esc_html_e( 'Custom CSS', 'snipi-ekrani' ); ?>
					<span class="snipi-badge snipi-badge--neutral"><?php esc_html_e( 'Napredni uporabniki', 'snipi-ekrani' ); ?></span>
				</h3>
				<button type="button" class="snipi-style-section__toggle" aria-expanded="false">
					<span class="snipi-toggle-icon">▶</span>
				</button>
			</div>
			<div class="snipi-style-section__body" style="display: none;">
				<p class="description">
					<?php esc_html_e( 'Custom CSS se aplicira po GUI nastavitvah in jih preglasi. Velja samo za ta ekran.', 'snipi-ekrani' ); ?>
				</p>
				<textarea
					id="snipi_css_editor"
					name="snipi_custom_css"
					rows="12"
					class="large-text code snipi-css-editor"
					spellcheck="false"
				><?php echo esc_textarea( $meta['custom_css'] ); ?></textarea>

				<!-- CSS Reference -->
				<div class="snipi-css-reference" style="margin-top: 20px;">
					<h4><?php esc_html_e( 'Razpoložljive CSS klase', 'snipi-ekrani' ); ?></h4>
					<table class="widefat striped">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Klasa', 'snipi-ekrani' ); ?></th>
								<th><?php esc_html_e( 'Element', 'snipi-ekrani' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							$classes = array(
								'.snipi'                    => __( 'Glavni wrapper celotne tabele', 'snipi-ekrani' ),
								'.snipi__header'            => __( 'Glava (datum, logo, ura)', 'snipi-ekrani' ),
								'.snipi__title'             => __( 'Naslov ekrana', 'snipi-ekrani' ),
								'.snipi__date'              => __( 'Datum v glavi', 'snipi-ekrani' ),
								'.snipi__clock-value'       => __( 'Prikaz ure', 'snipi-ekrani' ),
								'.snipi__pagination'        => __( 'Paginacija (stran X/Y)', 'snipi-ekrani' ),
								'.snipi__table'             => __( 'Glavna tabela', 'snipi-ekrani' ),
								'.snipi__table thead'       => __( 'Glava tabele', 'snipi-ekrani' ),
								'.snipi__row--alt'          => __( 'Izmenske vrstice', 'snipi-ekrani' ),
								'.snipi__day-label'         => __( 'Oznaka za prihodnje dneve', 'snipi-ekrani' ),
								'.snipi__live-indicator'    => __( 'Live ikona pri tekočih dogodkih', 'snipi-ekrani' ),
								'.snipi__bottom-row'        => __( 'Spodnja fiksna vrstica', 'snipi-ekrani' ),
								'.snipi__logo'              => __( 'Logotip v glavi', 'snipi-ekrani' ),
							);
							foreach ( $classes as $class => $description ) {
								echo '<tr><td><code>' . esc_html( $class ) . '</code></td><td>' . esc_html( $description ) . '</td></tr>';
							}
							?>
						</tbody>
					</table>
				</div>
			</div>
		</div>

		<?php
	}

	/**
	 * Začetni predogled z realnimi podatki
	 *
	 * @param int   $post_id
	 * @param array $meta
	 * @return void
	 */
	protected static function render_initial_preview( $post_id, $meta ) {
		$api_key = $meta['api_key'];

		if ( empty( $api_key ) ) {
			echo '<div class="snipi-preview-empty"><p>';
			esc_html_e( 'Najprej vnesite API ključ v zavihku Nastavitve.', 'snipi-ekrani' );
			echo '</p></div>';
			return;
		}

		$tz    = new DateTimeZone( 'Europe/Ljubljana' );
		$today = new DateTime( 'now', $tz );
		$date  = $today->format( 'Y-m-d' );

		$events = SNIPI_Data_Service::get_timeslots( $api_key, $date, $date, true );

		if ( is_wp_error( $events ) ) {
			echo '<div class="snipi-preview-error"><p>';
			echo esc_html__( 'Napaka pri pridobivanju podatkov:', 'snipi-ekrani' ) . ' ' . esc_html( $events->get_error_message() );
			echo '</p></div>';
			return;
		}

		$style_data = array(
			'custom_css'   => $meta['custom_css'],
			'styling_data' => $meta['styling_data'],
		);

		echo SNIPI_Renderer::render_preview_fragment( $events, $style_data, $post_id ); // phpcs:ignore
	}
}
