<?php w2dc_renderTemplate('admin_header.tpl.php'); ?>

<?php screen_icon('edit-pages'); ?>
<h2>
	<?php _e('Configure select/checkbox/radio search field', 'W2DC'); ?>
</h2>

<form method="POST" action="">
	<?php wp_nonce_field(W2DC_PATH, 'w2dc_configure_content_fields_nonce');?>
	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row">
					<label><?php _e('Search input mode', 'W2DC'); ?><span class="w2dc-red-asterisk">*</span></label>
				</th>
				<td>
					<select name="search_input_mode">
						<option value="checkboxes" <?php selected($search_field->search_input_mode, 'checkboxes'); ?>><?php _e('checkboxes', 'W2DC'); ?></option>
						<option value="selectbox" <?php selected($search_field->search_input_mode, 'selectbox'); ?>><?php _e('selectbox', 'W2DC'); ?></option>
						<option value="radiobutton" <?php selected($search_field->search_input_mode, 'radiobutton'); ?>><?php _e('radio buttons', 'W2DC'); ?></option>
					</select>
				</td>
			</tr>
		</tbody>
	</table>
	
	<?php submit_button(__('Save changes', 'W2DC')); ?>
</form>

<?php w2dc_renderTemplate('admin_footer.tpl.php'); ?>