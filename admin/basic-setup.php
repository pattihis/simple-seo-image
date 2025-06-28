<?php
/**
 * Basic setup functions for the plugin
 *
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * This function runs when user activates the plugin
 *
 * @since	4.0.0
 */
function ssim_activate_plugin() {

	add_option( 'ssim_bulk_updater_counter', '0' );

	set_transient( 'ssim_activation_admin_notice', true, 5 );
}

/**
 * Load plugin text domain
 *
 * @since	4.0.0
 */
function ssim_load_plugin_textdomain() {
    load_plugin_textdomain( 'simple-seo-image', false, SSIM_DIR . '/languages/' );
}
add_action( 'plugins_loaded', 'ssim_load_plugin_textdomain' );

/**
 * Print direct link to plugin settings in plugins list in admin
 *
 * @since	4.0.0
 */
function ssim_settings_link( $links ) {
	return array_merge(
		array(
			'settings' => '<a href="' . esc_url( admin_url( 'options-general.php?page=simple-seo-image' ) ) . '">' . esc_html__( 'Settings', 'simple-seo-image' ) . '</a>'
		),
		$links
	);
}
add_filter( 'plugin_action_links_simple-seo-image/simple-seo-image.php', 'ssim_settings_link' );

/**
 * Add donate and other links to plugins list
 *
 * @since	4.0.0
 */
function ssim_plugin_row_meta( $links, $file ) {

	if ( false !== strpos( $file, 'simple-seo-image.php' ) ) {
		$new_links = array(
			'support' => '<a href="https://profiles.wordpress.org/pattihis/" target="_blank">' . esc_html__( 'Support', 'simple-seo-image' ) . '</a>',
		);
		$links = array_merge( $links, $new_links );
	}

	return $links;
}
add_filter( 'plugin_row_meta', 'ssim_plugin_row_meta', 10, 2 );

/**
 * Admin notices
 *
 * @since 1.0.0
 */
function ssim_admin_notices() {

	if ( get_transient( 'ssim_activation_admin_notice' ) ) {

		$message = sprintf(
			/* translators: %s is the URL to the settings page */
			__( 'Thank you for installing <strong>Simple SEO Image</strong>! <a href="%s">Change settings &rarr;</a>', 'simple-seo-image' ),
			esc_url( admin_url( 'options-general.php?page=simple-seo-image' ) )
		);
		echo '<div class="notice notice-success is-dismissible"><p>' . wp_kses_post( $message ) . '</p></div>';

		delete_transient( 'ssim_activation_admin_notice' );

		return;
	}

	if ( get_transient( 'ssim_upgrade_complete_admin_notice' ) ) {

		$message = __( '<strong>Simple SEO Image</strong> successfully updated.', 'simple-seo-image' );
		echo '<div class="notice notice-success is-dismissible"><p>' . wp_kses_post( $message ) . '</p></div>';

		delete_transient( 'ssim_upgrade_complete_admin_notice' );
	}
}
add_action( 'admin_notices', 'ssim_admin_notices' );

/**
 * Admin footer text
 *
 * A function to add footer text to the settings page of the plugin. Footer text contains plugin rating and donation links.
 * Note: Remove the rating link if the plugin doesn't have a WordPress.org directory listing yet. (i.e. before initial approval)
 * @since	4.0.0
 * @refer	https://codex.wordpress.org/Function_Reference/get_current_screen
 */
function ssim_footer_text( $default ) {

	// Return default on non-plugin pages.
	$screen = get_current_screen();
	if ( 'settings_page_simple-seo-image' !== $screen->id ) {
		return $default;
	}

	$ssim_footer_text = sprintf(
		/* translators: %1$s is the URL to rate the plugin, %2$s is the URL to review the plugin */
		__( 'If you like this free plugin, please leave a <a href="%1$s" target="_blank">&#9733;&#9733;&#9733;&#9733;&#9733;</a> rating or <a href="%2$s" target="_blank">review</a> to support continued development. Thanks!', 'simple-seo-image' ),
		'https://wordpress.org/support/plugin/simple-seo-image/reviews/?rate=5#new-post',
		'https://wordpress.org/support/plugin/simple-seo-image/reviews/?rate=5#new-post'
	);

	return $ssim_footer_text;
}
add_filter( 'admin_footer_text', 'ssim_footer_text' );

/**
 * Admin footer version
 *
 * @since	4.0.0
 */
function ssim_footer_version( $default ) {

	$screen = get_current_screen();
	if ( 'settings_page_simple-seo-image' !== $screen->id ) {
		return $default;
	}

	return 'Simple SEO Image v' . SSIM_VERSION_NUM;
}
add_filter( 'update_footer', 'ssim_footer_version', 11 );
