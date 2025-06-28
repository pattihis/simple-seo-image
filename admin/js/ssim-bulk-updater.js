/**
 * Bulk Updater JavaScript functionality
 *
 * @since 4.0.0
 */
jQuery(document).ready(function($) {

	var ssim_stop = false;

	$("#bulk-updater-log").animate({scrollTop:$("#bulk-updater-log")[0].scrollHeight - $("#bulk-updater-log").height()},200);

	function ssim_do_bulk_updater(ssim_test=false) {

		ssim_stop = false;
		var focused = true;
		window.onfocus = function() {
			focused = true;
		};
		window.onblur = function() {
			focused = false;
		};
		$('.ssim-spinner').addClass("spinner");
		ssim_stop_bulk_updater_button_switch( true );

		$('#bulk-updater-log').append('<p class="ssim-green"><span class="dashicons dashicons-controls-play"></span>Initializing bulk updater. Please be patient and do not close the browser while it\'s running. In case you do, you can always resume by returning to this page later.</p>');

		$("#bulk-updater-log").animate({scrollTop:$("#bulk-updater-log")[0].scrollHeight - $("#bulk-updater-log").height()},200);

		data = {
			action: 'ssim_count_remaining_images',
		};
		var remaining_images = null;
		var reamining_images_count = $.post(ajaxurl, data, function (response) {
			remaining_images = response;
			console.log(remaining_images);
		});

		reamining_images_count.done(function() {

			if((ssim_test===true)&&(remaining_images>1)) {
				remaining_images = 1;
			}

			data = {
				action: 'ssim_before_bulk_updater',
				security: ssimAjax.before_bulk_updater_nonce
			};
			var ssim_initializer = $.post(ajaxurl, data);

			ssim_initializer.done(function ssim_rename_image() {

				if((remaining_images > 0)&&(ssim_stop===false)){
					data = {
						action: 'ssim_rename_old_image',
						security: ssimAjax.rename_old_image_nonce
					};

					var rename_image = $.post(ajaxurl, data, function (response) {
						$('#bulk-updater-log').append('<p>' + response + '</p>');
						if(ssim_test===false) {
							$('#bulk-updater-log').append('<p>Images remaining: ' + (remaining_images-1) + '</p>');
						}
						if( (($('#bulk-updater-log').prop('scrollHeight')-($('#bulk-updater-log').scrollTop()+$('#bulk-updater-log').height())) < 100) || (focused == false) )  {
							$("#bulk-updater-log").animate({scrollTop:$("#bulk-updater-log")[0].scrollHeight - $("#bulk-updater-log").height()},100);
						}
					});

					rename_image.done(function() {
						remaining_images--;
						ssim_rename_image();
					});
				} else {
					data = {
						action: 'ssim_after_bulk_updater',
						security: ssimAjax.after_bulk_updater_nonce
					};
					$.post(ajaxurl, data);

					if(ssim_stop===false) {
						$('#bulk-updater-log').append('<p class="ssim-green"><span class="dashicons dashicons-yes"></span>All done!</p>');
						$("#bulk-updater-log").animate({scrollTop:$("#bulk-updater-log")[0].scrollHeight - $("#bulk-updater-log").height()},200);
					} else {
						$('#bulk-updater-log').append('<p class="ssim-red"><span class="dashicons dashicons-dismiss"></span>Operation aborted by user.</p>');
						$("#bulk-updater-log").animate({scrollTop:$("#bulk-updater-log")[0].scrollHeight - $("#bulk-updater-log").height()},200);
					}

					$('.ssim-spinner').removeClass('spinner'); // Turn spinner off
					ssim_stop_bulk_updater_button_switch( false ); // Disable stop button
				}
			});
		});
	}

	$('.ssim_run_bulk_updater_button').click(function() {

		$('#ssim-confirm-run-dialog').dialog({
			autoOpen: false,
			width: 600,
			modal: true,
			buttons: {
				"Ok": function() {
					$(this).dialog("close");
					ssim_do_bulk_updater();
				},
				"Cancel": function() {
					$(this).dialog("close");
				}
			}
		});
		$('#ssim-confirm-run-dialog').dialog('open');
	});

	$('.ssim_test_bulk_updater_button').click(function() {

		$('#ssim-test-run-dialog').dialog({
			autoOpen: false,
			width: 600,
			modal: true,
			buttons: {
				"Ok": function() {
					$(this).dialog("close");
					ssim_do_bulk_updater(true);
				},
				"Cancel": function() {
					$(this).dialog("close");
				}
			}
		});
		$('#ssim-test-run-dialog').dialog('open');
	});

	$('.ssim_stop_bulk_updater_button').click(function() {
		ssim_stop=true;
	});

	$('.ssim_reset_counter_button').click(function() {

		$('#ssim-reset-counter-dialog').dialog({
			autoOpen: false,
			width: 600,
			modal: true,
			buttons: {
				"Ok": function() {
					data = {
						action: 'ssim_reset_bulk_updater_counter',
						security: ssimAjax.reset_counter_nonce
					};
					$.post(ajaxurl, data, function (response) {
						$('#bulk-updater-log').append('<p class="ssim-green"><span class="dashicons dashicons-yes"></span>' + response.message + '</p>');
						$('#bulk-updater-log').append('<p>Number of Images Remaining: ' + response.remaining_images + '</p>');
						$('#bulk-updater-log').append('<p>Number of Images Updated: 0</p>');
						$("#bulk-updater-log").animate({scrollTop:$("#bulk-updater-log")[0].scrollHeight - $("#bulk-updater-log").height()},200);
					});
					$(this).dialog("close");
				},
				"Cancel": function() {
					$(this).dialog("close");
				}
			}
		});
		$('#ssim-reset-counter-dialog').dialog('open');
	});

	$('.ssim_skip_image_button').click( function() {

		data = {
			action: 'ssim_bulk_updater_skip_image',
			security: ssimAjax.skip_image_nonce
		};

		$.post(ajaxurl, data, function (response) {
			$('#bulk-updater-log').append('<p class="ssim-red"><span class="dashicons dashicons-remove"></span> ' + response.message + '</p>');
			$('#bulk-updater-log').append('<p>Number of Images Remaining: ' + response.remaining_images + '</p>');
			$("#bulk-updater-log").animate({scrollTop:$("#bulk-updater-log")[0].scrollHeight - $("#bulk-updater-log").height()},200);
		});
	});

	/**
	 * Enable or disable "Stop Bulk Updater" button.
	 *
	 * @param state (bool) True to enable button, false to disable.
	 */
	function ssim_stop_bulk_updater_button_switch( state ) {
		switch ( state ) {
			case true:
				$('.ssim_stop_bulk_updater_button').prop('disabled', false); // Enable stop button
				$('.ssim_stop_bulk_updater_button').removeClass("button-secondary");
				$('.ssim_stop_bulk_updater_button').addClass("button-primary"); // Turn stop button primary
				break;

			case false:
			default:
				$('.ssim_stop_bulk_updater_button').removeClass("button-primary");
				$('.ssim_stop_bulk_updater_button').addClass("button-secondary"); // Turn stop button secondary
				$('.ssim_stop_bulk_updater_button').prop('disabled', true); // Disable stop button
				break;
		}
	}

	$('#bulk-updater-delete-log-button').click( function() {

		data = {
			action: 'ssim_bulk_updater_delete_log',
			security: ssimAjax.delete_log_nonce
		};

		$.post(ajaxurl, data, function (response) {
			$('#bulk-updater-log').append('<p class="ssim-red"><span class="dashicons dashicons-trash"></span> ' + response.message + '</p>');
			$("#bulk-updater-log").animate({scrollTop:$("#bulk-updater-log")[0].scrollHeight - $("#bulk-updater-log").height()},200);
		});
	});

});