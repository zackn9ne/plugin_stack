<?php if ($listing->level->images_number): ?>
<?php
$img_width = get_option('thumbnail_size_w'); 
$img_height = get_option('thumbnail_size_h'); 
?>
<script>
	var images_number = <?php echo $listing->level->images_number; ?>;

	jQuery(document).ready(function($) {
		$("#images_wrapper").on('click', '.delete_item', function() {
			$(this).parent().remove();

			if (images_number > $("#images_wrapper .w2dc-attached-item").length)
				$("#w2dc-upload-functions").show();
		});
	});
</script>

<div id="w2dc-upload-wrapper">
	<h3>
		<?php _e('Listing images', 'W2DC'); ?>
	</h3>

	<div id="images_wrapper">
	<?php foreach ($listing->images AS $attachment_id=>$attachment): ?>
		<?php $src = wp_get_attachment_image_src($attachment_id, 'thumbnail'); ?>
		<?php $src_full = wp_get_attachment_image_src($attachment_id, 'full'); ?>
		<div class="w2dc-attached-item">
			<div class="w2dc-delete-attached-item delete_item" title="<?php esc_attr_e('remove image', 'W2DC'); ?>"></div>
			<input type="hidden" name="attached_image_id[]" value="<?php echo $attachment_id; ?>" />
			<div class="w2dc-img-div-border" style="width: <?php echo $img_width; ?>px; height: <?php echo $img_height; ?>px">
				<span class="w2dc-img-div-helper"></span><a href="<?php echo $src_full[0]; ?>" data-lightbox="listing_images"><img src="<?php echo $src[0]; ?>" style="max-width: <?php echo $img_width; ?>px; max-height: <?php echo $img_height; ?>px" /></a>
			</div>
			<input type="text" name="attached_image_title[]" size="37" class="w2dc-form-control" value="<?php echo esc_attr($attachment['post_title']); ?>" />
			<?php if ($listing->level->logo_enabled): ?>
			<label><input type="radio" name="attached_image_as_logo" value="<?php echo $attachment_id; ?>" <?php checked($listing->logo_image, $attachment_id); ?>> <?php _e('set this image as logo', 'W2DC'); ?></label>
			<?php endif; ?>
		</div>
	<?php endforeach; ?>
	</div>
	<div class="clear_float"></div>

	<?php if (current_user_can('upload_files')): ?>
	<script>
		jQuery(document).ready(function() {
			jQuery('#upload_image').click(function(event) {
				event.preventDefault();
		
				var frame = wp.media({
		            title : '<?php echo esc_js(sprintf(__('Upload image (%d maximum)', 'W2DC'), $listing->level->images_number)); ?>',
		            multiple : true,
		            library : { type : 'image'},
		            button : { text : 'Insert' },
		        });
				frame.on( 'select', function() {
				    var selection = frame.state().get('selection');
				    selection.each(function(attachment) {
				    	attachment = attachment.toJSON();
						if (images_number <= jQuery("#images_wrapper .w2dc-attached-item").length)
							jQuery("#w2dc-upload-functions").hide();
						else {
							ajax_loader_show();

							if (typeof attachment.sizes.thumbnail != 'undefined')
								var attachment_url = attachment.sizes.thumbnail.url;
							else
								var attachment_url = attachment.sizes.full.url;
							var attachment_url_full = attachment.sizes.full.url;
							var attachment_id = attachment.id;
							var attachment_name = attachment.name;
							jQuery('<div class="w2dc-attached-item"><div class="w2dc-delete-attached-item delete_item" title="<?php esc_attr_e('remove image', 'W2DC'); ?>"></div><input type="hidden" name="attached_image_id[]" value="' + attachment_id + '" /><div class="w2dc-img-div-border" style="width: <?php echo $img_width; ?>px; height: <?php echo $img_height; ?>px"><span class="w2dc-img-div-helper"></span><a href="' + attachment_url_full + '" data-lightbox="listing_images"><img src="' + attachment_url + '" style="max-width: <?php echo $img_width; ?>px; max-height: <?php echo $img_height; ?>px" /></a></div><input type="text" name="attached_image_title[]" class="w2dc-form-control" value="' + attachment_name + '" size="37" /><?php if ($listing->level->logo_enabled): ?><label><input type="radio" name="attached_image_as_logo" value="' + attachment_id + '"> <?php _e('set this image as logo', 'W2DC'); ?></label><?php endif; ?></div>').appendTo("#images_wrapper");

							jQuery.post(
								js_objects.ajaxurl,
								{'action': 'upload_media_image', 'attachment_id': attachment_id, 'post_id': <?php echo $listing->post->ID; ?>, '_wpnonce': '<?php echo wp_create_nonce('upload_images'); ?>'},
								function (response_from_the_action_function){
									ajax_loader_hide();
								}
							);
						}
					});
				});
				frame.open();
			});
		});
	</script>
	<div id="w2dc-upload-functions" class="w2dc-content" <?php if (count($listing->images) >= $listing->level->images_number): ?>style="display: none;"<?php endif; ?>>
		<div class="w2dc-upload-option">
			<input
				type="button"
				id="upload_image"
				class="w2dc-btn w2dc-btn-primary"
				value="<?php esc_attr_e('Upload image', 'W2DC'); ?>" />
		</div>
	</div>
	<?php else: ?>
	<script>
		function addImageDiv(data) {
			var attachment_url = data.uploaded_file;
			var attachment_id = data.attachment_id;
			jQuery('<div class="w2dc-attached-item"><div class="w2dc-delete-attached-item delete_item" title="<?php esc_attr_e('remove image', 'W2DC'); ?>"></div><input type="hidden" name="attached_image_id[]" value="' + attachment_id + '" /><div class="w2dc-img-div-border" style="width: <?php echo $img_width; ?>px; height: <?php echo $img_height; ?>px"><span class="w2dc-img-div-helper"></span><img src="' + attachment_url + '" style="max-width: <?php echo $img_width; ?>px; max-height: <?php echo $img_height; ?>px" /></div><input type="text" name="attached_image_title[]" class="w2dc-form-control" size="37" /><?php if ($listing->level->logo_enabled): ?><label><input type="radio" name="attached_image_as_logo" value="' + attachment_id + '"> <?php _e('set this image as logo', 'W2DC'); ?></label><?php endif; ?></div>').appendTo("#images_wrapper");
	
			if (images_number <= jQuery("#images_wrapper .w2dc-attached-item").length)
				jQuery("#w2dc-upload-functions").hide();
		}
	</script>
	<div id="w2dc-upload-functions" class="w2dc-content" <?php if (count($listing->images) >= $listing->level->images_number): ?>style="display: none;"<?php endif; ?>>
		<div class="w2dc-upload-option">
			<input id="browse_file" name="browse_file" type="file" size="45" />
		</div>
		<div class="w2dc-upload-option">
			<label><input type="checkbox" id="crop_image" value="1" /> <?php _e('Crop thumbnail to exact dimensions (normally thumbnails are proportional)', 'W2DC'); ?></label>
		</div>
		<div class="w2dc-upload-option">
			<input
				type="button"
				class="w2dc-btn w2dc-btn-primary"
				onclick="return ajaxImageFileUploadToGallery(
					'browse_file',
					addImageDiv,
					jQuery('#crop_image').is(':checked'),
					'<?php echo admin_url('admin-ajax.php?action=upload_image&post_id='.$listing->post->ID.'&_wpnonce='.wp_create_nonce('upload_images')); ?>',
					'<?php echo esc_js(__('Choose image to upload first!', 'W2DC')); ?>'
				);"
				value="<?php esc_attr_e('Upload image', 'W2DC'); ?>" />
		</div>
	</div>
	<?php endif; ?>
