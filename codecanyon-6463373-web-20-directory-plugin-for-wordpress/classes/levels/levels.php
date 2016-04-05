<?php 

class w2dc_levels {
	public $levels_array = array();

	public function __construct() {
		$this->getLevelsFromDB();
	}
	
	public function saveOrder($order_input) {
		global $wpdb;

		if ($order_ids = explode(',', trim($order_input))) {
			$i = 1;
			foreach ($order_ids AS $id) {
				$wpdb->update($wpdb->levels, array('order_num' => $i), array('id' => $id));
				$i++;
			}
		}
		$this->getLevelsFromDB();
		return true;
	}
	
	public function getLevelsFromDB() {
		global $wpdb;
		$this->levels_array = array();

		$array = $wpdb->get_results("SELECT * FROM {$wpdb->levels} ORDER BY order_num", ARRAY_A);
		foreach ($array AS $row) {
			$level = new w2dc_level;
			$level->buildLevelFromArray($row);
			$this->levels_array[$row['id']] = $level;
		}
	}
	
	public function getLevelById($level_id) {
		if (isset($this->levels_array[$level_id]))
			return $this->levels_array[$level_id];
	}

	public function createLevelFromArray($array) {
		global $wpdb, $w2dc_instance;
		
		$insert_update_args = array(
				'name' => $array['name'],
				'description' => $array['description'],
				'active_years' => $array['active_years'],
				'active_months' => $array['active_months'],
				'active_days' => $array['active_days'],
				'raiseup_enabled' => $array['raiseup_enabled'],
				'sticky' => $array['sticky'],
				'listings_own_page' => $array['listings_own_page'],
				'nofollow' => $array['nofollow'],
				'featured' => $array['featured'],
				'categories_number' => $array['categories_number'],
				'locations_number' => w2dc_getValue($array, 'locations_number', 1),
				'unlimited_categories' => $array['unlimited_categories'],
				'google_map' => $array['google_map'],
				'logo_enabled' => $array['logo_enabled'],
				'images_number' => $array['images_number'],
				'videos_number' => $array['videos_number'],
				'categories' => serialize($array['categories_list']),
				'content_fields' => serialize($array['content_fields_list']),
				'locations_number' => w2dc_getValue($array, 'locations_number', 1),
				'google_map_markers' => w2dc_getValue($array, 'google_map_markers', 1),
		);
		$insert_update_args = apply_filters('w2dc_level_create_edit_args', $insert_update_args, $array);

		if ($wpdb->insert($wpdb->levels, $insert_update_args)) {
			$new_level_id = $wpdb->insert_id;
			
			do_action('w2dc_update_level', $new_level_id, $array);
			
			$this->getLevelsFromDB();
			$levels = $w2dc_instance->levels;
			$results = array();
			foreach ($levels->levels_array AS $level) {
				$results[$level->id]['disabled'] = false;
				$results[$level->id]['raiseup'] = false;
			}
			$level = $this->getLevelById($new_level_id);
			$level->saveUpgradeMeta($results);
			return true;
		}
	}
	
	public function saveLevelFromArray($level_id, $array) {
		global $wpdb;

		// update listings from eternal active period to numeric 
		$old_level = $this->getLevelById($level_id);
		if ($old_level->eternal_active_period && ($array['active_years'] || $array['active_months'] || $array['active_days'])) {
			$expiration_date = w2dc_sumDates(time(), $array['active_days'], $array['active_months'], $array['active_years']);
			$postids = $this->getPostIdsByLevelId($level_id);
			foreach ($postids AS $post_id) {
				delete_post_meta($post_id, '_expiration_date');
				update_post_meta($post_id, '_expiration_date', $expiration_date);
			}
		} elseif (!$old_level->eternal_active_period && $array['active_years'] == 0 && $array['active_months'] == 0 && $array['active_days'] == 0) {
			$postids = $this->getPostIdsByLevelId($level_id);
			foreach ($postids AS $post_id)
				delete_post_meta($post_id, '_expiration_date');
		}
		
		$insert_update_args = array(
				'name' => $array['name'],
				'description' => $array['description'],
				'active_years' => $array['active_years'],
				'active_months' => $array['active_months'],
				'active_days' => $array['active_days'],
				'sticky' => $array['sticky'],
				'listings_own_page' => $array['listings_own_page'],
				'nofollow' => $array['nofollow'],
				'raiseup_enabled' => $array['raiseup_enabled'],
				'featured' => $array['featured'],
				'categories_number' => $array['categories_number'],
				'locations_number' => w2dc_getValue($array, 'locations_number', 1),
				'unlimited_categories' => $array['unlimited_categories'],
				'google_map' => $array['google_map'],
				'logo_enabled' => $array['logo_enabled'],
				'images_number' => $array['images_number'],
				'videos_number' => $array['videos_number'],
				'categories' => serialize($array['categories_list']),
				'content_fields' => serialize($array['content_fields_list']),
				'locations_number' => w2dc_getValue($array, 'locations_number', 1),
				'google_map_markers' => w2dc_getValue($array, 'google_map_markers', 1),
		);
		$insert_update_args = apply_filters('w2dc_level_create_edit_args', $insert_update_args, $array);
	
		if ($wpdb->update($wpdb->levels, $insert_update_args, array('id' => $level_id), null, array('%d')) !== false) {
			do_action('w2dc_update_level', $level_id, $array);
			return true;
		}
	}
	
