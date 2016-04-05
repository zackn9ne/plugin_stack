<?php w2dc_renderTemplate('admin_header.tpl.php'); ?>

<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery("#the-list").sortable({
			placeholder: "ui-sortable-placeholder",
			helper: function(e, ui) {
				ui.children().each(function() {
					jQuery(this).width(jQuery(this).width());
				});
				return ui;
			},
			start: function(e, ui){
				ui.placeholder.height(ui.item.height());
			},
			update: function( event, ui ) {
				jQuery("#content_fields_order").val(jQuery(".content_field_weight_id").map(function() {
					return jQuery(this).val();
				}).get());
			}
    	});
	});
</script>

<?php screen_icon('options-general'); ?>
<h2>
	<?php _e('Content fields', 'W2DC'); ?>
	<?php echo sprintf('<a class="add-new-h2" href="?page=%s&action=%s">' . __('Create new field', 'W2DC') . '</a>', $_GET['page'], 'add'); ?>
</h2>
<?php _e('You may order content fields by drag & drop.', 'W2DC'); ?>
<form method="POST" action="<?php echo admin_url('admin.php?page=w2dc_content_fields'); ?>">
	<input type="hidden" id="content_fields_order" name="content_fields_order" value="" />
	<?php 
		$content_fields_table->display();
		
		submit_button(__('Save changes', 'W2DC'));
	?>
</form>
<br />
<br />

<h2>
	<?php _e('Content fields groups', 'W2DC'); ?>
	<?php echo sprintf('<a class="add-new-h2" href="?page=%s&action=%s">' . __('Create new fields group', 'W2DC') . '</a>', $_GET['page'], 'add_group'); ?>
</h2>
<form method="POST" action="<?php echo admin_url('admin.php?page=w2dc_content_fields'); ?>">
	<?php $content_fields_groups_table->display(); ?>
</form>

<?php w2dc_renderTemplate('admin_footer.tpl.php'); ?>