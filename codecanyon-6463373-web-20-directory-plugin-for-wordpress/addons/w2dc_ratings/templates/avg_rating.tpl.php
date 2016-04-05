<div class="w2dc-rating" <?php if ($listing->avg_rating->ratings_count): ?>itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating"<?php endif; ?>>
	<meta itemprop="reviewCount" content="<?php echo get_comments_number(); ?>" />
	<?php if ($listing->avg_rating->ratings_count): ?>
	<meta itemprop="ratingValue" content="<?php echo $listing->avg_rating->avg_value; ?>" />
	<meta itemprop="ratingCount" content="<?php echo $listing->avg_rating->ratings_count; ?>" />
	<?php endif; ?>
	<?php if (!is_admin() || (defined('DOING_AJAX') && DOING_AJAX)): ?>
	<script type="text/javascript">
		jQuery(document).ready(function() {
			<?php if ($active): ?>
			jQuery(function() {
				jQuery("#rater-<?php echo $listing->post->ID; ?>").rater({postHref: '<?php echo admin_url('admin-ajax.php?action=save_rating&post_id='.$listing->post->ID.'&_wpnonce='.wp_create_nonce('save_rating')); ?>'});
			});
			<?php endif; ?>

			jQuery('body').w2dc_tooltip({
				placement: 'right',
				selector: '[data-toggle="w2dc-tooltip"]'
			});
		});
	</script>
	<?php endif; ?>
	<div id="rater-<?php echo $listing->post->ID; ?>" class="stat">
		<div class="statVal">
			<nobr>
				<span class="ui-rater" data-toggle="w2dc-tooltip" title="<?php printf(__('Average rating: %s (%s)', 'W2DC'), $listing->avg_rating->avg_value, sprintf(_n('%d vote', '%d votes', $listing->avg_rating->ratings_count, 'W2DC'), $listing->avg_rating->ratings_count)); ?>">
					<span class="ui-rater-starsOff" style="width:100px;">
						<span class="ui-rater-starsOn" style="width: <?php echo $listing->avg_rating->avg_value*20; ?>px"></span>
					</span>
					<?php if ($show_avg): ?>
					<span class="ui-rater-avgvalue">
						<span class="ui-rater-rating"><?php echo $listing->avg_rating->avg_value; ?></span>
					</span>
					<?php endif; ?>
				</span>
			</nobr>
		</div>
	</div>
</div>