<div class="w2dc-content">
	<div class="w2dc-directory-frontpanel">
		<?php do_action('w2dc_directory_frontpanel', (isset($listing)) ? $listing : null); ?>
	
		<?php if (get_option('w2dc_favourites_list') && $w2dc_instance->action != 'myfavourites'): ?>
		<button class="w2dc-favourites-link w2dc-btn w2dc-btn-primary" onClick="window.location='<?php echo w2dc_directoryUrl(array('w2dc_action' => 'myfavourites')); ?>';"><span class="w2dc-glyphicon w2dc-glyphicon-star"></span> <?php _e('My bookmarks', 'W2DC'); ?></button>
		<?php endif; ?>
	
		<?php if (isset($listing)): ?>
			<?php if (w2dc_show_edit_button($listing->post->ID)): ?>
			<button class="w2dc-edit-listing-link w2dc-btn w2dc-btn-primary" onClick="window.location='<?php echo w2dc_get_edit_listing_link($listing->post->ID); ?>';"><span class="w2dc-glyphicon w2dc-glyphicon-pencil"></span> <?php _e('Edit listing', 'W2DC'); ?></button>
			<?php endif; ?>
		
			<?php if (get_option('w2dc_print_button')): ?>
			<script>
				var window_width = 860;
				var window_height = 800;
				var leftPosition, topPosition;
			   	leftPosition = (window.screen.width / 2) - ((window_width / 2) + 10);
			   	topPosition = (window.screen.height / 2) - ((window_height / 2) + 50);
			</script>
			<button class="w2dc-print-listing-link w2dc-btn w2dc-btn-primary" onClick="window.open('<?php echo add_query_arg('w2dc_action', 'printlisting', get_permalink($listing->post->ID)); ?>', 'print_window', 'height='+window_height+',width='+window_width+',left='+leftPosition+',top='+topPosition+',menubar=yes,scrollbars=yes');"><span class="w2dc-glyphicon w2dc-glyphicon-print"></span> <?php _e('Print listing', 'W2DC'); ?></button>
			<?php endif; ?>
		
			<?php if (get_option('w2dc_favourites_list')): ?>
			<button class="add_to_favourites w2dc-btn w2dc-btn-primary" listingid="<?php echo $listing->post->ID; ?>"><span class="w2dc-glyphicon w2dc-glyphicon-<?php if (checkQuickList($listing->post->ID)) echo 'heart-empty'; else echo 'heart'; ?>"></span> <span class="w2dc-bookmark-button"><?php if (checkQuickList(get_the_ID())) _e('Remove Bookmark', 'W2DC'); else _e('Add Bookmark', 'W2DC'); ?></span></button>
			<?php endif; ?>
		
			<?php if (get_option('w2dc_pdf_button')): ?>
			<button class="w2dc-pdf-listing-link w2dc-btn w2dc-btn-primary" onClick="window.open('http://pdfmyurl.com/?url=<?php echo urlencode(add_query_arg('w2dc_action', 'pdflisting', get_permalink($listing->post->ID))); ?>');"><span class="w2dc-glyphicon w2dc-glyphicon-save"></span> <?php _e('Save listing in PDF', 'W2DC'); ?></button>
			<?php endif; ?>
		<?php endif; ?>
	</div>
</div>