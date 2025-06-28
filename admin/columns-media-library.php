<?php
/**
 * Display image attributes as columns in the Media Library.
 *
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'manage_media_columns', 'ssim_manage_media_columns_add_columns' );
add_action( 'manage_media_custom_column', 'ssim_manage_media_custom_column_add_data', 10, 2 );

/**
 * Add columns in Media Library for each of the image attributes.
 *
 * @since 1.0.0
 *
 * @param array $columns An array of columns displayed in the Media list table.
 */
function ssim_manage_media_columns_add_columns( $columns ) {

	$columns['ssim_image_title']       = esc_html__( 'Title', 'simple-seo-image' );
	$columns['ssim_image_alt']         = esc_html__( 'Alt Text', 'simple-seo-image' );
	$columns['ssim_image_caption']     = esc_html__( 'Caption', 'simple-seo-image' );
	$columns['ssim_image_description'] = esc_html__( 'Description', 'simple-seo-image' );

	return $columns;
}

/**
 * Add image attributes data to the columns in the Media Library for each of the images.
 *
 * @since 1.0.0
 *
 * @param string $column_name Name of the custom column.
 * @param int $id Attachment ID.
 */
function ssim_manage_media_custom_column_add_data( $column_name, $id ) {

	$image = get_post( $id );

	if ( $image === null ) {
		return '';
	}

	switch ( $column_name ) {
		case 'ssim_image_title':
			echo esc_html( $image->post_title );
			break;

		case 'ssim_image_alt':
			$ssim_image_alt = get_post_meta( $id, '_wp_attachment_image_alt', true );
			echo ( $ssim_image_alt !== false ) ? esc_html( $ssim_image_alt ) : '';
			break;

		case 'ssim_image_caption':
			echo esc_html( $image->post_excerpt );
			break;

		case 'ssim_image_description':
			echo esc_html( $image->post_content );
			break;
	}
}
