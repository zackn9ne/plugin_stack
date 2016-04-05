<?php
/*
Plugin Name: Web 2.0 Directory plugin
Plugin URI: http://www.salephpscripts.com/wordpress_directory/
Description: Provides an ability to build any kind of directory site: classifieds, events directory, cars, bikes, boats and other vehicles dealers site, pets, real estate portal on your WordPress powered site. In other words - whatever you want.
Version: 1.10.1
Author: Mihail Chepovskiy
Author URI: http://www.salephpscripts.com
License: GPLv2 or any later version
*/

define('W2DC_VERSION', '1.10.1');

define('W2DC_PATH', plugin_dir_path(__FILE__));
define('W2DC_URL', plugins_url('/', __FILE__));

function w2dc_loadPaths() {
	define('W2DC_TEMPLATES_PATH', W2DC_PATH . 'templates/');

	if (!defined('W2DC_THEME_MODE')) {
		define('W2DC_RESOURCES_PATH', W2DC_PATH . 'resources/');
		define('W2DC_RESOURCES_URL', W2DC_URL . 'resources/');

		// Categories icons constant
		define('W2DC_CATEGORIES_ICONS_PATH', W2DC_RESOURCES_PATH . 'images/categories_icons/');
		define('W2DC_CATEGORIES_ICONS_URL', W2DC_RESOURCES_URL . 'images/categories_icons/');

		// Categories icons constant
		define('W2DC_LOCATION_ICONS_PATH', W2DC_RESOURCES_PATH . 'images/locations_icons/');
		define('W2DC_LOCATIONS_ICONS_URL', W2DC_RESOURCES_URL . 'images/locations_icons/');

		// Map Markers Icons Path
		define('W2DC_MAP_ICONS_PATH', W2DC_RESOURCES_PATH . 'images/map_icons/');
		define('W2DC_MAP_ICONS_URL', W2DC_RESOURCES_URL . 'images/map_icons/');
	}
}
add_action('init', 'w2dc_loadPaths', 0);

define('W2DC_POST_TYPE', 'w2dc_listing');
define('W2DC_CATEGORIES_TAX', 'w2dc-category');
define('W2DC_LOCATIONS_TAX', 'w2dc-location');
define('W2DC_TAGS_TAX', 'w2dc-tag');

// Deactivate deprecated modules
include_once(ABSPATH . 'wp-admin/includes/plugin.php');
$fsubmit =  'w2dc_fsubmit/w2dc_fsubmit.php';
if (is_plugin_active($fsubmit)) {
	add_option('w2dc_fsubmit_addon', 1);
	deactivate_plugins($fsubmit);
	exit('Press F5!!!');
}
$payments =  'w2dc_payments/w2dc_payments.php';
if (is_plugin_active($payments)) {
	add_option('w2dc_payments_addon', 1);
	deactivate_plugins($payments);
	exit('Press F5!!!');
}
$ratings =  'w2dc_ratings/w2dc_ratings.php';
if (is_plugin_active($ratings)) {
	add_option('w2dc_ratings_addon', 1);
	deactivate_plugins($ratings);
	exit('Press F5!!!');
}
$elocations =  'w2dc_elocations/w2dc_elocations.php';
if (is_plugin_active($elocations)) {
	deactivate_plugins($elocations);
	exit('Press F5!!!');
}
$esearch =  'w2dc_esearch/w2dc_esearch.php';
if (is_plugin_active($esearch)) {
	deactivate_plugins($esearch);
	exit('Press F5!!!');
}

include_once W2DC_PATH . 'install.php';
include_once W2DC_PATH . 'classes/admin.php';
include_once W2DC_PATH . 'classes/form_validation.php';
include_once W2DC_PATH . 'classes/listings/listings_manager.php';
include_once W2DC_PATH . 'classes/listings/listing.php';
include_once W2DC_PATH . 'classes/categories_manager.php';
include_once W2DC_PATH . 'classes/media_manager.php';
include_once W2DC_PATH . 'classes/content_fields/content_fields_manager.php';
include_once W2DC_PATH . 'classes/content_fields/content_fields.php';
include_once W2DC_PATH . 'classes/locations/locations_manager.php';
include_once W2DC_PATH . 'classes/locations/locations_levels_manager.php';
include_once W2DC_PATH . 'classes/locations/locations_levels.php';
include_once W2DC_PATH . 'classes/locations/location.php';
include_once W2DC_PATH . 'classes/levels/levels_manager.php';
include_once W2DC_PATH . 'classes/levels/levels.php';
include_once W2DC_PATH . 'classes/frontend_controller.php';
include_once W2DC_PATH . 'classes/shortcodes/directory_controller.php';
include_once W2DC_PATH . 'classes/shortcodes/listings_controller.php';
include_once W2DC_PATH . 'classes/shortcodes/map_controller.php';
include_once W2DC_PATH . 'classes/shortcodes/categories_controller.php';
include_once W2DC_PATH . 'classes/shortcodes/locations_controller.php';
include_once W2DC_PATH . 'classes/shortcodes/search_controller.php';
include_once W2DC_PATH . 'classes/shortcodes/slider_controller.php';
include_once W2DC_PATH . 'classes/shortcodes/buttons_controller.php';
include_once W2DC_PATH . 'classes/ajax_controller.php';
include_once W2DC_PATH . 'classes/settings_manager.php';
include_once W2DC_PATH . 'classes/google_maps.php';
include_once W2DC_PATH . 'classes/widgets.php';
include_once W2DC_PATH . 'classes/csv_manager.php';
include_once W2DC_PATH . 'classes/location_geoname.php';
include_once W2DC_PATH . 'classes/search_form.php';
include_once W2DC_PATH . 'classes/search_fields/search_fields.php';
include_once W2DC_PATH . 'functions.php';
include_once W2DC_PATH . 'functions_ui.php';
include_once W2DC_PATH . 'maps_styles.php';
include_once W2DC_PATH . 'vc.php';
include_once W2DC_PATH . 'vafpress-framework/bootstrap.php';
include_once W2DC_PATH . 'classes/customization/color_schemes.php';

global $w2dc_instance;
global $w2dc_messages;

define('W2DC_MAIN_SHORTCODE', 'webdirectory');
define('W2DC_LISTING_SHORTCODE', 'webdirectory-listing');

