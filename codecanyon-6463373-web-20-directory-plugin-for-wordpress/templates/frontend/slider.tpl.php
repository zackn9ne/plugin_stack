<?php if ($images): ?>
		<script>
		jQuery(document).ready(function(){
			var slider_<?php echo $random_id; ?> = jQuery("#w2dc-slider-<?php echo $random_id; ?>").bxSlider({
				slideWidth: <?php echo $slide_width; ?>,
				minSlides: 2,
				maxSlides: <?php echo $max_slides; ?>,
				slideMargin: 10,
				captions: false,
				moveSlides: 1,
				preloadImages: 'all',
				speed: 300,
				onSliderLoad: function() {
					jQuery('#w2dc-slider-<?php echo $random_id; ?>').css('visibility', 'visible');
					changeSlide_<?php echo $random_id; ?>(jQuery("#w2dc-slider-<?php echo $random_id; ?> .slide:not(.bx-clone)").first());
					<?php if (count($images) == 1): ?>
					jQuery("#w2dc-slider-wrapper-<?php echo $random_id; ?> .bx-wrapper").hide();
					<?php endif; ?>
				}
				<?php if (isset($auto_slides) && $auto_slides): ?>
				,auto: true,
				autoHover: true,
				pause: <?php echo $auto_slides_delay; ?>,
				onSlideBefore: function(currentSlide, totalSlides, currentSlideHtmlObject){
					changeSlide_<?php echo $random_id; ?>(currentSlide);
				}
				<?php endif; ?>
			});

			jQuery('#w2dc-slider-wrapper-<?php echo $random_id; ?> .bx-viewport').mousewheel(function(event, delta, deltaX, deltaY) {
				if (delta > 0) {
					slider_<?php echo $random_id; ?>.goToPrevSlide();
				}
				if (deltaY < 0) {
					slider_<?php echo $random_id; ?>.goToNextSlide();
				}
				event.stopPropagation();
				event.preventDefault();
			});

			jQuery("#w2dc-slider-<?php echo $random_id; ?> .slide").on('mouseover', function() {
				changeSlide_<?php echo $random_id; ?>(jQuery(this));
			});

			// Just change slide on click, this is only for touchscreens
			jQuery(".w2dc-touch #w2dc-slider-<?php echo $random_id; ?> .slide").click(function(e) {
				e.preventDefault();
				changeSlide_<?php echo $random_id; ?>(jQuery(this));
			});

			function changeSlide_<?php echo $random_id; ?>(slide) {
				jQuery("#w2dc-slider-<?php echo $random_id; ?>").find(".slide").removeClass("slide-active");
				slide.addClass('slide-active');
				jQuery("#w2dc-big-slide-wrapper-<?php echo $random_id; ?> .w2dc-big-slide").css('background-image', 'url('+slide.find("img").attr("src")+')');
				<?php if (!isset($enable_links) || $enable_links): ?>
				jQuery("#w2dc-big-slide-wrapper-<?php echo $random_id; ?> a").attr('href', slide.find("a").attr("href"));
				<?php endif; ?>
				jQuery("#w2dc-big-slide-wrapper-<?php echo $random_id; ?> .w2dc-big-slide-caption span").html(slide.find("img").attr("title"));
				if (jQuery("#w2dc-big-slide-wrapper-<?php echo $random_id; ?> .w2dc-big-slide-caption span").html() == '')
					jQuery("#w2dc-big-slide-wrapper-<?php echo $random_id; ?> .w2dc-big-slide-caption span").hide();
				else
					jQuery("#w2dc-big-slide-wrapper-<?php echo $random_id; ?> .w2dc-big-slide-caption span").show();

				var caption_height = jQuery("#w2dc-big-slide-wrapper-<?php echo $random_id; ?> .w2dc-big-slide-caption").height();
				jQuery("#w2dc-big-slide-wrapper-<?php echo $random_id; ?> .w2dc-big-slide-caption").css({ 'margin-top': '-'+caption_height+'px' });
			}
		});
		</script>
		<div class="w2dc-content w2dc-slider-wrapper" id="w2dc-slider-wrapper-<?php echo $random_id; ?>" style="<?php if ($max_width): ?>max-width: <?php echo $max_width; ?>px; <?php endif; ?>">
			<div class="w2dc-big-slide-wrapper" id="w2dc-big-slide-wrapper-<?php echo $random_id; ?>" style="height: <?php echo $height+10; ?>px;">
				<?php if (!isset($enable_links) || $enable_links): ?>
				<a data-lightbox="listing_images" href="javascript: void(0);"><div class="w2dc-big-slide" id="w2dc-big-slide-<?php echo $random_id; ?>" style="height: <?php echo $height; ?>px;"></div></a>
				<?php else: ?>
				<div class="w2dc-big-slide" id="w2dc-big-slide-<?php echo $random_id; ?>" style="height: <?php echo $height; ?>px;"></div>
				<?php endif; ?>
				<div class="w2dc-big-slide-caption"><span></span></div>
			</div>
			<div class="w2dc-slider" id="w2dc-slider-<?php echo $random_id; ?>">
				<?php foreach ($images AS $image): ?>
				<div class="slide"><?php echo $image; ?></div>
				<?php endforeach; ?>
			</div>
		</div>
<?php endif; ?>