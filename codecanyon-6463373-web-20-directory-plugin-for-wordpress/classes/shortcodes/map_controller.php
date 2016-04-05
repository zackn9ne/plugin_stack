<?php 

class w2dc_map_controller extends w2dc_frontend_controller {
	public function init($args = array()) {
		global $w2dc_instance;
		
		parent::init($args);

		$shortcode_atts = array_merge(array(
				'custom_home' => 0,
				'num' => -1,
				'radius_cycle' => 1,
				'clusters' => 0,
				'sticky_scroll' => 0,
				'sticky_scroll_toppadding' => 10,
				'show_summary_button' => 0,
				'show_readmore_button' => 1,
				'sticky_featured' => 0,
				'ajax_loading' => 0,
				'ajax_markers_loading' => 0,
				'geolocation' => 0,
				'start_address' => '',
				'start_latitude' => '',
				'start_longitude' => '',
				'start_zoom' => '',
				'map_style' => 'default',
				'include_categories_children' => 0,
				'uid' => null,
		), $args);
		$this->args = $shortcode_atts;
		
		if ($this->args['custom_home'] || ($this->args['uid'] && $w2dc_instance->getListingsShortcodeByuID($this->args['uid'])))
			return false;

		$args = array(
				'post_type' => W2DC_POST_TYPE,
				'post_status' => 'publish',
				'meta_query' => array(array('key' => '_listing_status', 'value' => 'active')),
				'posts_per_page' => $shortcode_atts['num'],
		);
		$args = apply_filters('w2dc_search_args', $args, $this->args, $this->args['include_categories_children'], $this->hash);

		if (isset($this->args['post__in'])) {
			$args = array_merge($args, array('post__in' => explode(',', $this->args['post__in'])));
		}

		if (isset($this->args['levels']) && !is_array($this->args['levels'])) {
			if ($levels = array_filter(explode(',', $this->args['levels']), 'trim')) {
				$this->levels_ids = $levels;
				add_filter('posts_join', 'join_levels');
				add_filter('posts_where', array($this, 'where_levels_ids'));
			}
		}
		
		if ($this->args['sticky_featured']) {
			add_filter('posts_join', 'join_levels');
			add_filter('posts_where', 'where_sticky_featured');
		}

		if (isset($this->args['neLat']) && isset($this->args['neLng']) && isset($this->args['swLat']) && isset($this->args['swLng'])) {
			$y1 = $this->args['neLat'];
			$y2 = $this->args['swLat'];
			// when zoom level 2 - there may be problems with neLng and swLng of bounds
			if ($this->args['neLng'] > $this->args['swLng']) {
				$x1 = $this->args['neLng'];
				$x2 = $this->args['swLng'];
			} else {
				$x1 = 180;
				$x2 = -180;
			}
			
			global $wpdb;
			$results = $wpdb->get_results($wpdb->prepare(
				"SELECT DISTINCT
					post_id FROM {$wpdb->locations_relationships} AS w2dc_lr
				WHERE MBRContains(
				GeomFromText('Polygon((%f %f,%f %f,%f %f,%f %f,%f %f))'),
				GeomFromText(CONCAT('POINT(',w2dc_lr.map_coords_1,' ',w2dc_lr.map_coords_2,')')))", $y2, $x2, $y2, $x1, $y1, $x1, $y1, $x2, $y2, $x2), ARRAY_A);

			$post_ids = array();
			foreach ($results AS $row)
				$post_ids[] = $row['post_id'];
			$post_ids = array_unique($post_ids);

			if ($post_ids) {
				if (isset($args['post__in'])) {
					$args['post__in'] = array_intersect($args['post__in'], $post_ids);
					if (empty($args['post__in']))
						// Do not show any listings
						$args['post__in'] = array(0);
				} else
					$args['post__in'] = $post_ids;
			} else
				// Do not show any listings
				$args['post__in'] = array(0);
		}
		
		if (isset($this->args['geo_poly']) && $this->args['geo_poly']) {
			$geo_poly = $this->args['geo_poly'];
			$sql_polygon = array();
			foreach ($geo_poly AS $vertex)
				$sql_polygon[] = $vertex['lat'] . ' ' . $vertex['lng'];
			$sql_polygon[] = $sql_polygon[0];

			global $wpdb, $w2dc_address_locations;
			$thread_stack = $wpdb->get_row("SELECT @@global.thread_stack", ARRAY_A);
			if ($thread_stack && $thread_stack['@@global.thread_stack'] <= 131072)
				$wpdb->query("SET @@global.thread_stack = " . 256*1024);

			if (!$wpdb->get_row("SHOW FUNCTION STATUS WHERE name='GISWithin'", ARRAY_A))
				$o = $wpdb->query("CREATE FUNCTION GISWithin(pt POINT, mp MULTIPOLYGON) RETURNS INT(1) DETERMINISTIC
BEGIN
			
DECLARE str, xy TEXT;
DECLARE x, y, p1x, p1y, p2x, p2y, m, xinters DECIMAL(16, 13) DEFAULT 0;
DECLARE counter INT DEFAULT 0;
DECLARE p, pb, pe INT DEFAULT 0;
			
SELECT MBRWithin(pt, mp) INTO p;
IF p != 1 OR ISNULL(p) THEN
RETURN p;
END IF;
			
SELECT X(pt), Y(pt), ASTEXT(mp) INTO x, y, str;
SET str = REPLACE(str, 'POLYGON((','');
SET str = REPLACE(str, '))', '');
SET str = CONCAT(str, ',');
			
SET pb = 1;
SET pe = LOCATE(',', str);
SET xy = SUBSTRING(str, pb, pe - pb);
SET p = INSTR(xy, ' ');
SET p1x = SUBSTRING(xy, 1, p - 1);
SET p1y = SUBSTRING(xy, p + 1);
SET str = CONCAT(str, xy, ',');
			
WHILE pe > 0 DO
SET xy = SUBSTRING(str, pb, pe - pb);
SET p = INSTR(xy, ' ');
SET p2x = SUBSTRING(xy, 1, p - 1);
SET p2y = SUBSTRING(xy, p + 1);
IF p1y < p2y THEN SET m = p1y; ELSE SET m = p2y; END IF;
IF y > m THEN
IF p1y > p2y THEN SET m = p1y; ELSE SET m = p2y; END IF;
IF y <= m THEN
IF p1x > p2x THEN SET m = p1x; ELSE SET m = p2x; END IF;
IF x <= m THEN
IF p1y != p2y THEN
SET xinters = (y - p1y) * (p2x - p1x) / (p2y - p1y) + p1x;
END IF;
IF p1x = p2x OR x <= xinters THEN
SET counter = counter + 1;
END IF;
END IF;
END IF;
END IF;
SET p1x = p2x;
SET p1y = p2y;
SET pb = pe + 1;
SET pe = LOCATE(',', str, pb);
END WHILE;
			
RETURN counter % 2;
			
END;
");
			
			$results = $wpdb->get_results("SELECT id, post_id FROM {$wpdb->locations_relationships} AS w2dc_lr
			WHERE GISWithin(
			GeomFromText(CONCAT('POINT(',w2dc_lr.map_coords_1,' ',w2dc_lr.map_coords_2,')')), PolygonFromText('POLYGON((" . implode(', ', $sql_polygon) . "))'))", ARRAY_A);
			
			$post_ids = array();
			foreach ($results AS $row) {
				$post_ids[] = $row['post_id'];
				$w2dc_address_locations[] = $row['id'];
			}
			$post_ids = array_unique($post_ids);
			
			if ($post_ids) {
				if (isset($args['post__in'])) {
					$args['post__in'] = array_intersect($args['post__in'], $post_ids);
					if (empty($args['post__in']))
						// Do not show any listings
						$args['post__in'] = array(0);
				} else
					$args['post__in'] = $post_ids;
			} else
				// Do not show any listings
				$args['post__in'] = array(0);
		}

		$this->google_map = new google_maps($this->args);
		$this->google_map->setUniqueId($this->hash);

		if (!$this->google_map->is_ajax_markers_management()) {
			$this->query = new WP_Query($args);
			//var_dump($this->query->request);
			$this->processQuery($this->args['ajax_markers_loading']);
		}
		
		if ($this->args['sticky_featured']) {
			remove_filter('posts_join', 'join_levels');
			remove_filter('posts_where', 'where_sticky_featured');
		}

		if ($this->levels_ids) {
			remove_filter('posts_join', 'join_levels');
			remove_filter('posts_where', array($this, 'where_levels_ids'));
		}
		
		apply_filters('w2dc_frontend_controller_construct', $this);
	}
	
	public function processQuery($is_ajax_map = false, $map_args = array()) {
		while ($this->query->have_posts()) {
			$this->query->the_post();

			$listing = new w2dc_listing;
			if (!$is_ajax_map) {
				$listing->loadListingForMap(get_post());
				$this->google_map->collectLocations($listing);
			} else {
				$listing->loadListingForAjaxMap(get_post());
				$this->google_map->collectLocationsForAjax($listing);
			}

			$this->listings[get_the_ID()] = $listing;
		}

		global $w2dc_address_locations, $w2dc_tax_terms_locations;
		// empty this global arrays - there may be some google maps on one page with different arguments
		$w2dc_address_locations = array();
		$w2dc_tax_terms_locations = array();

		// this is reset is really required after the loop ends
		wp_reset_postdata();
	}
	
	public function where_levels_ids($where = '') {
		if ($this->levels_ids)
			$where .= " AND (w2dc_levels.id IN (" . implode(',', $this->levels_ids) . "))";
		return $where;
	}

	public function display() {
		global $w2dc_instance;

		$width = false;
		$height = get_option('w2dc_default_map_height');
		if (isset($this->args['width']))
			$width = $this->args['width'];
		if (isset($this->args['height']))
			$height = $this->args['height'];

		ob_start();
		if ($this->args['custom_home'] || ($this->args['uid'] && $listings_controller = $w2dc_instance->getListingsShortcodeByuID($this->args['uid']))) {
			if ($directory_controller = $w2dc_instance->getShortcodeProperty('webdirectory')) {
				$show_summary_button = true;
				$show_readmore_button = true;
				if ($directory_controller->is_single) {
					$show_summary_button = false;
					$show_readmore_button = false;
				}

				// Google map may be disabled for index or excerpt pages in directory settings, so we need to check does this object exist in main shortcode.
				if ($directory_controller->google_map)
					$directory_controller->google_map->display(
							false,
							false,
							get_option('w2dc_enable_radius_search_cycle'),
							get_option('w2dc_enable_clusters'),
							$show_summary_button,
							$show_readmore_button,
							$width,
							$height,
							$this->args['sticky_scroll'],
							$this->args['sticky_scroll_toppadding'],
							$this->args['map_style'],
							$this->args['custom_home']
					);
			} elseif (isset($listings_controller) && $listings_controller) {
				$show_summary_button = true;
				$show_readmore_button = true;
				if (!$listings_controller->google_map) {
					$listings_controller->google_map = new google_maps($this->args);
					$listings_controller->google_map->setUniqueId($this->hash);
					foreach ($listings_controller->listings AS $listing)
						$listings_controller->google_map->collectLocations($listing);
				}
				$listings_controller->google_map->display(
						false,
						false,
						get_option('w2dc_enable_radius_search_cycle'),
						get_option('w2dc_enable_clusters'),
						$show_summary_button,
						$show_readmore_button,
						$width,
						$height,
						$this->args['sticky_scroll'],
						$this->args['sticky_scroll_toppadding'],
						$this->args['map_style'],
						$this->args['custom_home']
				);
			} else {
				$show_summary_button = false;
				$show_readmore_button = true;
				$google_map = new google_maps($this->args);
				$google_map->setUniqueId($this->hash);
				$google_map->display(
						false,
						false,
						get_option('w2dc_enable_radius_search_cycle'),
						get_option('w2dc_enable_clusters'),
						$show_summary_button,
						$show_readmore_button,
						$width,
						$height,
						$this->args['sticky_scroll'],
						$this->args['sticky_scroll_toppadding'],
						$this->args['map_style'],
						$this->args['custom_home']
				);
			}
		} else 
			$this->google_map->display(
					false, // hide directions
					false, // static image
					$this->args['radius_cycle'],
					$this->args['clusters'],
					$this->args['show_summary_button'],
					$this->args['show_readmore_button'],
					$width,
					$height,
					$this->args['sticky_scroll'],
					$this->args['sticky_scroll_toppadding'],
					$this->args['map_style'],
					$this->args['custom_home']
			);

		$output = ob_get_clean();

		wp_reset_postdata();
	
		return $output;
	}
}

?>