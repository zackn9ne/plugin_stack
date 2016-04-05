<?php if ($content_field->value): ?>
<div class="w2dc-field w2dc-field-output-block w2dc-field-output-block-<?php echo $content_field->type; ?> w2dc-field-output-block-<?php echo $content_field->id; ?>">
	<?php if ($content_field->icon_image || !$content_field->is_hide_name): ?>
	<span class="w2dc-field-caption">
		<?php if ($content_field->icon_image): ?>
		<span class="w2dc-field-icon w2dc-fa w2dc-fa-lg <?php echo $content_field->icon_image; ?>"></span>
		<?php endif; ?>
		<?php if (!$content_field->is_hide_name): ?>
		<span class="w2dc-field-name"><?php echo $content_field->name?>:</span>
		<?php endif; ?>
	</span>
	<?php endif; ?>
	<span class="w2dc-field-content">
		<?php if ($content_field->do_shortcodes): ?>
		<?php echo apply_filters('the_content', $content_field->value); ?>
		<?php else: ?>
		<?php remove_filter('the_content', 'do_shortcode', 11); ?>
		<?php echo apply_filters('the_content', $content_field->value); ?>
		<?php add_filter('the_content', 'do_shortcode', 11); ?>
		<?php endif; ?>
	</span>
</div>
<?php endif; ?>