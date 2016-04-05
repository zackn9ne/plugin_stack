		<div class="w2dc-content">
			<?php w2dc_renderMessages(); ?>

			<?php w2dc_renderTemplate('frontend/frontpanel_buttons.tpl.php'); ?>
			
			<?php if ($frontend_controller->getPageTitle()): ?>
			<header>
				<h2>
					<?php echo $frontend_controller->getPageTitle(); ?>
				</h2>

				<?php if ($frontend_controller->breadcrumbs): ?>
				<div class="w2dc-breadcrumbs">
					<?php echo $frontend_controller->getBreadCrumbs(); ?>
				</div>
				<?php endif; ?>
			</header>
			<?php endif; ?>

			<div class="w2dc-container-fluid w2dc-listings-block">
				<div class="w2dc-row w2dc-listings-block-header">
					<div class="w2dc-found-listings">
						<?php echo sprintf(_n('Found <span class="w2dc-badge">%d</span> listing', 'Found <span class="w2dc-badge">%d</span> listings', $frontend_controller->query->found_posts, 'W2DC'), $frontend_controller->query->found_posts); ?>
					</div>
				</div>

				<?php if ($frontend_controller->listings): ?>
					<?php while ($frontend_controller->query->have_posts()): ?>
					<?php $frontend_controller->query->the_post(); ?>
					<article id="post-<?php the_ID(); ?>" class="w2dc-row w2dc-listing <?php if ($frontend_controller->listings[get_the_ID()]->level->featured) echo 'w2dc-featured'; ?>">
						<?php $frontend_controller->listings[get_the_ID()]->display(); ?>
					</article>
					<?php endwhile; ?>
					
					<?php renderPaginator($frontend_controller->query); ?>
				<?php endif; ?>
			</div>
		</div>