/*
 * There are 2 types of shortcodes in the system:
 1. those process as simple wordpress shortcodes
 2. require initialization on 'wp' hook
 
 [webdirectory] shortcode must be initialized on 'wp' hook and then renders as simple shortcode
 */
global $w2dc_shortcodes, $w2dc_shortcodes_init;
$w2dc_shortcodes = array(
		'webdirectory' => 'w2dc_directory_controller',
		'webdirectory-listing' => 'w2dc_directory_controller',
		'webdirectory-listings' => 'w2dc_listings_controller',
		'webdirectory-map' => 'w2dc_map_controller',
		'webdirectory-categories' => 'w2dc_categories_controller',
		'webdirectory-locations' => 'w2dc_locations_controller',
		'webdirectory-search' => 'w2dc_search_controller',
		'webdirectory-slider' => 'w2dc_slider_controller',
		'webdirectory-buttons' => 'w2dc_buttons_controller',
);
$w2dc_shortcodes_init = array(
		'webdirectory' => 'w2dc_directory_controller',
		'webdirectory-listing' => 'w2dc_directory_controller',
		'webdirectory-listings' => 'w2dc_listings_controller',
);

class w2dc_plugin {
	public $admin;
	public $listings_manager;
	public $locations_manager;
	public $locations_levels_manager;
	public $categories_manager;
	public $content_fields_manager;
	public $media_manager;
	public $settings_manager;
	public $levels_manager;
	public $csv_manager;

	public $current_listing; // this is object of listing under edition right now
	public $levels;
	public $index_page_id;
	public $index_page_slug;
	public $index_page_url;
	public $listing_page_id;
	public $listing_page_slug;
	public $listing_page_url;
	public $frontend_controllers = array();
	public $_frontend_controllers = array(); // this duplicate property needed because we unset each controller when we render shortcodes, but WP doesn't really know which shortcode already was processed
	public $action;
	
	public $radius_values_array = array();
	
	public $order_by_date = false; // special flag, used to display or hide sticky pin

	public function __construct() {
		register_activation_hook(__FILE__, array($this, 'activation'));
		register_deactivation_hook(__FILE__, array($this, 'deactivation'));
	}
	
	public function activation() {
		global $wp_version;

		if (version_compare($wp_version, '3.6', '<')) {
			deactivate_plugins(basename(__FILE__)); // Deactivate ourself
			wp_die("Sorry, but you can't run this plugin on current WordPress version, it requires WordPress v3.6 or higher.");
		}
		flush_rewrite_rules();
		
		wp_schedule_event(current_time('timestamp'), 'hourly', 'sheduled_events');
	}

	public function deactivation() {
		flush_rewrite_rules();

		wp_clear_scheduled_hook('sheduled_events');
	}
	
	public function init() {
		global $w2dc_instance, $w2dc_shortcodes, $wpdb;

		$_GET = stripslashes_deep($_GET);
		if (isset($_REQUEST['w2dc_action']))
			$this->action = $_REQUEST['w2dc_action'];

		add_action('plugins_loaded', array($this, 'load_textdomains'));
		
		add_action('sheduled_events', array($this, 'suspend_expired_listings'));

		foreach ($w2dc_shortcodes AS $shortcode=>$function)
			add_shortcode($shortcode, array($this, 'renderShortcode'));

		add_action('init', array($this, 'register_post_type'), 0);
		add_action('init', array($this, 'getIndexPage'), 0);
		
		if (!isset($wpdb->content_fields))
			$wpdb->content_fields = $wpdb->prefix . 'w2dc_content_fields';
		if (!isset($wpdb->content_fields_groups))
			$wpdb->content_fields_groups = $wpdb->prefix . 'w2dc_content_fields_groups';
		if (!isset($wpdb->levels))
			$wpdb->levels = $wpdb->prefix . 'w2dc_levels';
		if (!isset($wpdb->levels_relationships))
			$wpdb->levels_relationships = $wpdb->prefix . 'w2dc_levels_relationships';
		if (!isset($wpdb->locations_levels))
			$wpdb->locations_levels = $wpdb->prefix . 'w2dc_locations_levels';
		if (!isset($wpdb->locations_relationships))
			$wpdb->locations_relationships = $wpdb->prefix . 'w2dc_locations_relationships';

		add_action('wp', array($this, 'loadFrontendControllers'), 1);

		$w2dc_instance->levels = new w2dc_levels;
		$w2dc_instance->locations_levels = new w2dc_locations_levels;
		$w2dc_instance->content_fields = new w2dc_content_fields;
		$w2dc_instance->search_fields = new w2dc_search_fields;

		$w2dc_instance->ajax_controller = new w2dc_ajax_controller;

		$this->admin = new w2dc_admin();

		add_filter('template_include', array($this, 'printlisting_template'));

		add_action('wp_loaded', array($this, 'wp_loaded'));
		add_filter('query_vars', array($this, 'add_query_vars'));
		add_filter('rewrite_rules_array', array($this, 'rewrite_rules'));
		
		add_filter('redirect_canonical', array($this, 'prevent_wrong_redirect'), 10, 2);
		add_filter('post_type_link', array($this, 'listing_permalink'), 10, 3);
		add_filter('term_link', array($this, 'category_permalink'), 10, 3);
		add_filter('term_link', array($this, 'location_permalink'), 10, 3);
		add_filter('term_link', array($this, 'tag_permalink'), 10, 3);

		add_filter('comments_open', array($this, 'filter_comment_status'), 100, 2);
		
		add_filter('wp_unique_post_slug_is_bad_flat_slug', array($this, 'reserve_slugs'), 10, 2);
		
		add_filter('no_texturize_shortcodes', array($this, 'w2dc_no_texturize'));

		// WPML builds wrong urls for translations,
		// also Paid Memberships Pro plugin breaks its redirect after login and before session had started,
		// that is why this filter must be disabled
		//add_filter('home_url', array($this, 'add_trailing_slash_to_home'), 1000, 2);

		add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts_styles'));
		add_action('wp_head', array($this, 'enqueue_dynamic_css'), 9999);
	}

	public function load_textdomains() {
		load_plugin_textdomain('W2DC', '', dirname(plugin_basename( __FILE__ )) . '/languages');
	}
	
