<?php

class w2dc_admin {

	public function __construct() {
		global $w2dc_instance;

		add_action('admin_menu', array($this, 'menu'));

		$w2dc_instance->settings_manager = new w2dc_settings_manager;

		$w2dc_instance->levels_manager = new w2dc_levels_manager;

		$w2dc_instance->listings_manager = new w2dc_listings_manager;

		$w2dc_instance->locations_manager = new w2dc_locations_manager;

		$w2dc_instance->locations_levels_manager = new w2dc_locations_levels_manager;

		$w2dc_instance->categories_manager = new w2dc_categories_manager;

		$w2dc_instance->content_fields_manager = new w2dc_content_fields_manager;

		$w2dc_instance->media_manager = new w2dc_media_manager;

		$w2dc_instance->csv_manager = new w2dc_csv_manager;

		add_action('admin_menu', array($this, 'addChooseLevelPage'));
		add_action('load-post-new.php', array($this, 'handleLevel'));

		// hide some meta-blocks when create/edit posts
		add_action('admin_init', array($this, 'hideMetaBlocks'));
		
		add_filter('post_row_actions', array($this, 'removeQuickEdit'), 10, 2);

		add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts_styles'), 0);

		add_action('admin_notices', 'w2dc_renderMessages');

		add_action('wp_ajax_w2dc_generate_color_palette', array($this, 'generate_color_palette'));
		add_action('wp_ajax_nopriv_w2dc_generate_color_palette', array($this, 'generate_color_palette'));
		add_action('wp_ajax_get_jqueryui_theme', array($this, 'get_jqueryui_theme'));
		add_action('wp_ajax_nopriv_get_jqueryui_theme', array($this, 'get_jqueryui_theme'));
		add_action('vp_option_before_ajax_save', array($this, 'remove_colorpicker_cookie'));
		add_action('wp_footer', array($this, 'render_colorpicker'));
	}

	public function addChooseLevelPage() {
		add_submenu_page('options.php',
			__('Choose level of new listing', 'W2DC'),
			__('Choose level of new listing', 'W2DC'),
			'publish_posts',
			'w2dc_choose_level',
			array($this, 'chooseLevelsPage')
		);
	}

	// Special page to choose the level for new listing
	public function chooseLevelsPage() {
		global $w2dc_instance;

		$w2dc_instance->levels_manager->displayChooseLevelTable();
	}
	
	public function handleLevel() {
		global $w2dc_instance;

		if (isset($_GET['post_type']) && $_GET['post_type'] == W2DC_POST_TYPE) {
			if (!isset($_GET['level_id'])) {
				// adapted for WPML
				global $sitepress;
				if (function_exists('icl_object_id') && $sitepress && isset($_GET['trid']) && isset($_GET['lang']) && isset($_GET['source_lang'])) {
					global $sitepress;
					$listing_id = $sitepress->get_original_element_id_by_trid($_GET['trid']);
					
					$listing = new w2dc_listing();
					$listing->loadListingFromPost($listing_id);
					wp_redirect(add_query_arg(array('post_type' => 'w2dc_listing', 'level_id' => $listing->level->id, 'trid' => $_GET['trid'], 'lang' => $_GET['lang'], 'source_lang' => $_GET['source_lang']), admin_url('post-new.php')));
				} else {
					if (count($w2dc_instance->levels->levels_array) != 1) {
						wp_redirect(add_query_arg('page', 'w2dc_choose_level', admin_url('options.php')));
					} else {
						$single_level = array_shift($w2dc_instance->levels->levels_array);
						wp_redirect(add_query_arg(array('post_type' => 'w2dc_listing', 'level_id' => $single_level->id), admin_url('post-new.php')));
					}
				}
				die();
			}
		}
	}

	public function menu() {
		add_menu_page(__("Directory Admin", "W2DC"),
			__('Directory Admin', 'W2DC'),
			'administrator',
			'w2dc_admin',
			array($this, 'w2dc_index_page'),
			W2DC_RESOURCES_URL . 'images/menuicon.png'
		);
	}
	
	public function hideMetaBlocks() {
		 global $post, $pagenow;

		if (($pagenow == 'post-new.php' && isset($_GET['post_type']) && $_GET['post_type'] == W2DC_POST_TYPE) || ($pagenow == 'post.php' && $post && $post->post_type == W2DC_POST_TYPE)) {
			$user_id = get_current_user_id();
			update_user_meta($user_id, 'metaboxhidden_' . W2DC_POST_TYPE, array('authordiv', 'trackbacksdiv', 'commentstatusdiv', 'postcustom'));
		}
	}

	public function w2dc_index_page() {
		global $w2dc_instance;
		if ($w2dc_instance->index_page_id === 0 && isset($_GET['action']) && $_GET['action'] == 'directory_page_installation') {
			$page = array('post_status' => 'publish', 'post_title' => __('Web 2.0 Directory', 'W2DC'), 'post_type' => 'page', 'post_content' => '[webdirectory]', 'comment_status' => 'closed');
			if (wp_insert_post($page))
				w2dc_addMessage(__('"Web 2.0 Directory" page with [webdirectory] shortcode was successfully created, thank you!'));
		}
		w2dc_renderTemplate('admin_index.tpl.php');
	}
	
