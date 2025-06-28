<?php
/**
 * Admin UI setup and render
 *
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * General Settings field Callback
 *
 * @since 1.0.0
 */
function ssim_general_settings_callback() {
	$settings = ssim_get_settings();
	?>
	<fieldset>

		<!-- Auto Add Image Title  -->
		<label for="ssim_settings[image_title]">
			<input type="checkbox" name="ssim_settings[image_title]" id="ssim_settings[image_title]" value="1" <?php if ( isset($settings['image_title']) ) checked( '1', $settings['image_title'] ); ?>>
			<span><?php esc_html_e('Set Image Title for new uploads', 'simple-seo-image'); ?></span>
		</label><br>

		<!-- Auto Add Alt Text -->
		<label for="ssim_settings[image_alttext]">
			<input type="checkbox" name="ssim_settings[image_alttext]" id="ssim_settings[image_alttext]" value="1" <?php if ( isset($settings['image_alttext']) ) checked( '1', $settings['image_alttext'] ); ?>>
			<span><?php esc_html_e('Set Image Alt Text for new uploads', 'simple-seo-image'); ?></span>
		</label><br>

		<!-- Auto Add Image Caption  -->
		<label for="ssim_settings[image_caption]">
			<input type="checkbox" name="ssim_settings[image_caption]" id="ssim_settings[image_caption]" value="1" <?php if ( isset($settings['image_caption']) ) checked( '1', $settings['image_caption'] ); ?>>
			<span><?php esc_html_e('Set Image Caption for new uploads', 'simple-seo-image'); ?></span>
		</label><br>

		<!-- Auto Add Image Description  -->
		<label for="ssim_settings[image_description]">
			<input type="checkbox" name="ssim_settings[image_description]" id="ssim_settings[image_description]" value="1" <?php if ( isset($settings['image_description']) ) checked( '1', $settings['image_description'] ); ?>>
			<span><?php esc_html_e('Set Image Description for new uploads', 'simple-seo-image'); ?></span>
		</label><br>

	</fieldset>
	<?php
}

/**
 * Filter Settings field callback
 *
 * @since 	4.0.0
 */
function ssim_filter_settings_callback() {
	$settings = ssim_get_settings();
	?>
	<fieldset>

		<!-- Filter Hyphens -->
		<label for="ssim_settings[hyphens]">
			<input type="checkbox" name="ssim_settings[hyphens]" id="ssim_settings[hyphens]" value="1" <?php if ( isset($settings['hyphens']) ) checked( '1', $settings['hyphens'] ); ?>>
			<span><?php esc_html_e('Remove hyphens ( - ) from filename', 'simple-seo-image') ?></span>
		</label><br>

		<!-- Filter Underscore  -->
		<label for="ssim_settings[under_score]">
			<input type="checkbox" name="ssim_settings[under_score]" id="ssim_settings[under_score]" value="1" <?php if ( isset($settings['under_score']) ) checked( '1', $settings['under_score'] ); ?>>
			<span><?php esc_html_e('Remove underscores ( _ ) from filename', 'simple-seo-image'); ?></span>
		</label><br>

		<!-- Filter Full stops  -->
		<label for="ssim_settings[full_stop]">
			<input type="checkbox" name="ssim_settings[full_stop]" id="ssim_settings[full_stop]" value="1" <?php if ( isset($settings['full_stop']) ) checked( '1', $settings['full_stop'] ); ?>>
			<span><?php esc_html_e('Remove full stops ( . ) from filename', 'simple-seo-image'); ?></span>
		</label><br>

		<!-- Filter Commas  -->
		<label for="ssim_settings[commas]">
			<input type="checkbox" name="ssim_settings[commas]" id="ssim_settings[commas]" value="1" <?php if ( isset($settings['commas']) ) checked( '1', $settings['commas'] ); ?>>
			<span><?php esc_html_e('Remove commas ( , ) from filename', 'simple-seo-image'); ?></span>
		</label><br>

		<!-- Filter Numbers  -->
		<label for="ssim_settings[all_numbers]">
			<input type="checkbox" name="ssim_settings[all_numbers]" id="ssim_settings[all_numbers]" value="1" <?php if ( isset($settings['all_numbers']) ) checked( '1', $settings['all_numbers'] ); ?>>
			<span><?php esc_html_e('Remove all numbers ( 0-9 ) from filename', 'simple-seo-image'); ?></span>
		</label><br>

	</fieldset>
	<?php
}

