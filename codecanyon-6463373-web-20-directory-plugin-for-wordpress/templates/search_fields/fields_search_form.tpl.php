<?php if ($search_fields): ?>
<div class="clear_float"></div>
<script>
	jQuery(document).ready(function($) {
		var fields_in_categories = new Array();
<?php
foreach ($search_fields AS $search_field): 
	if (!$search_field->content_field->isCategories() || $search_field->content_field->categories === array()): ?>
		fields_in_categories[<?php echo $search_field->content_field->id; ?>] = [];
<?php else: ?>
		fields_in_categories[<?php echo $search_field->content_field->id; ?>] = [<?php echo implode(',', $search_field->content_field->categories); ?>];
<?php endif; ?>
<?php endforeach; ?>

		jQuery(document).on("change", ".selected_tax_<?php echo W2DC_CATEGORIES_TAX; ?>", function() {
			hideShowFields($(this).val());
		});

		if ($(".selected_tax_<?php echo W2DC_CATEGORIES_TAX; ?>").val())
			hideShowFields($(".selected_tax_<?php echo W2DC_CATEGORIES_TAX; ?>").val());

		function hideShowFields(id) {
			var selected_categories_ids = [id];

			$(".w2dc-field-search-block-<?php echo $random_id; ?>").hide();
			$.each(fields_in_categories, function(index, value) {
				var show_field = false;
				if (value != undefined) {
					if (value.length > 0)
						for (key in value)
							for (key2 in selected_categories_ids)
								if (value[key] == selected_categories_ids[key2])
									show_field = true;

					if ((value.length == 0 || show_field) && $(".w2dc-field-search-block-"+index+"_<?php echo $random_id; ?>").length)
						$(".w2dc-field-search-block-"+index+"_<?php echo $random_id; ?>").show();
				}
			});
		}
	});
</script>

<div id="w2dc_search_fields_<?php echo $random_id; ?>">
	<div id="w2dc_standart_search_fields_<?php echo $random_id; ?>" class="w2dc_search_fields_block">
		<?php
		foreach ($search_fields AS $search_field)
			if (!$search_field->content_field->advanced_search_form):?>
			<div class="w2dc-search-content-field">
				<?php $search_field->renderSearch($random_id, $columns); ?>
			</div>
			<?php endif; ?>
	</div>
	<div class="clear_float"></div>
	<?php if ($is_advanced_search_panel): ?>
	<input type="hidden" name="use_advanced" id="use_advanced_<?php echo $random_id; ?>" value="<?php echo $use_advanced; ?>" />
	<div id="w2dc_advanced_search_fields_<?php echo $random_id; ?>" <?php if (!$use_advanced): ?>style="display: none;"<?php endif; ?> class="w2dc_search_fields_block">
		<?php
		foreach ($search_fields AS $search_field)
			if ($search_field->content_field->advanced_search_form):?>
			<div class="w2dc-search-content-field">
				<?php $search_field->renderSearch($random_id, $columns); ?>
			</div>
			<?php endif; ?>
	</div>
	<div class="clear_float"></div>
	<?php endif; ?>
</div>
<?php endif; ?>