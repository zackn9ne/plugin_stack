<?php 

class w2dc_directory_controller extends w2dc_frontend_controller {
	public $is_home = false;
	public $is_search = false;
	public $is_single = false;
	public $is_category = false;
	public $is_location = false;
	public $is_tag = false;
	public $is_favourites = false;
	public $breadcrumbs = array();
	public $custom_home = false;
	public $is_map_on_page = 1;
	public $request_by = 'directory_controller';

	public function init($args = array(), $shortcode = 'webdirectory') {
		global $w2dc_instance;
		
		parent::init($args);

		if (isset($args['custom_home']) && $args['custom_home'])
			$this->custom_home = true;

		if (get_query_var('page'))
			$paged = get_query_var('page');
		elseif (get_query_var('paged'))
			$paged = get_query_var('paged');
		else
			$paged = 1;

		if (get_query_var('listing-w2dc') || ($shortcode == 'webdirectory-listing' && isset($args['listing_id']) && is_numeric($args['listing_id']))) {
			if (get_query_var('listing-w2dc'))
				$args = array(
						'post_type' => W2DC_POST_TYPE,
						'post_status' => 'publish',
						'name' => get_query_var('listing-w2dc'),
						'posts_per_page' => 1,
				);
			else 
				$args = array(
						'post_type' => W2DC_POST_TYPE,
						'post_status' => 'publish',
						'p' => $args['listing_id'],
						'posts_per_page' => 1,
				);
			$this->query = new WP_Query($args);
			$this->processQuery(true);
			// Google Map uID must be absolutely unique on single listing page
			$this->google_map->setUniqueId(time());

			if (count($this->listings) == 1) {
				$listings_array = $this->listings;
				$listing = array_shift($listings_array);
				$this->listing = $listing;

				global $wp_rewrite;
				if ($shortcode != 'webdirectory-listing' && $wp_rewrite->using_permalinks() && ((get_option('w2dc_permalinks_structure') == 'category_slug' || get_option('w2dc_permalinks_structure') == 'location_slug' || get_option('w2dc_permalinks_structure') == 'tag_slug'))) {
					switch (get_option('w2dc_permalinks_structure')) {
						case 'category_slug':
							if ($terms = get_the_terms($this->listing->post->ID, W2DC_CATEGORIES_TAX)) {
								$term = array_shift($terms);
								$uri = '';
								if ($parents = w2dc_get_term_parents_slugs($term->term_id, W2DC_CATEGORIES_TAX))
									$uri = implode('/', $parents);
								if ($uri != get_query_var('tax-slugs-w2dc')) {
									wp_redirect(get_the_permalink($this->listing->post->ID), 301);
									die();
								}
							}
							break;
						case 'location_slug':
							if ($terms = get_the_terms($this->listing->post->ID, W2DC_LOCATIONS_TAX)) {
								$term = array_shift($terms);
								$uri = '';
								if ($parents = w2dc_get_term_parents_slugs($term->term_id, W2DC_LOCATIONS_TAX))
									$uri = implode('/', $parents);
								if ($uri != get_query_var('tax-slugs-w2dc')) {
									wp_redirect(get_the_permalink($this->listing->post->ID), 301);
									die();
								}
							}
							break;
						case 'tag_slug':
							if (($terms = get_the_terms($post->ID, W2DC_TAGS_TAX)) && ($term = array_shift($terms))) {
								if ($term->slug != get_query_var('tax-slugs-w2dc')) {
									wp_redirect(get_the_permalink($this->listing->post->ID), 301);
									die();
								}
							}
							break;
					}
				}
				
				$this->is_single = true;
				$this->template = 'frontend/listing_single.tpl.php';

				$this->page_title = $listing->title();

				if (get_option('w2dc_enable_breadcrumbs')) {
					if (!get_option('w2dc_hide_home_link_breadcrumb'))
						$this->breadcrumbs[] = '<a href="' . w2dc_directoryUrl() . '" itemprop="url"><span itemprop="title">' . __('Home', 'W2DC') . '</span></a>';
					switch (get_option('w2dc_breadcrumbs_mode')) {
						case 'category':
							if ($terms = get_the_terms($this->listing->post->ID, W2DC_CATEGORIES_TAX)) {
								$term = array_shift($terms);
								$this->breadcrumbs = array_merge($this->breadcrumbs, w2dc_get_term_parents($term, W2DC_CATEGORIES_TAX, true, true));
							}
							break;
						case 'location':
							if ($terms = get_the_terms($this->listing->post->ID, W2DC_LOCATIONS_TAX)) {
								$term = array_shift($terms);
								$this->breadcrumbs = array_merge($this->breadcrumbs, w2dc_get_term_parents($term, W2DC_LOCATIONS_TAX, true, true));
							}
							break;
					}
					$this->breadcrumbs[] = $listing->title();
				}
				
				if (get_option('w2dc_listing_contact_form') && $w2dc_instance->action == 'contact')
					$this->contactOwnerAction($listing->post);

				if (get_option('w2dc_listing_contact_form') && defined('WPCF7_VERSION') && get_wpml_dependent_option('w2dc_listing_contact_form_7'))
					add_filter('wpcf7_form_action_url', array($this, 'w2dc_add_listing_id_to_wpcf7'));
				
				add_filter('language_attributes', array($this, 'add_opengraph_doctype'));
				add_action('wp_head', array($this, 'insert_fb_in_head'), 5);
				if (function_exists('rel_canonical'))
					remove_action('wp_head', 'rel_canonical');
				// replace the default WordPress canonical URL function with your own
				add_action('wp_head', array($this, 'rel_canonical_with_custom_tag_override'));
			}/*  else {
				if ($template = get_404_template()) {
					status_header(404);
					nocache_headers();
				} else
					$template = get_index_template();

				if ($template = apply_filters('template_include', $template))
					include($template);
				exit;
			} */
		} elseif ($w2dc_instance->action == 'search') {
			$this->is_search = true;
			$this->template = 'frontend/search.tpl.php';
			
			if (!get_option('w2dc_map_on_excerpt'))
				$this->is_map_on_page = 0;

			if (get_option('w2dc_main_search'))
				$this->search_form = new search_form($this->hash, $this->request_by);

			$default_orderby_args = array('order_by' => get_option('w2dc_default_orderby'), 'order' => get_option('w2dc_default_order'));

			if (!get_option('w2dc_ajax_initial_load')) {
				$this->args = array_merge($default_orderby_args, $_GET);
				$order_args = apply_filters('w2dc_order_args', array(), $default_orderby_args);
	
				$args = array(
						'post_type' => W2DC_POST_TYPE,
						'post_status' => 'publish',
						'meta_query' => array(array('key' => '_listing_status', 'value' => 'active')),
						'posts_per_page' => get_option('w2dc_listings_number_excerpt'),
						'paged' => $paged,
				);
				$args = array_merge($args, $order_args);
				$args = apply_filters('w2dc_search_args', $args, array(), true, $this->hash);
				
				// found some plugins those break WP_Query by injections in pre_get_posts action, so decided to remove this hook temporarily
				global $wp_filter;
				if (isset($wp_filter['pre_get_posts'])) {
					$pre_get_posts = $wp_filter['pre_get_posts'];
					unset($wp_filter['pre_get_posts']);
				}
				$this->query = new WP_Query($args);
				$this->processQuery(get_option('w2dc_map_on_excerpt'));
				if (isset($pre_get_posts))
					$wp_filter['pre_get_posts'] = $pre_get_posts;
			} else {
				$this->do_initial_load = false;
				$this->args = $_GET;
				if ($this->is_map_on_page) {
					$this->google_map = new google_maps();
					$this->google_map->setUniqueId($this->hash);
				}
			}

			$this->page_title = __('Search results', 'W2DC');

			$this->args['perpage'] = get_option('w2dc_listings_number_excerpt');

			if (get_option('w2dc_enable_breadcrumbs')) {
				if (!get_option('w2dc_hide_home_link_breadcrumb'))
					$this->breadcrumbs[] = '<a href="' . w2dc_directoryUrl() . '">' . __('Home', 'W2DC') . '</a>';
				$this->breadcrumbs[] = __('Search results', 'W2DC');
			}
			$base_url_args = apply_filters('w2dc_base_url_args', array('w2dc_action' => 'search'));
			$this->base_url = w2dc_directoryUrl($base_url_args);
		} elseif (get_query_var('category-w2dc')) {
			if ($category_object = w2dc_get_term_by_path(get_query_var('category-w2dc'))) {
				$this->is_category = true;
				$this->category = $category_object;
				
				if (!get_option('w2dc_map_on_excerpt'))
					$this->is_map_on_page = 0;
				
				if (get_option('w2dc_main_search'))
					$this->search_form = new search_form($this->hash, $this->request_by);

				$default_orderby_args = array('order_by' => get_option('w2dc_default_orderby'), 'order' => get_option('w2dc_default_order'));
				$this->args = $default_orderby_args;
				$this->args['categories'] = $category_object->term_id;

				if (!get_option('w2dc_ajax_initial_load')) {
					$order_args = apply_filters('w2dc_order_args', array(), $default_orderby_args);
	
					$args = array(
							'tax_query' => array(
									array(
										'taxonomy' => W2DC_CATEGORIES_TAX,
										'field' => 'slug',
										'terms' => $category_object->slug,
									)
							),
							'post_type' => W2DC_POST_TYPE,
							'post_status' => 'publish',
							'meta_query' => array(array('key' => '_listing_status', 'value' => 'active')),
							'posts_per_page' => get_option('w2dc_listings_number_excerpt'),
							'paged' => $paged
					);
					$args = array_merge($args, $order_args);
	
					// found some plugins those break WP_Query by injections in pre_get_posts action, so decided to remove this hook temporarily
					global $wp_filter;
					if (isset($wp_filter['pre_get_posts'])) {
						$pre_get_posts = $wp_filter['pre_get_posts'];
						unset($wp_filter['pre_get_posts']);
					}
					$this->query = new WP_Query($args);
					$this->processQuery($this->is_map_on_page);
					if (isset($pre_get_posts))
						$wp_filter['pre_get_posts'] = $pre_get_posts;
				} else {
					$this->do_initial_load = false;
					if ($this->is_map_on_page) {
						$this->google_map = new google_maps();
						$this->google_map->setUniqueId($this->hash);
					}
				}

				$this->args['perpage'] = get_option('w2dc_listings_number_excerpt');
				$this->template = 'frontend/category.tpl.php';
				$this->page_title = $category_object->name;

				if (get_option('w2dc_enable_breadcrumbs')) {
					if (!get_option('w2dc_hide_home_link_breadcrumb'))
						$this->breadcrumbs[] = '<a href="' . w2dc_directoryUrl() . '">' . __('Home', 'W2DC') . '</a>';
					$this->breadcrumbs = array_merge($this->breadcrumbs, w2dc_get_term_parents($category_object, W2DC_CATEGORIES_TAX, true, true));
				}
				
				$this->base_url = get_term_link($category_object, W2DC_CATEGORIES_TAX);
			} else {
				if ($template = get_404_template()) {
					status_header(404);
					nocache_headers();
				} else
					$template = get_index_template();

				if ($template = apply_filters('template_include', $template))
					include($template);
				exit;
			}
		} elseif (get_query_var('location-w2dc')) {
			if ($location_object = w2dc_get_term_by_path(get_query_var('location-w2dc'))) {
				$this->is_location = true;
				$this->location = $location_object;
				
				if (!get_option('w2dc_map_on_excerpt'))
					$this->is_map_on_page = 0;

				global $w2dc_tax_terms_locations;
				$w2dc_tax_terms_locations = get_term_children($location_object->term_id, W2DC_LOCATIONS_TAX);
				$w2dc_tax_terms_locations[] = $location_object->term_id;
				
				if (get_option('w2dc_main_search'))
					$this->search_form = new search_form($this->hash, $this->request_by);

				$default_orderby_args = array('order_by' => get_option('w2dc_default_orderby'), 'order' => get_option('w2dc_default_order'));
				$this->args = $default_orderby_args;
				$this->args['location_id'] = $location_object->term_id;
				
				if (!get_option('w2dc_ajax_initial_load')) {
					$order_args = apply_filters('w2dc_order_args', array(), $default_orderby_args);
	
					$args = array(
							'tax_query' => array(
									array(
										'taxonomy' => W2DC_LOCATIONS_TAX,
										'field' => 'slug',
										'terms' => $location_object->slug,
									)
							),
							'post_type' => W2DC_POST_TYPE,
							'post_status' => 'publish',
							'meta_query' => array(array('key' => '_listing_status', 'value' => 'active')),
							'posts_per_page' => get_option('w2dc_listings_number_excerpt'),
							'paged' => $paged
					);
					$args = array_merge($args, $order_args);
	
					// found some plugins those break WP_Query by injections in pre_get_posts action, so decided to remove this hook temporarily
					global $wp_filter;
					if (isset($wp_filter['pre_get_posts'])) {
						$pre_get_posts = $wp_filter['pre_get_posts'];
						unset($wp_filter['pre_get_posts']);
					}
					$this->query = new WP_Query($args);
					$this->processQuery($this->is_map_on_page);
					if (isset($pre_get_posts))
						$wp_filter['pre_get_posts'] = $pre_get_posts;
				} else {
					$this->do_initial_load = false;
					if ($this->is_map_on_page) {
						$this->google_map = new google_maps();
						$this->google_map->setUniqueId($this->hash);
					}
				}

				$this->args['perpage'] = get_option('w2dc_listings_number_excerpt');
				$this->template = 'frontend/location.tpl.php';
				$this->page_title = $location_object->name;
				
				if (get_option('w2dc_enable_breadcrumbs')) {
					if (!get_option('w2dc_hide_home_link_breadcrumb'))
						$this->breadcrumbs[] = '<a href="' . w2dc_directoryUrl() . '">' . __('Home', 'W2DC') . '</a>';
					$this->breadcrumbs = array_merge($this->breadcrumbs, w2dc_get_term_parents($location_object, W2DC_LOCATIONS_TAX, true, true));
				}
				
				$this->base_url = get_term_link($location_object, W2DC_LOCATIONS_TAX);
			} else {
				if ($template = get_404_template()) {
					status_header(404);
					nocache_headers();
				} else
					$template = get_index_template();

				if ($template = apply_filters('template_include', $template))
					include($template);
				exit;
			}
		} elseif (get_query_var('tag-w2dc')) {
			if ($tag_object = get_term_by('slug', get_query_var('tag-w2dc'), W2DC_TAGS_TAX)) {
				$this->is_tag = true;
				$this->tag = $tag_object;
				
				if (!get_option('w2dc_map_on_excerpt'))
					$this->is_map_on_page = 0;

				if (get_option('w2dc_main_search'))
					$this->search_form = new search_form($this->hash, $this->request_by);

				$default_orderby_args = array('order_by' => get_option('w2dc_default_orderby'), 'order' => get_option('w2dc_default_order'));
				$this->args = $default_orderby_args;
				$this->args['tag_id'] = $tag_object->term_id;
				
				if (!get_option('w2dc_ajax_initial_load')) {
					$order_args = apply_filters('w2dc_order_args', array(), $default_orderby_args);
	
					$args = array(
							'tax_query' => array(
									array(
											'taxonomy' => W2DC_TAGS_TAX,
											'field' => 'slug',
											'terms' => $tag_object->slug,
									)
							),
							'post_type' => W2DC_POST_TYPE,
							'post_status' => 'publish',
							'meta_query' => array(array('key' => '_listing_status', 'value' => 'active')),
							'posts_per_page' => get_option('w2dc_listings_number_excerpt'),
							'paged' => $paged,
					);
					$args = array_merge($args, $order_args);
		
					// found some plugins those break WP_Query by injections in pre_get_posts action, so decided to remove this hook temporarily
					global $wp_filter;
					if (isset($wp_filter['pre_get_posts'])) {
						$pre_get_posts = $wp_filter['pre_get_posts'];
						unset($wp_filter['pre_get_posts']);
					}
					$this->query = new WP_Query($args);
					$this->processQuery($this->is_map_on_page);
					if (isset($pre_get_posts))
						$wp_filter['pre_get_posts'] = $pre_get_posts;
				} else {
					$this->do_initial_load = false;
					if ($this->is_map_on_page) {
						$this->google_map = new google_maps();
						$this->google_map->setUniqueId($this->hash);
					}
				}

				$this->args['perpage'] = get_option('w2dc_listings_number_excerpt');
				$this->template = 'frontend/tag.tpl.php';
				$this->page_title = $tag_object->name;

				if (get_option('w2dc_enable_breadcrumbs')) {
					if (!get_option('w2dc_hide_home_link_breadcrumb'))
						$this->breadcrumbs[] = '<a href="' . w2dc_directoryUrl() . '">' . __('Home', 'W2DC') . '</a>';
					$this->breadcrumbs[] = '<a href="' . get_term_link($tag_object->slug, W2DC_TAGS_TAX) . '" title="' . esc_attr(sprintf(__('View all listings in %s', 'W2DC'), $tag_object->name)) . '">' . $tag_object->name . '</a>';
				}
				
				$this->base_url = get_term_link($tag_object, W2DC_TAGS_TAX);
			} else {
				if ($template = get_404_template()) {
					status_header(404);
					nocache_headers();
				} else
					$template = get_index_template();

				if ($template = apply_filters('template_include', $template))
					include($template);
				exit;
			}
		} elseif ($w2dc_instance->action == 'myfavourites') {
			$this->is_favourites = true;

			if (!$favourites = checkQuickList())
				$favourites = array(0);
			$args = array(
					'post__in' => $favourites,
					'post_type' => W2DC_POST_TYPE,
					'post_status' => 'publish',
					'meta_query' => array(array('key' => '_listing_status', 'value' => 'active')),
					'posts_per_page' => get_option('w2dc_listings_number_excerpt'),
					'paged' => $paged,
			);
			$this->query = new WP_Query($args);
			$this->processQuery(get_option('w2dc_map_on_excerpt'));
			
			$this->args['perpage'] = get_option('w2dc_listings_number_excerpt');
			$this->template = 'frontend/favourites.tpl.php';
			$this->page_title = __('My bookmarks', 'W2DC');

			if (get_option('w2dc_enable_breadcrumbs')) {
				if (!get_option('w2dc_hide_home_link_breadcrumb'))
					$this->breadcrumbs[] = '<a href="' . w2dc_directoryUrl() . '">' . __('Home', 'W2DC') . '</a>';
				$this->breadcrumbs[] = __('My bookmarks', 'W2DC');
			}
		} elseif (!$w2dc_instance->action) {
			$this->is_home = true;
			
			if (!get_option('w2dc_map_on_index'))
				$this->is_map_on_page = 0;

			if (get_option('w2dc_main_search'))
				$this->search_form = new search_form($this->hash, $this->request_by);

			$default_orderby_args = array('order_by' => get_option('w2dc_default_orderby'), 'order' => get_option('w2dc_default_order'));
			//$this->args = $default_orderby_args;
			if (!get_option('w2dc_ajax_initial_load')) {
				$order_args = apply_filters('w2dc_order_args', array(), $default_orderby_args);
	
				$args = array(
						'post_type' => W2DC_POST_TYPE,
						'post_status' => 'publish',
						'meta_query' => array(array('key' => '_listing_status', 'value' => 'active')),
						'posts_per_page' => get_option('w2dc_listings_number_index'),
						'paged' => $paged,
				);
				$args = array_merge($args, $order_args);
	
				$this->query = new WP_Query($args);
				$this->processQuery($this->is_map_on_page);
			} else {
				$this->do_initial_load = false;
				if ($this->is_map_on_page) {
					$this->google_map = new google_maps();
					$this->google_map->setUniqueId($this->hash);
				}
			}

			$base_url_args = apply_filters('w2dc_base_url_args', array());
			$this->base_url = w2dc_directoryUrl($base_url_args);

			$this->args['perpage'] = get_option('w2dc_listings_number_index');
			$this->template = 'frontend/index.tpl.php';
			$this->page_title = get_post($w2dc_instance->index_page_id)->post_title;
		}
		$this->args['is_home'] = $this->is_home;
		$this->args['paged'] = $paged;
		$this->args['custom_home'] = (int)$this->custom_home;
		$this->args['with_map'] = $this->is_map_on_page;

		$this->args['onepage'] = 0;
		$this->args['hide_paginator'] = 0;
		$this->args['hide_count'] = 0;
		$this->args['hide_order'] = (int)(!(get_option('w2dc_show_orderby_links')));
		$this->args['show_views_switcher'] = (int)get_option('w2dc_views_switcher');
		$this->args['listings_view_type'] = get_option('w2dc_views_switcher_default');
		$this->args['listings_view_grid_columns'] = (int)get_option('w2dc_views_switcher_grid_columns');
		$this->args['listing_thumb_width'] = (int)get_option('w2dc_listing_thumb_width');
		$this->args['wrap_logo_list_view'] = (int)get_option('w2dc_wrap_logo_list_view');
		$this->args['logo_animation_effect'] = (int)get_option('w2dc_logo_animation_effect');

		add_action('get_header', array($this, 'configure_seo_filters'), 2);
		
		// adapted for WPML
		add_filter('icl_ls_languages', array($this, 'adapt_wpml_urls'));
		add_filter('WPML_alternate_hreflang', array($this, 'alternate_hreflang'), 10, 2);

		// this is possible to build custom home page instead of static set of blocks
		if (!$this->is_single && $this->custom_home)
			$this->template = 'frontend/listings_block.tpl.php';
		
		apply_filters('w2dc_frontend_controller_construct', $this);
	}
	
