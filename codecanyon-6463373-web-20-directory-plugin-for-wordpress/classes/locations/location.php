<?php 

class w2dc_location {
	public $id;
	public $post_id;
	public $selected_location = 0;
	public $address_line_1;
	public $address_line_2;
	public $zip_or_postal_index;
	public $additional_info;
	public $manual_coords = false;
	public $map_coords_1;
	public $map_coords_2;
	public $map_icon_file;
	
	public function __construct($post_id = null) {
		$this->post_id = $post_id;
	}

	public function createLocationFromArray($array) {
		$this->id = w2dc_getValue($array, 'id');
		$this->selected_location = w2dc_getValue($array, 'selected_location');
		$this->address_line_1 = w2dc_getValue($array, 'address_line_1');
		$this->address_line_2 = w2dc_getValue($array, 'address_line_2');
		$this->zip_or_postal_index = w2dc_getValue($array, 'zip_or_postal_index');
		$this->additional_info = w2dc_getValue($array, 'additional_info');
		$this->manual_coords = w2dc_getValue($array, 'manual_coords');
		$this->map_coords_1 = w2dc_getValue($array, 'map_coords_1');
		$this->map_coords_2 = w2dc_getValue($array, 'map_coords_2');
		$this->map_icon_file = w2dc_getValue($array, 'map_icon_file');
	}
	
	public function getSelectedLocationString($glue = ', ', $reverse = false) {
		global $w2dc_instance;

		if ($this->selected_location != 0) {
			$chain = array();
			$parent_id = $this->selected_location;
			while ($parent_id != 0) {
				if (!is_wp_error($term = get_term($parent_id, W2DC_LOCATIONS_TAX)) && $term) {
					$chain[] = $term->name;
					$parent_id = $term->parent;
				} else 
					break;
			}

			// reverse locations for actual locations levels 
			$chain = array_reverse($chain);

			$locations_levels = $w2dc_instance->locations_levels;
			$locations_levels_array = array_values($locations_levels->levels_array);
			$result_chain = array();
			foreach ($chain AS $location_key=>$location) {
				if ($locations_levels_array[$location_key]->in_address_line)
					$result_chain[] = $location;
			}

			// do not reverse as it was reversed before
			if (!$reverse)
				$result_chain = array_reverse($result_chain);
			return implode($glue, $result_chain);
		}
	}

	public function getWholeAddress($microdata = true, $glue = ', ', $reverse = false) {
		$out = array();
		$separator_previous = false;
		foreach (get_option('w2dc_addresses_order') AS $part) {
			switch ($part) {
				case 'location':
					if ($location_string = $this->getSelectedLocationString($glue, $reverse)) {
						$out[] = (($microdata) ? '<span itemprop="addressLocality">' : '') . $location_string . (($microdata) ? '</span>' : '');
						$separator_previous = false;
					} elseif (in_array($separator_previous, array('space','comma')))
						array_pop($out);
					break;
				case 'line_1':
					if (trim($this->address_line_1)) {
						$out[] = (($microdata) ? '<span itemprop="streetAddress">' : '') . trim($this->address_line_1) . (($microdata) ? '</span>' : '');
						$separator_previous = false;
					} elseif (in_array($separator_previous, array('space','comma')))
						array_pop($out);
					break;
				case 'line_2':
					if (trim($this->address_line_2)) {
						$out[] = trim($this->address_line_2);
						$separator_previous = false;
					} elseif (in_array($separator_previous, array('space','comma')))
						array_pop($out);
					break;
				case 'zip':
					if (trim($this->zip_or_postal_index)) {
						$out[] = (($microdata) ? '<span itemprop="postalCode">' : '') . trim($this->zip_or_postal_index) . (($microdata) ? '</span>' : '');
						$separator_previous = false;
					} elseif (in_array($separator_previous, array('space','comma')))
						array_pop($out);
					break;
				case 'space1':
				case 'space2':
				case 'space3':
					if ($separator_previous != 'break') {
						$out[] = ' ';
						$separator_previous = 'space';
					}
					break;
				case 'comma1':
				case 'comma2':
				case 'comma3':
					if ($separator_previous != 'break') {
						$out[] = ', ';
						$separator_previous = 'comma';
					}
					break;
				case 'break1':
				case 'break2':
					if ($separator_previous != 'break') {
						$out[] = '<br />';
						$separator_previous = 'break';
					}
					break;
			}
		}

		return trim(implode('', $out), ', ');
	}
	
	public function renderInfoFieldForMap() {
		return $this->additional_info;
	}
}

?>