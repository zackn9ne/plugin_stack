		<div class="w2dc-content">
			<?php w2dc_renderMessages(); ?>

			<?php if ($frontend_controller->listings): ?>
			<?php while ($frontend_controller->query->have_posts()): ?>
				<?php $frontend_controller->query->the_post(); ?>
				<?php $listing = $frontend_controller->listings[get_the_ID()]; ?>
				
				<?php w2dc_renderTemplate('frontend/frontpanel_buttons.tpl.php', array('listing' => $listing)); ?>

				<div id="<?php echo $listing->post->post_name; ?>" itemscope itemtype="http://schema.org/LocalBusiness">
					<meta itemprop="url" content="<?php echo the_permalink(); ?>" />
					<?php if ($listing->title()): ?>
					<header class="w2dc-listing-header">
						<h2 itemprop="name"><?php echo $listing->title(); ?></h2><?php do_action('w2dc_listing_title_html', $listing); ?>
						<?php if (get_option('w2dc_share_buttons') && get_option('w2dc_share_buttons_place') == 'title'): ?>
						<?php w2dc_renderTemplate('frontend/sharing_buttons_ajax_call.tpl.php', array('post_id' => $listing->post->ID)); ?>
						<?php endif; ?>
						<?php if ($frontend_controller->breadcrumbs): ?>
						<div class="w2dc-breadcrumbs" itemscope itemtype="http://data-vocabulary.org/Breadcrumb">
							<?php echo $frontend_controller->getBreadCrumbs(); ?>
						</div>
						<?php endif; ?>
					</header>
					<?php endif; ?>

					<article id="post-<?php the_ID(); ?>" class="w2dc-listing">
						<?php if ($listing->logo_image && (!get_option('w2dc_exclude_logo_from_listing') || count($listing->images) > 1)): ?>
						<div class="w2dc-listing-logo-wrap w2dc-single-listing-logo-wrap" id="images">
							<?php do_action('w2dc_listing_pre_logo_wrap_html', $listing); ?>

							<?php
							$images = array();
							foreach ($listing->images AS $attachment_id=>$image) {
								if (!get_option('w2dc_exclude_logo_from_listing') || $listing->logo_image != $attachment_id) {
									$image_src = wp_get_attachment_image_src($attachment_id, 'full');
									if (get_option('w2dc_enable_lighbox_gallery'))
										$images[] = '<a href="' . $image_src[0] . '" data-lightbox="listing_images"><img src="' . $image_src[0] . '" title="' . esc_attr($image['post_title']) . '" /></a>';
									else
										$images[] = '<img src="' . $image_src[0] . '" title="' . esc_attr($image['post_title']) . '" />';
								}
							}
							$max_slides = (count($listing->images) < 5) ? count($listing->images) : 5;
							if (get_option('w2dc_100_single_logo_width'))
								w2dc_renderTemplate('frontend/slider.tpl.php', array(
										'slide_width' => 150,
										'max_width' => false,
										'max_slides' => $max_slides,
										'height' => 450,
										'images' => $images,
										'enable_links' => get_option('w2dc_enable_lighbox_gallery'),
										'auto_slides' => get_option('w2dc_auto_slides_gallery'),
										'auto_slides_delay' => get_option('w2dc_auto_slides_gallery_delay'),
										'random_id' => generateRandomVal()
								));
							else
								w2dc_renderTemplate('frontend/slider.tpl.php', array(
										'slide_width' => 130,
										'max_width' => get_option('w2dc_single_logo_width'),
										'max_slides' => $max_slides,
										'height' => get_option('w2dc_single_logo_width')*0.7,
										'images' => $images,
										'enable_links' => get_option('w2dc_enable_lighbox_gallery'),
										'auto_slides' => get_option('w2dc_auto_slides_gallery'),
										'auto_slides_delay' => get_option('w2dc_auto_slides_gallery_delay'),
										'random_id' => generateRandomVal()
								)); ?>
						</div>
						<?php endif; ?>

						<div class="w2dc-single-listing-text-content-wrap">
							<?php if (get_option('w2dc_share_buttons') && get_option('w2dc_share_buttons_place') == 'before_content'): ?>
							<?php w2dc_renderTemplate('frontend/sharing_buttons_ajax_call.tpl.php', array('post_id' => $listing->post->ID)); ?>
							<?php endif; ?>
						
							<?php do_action('w2dc_listing_pre_content_html', $listing); ?>
					
							<?php $listing->renderContentFields(true); ?>
					
							<?php do_action('w2dc_listing_post_content_html', $listing); ?>
							
							<?php if (get_option('w2dc_share_buttons') && get_option('w2dc_share_buttons_place') == 'after_content'): ?>
							<?php w2dc_renderTemplate('frontend/sharing_buttons_ajax_call.tpl.php', array('post_id' => $listing->post->ID)); ?>
							<?php endif; ?>
						</div>

						<script>
							jQuery(document).ready(function($) {
								<?php if (get_option('w2dc_listings_tabs_order')): ?>
								if (1==2) var x = 1;
								<?php foreach (get_option('w2dc_listings_tabs_order') AS $tab): ?>
								else if ($('#<?php echo $tab; ?>').length)
									w2dc_show_tab($('.w2dc-listing-tabs a[data-tab="#<?php echo $tab; ?>"]'));
								<?php endforeach; ?>
								<?php else: ?>
								w2dc_show_tab($('.w2dc-listing-tabs a:first'));
								<?php endif; ?>
							});
						</script>

						<?php if (
							($fields_groups = $listing->getFieldsGroupsOnTabs())
							|| ($listing->level->google_map && $listing->isMap() && $listing->locations)
							|| (w2dc_comments_open())
							|| ($listing->level->videos_number && $listing->videos)
							|| (get_option('w2dc_listing_contact_form') && (!$listing->is_claimable || !get_option('w2dc_hide_claim_contact_form')))
							): ?>
						<ul class="w2dc-listing-tabs w2dc-nav w2dc-nav-tabs w2dc-clearfix" role="tablist">
							<?php if ($listing->level->google_map && $listing->isMap() && $listing->locations): ?>
							<li><a href="javascript: void(0);" data-tab="#addresses-tab" data-toggle="w2dc-tab" role="tab"><?php _e('Map', 'W2DC'); ?></a></li>
							<?php endif; ?>
							<?php if (w2dc_comments_open()): ?>
							<li><a href="javascript: void(0);" data-tab="#comments-tab" data-toggle="w2dc-tab" role="tab"><?php echo _n('Comment', 'Comments', $listing->post->comment_count, 'W2DC'); ?> (<?php echo $listing->post->comment_count; ?>)</a></li>
							<?php endif; ?>
							<?php if ($listing->level->videos_number && $listing->videos): ?>
							<li><a href="javascript: void(0);" data-tab="#videos-tab" data-toggle="w2dc-tab" role="tab"><?php echo _n('Video', 'Videos', count($listing->videos), 'W2DC'); ?> (<?php echo count($listing->videos); ?>)</a></li>
							<?php endif; ?>
							<?php if (get_option('w2dc_listing_contact_form') && (!$listing->is_claimable || !get_option('w2dc_hide_claim_contact_form')) && ($listing_owner = get_userdata($listing->post->post_author)) && $listing_owner->user_email): ?>
							<li><a href="javascript: void(0);" data-tab="#contact-tab" data-toggle="w2dc-tab" role="tab"><?php _e('Contact', 'W2DC'); ?></a></li>
							<?php endif; ?>
							<?php
							foreach ($fields_groups AS $fields_group): ?>
							<li><a href="javascript: void(0);" data-tab="#field-group-tab-<?php echo $fields_group->id; ?>" data-toggle="w2dc-tab" role="tab"><?php echo $fields_group->name; ?></a></li>
							<?php endforeach; ?>
						</ul>

						<div class="w2dc-tab-content">
							<?php if ($listing->level->google_map && $listing->isMap() && $listing->locations): ?>
							<div id="addresses-tab" class="w2dc-tab-pane w2dc-fade" role="tabpanel">
								<?php $listing->renderMap($frontend_controller->hash, get_option('w2dc_show_directions'), false, get_option('w2dc_enable_radius_search_cycle'), get_option('w2dc_enable_clusters'), false, false); ?>
							</div>
							<?php endif; ?>

							<?php if (w2dc_comments_open()): ?>
							<div id="comments-tab" class="w2dc-tab-pane w2dc-fade" role="tabpanel">
								<?php comments_template('', true); ?>
							</div>
							<?php endif; ?>

							<?php if ($listing->level->videos_number && $listing->videos): ?>
							<div id="videos-tab" class="w2dc-tab-pane w2dc-fade" role="tabpanel">
							<?php foreach ($listing->videos AS $video): ?>
								<iframe width="100%" height="400" class="fitvidsignore" src="http://www.youtube.com/embed/<?php echo $video['id']; ?>" frameborder="0" allowfullscreen></iframe>
							<?php endforeach; ?>
							</div>
							<?php endif; ?>

							<?php if (get_option('w2dc_listing_contact_form') && (!$listing->is_claimable || !get_option('w2dc_hide_claim_contact_form')) && ($listing_owner = get_userdata($listing->post->post_author)) && $listing_owner->user_email): ?>
							<div id="contact-tab" class="w2dc-tab-pane w2dc-fade" role="tabpanel">
							<?php if (defined('WPCF7_VERSION') && get_wpml_dependent_option('w2dc_listing_contact_form_7')): ?>
								<?php echo do_shortcode(get_wpml_dependent_option('w2dc_listing_contact_form_7')); ?>
							<?php else: ?>
								<?php w2dc_renderTemplate('frontend/contact_form.tpl.php', array('listing' => $listing)); ?>
							<?php endif; ?>
							</div>
							<?php endif; ?>
							
							<?php foreach ($fields_groups AS $fields_group): ?>
							<div id="field-group-tab-<?php echo $fields_group->id; ?>" class="w2dc-tab-pane w2dc-fade" role="tabpanel">
								<?php echo $fields_group->renderOutput($listing); ?></a></li>
							</div>
							<?php endforeach; ?>
						</div>
						<?php endif; ?>
					</article>
				</div>
			<?php endwhile; endif; ?>
		</div>