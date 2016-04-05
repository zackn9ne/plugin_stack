<?php

add_action('vc_before_init', 'w2dc_vc_init');

function w2dc_vc_init() {
	global $w2dc_instance, $w2dc_fsubmit_instance, $w2dc_maps_styles;
	
	$map_styles = array('default' => '');
	foreach ($w2dc_maps_styles AS $name=>$style)
		$map_styles[$name] = $name;

	$levels = array(__('All', 'W2DC') => 0);
	foreach ($w2dc_instance->levels->levels_array AS $level) {
		$levels[$level->name] = $level->id;
	}

	$ordering = w2dc_orderingItems();
	
	add_shortcode_param('categoriesfield', 'w2dc_categories_param');
	function w2dc_categories_param($settings, $value) {
		$out = "<script>
			function updateTagChecked() { jQuery('#" . $settings['param_name'] . "').val(jQuery('#" . $settings['param_name'] . "_select').val()); }
	
			jQuery(function() {
				jQuery('#" . $settings['param_name'] . "_select option').click(updateTagChecked);
				updateTagChecked();
			});
		</script>";
	
		$out .= '<select multiple="multiple" id="' . $settings['param_name'] . '_select" name="' . $settings['param_name'] . '_select" style="height: 300px">';
		$out .= '<option value="0" ' . ((!$value) ? 'selected' : '') . '>' . __('- Select All -', 'W2DC') . '</option>';
		ob_start();
		w2dc_renderOptionsTerms(W2DC_CATEGORIES_TAX, 0, explode(',', $value));
		$out .= ob_get_clean();
		$out .= '</select>';
		$out .= '<input type="hidden" id="' . $settings['param_name'] . '" name="' . $settings['param_name'] . '" class="wpb_vc_param_value" value="' . $value . '" />';
	
		return $out;
	}

	add_shortcode_param('locationsfield', 'w2dc_locations_param');
	function w2dc_locations_param($settings, $value) {
		$out = "<script>
			function updateTagChecked() { jQuery('#" . $settings['param_name'] . "').val(jQuery('#" . $settings['param_name'] . "_select').val()); }
	
			jQuery(function() {
				jQuery('#" . $settings['param_name'] . "_select option').click(updateTagChecked);
				updateTagChecked();
			});
		</script>";
	
		$out .= '<select multiple="multiple" id="' . $settings['param_name'] . '_select" name="' . $settings['param_name'] . '_select" style="height: 300px">';
		$out .= '<option value="0" ' . ((!$value) ? 'selected' : '') . '>' . __('- Select All -', 'W2DC') . '</option>';
		ob_start();
		w2dc_renderOptionsTerms(W2DC_LOCATIONS_TAX, 0, explode(',', $value));
		$out .= ob_get_clean();
		$out .= '</select>';
		$out .= '<input type="hidden" id="' . $settings['param_name'] . '" name="' . $settings['param_name'] . '" class="wpb_vc_param_value" value="' . $value . '" />';
	
		return $out;
	}
	
	vc_map( array(
		'name'                    => __('Web 2.0 Directory', 'W2DC'),
		'description'             => __('Main shortcode', 'W2DC'),
		'base'                    => 'webdirectory',
		'icon'                    => W2DC_RESOURCES_URL . 'images/vc/webdirectory.png',
		'show_settings_on_create' => true,
		'category'                => __('Directory Content', 'W2DC'),
		'params'                  => array(
			array(
					'type' => 'dropdown',
					'param_name' => 'custom_home',
					'value' => array(__('No', 'W2DC') => '0', __('Yes', 'W2DC') => '1'),
					'heading' => __('Is it on custom home page?', 'W2DC'),
			),
		),
	));
	
	if ($w2dc_fsubmit_instance) {
		vc_map( array(
			'name'                    => __('Listings submit', 'W2DC'),
			'description'             => __('Listings submission pages', 'W2DC'),
			'base'                    => 'webdirectory-submit',
			'icon'                    => W2DC_RESOURCES_URL . 'images/vc/submit.png',
			'show_settings_on_create' => false,
			'category'                => __('Directory Content', 'W2DC'),
			'params'                  => array(
				array(
						'type' => 'dropdown',
						'param_name' => 'show_steps',
						'value' => array(__('No', 'W2DC') => '0', __('Yes', 'W2DC') => '1'),
						'heading' => __('Show submission steps?', 'W2DC'),
				),
				array(
						'type' => 'dropdown',
						'param_name' => 'columns',
						'value' => array('1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5'),
						'std' => 3,
						'heading' => __('Columns number on choose level page', 'W2DC'),
				),
				array(
						'type' => 'dropdown',
						'param_name' => 'columns_same_height',
						'value' => array(__('No', 'W2DC') => '0', __('Yes', 'W2DC') => '1'),
						'heading' => __('Show negative paratmeters?', 'W2DC'),
						'description' => __('Show parameters those have negation. For example, such row in table will be shown: Featured Listings - No. In other case it will be completely hidden.', 'W2DC'),
				),
				array(
						'type' => 'dropdown',
						'param_name' => 'show_period',
						'value' => array(__('Yes', 'W2DC') => '1', __('No', 'W2DC') => '0'),
						'heading' => __('Show level active period on choose level page?', 'W2DC'),
				),
				array(
						'type' => 'dropdown',
						'param_name' => 'show_sticky',
						'value' => array(__('Yes', 'W2DC') => '1', __('No', 'W2DC') => '0'),
						'heading' => __('Show is level sticky on choose level page?', 'W2DC'),
				),
				array(
						'type' => 'dropdown',
						'param_name' => 'show_featured',
						'value' => array(__('Yes', 'W2DC') => '1', __('No', 'W2DC') => '0'),
						'heading' => __('Show is level featured on choose level page?', 'W2DC'),
				),
				array(
						'type' => 'dropdown',
						'param_name' => 'show_categories',
						'value' => array(__('Yes', 'W2DC') => '1', __('No', 'W2DC') => '0'),
						'heading' => __('Show level\'s categories number on choose level page?', 'W2DC'),
				),
				array(
						'type' => 'dropdown',
						'param_name' => 'show_locations',
						'value' => array(__('Yes', 'W2DC') => '1', __('No', 'W2DC') => '0'),
						'heading' => __('Show level\'s locations number on choose level page?', 'W2DC'),
				),
				array(
						'type' => 'dropdown',
						'param_name' => 'show_maps',
						'value' => array(__('Yes', 'W2DC') => '1', __('No', 'W2DC') => '0'),
						'heading' => __('Show is level supports maps on choose level page?', 'W2DC'),
				),
				array(
						'type' => 'dropdown',
						'param_name' => 'show_images',
						'value' => array(__('Yes', 'W2DC') => '1', __('No', 'W2DC') => '0'),
						'heading' => __('Show level\'s images number on choose level page?', 'W2DC'),
				),
				array(
						'type' => 'dropdown',
						'param_name' => 'show_videos',
						'value' => array(__('Yes', 'W2DC') => '1', __('No', 'W2DC') => '0'),
						'heading' => __('Show level\'s videos number on choose level page?', 'W2DC'),
				),
			),
		));
		vc_map( array(
			'name'                    => __('Pricing table', 'W2DC'),
			'description'             => __('Listings levels table. Works in the same way as 1st step on Listings submit, displays only pricing table. Note, that page with Listings submit element required.', 'W2DC'),
			'base'                    => 'webdirectory-levels-table',
			'icon'                    => W2DC_RESOURCES_URL . 'images/vc/pricing_table.png',
			'show_settings_on_create' => false,
			'category'                => __('Directory Content', 'W2DC'),
			'params'                  => array(
				array(
						'type' => 'dropdown',
						'param_name' => 'columns',
						'value' => array('1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5'),
						'std' => 3,
						'heading' => __('Columns number on choose level page', 'W2DC'),
				),
				array(
						'type' => 'dropdown',
						'param_name' => 'columns_same_height',
						'value' => array(__('No', 'W2DC') => '0', __('Yes', 'W2DC') => '1'),
						'heading' => __('Show negative paratmeters?', 'W2DC'),
						'description' => __('Show parameters those have negation. For example, such row in table will be shown: Featured Listings - No. In other case it will be completely hidden.', 'W2DC'),
				),
				array(
						'type' => 'dropdown',
						'param_name' => 'show_period',
						'value' => array(__('Yes', 'W2DC') => '1', __('No', 'W2DC') => '0'),
						'heading' => __('Show level active period on choose level page?', 'W2DC'),
				),
				array(
						'type' => 'dropdown',
						'param_name' => 'show_sticky',
						'value' => array(__('Yes', 'W2DC') => '1', __('No', 'W2DC') => '0'),
						'heading' => __('Show is level sticky on choose level page?', 'W2DC'),
				),
				array(
						'type' => 'dropdown',
						'param_name' => 'show_featured',
						'value' => array(__('Yes', 'W2DC') => '1', __('No', 'W2DC') => '0'),
						'heading' => __('Show is level featured on choose level page?', 'W2DC'),
				),
				array(
						'type' => 'dropdown',
						'param_name' => 'show_categories',
						'value' => array(__('Yes', 'W2DC') => '1', __('No', 'W2DC') => '0'),
						'heading' => __('Show level\'s categories number on choose level page?', 'W2DC'),
				),
				array(
						'type' => 'dropdown',
						'param_name' => 'show_locations',
						'value' => array(__('Yes', 'W2DC') => '1', __('No', 'W2DC') => '0'),
						'heading' => __('Show level\'s locations number on choose level page?', 'W2DC'),
				),
				array(
						'type' => 'dropdown',
						'param_name' => 'show_maps',
						'value' => array(__('Yes', 'W2DC') => '1', __('No', 'W2DC') => '0'),
						'heading' => __('Show is level supports maps on choose level page?', 'W2DC'),
				),
				array(
						'type' => 'dropdown',
						'param_name' => 'show_images',
						'value' => array(__('Yes', 'W2DC') => '1', __('No', 'W2DC') => '0'),
						'heading' => __('Show level\'s images number on choose level page?', 'W2DC'),
				),
				array(
						'type' => 'dropdown',
						'param_name' => 'show_videos',
						'value' => array(__('Yes', 'W2DC') => '1', __('No', 'W2DC') => '0'),
						'heading' => __('Show level\'s videos number on choose level page?', 'W2DC'),
				),
			),
		));
		vc_map( array(
			'name'                    => __('Users Dashboard', 'W2DC'),
			'description'             => __('Directory frontend dashboard', 'W2DC'),
			'base'                    => 'webdirectory-dashboard',
			'icon'                    => W2DC_RESOURCES_URL . 'images/vc/dashboard.png',
			'show_settings_on_create' => false,
			'category'                => __('Directory Content', 'W2DC'),
		));
	}
	
	$vc_listings_args = array(
		'name'                    => __('Directory Listings', 'W2DC'),
		'description'             => __('Directory listings filtered by params', 'W2DC'),
		'base'                    => 'webdirectory-listings',
		'icon'                    => W2DC_RESOURCES_URL . 'images/vc/listings.png',
		'show_settings_on_create' => true,
		'category'                => __('Directory Content', 'W2DC'),
		'params'                  => array(
			array(
					'type' => 'textfield',
					'param_name' => 'uid',
					'value' => '',
					'heading' => __('Enter unique string to connect this shortcode with another elements', 'W2DC'),
			),
			array(
					'type' => 'dropdown',
					'param_name' => 'onepage',
					'value' => array(__('No', 'W2DC') => '0', __('Yes', 'W2DC') => '1'),
					'heading' => __('Show all possible listings on one page?', 'W2DC'),
			),
			array(
					'type' => 'dropdown',
					'param_name' => 'ajax_initial_load',
					'value' => array(__('No', 'W2DC') => '0', __('Yes', 'W2DC') => '1'),
					'heading' => __('Load listings only after the page was completely loaded.', 'W2DC'),
			),
			array(
					'type' => 'textfield',
					'param_name' => 'perpage',
					'value' => 10,
					'heading' => __('Number of listing per page', 'W2DC'),
					'description' => __('Number of listings to display per page. Set -1 to display all listings without paginator.', 'W2DC'),
					'dependency' => array('element' => 'onepage', 'value' => '0'),
			),
			array(
					'type' => 'dropdown',
					'param_name' => 'hide_paginator',
					'value' => array(__('No', 'W2DC') => '0', __('Yes', 'W2DC') => '1'),
					'heading' => __('Hide paginator', 'W2DC'),
					'description' => __('When paginator is hidden - it will display only exact number of listings.', 'W2DC'),
					'dependency' => array('element' => 'onepage', 'value' => '0'),
			),
			array(
					'type' => 'dropdown',
					'param_name' => 'sticky_featured',
					'value' => array(__('No', 'W2DC') => '0', __('Yes', 'W2DC') => '1'),
					'heading' => __('Show only sticky or/and featured listings?', 'W2DC'),
					'description' => __('Whether to show only sticky or/and featured listings.', 'W2DC'),
			),
			array(
					'type' => 'dropdown',
					'param_name' => 'order_by',
					'value' => $ordering,
					'heading' => __('Order by', 'W2DC'),
					'description' => __('Order listings by any of these parameter.', 'W2DC'),
			),
			array(
					'type' => 'dropdown',
					'param_name' => 'order',
					'value' => array(__('Ascending', 'W2DC') => 'ASC', __('Descending', 'W2DC') => 'DESC'),
					'description' => __('Direction of sorting.', 'W2DC'),
			),
			array(
					'type' => 'dropdown',
					'param_name' => 'hide_order',
					'value' => array(__('No', 'W2DC') => '0', __('Yes', 'W2DC') => '1'),
					'heading' => __('Hide ordering links?', 'W2DC'),
					'description' => __('Whether to hide ordering navigation links.', 'W2DC'),
			),
			array(
					'type' => 'dropdown',
					'param_name' => 'hide_count',
					'value' => array(__('No', 'W2DC') => '0', __('Yes', 'W2DC') => '1'),
					'heading' => __('Hide number of listings?', 'W2DC'),
					'description' => __('Whether to hide number of found listings.', 'W2DC'),
			),
			array(
					'type' => 'dropdown',
					'param_name' => 'show_views_switcher',
					'value' => array(__('Yes', 'W2DC') => '1', __('No', 'W2DC') => '0'),
					'heading' => __('Show listings views switcher?', 'W2DC'),
					'description' => __('Whether to show listings views switcher.', 'W2DC'),
			),
			array(
					'type' => 'dropdown',
					'param_name' => 'listings_view_type',
					'value' => array(__('List', 'W2DC') => 'list', __('Grid', 'W2DC') => 'grid'),
					'heading' => __('Listings view by default', 'W2DC'),
					'description' => __('Do not forget that selected view will be stored in cookies.', 'W2DC'),
			),
			array(
					'type' => 'dropdown',
					'param_name' => 'listings_view_grid_columns',
					'value' => array('1', '2', '3', '4'),
					'heading' => __('Number of columns for listings Grid View', 'W2DC'),
					'std' => 2,
			),
			array(
					'type' => 'textfield',
					'param_name' => 'listing_thumb_width',
					'value' => 300,
					'heading' => __('Listing thumbnail logo width in List View', 'W2DC'),
					'description' => __('in pixels', 'W2DC'),
			),
			array(
					'type' => 'dropdown',
					'param_name' => 'wrap_logo_list_view',
					'value' => array(__('No', 'W2DC') => '0', __('Yes', 'W2DC') => '1'),
					'heading' => __('Wrap logo image by text content in List View', 'W2DC'),
			),
			array(
					'type' => 'dropdown',
					'param_name' => 'logo_animation_effect',
					'value' => array(
							__('No effect', 'W2DC') => 0,
							sprintf(__('Animation effect #%d', 'W2DC'), 1) => 1,
							sprintf(__('Animation effect #%d', 'W2DC'), 2) => 2,
							sprintf(__('Animation effect #%d', 'W2DC'), 3) => 3,
							sprintf(__('Animation effect #%d', 'W2DC'), 4) => 4,
							sprintf(__('Animation effect #%d', 'W2DC'), 5) => 5,
							sprintf(__('Animation effect #%d', 'W2DC'), 6) => 6
					),
					'std' => 6,
					'heading' => __('Thumbnail animation hover effect', 'W2DC'),
			),
			array(
					'type' => 'textfield',
					'param_name' => 'address',
					'heading' => __('Address', 'W2DC'),
					'description' => __('Display listings near this address, recommended to set "radius" attribute.', 'W2DC'),
			),
			array(
					'type' => 'textfield',
					'param_name' => 'radius',
					'heading' => __('Radius', 'W2DC'),
					'description' => __('display listings near provided address within this radius in miles or kilometers.', 'W2DC'),
			),
			array(
					'type' => 'categoriesfield',
					'param_name' => 'categories',
					'value' => 0,
					'heading' => __('Categories', 'W2DC'),
			),
			array(
					'type' => 'locationsfield',
					'param_name' => 'locations',
					'value' => 0,
					'heading' => __('Locations', 'W2DC'),
			),
			array(
					'type' => 'checkbox',
					'param_name' => 'levels',
					'value' => $levels,
					'heading' => __('Listings levels', 'W2DC'),
					'description' => __('Categories may be dependent from listings levels.', 'W2DC'),
			),
			array(
					'type' => 'textfield',
					'param_name' => 'post__in',
					'heading' => __('Exact listings', 'W2DC'),
					'description' => __('Comma separated string of listings IDs. Possible to display exact listings.', 'W2DC'),
			),
		),
	);
	foreach ($w2dc_instance->search_fields->search_fields_array AS $search_field) {
		if (method_exists($search_field, 'getVCParams') && ($field_params = $search_field->getVCParams()))
			$vc_listings_args['params'] = array_merge($vc_listings_args['params'], $field_params);
	}
	vc_map($vc_listings_args);
	
	vc_map(array(
			'name'                    => __('Single Listing', 'W2DC'),
			'description'             => __('The page with single listing', 'W2DC'),
			'base'                    => 'webdirectory-listing',
			'icon'                    => W2DC_RESOURCES_URL . 'images/vc/single.png',
			'show_settings_on_create' => true,
			'category'                => __('Directory Content', 'W2DC'),
			'params'                  => array(
					array(
							'type' => 'textfield',
							'param_name' => 'listing_id',
							'heading' => __('ID of listing', 'W2DC'),
							'description' => __('Enter exact ID of listing or leave empty to build custom page for any single listing.', 'W2DC'),
					),
			),
		)
	);
	
	$vc_maps_args = array(
			'name'                    => __('Directory Map', 'W2DC'),
			'description'             => __('Directory map and markers', 'W2DC'),
			'base'                    => 'webdirectory-map',
			'icon'                    => W2DC_RESOURCES_URL . 'images/vc/maps.png',
			'show_settings_on_create' => true,
			'category'                => __('Directory Content', 'W2DC'),
			'params'                  => array(
					array(
							'type' => 'dropdown',
							'param_name' => 'custom_home',
							'value' => array(__('No', 'W2DC') => '0', __('Yes', 'W2DC') => '1'),
							'heading' => __('Is it on custom home page?', 'W2DC'),
					),
					array(
							'type' => 'textfield',
							'param_name' => 'uid',
							'value' => '',
							'heading' => __('Enter unique string to connect this shortcode with another elements', 'W2DC'),
							'dependency' => array('element' => 'custom_home', 'value' => '0'),
					),
					array(
							'type' => 'textfield',
							'param_name' => 'num',
							'value' => -1,
							'heading' => __('Number of markers', 'W2DC'),
							'description' => __('Number of markers to display on map (default "-1″ – this means unlimited).', 'W2DC'),
							'dependency' => array('element' => 'custom_home', 'value' => '0'),
					),
					array(
							'type' => 'textfield',
							'param_name' => 'width',
							'heading' => __('Width', 'W2DC'),
							'description' => __('Set map width in pixels. With empty field the map will take all possible width.', 'W2DC'),
					),
					array(
							'type' => 'textfield',
							'param_name' => 'height',
							'value' => 300,
							'heading' => __('Height', 'W2DC'),
							'description' => __('Set map height in pixels, also possible to set 100% value.', 'W2DC'),
					),
					array(
							'type' => 'dropdown',
							'param_name' => 'map_style',
							'value' => $map_styles,
							'heading' => __('Google Maps style', 'W2DC'),
					),
					array(
							'type' => 'dropdown',
							'param_name' => 'sticky_scroll',
							'value' => array(__('No', 'W2DC') => '0', __('Yes', 'W2DC') => '1'),
							'heading' => __('Make map to be sticky on scroll', 'W2DC'),
					),
					array(
							'type' => 'textfield',
							'param_name' => 'sticky_scroll_toppadding',
							'value' => 10,
							'heading' => __('Sticky scroll top padding', 'W2DC'),
							'description' => __('Top padding in pixels.', 'W2DC'),
							'dependency' => array('element' => 'sticky_scroll', 'value' => '1'),
					),
					array(
							'type' => 'dropdown',
							'param_name' => 'show_summary_button',
							'value' => array(__('No', 'W2DC') => '0', __('Yes', 'W2DC') => '1'),
							'heading' => __('Show summary button?', 'W2DC'),
							'description' => __('Show "« Summary" button in info window.', 'W2DC'),
							'dependency' => array('element' => 'custom_home', 'value' => '0'),
					),
					array(
							'type' => 'dropdown',
							'param_name' => 'show_readmore_button',
							'value' => array(__('Yes', 'W2DC') => '1', __('No', 'W2DC') => '0'),
							'heading' => __('Show readmore button?', 'W2DC'),
							'description' => __('Show "Read more »" button in info window.', 'W2DC'),
							'dependency' => array('element' => 'custom_home', 'value' => '0'),
					),
					array(
							'type' => 'dropdown',
							'param_name' => 'geolocation',
							'value' => array(__('No', 'W2DC') => '0', __('Yes', 'W2DC') => '1'),
							'heading' => __('GeoLocation', 'W2DC'),
							'description' => __('Enable automatic geolocation.', 'W2DC'),
							'dependency' => array('element' => 'custom_home', 'value' => '0'),
					),
					array(
							'type' => 'dropdown',
							'param_name' => 'ajax_loading',
							'value' => array(__('No', 'W2DC') => '0', __('Yes', 'W2DC') => '1'),
							'heading' => __('AJAX loading', 'W2DC'),
							'description' => __('When map contains lots of markers - this may slow down map markers loading. Select AJAX to speed up loading. Requires Starting Address or Starting Point coordinates Latitude and Longitude.', 'W2DC'),
							'dependency' => array('element' => 'custom_home', 'value' => '0'),
					),
					array(
							'type' => 'dropdown',
							'param_name' => 'ajax_markers_loading',
							'value' => array(__('No', 'W2DC') => '0', __('Yes', 'W2DC') => '1'),
							'heading' => __('Maps info window AJAX loading', 'W2DC'),
							'description' => __('This may additionaly speed up loading.', 'W2DC'),
							'dependency' => array('element' => 'custom_home', 'value' => '0'),
					),
					array(
							'type' => 'textfield',
							'param_name' => 'start_address',
							'heading' => __('Starting Address', 'W2DC'),
							'description' => __('When map markers load by AJAX - it should have starting point and starting zoom. Enter start address or select latitude and longitude. Example: 1600 Amphitheatre Pkwy, Mountain View, CA 94043, USA', 'W2DC'),
							//'dependency' => array('element' => 'ajax_loading', 'value' => '1'),
					),
					array(
							'type' => 'textfield',
							'param_name' => 'start_latitude',
							'heading' => __('Starting Point Latitude', 'W2DC'),
							//'dependency' => array('element' => 'ajax_loading', 'value' => '1'),
					),
					array(
							'type' => 'textfield',
							'param_name' => 'start_longitude',
							'heading' => __('Starting Point Longitude', 'W2DC'),
							//'dependency' => array('element' => 'ajax_loading', 'value' => '1'),
					),
					array(
							'type' => 'dropdown',
							'param_name' => 'start_zoom',
							'heading' => __('Starting Point Zoom', 'W2DC'),
							'value' => array('1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19'),
							'std' => 11,
							//'dependency' => array('element' => 'ajax_loading', 'value' => '1'),
					),
					array(
							'type' => 'dropdown',
							'param_name' => 'sticky_featured',
							'value' => array(__('No', 'W2DC') => '0', __('Yes', 'W2DC') => '1'),
							'heading' => __('Show markers only of sticky or/and featured listings?', 'W2DC'),
							'description' => __('Whether to show markers only of sticky or/and featured listings.', 'W2DC'),
							'dependency' => array('element' => 'custom_home', 'value' => '0'),
					),
					array(
							'type' => 'textfield',
							'param_name' => 'address',
							'heading' => __('Address', 'W2DC'),
							'description' => __('Display markers near this address, recommended to set "radius" attribute.', 'W2DC'),
							//'dependency' => array('element' => 'ajax_loading', 'value' => '0'),
					),
					array(
							'type' => 'textfield',
							'param_name' => 'radius',
							'heading' => __('Radius', 'W2DC'),
							'description' => __('display listings near provided address within this radius in miles or kilometers.', 'W2DC'),
							//'dependency' => array('element' => 'ajax_loading', 'value' => '0'),
					),
					array(
							'type' => 'dropdown',
							'param_name' => 'radius_cycle',
							'value' => array(__('Yes', 'W2DC') => '1', __('No', 'W2DC') => '0'),
							'heading' => __('Show radius cycle?', 'W2DC'),
							'description' => __('Display radius cycle on map when radius filter provided.', 'W2DC'),
					),
					array(
							'type' => 'dropdown',
							'param_name' => 'clusters',
							'value' => array(__('No', 'W2DC') => '0', __('Yes', 'W2DC') => '1'),
							'heading' => __('Group map markers in clusters?', 'W2DC'),
							'dependency' => array('element' => 'custom_home', 'value' => '0'),
					),
					array(
							'type' => 'categoriesfield',
							'param_name' => 'categories',
							'value' => 0,
							'heading' => __('Categories', 'W2DC'),
							'dependency' => array('element' => 'custom_home', 'value' => '0'),
					),
					array(
							'type' => 'locationsfield',
							'param_name' => 'locations',
							'value' => 0,
							'heading' => __('Locations', 'W2DC'),
							'dependency' => array('element' => 'custom_home', 'value' => '0'),
					),
					array(
							'type' => 'checkbox',
							'param_name' => 'levels',
							'value' => $levels,
							'heading' => __('Listings levels', 'W2DC'),
							'description' => __('Categories may be dependent from listings levels.', 'W2DC'),
							'dependency' => array('element' => 'custom_home', 'value' => '0'),
					),
					array(
							'type' => 'textfield',
							'param_name' => 'post__in',
							'heading' => __('Exact listings', 'W2DC'),
							'description' => __('Comma separated string of listings IDs. Possible to display exact listings.', 'W2DC'),
							'dependency' => array('element' => 'custom_home', 'value' => '0'),
					),
			),
	);
	foreach ($w2dc_instance->search_fields->search_fields_array AS $search_field) {
		if (method_exists($search_field, 'getVCParams') && ($field_params = $search_field->getVCParams()))
			$vc_maps_args['params'] = array_merge($vc_maps_args['params'], $field_params);
	}
	vc_map($vc_maps_args);

	vc_map( array(
		'name'                    => __('Categories List', 'W2DC'),
		'description'             => __('Directory categories list', 'W2DC'),
		'base'                    => 'webdirectory-categories',
		'icon'                    => W2DC_RESOURCES_URL . 'images/vc/categories.png',
		'show_settings_on_create' => true,
		'category'                => __('Directory Content', 'W2DC'),
		'params'                  => array(
			array(
				'type' => 'dropdown',
				'param_name' => 'custom_home',
				'value' => array(__('No', 'W2DC') => '0', __('Yes', 'W2DC') => '1'),
				'heading' => __('Is it on custom home page?', 'W2DC'),
			),
			array(
				'type' => 'textfield',
				'param_name' => 'parent',
				'value' => 0,
				'heading' => __('Parent category', 'W2DC'),
				'description' => __('ID of parent category (default 0 – this will build whole categories tree starting from the root).', 'W2DC'),
				'dependency' => array('element' => 'custom_home', 'value' => '0'),
			),
			array(
				'type' => 'dropdown',
				'param_name' => 'depth',
				'value' => array('1', '2'),
				'heading' => __('Categories nesting level', 'W2DC'),
				'description' => __('The max depth of categories tree. When set to 1 – only root categories will be listed.', 'W2DC'),
			),
			array(
				'type' => 'textfield',
				'param_name' => 'subcats',
				'value' => 0,
				'heading' => __('Show subcategories items number', 'W2DC'),
				'description' => __('This is the number of subcategories those will be displayed in the table, when category item includes more than this number "View all subcategories ->" link appears at the bottom.', 'W2DC'),
				'dependency' => array('element' => 'depth', 'value' => '2'),
			),
			array(
				'type' => 'dropdown',
				'param_name' => 'columns',
				'value' => array('1', '2', '3', '4'),
				'heading' => __('Categories columns number', 'W2DC'),
				'description' => __('Categories list is divided by columns.', 'W2DC'),
			),
			array(
				'type' => 'dropdown',
				'param_name' => 'count',
				'value' => array(__('Yes', 'W2DC') => '1', __('No', 'W2DC') => '0'),
				'heading' => __('Show category listings count?', 'W2DC'),
				'description' => __('Whether to show number of listings assigned with current category in brackets.', 'W2DC'),
			),
			array(
				'type' => 'checkbox',
				'param_name' => 'levels',
				'value' => $levels,
				'heading' => __('Listings levels', 'W2DC'),
				'description' => __('Categories may be dependent from listings levels.', 'W2DC'),
				'dependency' => array('element' => 'custom_home', 'value' => '0'),
			),
			array(
				'type' => 'categoriesfield',
				'param_name' => 'categories',
				'value' => 0,
				'heading' => __('Categories', 'W2DC'),
				'description' => __('Comma separated string of categories slugs or IDs. Possible to display exact categories.', 'W2DC'),
				'dependency' => array('element' => 'custom_home', 'value' => '0'),
			),
		),
	));

	vc_map( array(
		'name'                    => __('Locations List', 'W2DC'),
		'description'             => __('Directory locations list', 'W2DC'),
		'base'                    => 'webdirectory-locations',
		'icon'                    => W2DC_RESOURCES_URL . 'images/vc/categories.png',
		'show_settings_on_create' => true,
		'category'                => __('Directory Content', 'W2DC'),
		'params'                  => array(
			array(
				'type' => 'dropdown',
				'param_name' => 'custom_home',
				'value' => array(__('No', 'W2DC') => '0', __('Yes', 'W2DC') => '1'),
				'heading' => __('Is it on custom home page?', 'W2DC'),
			),
			array(
				'type' => 'textfield',
				'param_name' => 'parent',
				'value' => 0,
				'heading' => __('Parent location', 'W2DC'),
				'description' => __('ID of parent location (default 0 – this will build whole locations tree starting from the root).', 'W2DC'),
				'dependency' => array('element' => 'custom_home', 'value' => '0'),
			),
			array(
				'type' => 'dropdown',
				'param_name' => 'depth',
				'value' => array('1', '2'),
				'heading' => __('Locations nesting level', 'W2DC'),
				'description' => __('The max depth of locations tree. When set to 1 – only root locations will be listed.', 'W2DC'),
			),
			array(
				'type' => 'textfield',
				'param_name' => 'sublocations',
				'value' => 0,
				'heading' => __('Show sublocations items number', 'W2DC'),
				'description' => __('This is the number of sublocations those will be displayed in the table, when location item includes more than this number "View all sublocations ->" link appears at the bottom.', 'W2DC'),
				'dependency' => array('element' => 'depth', 'value' => '2'),
			),
			array(
				'type' => 'dropdown',
				'param_name' => 'columns',
				'value' => array('1', '2', '3', '4'),
				'heading' => __('Locations columns number', 'W2DC'),
				'description' => __('Locations list is divided by columns.', 'W2DC'),
			),
			array(
				'type' => 'dropdown',
				'param_name' => 'count',
				'value' => array(__('No', 'W2DC') => '0', __('Yes', 'W2DC') => '1'),
				'heading' => __('Show location listings count?', 'W2DC'),
				'description' => __('Whether to show number of listings assigned with current location in brackets.', 'W2DC'),
			),
			array(
				'type' => 'locationsfield',
				'param_name' => 'locations',
				'value' => 0,
				'heading' => __('Locations', 'W2DC'),
				'description' => __('Comma separated string of locations slugs or IDs. Possible to display exact locations.', 'W2DC'),
				'dependency' => array('element' => 'custom_home', 'value' => '0'),
			),
		),
	));

	vc_map( array(
		'name'                    => __('Search form', 'W2DC'),
		'description'             => __('Directory listings search form', 'W2DC'),
		'base'                    => 'webdirectory-search',
		'icon'                    => W2DC_RESOURCES_URL . 'images/vc/search.png',
		'show_settings_on_create' => false,
		'category'                => __('Directory Content', 'W2DC'),
		'params'                  => array(
				array(
						'type' => 'textfield',
						'param_name' => 'uid',
						'value' => '',
						'heading' => __('Enter unique string to connect this shortcode with another elements', 'W2DC'),
				),
				array(
						'type' => 'dropdown',
						'param_name' => 'columns',
						'value' => array('2', '1'),
						'heading' => __('Number of columns to arrange search fields', 'W2DC'),
				),
				array(
						'type' => 'dropdown',
						'param_name' => 'advanced_open',
						'value' => array(__('No', 'W2DC') => '0', __('Yes', 'W2DC') => '1'),
						'heading' => __('Advanced search panel always open', 'W2DC'),
				),
			),
	));

	$vc_slider_args = array(
			'name'                    => __('Listings slider', 'W2DC'),
			'description'             => __('Directory listings in slider view', 'W2DC'),
			'base'                    => 'webdirectory-slider',
			'icon'                    => W2DC_RESOURCES_URL . 'images/vc/slider.png',
			'show_settings_on_create' => true,
			'category'                => __('Directory Content', 'W2DC'),
			'params'                  => array(
					array(
							'type' => 'textfield',
							'param_name' => 'slides',
							'value' => 3,
							'heading' => __('Maximum number of slides', 'W2DC'),
					),
					array(
							'type' => 'textfield',
							'param_name' => 'max_width',
							'heading' => __('Maximum width of slider in pixels', 'W2DC'),
							'description' => __('Leave empty to make it auto width.', 'W2DC'),
					),
					array(
							'type' => 'textfield',
							'param_name' => 'height',
							'value' => 400,
							'heading' => __('Height of slider in pixels', 'W2DC'),
					),
					array(
							'type' => 'textfield',
							'param_name' => 'slide_width',
							'value' => 150,
							'heading' => __('Maximum width of one slide in pixels', 'W2DC'),
					),
					array(
							'type' => 'dropdown',
							'param_name' => 'max_slides',
							'value' => array('2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6'),
							'heading' => __('Maximum number of slides to be shown in carousel', 'W2DC'),
							'description' => __('Slides will be sized up if carousel becomes larger than the original size.', 'W2DC'),
					),
					array(
							'type' => 'dropdown',
							'param_name' => 'auto_slides',
							'value' => array(__('No', 'W2DC') => '0', __('Yes', 'W2DC') => '1'),
							'heading' => __('Enable automatic rotating slideshow', 'W2DC'),
					),
					array(
							'type' => 'textfield',
							'param_name' => 'auto_slides_delay',
							'value' => 3000,
							'heading' => __('The delay in rotation (in ms)', 'W2DC'),
							'dependency' => array('element' => 'auto_slides', 'value' => '1'),
					),
					array(
							'type' => 'dropdown',
							'param_name' => 'sticky_featured',
							'value' => array(__('No', 'W2DC') => '0', __('Yes', 'W2DC') => '1'),
							'heading' => __('Show only sticky or/and featured listings?', 'W2DC'),
							'description' => __('Whether to show only sticky or/and featured listings.', 'W2DC'),
					),
					array(
							'type' => 'dropdown',
							'param_name' => 'order_by_rand',
							'value' => array(__('No', 'W2DC') => '0', __('Yes', 'W2DC') => '1'),
							'heading' => __('Order listings randomly?', 'W2DC'),
					),
					array(
							'type' => 'dropdown',
							'param_name' => 'order_by',
							'value' => $ordering,
							'heading' => __('Order by', 'W2DC'),
							'description' => __('Order listings by any of these parameter.', 'W2DC'),
							'dependency' => array('element' => 'order_by_rand', 'value' => '0'),
					),
					array(
							'type' => 'dropdown',
							'param_name' => 'order',
							'value' => array(__('Ascending', 'W2DC') => 'ASC', __('Descending', 'W2DC') => 'DESC'),
							'description' => __('Direction of sorting.', 'W2DC'),
							'dependency' => array('element' => 'order_by_rand', 'value' => '0'),
					),
					array(
							'type' => 'textfield',
							'param_name' => 'address',
							'heading' => __('Address', 'W2DC'),
							'description' => __('Display listings near this address, recommended to set "radius" attribute.', 'W2DC'),
					),
					array(
							'type' => 'textfield',
							'param_name' => 'radius',
							'heading' => __('Radius', 'W2DC'),
							'description' => __('display listings near provided address within this radius in miles or kilometers.', 'W2DC'),
					),
					array(
							'type' => 'categoriesfield',
							'param_name' => 'categories',
							'value' => 0,
							'heading' => __('Categories', 'W2DC'),
					),
					array(
							'type' => 'checkbox',
							'param_name' => 'levels',
							'value' => $levels,
							'heading' => __('Listings levels', 'W2DC'),
							'description' => __('Categories may be dependent from listings levels.', 'W2DC'),
					),
					array(
							'type' => 'textfield',
							'param_name' => 'post__in',
							'heading' => __('Exact listings', 'W2DC'),
							'description' => __('Comma separated string of listings IDs. Possible to display exact listings.', 'W2DC'),
					),
			),
	);
	foreach ($w2dc_instance->search_fields->search_fields_array AS $search_field) {
		if (method_exists($search_field, 'getVCParams') && ($field_params = $search_field->getVCParams()))
			$vc_slider_args['params'] = array_merge($vc_slider_args['params'], $field_params);
	}
	vc_map($vc_slider_args);
	
	vc_map( array(
		'name'                    => __('Front buttons', 'W2DC'),
		'description'             => __('Submit listing, my bookmarks, edit listing, print listing, ....', 'W2DC'),
		'base'                    => 'webdirectory-buttons',
		'icon'                    => W2DC_RESOURCES_URL . 'images/vc/buttons.png',
		'show_settings_on_create' => false,
		'category'                => __('Directory Content', 'W2DC'),
	));

}

