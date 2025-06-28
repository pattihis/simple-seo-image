<?php
/**
 * Main functionality of the plugin is included here.
 *
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Auto add image attributes from image filename for new uploads.
 *
 * @since 1.0.0
 *
 * @param $post_id (int) Attachment ID.
 */
function ssim_auto_image_attributes( $post_id ) {

	if( ! wp_attachment_is_image( $post_id ) ) {
		return;
	}

	$image = get_post( $post_id );

	add_post_meta( $post_id, 'ssim_wp_attachment_original_post_title', $image->post_title, true );

	$image_name = ssim_image_name_from_filename( $post_id );

	ssim_update_image( $post_id, $image_name );
}
add_action( 'add_attachment', 'ssim_auto_image_attributes' );

/**
 * Auto add image attributes from image filename for existing uploads
 *
 * @since 	4.0.0
 */
function ssim_rename_old_image() {

	check_ajax_referer( 'ssim_rename_old_image_nonce', 'security' );

	$counter = get_option('ssim_bulk_updater_counter');
	$counter = intval ($counter);

	// Try to get from cache first
	$cache_key = 'ssim_image_' . $counter;
	$image = wp_cache_get( $cache_key, 'ssim_cache' );

	if ( false === $image ) {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$image = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT ID, post_parent FROM {$wpdb->prefix}posts WHERE post_type='attachment' AND post_mime_type LIKE %s ORDER BY post_date LIMIT 1 OFFSET %d",
				'image%',
				$counter
			)
		);

		// Cache for 30 seconds
		wp_cache_set( $cache_key, $image, 'ssim_cache', 30 );
	}

	if ($image === NULL) {
		wp_die();
	}

	$image_name = ssim_image_name_from_filename($image->ID, true);

	ssim_update_image($image->ID, $image_name, true);

	$counter++;
	update_option( 'ssim_bulk_updater_counter', $counter );

	$image_url = wp_get_attachment_url($image->ID);

	echo esc_html__( 'Image attributes updated for: ', 'simple-seo-image' ) . ' <a href="' . esc_url( get_edit_post_link( $image->ID ) ) . '">' . esc_html( $image_url ) . '</a>';

	wp_die();
}
add_action( 'wp_ajax_ssim_rename_old_image', 'ssim_rename_old_image' );


/**
 * Print number of images updated by the bulk updater
 *
 * @since	4.0.0
 */
function ssim_number_of_images_updated() {

	$ssim_images_updated_counter = get_option('ssim_bulk_updater_counter');
	return $ssim_images_updated_counter;
}


/**
 * Count total number of images in the database
 *
 * @since 	4.0.0
 */
function ssim_total_number_of_images() {

	// Try to get from cache first
	$cache_key = 'ssim_total_images_count';
	$total_no_of_images = wp_cache_get( $cache_key, 'ssim_cache' );

	if ( false === $total_no_of_images ) {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$total_no_of_images = $wpdb->get_var(
			"SELECT COUNT(ID) FROM {$wpdb->prefix}posts WHERE post_type='attachment' AND post_mime_type LIKE 'image%'"
		);

		// Cache for 30 seconds
		wp_cache_set( $cache_key, $total_no_of_images, 'ssim_cache', 30 );
	}

	return $total_no_of_images;
}

/**
 * Count remaining number of images to process.
 *
 * @since 1.0.0
 *
 * @return (integer) Returns the number of remaining images to process.
 */
function ssim_count_remaining_images() {

	$total_no_of_images = ssim_total_number_of_images();

	$no_of_images_processed = get_option('ssim_bulk_updater_counter');
	$no_of_images_processed = intval( $no_of_images_processed );

	return max( $total_no_of_images - $no_of_images_processed, 0 );
}

/**
 * Wrapper for ssim_count_remaining_images() to echo the result.
 *
 * @since 1.0.0
 */
function ssim_echo_count_remaining_images() {
	echo esc_html( ssim_count_remaining_images() );
	wp_die();
}
add_action( 'wp_ajax_ssim_count_remaining_images', 'ssim_echo_count_remaining_images' );


/**
 * Reset counter to zero so that bulk updating starts from scratch
 *
 * @since	4.0.0
 */
function ssim_reset_bulk_updater_counter() {

	check_ajax_referer( 'ssim_reset_counter_nonce', 'security' );

	update_option( 'ssim_bulk_updater_counter', '0' );

	$response = array(
		'message'			=> __('Counter reset. The bulk updater will start from scratch in the next run.', 'simple-seo-image'),
		'remaining_images'	=> ssim_count_remaining_images(),
	);
	wp_send_json($response);
}
add_action( 'wp_ajax_ssim_reset_bulk_updater_counter', 'ssim_reset_bulk_updater_counter' );

