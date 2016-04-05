<?php 

class w2dc_ajax_controller {

	public function __construct() {
		add_action('wp_ajax_w2dc_get_map_markers', array($this, 'get_map_markers'));
		add_action('wp_ajax_nopriv_w2dc_get_map_markers', array($this, 'get_map_markers'));

		add_action('wp_ajax_w2dc_get_map_marker_info', array($this, 'get_map_marker_info'));
		add_action('wp_ajax_nopriv_w2dc_get_map_marker_info', array($this, 'get_map_marker_info'));

		add_action('wp_ajax_w2dc_get_sharing_buttons', array($this, 'get_sharing_buttons'));
		add_action('wp_ajax_nopriv_w2dc_get_sharing_buttons', array($this, 'get_sharing_buttons'));

		add_action('wp_ajax_w2dc_controller_request', array($this, 'controller_request'));
		add_action('wp_ajax_nopriv_w2dc_controller_request', array($this, 'controller_request'));

		add_action('wp_ajax_w2dc_search_by_poly', array($this, 'search_by_poly'));
		add_action('wp_ajax_nopriv_w2dc_search_by_poly', array($this, 'search_by_poly'));
	}

	public function controller_request() {
		global $w2dc_instance;

		$post_args = $_POST;

		switch ($post_args['controller']) {
			case "directory_controller":
			case "listings_controller":
				if ($post_args['controller'] == "directory_controller")
					$shortcode_atts = array_merge(array(
							'perpage' => (isset($post_args['is_home']) && $post_args['is_home']) ? get_option('w2dc_listings_number_index') : get_option('w2dc_listings_number_excerpt'),
							'onepage' => 0,
							'sticky_featured' => 0,
							'order_by' => get_option('w2dc_default_orderby'),
							'order' => get_option('w2dc_default_order'),
							'hide_order' => (int)(!(get_option('w2dc_show_orderby_links'))),
							'hide_count' => 0,
							'hide_paginator' => 0,
							'show_views_switcher' => (int)get_option('w2dc_views_switcher'),
							'listings_view_type' => get_option('w2dc_views_switcher_default'),
							'listings_view_grid_columns' => (int)get_option('w2dc_views_switcher_grid_columns'),
							'listing_thumb_width' => (int)get_option('w2dc_listing_thumb_width'),
							'wrap_logo_list_view' => (int)get_option('w2dc_wrap_logo_list_view'),
							'logo_animation_effect' => (int)get_option('w2dc_logo_animation_effect'),
							'author' => 0,
							'paged' => 1,
							'template' => 'frontend/listings_block.tpl.php',
					), $post_args);
				elseif ($post_args['controller'] == "listings_controller")
					$shortcode_atts = array_merge(array(
							'perpage' => 10,
							'onepage' => 0,
							'sticky_featured' => 0,
							'order_by' => 'post_date',
							'order' => 'DESC',
							'hide_order' => 0,
							'hide_count' => 0,
							'hide_paginator' => 0,
							'show_views_switcher' => 1,
							'listings_view_type' => 'list',
							'listings_view_grid_columns' => 2,
							'listing_thumb_width' => 300,
							'wrap_logo_list_view' => 0,
							'logo_animation_effect' => 6,
							'author' => 0,
							'paged' => 1,
							'template' => 'frontend/listings_block.tpl.php',
					), $post_args);

				// This is required workaround
				if (isset($post_args['order_by'])) {
					$_GET['order_by'] = w2dc_getValue($post_args, 'order_by', $shortcode_atts['order_by']);
					$_GET['order'] = w2dc_getValue($post_args, 'order', $shortcode_atts['order']);
				} elseif (isset($post_args['radius']) && $post_args['radius'] && ((isset($post_args['location_id']) && $post_args['location_id']) || (isset($post_args['address']) && $post_args['address']))) {
					// When search by radius - order by distance by default instead of ordering by date
					$shortcode_atts['order_by'] = 'distance';
					$shortcode_atts['order'] = 'ASC';
				}

				// Strongly required for paginator
				set_query_var('page', $shortcode_atts['paged']);

				$controller = new w2dc_frontend_controller();
				$controller->init($post_args);
				$controller->hash = $post_args['hash'];
				$controller->args = $shortcode_atts;
				$controller->request_by = 'listings_controller';
				$controller->custom_home = (isset($shortcode_atts['custom_home']) && $shortcode_atts['custom_home']);

				$default_orderby_args = array('order_by' => $shortcode_atts['order_by'], 'order' => $shortcode_atts['order']);
				$order_args = apply_filters('w2dc_order_args', array(), $default_orderby_args, false);
				
				$args = array(
						'post_type' => W2DC_POST_TYPE,
						'post_status' => 'publish',
						'meta_query' => array(array('key' => '_listing_status', 'value' => 'active')),
						'posts_per_page' => $shortcode_atts['perpage'],
						'paged' => $shortcode_atts['paged'],
				);
				if ($shortcode_atts['author'])
					$args['author'] = $shortcode_atts['author'];
				// render just one page
				if ($shortcode_atts['onepage'])
					$args['posts_per_page'] = -1;

				$args = array_merge($args, $order_args);
				$args = apply_filters('w2dc_search_args', $args, $shortcode_atts, true, $controller->hash);
				if (isset($shortcode_atts['post__in'])) {
					$args = array_merge($args, array('post__in' => explode(',', $shortcode_atts['post__in'])));
				}
				
				if (isset($shortcode_atts['levels']) && !is_array($shortcode_atts['levels'])) {
					if ($levels = array_filter(explode(',', $shortcode_atts['levels']), 'trim')) {
						$controller->levels_ids = $levels;
						add_filter('posts_where', array($controller, 'where_levels_ids'));
					}
				}
				
				if (isset($shortcode_atts['levels']) || $shortcode_atts['sticky_featured']) {
					add_filter('posts_join', 'join_levels');
					if ($shortcode_atts['sticky_featured'])
						add_filter('posts_where', 'where_sticky_featured');
				}
					
				// found some plugins those break WP_Query by injections in pre_get_posts action, so decided to remove this hook temporarily
				global $wp_filter;
				if (isset($wp_filter['pre_get_posts'])) {
					$pre_get_posts = $wp_filter['pre_get_posts'];
					unset($wp_filter['pre_get_posts']);
				}
				$controller->query = new WP_Query($args);
				//var_dump($controller->query->request);
				
				if (isset($post_args['with_map']) && $post_args['with_map'])
					$load_map_markers = true;
				else
					$load_map_markers = false;
				$controller->processQuery($load_map_markers);

				if (isset($post_args['base_url']) && $post_args['base_url'])
					$controller->base_url = $post_args['base_url'];
				else
					$controller->base_url = w2dc_directoryUrl();
				
				global $w2dc_global_base_url;
				$w2dc_global_base_url = $controller->base_url;
				add_filter('get_pagenum_link', array($this, 'get_pagenum_link'));

				$listings_html = '';
				if (!isset($post_args['without_listings']) || !$post_args['without_listings']) {
					if (isset($post_args['do_append']) && $post_args['do_append']) {
						if ($controller->listings)
							while ($controller->query->have_posts()) {
								$controller->query->the_post(); 
								$listings_html .= '<article id="post-' . get_the_ID() . '" class="w2dc-row w2dc-listing ' . (($controller->listings[get_the_ID()]->level->featured) ? 'w2dc-featured' : '') . ' ' . (($controller->listings[get_the_ID()]->level->sticky) ? 'w2dc-sticky' : '') . '">';
								$listings_html .= $controller->listings[get_the_ID()]->display(false, true);
								$listings_html .= '</article>';
							}
						unset($controller->args['do_append']);
					} else
						$listings_html = w2dc_renderTemplate('frontend/listings_block.tpl.php', array('frontend_controller' => $controller), true);
				}
				wp_reset_postdata();
				
				$out = array(
						'html' => $listings_html,
						'hash' => $controller->hash,
						'map_markers' => (($controller->google_map) ? $controller->google_map->locations_option_array : ''),
						'hide_show_more_listings_button' => ($shortcode_atts['paged'] >= $controller->query->max_num_pages) ? 1 : 0,
				);
				
				if (isset($w2dc_instance->radius_values_array[$controller->hash]) && isset($w2dc_instance->radius_values_array[$controller->hash]['x_coord']) && isset($w2dc_instance->radius_values_array[$controller->hash]['y_coord'])) {
					$out['radius_params'] = array(
							'radius_value' => $w2dc_instance->radius_values_array[$controller->hash]['radius'],
							'map_coords_1' => $w2dc_instance->radius_values_array[$controller->hash]['x_coord'],
							'map_coords_2' => $w2dc_instance->radius_values_array[$controller->hash]['y_coord'],
							'dimension' => get_option('w2dc_miles_kilometers_in_search')
					);
				}
				
				echo json_encode($out);

				break;
		}
		
		die();
	}
	
