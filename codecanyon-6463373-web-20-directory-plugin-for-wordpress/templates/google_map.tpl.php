<?php if ($sticky_scroll || $height == '100%'): ?>
<script>
	jQuery(document).ready(function() {
		<?php if ($sticky_scroll): ?>
		jQuery("#w2dc-maps-canvas-<?php echo $unique_map_id; ?>").width(jQuery("#w2dc-maps-canvas-<?php echo $unique_map_id; ?>").parent().width()).css({ 'z-index': 0 });
		
		jQuery("#w2dc-maps-canvas-background-<?php echo $unique_map_id; ?>").position().left = jQuery("#w2dc-maps-canvas-<?php echo $unique_map_id; ?>").position().left;
		jQuery("#w2dc-maps-canvas-background-<?php echo $unique_map_id; ?>").position().top = jQuery("#w2dc-maps-canvas-<?php echo $unique_map_id; ?>").position().top;
		jQuery("#w2dc-maps-canvas-background-<?php echo $unique_map_id; ?>").width(jQuery("#w2dc-maps-canvas-<?php echo $unique_map_id; ?>").width());
		jQuery("#w2dc-maps-canvas-background-<?php echo $unique_map_id; ?>").height(jQuery("#w2dc-maps-canvas-<?php echo $unique_map_id; ?>").height());

		a = function() {
			var b = jQuery(document).scrollTop();
			var d = jQuery("#scroller_anchor_<?php echo $unique_map_id; ?>").offset().top-<?php echo $sticky_scroll_toppadding; ?>;
			var c = jQuery("#w2dc-maps-canvas-<?php echo $unique_map_id; ?>");
			var e = jQuery("#w2dc-maps-canvas-background-<?php echo $unique_map_id; ?>");

			// .scroller_bottom - this is special class used to restrict the area of scroll of map canvas
			if (jQuery(".scroller_bottom").length)
				var f = jQuery(".scroller_bottom").offset().top-(jQuery("#w2dc-maps-canvas-<?php echo $unique_map_id; ?>").height()+<?php echo $sticky_scroll_toppadding; ?>);
			else
				var f = jQuery(document).height();

			if (b>d && b<f) {
				c.css({ position: "fixed", top: "<?php echo $sticky_scroll_toppadding; ?>px" });
				e.css({ position: "relative" });
			} else {
				if (b<=d) {
					c.css({ position: "relative", top: "" });
					e.css({ position: "absolute" });
				}
				if (b>=f) {
					c.css({ position: "absolute" });
					c.offset({ top: f });
					e.css({ position: "absolute" });
				}
			}
		};
		jQuery(window).scroll(a);
		a();
		jQuery("#w2dc-maps-canvas-background-<?php echo $unique_map_id; ?>").css({ position: "absolute" });
		<?php endif; ?>

		<?php if ($height == '100%'): ?>
		jQuery('#w2dc-maps-canvas-<?php echo $unique_map_id; ?>').height(function(index, height) {
			return window.innerHeight - jQuery('#scroller_anchor_<?php echo $unique_map_id; ?>').outerHeight(true) - <?php echo $sticky_scroll_toppadding; ?>;
		});
		jQuery(window).resize(function(){
			jQuery('#w2dc-maps-canvas-<?php echo $unique_map_id; ?>').height(function(index, height) {
				return window.innerHeight - jQuery('#scroller_anchor_<?php echo $unique_map_id; ?>').outerHeight(true) - <?php echo $sticky_scroll_toppadding; ?>;
			});
		});
		<?php endif; ?>
	});
</script>
<?php endif; ?>