	// adapted for WPML
	public function adapt_wpml_urls($w_active_languages) {
		global $sitepress, $w2dc_instance;

		// WPML will not switch language using $sitepress->switch_lang() function when there is 'lang=' parameter in the URL, so we have to use such hack
		if ($sitepress->get_option('language_negotiation_type') == 3)
			remove_all_filters('icl_current_language');

		foreach ($w_active_languages AS &$language) {
			$sitepress->switch_lang($language['language_code']);
			$w2dc_instance->getIndexPage();
			if ($this->is_single && ($tlisting_post_id = icl_object_id($this->listing->post->ID, W2DC_POST_TYPE, false, $language['language_code']))) {
				$language['url'] = get_permalink($tlisting_post_id);
			}
			if ($this->is_category && ($tterm_id = icl_object_id($this->category->term_id, W2DC_CATEGORIES_TAX, false, $language['language_code']))) {
				$tterm = get_term($tterm_id, W2DC_CATEGORIES_TAX);
				$language['url'] = get_term_link($tterm);
			}
			if ($this->is_location && ($tterm_id = icl_object_id($this->location->term_id, W2DC_LOCATIONS_TAX, false, $language['language_code']))) {
				$tterm = get_term($tterm_id, W2DC_LOCATIONS_TAX);
				$language['url'] = get_term_link($tterm, W2DC_LOCATIONS_TAX);
			}
			if ($this->is_tag && ($tterm_id = icl_object_id($this->tag->term_id, W2DC_TAGS_TAX, false, $language['language_code']))) {
				$tterm = get_term($tterm_id, W2DC_TAGS_TAX);
				$language['url'] = get_term_link($tterm, W2DC_TAGS_TAX);
			}
			if ($this->is_favourites) {
				$language['url'] = w2dc_directoryUrl(array('w2dc_action' => 'myfavourites'));
			}
			$sitepress->switch_lang(ICL_LANGUAGE_CODE);
			$w2dc_instance->getIndexPage();
		}
		return $w_active_languages;
	}
	
