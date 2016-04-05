			<script>
				jQuery(document).ready( function() {
					var color_scheme = jQuery('#w2dc_color_scheme');
					jQuery('.w2dc-color-option').click( function() {
						if (jQuery(this).hasClass('selected'))
							return;

						var selected_color_scheme = jQuery(this).find(".css_name").val()
						jQuery(this).siblings('.selected').removeClass('selected');
						jQuery(this).addClass('selected');

						jQuery.cookie("w2dc_compare_palettes", selected_color_scheme, {expires: 1, path: "/"});
						if (color_scheme.length)
							color_scheme.val(selected_color_scheme);

						jQuery.post(
								js_objects.ajaxurl,
								{'action': 'w2dc_generate_color_palette'},
								function(response_from_the_action_function) {
									if (response_from_the_action_function != 0) {
										jQuery("head").append('<style>'+response_from_the_action_function+'</style>');
									}
								}
						);
						jQuery.post(
								js_objects.ajaxurl,
								{'action': 'get_jqueryui_theme'},
								function(jqueryui_css_theme_url) {
									if (jqueryui_css_theme_url != 0) {
										jQuery("#jquery-ui-style-css").attr('href', jqueryui_css_theme_url);
									}
								}
						);
					});
				});
			</script>

			<input type="hidden" id="w2dc_color_scheme" name="w2dc_color_scheme" value="<?php echo $selected_scheme; ?>" />
			<div class="w2dc-color-option color-option <?php if ($selected_scheme == 'default') echo 'selected'; ?>">
				<input type="hidden" class="css_name" value="default" />
				<div class="w2dc-color-scheme-name"><?php _e('Default', 'W2DC'); ?></div>
				<table class="color-palette">
					<tr>
						<td style="background-color: #a5ffff">&nbsp;</td>
						<td style="background-color: #47c6c6">&nbsp;</td>
						<td style="background-color: #17607a">&nbsp;</td>
						<td style="background-color: #2393ba">&nbsp;</td>
					</tr>
				</table>
			</div>
			<div class="w2dc-color-option color-option <?php if ($selected_scheme == 'orange') echo 'selected'; ?>">
				<input type="hidden" class="css_name" value="orange" />
				<div class="w2dc-color-scheme-name"><?php _e('Orange', 'W2DC'); ?></div>
				<table class="color-palette">
					<tr>
						<td style="background-color: #ff8000">&nbsp;</td>
						<td style="background-color: #ff6600">&nbsp;</td>
						<td style="background-color: #000000">&nbsp;</td>
						<td style="background-color: #4d4d4d">&nbsp;</td>
					</tr>
				</table>
			</div>
			<div class="w2dc-color-option color-option <?php if ($selected_scheme == 'red') echo 'selected'; ?>">
				<input type="hidden" class="css_name" value="red" />
				<div class="w2dc-color-scheme-name"><?php _e('Red-Blue', 'W2DC'); ?></div>
				<table class="color-palette">
					<tr>
						<td style="background-color: #ed4e6e">&nbsp;</td>
						<td style="background-color: #cb4862">&nbsp;</td>
						<td style="background-color: #476583">&nbsp;</td>
						<td style="background-color: #679acd">&nbsp;</td>
					</tr>
				</table>
			</div>
			<div class="w2dc-color-option color-option <?php if ($selected_scheme == 'yellow') echo 'selected'; ?>">
				<input type="hidden" class="css_name" value="yellow" />
				<div class="w2dc-color-scheme-name"><?php _e('Yellow', 'W2DC'); ?></div>
				<table class="color-palette">
					<tr>
						<td style="background-color: #ffff00">&nbsp;</td>
						<td style="background-color: #e0e000">&nbsp;</td>
						<td style="background-color: #868600">&nbsp;</td>
						<td style="background-color: #d2d300">&nbsp;</td>
					</tr>
				</table>
			</div>
			<div class="w2dc-color-option color-option <?php if ($selected_scheme == 'green') echo 'selected'; ?>">
				<input type="hidden" class="css_name" value="green" />
				<div class="w2dc-color-scheme-name"><?php _e('Green', 'W2DC'); ?></div>
				<table class="color-palette">
					<tr>
						<td style="background-color: #7fff00">&nbsp;</td>
						<td style="background-color: #33cc00">&nbsp;</td>
						<td style="background-color: #7f8778">&nbsp;</td>
						<td style="background-color: #a5aba1">&nbsp;</td>
					</tr>
				</table>
			</div>
			<div class="w2dc-color-option color-option <?php if ($selected_scheme == 'gray') echo 'selected'; ?>">
				<input type="hidden" class="css_name" value="gray" />
				<div class="w2dc-color-scheme-name"><?php _e('Gray', 'W2DC'); ?></div>
				<table class="color-palette">
					<tr>
						<td style="background-color: #cfdbc5">&nbsp;</td>
						<td style="background-color: #acc7a6">&nbsp;</td>
						<td style="background-color: #236b8e">&nbsp;</td>
						<td style="background-color: #3299cb">&nbsp;</td>
					</tr>
				</table>
			</div>
			<div class="w2dc-color-option color-option <?php if ($selected_scheme == 'blue') echo 'selected'; ?>">
				<input type="hidden" class="css_name" value="blue" />
				<div class="w2dc-color-scheme-name"><?php _e('Blue', 'W2DC'); ?></div>
				<table class="color-palette">
					<tr>
						<td style="background-color: #499df5">&nbsp;</td>
						<td style="background-color: #194df2">&nbsp;</td>
						<td style="background-color: #6c7b8b">&nbsp;</td>
						<td style="background-color: #96a1ad">&nbsp;</td>
					</tr>
				</table>
			</div>
			<div class="clear_float"></div>