	public function w2dc_no_texturize($shortcodes) {
		global $w2dc_shortcodes;
		
		foreach ($w2dc_shortcodes AS $shortcode=>$function)
			$shortcodes[] = $shortcode;
		
		return $shortcodes;
	}

	public function renderShortcode() {
		global $w2dc_shortcodes;

		// remove content filters in order not to break the layout of page
		remove_filter('the_content', 'wpautop');
		remove_filter('the_content', 'wptexturize');
		remove_filter('the_content', 'shortcode_unautop');
		remove_filter('the_content', 'convert_chars');
		remove_filter('the_content', 'prepend_attachment');
		remove_filter('the_content', 'convert_smilies');

		$attrs = func_get_args();
		$shortcode = $attrs[2];

		if (isset($this->_frontend_controllers[$shortcode])) {
			$shortcode_controllers = $this->_frontend_controllers[$shortcode];
			foreach ($shortcode_controllers AS $key=>&$controller) {
				unset($this->_frontend_controllers[$shortcode][$key]);
				if (method_exists($controller, 'display'))
					return $controller->display();
			}
		}

		if (isset($w2dc_shortcodes[$shortcode])) {
			$shortcode_class = $w2dc_shortcodes[$shortcode];
			if ($attrs[0] === '')
				$attrs[0] = array();
			$shortcode_instance = new $shortcode_class();
			$this->frontend_controllers[$shortcode][] = $shortcode_instance;
			$shortcode_instance->init($attrs[0], $shortcode);

			if (method_exists($shortcode_instance, 'display'))
				return $shortcode_instance->display();
		}
	}

	public function loadFrontendControllers() {
		global $post;

		if ($post) {
			$pattern = get_shortcode_regex();
			$this->loadNestedFrontendController($pattern, $post->post_content);
		}
	}

	// this may be recursive function to catch nested shortcodes
	public function loadNestedFrontendController($pattern, $content) {
		global $w2dc_shortcodes_init, $w2dc_shortcodes;

		if (preg_match_all('/'.$pattern.'/s', $content, $matches) && array_key_exists(2, $matches)) {
			foreach ($matches[2] AS $key=>$shortcode) {
				if ($shortcode != 'shortcodes') {
					if (isset($w2dc_shortcodes_init[$shortcode]) && class_exists($w2dc_shortcodes_init[$shortcode])) {
						$shortcode_class = $w2dc_shortcodes_init[$shortcode];
						if (!($attrs = shortcode_parse_atts($matches[3][$key])))
							$attrs = array();
						$shortcode_instance = new $shortcode_class();
						$this->frontend_controllers[$shortcode][] = $shortcode_instance;
						$this->_frontend_controllers[$shortcode][] = $shortcode_instance;
						$shortcode_instance->init($attrs, $shortcode);
					} elseif (isset($w2dc_shortcodes[$shortcode]) && class_exists($w2dc_shortcodes[$shortcode])) {
						$shortcode_class = $w2dc_shortcodes[$shortcode];
						$this->frontend_controllers[$shortcode][] = $shortcode_class;
					}
					if ($shortcode_content = $matches[5][$key])
						$this->loadNestedFrontendController($pattern, $shortcode_content);
				}
			}
		}
	}

	public function getIndexPage() {
		if ($array = w2dc_getIndexPage()) {
			$this->index_page_id = $array['id'];
			$this->index_page_slug = $array['slug'];
			$this->index_page_url = $array['url'];
		}

		if ($array = w2dc_getListingPage()) {
			$this->listing_page_id = $array['id'];
			$this->listing_page_slug = $array['slug'];
			$this->listing_page_url = $array['url'];
		}
		
		if ($this->index_page_slug == get_option('w2dc_category_slug') || $this->index_page_slug == get_option('w2dc_tag_slug'))
			w2dc_addMessage('Categories or tags slug is the same as slug of directory page! This may cause problems. Go to <a href="' . admin_url('admin.php?page=w2dc_settings') . '">settings page</a> and enter another slug.', 'error');
		
		if ($this->index_page_id === 0 && is_admin())
			w2dc_addMessage(sprintf(__('<b>Web 2.0 Directory plugin</b>: sorry, but there isn\'t any page with [webdirectory] shortcode. Create <a href="%s">this special page</a> for you?', 'W2DC'), admin_url('admin.php?page=w2dc_admin&action=directory_page_installation')));
	}

	public function add_query_vars($vars) {
		$vars[] = 'listing-w2dc';
		$vars[] = 'category-w2dc';
		$vars[] = 'location-w2dc';
		$vars[] = 'tag-w2dc';
		$vars[] = 'tax-slugs-w2dc';

		if (!is_admin()) {
			// order query var may damage sorting of listings at the frontend - it shows WP posts instead of directory listings
			$key = array_search('order', $vars);
			unset($vars[$key]);
		}

		return $vars;
	}
	
	public function rewrite_rules($rules) {
		return $this->w2dc_addRules() + $rules;
	}
	
