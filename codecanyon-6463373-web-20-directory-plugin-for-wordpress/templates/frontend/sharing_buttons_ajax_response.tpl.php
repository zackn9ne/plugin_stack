<?php global $w2dc_social_services; ?>
<script>
	jQuery(function () { jQuery('.w2dc-share-button [data-toggle="w2dc-tooltip"]').w2dc_tooltip() });
</script>
<?php foreach (get_option('w2dc_share_buttons') AS $button): ?>
<div class="w2dc-share-button">
	<?php w2dc_renderSharingButton($post_id, $button); ?>
</div>
<?php endforeach; ?>