	<?php if ($frontend_controller->listings): ?>
		<table class="w2dc-table w2dc-table-striped">
			<tr>
				<th class="td_listings_id"><?php _e('ID', 'W2DC'); ?></th>
				<th class="td_listings_title"><?php _e('Title', 'W2DC'); ?></th>
				<?php 
				// adapted for WPML
				global $sitepress;
				if (function_exists('icl_object_id') && $sitepress && get_option('w2dc_enable_frontend_translations') && ($languages = icl_get_languages()) && count($languages) > 1): ?>
				<th class="td_listings_translations">
					<?php foreach ($languages AS $lang_code=>$lang): ?>
					<?php if ($lang_code != ICL_LANGUAGE_CODE && icl_object_id($w2dc_instance->dashboard_page_id, 'page', false, $lang_code)): ?>
					<img src="<?php echo $lang['country_flag_url']; ?>" title="<?php esc_attr_e($lang['native_name']); ?>" />&nbsp;&nbsp;
					<?php endif; ?>
					<?php endforeach; ?>
				</th>
				<?php endif; ?>
				<th class="td_listings_level"><?php _e('Level', 'W2DC'); ?></th>
				<th class="td_listings_status"><?php _e('Status', 'W2DC'); ?></th>
				<th class="td_listings_date"><?php _e('Expiration date', 'W2DC'); ?></th>
				<th class="td_listings_options"></th>
			</tr>
		<?php while ($frontend_controller->query->have_posts()): ?>
			<?php $frontend_controller->query->the_post(); ?>
			<?php $listing = $frontend_controller->listings[get_the_ID()]; ?>
			<tr>
				<td class="td_listings_id"><?php echo $listing->post->ID; ?></td>
				<td class="td_listings_title">
					<?php
					if (w2dc_current_user_can_edit_listing($listing->post->ID))
						echo '<a href="' . w2dc_get_edit_listing_link($listing->post->ID) . '">' . $listing->title() . '</a>';
					else
						echo $listing->title();
					do_action('w2dc_dashboard_listing_title', $listing);
					?>
					<?php if ($listing->post->post_status == 'pending') echo ' - ' . __('Pending', 'W2DC'); ?>
					<?php if ($listing->post->post_status == 'draft') echo ' - ' . __('Draft', 'W2DC'); ?>
					<?php if ($listing->claim && $listing->claim->isClaimed()) echo '<div>' . $listing->claim->getClaimMessage() . ($listing->claim->isOption() ? ' <a href="' . w2dc_dashboardUrl(array('listing_id' => $listing->post->ID, 'w2dc_action' => 'process_claim')) . '">' . __('here', 'W2DC') . '</a>' : '') . '</div>'; ?>
				</td>
				<?php 
				// adapted for WPML
				global $sitepress;
				if (function_exists('icl_object_id') && $sitepress && get_option('w2dc_enable_frontend_translations') && ($languages = icl_get_languages()) && count($languages) > 1): ?>
				<td class="td_listings_translations">
				<?php if (w2dc_current_user_can_edit_listing($listing->post->ID)):
					global $sitepress;
					$trid = $sitepress->get_element_trid($listing->post->ID, 'post_' . W2DC_POST_TYPE);
					$translations = $sitepress->get_element_translations($trid); ?>
					<?php foreach ($languages AS $lang_code=>$lang): ?>
					<?php if ($lang_code != ICL_LANGUAGE_CODE && icl_object_id($w2dc_instance->dashboard_page_id, 'page', false, $lang_code)): ?>
					<?php $lang_details = $sitepress->get_language_details($lang_code); ?>
					<?php if (isset($translations[$lang_code])): ?>
					<a style="text-decoration:none" title="<?php echo sprintf(__('Edit the %s translation', 'sitepress'), $lang_details['display_name']); ?>" href="<?php echo add_query_arg(array('w2dc_action' => 'edit_listing', 'listing_id' => icl_object_id($listing->post->ID, W2DC_POST_TYPE, true, $lang_code)), get_permalink(icl_object_id($w2dc_instance->dashboard_page_id, 'page', true, $lang_code))); ?>">
						<img src="<?php echo ICL_PLUGIN_URL; ?>/res/img/edit_translation.png" alt="<?php esc_attr_e(__('edit translation', 'W2DC')); ?>" />
					</a>&nbsp;&nbsp;
					<?php else: ?>
					<a style="text-decoration:none" title="<?php echo sprintf(__('Add translation to %s', 'sitepress'), $lang_details['display_name']); ?>" href="<?php echo w2dc_dashboardUrl(array('w2dc_action' => 'add_translation', 'listing_id' => $listing->post->ID, 'to_lang' => $lang_code)); ?>">
						<img src="<?php echo ICL_PLUGIN_URL; ?>/res/img/add_translation.png" alt="<?php esc_attr_e(__('add translation', 'W2DC')); ?>" />
					</a>&nbsp;&nbsp;
					<?php endif; ?>
					<?php endif; ?>
					<?php endforeach; ?>
				<?php endif; ?>
				</td>
				<?php endif; ?>
				<td class="td_listings_level">
					<?php if ($listing->level->isUpgradable())
						echo '<a href="' . w2dc_dashboardUrl(array('w2dc_action' => 'upgrade_listing', 'listing_id' => $listing->post->ID)) . '" title="' . esc_attr__('Change level', 'W2DC') . '">'; ?>
					<?php echo $listing->level->name; ?>
					<?php if ($listing->level->isUpgradable())
						echo ' <img src="' . W2DC_RESOURCES_URL . 'images/upgrade.png" class="w2dc-field-icon" /></a>'; ?>
				</td>
				<td class="td_listings_status">
					<?php
					if ($listing->status == 'active')
						echo '<span class="w2dc-badge w2dc-listing-status-active">' . __('active', 'W2DC') . '</span>';
					elseif ($listing->status == 'expired')
						echo '<span class="w2dc-badge w2dc-listing-status-expired">' . __('expired', 'W2DC') . '</span>';
					elseif ($listing->status == 'unpaid')
						echo '<span class="w2dc-badge w2dc-listing-status-unpaid">' . __('unpaid', 'W2DC') . '</span>';
					elseif ($listing->status == 'stopped')
						echo '<span class="w2dc-badge w2dc-listing-status-stopped">' . __('stopped', 'W2DC') . '</span>';
					do_action('w2dc_listing_status_option', $listing);
					?>
				</td>
				<td class="td_listings_date"><?php if ($listing->level->eternal_active_period) _e('Eternal active period', 'W2DC'); else echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), intval($listing->expiration_date)); ?></td>
				<td class="td_listings_options">
					<?php if (w2dc_current_user_can_edit_listing($listing->post->ID)): ?>
					<div class="w2dc-btn-group">
						<a href="<?php echo w2dc_get_edit_listing_link($listing->post->ID); ?>" class="w2dc-btn w2dc-btn-primary w2dc-btn-xs" title="<?php esc_attr_e('edit listing', 'W2DC'); ?>"><span class="w2dc-glyphicon w2dc-glyphicon-edit"></span></a>
						<a href="<?php echo w2dc_dashboardUrl(array('w2dc_action' => 'delete_listing', 'listing_id' => $listing->post->ID)); ?>" class="w2dc-btn w2dc-btn-primary w2dc-btn-xs" title="<?php esc_attr_e('delete listing', 'W2DC'); ?>"><span class="w2dc-glyphicon w2dc-glyphicon-trash"></span></a>
						<?php
						if ($listing->level->raiseup_enabled && $listing->status == 'active' && $listing->post->post_status == 'publish') {
							$raise_up_link = strip_tags(apply_filters('w2dc_raiseup_option', __('raise up listing', 'W2DC'), $listing));
							echo '<a href="' . w2dc_dashboardUrl(array('w2dc_action' => 'raiseup_listing', 'listing_id' => $listing->post->ID)) . '" class="w2dc-btn w2dc-btn-primary w2dc-btn-xs" title="' . esc_attr($raise_up_link) . '"><span class="w2dc-glyphicon w2dc-glyphicon-arrow-up"></span></a>';
						}?>
						<?php
						if ($listing->status == 'expired') {
							$renew_link = strip_tags(apply_filters('w2dc_renew_option', __('renew listing', 'W2DC'), $listing));
							echo '<a href="' . w2dc_dashboardUrl(array('w2dc_action' => 'renew_listing', 'listing_id' => $listing->post->ID)) . '" class="w2dc-btn w2dc-btn-primary w2dc-btn-xs" title="' . esc_attr($renew_link) . '"><span class="w2dc-glyphicon w2dc-glyphicon-refresh"></span></a>';
						}?>
						<?php
						if ($listing->status == 'active' && $listing->post->post_status == 'publish') {
							echo '<a href="' . get_permalink($listing->post->ID) . '" class="w2dc-btn w2dc-btn-primary w2dc-btn-xs" title="' . esc_attr__('view listing', 'W2DC') . '"><span class="w2dc-glyphicon w2dc-glyphicon-link"></span></a>';
						}?>
						<?php do_action('w2dc_dashboard_listing_options', $listing); ?>
					</div>
					<?php endif; ?>
				</td>
			</tr>
		<?php endwhile; ?>
		</table>
		<?php renderPaginator($frontend_controller->query); ?>
		<?php endif; ?>