	public function w2dc_addRules() {
		global $wp_rewrite;
		//var_dump($wp_rewrite);
	
		//var_dump($wp_rewrite->rewrite_rules());
		/* 		foreach (get_option('rewrite_rules') AS $key=>$rule)
		 echo $key . '
		' . $rule . '
	
	
		'; */
	
		$page_url = $this->index_page_slug;
		
		foreach (get_post_ancestors($this->index_page_id) AS $parent_id) {
			$parent = get_page($parent_id);
			$page_url = $parent->post_name . '/' . $page_url;
		}

		$rules['(' . $page_url . ')/' . $wp_rewrite->pagination_base . '/?([0-9]{1,})/?$'] = 'index.php?page_id=' .  $this->index_page_id . '&paged=$matches[2]';
		$rules['(' . $page_url . ')/?$'] = 'index.php?page_id=' .  $this->index_page_id;
	
		$rules['(' . $page_url . ')?/?' . get_option('w2dc_category_slug') . '/(.+?)/' . $wp_rewrite->pagination_base . '/?([0-9]{1,})/?$'] = 'index.php?page_id=' .  $this->index_page_id . '&category-w2dc=$matches[2]&paged=$matches[3]';
		$rules['(' . $page_url . ')?/?' . get_option('w2dc_category_slug') . '/(.+?)/?$'] = 'index.php?page_id=' .  $this->index_page_id . '&category-w2dc=$matches[2]';

		$rules['(' . $page_url . ')?/?' . get_option('w2dc_location_slug') . '/(.+?)/' . $wp_rewrite->pagination_base . '/?([0-9]{1,})/?$'] = 'index.php?page_id=' .  $this->index_page_id . '&location-w2dc=$matches[2]&paged=$matches[3]';
		$rules['(' . $page_url . ')?/?' . get_option('w2dc_location_slug') . '/(.+?)/?$'] = 'index.php?page_id=' .  $this->index_page_id . '&location-w2dc=$matches[2]';
	
		$rules['(' . $page_url . ')?/?' . get_option('w2dc_tag_slug') . '/([^\/.]+)/' . $wp_rewrite->pagination_base . '/?([0-9]{1,})/?$'] = 'index.php?page_id=' .  $this->index_page_id . '&tag-w2dc=$matches[2]&paged=$matches[3]';
		$rules['(' . $page_url . ')?/?' . get_option('w2dc_tag_slug') . '/([^\/.]+)/?$'] = 'index.php?page_id=' .  $this->index_page_id . '&tag-w2dc=$matches[2]';

		if ($this->listing_page_id)
			$listing_page_id = $this->listing_page_id;
		else 
			$listing_page_id = $this->index_page_id;

		$rules[$page_url . '/([^\/.]+)/?$'] = 'index.php?page_id=' . $listing_page_id . '&listing-w2dc=$matches[1]';
		if (strpos(get_option('permalink_structure'), '/%post_id%/%postname%') !== 0) {
			// /%post_id%/%postname%/ will not work when the same structure enabled for native WP posts
			$rules['(' . $page_url . ')?/?([0-9]+)/([^\/.]+)/?$'] = 'index.php?page_id=' . $listing_page_id . '&listing-w2dc=$matches[3]';
		}
		if (get_option('w2dc_permalinks_structure') == 'post_id') {
			// Avoid mismatches with archive pages with /%year%/%monthnum%/ permalinks structure
			$rules['(?!(?:199[0-9]|20[012][0-9])/(?:0[1-9]|1[012]))([0-9]{1,})/([^\/.]+)/?$'] = 'index.php?page_id=' . $listing_page_id . '&listing-w2dc=$matches[2]';
		}

		$rules['(' . $page_url . ')?/?' . get_option('w2dc_listing_slug') . '/(.+?)/([^\/.]+)/?$'] = 'index.php?page_id=' . $listing_page_id . '&tax-slugs-w2dc=$matches[2]&listing-w2dc=$matches[3]';
		$rules['(' . $page_url . ')?/?' . get_option('w2dc_listing_slug') . '/([^\/.]+)/?$'] = 'index.php?page_id=' . $listing_page_id . '&listing-w2dc=$matches[2]';
	
		return $rules;
	}
	
	public function wp_loaded() {
		if ($rules = get_option('rewrite_rules'))
			foreach ($this->w2dc_addRules() as $key=>$value)
				if (!isset($rules[$key]) || $rules[$key] != $value) {
					global $wp_rewrite;
					$wp_rewrite->flush_rules();
					return;
				}
	}
	
	public function prevent_wrong_redirect($redirect_url, $requested_url) {
		if (!is_null($this->frontend_controllers))
			return $requested_url;
	
		return $redirect_url;
	}

	public function listing_permalink($permalink, $post, $leavename) {
		if ($post->post_type == W2DC_POST_TYPE) {
			global $wp_rewrite;
			if ($wp_rewrite->using_permalinks()) {
				if ($leavename)
					$postname = '%postname%';
				else
					$postname = $post->post_name;

				switch (get_option('w2dc_permalinks_structure')) {
					case 'post_id':
						return w2dc_directoryUrl($post->ID . '/' . $postname);
						break;
					case 'postname':
						if (get_option('page_on_front') == $this->index_page_id)
							return w2dc_directoryUrl($post->ID . '/' . $postname);
						else
							return w2dc_directoryUrl($postname);
						break;
					case 'listing_slug':
						if (get_option('w2dc_listing_slug'))
							return w2dc_directoryUrl(get_option('w2dc_listing_slug') . '/' . $postname);
						else
							if (get_option('page_on_front') == $this->index_page_id)
								return w2dc_directoryUrl($post->ID . '/' . $postname);
							else
								return w2dc_directoryUrl($postname);
						break;
					case 'category_slug':
						if (get_option('w2dc_listing_slug') && get_option('w2dc_category_slug') && ($terms = get_the_terms($post->ID, W2DC_CATEGORIES_TAX))) {
							$term = array_shift($terms);
							if ($cur_term = w2dc_get_term_by_path(get_query_var('category-w2dc'))) {
								foreach ($terms AS $lterm) {
									$term_path_ids = w2dc_get_term_parents_ids($lterm->term_id, W2DC_CATEGORIES_TAX);
									if ($cur_term->term_id == $lterm->term_id) { $term = $lterm; break; }  // exact term much more better
									if (in_array($cur_term->term_id, $term_path_ids)) { $term = $lterm; break; }
								}
							}
							$uri = '';
							if ($parents = w2dc_get_term_parents_slugs($term->term_id, W2DC_CATEGORIES_TAX))
								$uri = implode('/', $parents);
							return w2dc_directoryUrl(get_option('w2dc_listing_slug') . '/' . $uri . '/' . $postname);
						} else
							if (get_option('page_on_front') == $this->index_page_id)
								return w2dc_directoryUrl($post->ID . '/' . $postname);
							else
								return w2dc_directoryUrl($postname);
						break;
					case 'location_slug':
						if (get_option('w2dc_listing_slug') && get_option('w2dc_location_slug') && ($terms = get_the_terms($post->ID, W2DC_LOCATIONS_TAX)) && ($term = array_shift($terms))) {
							if ($cur_term = w2dc_get_term_by_path(get_query_var('location-w2dc'))) {
								foreach ($terms AS $lterm) {
									$term_path_ids = w2dc_get_term_parents_ids($lterm->term_id, W2DC_LOCATIONS_TAX);
									if ($cur_term->term_id == $lterm->term_id) { $term = $lterm; break; }  // exact term much more better
									if (in_array($cur_term->term_id, $term_path_ids)) { $term = $lterm; break; }
								}
							}
							$uri = '';
							if ($parents = w2dc_get_term_parents_slugs($term->term_id, W2DC_LOCATIONS_TAX))
								$uri = implode('/', $parents);
							return w2dc_directoryUrl(get_option('w2dc_listing_slug') . '/' . $uri . '/' . $postname);
						} else {
							if (get_option('page_on_front') == $this->index_page_id)
								return w2dc_directoryUrl($post->ID . '/' . $postname);
							else
								return w2dc_directoryUrl($postname);
						}
						break;
					case 'tag_slug':
						if (get_option('w2dc_listing_slug') && get_option('w2dc_tag_slug') && ($terms = get_the_terms($post->ID, W2DC_TAGS_TAX)) && ($term = array_shift($terms))) {
							return w2dc_directoryUrl(get_option('w2dc_listing_slug') . '/' . $term->slug . '/' . $postname);
						} else
							if (get_option('page_on_front') == $this->index_page_id)
								return w2dc_directoryUrl($post->ID . '/' . $postname);
							else
								return w2dc_directoryUrl($postname);
						break;
					default:
						if (get_option('page_on_front') == $this->index_page_id)
							return w2dc_directoryUrl($post->ID . '/' . $postname);
						else
							return w2dc_directoryUrl($postname);
				}
			} else
				return w2dc_ListingUrl($post->post_name);
		}
		return $permalink;
	}