</div>
<?php endif; ?>


<?php if ($listing->level->videos_number && get_option('w2dc_google_api_key')): ?>
<script>
	var videos_number = <?php echo $listing->level->videos_number; ?>;

	function attachYoutubeVideo() {
		if (jQuery("#attach_video_input").val()) {
			var regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/;
			var matches = jQuery("#attach_video_input").val().match(regExp);
			if (matches && matches[2].length == 11) {
				var video_id = matches[2];
				jQuery.getJSON('https://www.googleapis.com/youtube/v3/videos?key=<?php echo get_option('w2dc_google_api_key'); ?>'+'&part=snippet&id='+video_id, function(data, status, xhr) {
					if (data.items.length) {
						jQuery('<div class="w2dc-attached-item"><div class="w2dc-delete-attached-item delete_item" title="<?php esc_attr_e('remove video', 'W2DC'); ?>"></div><input type="hidden" name="attached_video_id[]" value="' + video_id + '" /><div class="w2dc-img-div-border" style="width: 120px; height: 90px"><span class="w2dc-img-div-helper"></span><img src="' + data.items[0].snippet.thumbnails.default.url + '" style="max-width: 120px; max-height: 90px" /></div><input type="text" name="attached_video_title[]" class="w2dc-form-control" value="" size="37" /></div>').appendTo("#videos_wrapper");
						jQuery("input[name=attached_video_title\\[\\]]:last").val(data.items[0].snippet.title);
					
					    if (videos_number <= jQuery("#videos_wrapper .w2dc-attached-item").length)
							jQuery("#attach_videos_functions").hide();
					} else
						alert("<?php esc_attr_e('Wrong URL or this videos unavailable', 'W2DC'); ?>");
				});
			} else
				alert("<?php esc_attr_e('Wrong URL or this videos unavailable', 'W2DC'); ?>");
		}
	}

	jQuery(document).ready(function($) {
		jQuery(document).on("click", "#videos_wrapper .delete_item", function() {
			$(this).parent().remove();

			if (videos_number > $("#videos_wrapper .w2dc-attached-item").length)
				$("#attach_videos_functions").show();
		});
	});