	public function get_pagenum_link($result) {
		global $w2dc_global_base_url;

		if ($w2dc_global_base_url) {
			preg_match('/paged=(.?)/', $result, $matches);
			if (isset($matches[1])) {
				global $wp_rewrite;
				if ($wp_rewrite->using_permalinks()) {
					$parsed_url = parse_url($w2dc_global_base_url);
					$query_args = (isset($parsed_url['query'])) ? wp_parse_args($parsed_url['query']) : array();
					$query_args = array_map('urlencode', $query_args);
					$url_without_get = ($pos_get = strpos($w2dc_global_base_url, '?')) ? substr($w2dc_global_base_url, 0, $pos_get) : $w2dc_global_base_url;
					return esc_url(add_query_arg($query_args, trailingslashit(trailingslashit($url_without_get) . 'page/' . $matches[1])));
				} else
					return add_query_arg('page', $matches[1], $w2dc_global_base_url);
			} else 
				return $w2dc_global_base_url;
		}
		return $result;
	}

	public function get_map_markers() {
		global $w2dc_instance;

		$post_args = $_POST;
		$hash = $post_args['hash'];

		$map_markers = array();
		if (isset($post_args['neLat']) && isset($post_args['neLng']) && isset($post_args['swLat']) && isset($post_args['swLng'])) {
			// needed to unset 'ajax_loading' parameter when it is calling by AJAX, then $args will be passed to map controller
			if (isset($post_args['ajax_loading']))
				unset($post_args['ajax_loading']);
			
			if (isset($post_args['radius']) && $post_args['radius'] && ((isset($post_args['location_id']) && $post_args['location_id']) || (isset($post_args['address']) && $post_args['address']))) {
				// When search by radius - order by distance by default instead of ordering by date
				$post_args['order_by'] = 'distance';
				$post_args['order'] = 'ASC';
			}

			$map_controller = new w2dc_map_controller();
			$map_controller->init($post_args);
			$map_controller->hash = $hash;
			wp_reset_postdata();
			
			$map_markers = $map_controller->google_map->locations_option_array;
		}
			
		$listings_html = '';
		if ((!isset($post_args['without_listings']) || !$post_args['without_listings'])) {
			$shortcode_atts = array_merge(array(
					'perpage' => 10,
					'onepage' => 0,
					'sticky_featured' => 0,
					'order_by' => 'post_date',
					'order' => 'DESC',
					'hide_order' => 0,
					'hide_count' => 0,
					'hide_paginator' => 0,
					'show_views_switcher' => 1,
					'listings_view_type' => 'list',
					'listings_view_grid_columns' => 2,
					'listing_thumb_width' => 300,
					'wrap_logo_list_view' => 0,
					'logo_animation_effect' => 6,
					'author' => 0,
					'paged' => 1,
					'ajax_initial_load' => 0,
					'template' => 'frontend/listings_block.tpl.php',
			), $post_args);

			$post_ids = array();
			if (isset($map_controller->google_map->locations_array) && $map_controller->google_map->locations_array) {
				foreach ($map_controller->google_map->locations_array AS $location)
					$post_ids[] = $location->post_id;
				$shortcode_atts['post__in'] = implode(',', $post_ids);
			} else
				$shortcode_atts['post__in'] = 0;

			$listings_controller = new w2dc_listings_controller();
			$listings_controller->init($shortcode_atts);
			$listings_controller->hash = $hash;

			$listings_html = w2dc_renderTemplate('frontend/listings_block.tpl.php', array('frontend_controller' => $listings_controller), true);
			wp_reset_postdata();
		}

		$out = array(
				'html' => $listings_html,
				'hash' => $hash,
				'map_markers' => $map_markers,
		);
			
		if (isset($w2dc_instance->radius_values_array[$hash]) && isset($w2dc_instance->radius_values_array[$hash]['x_coord']) && isset($w2dc_instance->radius_values_array[$hash]['y_coord'])) {
			$out['radius_params'] = array(
					'radius_value' => $w2dc_instance->radius_values_array[$hash]['radius'],
					'map_coords_1' => $w2dc_instance->radius_values_array[$hash]['x_coord'],
					'map_coords_2' => $w2dc_instance->radius_values_array[$hash]['y_coord'],
					'dimension' => get_option('w2dc_miles_kilometers_in_search')
			);
		}
			
		echo json_encode($out);

		die();
	}
	