	public function category_permalink($permalink, $category, $tax) {
		if ($tax == W2DC_CATEGORIES_TAX) {
			global $wp_rewrite;
			if ($wp_rewrite->using_permalinks()) {
				$uri = '';
				if ($parents = w2dc_get_term_parents_slugs($category->term_id, W2DC_CATEGORIES_TAX))
					$uri = implode('/', $parents);
				return w2dc_directoryUrl(get_option('w2dc_category_slug') . '/' . $uri);
			} else
				return w2dc_directoryUrl(array('category-w2dc' => $category->slug));
		}
		return $permalink;
	}

	public function location_permalink($permalink, $location, $tax) {
		if ($tax == W2DC_LOCATIONS_TAX) {
			global $wp_rewrite;
			if ($wp_rewrite->using_permalinks()) {
				$uri = '';
				if ($parents = w2dc_get_term_parents_slugs($location->term_id, W2DC_LOCATIONS_TAX))
					$uri = implode('/', $parents);
				return w2dc_directoryUrl(get_option('w2dc_location_slug') . '/' . $uri);
			} else
				return w2dc_directoryUrl(array('location-w2dc' => $location->slug));
		}
		return $permalink;
	}

	public function tag_permalink($permalink, $tag, $tax) {
		if ($tax == W2DC_TAGS_TAX) {
			global $wp_rewrite;
			if ($wp_rewrite->using_permalinks())
				return w2dc_directoryUrl(get_option('w2dc_tag_slug') . '/' . $tag->slug);
			else
				return w2dc_directoryUrl(array('tag-w2dc' => $tag->slug));
		}
		return $permalink;
	}
	
	public function reserve_slugs($is_bad_flat_slug, $slug) {
		if (in_array($slug, array(get_option('w2dc_listing_slug'), get_option('w2dc_category_slug'), get_option('w2dc_location_slug'), get_option('w2dc_tag_slug'))))
			return true;
		return $is_bad_flat_slug;
	}

	public function register_post_type() {
		$args = array(
			'labels' => array(
				'name' => __('Directory listings', 'W2DC'),
				'singular_name' => __('Directory listing', 'W2DC'),
				'add_new' => __('Create new listing', 'W2DC'),
				'add_new_item' => __('Create new listing', 'W2DC'),
				'edit_item' => __('Edit listing', 'W2DC'),
				'new_item' => __('New listing', 'W2DC'),
				'view_item' => __('View listing', 'W2DC'),
				'search_items' => __('Search listings', 'W2DC'),
				'not_found' =>  __('No listings found', 'W2DC'),
				'not_found_in_trash' => __('No listings found in trash', 'W2DC')
			),
			'has_archive' => true,
			'description' => __('Directory listings', 'W2DC'),
			'public' => true,
			'exclude_from_search' => false, // this must be false otherwise it breaks pagination for custom taxonomies
			'supports' => array('title', 'author', 'comments'),
			'menu_icon' => W2DC_RESOURCES_URL . 'images/menuicon.png',
		);
		if (get_option('w2dc_enable_description'))
			$args['supports'][] = 'editor';
		if (get_option('w2dc_enable_summary'))
			$args['supports'][] = 'excerpt';
		register_post_type(W2DC_POST_TYPE, $args);
		
		register_taxonomy(W2DC_CATEGORIES_TAX, W2DC_POST_TYPE, array(
				'hierarchical' => true,
				'has_archive' => true,
				'labels' => array(
					'name' =>  __('Listing categories', 'W2DC'),
					'menu_name' =>  __('Directory categories', 'W2DC'),
					'singular_name' => __('Category', 'W2DC'),
					'add_new_item' => __('Create category', 'W2DC'),
					'new_item_name' => __('New category', 'W2DC'),
					'edit_item' => __('Edit category', 'W2DC'),
					'view_item' => __('View category', 'W2DC'),
					'update_item' => __('Update category', 'W2DC'),
					'search_items' => __('Search categories', 'W2DC'),
				),
			)
		);
		register_taxonomy(W2DC_LOCATIONS_TAX, W2DC_POST_TYPE, array(
				'hierarchical' => true,
				'has_archive' => true,
				'labels' => array(
					'name' =>  __('Listing locations', 'W2DC'),
					'menu_name' =>  __('Directory locations', 'W2DC'),
					'singular_name' => __('Location', 'W2DC'),
					'add_new_item' => __('Create location', 'W2DC'),
					'new_item_name' => __('New location', 'W2DC'),
					'edit_item' => __('Edit location', 'W2DC'),
					'view_item' => __('View location', 'W2DC'),
					'update_item' => __('Update location', 'W2DC'),
					'search_items' => __('Search locations', 'W2DC'),
					
				),
			)
		);
		register_taxonomy(W2DC_TAGS_TAX, W2DC_POST_TYPE, array(
				'hierarchical' => false,
				'labels' => array(
					'name' =>  __('Listing tags', 'W2DC'),
					'menu_name' =>  __('Directory tags', 'W2DC'),
					'singular_name' => __('Tag', 'W2DC'),
					'add_new_item' => __('Create tag', 'W2DC'),
					'new_item_name' => __('New tag', 'W2DC'),
					'edit_item' => __('Edit tag', 'W2DC'),
					'view_item' => __('View tag', 'W2DC'),
					'update_item' => __('Update tag', 'W2DC'),
					'search_items' => __('Search tags', 'W2DC'),
				),
			)
		);

		if (!get_option('w2dc_installed_directory') || get_option('w2dc_installed_directory_version') != W2DC_VERSION)
			w2dc_install_directory();
	}