/**
 * Wrapper for functions to run before running bulk updater
 *
 * @since	4.0.0
 */
function ssim_before_bulk_updater() {

	check_ajax_referer( 'ssim_before_bulk_updater_nonce', 'security' );

	do_action('ssim_before_bulk_updater');

	wp_die();
}
add_action( 'wp_ajax_ssim_before_bulk_updater', 'ssim_before_bulk_updater' );

/**
 * Wrapper for functions to run after running bulk updater
 *
 * @since	4.0.0
 */
function ssim_after_bulk_updater() {

	check_ajax_referer( 'ssim_after_bulk_updater_nonce', 'security' );

	do_action('ssim_after_bulk_updater');

	wp_die();
}
add_action( 'wp_ajax_ssim_after_bulk_updater', 'ssim_after_bulk_updater');

/**
 * Increment the counter by one to skip one image.
 *
 * @since 1.0.0
 */
function ssim_bulk_updater_skip_image() {

	check_ajax_referer( 'ssim_bulk_updater_skip_image_nonce', 'security' );

	$counter = get_option( 'ssim_bulk_updater_counter' );
	$counter = intval ( $counter );

	// Try to get from cache first
	$cache_key = 'ssim_skip_image_' . $counter;
	$image = wp_cache_get( $cache_key, 'ssim_cache' );

	if ( false === $image ) {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$image = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT ID, post_parent FROM {$wpdb->prefix}posts WHERE post_type='attachment' AND post_mime_type LIKE %s ORDER BY post_date LIMIT 1 OFFSET %d",
				'image%',
				$counter
			)
		);

		// Cache for 30 seconds
		wp_cache_set( $cache_key, $image, 'ssim_cache', 30 );
	}

	if ( $image === NULL ) {
		$response = array(
			'message'			=> __( 'No more images to skip.', 'simple-seo-image' ),
			'remaining_images'	=> ssim_count_remaining_images(),
		);
		wp_send_json( $response );
	}

	$image_url = wp_get_attachment_url($image->ID);

	$counter++;
	update_option( 'ssim_bulk_updater_counter', $counter );

	$response = array(
		'message'			=> __( 'Image skipped: ', 'simple-seo-image' ) . ' <a href="' . esc_url( get_edit_post_link( $image->ID ) ) . '">' . esc_html( $image_url ) . '</a>',
		'remaining_images'	=> ssim_count_remaining_images(),
	);
	wp_send_json( $response );
}
add_action( 'wp_ajax_ssim_bulk_updater_skip_image', 'ssim_bulk_updater_skip_image' );

/**
 * Localize script for bulk updater AJAX nonces
 *
 * @since 1.0.0
 */
function ssim_localize_bulk_updater_script() {

	$screen = get_current_screen();
	if ( 'settings_page_simple-seo-image' !== $screen->id ) {
		return;
	}

	$ajax_data = [
		'before_bulk_updater_nonce' => wp_create_nonce( 'ssim_before_bulk_updater_nonce' ),
		'rename_old_image_nonce'    => wp_create_nonce( 'ssim_rename_old_image_nonce' ),
		'after_bulk_updater_nonce'  => wp_create_nonce( 'ssim_after_bulk_updater_nonce' ),
		'reset_counter_nonce'       => wp_create_nonce( 'ssim_reset_counter_nonce' ),
		'skip_image_nonce'          => wp_create_nonce( 'ssim_bulk_updater_skip_image_nonce' ),
		'delete_log_nonce'          => wp_create_nonce( 'ssim_bulk_updater_delete_log_nonce' ),
	];

	wp_localize_script( 'ssim-bulk-updater-js', 'ssimAjax', $ajax_data );
}
add_action( 'admin_enqueue_scripts', 'ssim_localize_bulk_updater_script' );


/**
 * Insert Image Title Into Post HTML
 *
 * @since 1.0.0
 *
 * @param string $html The HTML for the image.
 * @param int    $id   The attachment ID.
 *
 * @return string Modified HTML with title attribute.
 */