	public function search_by_poly() {
		$post_args = $_POST;
		$hash = $post_args['hash'];
		
		$out = array(
				'hash' => $hash
		);

		$map_markers = array();
		if (isset($post_args['geo_poly']) && $post_args['geo_poly']) {
			$map_controller = new w2dc_map_controller();
			$map_controller->init($post_args);
			$map_controller->hash = $hash;
			wp_reset_postdata();

			$map_markers = $map_controller->google_map->locations_option_array;
		}
		
		$listings_html = '';
		if ((!isset($post_args['without_listings']) || !$post_args['without_listings'])) {
			$shortcode_atts = array_merge(array(
					'perpage' => 10,
					'onepage' => 0,
					'sticky_featured' => 0,
					'order_by' => 'post_date',
					'order' => 'DESC',
					'hide_order' => 0,
					'hide_count' => 0,
					'hide_paginator' => 0,
					'show_views_switcher' => 1,
					'listings_view_type' => 'list',
					'listings_view_grid_columns' => 2,
					'listing_thumb_width' => 300,
					'wrap_logo_list_view' => 0,
					'logo_animation_effect' => 6,
					'author' => 0,
					'paged' => 1,
					'ajax_initial_load' => 0,
					'template' => 'frontend/listings_block.tpl.php',
			), $post_args);

			if (isset($map_controller->google_map->locations_array) && $map_controller->google_map->locations_array) {
				$post_ids = array();
				foreach ($map_controller->google_map->locations_array AS $location)
					$post_ids[] = $location->post_id;
				$shortcode_atts['post__in'] = implode(',', $post_ids);
			} else
				$shortcode_atts['post__in'] = 0;
		
			$listings_controller = new w2dc_listings_controller();
			$listings_controller->init($shortcode_atts);
			$listings_controller->hash = $hash;
		
			$listings_html = w2dc_renderTemplate('frontend/listings_block.tpl.php', array('frontend_controller' => $listings_controller), true);
			wp_reset_postdata();
		}
		
		$out['html'] = $listings_html;
		$out['map_markers'] = $map_markers;

		echo json_encode($out);
	
		die();
	}
	