	public function deleteLevel($level_id) {
		global $wpdb;
		
		$postids = $this->getPostIdsByLevelId($level_id);
		foreach ($postids AS $post_id)
			wp_delete_post($post_id, true);
	
		$wpdb->delete($wpdb->levels, array('id' => $level_id));

		// Renew levels' upgrade meta
		$this->getLevelsFromDB();
		$results = array();
		foreach ($this->levels_array AS $level1) {
			foreach ($this->levels_array AS $level2) {
				$results[$level1->id][$level2->id]['disabled'] = $level1->upgrade_meta[$level2->id]['disabled'];
				$results[$level1->id][$level2->id]['raiseup'] = $level1->upgrade_meta[$level2->id]['raiseup'];
			}
			$level1->saveUpgradeMeta($results[$level1->id]);
		}

		return true;
	}
	
	public function getPostIdsByLevelId($level_id) {
		global $wpdb;

		return $postids = $wpdb->get_col($wpdb->prepare("SELECT post_id FROM {$wpdb->levels_relationships} WHERE level_id=%d", $level_id));
	}
}

class w2dc_level {
	public $id;
	public $order_num;
	public $name;
	public $description;
	public $active_years = 0;
	public $active_months = 0;
	public $active_days = 0;
	public $eternal_active_period;
	public $featured = 0;
	public $listings_own_page = 1;
	public $nofollow = 0;
	public $raiseup_enabled = 0;
	public $sticky = 0;
	public $categories_number = 0;
	public $unlimited_categories = 1;
	public $locations_number = 1;
	public $google_map = 1;
	public $google_map_markers = 1;
	public $logo_enabled;
	public $images_number = 1;
	public $videos_number = 1;
	public $categories = array();
	public $content_fields = array();
	public $upgrade_meta = array();

	public function buildLevelFromArray($array) {
		$this->id = w2dc_getValue($array, 'id');
		$this->order_num = w2dc_getValue($array, 'order_num');
		$this->name = w2dc_getValue($array, 'name');
		$this->description = w2dc_getValue($array, 'description');
		$this->active_years = w2dc_getValue($array, 'active_years');
		$this->active_months = w2dc_getValue($array, 'active_months');
		$this->active_days = w2dc_getValue($array, 'active_days');
		if ($this->active_years == 0 && $this->active_months == 0 && $this->active_days == 0)
			$this->eternal_active_period = 1;
		else 
			$this->eternal_active_period = 0;
		
		$this->featured = w2dc_getValue($array, 'featured');
		$this->sticky = w2dc_getValue($array, 'sticky');
		$this->listings_own_page = w2dc_getValue($array, 'listings_own_page');
		$this->nofollow = w2dc_getValue($array, 'nofollow');
		$this->raiseup_enabled = w2dc_getValue($array, 'raiseup_enabled');
		$this->categories_number = w2dc_getValue($array, 'categories_number');
		$this->unlimited_categories = w2dc_getValue($array, 'unlimited_categories');
		$this->locations_number = w2dc_getValue($array, 'locations_number');
		$this->google_map = w2dc_getValue($array, 'google_map');
		$this->google_map_markers = w2dc_getValue($array, 'google_map_markers');
		$this->logo_enabled = w2dc_getValue($array, 'logo_enabled');
		$this->images_number = w2dc_getValue($array, 'images_number');
		$this->videos_number = w2dc_getValue($array, 'videos_number');
		$this->categories = w2dc_getValue($array, 'categories');
		$this->content_fields = w2dc_getValue($array, 'content_fields');
		$this->upgrade_meta = (w2dc_getValue($array, 'upgrade_meta')) ? unserialize(w2dc_getValue($array, 'upgrade_meta')) : array();
		
		$this->convertCategories();
		$this->convertContentFields();
		
		apply_filters('w2dc_levels_loading', $this, $array);
	}
	
