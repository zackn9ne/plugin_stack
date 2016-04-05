<?php w2dc_renderTemplate('admin_header.tpl.php'); ?>

<?php screen_icon('edit-pages'); ?>
<h2><?php _e('CSV Import'); ?></h2>

<h3><?php _e('Import results', 'W2DC'); ?></h3>

<?php if ($log['messages']): ?>
<div class="updated">
<?php foreach ($log['messages'] AS $message): ?>
<p><?php echo $message; ?></p>
<?php endforeach; ?>
</div>
<?php endif; ?>

<?php if ($log['errors']): ?>
<div class="error">
<?php foreach ($log['errors'] AS $error): ?>
<p><?php echo $error; ?></p>
<?php endforeach; ?>
</div>
<?php endif; ?>

<form method="POST" action="">
	<input type="hidden" name="action" value="import_settings">
	<input type="hidden" name="csv_file_name" value="<?php echo esc_attr($csv_file_name); ?>">
	<input type="hidden" name="images_dir" value="<?php echo esc_attr($images_dir); ?>">
	<input type="hidden" name="columns_separator" value="<?php echo esc_attr($columns_separator); ?>">
	<input type="hidden" name="images_separator" value="<?php echo esc_attr($images_separator); ?>">
	<input type="hidden" name="categories_separator" value="<?php echo esc_attr($categories_separator); ?>">
	<input type="hidden" name="category_not_found" value="<?php echo esc_attr($category_not_found); ?>">
	<input type="hidden" name="listings_author" value="<?php echo esc_attr($listings_author); ?>">
	<input type="hidden" name="do_geocode" value="<?php echo esc_attr($do_geocode); ?>">
	<?php if (get_option('w2dc_fsubmit_addon') && get_option('w2dc_claim_functionality')): ?>
	<input type="hidden" name="is_claimable" value="<?php echo esc_attr($is_claimable); ?>">
	<?php endif; ?>
	<?php foreach ($fields AS $field): ?>
	<input type="hidden" name="fields[]" value="<?php echo esc_attr($field); ?>">
	<?php endforeach; ?>
	<?php wp_nonce_field(W2DC_PATH, 'w2dc_csv_import_nonce');?>

	<?php if ($log['errors'] || $test_mode): ?>
	<?php submit_button(__('Go back', 'W2DC'), 'primary', 'goback', false); ?>
	&nbsp;&nbsp;&nbsp;
	<?php endif; ?>

	<a href="<?php echo admin_url('admin.php?page=w2dc_csv_import'); ?>" class="button button-primary"><?php _e('Import new file', 'W2DC'); ?></a>
</form>

<?php w2dc_renderTemplate('admin_footer.tpl.php'); ?>