	public function get_map_marker_info() {
		global $w2dc_instance, $wpdb;

		if (isset($_POST['location_id']) && is_numeric($_POST['location_id'])) {
			$location_id = $_POST['location_id'];

			$row = $wpdb->get_row("SELECT * FROM {$wpdb->locations_relationships} WHERE id=".$location_id, ARRAY_A);

			if ($row && $row['location_id'] || $row['map_coords_1'] != '0.000000' || $row['map_coords_2'] != '0.000000' || $row['address_line_1'] || $row['zip_or_postal_index']) {
				$listing = new w2dc_listing;
				if ($listing->loadListingFromPost($row['post_id'])) {
					$location = new w2dc_location($row['post_id']);
					$location_settings = array(
							'id' => $row['id'],
							'selected_location' => $row['location_id'],
							'address_line_1' => $row['address_line_1'],
							'address_line_2' => $row['address_line_2'],
							'zip_or_postal_index' => $row['zip_or_postal_index'],
					);
					if ($listing->level->google_map) {
						$location_settings['manual_coords'] = w2dc_getValue($row, 'manual_coords');
						$location_settings['map_coords_1'] = w2dc_getValue($row, 'map_coords_1');
						$location_settings['map_coords_2'] = w2dc_getValue($row, 'map_coords_2');
						if ($listing->level->google_map_markers)
							$location_settings['map_icon_file'] = w2dc_getValue($row, 'map_icon_file');
					}
					$location->createLocationFromArray($location_settings);
						
					$logo_image = '';
					if ($listing->logo_image) {
						$src = wp_get_attachment_image_src($listing->logo_image, array(80, 80));
						$logo_image = $src[0];
					}
						
					$listing_link = '';
					if ($listing->level->listings_own_page)
						$listing_link = get_permalink($listing->post->ID);
						
					$content_fields_output = $listing->setMapContentFields($w2dc_instance->content_fields->getMapContentFields(), $location);

					$locations_option_array = array(
							$location->id,
							$location->map_coords_1,
							$location->map_coords_2,
							$location->map_icon_file,
							$listing->map_zoom,
							$listing->title(),
							$logo_image,
							$listing_link,
							$content_fields_output,
							'post-' . $listing->post->ID,
					);
						
					echo json_encode($locations_option_array);
				}
			}
		}
		die();
	}
	
	public function get_sharing_buttons() {
		w2dc_renderTemplate('frontend/sharing_buttons_ajax_response.tpl.php', array('post_id' => $_POST['post_id']));
		
		die();
	}
}
?>