	public function convertCategories() {
		if ($this->categories) {
			$unserialized_categories = unserialize($this->categories);
			if (count($unserialized_categories) > 1 || $unserialized_categories != array(''))
				$this->categories = $unserialized_categories;
			else
				$this->categories = array();
		} else
			$this->categories = array();
		return $this->categories;
	}

	public function convertContentFields() {
		if ($this->content_fields) {
			$unserialized_content_fields = unserialize($this->content_fields);
			if (count($unserialized_content_fields) > 1 || $unserialized_content_fields != array(''))
				$this->content_fields = $unserialized_content_fields;
			else
				$this->content_fields = array();
		} else
			$this->content_fields = array();
		return $this->content_fields;
	}
	
	public function getActivePeriodString() {
		if ($this->eternal_active_period)
			return __('Never expire', 'W2DC');
		else {
			$string_arr = array();
			if ($this->active_days > 0)
				$string_arr[] = $this->active_days . ' ' . _n('day', 'days', $this->active_days, 'W2DC');
			if ($this->active_months > 0)
				$string_arr[] = $this->active_months . ' ' . _n('month', 'months', $this->active_months, 'W2DC');
			if ($this->active_years > 0)
				$string_arr[] = $this->active_years . ' ' . _n('year', 'years', $this->active_years, 'W2DC');
			return implode(', ', $string_arr);
		}
	}
	
	public function saveUpgradeMeta($meta) {
		global $wpdb;
		
		$this->upgrade_meta = $meta;
		
		$this->upgrade_meta = apply_filters('w2dc_level_upgrade_meta', $this->upgrade_meta, $this);

		return $wpdb->update($wpdb->levels, array('upgrade_meta' => serialize($this->upgrade_meta)), array('id' => $this->id));
	}
	
	public function isUpgradable() {
		foreach ($this->upgrade_meta AS $id=>$meta) {
			if (($id != $this->id) && (!isset($meta['disabled']) || !$meta['disabled']))
				return true;
		}
		return false;
	}
}

// adapted for WPML
add_action('init', 'levels_names_into_strings');
function levels_names_into_strings() {
	global $w2dc_instance, $sitepress;

	if (function_exists('icl_object_id') && $sitepress) {
		if (function_exists('icl_register_string'))
			foreach ($w2dc_instance->levels->levels_array AS &$level) {
				icl_register_string('Web 2.0 Directory', 'The name of level #' . $level->id, $level->name);
				$level->name = icl_t('Web 2.0 Directory', 'The name of level #' . $level->id, $level->name);
				icl_register_string('Web 2.0 Directory', 'The description of level #' . $level->id, $level->description);
				$level->description = icl_t('Web 2.0 Directory', 'The description of level #' . $level->id, $level->description);
			}
	}
}

add_filter('w2dc_level_create_edit_args', 'filter_level_categories', 10, 2);
function filter_level_categories($insert_update_args, $array) {
	global $sitepress;

	if (function_exists('icl_object_id') && $sitepress) {
		if ($sitepress->get_default_language() != ICL_LANGUAGE_CODE) {
			if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['level_id']))
				unset($insert_update_args['categories']);
			else 
				$insert_update_args['categories'] = '';
		}
	}
	return $insert_update_args;
}

add_action('w2dc_update_level', 'save_level_categories', 10, 2);
function save_level_categories($level_id, $array) {
	global $sitepress;

	if (function_exists('icl_object_id') && $sitepress) {
		if ($sitepress->get_default_language() != ICL_LANGUAGE_CODE) {
			update_option('w2dc_wpml_level_categories_'.$level_id.'_'.ICL_LANGUAGE_CODE, $array['categories_list']);
		}
	}
}
	
add_action('init', 'load_levels_categories');
function load_levels_categories() {
	global $w2dc_instance, $sitepress;

	if (function_exists('icl_object_id') && $sitepress) {
		if ($sitepress->get_default_language() != ICL_LANGUAGE_CODE) {
			foreach ($w2dc_instance->levels->levels_array AS &$level) {
				$_categories = get_option('w2dc_wpml_level_categories_'.$level->id.'_'.ICL_LANGUAGE_CODE);
				if ($_categories && (count($_categories) > 1 || $_categories != array('')))
					$level->categories = $_categories;
				else
					$level->categories = array();
			}
		}
	}
}

?>