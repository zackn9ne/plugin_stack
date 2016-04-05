<?php w2dc_renderTemplate('admin_header.tpl.php'); ?>

<?php screen_icon('tools'); ?>
<h2>
	<?php _e('Directory settings', 'W2DC'); ?>
</h2>

<?php //w2dc_renderTemplate('donate.tpl.php'); ?>

<div class="w2dc-settings-wrap">
	<h2 class="nav-tab-wrapper">
		<a class="nav-tab <?php if ($section == 'w2dc_settings_page') echo 'nav-tab-active'; ?>" href="<?php echo admin_url('admin.php?page=w2dc_settings')?>"><?php _e('General', 'W2DC'); ?></a>
		<a class="nav-tab <?php if ($section == 'w2dc_listings_settings_page') echo 'nav-tab-active'; ?>" href="<?php echo admin_url('admin.php?page=w2dc_settings&section=w2dc_listings_settings_page')?>"><?php _e('Listings', 'W2DC'); ?></a>
		<a class="nav-tab <?php if ($section == 'w2dc_pagesviews_settings_page') echo 'nav-tab-active'; ?>" href="<?php echo admin_url('admin.php?page=w2dc_settings&section=w2dc_pagesviews_settings_page')?>"><?php _e('Pages & views', 'W2DC'); ?></a>
		<a class="nav-tab <?php if ($section == 'w2dc_maps_settings_page') echo 'nav-tab-active'; ?>" href="<?php echo admin_url('admin.php?page=w2dc_settings&section=w2dc_maps_settings_page')?>"><?php _e('Maps', 'W2DC'); ?></a>
		<a class="nav-tab <?php if ($section == 'w2dc_notifications_settings_page') echo 'nav-tab-active'; ?>" href="<?php echo admin_url('admin.php?page=w2dc_settings&section=w2dc_notifications_settings_page')?>"><?php _e('Email notifications', 'W2DC'); ?></a>
		<a class="nav-tab <?php if ($section == 'w2dc_advanced_settings_page') echo 'nav-tab-active'; ?>" href="<?php echo admin_url('admin.php?page=w2dc_settings&section=w2dc_advanced_settings_page')?>"><?php _e('Advanced', 'W2DC'); ?></a>
		<?php do_action('w2dc_admin_settings_sections', $section); ?>
	</h2>
	
	<form method="POST" action="options.php">
	<?php
		settings_fields($section);
	
		do_settings_sections($section);
	
		submit_button();
	?>
	</form>
</div>
<div class="clear_float"></div>

<?php echo w2dc_renderTemplate('admin_footer.tpl.php'); ?>