</script>

<div id="videos_attach_wrapper">
	<h3>
		<?php _e('Listing videos', 'W2DC'); ?>
	</h3>
	
	<div id="videos_wrapper">
	<?php foreach ($listing->videos AS $video): ?>
		<div class="w2dc-attached-item">
			<div class="w2dc-delete-attached-item delete_item" title="<?php esc_attr_e('remove video', 'W2DC'); ?>"></div>
			<input type="hidden" name="attached_video_id[]" value="<?php echo esc_attr($video['id']); ?>" />
			<div class="w2dc-img-div-border" style="width: 120px; height: 90px">
				<span class="w2dc-img-div-helper"></span><img src="http://i.ytimg.com/vi/<?php echo $video['id']; ?>/default.jpg" style="max-width: 120px; max-height: 90px" />
			</div>
			<input type="text" name="attached_video_title[]" class="w2dc-form-control" value="<?php echo esc_attr($video['caption']); ?>" size="37" />
		</div>
	<?php endforeach; ?>
	</div>
	<div class="clear_float"></div>

	<div id="attach_videos_functions" class="w2dc-content" <?php if (count($listing->videos) >= $listing->level->videos_number): ?>style="display: none;"<?php endif; ?>>
		<div class="w2dc-upload-option">
			<label><?php _e('Enter full YouTube video link', 'W2DC'); ?></label>
		</div>
		<div class="w2dc-upload-option">
			<input type="text" id="attach_video_input" class="w2dc-form-control" style="width: 100%" />
		</div>
		<div class="w2dc-upload-option">
			<input
				type="button"
				class="w2dc-btn w2dc-btn-primary"
				onclick="return attachYoutubeVideo(); "
				value="<?php esc_attr_e('Attach video', 'W2DC'); ?>" />
		</div>
	</div>
</div>
<?php endif; ?>