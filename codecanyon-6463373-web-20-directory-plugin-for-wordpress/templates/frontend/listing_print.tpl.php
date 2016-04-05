<?php
global $w2dc_instance;
$frontend_controller = new w2dc_directory_controller();
$frontend_controller->init();
$w2dc_instance->frontend_controllers['webdirectory'][] = $frontend_controller;
$w2dc_instance->_frontend_controllers['webdirectory'][] = $frontend_controller;

if ($w2dc_instance->action == 'pdflisting')
	$pdflisting = true;
else
	$pdflisting = false;

?>

<!DOCTYPE html>
<!--[if IE 7]>
<html class="ie ie7" <?php language_attributes(); ?>>
<![endif]-->
<!--[if IE 8]>
<html class="ie ie8" <?php language_attributes(); ?>>
<![endif]-->
<!--[if !(IE 7) | !(IE 8)  ]><!-->
<html <?php language_attributes(); ?>>
<!--<![endif]-->
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<?php wp_head(); ?>
	
	<style type="text/css">
	.w2dc-print-buttons {
		margin: 10px;
	}
	@media print {
		.w2dc-print-buttons {
			display: none;
		}
	}
	</style>
</head>

<body <?php body_class(); ?> style="background-color: #FFF">
	<div id="page" class="site w2dc-content">
		<div id="main" class="wrapper">
			<div class="w2dc-container-fluid entry-content">
				<?php if (!$pdflisting): ?>
				<div class="w2dc-print-buttons w2dc-row w2dc-clearfix">
					<div class="w2dc-col-sm-2">
						<input type="button" class="w2dc-btn w2dc-btn-primary" onclick="window.print();" value="<?php esc_attr_e('Print listing', 'W2DC'); ?>" />
					</div>
					<div class="w2dc-col-sm-2">
						<input type="button" class="w2dc-btn w2dc-btn-primary" onclick="window.close();" value="<?php esc_attr_e('Close window', 'W2DC'); ?>" />
					</div>
				</div>
				<?php endif; ?>

			<?php while ($frontend_controller->query->have_posts()): ?>
				<?php $frontend_controller->query->the_post(); ?>
				<?php $listing = $frontend_controller->listings[get_the_ID()]; ?>
				<?php if (get_the_title()): ?>
				<div class="w2dc-row w2dc-clearfix w2dc-listing-header">
					<div class="w2dc-col-sm-12">
						<h2><?php the_title(); ?> <?php do_action('w2dc_listing_title_html', $listing); ?></h2>
					</div>
				</div>
				<?php endif;?>

				<?php if ($listing->logo_image): ?>
				<div class="w2dc-listing-logo-wrap">
					<?php do_action('w2dc_listing_pre_logo_wrap_html', $listing); ?>
					<div class="w2dc-listing-logo">
						<?php $src = wp_get_attachment_image_src($listing->logo_image, 'full'); ?>
						<img src="<?php echo $src[0]; ?>" />
					</div>
				</div>
				<?php endif; ?>

				<div class="w2dc-listing-text-content-wrap entry-content">
					<?php do_action('w2dc_listing_pre_content_html', $listing); ?>

					<em class="w2dc-listing-date"><?php echo get_the_date(); ?> <?php echo get_the_time(); ?></em>

					<?php $listing->renderContentFields(true); ?>
					
					<?php do_action('w2dc_listing_post_content_html', $listing); ?>
				</div>
				<div class="clear_float"></div>

				<?php if ($listing->level->google_map && $listing->isMap() && $listing->locations): ?>
				<h2><?php _e('Map', 'W2DC'); ?></h2>
				<?php $listing->renderMap($frontend_controller->hash, false, true); ?>
				<?php endif; ?>
				
				<?php if (count($listing->images) > 1): ?>
				<h2><?php _e('Images', 'W2DC'); ?> (<?php echo count($listing->images); ?>)</h2>
				<?php foreach ($listing->images AS $attachment_id=>$image): ?>
					<?php $src_thumbnail = wp_get_attachment_image_src($attachment_id,'large'); ?>
					<div style="margin: 10px">
						<img src="<?php echo $src_thumbnail[0]; ?>"/>
					</div>
				<?php endforeach; ?>
				<?php endif; ?>

				<?php if (get_comments_number()): ?>
				<h2 class="comments-title">
					<?php
						printf(_n('One thought on &ldquo;%2$s&rdquo;', '%1$s thoughts on &ldquo;%2$s&rdquo;', get_comments_number(), 'W2DC'),
							number_format_i18n(get_comments_number()), '<span>' . get_the_title() . '</span>');
					?>
				</h2>
				<ol class="commentlist">
				<?php wp_list_comments(array('reply_text' => '', 'login_text' => '', 'style' => 'ol'), get_comments(array('post_id' => $listing->post->ID))); ?>
				</ol>
				<?php endif; ?>
			<?php endwhile; ?>

				<?php if (!$pdflisting): ?>
				<div class="w2dc-print-buttons w2dc-row">
					<div class="w2dc-col-sm-2">
						<input type="button" class="w2dc-btn w2dc-btn-primary" onclick="window.print();" value="<?php esc_attr_e('Print listing', 'W2DC'); ?>" />
					</div>
					<div class="w2dc-col-sm-2">
						<input type="button" class="w2dc-btn w2dc-btn-primary" onclick="window.close();" value="<?php esc_attr_e('Close window', 'W2DC'); ?>" />
					</div>
				</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
<?php wp_footer(); ?>
</body>
</html>