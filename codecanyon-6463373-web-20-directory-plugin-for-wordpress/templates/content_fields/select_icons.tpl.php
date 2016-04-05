		<input type="button" id="reset_icon" class="button button-primary button-large" value="<?php esc_attr_e('Reset icon image', 'W2DC'); ?>" />

		<div class="w2dc-icons-theme-block">
		<?php foreach ($custom_fields_icons AS $icon): ?>
			<span class="w2dc-icon w2dc-fa w2dc-fa-lg <?php echo $icon; ?>" id="<?php echo $icon; ?>" title="<?php echo $icon; ?>"></span>
		<?php endforeach;?>
		</div>
		<div class="clear_float"></div>