<?php
/**
 * SNIPI Admin Styling Tab
 * 
 * Vsebina taba "Oblikovanje" v edit screenu.
 * Prikazuje:
 * - CSS editor za custom styling
 * - Preview funkcionalnost
 * 
 * @package SNIPI_Ekrani
 * @since 1.2.0
 */

// Prepoved direktnega dostopa
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
		?>
		<!-- CUSTOM CSS EDITOR -->
		<div class="snipi-field-group">
			<label for="snipi_css_editor" class="snipi-label">
				<?php esc_html_e( 'Custom CSS', 'snipi-ekrani' ); ?>
			</label>
			<p class="description">
				<?php esc_html_e( 'Vnesite custom CSS kodo za prilagoditev izgleda tabele. CSS se aplicira samo na ta ekran.', 'snipi-ekrani' ); ?>
			</p>
			
			<textarea 
				id="snipi_css_editor" 
				name="snipi_custom_css" 
				rows="15" 
				class="large-text code snipi-css-editor"
				spellcheck="false"
			><?php echo esc_textarea( $meta['custom_css'] ); ?></textarea>

			<!-- Preview gumb -->
			<div class="snipi-preview-actions">
				<button 
					type="button" 
					id="snipi_preview_css" 
					class="button button-secondary"
				>
					<i class="fas fa-search"></i> <?php esc_html_e( 'Predogled CSS', 'snipi-ekrani' ); ?>
				</button>
				<span class="description">
					<?php esc_html_e( 'Oglejte si kako bodo spremembe izgledale pred shranjevanjem.', 'snipi-ekrani' ); ?>
				</span>
			</div>
		</div>

		<!-- PREVIEW BOX -->
		<div class="snipi-field-group">
			<h3><i class="fas fa-eye"></i> <?php esc_html_e( 'Predogled', 'snipi-ekrani' ); ?></h3>
			<div id="snipi-styling-preview" class="snipi-styling-preview">
				<?php self::render_initial_preview( $post_id, $meta ); ?>
			</div>
		</div>

		<!-- CSS QUICK REFERENCE -->
		<div class="snipi-field-group">
			<h3><i class="fas fa-list"></i> <?php esc_html_e( 'Uporabne CSS classe', 'snipi-ekrani' ); ?></h3>
			<div class="snipi-css-reference">
				<table class="widefat striped">
					<thead>
						<tr>
							<th><?php esc_html_e( 'CSS Class', 'snipi-ekrani' ); ?></th>
							<th><?php esc_html_e( 'Element', 'snipi-ekrani' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td><code>.snipi</code></td>
							<td><?php esc_html_e( 'Glavni wrapper celotne tabele', 'snipi-ekrani' ); ?></td>
						</tr>
						<tr>
							<td><code>.snipi__header</code></td>
							<td><?php esc_html_e( 'Glava tabele (datum, logo, ura)', 'snipi-ekrani' ); ?></td>
						</tr>
						<tr>
							<td><code>.snipi__title</code></td>
							<td><?php esc_html_e( 'Glavni naslov "Urnik izobraževanj"', 'snipi-ekrani' ); ?></td>
						</tr>
						<tr>
							<td><code>.snipi__date</code></td>
							<td><?php esc_html_e( 'Datum v headerju', 'snipi-ekrani' ); ?></td>
						</tr>
						<tr>
							<td><code>.snipi__clock-value</code></td>
							<td><?php esc_html_e( 'Prikaz trenutne ure', 'snipi-ekrani' ); ?></td>
						</tr>
						<tr>
							<td><code>.snipi__pagination</code></td>
							<td><?php esc_html_e( 'Paginacija (stran X/Y)', 'snipi-ekrani' ); ?></td>
						</tr>
						<tr>
							<td><code>.snipi__table</code></td>
							<td><?php esc_html_e( 'Glavna tabela', 'snipi-ekrani' ); ?></td>
						</tr>
						<tr>
							<td><code>.snipi__table thead</code></td>
							<td><?php esc_html_e( 'Glava tabele (ČAS, IZOBRAŽEVANJE...)', 'snipi-ekrani' ); ?></td>
						</tr>
						<tr>
							<td><code>.snipi__row</code></td>
							<td><?php esc_html_e( 'Posamezna vrstica dogodka', 'snipi-ekrani' ); ?></td>
						</tr>
						<tr>
							<td><code>.snipi__row--alt</code></td>
							<td><?php esc_html_e( 'Izmenične vrstice (vsaka druga)', 'snipi-ekrani' ); ?></td>
						</tr>
						<tr>
							<td><code>.snipi__day-label</code></td>
							<td><?php esc_html_e( 'Oznaka za prihodnje dneve', 'snipi-ekrani' ); ?></td>
						</tr>
						<tr>
							<td><code>.snipi__live-indicator</code></td>
							<td><?php esc_html_e( 'Live ikona (rdeča) pri trenutnih dogodkih', 'snipi-ekrani' ); ?></td>
						</tr>
						<tr>
							<td><code>.snipi__bottom-row</code></td>
							<td><?php esc_html_e( 'Spodnja fiksna vrstica', 'snipi-ekrani' ); ?></td>
						</tr>
						<tr>
							<td><code>.snipi__logo</code></td>
							<td><?php esc_html_e( 'Logotip v headerju', 'snipi-ekrani' ); ?></td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>

		<!-- CSS PRIMERI -->
		<div class="snipi-field-group">
			<h3><i class="fas fa-lightbulb"></i> <?php esc_html_e( 'Primeri uporabe', 'snipi-ekrani' ); ?></h3>
			<div class="snipi-css-examples">
				
				<h4><?php esc_html_e( 'Primer 1: Sprememba barve headerja', 'snipi-ekrani' ); ?></h4>
				<pre><code>/* Moder header z belo pisavo */
.snipi__header {
	background: #2271b1;
	color: white;
}

.snipi__date,
.snipi__clock-value,
.snipi__pagination {
	color: white;
}</code></pre>

				<h4><?php esc_html_e( 'Primer 2: Večji font za naslov', 'snipi-ekrani' ); ?></h4>
				<pre><code>/* Povečaj naslov */
.snipi__title {
	font-size: 3rem;
	font-weight: 700;
}</code></pre>

				<h4><?php esc_html_e( 'Primer 3: Alternirajoče vrstice', 'snipi-ekrani' ); ?></h4>
				<pre><code>/* Svetlo siva alternacija */
.snipi__row--alt {
	background: #f6f7f7;
}

/* Hover effect */
.snipi__table tbody tr:hover {
	background: #e3f2fd;
}</code></pre>

				<h4><?php esc_html_e( 'Primer 4: Skrij stolpce na mobilnih napravah', 'snipi-ekrani' ); ?></h4>
				<pre><code>/* Skrij nadstropje na mobilnih */
@media (max-width: 768px) {
	.snipi__table th[data-snipi-col="floor"],
	.snipi__table td[data-snipi-col="floor"] {
		display: none;
	}
}</code></pre>

				<h4><?php esc_html_e( 'Primer 5: Stilizacija spodnje vrstice', 'snipi-ekrani' ); ?></h4>
				<pre><code>/* Poudarjena spodnja vrstica */
.snipi__bottom-row {
	background: #ffc107;
	color: #000;
	font-weight: 600;
	padding: 20px;
	text-align: center;
}</code></pre>

			</div>
		</div>
		<?php
	}

	/**
	 * Renderaj začetni preview (z današnjimi podatki)
	 * 
	 * @param int   $post_id ID ekrana
	 * @param array $meta    Meta podatki
	 * @return void
	 */
	protected static function render_initial_preview( $post_id, $meta ) {
		// Pridobi API ključ
		$api_key = $meta['api_key'];
		
		if ( empty( $api_key ) ) {
			echo '<div class="snipi-preview-empty">';
			echo '<p>' . esc_html__( 'Najprej vnesite API ključ v zavihku Nastavitve.', 'snipi-ekrani' ) . '</p>';
			echo '</div>';
			return;
		}

		// Pridobi današnje dogodke
		$tz    = new DateTimeZone( 'Europe/Ljubljana' );
		$today = new DateTime( 'now', $tz );
		$date  = $today->format( 'Y-m-d' );

		$events = SNIPI_Data_Service::get_timeslots( $api_key, $date, $date, true );

		if ( is_wp_error( $events ) ) {
			echo '<div class="snipi-preview-error">';
			echo '<p>' . esc_html__( 'Napaka pri pridobivanju podatkov iz API-ja:', 'snipi-ekrani' ) . ' ' . esc_html( $events->get_error_message() ) . '</p>';
			echo '</div>';
			return;
		}

		// Renderaj preview preko SNIPI_Renderer
		$style_data = array(
			'custom_css' => $meta['custom_css'],
		);

		$preview_html = SNIPI_Renderer::render_preview_fragment( $events, $style_data, $post_id );
		
		// Izpiši preview
		echo $preview_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