<div class="w2dc-content">
<?php if (!$static_image): ?>
	<script>
		map_markers_attrs_array.push(new map_markers_attrs('<?php echo $unique_map_id; ?>', eval(<?php echo $locations_options; ?>), <?php echo ($enable_radius_cycle) ? 1 : 0; ?>, <?php echo ($enable_clusters) ? 1 : 0; ?>, <?php echo ($show_summary_button) ? 1 : 0; ?>, <?php echo ($show_readmore_button) ? 1 : 0; ?>, '<?php echo esc_js($map_style_name); ?>', <?php echo $map_args; ?>));
	</script>

	<?php if ($sticky_scroll || $height == '100%'): ?>
	<div id="scroller_anchor_<?php echo $unique_map_id; ?>"></div> 
	<?php endif; ?>

	<div id="w2dc-maps-canvas-<?php echo $unique_map_id; ?>" class="w2dc-maps-canvas" <?php if ($custom_home): ?>data-custom-home="1"<?php endif; ?> data-shortcode-hash="<?php echo $unique_map_id; ?>" style="width: <?php if ($width) echo $width . 'px'; else echo 'auto'; ?>; height: <?php if ($height) echo $height; else echo '300'; ?>px"></div>

	<?php if ($sticky_scroll): ?>
	<div id="w2dc-maps-canvas-background-<?php echo $unique_map_id; ?>" style="position: relative"></div>
	<?php endif; ?>
	
	<?php if ($show_directions): ?>
	<div class="w2dc-row w2dc-form-group">
		<?php if (get_option('w2dc_directions_functionality') == 'builtin'): ?>
		<label class="w2dc-col-md-12 w2dc-control-label"><?php _e('Get directions from:', 'W2DC'); ?></label>
		<script>
			jQuery(document).ready(function($) {
				<?php if (get_option('w2dc_address_geocode')): ?>
				jQuery(".w2dc-get-location-<?php echo $unique_map_id; ?>").click(function() { geocodeField(jQuery("#from_direction_<?php echo $unique_map_id; ?>"), "<?php echo esc_js(__('GeoLocation service does not work on your device!', 'W2DC')); ?>"); });
				<?php endif; ?>
			});
		</script>
		<?php if (get_option('w2dc_address_geocode')): ?>
		<div class="w2dc-col-md-12 w2dc-has-feedback">
			<input type="text" id="from_direction_<?php echo $unique_map_id; ?>" class="w2dc-form-control <?php if (get_option('w2dc_address_autocomplete')): ?>w2dc-field-autocomplete<?php endif; ?>" placeholder="<?php esc_attr_e('Enter address or zip code', 'W2DC'); ?>" />
			<span class="w2dc-get-location w2dc-get-location-<?php echo $unique_map_id; ?> w2dc-glyphicon w2dc-glyphicon-screenshot w2dc-form-control-feedback" title="<?php esc_attr_e('Get my location', 'W2DC'); ?>"></span>
		</div>
		<?php else: ?>
		<div class="w2dc-col-md-12">
			<input type="text" id="from_direction_<?php echo $unique_map_id; ?>" placeholder="<?php esc_attr_e('Enter address or zip code', 'W2DC'); ?>" class="w2dc-form-control" />
		</div>
		<?php endif; ?>
		<div class="w2dc-col-md-12">
			<?php $i = 1; ?>
			<?php foreach ($locations_array AS $location): ?>
			<div class="w2dc-radio">
				<label>
					<input type="radio" name="select_direction" class="select_direction_<?php echo $unique_map_id; ?>" <?php checked($i, 1); ?> value="<?php esc_attr_e($location->getWholeAddress(false)); ?>" />
					<?php echo $location->getWholeAddress(false); ?>
				</label>
			</div>
			<?php endforeach; ?>
		</div>
		<div class="w2dc-col-md-12">
			<input type="button" class="direction_button front-btn w2dc-btn w2dc-btn-primary" id="get_direction_button_<?php echo $unique_map_id; ?>" value="<?php esc_attr_e('Get directions', 'W2DC'); ?>">
		</div>
		<div class="w2dc-col-md-12">
			<div id="route_<?php echo $unique_map_id; ?>" class="w2dc-maps-direction-route"></div>
		</div>
		<?php elseif (get_option('w2dc_directions_functionality') == 'google'): ?>
		<label class="w2dc-col-md-12 w2dc-control-label"><?php _e('directions to:', 'W2DC'); ?></label>
		<form action="http://maps.google.com/maps" target="_blank">
			<div class="w2dc-col-md-12">
				<?php $i = 1; ?>
				<?php foreach ($locations_array AS $location): ?>
				<div class="w2dc-radio">
					<label>
						<input type="radio" name="q" class="select_direction_<?php echo $unique_map_id; ?>" <?php checked($i, 1); ?> value="<?php esc_attr_e($location->getWholeAddress(false)); ?>" />
						<?php echo $location->getWholeAddress(false); ?>
					</label>
				</div>
				<?php endforeach; ?>
			</div>
			<div class="w2dc-col-md-12">
				<input class="w2dc-btn w2dc-btn-primary" type="submit" value="<?php esc_attr_e('Get directions', 'W2DC'); ?>" />
			</div>
		</form>
		<?php endif; ?>
	</div>
	<?php endif; ?>
<?php else: ?>
	<img src="http://maps.googleapis.com/maps/api/staticmap?size=795x350&<?php foreach ($locations_array  AS $location) { if ($location->map_coords_1 != 0 && $location->map_coords_2 != 0) { ?>markers=<?php if (W2DC_MAP_ICONS_URL && $location->map_icon_file) { ?>icon:<?php echo W2DC_MAP_ICONS_URL . 'icons/' . urlencode($location->map_icon_file) . '%7C'; }?><?php echo $location->map_coords_1 . ',' . $location->map_coords_2 . '&'; }} ?><?php if ($map_zoom) echo 'zoom=' . $map_zoom; ?>&sensor=true<?php if (get_option('w2dc_google_api_key')) echo '&key='.get_option('w2dc_google_api_key'); ?>" />
<?php endif; ?>
</div>