	// adapted for WPML
	public function alternate_hreflang($url, $lang) {
		global $sitepress, $w2dc_instance;
		
		// WPML will not switch language using $sitepress->switch_lang() function when there is 'lang=' parameter in the URL, so we have to use such hack
		if ($sitepress->get_option('language_negotiation_type') == 3)
			remove_all_filters('icl_current_language');

		$sitepress->switch_lang($lang['language_code']);
		$w2dc_instance->getIndexPage();
		if ($this->is_single && ($tlisting_post_id = icl_object_id($this->listing->post->ID, W2DC_POST_TYPE, false, $lang['language_code']))) {
			$url = get_permalink($tlisting_post_id);
		}
		if ($this->is_category && ($tterm_id = icl_object_id($this->category->term_id, W2DC_CATEGORIES_TAX, false, $lang['language_code']))) {
			$tterm = get_term($tterm_id, W2DC_CATEGORIES_TAX);
			$url = get_term_link($tterm, W2DC_CATEGORIES_TAX);
		}
		if ($this->is_location && ($tterm_id = icl_object_id($this->location->term_id, W2DC_LOCATIONS_TAX, false, $lang['language_code']))) {
			$tterm = get_term($tterm_id, W2DC_LOCATIONS_TAX);
			$url = get_term_link($tterm, W2DC_LOCATIONS_TAX);
		}
		if ($this->is_tag && ($tterm_id = icl_object_id($this->tag->term_id, W2DC_TAGS_TAX, false, $lang['language_code']))) {
			$tterm = get_term($tterm_id, W2DC_TAGS_TAX);
			$url = get_term_link($tterm, W2DC_TAGS_TAX);
		}
		if ($this->is_favourites) {
			$url = w2dc_directoryUrl(array('w2dc_action' => 'myfavourites'));
		}
		$sitepress->switch_lang(ICL_LANGUAGE_CODE);
		$w2dc_instance->getIndexPage();

		return $url;
	}

