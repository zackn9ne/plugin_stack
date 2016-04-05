<?php

function loadMarkersSizes() {
	if (!defined('W2DC_THEME_MODE')) {
		define('W2DC_MARKER_IMAGE_WIDTH', get_option('w2dc_map_marker_width'));
		define('W2DC_MARKER_IMAGE_HEIGHT', get_option('w2dc_map_marker_height'));
		define('W2DC_MARKER_ANCHOR_X', get_option('w2dc_map_marker_anchor_x'));
		define('W2DC_MARKER_ANCHOR_Y', get_option('w2dc_map_marker_anchor_y'));
		define('W2DC_INFOWINDOW_WIDTH', get_option('w2dc_map_infowindow_width'));
		define('W2DC_INFOWINDOW_OFFSET', -get_option('w2dc_map_infowindow_offset'));
		define('W2DC_INFOWINDOW_LOGO_WIDTH', get_option('w2dc_map_infowindow_logo_width'));
	}
}
add_action('init', 'loadMarkersSizes', 0);

class google_maps {
	public $args;
	public $unique_map_id;
	
	public $map_zoom;
	public $locations_array = array();
	public $locations_option_array = array();

	public static $map_content_fields;

	public function __construct($args = array()) {
		$this->args = $args;
	}
	
	public function setUniqueId($unique_id) {
		$this->unique_map_id = $unique_id;
	}

	public function collectLocations($listing) {
		global $w2dc_instance, $w2dc_address_locations, $w2dc_tax_terms_locations;

		if (count($listing->locations) == 1)
			$this->map_zoom = $listing->map_zoom;

		foreach ($listing->locations AS $location) {
			if ((!$w2dc_address_locations || in_array($location->id, $w2dc_address_locations))  && (!$w2dc_tax_terms_locations || in_array($location->selected_location, $w2dc_tax_terms_locations))) {
				if ($location->map_coords_1 != '0.000000' || $location->map_coords_2 != '0.000000') {
					$logo_image = '';
					if ($listing->logo_image) {
						$src = wp_get_attachment_image_src($listing->logo_image, array(W2DC_INFOWINDOW_LOGO_WIDTH, 400));
						$logo_image = $src[0];
					}
	
					$listing_link = '';
					if ($listing->level->listings_own_page)
						$listing_link = get_permalink($listing->post->ID);
	
					if ($w2dc_instance->content_fields->getMapContentFields())
						$content_fields_output = $listing->setMapContentFields($w2dc_instance->content_fields->getMapContentFields(), $location);
					else 
						$content_fields_output = '';
	
					$this->locations_array[] = $location;
					$this->locations_option_array[] = array(
							$location->id,
							$location->map_coords_1,
							$location->map_coords_2,
							$location->map_icon_file,
							$listing->map_zoom,
							get_the_title(),
							$logo_image,
							$listing_link,
							$content_fields_output,
							'post-' . $listing->post->ID,
							($listing->level->nofollow) ? 1 : 0,
					);
				}
			}
		}

		if ($this->locations_option_array)
			return true;
		else
			return false;
	}
	
	public function collectLocationsForAjax($listing) {	
		foreach ($listing->locations AS $location) {
			if ($location->map_coords_1 != '0.000000' || $location->map_coords_2 != '0.000000') {
				$this->locations_array[] = $location;
				$this->locations_option_array[] = array(
						$location->id,
						$location->map_coords_1,
						$location->map_coords_2,
						$location->map_icon_file,
						null,
						null,
						null,
						null,
						null,
						null,
						null,
				);
			}
		}
		if ($this->locations_option_array)
			return true;
		else
			return false;
	}

	public function display($show_directions = true, $static_image = false, $enable_radius_cycle = true, $enable_clusters = true, $show_summary_button = true, $show_readmore_button = true, $width = false, $height = false, $sticky_scroll = false, $sticky_scroll_toppadding = 10, $map_style_name = 'default', $custom_home = false) {
		//if ($this->locations_option_array || $this->is_ajax_markers_management()) {
			$locations_options = json_encode($this->locations_option_array);
			$map_args = json_encode($this->args);
			w2dc_renderTemplate('google_map.tpl.php',
					array(
							'locations_options' => $locations_options,
							'locations_array' => $this->locations_array,
							'show_directions' => $show_directions,
							'static_image' => $static_image,
							'enable_radius_cycle' => $enable_radius_cycle,
							'enable_clusters' => $enable_clusters,
							'map_zoom' => $this->map_zoom,
							'show_summary_button' => $show_summary_button,
							'show_readmore_button' => $show_readmore_button,
							'map_style_name' => $map_style_name,
							'custom_home' => $custom_home,
							'width' => $width,
							'height' => $height,
							'sticky_scroll' => $sticky_scroll,
							'sticky_scroll_toppadding' => $sticky_scroll_toppadding,
							'unique_map_id' => $this->unique_map_id,
							'map_args' => $map_args
					));
			wp_enqueue_script('google_maps_infobox');
		//}
	}
	
	public function is_ajax_markers_management() {
		if (isset($this->args['ajax_loading']) && $this->args['ajax_loading'] && ((isset($this->args['start_address']) && $this->args['start_address']) || ((isset($this->args['start_latitude']) && $this->args['start_latitude']) && (isset($this->args['start_longitude']) && $this->args['start_longitude']))))
			return true;
		else
			return false;
	}
}

?>