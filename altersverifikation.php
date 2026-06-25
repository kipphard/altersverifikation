<?php
/**
 * Plugin Name:       Altersverifikation – Age Gate für WooCommerce & WordPress
 * Plugin URI:        https://products.kipphard.com/altersverifikation
 * Description:       Zeigt einen DSGVO-konformen Altersverifikations-Overlay (Age Gate) vor dem Seiteninhalt. Geburtsdatum wird ausschließlich client-seitig ausgewertet – keine personenbezogenen Daten verlassen den Browser.
 * Version:           0.1.0
 * Requires at least: 6.4
 * Requires PHP:      7.4
 * Author:            André Kipphard
 * Author URI:        https://kipphard.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       altersverifikation
 * Domain Path:       /languages
 *
 * @package Kipphard\Altersverifikation
 */

defined( 'ABSPATH' ) || exit;

define( 'AVF_VERSION', '0.1.0' );
define( 'AVF_FILE', __FILE__ );
define( 'AVF_DIR', plugin_dir_path( __FILE__ ) );
define( 'AVF_URL', plugin_dir_url( __FILE__ ) );
define( 'AVF_SLUG', 'altersverifikation' );

/**
 * Minimaler PSR-4-Autoloader für den Namespace Kipphard\Altersverifikation\.
 * Kipphard\Altersverifikation\Foo_Bar -> includes/class-foo-bar.php
 */
spl_autoload_register(
	static function ( $class ) {
		$prefix = 'Kipphard\\Altersverifikation\\';
		if ( strncmp( $class, $prefix, strlen( $prefix ) ) !== 0 ) {
			return;
		}
		$relative = substr( $class, strlen( $prefix ) );
		$file     = 'class-' . strtolower( str_replace( '_', '-', $relative ) ) . '.php';
		$path     = AVF_DIR . 'includes/' . $file;
		if ( is_readable( $path ) ) {
			require_once $path;
		}
	}
);

register_activation_hook( __FILE__, array( '\Kipphard\Altersverifikation\Plugin', 'activate' ) );

add_action(
	'plugins_loaded',
	static function () {
		\Kipphard\Altersverifikation\Plugin::instance()->boot();
	}
);
