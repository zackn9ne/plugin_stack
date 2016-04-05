<?php if (get_option('w2dc_share_buttons')): ?>
<div class="w2dc-share-buttons">
	<script>
		jQuery(function () {
			jQuery('.w2dc-share-buttons').addClass('w2dc-ajax-loading');
			jQuery.ajax({
				type: "POST",
				url: js_objects.ajaxurl,
				data: {'action': 'w2dc_get_sharing_buttons', 'post_id': <?php echo $post_id; ?>},
				dataType: 'html',
				success: function(response_from_the_action_function){
					if (response_from_the_action_function != 0)
						jQuery('.w2dc-share-buttons').html(response_from_the_action_function);
				},
				complete: function() {
					jQuery('.w2dc-share-buttons').removeClass('w2dc-ajax-loading').css('height', 'auto');
				}
			});
		});
	</script>
</div>
<?php endif; ?>