<?php
/**
 * SNIPI Admin Columns
 * 
 * Upravlja custom stolpce v listi vseh ekranov (admin CPT list).
 * Prikazuje: Kratko kodo, API ključ, Avtorja in Datum.
 * 
 * @package SNIPI_Ekrani
 * @since 1.2.0
 */

// Prepoved direktnega dostopa
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SNIPI_Admin_Columns {

	/**
	 * Inicializacija - registracija WordPress hookov
	 * 
	 * @return void
	 */
	public static function init() {
		// Dodaj custom stolpce v list
		add_filter( 'manage_ekran_posts_columns', array( __CLASS__, 'add_columns' ) );
		
		// Renderaj vsebino custom stolpcev
		add_action( 'manage_ekran_posts_custom_column', array( __CLASS__, 'render_column_content' ), 10, 2 );
	}

	/**
	 * Dodaj custom stolpce v CPT list
	 * 
	 * Vključi stolpce: Kratka koda, API ključ, poleg standardnih (Avtor, Datum).
	 * 
	 * @param array $columns Obstoječi stolpci
	 * @return array Modificirani stolpci
	 */
	public static function add_columns( $columns ) {
		$new_columns = array();

		// Iteracija čez obstoječe stolpce - pravi vrstni red: Naslov, API ključ, Kratka koda
		foreach ( $columns as $key => $label ) {
			// Dodaj trenutni stolpec (npr. checkbox, title)
			$new_columns[ $key ] = $label;
			
			// Po stolpcu 'title' dodaj naše custom stolpce
			if ( 'title' === $key ) {
				$new_columns['api_key']   = 'API ključ';
				$new_columns['shortcode'] = 'Kratka koda';
			}
		}

		// Dodaj še stolpca Avtor in Datum na konec
		$new_columns['author'] = __( 'Avtor', 'snipi-ekrani' );
		$new_columns['date']   = __( 'Datum', 'snipi-ekrani' );

		return $new_columns;
	}

	/**
	 * Renderaj vsebino custom stolpcev
	 * 
	 * @param string $column  ID stolpca
	 * @param int    $post_id ID ekrana
	 * @return void
	 */
	public static function render_column_content( $column, $post_id ) {
		switch ( $column ) {
			case 'shortcode':
				self::render_shortcode_column( $post_id );
				break;

			case 'api_key':
				self::render_api_key_column( $post_id );
				break;

			case 'author':
				self::render_author_column( $post_id );
				break;

			case 'date':
				self::render_date_column( $post_id );
				break;
		}
	}

	/**
	 * Renderaj stolpec "Kratka koda"
	 * 
	 * Prikaže shortcode z gumbom za kopiranje.
	 * 
	 * @param int $post_id ID ekrana
	 * @return void
	 */
	protected static function render_shortcode_column( $post_id ) {
		// Generacija shortcode
		$shortcode = '[snipi_ekran id="' . intval( $post_id ) . '"]';
		
		// Prikaz kode
		echo '<code>' . esc_html( $shortcode ) . '</code> ';
		
		// Gumb za kopiranje z ikono
		echo '<button type="button" class="snipi-copy-list" data-snipi-copy="' . esc_attr( $shortcode ) . '" title="Kopiraj kratko kodo">';
		echo '<img src="' . esc_url( SNIPI_EKRANI_URL . 'assets/Copy_icon_256px.svg' ) . '" alt="Kopiraj" class="snipi-copy-icon" />';
		echo '</button>';
	}

	/**
	 * Renderaj stolpec "API ključ"
	 * 
	 * Prikaže shranjeni API ključ za ta ekran.
	 * 
	 * @param int $post_id ID ekrana
	 * @return void
	 */
	protected static function render_api_key_column( $post_id ) {
		$api_key = get_post_meta( $post_id, '_snipi_api_key', true );
		
		if ( $api_key ) {
			echo esc_html( $api_key );
		} else {
			echo '<span style="color: #999;">—</span>';
		}
	}

	/**
	 * Renderaj stolpec "Avtor"
	 * 
	 * Prikaže uporabnika ki je ustvaril ekran.
	 * 
	 * @param int $post_id ID ekrana
	 * @return void
	 */
	protected static function render_author_column( $post_id ) {
		$author_id = get_post_field( 'post_author', $post_id );
		$author_name = get_the_author_meta( 'display_name', $author_id );
		
		echo esc_html( $author_name );
	}

	/**
	 * Renderaj stolpec "Datum"
	 * 
	 * Prikaže datum kreiranja ekrana.
	 * 
	 * @param int $post_id ID ekrana
	 * @return void
	 */
	protected static function render_date_column( $post_id ) {
		echo esc_html( get_the_date( '', $post_id ) );
	}
}

// Inicializacija razreda
SNIPI_Admin_Columns::init();
