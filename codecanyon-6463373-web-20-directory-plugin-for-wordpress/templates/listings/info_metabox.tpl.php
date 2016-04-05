<div id="misc-publishing-actions">
	<div class="misc-pub-section">
		<label for="post_level"><?php _e('Listing level', 'W2DC'); ?>:</label>
		<span id="post-level-display">
			<?php
			if ($listing->listing_created && $listing->level->isUpgradable())
					echo '<a href="' . admin_url('options.php?page=w2dc_upgrade&listing_id=' . $listing->post->ID) . '">';
			else
				echo '<b>'; ?>
			<?php echo apply_filters('w2dc_create_option', $listing->level->name, $listing); ?>
			<?php
			if ($listing->listing_created && $listing->level->isUpgradable())
				echo '</a>';
			else
				echo '</b>'; ?>
		</span>
	</div>

	<?php if ($listing->listing_created): ?>
	<div class="misc-pub-section">
		<label for="post_level"><?php _e('Listing status', 'W2DC'); ?>:</label>
		<span id="post-level-display">
			<?php if ($listing->status == 'active'): ?>
			<span class="w2dc-badge w2dc-listing-status-active"><?php _e('active', 'W2DC'); ?></span>
			<?php elseif ($listing->status == 'expired'): ?>
			<span class="w2dc-badge w2dc-listing-status-expired"><?php _e('expired', 'W2DC'); ?></span><br />
			<a href="<?php echo admin_url('options.php?page=w2dc_renew&listing_id=' . $listing->post->ID); ?>"><img src="<?php echo W2DC_RESOURCES_URL; ?>images/page_refresh.png" class="w2dc-field-icon" /><?php echo apply_filters('w2dc_renew_option', __('renew listing', 'W2DC'), $listing); ?></a>
			<?php elseif ($listing->status == 'unpaid'): ?>
			<span class="w2dc-badge w2dc-listing-status-unpaid"><?php _e('unpaid ', 'W2DC'); ?></span>
			<?php elseif ($listing->status == 'stopped'): ?>
			<span class="w2dc-badge w2dc-listing-status-stopped"><?php _e('stopped', 'W2DC'); ?></span>
			<?php endif;?>
			<?php do_action('w2dc_listing_status_option', $listing); ?>
		</span>
	</div>

	<div class="misc-pub-section curtime">
		<span id="timestamp">
			<?php _e('Order date', 'W2DC'); ?>:
			<b><?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), intval($listing->order_date)); ?></b>
			<?php if ($listing->level->raiseup_enabled && $listing->status == 'active'): ?>
			<br />
			<a href="<?php echo admin_url('options.php?page=w2dc_raise_up&listing_id=' . $listing->post->ID); ?>"><img src="<?php echo W2DC_RESOURCES_URL; ?>images/raise_up.png" class="w2dc-field-icon" /><?php echo apply_filters('w2dc_raiseup_option', __('raise up listing', 'W2DC'), $listing); ?></a>
			<?php endif; ?>
		</span>
	</div>

	<?php if ($listing->level->eternal_active_period || $listing->expiration_date): ?>
	<div class="misc-pub-section curtime">
		<span id="timestamp">
			<?php _e('Expiry on', 'W2DC'); ?>:
			<?php if ($listing->level->eternal_active_period): ?>
			<b><?php _e('Eternal active period', 'W2DC'); ?></b>
			<?php else: ?>
			<b><?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), intval($listing->expiration_date)); ?></b>
			<?php endif; ?>
		</span>
	</div>
	<?php endif; ?>

	<?php endif; ?>
</div>