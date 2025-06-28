<?php
/**
 * Plugin Name: Simple SEO Image
 * Plugin URI: https://wordpress.org/plugins/simple-seo-image/
 * Description: Optimize image SEO automatically. Add alt text, titles, captions and descriptions from filenames. Bulk update existing images.
 * Author: George Pattichis
 * Author URI: https://profiles.wordpress.org/pattihis/
 * Version: 1.0
 * Requires at least: 5.3.0
 * Tested up to: 6.8.1
 * Requires PHP: 5.6
 * Text Domain: simple-seo-image
 * Domain Path: /languages
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin directory path and URL constants
 *
 * @since 1.0.0
 */
if ( ! defined( 'SSIM_DIR' ) ) {

	define( 'SSIM_DIR', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'SSIM_URL' ) ) {

	define( 'SSIM_URL', plugin_dir_url( __FILE__ ) );
}

/**
 * A constant with current version of plugin
 *
 * @since 1.0.0
 */
if ( ! defined( 'SSIM_VERSION_NUM' ) ) {
	define( 'SSIM_VERSION_NUM', '1.0' );
}

/**
 * Do stuff after a plugin upgrade.
 *
 * @since 1.0.0
 */
function ssim_upgrader() {

	$current_ver = get_option( 'ssim_version', '1.0' );

	if ( version_compare( $current_ver, SSIM_VERSION_NUM, '==' ) ) {
		return;
	}

	// TODO: Add upgrade code here.

	update_option( 'ssim_version', SSIM_VERSION_NUM );
}
add_action( 'admin_init', 'ssim_upgrader' );

// Load everything.
require_once SSIM_DIR . '/loader.php';

// Register activation hook.
register_activation_hook( __FILE__, 'ssim_activate_plugin' );