/**
 * Basic SEO Settings Callback
 *
 * @since 	4.0.0
 */
function ssim_basic_seo_settings_callback() {
	$settings = ssim_get_settings();
	?>
	<fieldset>

		<!-- Insert Image Title Into Post HTML -->
		<label for="ssim_settings[image_title_to_html]">
			<input type="checkbox" name="ssim_settings[image_title_to_html]" id="ssim_settings[image_title_to_html]" value="1" <?php if ( isset($settings['image_title_to_html']) ) checked( '1', $settings['image_title_to_html'] ); ?>>
			<span><?php echo wp_kses_post( __('Insert Image Title into post HTML. This will add <code>title="Image Title"</code> in the <code>&lt;img&gt;</code> tag.', 'simple-seo-image') ); ?></span>
		</label><br>

	</fieldset>
	<?php
}
















/**
 * Admin interface renderer
 *
 * @since 1.0.0
 */
function ssim_admin_interface_render() {

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?>

	<div id="ssim-wrap" class="wrap">

		<h1>Simple SEO Image</h1>

		<div class="ssim-admin-options ssim-columns-1">

			<div class="ssim-admin-options-main">
				<h2 class="nav-tab-wrapper hide-if-no-js showh2">
					<a class="nav-tab" href="#ssim-basic"><?php esc_html_e('Settings', 'simple-seo-image'); ?></a>
					<a class="nav-tab" href="#ssim-bulk-updater"><?php esc_html_e('Bulk Updater', 'simple-seo-image'); ?></a>
				</h2>

				<form id="ssim-settings" action="options.php" method="post" enctype="multipart/form-data">

					<!-- Output nonce, action, and option_page fields for a settings page. -->
					<?php settings_fields( 'ssim_settings_group' ); ?>

					<!-- Settings -->
					<div id="ssim-basic" class="ssim-settings-tab">
						<h2 class="showh2"><?php esc_html_e('Upload Options', 'simple-seo-image'); ?></h2>
						<p><?php esc_html_e( 'Automatically add image attributes such as Image Title, Alt Text, Caption and Description from image filename for new uploads.', 'simple-seo-image' ); ?></p>
						<?php do_settings_sections( 'ssim_basic_settings_section' ); ?>
						<?php submit_button( esc_html__('Save Settings', 'simple-seo-image') ); ?>
					</div>

				</form>

				<!-- Bulk Updater -->
				<div id="ssim-bulk-updater" class="ssim-settings-tab">

					<h2 class="showh2"><?php esc_html_e('Bulk Updater', 'simple-seo-image'); ?></h2>

					<p><?php esc_html_e('Run this bulk updater to update Image Title, Caption, Description and Alt Text for all images.', 'simple-seo-image'); ?></p>

					<div class="notice notice-warning inline" style="max-width: 520px;">
						<p class="hide-if-js"><strong><?php esc_html_e('It seems that JavaScript is disabled in your browser. Please enable JavasScript or use a different browser to use the bulk updater.', 'simple-seo-image'); ?></strong></p>

						<p><?php esc_html_e('IMPORTANT: Please backup your database before running the bulk updater.', 'simple-seo-image'); ?></p>

						<p><?php echo wp_kses_post( __('Use <code>Test Bulk Updater</code> button to update one image at a time and verify the results.', 'simple-seo-image') ); ?></p>

					</div>


					<p class="submit">
						<input class="button-primary ssim-bulk-updater-buttons ssim_run_bulk_updater_button" type="submit" name="Run Bulk Updater" value="<?php esc_attr_e( 'Run Bulk Updater', 'simple-seo-image' ); ?>" />

						<input class="button-secondary ssim-bulk-updater-buttons ssim_test_bulk_updater_button" type="submit" name="Test Bulk Updater" value="<?php esc_attr_e( 'Test Bulk Updater', 'simple-seo-image' ); ?>" />

						<input class="button-secondary ssim-bulk-updater-buttons ssim_stop_bulk_updater_button" type="submit" name="Stop Bulk Updater" value="<?php esc_attr_e( 'Stop Bulk Updater', 'simple-seo-image' ); ?>" disabled />
					</p>

					<h2 class="showh2"><?php esc_html_e('Tools', 'simple-seo-image'); ?></h2>

					<p><?php esc_html_e('To restart processing images from the beginning (the oldest upload first), reset the counter.', 'simple-seo-image'); ?></p>

					<?php if ( apply_filters( 'ssim_debug_mode', false ) ) { ?>
						<p><?php esc_html_e( 'If Bulk Updater is stuck, refresh the page, skip current image and try running the bulk updater again.', 'simple-seo-image' ); ?></p>
					<?php } ?>

					<p class="submit">
						<input class="button-secondary ssim-bulk-updater-buttons ssim_reset_counter_button" type="submit" name="Reset Counter" value="<?php esc_attr_e( 'Reset Counter', 'simple-seo-image' ); ?>" />

						<?php if ( apply_filters( 'ssim_debug_mode', false ) ) { ?>
							<input class="button-secondary ssim-bulk-updater-buttons ssim_skip_image_button" type="submit" name="Skip Image" value="<?php esc_attr_e( 'Skip Image', 'simple-seo-image' ); ?>" />
						<?php } ?>
					</p>

					<!-- Event log -->
					<div id="bulk-updater-results">
						<fieldset id="bulk-updater-log-wrapper">
							<legend><span class="dashicons dashicons-welcome-write-blog"></span>&nbsp;<strong><?php esc_html_e('Event Log', 'simple-seo-image'); ?></strong>&nbsp;<div class="ssim-spinner is-active" style="margin-top:0px;"></div></legend>


							<div id="bulk-updater-log">


								<p id="ssim_remaining_images_text"><?php esc_html_e('Number of Images Remaining: ', 'simple-seo-image'); ?><?php echo esc_html( ssim_count_remaining_images() ); ?></p>

								<p><?php esc_html_e('Number of Images Updated: ', 'simple-seo-image'); ?><?php echo esc_html( ssim_number_of_images_updated() ); ?></p>

							</div>
						</fieldset>
					</div>

					<!-- Dialogs -->
					<div class="hidden-dialogs" style="display:none;">

						<!-- Run Bulk Updater Confirmation Dialog -->
						<div id="ssim-confirm-run-dialog" title="<?php esc_attr_e('Run Bulk Updater', 'simple-seo-image'); ?>">
							<p><?php esc_html_e('You are about to run the bulk updater. This will update all images and cannot be undone. Please make a database backup before you proceed. Press OK to confirm.', 'simple-seo-image'); ?></p>
						</div>

						<!-- Test Bulk Updater Dialog -->
						<div id="ssim-test-run-dialog" title="<?php esc_attr_e('Test Bulk Updater', 'simple-seo-image'); ?>">
							<p><?php esc_html_e('The bulk updater will do a test run by updating one image. Note that this is a live test and actual values will be updated in the database. Please make a database backup before you proceed. Press Ok to confirm.', 'simple-seo-image'); ?></p>
						</div>

						<!-- Bulk Updater Reset Counter Dialog -->
						<div id="ssim-reset-counter-dialog" title="<?php esc_attr_e('Reset Counter', 'simple-seo-image'); ?>">
							<p><?php esc_html_e('You are about to reset the bulk updater counter. The bulk updater will start from scratch in the next run. Press Ok to confirm.', 'simple-seo-image'); ?></p>
						</div>

					</div>

				</div><!-- Bulk Updater ends -->
			</div><!-- .ssim-admin-options-main -->


		</div><!-- .ssim-admin-options -->
	</div><!-- #ssim-wrap .wrap -->
	<?php
}
