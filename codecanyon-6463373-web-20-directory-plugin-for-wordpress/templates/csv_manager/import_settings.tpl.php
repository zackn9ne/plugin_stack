<?php w2dc_renderTemplate('admin_header.tpl.php'); ?>

<?php screen_icon('edit-pages'); ?>
<h2><?php _e('CSV Import'); ?></h2>

<p class="description"><?php _e('On this first step select CSV file for import, also you may import images in zip archive', 'W2DC'); ?></p>

<form method="POST" action="" enctype="multipart/form-data">
	<input type="hidden" name="action" value="import_settings">
	<?php wp_nonce_field(W2DC_PATH, 'w2dc_csv_import_nonce');?>
	
	<h3><?php _e('Import settings', 'W2DC'); ?></h3>
	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row">
					<label><?php _e('CSV File', 'W2DC'); ?><span class="w2dc-red-asterisk">*</span></label>
				</th>
				<td>
					<input
						name="csv_file"
						type="file" />
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label><?php _e('Images ZIP archive', 'W2DC'); ?>
				</th>
				<td>
					<input
						name="images_file"
						type="file" />
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label><?php _e('Columns separator', 'W2DC'); ?><span class="w2dc-red-asterisk">*</span></label>
				</th>
				<td>
					<input
						name="columns_separator"
						type="text"
						size="2"
						value="<?php echo isset($columns_separator) ? esc_attr($columns_separator) : ','; ?>" />
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label><?php _e('Images separator', 'W2DC'); ?><span class="w2dc-red-asterisk">*</span></label>
				</th>
				<td>
					<input
						name="images_separator"
						type="text"
						size="2"
						value="<?php echo isset($images_separator) ? esc_attr($images_separator) : ';'; ?>" />
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label><?php _e('Categories/Tags/MultiValues separator', 'W2DC'); ?><span class="w2dc-red-asterisk">*</span></label>
				</th>
				<td>
					<input
						name="categories_separator"
						type="text"
						size="2"
						value="<?php echo isset($categories_separator) ? esc_attr($categories_separator) : ';'; ?>" />
				</td>
			</tr>
		</tbody>
	</table>
	
	<?php w2dc_renderTemplate('csv_manager/import_instructions.tpl.php'); ?>
	
	<?php submit_button(__('Upload', 'W2DC')); ?>
</form>

<?php w2dc_renderTemplate('admin_footer.tpl.php'); ?>