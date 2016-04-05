<div class="w2dc-content">
	<?php w2dc_renderMessages(); ?>

	<?php if (count($w2dc_instance->levels->levels_array) > 1): ?>
	<h2><?php echo sprintf(apply_filters('w2dc_create_option', __('Create new listing in level "%s"', 'W2DC'), $w2dc_instance->current_listing), $w2dc_instance->current_listing->level->name); ?></h2>
	<?php endif; ?>

	<?php if ($frontend_controller->args['show_steps']): ?>
	<?php if ((count($w2dc_instance->levels->levels_array) > 1) || (get_option('w2dc_fsubmit_login_mode') == 1 && !is_user_logged_in())): ?>
	<div class="w2dc-submit-section-adv">
		<?php $step = 1; ?>

		<?php if (count($w2dc_instance->levels->levels_array) > 1): ?>
		<div class="w2dc-adv-step">
			<div class="w2dc-adv-circle w2dc-adv-circle-passed"><?php _e('Step', 'W2DC'); ?> <?php echo $step++; ?></div>
			<?php _e('Choose level', 'W2DC'); ?>
		</div>
		<div class="w2dc-adv-line w2dc-adv-line-passed"></div>
		<?php endif; ?>

		<?php if (get_option('w2dc_fsubmit_login_mode') == 1 && !is_user_logged_in()): ?>
		<div class="w2dc-adv-step">
			<div class="w2dc-adv-circle w2dc-adv-circle-passed"><?php _e('Step', 'W2DC'); ?> <?php echo $step++; ?></div>
			<?php _e('Login', 'W2DC'); ?>
		</div>
		<div class="w2dc-adv-line w2dc-adv-line-passed"></div>
		<?php endif; ?>

		<div class="w2dc-adv-step w2dc-adv-step-active">
			<div class="w2dc-adv-circle w2dc-adv-circle-active"><?php _e('Step', 'W2DC'); ?> <?php echo $step++; ?></div>
			<?php _e('Create listing', 'W2DC'); ?>
		</div>
		
		<?php $step = apply_filters('w2dc_create_listings_steps_html', $step, $w2dc_instance->current_listing->level); ?>

		<div class="clear_float"></div>
	</div>
	<?php endif; ?>
	<?php endif; ?>

	<form action="<?php echo w2dc_submitUrl(array('level' => $w2dc_instance->current_listing->level->id)); ?>" method="POST">
		<input type="hidden" name="listing_id" value="<?php echo $w2dc_instance->current_listing->post->ID; ?>" />
		<input type="hidden" name="listing_id_hash" value="<?php echo md5($w2dc_instance->current_listing->post->ID . wp_salt()); ?>" />

		<?php if (!is_user_logged_in() && (get_option('w2dc_fsubmit_login_mode') == 2 || get_option('w2dc_fsubmit_login_mode') == 3)): ?>
		<div class="w2dc-submit-section">
			<h3 class="w2dc-submit-section-label"><?php _e('Contact info', 'W2DC'); ?></h3>
			<div class="w2dc-submit-section-inside">
				<label class="w2dc-fsubmit-contact"><?php _e('Contact Name', 'W2DC'); ?><?php if (get_option('w2dc_fsubmit_login_mode') == 2): ?><span class="w2dc-red-asterisk">*</span><?php endif; ?></label>
				<input type="text" name="w2dc_user_contact_name" value="<?php echo esc_attr($frontend_controller->w2dc_user_contact_name); ?>" class="w2dc-form-control" style="width: 100%;" />

				<label class="w2dc-fsubmit-contact"><?php _e('Contact Email', 'W2DC'); ?><?php if (get_option('w2dc_fsubmit_login_mode') == 2): ?><span class="w2dc-red-asterisk">*</span><?php endif; ?></label>
				<input type="text" name="w2dc_user_contact_email" value="<?php echo esc_attr($frontend_controller->w2dc_user_contact_email); ?>" class="w2dc-form-control" style="width: 100%;" />
			</div>
		</div>
		<?php endif; ?>

		<div class="w2dc-submit-section">
			<h3 class="w2dc-submit-section-label"><?php _e('Listing title', 'W2DC'); ?><span class="w2dc-red-asterisk">*</span></h3>
			<div class="w2dc-submit-section-inside">
				<input type="text" name="post_title" style="width: 100%" class="w2dc-form-control" value="<?php if ($w2dc_instance->current_listing->post->post_title != __('Auto Draft')) echo esc_attr($w2dc_instance->current_listing->post->post_title); ?>" />
			</div>
		</div>

		<?php if (post_type_supports(W2DC_POST_TYPE, 'editor')): ?>
		<div class="w2dc-submit-section">
			<h3 class="w2dc-submit-section-label"><?php echo $w2dc_instance->content_fields->getContentFieldBySlug('content')->name; ?><?php if ($w2dc_instance->content_fields->getContentFieldBySlug('content')->is_required): ?><span class="w2dc-red-asterisk">*</span><?php endif; ?></h3>
			<div class="w2dc-submit-section-inside">
				<?php wp_editor($w2dc_instance->current_listing->post->post_content, 'post_content', array('media_buttons' => false, 'editor_class' => 'w2dc-editor-class')); ?>
			</div>
		</div>
		<?php endif; ?>

		<?php if (post_type_supports(W2DC_POST_TYPE, 'excerpt')): ?>
		<div class="w2dc-submit-section">
			<h3 class="w2dc-submit-section-label"><?php echo $w2dc_instance->content_fields->getContentFieldBySlug('summary')->name; ?><?php if ($w2dc_instance->content_fields->getContentFieldBySlug('summary')->is_required): ?><span class="w2dc-red-asterisk">*</span><?php endif; ?></h3>
			<div class="w2dc-submit-section-inside">
				<textarea name="post_excerpt" class="w2dc-editor-class w2dc-form-control" rows="4"><?php echo esc_textarea($w2dc_instance->current_listing->post->post_excerpt)?></textarea>
			</div>
		</div>
		<?php endif; ?>
		
		<?php do_action('w2dc_create_listing_metaboxes_pre', $w2dc_instance->current_listing); ?>

		<?php if (!$w2dc_instance->current_listing->level->eternal_active_period && (get_option('w2dc_change_expiration_date') || current_user_can('manage_options'))): ?>
		<div class="w2dc-submit-section">
			<h3 class="w2dc-submit-section-label"><?php _e('Listing expiration date', 'W2DC'); ?></h3>
			<div class="w2dc-submit-section-inside">
				<?php $w2dc_instance->listings_manager->listingExpirationDateMetabox($w2dc_instance->current_listing->post); ?>
			</div>
		</div>
		<?php endif; ?>
		
		<?php if (get_option('w2dc_claim_functionality') && !get_option('w2dc_hide_claim_metabox')): ?>
		<div class="w2dc-submit-section">
			<h3 class="w2dc-submit-section-label"><?php _e('Listing claim', 'W2DC'); ?></h3>
			<div class="w2dc-submit-section-inside">
				<?php $w2dc_instance->listings_manager->listingClaimMetabox($w2dc_instance->current_listing->post); ?>
			</div>
		</div>
		<?php endif; ?>
	
		<?php if ($w2dc_instance->current_listing->level->categories_number > 0 || $w2dc_instance->current_listing->level->unlimited_categories): ?>
		<div class="w2dc-submit-section">
			<h3 class="w2dc-submit-section-label"><?php echo $w2dc_instance->content_fields->getContentFieldBySlug('categories_list')->name; ?></h3>
			<div class="w2dc-submit-section-inside">
				<div class="w2dc-categories-tree-panel w2dc-editor-class" id="<?php echo W2DC_CATEGORIES_TAX; ?>-all">
					<?php w2dc_terms_checklist($w2dc_instance->current_listing->post->ID); ?>
				</div>
			</div>
		</div>
		<?php endif; ?>
		
		<?php if (get_option('w2dc_enable_tags')): ?>
		<div class="w2dc-submit-section">
			<h3 class="w2dc-submit-section-label"><?php echo $w2dc_instance->content_fields->getContentFieldBySlug('listing_tags')->name; ?> <i>(<?php _e('select existing or type new', 'W2DC'); ?>)</i></h3>
			<div class="w2dc-submit-section-inside">
				<?php w2dc_tags_selectbox($w2dc_instance->current_listing->post->ID); ?>
			</div>
		</div>
		<?php endif; ?>
	
		<?php if ($w2dc_instance->content_fields->isNotCoreContentFields()): ?>
		<div class="w2dc-submit-section">
			<div class="w2dc-submit-section-inside">
				<?php $w2dc_instance->content_fields_manager->contentFieldsMetabox($w2dc_instance->current_listing->post); ?>
			</div>
		</div>
		<?php endif; ?>
	
		<?php if ($w2dc_instance->current_listing->level->images_number > 0 || $w2dc_instance->current_listing->level->videos_number > 0): ?>
		<div class="w2dc-submit-section">
			<h3 class="w2dc-submit-section-label"><?php _e('Listing Media', 'W2DC'); ?></h3>
			<div class="w2dc-submit-section-inside">
				<?php $w2dc_instance->media_manager->mediaMetabox(); ?>
			</div>
		</div>
		<?php endif; ?>
	
		<?php if ($w2dc_instance->current_listing->level->locations_number > 0): ?>
		<div class="w2dc-submit-section">
			<h3 class="w2dc-submit-section-label"><?php _e('Listing locations', 'W2DC'); ?></h3>
			<div class="w2dc-submit-section-inside">
				<?php $w2dc_instance->locations_manager->listingLocationsMetabox($w2dc_instance->current_listing->post); ?>
			</div>
		</div>
		<?php endif; ?>
		
		<?php do_action('w2dc_create_listing_metaboxes_post', $w2dc_instance->current_listing); ?>

		<?php if (get_option('w2dc_enable_recaptcha')): ?>
		<div class="w2dc-submit-section-adv">
			<?php echo w2dc_recaptcha(); ?>
		</div>
		<?php endif; ?>

		<?php
		if ($tos_page = get_wpml_dependent_option('w2dc_tospage')) : ?>
		<div class="w2dc-submit-section-adv">
			<label><input type="checkbox" name="w2dc_tospage" value="1" /> <?php printf(__('I agree to the <a href="%s" target="_blank">Terms of Services</a>', 'W2DC'), get_permalink($tos_page)); ?></label>
		</div>
		<?php endif; ?>

		<?php require_once(ABSPATH . 'wp-admin/includes/template.php'); ?>
		<?php submit_button(__('Submit new listing', 'W2DC'), 'w2dc-btn w2dc-btn-primary')?>
	</form>
</div>