add_action('vc_load_default_templates_action', 'w2dc_custom_templates_vc');
function w2dc_custom_templates_vc() {
	$data               = array();
	$data['name']       = __('Directory custom homepage 1', 'W2DC');
	$data['content']    = <<<CONTENT
        [vc_row][vc_column width="2/3"][webdirectory-search columns="2"][webdirectory custom_home="1"][/vc_column][vc_column width="1/3"][webdirectory-categories parent="0" depth="1" columns="1" subcats="1" count="1" categories="0" custom_home="1" levels="0"][webdirectory-map custom_home="1" sticky_scroll="1" sticky_scroll_toppadding="25" height="100%"][/vc_column][/vc_row]
CONTENT;

	vc_add_default_templates($data);

	$data               = array();
	$data['name']       = __('Directory custom homepage 2', 'W2DC');
	$data['content']    = <<<CONTENT
        [vc_row][vc_column width="1/1"][webdirectory-search columns="2"][/vc_column][/vc_row][vc_row][vc_column width="1/2"][webdirectory-slider slides="10" height="350" slide_width="130" max_slides="4" sticky_featured="0" order_by="post_date" order="ASC" categories="34" field_methods_of_payment="0" order_by_rand="0" auto_slides="1" auto_slides_delay="3000"][/vc_column][vc_column width="1/2"][webdirectory-map custom_home="1" height="500"][/vc_column][/vc_row][vc_row][vc_column width="1/1"][webdirectory-buttons][webdirectory custom_home="1"][/vc_column][/vc_row]
CONTENT;

	vc_add_default_templates($data);
	
	$data               = array();
	$data['name']       = __('Directory custom homepage 3', 'W2DC');
	$data['content']    = <<<CONTENT
        [vc_row][vc_column width="1/2"][webdirectory-map custom_home="1" sticky_scroll="1" sticky_scroll_toppadding="20" height="100%"][/vc_column][vc_column width="1/2"][webdirectory custom_home="1"][/vc_column][/vc_row][vc_row el_class="scroller_bottom"][/vc_row]
CONTENT;

	vc_add_default_templates($data);
}

?>