	// Add listing ID to query string while rendering Contact Form 7
	public function w2dc_add_listing_id_to_wpcf7($url) {
		if ($this->is_single)
			$url = esc_url(add_query_arg('listing_id', $this->listing->post->ID, $url));
		
		return $url;
	}

	public function contactOwnerAction($post) {
		$validation = new form_validation;
		if (!is_user_logged_in()) {
			$validation->set_rules('contact_name', __('Contact name', 'W2DC'), 'required');
			$validation->set_rules('contact_email', __('Contact email', 'W2DC'), 'required|valid_email');
		}
		$validation->set_rules('contact_message', __('Your message', 'W2DC'), 'required|max_length[1500]');
		if ($validation->run()) {
			if (!is_user_logged_in()) {
				$contact_name = $validation->result_array('contact_name');
				$contact_email = $validation->result_array('contact_email');
			} else {
				$current_user = wp_get_current_user();
				$contact_name = $current_user->display_name;
				$contact_email = $current_user->user_email;
			}
			$contact_message = $validation->result_array('contact_message');

			if (w2dc_is_recaptcha_passed()) {
				$listing = new w2dc_listing();
				$listing->loadListingFromPost($post->ID);
				$listing_owner = get_userdata($post->post_author);

				/* $headers =  "MIME-Version: 1.0\r\n" .
						"From: $contact_name <$contact_email>\r\n" .
						"Reply-To: $contact_email\r\n" .
						"Content-Type: text/plain; charset=\"" . get_option('blog_charset') . "\"\r\n"; */
				$headers[] = "From: $contact_name <$contact_email>";
				$headers[] = "Reply-To: $contact_email";

				global $w2dc_instance;
				$subject = "[" . get_option('blogname') . "] " . sprintf(__('%s contacted you about your listing "%s"', 'W2DC'), $contact_name, $listing->title());

				$body = w2dc_renderTemplate('emails/contact_form.tpl.php',
						array(
								'contact_name' => $contact_name,
								'contact_email' => $contact_email,
								'contact_message' => $contact_message,
								'listing_title' => get_the_title($post),
								'listing_url' => get_permalink($post->ID)
						), true);

				if (wp_mail($listing_owner->user_email, $subject, $body, $headers)) {
					unset($_POST['contact_name']);
					unset($_POST['contact_email']);
					unset($_POST['contact_message']);
					w2dc_addMessage(__('You message was sent successfully!', 'W2DC'));
				} else
					w2dc_addMessage(__('An error occurred and your message wasn\'t sent!', 'W2DC'), 'error');
			} else {
				w2dc_addMessage(__('Verification code wasn\'t entered correctly!', 'W2DC'), 'error');
			}
		} else {
			w2dc_addMessage($validation->error_string(), 'error');
		}
	}
	