function ssim_restore_image_title( $html, $id ) {

	$settings = ssim_get_settings();

	if ( ! ( isset( $settings['image_title_to_html'] ) && boolval( $settings['image_title_to_html'] ) ) ) {
		return $html;
	}

	// If html already contains a title, do nothing.
	if ( false !== strpos( $html, 'title=' ) ) {
		return $html;
	}

	$attachment = get_post( $id );
	if ( ! $attachment ) {
		return $html;
	}

	$mytitle = esc_attr( $attachment->post_title );

	if ( empty( $mytitle ) ) {
		return $html;
	}

	return str_replace( '<img', '<img title="' . $mytitle . '"', $html );
}
add_filter( 'media_send_to_editor', 'ssim_restore_image_title', 15, 2 );

/**
 * Add title attribute to gallery links
 *
 * @since 1.0.0
 *
 * @param string $content The link HTML.
 * @param int    $id      The attachment ID.
 *
 * @return string Modified HTML with title attribute.
 */
function ssim_restore_title_to_gallery( $content, $id ) {

	$settings = ssim_get_settings();

	if ( ! ( isset( $settings['image_title_to_html'] ) && boolval( $settings['image_title_to_html'] ) ) ) {
		return $content;
	}

	$thumb_title = get_the_title( $id );

	if ( empty( $thumb_title ) ) {
		return $content;
	}

	return str_replace( '<a', '<a title="' . esc_attr( $thumb_title ) . '"', $content );
}
add_filter( 'wp_get_attachment_link', 'ssim_restore_title_to_gallery', 10, 4 );

/**
 * Add title attribute to images in post content (for Gutenberg and other methods)
 *
 * @since 1.0.0
 *
 * @param string $content The post content.
 *
 * @return string Modified content with title attributes added to images.
 */
function ssim_add_title_to_content_images( $content ) {

	$settings = ssim_get_settings();

	if ( ! ( isset( $settings['image_title_to_html'] ) && boolval( $settings['image_title_to_html'] ) ) ) {
		return $content;
	}

	// Pattern to match img tags without title attribute.
	$pattern = '/<img(?![^>]*title=)([^>]*?)\s*\/?>(?:<\/img>)?/i';

	return preg_replace_callback( $pattern, 'ssim_add_title_callback', $content );
}
add_filter( 'the_content', 'ssim_add_title_to_content_images', 10 );

/**
 * Callback function to add title attribute to image tags
 *
 * @since 1.0.0
 *
 * @param array $matches Regex matches.
 *
 * @return string Modified image tag with title attribute.
 */
function ssim_add_title_callback( $matches ) {

	$img_tag    = $matches[0];
	$attributes = $matches[1];

	// Extract attachment ID from various possible sources.
	$attachment_id = null;

	// Try to get ID from wp-image-{id} class.
	if ( preg_match( '/wp-image-(\d+)/', $attributes, $id_matches ) ) {
		$attachment_id = intval( $id_matches[1] );
	}

	// Try to get ID from data-id attribute.
	if ( ! $attachment_id && preg_match( '/data-id=["\']?(\d+)["\']?/', $attributes, $id_matches ) ) {
		$attachment_id = intval( $id_matches[1] );
	}

	// Try to extract from attachment URL.
	if ( ! $attachment_id && preg_match( '/src=["\']?([^"\'>]+)["\']?/', $attributes, $src_matches ) ) {
		// Try to get from cache first to avoid repeated expensive lookups.
		$cache_key     = 'url_to_postid_' . md5( $src_matches[1] );
		$attachment_id = wp_cache_get( $cache_key, 'ssim_cache' );
		if ( false === $attachment_id ) {
			$attachment_id = attachment_url_to_postid( $src_matches[1] );
			wp_cache_set( $cache_key, $attachment_id, 'ssim_cache', 3600 );
		}
	}

	if ( ! $attachment_id ) {
		return $img_tag;
	}

	$attachment = get_post( $attachment_id );
	if ( ! $attachment ) {
		return $img_tag;
	}

	$title = esc_attr( $attachment->post_title );
	if ( empty( $title ) ) {
		return $img_tag;
	}

	// Add title attribute before the closing > or />.
	if ( substr( $img_tag, -2 ) === '/>' ) {
		$img_tag = substr( $img_tag, 0, -2 ) . ' title="' . $title . '" />';
	} elseif ( substr( $img_tag, -1 ) === '>' ) {
		$img_tag = substr( $img_tag, 0, -1 ) . ' title="' . $title . '">';
	}

	return $img_tag;
}

/**
 * Replace commas in filename with hyphens
 *
 * WordPress removes commas during file upload. This function replaces commas with hyphens so that we can replace them later.
 *
 * @since 1.0.0
 *
 * @param array $file File array from upload.
 *
 * @return array Modified file array.
 */
