<?php 

class w2dc_slider_controller extends w2dc_frontend_controller {
	public function init($args = array()) {
		global $w2dc_instance;
		
		parent::init($args);

		$shortcode_atts = array_merge(array(
				'slides' => 5,
				'max_width' => '',
				'height' => 400,
				'slide_width' => 150,
				'max_slides' => 4,
				'sticky_featured' => 0,
				'auto_slides' => 0,
				'auto_slides_delay' => 3000,
				'order_by' => 'post_date',
				'order' => 'ASC',
				'order_by_rand' => 0,
		), $args);
		$this->args = $shortcode_atts;

		$args = array(
				'post_type' => W2DC_POST_TYPE,
				'post_status' => 'publish',
				'meta_query' => array(
						array('key' => '_listing_status', 'value' => 'active'),
						array('key' => '_thumbnail_id'),
				),
				'posts_per_page' => $this->args['slides'],
				'paged' => -1,
		);
		if ($this->args['order_by_rand'])
			$args['orderby'] = 'rand';
		else
			$args = array_merge($args, apply_filters('w2dc_order_args', array(), $shortcode_atts, false));

		$args = apply_filters('w2dc_search_args', $args, $this->args, false, $this->hash);

		if (isset($this->args['post__in'])) {
			$args = array_merge($args, array('post__in' => explode(',', $this->args['post__in'])));
		}
		
		if (isset($this->args['levels']) && !is_array($this->args['levels'])) {
			if ($levels = array_filter(explode(',', $this->args['levels']), 'trim')) {
				$this->levels_ids = $levels;
				add_filter('posts_where', array($this, 'where_levels_ids'));
			}
		}

		if (isset($this->args['levels']) || $this->args['sticky_featured']) {
			add_filter('posts_join', 'join_levels');
			if ($this->args['sticky_featured'])
				add_filter('posts_where', 'where_sticky_featured');
		}
		$this->query = new WP_Query($args);
		$this->processQuery(false);

		if ($this->args['sticky_featured']) {
			remove_filter('posts_join', 'join_levels');
			remove_filter('posts_where', 'where_sticky_featured');
		}

		if ($this->levels_ids)
			remove_filter('posts_where', array($this, 'where_levels_ids'));
		
		$this->template = 'frontend/slider.tpl.php';

		apply_filters('w2dc_frontend_controller_construct', $this);
	}
	
	public function where_levels_ids($where = '') {
		if ($this->levels_ids)
			$where .= " AND (w2dc_levels.id IN (" . implode(',', $this->levels_ids) . "))";
		return $where;
	}

	public function display() {
		$images = array();
		while ($this->query->have_posts()) {
			$this->query->the_post();
			$listing = $this->listings[get_the_ID()];
			if (has_post_thumbnail())
				if ($listing->level->listings_own_page)
					$images[] = '<a href="' . get_the_permalink() . '" ' . (($listing->level->nofollow) ? 'rel="nofollow"' : '') . '>' . get_the_post_thumbnail(get_the_ID(), 'full', array('title' => $listing->title())) . '</a>';
				else
					$images[] = get_the_post_thumbnail(get_the_ID(), 'full', array('title' => $listing->title()));
		}

		if ($images) {
			$output =  w2dc_renderTemplate($this->template, array(
					'slide_width' => $this->args['slide_width'],
					'max_width' => $this->args['max_width'],
					'max_slides' => $this->args['max_slides'],
					'height' => $this->args['height'],
					'auto_slides' => $this->args['auto_slides'],
					'auto_slides_delay' => $this->args['auto_slides_delay'],
					'images' => $images,
					'random_id' => generateRandomVal()
			), true);
			wp_reset_postdata();
	
			return $output;
		}
	}
}

?>