	public function configure_seo_filters() {
		if ($this->is_home || $this->is_single || $this->is_search || $this->is_category || $this->is_location || $this->is_tag || $this->is_favourites) {
			add_filter('wp_title', array($this, 'page_title'), 10, 2);
			if (defined('WPSEO_VERSION')) {
				if (version_compare(WPSEO_VERSION, '1.7.2', '<'))
					global $wpseo_front;
				else
					$wpseo_front = WPSEO_Frontend::get_instance();

				// real number of page for WP SEO plugin
				if ($this->query) {
					global $wp_query;
					$wp_query->max_num_pages = $this->query->max_num_pages;
				}

				// remove force_rewrite option of WP SEO plugin
				remove_action('get_header', array(&$wpseo_front, 'force_rewrite_output_buffer'));
				remove_action('wp_footer', array(&$wpseo_front, 'flush_cache'));
				
				remove_filter('wp_title', array(&$wpseo_front, 'title'), 15, 3);
				remove_action('wp_head', array(&$wpseo_front, 'head'), 1, 1);
	
				add_action('wp_head', array( $this, 'page_meta'));
			}
		}
	}
	
	public function page_meta() {
		if (version_compare(WPSEO_VERSION, '1.7.2', '<'))
			global $wpseo_front;
		else
			$wpseo_front = WPSEO_Frontend::get_instance();
		if ($this->is_single) {
			global $post;
			$saved_page = $post;
			$post = get_post($this->listing->post->ID);
	
			$wpseo_front->metadesc();
			$wpseo_front->metakeywords();
	
			$post = $saved_page;
		} elseif ($this->is_category) {
			if (version_compare(WPSEO_VERSION, '1.5.0', '<'))
				$metadesc = wpseo_get_term_meta($this->category, $this->category->taxonomy, 'desc');
			else
				$metadesc = WPSEO_Taxonomy_Meta::get_term_meta($this->category, $this->category->taxonomy, 'desc');

			if (!$metadesc && isset($wpseo_front->options['metadesc-' . $this->category->taxonomy]))
				$metadesc = wpseo_replace_vars($wpseo_front->options['metadesc-' . $this->category->taxonomy], (array) $this->category );
			$metadesc = apply_filters('wpseo_metadesc', trim($metadesc));
			echo '<meta name="description" content="' . esc_attr(strip_tags(stripslashes($metadesc))) . '"/>' . "\n";
		} elseif ($this->is_location) {
			if (version_compare(WPSEO_VERSION, '1.5.0', '<'))
				$metadesc = wpseo_get_term_meta($this->location, $this->location->taxonomy, 'desc');
			else
				$metadesc = WPSEO_Taxonomy_Meta::get_term_meta($this->location, $this->location->taxonomy, 'desc');

			if (!$metadesc && isset($wpseo_front->options['metadesc-' . $this->location->taxonomy]))
				$metadesc = wpseo_replace_vars($wpseo_front->options['metadesc-' . $this->location->taxonomy], (array) $this->location );
			$metadesc = apply_filters('wpseo_metadesc', trim($metadesc));
			echo '<meta name="description" content="' . esc_attr(strip_tags(stripslashes($metadesc))) . '"/>' . "\n";
		} elseif ($this->is_tag) {
			if (version_compare(WPSEO_VERSION, '1.5.0', '<'))
				$metadesc = wpseo_get_term_meta($this->tag, $this->tag->taxonomy, 'desc');
			else
				$metadesc = WPSEO_Taxonomy_Meta::get_term_meta($this->tag, $this->tag->taxonomy, 'desc');

			if (!$metadesc && isset($wpseo_front->options['metadesc-' . $this->tag->taxonomy]))
				$metadesc = wpseo_replace_vars($wpseo_front->options['metadesc-' . $this->tag->taxonomy], (array) $this->tag );
			$metadesc = apply_filters('wpseo_metadesc', trim($metadesc));
			echo '<meta name="description" content="' . esc_attr(strip_tags(stripslashes($metadesc))) . '"/>' . "\n";
		} elseif ($this->is_home) {
			$wpseo_front->metadesc();
			$wpseo_front->metakeywords();
		}
	}
	