function ssim_clean_filename( $file ) {

	$image_extensions = [
		'image/jpeg',
		'image/gif',
		'image/png',
		'image/bmp',
		'image/tiff',
		'ico',
	];

	if ( ! in_array( $file['type'], $image_extensions ) ) {
		return $file;
	}

	$settings = ssim_get_settings();

	if ( isset( $settings['commas'] ) && boolval( $settings['commas'] ) ) {
		$file['name'] = str_replace( ',', '-', $file['name'] );
	}

	return $file;
}
add_filter( 'wp_handle_upload_prefilter', 'ssim_clean_filename' );

/**
 * Extract, format and return image name from filename.
 *
 * @since 1.0.0
 *
 * @param int     $image_id Attachment ID.
 * @param boolean $bulk     True when called from the Bulk Updater. False by default.
 *
 * @return string Name of the image extracted from filename.
 */
function ssim_image_name_from_filename( $image_id, $bulk = false ) {

	if ( null === $image_id ) {
		return;
	}

	$settings = ssim_get_settings();

	$image_name = get_post_meta( $image_id, 'ssim_wp_attachment_original_post_title', true );

	if ( ( false === $image_name ) || ( '' === $image_name ) ) {
		$image_url       = wp_get_attachment_url( $image_id );
		$image_extension = pathinfo( $image_url );
		$image_name      = basename( $image_url, '.' . $image_extension['extension'] );
	}

	if ( true === $bulk ) {
		$image_name = str_replace( '-', ' ', $image_name );
		$image_name = str_replace( '_', ' ', $image_name );
		return $image_name;
	}

	$filter_chars = [];

	if ( isset( $settings['hyphens'] ) && boolval( $settings['hyphens'] ) ) {
		$filter_chars[] = '-';
	}
	if ( isset( $settings['under_score'] ) && boolval( $settings['under_score'] ) ) {
		$filter_chars[] = '_';
	}
	if ( isset( $settings['full_stop'] ) && boolval( $settings['full_stop'] ) ) {
		$filter_chars[] = '.';
	}

	if ( ! empty( $filter_chars ) ) {
		$image_name = str_replace( $filter_chars, ' ', $image_name );
	}

	if ( isset( $settings['all_numbers'] ) && boolval( $settings['all_numbers'] ) ) {
		$image_name = preg_replace( '/[0-9]+/', '', $image_name );
	}

	$image_name = preg_replace( '/\s\s+/', ' ', $image_name );
	$image_name = trim( $image_name );

	return $image_name;
}

/**
 * Update image attributes in database
 *
 * @since 1.0.0
 *
 * @param int    $image_id ID of the image to work on.
 * @param string $text     String to be used for Image Title, Caption, Description and Alt Text.
 * @param bool   $bulk     True when called from Bulk Updater. False by default.
 *
 * @return bool True on success. False otherwise.
 */
function ssim_update_image( $image_id, $text, $bulk = false ) {

	if ( null === $image_id ) {
		return false;
	}

	$settings = ssim_get_settings();

	$image       = [];
	$image['ID'] = $image_id;

	if ( true === $bulk ) {

		$image['post_title']   = $text;
		$image['post_excerpt'] = $text;
		$image['post_content'] = $text;

		update_post_meta( $image_id, '_wp_attachment_image_alt', $text );
	} else {

		if ( isset( $settings['image_title'] ) && boolval( $settings['image_title'] ) ) {
			$image['post_title'] = $text;
		}
		if ( isset( $settings['image_caption'] ) && boolval( $settings['image_caption'] ) ) {
			$image['post_excerpt'] = $text;
		}
		if ( isset( $settings['image_description'] ) && boolval( $settings['image_description'] ) ) {
			$image['post_content'] = $text;
		}
		if ( isset( $settings['image_alttext'] ) && boolval( $settings['image_alttext'] ) ) {
			update_post_meta( $image_id, '_wp_attachment_image_alt', $text );
		}
	}

	$return_id = wp_update_post( $image );

	if ( 0 === $return_id ) {
		return false;
	}

	return true;
}

/**
 * Get the boolean value of a variable
 *
 * For backwards compatibility with pre PHP 5.5
 *
 * @param mixed $var The scalar value being converted to a boolean.
 *
 * @return bool The boolean value of var.
 */
if ( ! function_exists( 'boolval' ) ) {

	/**
	 * Get the boolean value of a variable
	 *
	 * For backwards compatibility with pre PHP 5.5
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $value The scalar value being converted to a boolean.
	 *
	 * @return bool The boolean value of var.
	 */
	function boolval( $value ) {
		return (bool) $value;
	}
}
