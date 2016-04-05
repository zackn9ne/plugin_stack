<?php w2dc_renderTemplate('admin_header.tpl.php'); ?>

<?php screen_icon('edit-pages'); ?>
<h2>
	<?php _e('Configure select/radio buttons/checkboxes field', 'W2DC'); ?>
</h2>

<script language="JavaScript" type="text/javascript">
	jQuery(document).ready(function($) {
		var max_index = <?php echo ((count(array_keys($content_field->selection_items)) ? max(array_keys($content_field->selection_items)) : 1)); ?>;
		$("#add_selection_item").click(function() {
			max_index = max_index+1;
			$("#selection_items_wrapper").append('<div class="selection_item"><input name="selection_items['+max_index+']" type="text" class="regular-text" value="" /><img class="w2dc-delete-selection-item" src="<?php echo W2DC_RESOURCES_URL . 'images/delete.png'?>" title="<?php esc_attr_e('Remove selection item', 'W2DC')?>" /></div>');
		});
		jQuery(document).on("click", ".w2dc-delete-selection-item", function() {
			$(this).parent().remove();
		});
	});
</script>

<form method="POST" action="">
	<?php wp_nonce_field(W2DC_PATH, 'w2dc_configure_content_fields_nonce');?>
	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row">
					<label><?php _e('Selection items:', 'W2DC'); ?></label>
				</th>
				<td>
					<div id="selection_items_wrapper">
						<?php if (count($content_field->selection_items)): ?>
						<?php foreach ($content_field->selection_items AS $key=>$item): ?>
						<div class="selection_item">
							<input
								name="selection_items[<?php echo $key; ?>]"
								type="text"
								class="regular-text"
								value="<?php echo $item; ?>" />
							<img class="w2dc-delete-selection-item" src="<?php echo W2DC_RESOURCES_URL . 'images/delete.png'?>" title="<?php esc_attr_e('Remove selection item', 'W2DC')?>" />
						</div>
						<?php endforeach; ?>
						<?php else: ?>
						<div class="selection_item">
							<input
								name="selection_items[1]"
								type="text"
								class="regular-text"
								value="" />
							<img class="w2dc-delete-selection-item" src="<?php echo W2DC_RESOURCES_URL . 'images/delete.png'?>" title="<?php esc_attr_e('Remove selection item', 'W2DC')?>" />
						</div>
						<?php endif; ?>
					</div>
				</td>
			</tr>
		</tbody>
	</table>
	<input type="button" id="add_selection_item" class="button button-primary" value="<?php esc_attr_e('Add selection item', 'W2DC'); ?>" />
	
	<?php submit_button(__('Save changes', 'W2DC')); ?>
</form>

<?php w2dc_renderTemplate('admin_footer.tpl.php'); ?>