	public function page_title($title, $separator = '|') {
		global $w2dc_instance;
		if (defined('WPSEO_VERSION')) {
			if (version_compare(WPSEO_VERSION, '1.7.2', '<'))
				global $wpseo_front;
			else
				$wpseo_front = WPSEO_Frontend::get_instance();
			if ($this->is_single) {
				$title = $wpseo_front->get_content_title(get_post($this->listing->post->ID));
				return esc_html(strip_tags(stripslashes(apply_filters('wpseo_title', $title))));
			} elseif ($this->is_category) {
				if (version_compare(WPSEO_VERSION, '1.5.0', '<'))
					$title = trim(wpseo_get_term_meta($this->category, $this->category->taxonomy, 'title'));
				else
					$title = trim(WPSEO_Taxonomy_Meta::get_term_meta($this->category, $this->category->taxonomy, 'title'));

				if (!empty($title))
					return wpseo_replace_vars($title, (array)$this->category);
				return $wpseo_front->get_title_from_options('title-tax-' . $this->category->taxonomy, $this->category);
			} elseif ($this->is_location) {
				if (version_compare(WPSEO_VERSION, '1.5.0', '<'))
					$title = trim(wpseo_get_term_meta($this->location, $this->location->taxonomy, 'title'));
				else
					$title = trim(WPSEO_Taxonomy_Meta::get_term_meta($this->location, $this->location->taxonomy, 'title'));

				if (!empty($title))
					return wpseo_replace_vars($title, (array)$this->location);
				return $wpseo_front->get_title_from_options('title-tax-' . $this->location->taxonomy, $this->location);
			} elseif ($this->is_tag) {
				if (version_compare(WPSEO_VERSION, '1.5.0', '<'))
					$title = trim(wpseo_get_term_meta($this->tag, $this->tag->taxonomy, 'title'));
				else
					$title = trim(WPSEO_Taxonomy_Meta::get_term_meta($this->tag, $this->tag->taxonomy, 'title'));

				if (!empty($title))
					return wpseo_replace_vars($title, (array)$this->tag);
				return $wpseo_front->get_title_from_options('title-tax-' . $this->tag->taxonomy, $this->tag);
			} elseif ($this->is_home) {
				//$page = get_post($w2dc_instance->index_page_id);
				//return $wpseo_front->get_title_from_options('title-' . W2DC_POST_TYPE, (array) $page);
				return $wpseo_front->get_content_title();
			}

			if ($this->getPageTitle())
				$title = esc_html(strip_tags(stripslashes($this->getPageTitle()))) . ' ';
			return $title . wpseo_replace_vars('%%sep%% %%sitename%%', array());
		} else {
			$directory_title = '';
			if ($this->getPageTitle())
				$directory_title = $this->getPageTitle() . ' ' . $separator . ' ';
			if (get_wpml_dependent_option('w2dc_directory_title')) 
				$directory_title .= get_wpml_dependent_option('w2dc_directory_title');
			else
				$directory_title .= get_option('blogname');
			return $directory_title;
		}
	
		return $title;
	}

