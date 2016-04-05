<?php echo $args['before_widget']; ?>
<?php if (!empty($title))
echo $args['before_title'] . $title . $args['after_title'];
?>
<div class="w2dc-content w2dc-widget w2dc_recent_listings_widget">
	<ul>
		<?php foreach ($listings AS $listing): ?>
		<li class="w2dc-widget-listing <?php if ($listing->level->featured): ?>w2dc-featured<?php endif; ?>">
			<?php if ($listing->logo_image): ?>
			<div class="w2dc-widget-listing-logo">
				<?php $src = wp_get_attachment_image_src($listing->logo_image, array(45, 45)); ?>
				<img src="<?php echo $src[0]; ?>" width="<?php echo $src[1]; ?>" height="<?php echo $src[2]; ?>" />
			</div>
			<?php endif; ?>
	
			<div class="w2dc-widget-listing-title">
				<?php if (!$listing->level->listings_own_page): ?>
				<?php echo $listing->title(); ?>
				<?php else: ?>
				<a href="<?php echo get_permalink($listing->post->ID); ?>" title="<?php echo esc_attr($listing->title()); ?>" <?php if ($listing->level->nofollow): ?>rel="nofollow"<?php endif; ?>><?php echo $listing->title(); ?></a>
				<?php endif; ?>
				<?php if (!get_option('w2dc_hide_listings_creation_date')): ?>
				<br />
				<?php echo human_time_diff(mysql2date('U', $listing->post->post_date), time()); ?> <?php _e('ago', 'W2DC'); ?>
				<?php endif; ?>
			</div>
		</li>
		<?php endforeach; ?>
	</ul>
</div>
<?php echo $args['after_widget']; ?>