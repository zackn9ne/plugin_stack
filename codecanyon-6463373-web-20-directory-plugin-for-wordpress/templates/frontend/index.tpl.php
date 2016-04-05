		<div class="w2dc-content">
			<?php w2dc_renderMessages(); ?>
			
			<?php w2dc_renderTemplate('frontend/frontpanel_buttons.tpl.php'); ?>

			<?php
			if (get_option('w2dc_main_search'))
				$frontend_controller->search_form->display();
			?>

			<?php if (get_option('w2dc_show_categories_index')): ?>
			<?php w2dc_renderAllCategories(0, get_option('w2dc_categories_nesting_level'), get_option('w2dc_categories_columns'), get_option('w2dc_show_category_count'), get_option('w2dc_subcategories_items')); ?>
			<?php endif; ?>

			<?php if (get_option('w2dc_show_locations_index')): ?>
			<?php w2dc_renderAllLocations(0, get_option('w2dc_locations_nesting_level'), get_option('w2dc_locations_columns'), get_option('w2dc_show_location_count'), get_option('w2dc_sublocations_items')); ?>
			<?php endif; ?>

			<?php if (get_option('w2dc_map_on_index')): ?>
			<?php $frontend_controller->google_map->display(false, false, get_option('w2dc_enable_radius_search_cycle'), get_option('w2dc_enable_clusters'), true, true, false, get_option('w2dc_default_map_height'), false, 10, get_option('w2dc_map_style'), false); ?>
			<?php endif; ?>

			<?php if (get_option('w2dc_listings_on_index')): ?>
			<?php w2dc_renderTemplate('frontend/listings_block.tpl.php', array('frontend_controller' => $frontend_controller)); ?>
			<?php endif; ?>
		</div>