	// rewrite canonical URL
	public function rel_canonical_with_custom_tag_override() {
		echo '<link rel="canonical" href="' . get_permalink($this->listing->post->ID) . '" />
';
	}
	
	// Adding the Open Graph in the Language Attributes
	public function add_opengraph_doctype($output) {
		return $output . ' xmlns:og="http://opengraphprotocol.org/schema/" xmlns:fb="http://www.facebook.com/2008/fbml"';
	}
	
	// Lets add Open Graph Meta Info
	public function insert_fb_in_head() {
		echo '<meta property="og:type" content="article" />
';
		echo '<meta property="og:title" content="' . esc_attr($this->listing->title()) . '" />
';
		if ($this->listing->post->post_excerpt)
			$excerpt = $this->listing->post->post_excerpt;
		else
			$excerpt = $this->listing->getExcerptFromContent();
		echo '<meta property="og:description" content="' . esc_attr($excerpt) . '" />
';		
		echo '<meta property="og:url" content="' . get_permalink($this->listing->post->ID) . '" />
';
		echo '<meta property="og:site_name" content="' . get_option('w2dc_directory_title') . '" />
';
		if ($this->listing->logo_image) {
			$thumbnail_src = $src_full = wp_get_attachment_image_src($this->listing->logo_image, 'medium');
			echo '<meta property="og:image" content="' . esc_attr($thumbnail_src[0]) . '" />
';
		}
	}

