	<script>
		jQuery(document).ready( function() {
			jQuery(document).on('mouseenter', '.w2dc-no-touch #w2dc-color-picker-panel', function () {
				jQuery('#w2dc-color-picker-panel').stop().animate({left: "0px"}, 500);
			});
			jQuery(document).on('mouseleave', '.w2dc-no-touch #w2dc-color-picker-panel', function () {
				var width = jQuery('#w2dc-color-picker-panel').width() - 50;
				jQuery('#w2dc-color-picker-panel').stop().animate({left: - width}, 500);
			});

			var panel_opened = false;
			jQuery('.w2dc-touch #w2dc-color-picker-panel-tools').click(function () {
				if (panel_opened) {
					var width = jQuery('#w2dc-color-picker-panel').width() - 50;
					jQuery('#w2dc-color-picker-panel').stop().animate({left: - width}, 500);
					panel_opened = false;
				} else {
					jQuery('#w2dc-color-picker-panel').stop().animate({left: "0px"}, 500);
					panel_opened = true;
				}
			});
		});
	</script>
	<div id="w2dc-color-picker-panel" class="w2dc-content">
		<fieldset id="w2dc-color-picker">
			<label><?php _e('Choose color palette:'); ?></label>
			<?php $selected_scheme = (isset($_COOKIE['w2dc_compare_palettes']) ? $_COOKIE['w2dc_compare_palettes'] : get_option('w2dc_color_scheme')); ?>
			<?php w2dc_renderTemplate('color_picker/color_picker_settings.tpl.php', array('selected_scheme' => $selected_scheme)); ?>
			<label><?php printf(__('Return to the <a href="%s">backend</a>', 'W2DC'), admin_url('admin.php?page=w2dc_settings#_customization')); ?></label>
		</fieldset>
		<div id="w2dc-color-picker-panel-tools" class="clearfix">
			<img src="<?php echo W2DC_RESOURCES_URL . 'images/icons_by_Designmodo/settings.png'; ?>" />
		</div>
	</div>