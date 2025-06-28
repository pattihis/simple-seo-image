<?php
/**
 * Loads the plugin files
 *
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load basic setup. Plugin list links, text domain, footer links etc.
require_once SSIM_DIR . '/admin/basic-setup.php';

// Load admin setup. Register menus and settings.
require_once SSIM_DIR . '/admin/admin-setup.php';

// Render Admin UI.
require_once SSIM_DIR . '/admin/admin-ui-render.php';
require_once SSIM_DIR . '/admin/columns-media-library.php';

// Do plugin operations.
require_once SSIM_DIR . '/admin/do.php';