	public function display() {
		$output =  w2dc_renderTemplate($this->template, array('frontend_controller' => $this), true);
		wp_reset_postdata();

		return $output;
	}
}

add_action('init', 'w2dc_handle_wpcf7');
function w2dc_handle_wpcf7() {
	if (defined('WPCF7_VERSION')) {
		if (version_compare(WPCF7_VERSION, '3.9', '<')) {
			// Old versions
			if (get_option('w2dc_listing_contact_form') && defined('WPCF7_VERSION') && get_wpml_dependent_option('w2dc_listing_contact_form_7'))
				add_action('wpcf7_before_send_mail', 'w2dc_wpcf7_handle_email');
			
			function w2dc_wpcf7_handle_email(&$WPCF7_ContactForm = null) {
				if (isset($_GET['listing_id'])) {
					$post = get_post($_GET['listing_id']);
			
					if ($post && isset($_POST['_wpcf7']) && preg_match_all('/'.get_shortcode_regex().'/s', get_wpml_dependent_option('w2dc_listing_contact_form_7'), $matches))
						foreach ($matches[2] AS $key=>$shortcode) {
							if ($shortcode == 'contact-form-7') {
								if ($attrs = shortcode_parse_atts($matches[3][$key]))
									if (isset($attrs['id']) && $attrs['id'] == $_POST['_wpcf7']) {
										if (($listing_owner = get_userdata($post->post_author)) && $listing_owner->user_email)
											$WPCF7_ContactForm->mail['recipient'] = $listing_owner->user_email;
									}
							}
						}
				}
			}
		} else {
			// New versions
			if (get_option('w2dc_listing_contact_form') && defined('WPCF7_VERSION') && get_wpml_dependent_option('w2dc_listing_contact_form_7'))
				add_filter('wpcf7_mail_components', 'w2dc_wpcf7_handle_email', 10, 2);
			
			function w2dc_wpcf7_handle_email($WPCF7_components, $WPCF7_currentform) {
				if (isset($_GET['listing_id'])) {
					$post = get_post($_GET['listing_id']);
	
					$mail = $WPCF7_currentform->prop('mail');
					// DO not touch mail_2
					if ($mail['recipient'] == $WPCF7_components['recipient'])
						if ($post && isset($_POST['_wpcf7']) && preg_match_all('/'.get_shortcode_regex().'/s', get_wpml_dependent_option('w2dc_listing_contact_form_7'), $matches))
							foreach ($matches[2] AS $key=>$shortcode) {
								if ($shortcode == 'contact-form-7') {
									if ($attrs = shortcode_parse_atts($matches[3][$key]))
										if (isset($attrs['id']) && $attrs['id'] == $_POST['_wpcf7']) {
											if (($listing_owner = get_userdata($post->post_author)) && $listing_owner->user_email)
												$WPCF7_components['recipient'] = $listing_owner->user_email;
										}
								}
							}
				}
				return $WPCF7_components;
			}
		}
	}
}

?>