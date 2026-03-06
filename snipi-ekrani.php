<?php
/**
 * Plugin Name: SNIPI ekrani
 * Plugin URI: https://github.com/Squarebow/snipi-ekrani
 * Description: Prikaže urnike iz Snipi API na WordPress strani. Podpira več ekranov, oblikovanje tabele, avtomatsko paginacijo, autoplay, 16:9 prikaz in osveževanje podatkov v živo.
 * Version: 2.3.4
 * Author: Aleš Lednik
 * Author URI: https://squarebow.com
 * Text Domain: snipi-ekrani
 * Domain Path: /languages
 * Requires at least: 6.7
 * Requires PHP: 8.3
 * Tested up to: 6.7
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI: false
 * GitHub Plugin URI: https://github.com/Squarebow/snipi-ekrani
 * Primary Branch: main
 * 
 * @package SNIPI_Ekrani
 */

// Prepoved direktnega dostopa
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Definicija konstant
 * 
 * SNIPI_EKRANI_PATH - Absolutna pot do plugin direktorija
 * SNIPI_EKRANI_URL  - URL do plugin direktorija
 */
define( 'SNIPI_EKRANI_PATH', plugin_dir_path( __FILE__ ) );
define( 'SNIPI_EKRANI_URL', plugin_dir_url( __FILE__ ) );

/**
 * Helper funkcija za verzioniranje assetov
 * 
 * Uporablja filemtime() za cache-busting - vsaka sprememba datoteke
 * samodejno posodobi verzijo in prisili browser da naloži novo verzijo.
 * 
 * @param string $relative_path Relativna pot do asseta (npr. 'assets/css/admin.css')
 * @return int Unix timestamp zadnje spremembe datoteke
 */
if ( ! function_exists( 'snipi_ekrani_asset_version' ) ) {
	function snipi_ekrani_asset_version( $relative_path ) {
		// Odstrani leading slash če obstaja
		$relative_path = ltrim( $relative_path, '/' );
		
		// Sestavimo polno pot
		$full_path = SNIPI_EKRANI_PATH . $relative_path;

		// Če datoteka obstaja, vrni njen filemtime
		if ( file_exists( $full_path ) ) {
			return filemtime( $full_path );
		}

		// Fallback - trenutni timestamp
		return time();
	}
}

/**
 * ============================================================================
 * NALAGANJE RAZREDOV (Class autoloading)
 * ============================================================================
 * 
 * Vsi potrebni PHP razredi se naložijo tukaj.
 * Razredi so razporejeni v module za boljšo organizacijo kode.
 */

// Admin moduli
require_once SNIPI_EKRANI_PATH . 'includes/Admin/class-admin-core.php';
require_once SNIPI_EKRANI_PATH . 'includes/Admin/class-admin-settings.php';
require_once SNIPI_EKRANI_PATH . 'includes/Admin/class-admin-meta.php';
require_once SNIPI_EKRANI_PATH . 'includes/Admin/class-admin-columns.php';
require_once SNIPI_EKRANI_PATH . 'includes/Admin/class-admin-edit-screen.php';
require_once SNIPI_EKRANI_PATH . 'includes/Admin/class-admin-settings-tab.php';
require_once SNIPI_EKRANI_PATH . 'includes/Admin/class-admin-styling-tab.php';

// API moduli
require_once SNIPI_EKRANI_PATH . 'includes/Api/class-data-service.php';
require_once SNIPI_EKRANI_PATH . 'includes/Api/class-rest-controller.php';

// Frontend moduli
require_once SNIPI_EKRANI_PATH . 'includes/Front/class-renderer.php';
require_once SNIPI_EKRANI_PATH . 'includes/Front/class-shortcode.php';

/**
 * ============================================================================
 * AKTIVACIJA VTIČNIKA
 * ============================================================================
 */

/**
 * Funkcija se izvede ob aktivaciji vtičnika
 * 
 * Trenutno samo refresh-a rewrite rules (za CPT).
 * V prihodnosti lahko doda default opcije, ustvari tabele, itd.
 * 
 * @return void
 */
function snipi_ekrani_activate() {
	// Flush rewrite rules za nov CPT
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'snipi_ekrani_activate' );

/**
 * ============================================================================
 * DEAKTIVACIJA VTIČNIKA
 * ============================================================================
 */

/**
 * Funkcija se izvede ob deaktivaciji vtičnika
 * 
 * Očisti rewrite rules.
 * Ne briše podatkov - uporabnik jih lahko hoče obdržati.
 * 
 * @return void
 */
function snipi_ekrani_deactivate() {
	// Flush rewrite rules
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'snipi_ekrani_deactivate' );
