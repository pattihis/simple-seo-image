<?php
/**
 * Admin setup for the plugin
 *
 * @since 1.0.0
 */


// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Add admin menu pages
 *
 * @since 	4.0.0
 */
function ssim_add_menu_links() {

	$ssim_plugin_title = 'Simple SEO Image';
	$ssim_menu_title   = 'Simple SEO Image';

	add_options_page( $ssim_plugin_title, $ssim_menu_title, 'manage_options', 'simple-seo-image', 'ssim_admin_interface_render' );
}
add_action( 'admin_menu', 'ssim_add_menu_links' );


/**
 * Register Settings
 *
 * @since 	4.0.0
 */
function ssim_register_settings() {

	register_setting(
		'ssim_settings_group',
		'ssim_settings',
		'ssim_settings_validater_and_sanitizer'
	);

	add_settings_section(
		'ssim_basic_settings_section_id',
		esc_html__( 'Basic Settings', 'simple-seo-image' ),
		'__return_false',
		'ssim_basic_settings_section'
	);

	add_settings_field(
		'ssim_general_settings',
		wp_kses_post( __( 'General Settings<p class="ssim-description">Select image attributes that should be automatically generated when you upload a new image.</p>', 'simple-seo-image' ) ),
		'ssim_general_settings_callback',
		'ssim_basic_settings_section',
		'ssim_basic_settings_section_id'
	);

	add_settings_field(
		'ssim_filter_settings',
		wp_kses_post( __( 'Filter Settings<p class="ssim-description">Selected characters will be removed from filename text before using them as image attributes.</p>', 'simple-seo-image' ) ),
		'ssim_filter_settings_callback',
		'ssim_basic_settings_section',
		'ssim_basic_settings_section_id'
	);

	add_settings_field(
		'ssim_basic_seo_settings',
		esc_html__( 'Basic SEO Settings', 'simple-seo-image' ),
		'ssim_basic_seo_settings_callback',
		'ssim_basic_settings_section',
		'ssim_basic_settings_section_id'
	);
}
add_action( 'admin_init', 'ssim_register_settings' );

/**
 * Input validator and sanitizer
 *
 * @since 1.0.0
 *
 * @param array $settings An array that contains all the settings.
 *
 * @return array Array containing all the settings.
 */
function ssim_settings_validater_and_sanitizer( $settings ) {

	$settings['custom_filter']	= sanitize_text_field( $settings['custom_filter'] );

	if ( @preg_match( $settings['regex_filter'], null ) === false ) {
		unset( $settings['regex_filter'] );
	}

	$settings['custom_attribute_title'] 			= ssim_sanitize_text_field( $settings['custom_attribute_title'] );
	$settings['custom_attribute_alt_text'] 			= ssim_sanitize_text_field( $settings['custom_attribute_alt_text'] );
	$settings['custom_attribute_caption'] 			= ssim_sanitize_text_field( $settings['custom_attribute_caption'] );
	$settings['custom_attribute_description'] 		= ssim_sanitize_text_field( $settings['custom_attribute_description'] );

	return $settings;
}

/**
 * Extend sanitize_text_field() to preserve %category% custom attribute tag.
 *
 * sanitize_text_field() converts %category% to tegory%.
 * Here %category% is replaced with SSIM_CATEGORY_CUSTOM_TAG keyword before sanitization.
 * Then SSIM_CATEGORY_CUSTOM_TAG is replaced with %category% after sanitization.
 *
 * @since 1.0.0
 *
 * @param string $str String to be sanitized.
 *
 * @return string Sanitized string with %category% preserved.
 */
function ssim_sanitize_text_field( $str ) {

	$str = str_replace( '%category%', 'SSIM_CATEGORY_CUSTOM_TAG', $str );
	$str = sanitize_text_field( $str );
	$str = str_replace( 'SSIM_CATEGORY_CUSTOM_TAG', '%category%', $str );

	return $str;
}

/**
 * Set global default values for settings
 *
 * @since 	4.0.0
 * @return	array	A merged array of default and settings saved in database.
 */
function ssim_get_settings() {

	$ssim_defaults = array(
		'image_title' 				=> '1',
		'image_caption' 			=> '1',
		'image_description' 		=> '1',
		'image_alttext' 			=> '1',

		'image_title_to_html' 		=> '1',

		'hyphens' 					=> '1',
		'under_score' 				=> '1',
		'full_stop' 				=> '0',
		'commas' 					=> '0',
		'all_numbers' 				=> '0',
	);

	$settings = get_option( 'ssim_settings', $ssim_defaults );

	return $settings;
}

/**
 * Load Admin Side Js and CSS
 *
 * Used for styling the plugin pages and bulk updater.
 * @since 1.0.0
 */
function ssim_enqueue_js_and_css() {

	$screen = get_current_screen();
	if ( 'settings_page_simple-seo-image' !== $screen->id ) {
		return;
	}

	// Custom SSIM Styling.
	wp_enqueue_style( 'ssim-style', plugins_url( '/css/ssim-style.css', __FILE__ ), '', SSIM_VERSION_NUM );

	// Main SSIM JS for settings tabs.
	wp_enqueue_script( 'ssim-js', plugins_url( '/js/ssim-js.js', __FILE__ ), [ 'jquery', 'jquery-ui-dialog' ], SSIM_VERSION_NUM, true );

	// Bulk Updater JS.
	wp_enqueue_script( 'ssim-bulk-updater-js', plugins_url( '/js/ssim-bulk-updater.js', __FILE__ ), [ 'jquery', 'jquery-ui-dialog' ], SSIM_VERSION_NUM, true );

	// jQuery Dialog CSS.
	wp_enqueue_style( 'wp-jquery-ui-dialog' );
}
add_action( 'admin_enqueue_scripts', 'ssim_enqueue_js_and_css' );

/**
 * Tags for custom attribute
 *
 * @since 1.0.0
 */
function ssim_custom_attribute_tags() {

	$available_tags = array(
		'filename'			=> __( 'Image filename', 'simple-seo-image' ),
		'posttitle'			=> __( 'Title of the post, page or product where the image is used', 'simple-seo-image' ),
		'sitetitle'			=> __( 'Site Title defined in WordPress General Settings', 'simple-seo-image' ),
		'category'			=> __( 'Post or product Category', 'simple-seo-image' ),
		'tag'				=> __( 'Post or product Tag', 'simple-seo-image' ),
		'excerpt'			=> __( 'Excerpt or product short description', 'simple-seo-image' ),
		'copymedialibrary'	=> __( 'Copy attribute from Media Library', 'simple-seo-image' ),
		'imagetitle'		=> __( 'Image Title', 'simple-seo-image' ),
		'imagealttext'		=> __( 'Image Alt Text', 'simple-seo-image' ),
		'imagecaption'		=> __( 'Image Caption', 'simple-seo-image' ),
		'imagedescription'	=> __( 'Image Description', 'simple-seo-image' ),
	);

	/**
	 * Filter the custom attribute tags.
	 *
	 * @since 1.0.0
	 *
	 * @param $available_tags (array) Array containing all custom attribute tags.
	 */
	return apply_filters( 'ssim_custom_attribute_tags', $available_tags );
}
