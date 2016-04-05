<?php w2dc_renderTemplate('admin_header.tpl.php'); ?>

<?php screen_icon('edit-pages'); ?>
<h2>
	<?php _e('Web 2.0 Directory plugin', 'W2DC'); ?>
</h2>

<?php //w2dc_renderTemplate('donate.tpl.php'); ?>

<div class="w2dc-settings-wrap">
	<table class="form-table">
			<tbody>
				<tr class="w2dc-index-setting-row">
					<th scope="row">
						<a href="<?php echo admin_url('edit.php?post_type=w2dc_listing'); ?>"><img src="<?php echo W2DC_RESOURCES_URL; ?>images/icons_by_Designmodo/search.png" title="<?php esc_attr_e('View all listings', 'W2DC'); ?>" /></a>
					</th>
					<td>
						<?php _e('<b>Manage your directory listings.</b> You may manage any listings, comments, attached images and videos. According to listings levels settings some items may be raised up, expired items may be renewed manually.', 'W2DC'); ?>
					</td>
				</tr>
				<tr class="w2dc-index-setting-row">
					<th scope="row">
						<a href="<?php echo admin_url('admin.php?page=w2dc_settings'); ?>"><img src="<?php echo W2DC_RESOURCES_URL; ?>images/icons_by_Designmodo/settings.png" title="<?php esc_attr_e('Directory settings', 'W2DC'); ?>" /></a>
					</th>
					<td>
						<?php _e('<b>Manage main directory settings.</b> Configure general settings, listings, maps, directory pages, listings views, email notifications, search form and much more.', 'W2DC'); ?>
					</td>
				</tr>
				<tr class="w2dc-index-setting-row">
					<th scope="row">
						<a href="<?php echo admin_url('admin.php?page=w2dc_levels'); ?>"><img src="<?php echo W2DC_RESOURCES_URL; ?>images/icons_by_Designmodo/params.png" title="<?php esc_attr_e('Listings levels', 'W2DC'); ?>" /></a>
					</th>
					<td>
						<?php _e('Levels of listings control the functionality amount of listings and their <b>directory/classifieds conception</b>. Each listing may belong to different levels, some may have eternal active period, have sticky status and enabled google maps, other may have greater number of allowed attached images or videos.', 'W2DC'); ?>
					</td>
				</tr>
				<tr class="w2dc-index-setting-row">
					<th scope="row">
						<a href="<?php echo admin_url('edit-tags.php?taxonomy=w2dc-location&post_type=w2dc_listing'); ?>"><img src="<?php echo W2DC_RESOURCES_URL; ?>images/icons_by_Designmodo/location.png" title="<?php esc_attr_e('Directory locations', 'W2DC'); ?>" /></a>
					</th>
					<td>
						<?php _e('<b>Manage directory locations.</b> Fill in locations tree with countries, states, cities.', 'W2DC'); ?>
					</td>
				</tr>
				<tr class="w2dc-index-setting-row">
					<th scope="row">
						<a href="<?php echo admin_url('admin.php?page=w2dc_locations_levels'); ?>"><img src="<?php echo W2DC_RESOURCES_URL; ?>images/icons_by_Designmodo/world.png" title="<?php esc_attr_e('Locations levels', 'W2DC'); ?>" /></a>
					</th>
					<td>
						<?php _e('By default there are 3 locations levels: country, state and city. Remove existed or set up new locations levels.', 'W2DC'); ?>
					</td>
				</tr>
				<tr class="w2dc-index-setting-row">
					<th scope="row">
						<a href="<?php echo admin_url('admin.php?page=w2dc_content_fields'); ?>"><img src="<?php echo W2DC_RESOURCES_URL; ?>images/icons_by_Designmodo/calendar.png" title="<?php esc_attr_e('Content fields', 'W2DC'); ?>" /></a>
					</th>
					<td>
						<?php _e('Set up content fields for listings. Each field has type that defines its behaviour. You may hide field name, select custom field icon, set field as required, manage visibility on pages. Also listings may be ordered by some fields. Note that you may <b>assign fields for specific categories</b>.', 'W2DC'); ?>
					</td>
				</tr>
				<tr class="w2dc-index-setting-row">
					<th scope="row">
						<a href="<?php echo admin_url('edit-tags.php?taxonomy=w2dc-category&post_type=w2dc_listing'); ?>"><img src="<?php echo W2DC_RESOURCES_URL; ?>images/icons_by_Designmodo/news.png" title="<?php esc_attr_e('Directory categories', 'W2DC'); ?>" /></a>
					</th>
					<td>
						<?php _e('Manage directory categories.', 'W2DC'); ?>
					</td>
				</tr>
				<tr class="w2dc-index-setting-row">
					<th scope="row">
						<a href="<?php echo admin_url('edit-tags.php?taxonomy=w2dc-tag&post_type=w2dc_listing'); ?>"><img src="<?php echo W2DC_RESOURCES_URL; ?>images/icons_by_Designmodo/pen.png" title="<?php esc_attr_e('Directory tags', 'W2DC'); ?>" /></a>
					</th>
					<td>
						<?php _e('Manage directory tags.', 'W2DC'); ?>
					</td>
				</tr>
				<?php do_action('w2dc_admin_index_html'); ?>
			</tbody>
	</table>
</div>
<div class="clear_float"></div>

<?php w2dc_renderTemplate('admin_footer.tpl.php'); ?>