<?php
/**
 * Plugin Name: SNIPI ekrani DEV
 * Plugin URI: https://github.com/Squarebow/snipi-ekrani
 * Description: Prikaže urnike iz Snipi API na WordPress strani. Podpira več ekranov, oblikovanje tabele, avtomatsko paginacijo, autoplay, 16:9 prikaz in osveževanje podatkov v živo.
 * Version: 1.0.9
 * Author: Aleš Lednik
 * Author URI: https://squarebow.com
 * Text Domain: snipi-ekrani
 * Domain Path: /languages
 * Requires at least: 5.4
 * Requires PHP: 7.4
 * Tested up to: 6.7
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI: false
 *  * ...
 * GitHub Plugin URI: https://github.com/Squarebow/snipi-ekrani
 * Primary Branch: main
 * 
 * @package SNIPI_Ekrani
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define
define( 'SNIPI_EKRANI_PATH', plugin_dir_path( __FILE__ ) );
define( 'SNIPI_EKRANI_URL', plugin_dir_url( __FILE__ ) );

if ( ! function_exists( 'snipi_ekrani_asset_version' ) ) {
	/**
	 * Returns a cache-busting version string based on file modification time.
	 * Falls back to current timestamp if the file does not exist (should not happen).
	 *
	 * @param string $relative_path Asset path relative to plugin root.
	 * @return int
	 */
	function snipi_ekrani_asset_version( $relative_path ) {
		$relative_path = ltrim( $relative_path, '/' );
		$full_path     = SNIPI_EKRANI_PATH . $relative_path;

		if ( file_exists( $full_path ) ) {
			return filemtime( $full_path );
		}

		return time();
	}
}

// Autoload includes
require_once SNIPI_EKRANI_PATH . 'includes/Admin/class-admin.php';
require_once SNIPI_EKRANI_PATH . 'includes/Admin/class-admin-styling.php';
require_once SNIPI_EKRANI_PATH . 'includes/Api/class-data-service.php';
require_once SNIPI_EKRANI_PATH . 'includes/Api/class-rest-controller.php';
require_once SNIPI_EKRANI_PATH . 'includes/Front/class-renderer.php';
require_once SNIPI_EKRANI_PATH . 'includes/Front/class-shortcode.php';

/**
 * Aktivacija vtičnika
 */
function snipi_ekrani_activate() {
	// Rezervirano za morebitne prihodnje potrebe (flush rewrite itd.)
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'snipi_ekrani_activate' );

/**
 * Deaktivacija vtičnika
 */
function snipi_ekrani_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'snipi_ekrani_deactivate' );
