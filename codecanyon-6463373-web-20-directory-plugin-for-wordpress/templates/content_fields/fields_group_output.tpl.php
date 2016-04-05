<div class="w2dc-fields-group">
	<?php if (!$content_fields_group->on_tab): ?>
	<div class="w2dc-fields-group-caption"><?php echo $content_fields_group->name; ?></div>
	<?php endif; ?>
	<?php if (!$content_fields_group->hide_anonymous ||current_user_can( 'delete_others_posts' )): ?>
		<?php foreach ($content_fields_group->content_fields_array AS $content_field): ?>
			<?php $content_field->renderOutput($listing); ?>
		<?php endforeach; ?>
	<?php elseif ($content_fields_group->hide_anonymous && !current_user_can( 'delete_others_posts' )): ?>
		<?php printf(__('You must be a manager to see this info')); ?>
	<?php endif; ?>
</div>