	public function removeQuickEdit($actions, $post) {
		if ($post->post_type == W2DC_POST_TYPE) {
			unset($actions['inline hide-if-no-js']);
			unset($actions['view']);
		}
		return $actions;
	}
	
	public function admin_enqueue_scripts_styles() {
		add_action('admin_head', array($this, 'enqueue_global_vars'));

		wp_register_style('w2dc_bootstrap', W2DC_RESOURCES_URL . 'css/bootstrap.css');
		wp_register_style('w2dc_admin', W2DC_RESOURCES_URL . 'css/admin.css');
		wp_register_style('w2dc_font_awesome', W2DC_RESOURCES_URL . 'css/font-awesome.css');
		wp_register_script('js_functions', W2DC_RESOURCES_URL . 'js/js_functions.js', array('jquery'), false, true);

		// this jQuery UI version 1.10.3 is for WP v3.7.1
		wp_register_style('jquery-ui-style', '//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/themes/smoothness/jquery-ui.css');

		wp_register_script('w2dc_google_maps_edit', W2DC_RESOURCES_URL . 'js/google_maps_edit.js', array('jquery'));

		wp_register_script('categories_edit_scripts', W2DC_RESOURCES_URL . 'js/categories_icons.js', array('jquery'));
		wp_register_script('categories_scripts', W2DC_RESOURCES_URL . 'js/manage_categories.js', array('jquery'));
		
		wp_register_script('locations_edit_scripts', W2DC_RESOURCES_URL . 'js/locations_icons.js', array('jquery'));
		
		wp_register_style('media_styles', W2DC_RESOURCES_URL . 'lightbox/css/lightbox.css');
		wp_register_script('media_scripts_lightbox', W2DC_RESOURCES_URL . 'lightbox/js/lightbox.min.js', array('jquery'));
		wp_register_script('media_scripts', W2DC_RESOURCES_URL . 'js/ajaxfileupload.js', array('jquery'));
		
		wp_enqueue_style('w2dc_bootstrap');
		wp_enqueue_style('w2dc_admin');
		wp_enqueue_style('w2dc_font_awesome');
		wp_enqueue_script('jquery-ui-dialog');
		wp_enqueue_style('jquery-ui-style');
		wp_enqueue_script('js_functions');

		wp_localize_script(
			'js_functions',
			'google_maps_callback',
			array(
					'callback' => 'w2dc_load_maps_api_backend'
			)
		);

		wp_enqueue_script('w2dc_google_maps_edit');
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
		echo 'var js_objects = ' . json_encode(
				array(
						'ajaxurl' => $ajaxurl,
						'ajax_loader_url' => W2DC_RESOURCES_URL . 'images/ajax-loader.gif',
				)
		) . ';
';

		global $w2dc_maps_styles;
		echo 'var google_maps_objects = ' . json_encode(
				array(
						'notinclude_maps_api' => (int)get_option('w2dc_notinclude_maps_api_backend'),
						'google_api_key' => get_option('w2dc_google_api_key'),
						'global_map_icons_path' => W2DC_MAP_ICONS_URL,
						'marker_image_width' => W2DC_MARKER_IMAGE_WIDTH,
						'marker_image_height' => W2DC_MARKER_IMAGE_HEIGHT,
						'marker_image_anchor_x' => W2DC_MARKER_ANCHOR_X,
						'marker_image_anchor_y' => W2DC_MARKER_ANCHOR_Y,
						'default_geocoding_location' => get_option('w2dc_default_geocoding_location'),
						'map_style_name' => get_option('w2dc_map_style'),
						'map_styles' => $w2dc_maps_styles,
				)
		) . ';
';
		echo '</script>
';
	}

	public function generate_color_palette() {
		ob_start();
		include W2DC_PATH . '/classes/customization/dynamic_css.php';
		$dynamic_css = ob_get_contents();
		ob_get_clean();

		echo $dynamic_css;
		die();
	}

	public function get_jqueryui_theme() {
		global $w2dc_color_schemes;

		if (isset($_COOKIE['w2dc_compare_palettes']) && get_option('w2dc_compare_palettes')) {
			$scheme = $_COOKIE['w2dc_compare_palettes'];
			if ($scheme && isset($w2dc_color_schemes[$scheme]['w2dc_jquery_ui_schemas']))
				echo '//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/themes/' . $w2dc_color_schemes[$scheme]['w2dc_jquery_ui_schemas'] . '/jquery-ui.css';
		}
		die();
	}
	
	public function remove_colorpicker_cookie($opt) {
		if (isset($_COOKIE['w2dc_compare_palettes'])) {
			unset($_COOKIE['w2dc_compare_palettes']);
			setcookie('w2dc_compare_palettes', null, -1, '/');
		}
	}

	public function render_colorpicker() {
		global $w2dc_instance;

		if (!empty($w2dc_instance->frontend_controllers))
			if (get_option('w2dc_compare_palettes'))
				if (current_user_can('manage_options'))
					if (!(function_exists('is_rtl') && is_rtl()))
						w2dc_renderTemplate('color_picker/color_picker_panel.tpl.php');
					else
						w2dc_renderTemplate('color_picker/color_picker_panel_rtl.tpl.php');
	}
}
?>