	public function suspend_expired_listings() {
		global $wpdb;

		$posts_ids = $wpdb->get_col($wpdb->prepare("
				SELECT
					wp_pm1.post_id
				FROM
					{$wpdb->postmeta} AS wp_pm1
				LEFT JOIN
					{$wpdb->postmeta} AS wp_pm2 ON wp_pm1.post_id=wp_pm2.post_id
				LEFT JOIN
					{$wpdb->levels_relationships} AS wp_lr ON wp_lr.post_id=wp_pm1.post_id
				LEFT JOIN
					{$wpdb->levels} AS wp_l ON wp_l.id=wp_lr.level_id
				WHERE
					wp_pm1.meta_key = '_expiration_date' AND
					wp_pm1.meta_value < %d AND
					wp_pm2.meta_key = '_listing_status' AND
					(wp_pm2.meta_value = 'active' OR wp_pm2.meta_value = 'stopped') AND
					(wp_l.active_years != 0 OR wp_l.active_months != 0 OR wp_l.active_days != 0)
			", time()));
		foreach ($posts_ids AS $post_id) {
			if (!get_post_meta($post_id, '_expiration_notification_sent', true)) {
				$post = get_post($post_id);
				$listing_owner = get_userdata($post->post_author);
			
				/* $headers =  "MIME-Version: 1.0\n" .
						"From: get_option('blogname' <" . get_option('admin_email') . ">\n" .
						"Reply-To: get_option('admin_email')\n" .
						"Content-Type: text/plain; charset=\"" . get_option('blog_charset') . "\"\n"; */
				$headers[] = "From: " . get_option('blogname') . " <" . get_option('admin_email') . ">";
				$headers[] = "Reply-To: " . get_option('admin_email');
			
				$subject = "[" . get_option('blogname') . "] " . __('Expiration notification', 'W2DC');
			
				$body = str_replace('[listing]', $post->post_title,
						str_replace('[link]', ((get_option('w2dc_fsubmit_addon') && isset($this->dashboard_page_url) && $this->dashboard_page_url) ? w2dc_dashboardUrl(array('w2dc_action' => 'renew_listing', 'listing_id' => $post->ID)) : admin_url('options.php?page=w2dc_renew&listing_id=' . $post->ID)),
				get_option('w2dc_expiration_notification')));

				$listings_ids = array();
				
				// adapted for WPML
				global $sitepress;
				if (function_exists('icl_object_id') && $sitepress) {
					$trid = $sitepress->get_element_trid($post_id, 'post_' . W2DC_POST_TYPE);
					$translations = $sitepress->get_element_translations($trid);
					foreach ($translations AS $lang=>$translation)
						$listings_ids[] = $translation->element_id;
				} else
					$listings_ids[] = $post_id;
			
				if (wp_mail($listing_owner->user_email, $subject, $body, $headers)) {
					foreach ($listings_ids AS $listing_id)
						add_post_meta($listing_id, '_expiration_notification_sent', true);
				}

				foreach ($listings_ids AS $listing_id) {
					update_post_meta($listing_id, '_listing_status', 'expired');
					wp_update_post(array('ID' => $listing_id, 'post_status' => 'draft')); // This needed in order terms counts were always actual
				}
			}
		}

		$posts_ids = $wpdb->get_col($wpdb->prepare("
				SELECT
					wp_pm1.post_id
				FROM
					{$wpdb->postmeta} AS wp_pm1
				LEFT JOIN
					{$wpdb->postmeta} AS wp_pm2 ON wp_pm1.post_id=wp_pm2.post_id
				LEFT JOIN
					{$wpdb->levels_relationships} AS wp_lr ON wp_lr.post_id=wp_pm1.post_id
				LEFT JOIN
					{$wpdb->levels} AS wp_l ON wp_l.id=wp_lr.level_id
				WHERE
					wp_pm1.meta_key = '_expiration_date' AND
					wp_pm1.meta_value < %d AND
					wp_pm2.meta_key = '_listing_status' AND
					(wp_pm2.meta_value = 'active' OR wp_pm2.meta_value = 'stopped') AND
					(wp_l.active_years != 0 OR wp_l.active_months != 0 OR wp_l.active_days != 0)
			", time()+(get_option('w2dc_send_expiration_notification_days')*86400)));
		foreach ($posts_ids AS $post_id) {
			if (!get_post_meta($post_id, '_preexpiration_notification_sent', true)) {
				$post = get_post($post_id);
				$listing_owner = get_userdata($post->post_author);
				
				/* $headers =  "MIME-Version: 1.0\n" .
						"From: get_option('blogname' <" . get_option('admin_email') . ">\n" .
						"Reply-To: get_option('admin_email')\n" .
						"Content-Type: text/plain; charset=\"" . get_option('blog_charset') . "\"\n"; */
				$headers[] = "From: " . get_option('blogname') . " <" . get_option('admin_email') . ">";
				$headers[] = "Reply-To: " . get_option('admin_email');

				$subject = "[" . get_option('blogname') . "] " . __('Expiration notification', 'W2DC');
				
				$body = str_replace('[listing]', $post->post_title,
						str_replace('[days]',
				get_option('w2dc_send_expiration_notification_days'), get_option('w2dc_preexpiration_notification')));
				
				if (wp_mail($listing_owner->user_email, $subject, $body, $headers)) {
					$listings_ids = array();
					
					// adapted for WPML
					global $sitepress;
					if (function_exists('icl_object_id') && $sitepress) {
						$trid = $sitepress->get_element_trid($post_id, 'post_' . W2DC_POST_TYPE);
						$translations = $sitepress->get_element_translations($trid);
						foreach ($translations AS $lang=>$translation)
							$listings_ids[] = $translation->element_id;
					} else
						$listings_ids[] = $post_id;
					
					foreach ($listings_ids AS $listing_id)
						add_post_meta($listing_id, '_preexpiration_notification_sent', true);
				}
			}
		}
	}

	/**
	 * Special template for listings printing functionality
	 */
	public function printlisting_template($template) {
		if ((is_page($this->index_page_id) || is_page($this->listing_page_id)) && ($this->action == 'printlisting' || $this->action == 'pdflisting')) {
			if (is_file(W2DC_TEMPLATES_PATH . 'frontend/listing_print-custom.tpl.php'))
				$template = W2DC_TEMPLATES_PATH . 'frontend/listing_print-custom.tpl.php';
			else
				$template = W2DC_TEMPLATES_PATH . 'frontend/listing_print.tpl.php';
		}
		return $template;
	}
	
	function filter_comment_status($open, $post_id) {
		$post = get_post($post_id);
		if ($post->post_type == W2DC_POST_TYPE) {
			if (get_option('w2dc_listings_comments_mode') == 'enabled')
				return true;
			elseif (get_option('w2dc_listings_comments_mode') == 'disabled')
				return false;
			else 
				return $open;
		} else
			return $open;
	}

	/**
	 * Get property by shortcode name
	 * 
	 * @param string $shortcode
	 * @param string $property if property missed - return controller object
	 * @return mixed
	 */
	public function getShortcodeProperty($shortcode, $property = false) {
		if (!isset($this->frontend_controllers[$shortcode]) || !isset($this->frontend_controllers[$shortcode][0]))
			return false;

		if ($property && !isset($this->frontend_controllers[$shortcode][0]->$property))
			return false;

		if ($property)
			return $this->frontend_controllers[$shortcode][0]->$property;
		else 
			return $this->frontend_controllers[$shortcode][0];
	}
	
	public function getShortcodeByHash($hash) {
		if (!isset($this->frontend_controllers) || !is_array($this->frontend_controllers) || empty($this->frontend_controllers))
			return false;

		foreach ($this->frontend_controllers AS $shortcodes)
			foreach ($shortcodes AS $controller)
				if (is_object($controller) && $controller->hash == $hash)
					return $controller;
	}
	
	public function getListingsShortcodeByuID($uid) {
		foreach ($this->frontend_controllers AS $shortcodes)
			foreach ($shortcodes AS $controller)
				if (is_object($controller) && get_class($controller) == 'w2dc_listings_controller' && $controller->args['uid'] == $uid)
					return $controller;
	}

	public function enqueue_scripts_styles($load_scripts_styles = false) {
		global $w2dc_enqueued;
		if (($this->frontend_controllers || $load_scripts_styles) && !$w2dc_enqueued) {
			add_action('wp_head', array($this, 'enqueue_global_vars'));

			wp_register_style('w2dc_bootstrap', W2DC_RESOURCES_URL . 'css/bootstrap.css');
			if (!(function_exists('is_rtl') && is_rtl()))
				wp_register_style('w2dc_frontend', W2DC_RESOURCES_URL . 'css/frontend.css');
			else
				wp_register_style('w2dc_frontend', W2DC_RESOURCES_URL . 'css/frontend-rtl.css');
			wp_register_style('w2dc_font_awesome', W2DC_RESOURCES_URL . 'css/font-awesome.css');
	
			if (is_file(W2DC_RESOURCES_PATH . 'css/frontend-custom.css'))
				wp_register_style('w2dc_frontend-custom', W2DC_RESOURCES_URL . 'css/frontend-custom.css');
	
			wp_register_script('js_functions', W2DC_RESOURCES_URL . 'js/js_functions.js', array('jquery'), false, true);

			wp_register_script('categories_scripts', W2DC_RESOURCES_URL . 'js/manage_categories.js', array('jquery'), false, true);

			wp_register_style('media_styles', W2DC_RESOURCES_URL . 'lightbox/css/lightbox.css');
			wp_register_script('media_scripts_lightbox', W2DC_RESOURCES_URL . 'lightbox/js/lightbox.min.js', array('jquery'), false, true);
			wp_register_script('media_scripts', W2DC_RESOURCES_URL . 'js/ajaxfileupload.js', array('jquery'), false, true);

			// this jQuery UI version 1.10.4
			if (get_option('w2dc_jquery_ui_schemas')) $ui_theme = w2dc_get_dynamic_option('w2dc_jquery_ui_schemas'); else $ui_theme = 'smoothness';
			wp_register_style('jquery-ui-style', '//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/themes/' . $ui_theme . '/jquery-ui.css');

			wp_register_script('w2dc_google_maps_edit', W2DC_RESOURCES_URL . 'js/google_maps_edit.js', array('jquery'), false, true);

			wp_enqueue_style('w2dc_bootstrap');
			wp_enqueue_style('w2dc_frontend');
			wp_enqueue_style('w2dc_font_awesome');
	
			// Include dynamic-css file only when we are not in palettes comparison mode
			if (!isset($_COOKIE['w2dc_compare_palettes']) || !get_option('w2dc_compare_palettes')) {
				// Include dynamically generated css file if this file exists
				$upload_dir = wp_upload_dir();
				$filename = trailingslashit(set_url_scheme($upload_dir['baseurl'])) . 'w2dc-plugin.css';
				$filename_dir = trailingslashit($upload_dir['basedir']) . 'w2dc-plugin.css';
				global $wp_filesystem;
				if (empty($wp_filesystem)) {
					require_once(ABSPATH .'/wp-admin/includes/file.php');
					WP_Filesystem();
				}
				if ($wp_filesystem && trim($wp_filesystem->get_contents($filename_dir))) // if css file creation success
					wp_enqueue_style('w2dc-dynamic-css', $filename);
			}
	
			wp_enqueue_style('w2dc_frontend-custom');

			wp_enqueue_script('jquery-ui-dialog');
			wp_enqueue_script('jquery-ui-autocomplete');
			if (!get_option('w2dc_notinclude_jqueryui_css'))
				wp_enqueue_style('jquery-ui-style');

			wp_enqueue_script('js_functions');
			
			wp_register_style('listings_slider', W2DC_RESOURCES_URL . 'css/bxslider/jquery.bxslider.css');
			wp_enqueue_style('listings_slider');

			// Single Listing page
			if ($this->getShortcodeProperty('webdirectory', 'is_single') || $this->getShortcodeProperty('webdirectory-listing', 'is_single')) {
				if (get_option('w2dc_images_lightbox') && get_option('w2dc_enable_lighbox_gallery')) {
					wp_enqueue_style('media_styles');
					wp_enqueue_script('media_scripts_lightbox');
				}
			}
			
			wp_localize_script(
				'js_functions',
				'google_maps_callback',
				array(
						'callback' => 'w2dc_load_maps_api'
				)
			);

			$w2dc_enqueued = true;
		}
	}
	
	public function enqueue_global_vars() {
		// adapted for WPML
		global $sitepress;
		if (function_exists('icl_object_id') && $sitepress) {
			$ajaxurl = admin_url('admin-ajax.php?lang=' .  $sitepress->get_current_language());
		} else
			$ajaxurl = admin_url('admin-ajax.php');

		echo '
<script>
';
		echo 'var controller_args_array = {};
';
		echo 'var map_markers_attrs_array = [];
';
		echo 'var map_markers_attrs = (function(map_id, markers_array, enable_radius_cycle, enable_clusters, show_summary_button, show_readmore_button, map_style_name, map_attrs) {
		this.map_id = map_id;
		this.markers_array = markers_array;
		this.enable_radius_cycle = enable_radius_cycle;
		this.enable_clusters = enable_clusters;
		this.show_summary_button = show_summary_button;
		this.show_readmore_button = show_readmore_button;
		this.map_style_name = map_style_name;
		this.map_attrs = map_attrs;
		});
';
		global $w2dc_maps_styles;
		echo 'var js_objects = ' . json_encode(
				array(
						'ajaxurl' => $ajaxurl,
						'ajax_loader_url' => W2DC_RESOURCES_URL . 'images/ajax-loader.gif',
						'ajax_iloader_url' => W2DC_RESOURCES_URL . 'images/ajax-indicator.gif',
						'ajax_loader_text' => __('Loading...', 'W2DC'),
						'search_button_text' => __('Search', 'W2DC'),
						'ajax_map_loader_url' => W2DC_RESOURCES_URL . 'images/ajax-map-loader.gif',
						'in_favourites_icon' => 'w2dc-glyphicon-heart-empty',
						'not_in_favourites_icon' => 'w2dc-glyphicon-heart',
						'in_favourites_msg' => __('Add Bookmark', 'W2DC'),
						'not_in_favourites_msg' => __('Remove Bookmark', 'W2DC'),
						'ajax_load' => (int)get_option('w2dc_ajax_load'),
						'ajax_initial_load' => (int)get_option('w2dc_ajax_initial_load'),
				)
		) . ';
';
			
		$map_content_fields = $this->content_fields->getMapContentFields();
		$map_content_fields_icons = array('w2dc-fa-info-circle');
		foreach ($map_content_fields AS $content_field)
			if (is_a($content_field, 'w2dc_content_field') && $content_field->icon_image)
				$map_content_fields_icons[] = $content_field->icon_image;
			else
				$map_content_fields_icons[] = '';
		echo 'var google_maps_objects = ' . json_encode(
				array(
						'notinclude_maps_api' => (int)get_option('w2dc_notinclude_maps_api'),
						'google_api_key' => get_option('w2dc_google_api_key'),
						'global_map_icons_path' => W2DC_MAP_ICONS_URL,
						'marker_image_width' => W2DC_MARKER_IMAGE_WIDTH,
						'marker_image_height' => W2DC_MARKER_IMAGE_HEIGHT,
						'marker_image_anchor_x' => W2DC_MARKER_ANCHOR_X,
						'marker_image_anchor_y' => W2DC_MARKER_ANCHOR_Y,
						'infowindow_width' => W2DC_INFOWINDOW_WIDTH,
						'infowindow_offset' => W2DC_INFOWINDOW_OFFSET,
						'infowindow_logo_width' => W2DC_INFOWINDOW_LOGO_WIDTH,
						'w2dc_map_content_fields_icons' => $map_content_fields_icons,
						'w2dc_map_info_window_button_readmore' => __('Read more »', 'W2DC'),
						'w2dc_map_info_window_button_summary' => __('« Summary', 'W2DC'),
						'map_style_name' => get_option('w2dc_map_style'),
						'map_styles' => $w2dc_maps_styles,
						/* 'draw_area_button' => __('Draw Area', 'W2DC'),
						'edit_area_button' => __('Edit Area', 'W2DC'),
						'apply_area_button' => __('Apply Area', 'W2DC'),
						'reload_map_button' => __('Reload Map', 'W2DC'),
						'my_location_button' => __('My Location', 'W2DC'), */
				)
		) . ';
';
		echo '</script>
';
	}

	// Include dynamically generated css code if css file does not exist.
	public function enqueue_dynamic_css($load_scripts_styles = false) {
		if ($this->frontend_controllers || $load_scripts_styles) {
			$upload_dir = wp_upload_dir();
			$filename = trailingslashit(set_url_scheme($upload_dir['baseurl'])) . 'w2dc-plugin.css';
			$filename_dir = trailingslashit($upload_dir['basedir']) . 'w2dc-plugin.css';
			global $wp_filesystem;
			if (empty($wp_filesystem)) {
				require_once(ABSPATH .'/wp-admin/includes/file.php');
				WP_Filesystem();
			}
			if ((!$wp_filesystem || !trim($wp_filesystem->get_contents($filename_dir))) ||
				// When we are in palettes comparison mode - this will build css according to $_COOKIE['w2dc_compare_palettes']
				(isset($_COOKIE['w2dc_compare_palettes']) && get_option('w2dc_compare_palettes')))
			{
				ob_start();
				include W2DC_PATH . '/classes/customization/dynamic_css.php';
				$dynamic_css = ob_get_contents();
				ob_get_clean();
				echo '<style type="text/css">
	';
				echo $dynamic_css;
				echo '</style>';
			}
		}
	}
}

$w2dc_instance = new w2dc_plugin();
$w2dc_instance->init();

if (get_option('w2dc_fsubmit_addon'))
	include_once W2DC_PATH . 'addons/w2dc_fsubmit/w2dc_fsubmit.php';
if (get_option('w2dc_payments_addon'))
	include_once W2DC_PATH . 'addons/w2dc_payments/w2dc_payments.php';
if (get_option('w2dc_ratings_addon'))
	include_once W2DC_PATH . 'addons/w2dc_ratings/w2dc_ratings.php';

?>
