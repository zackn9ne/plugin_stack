<?php 

class w2dc_locations_levels {
	public $levels_array;

	public function __construct() {
		$this->getLevelsFromDB();
	}

	public function getLevelsFromDB() {
		global $wpdb;
		$this->levels_array = array();

		$array = $wpdb->get_results("SELECT * FROM {$wpdb->locations_levels}", ARRAY_A);
		foreach ($array AS $row) {
			$level = new w2dc_locations_level;
			$level->buildLevelFromArray($row);
			$this->levels_array[$row['id']] = $level;
		}
	}
	
	public function getNamesArray() {
		$names = array();
		foreach ($this->levels_array AS $level)
			$names[] = $level->name;
		
		return $names;
	}

	public function getSelectionsArray() {
		$selections = array();
		foreach ($this->levels_array AS $level)
			$selections[] = sprintf(__('Select %s', 'W2DC'), $level->name);
		
		return $selections;
	}
	
	public function getLevelById($level_id) {
		if (isset($this->levels_array[$level_id]))
			return $this->levels_array[$level_id];
	}
	
	public function createLevelFromArray($array) {
		global $wpdb;
		
		$insert_update_args = array(
				'name' => $array['name'],
				//'in_widget' => $array['in_widget'],
				'in_address_line' => $array['in_address_line'],
		);
	
		return $wpdb->insert($wpdb->locations_levels, $insert_update_args);
	}
	
	public function saveLevelFromArray($level_id, $array) {
		global $wpdb;
		
		$insert_update_args = array(
				'name' => $array['name'],
				//'in_widget' => $array['in_widget'],
				'in_address_line' => $array['in_address_line'],
		);
	
		return $wpdb->update($wpdb->locations_levels, $insert_update_args,	array('id' => $level_id), null, array('%d')) !== false;
	}
	
	public function deleteLevel($level_id) {
		global $wpdb;
	
		$wpdb->delete($wpdb->locations_levels, array('id' => $level_id));
		return true;
	}
}

class w2dc_locations_level {
	public $id;
	public $name;
	//public $in_widget = 1;
	public $in_address_line = 1;

	public function buildLevelFromArray($array) {
		$this->id = w2dc_getValue($array, 'id');
		$this->name = w2dc_getValue($array, 'name');
		//$this->in_widget = w2dc_getValue($array, 'in_widget');
		$this->in_address_line =w2dc_getValue($array, 'in_address_line');
	}
}


if( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
class w2dc_manage_locations_levels_table extends WP_List_Table {

	public function __construct() {
		parent::__construct(array(
				'singular' => __('locations level', 'W2DC'),
				'plural' => __('locations levels', 'W2DC'),
				'ajax' => false
		));
	}

	public function get_columns() {
		$columns = array(
				'locations_level_name' => __('Name', 'W2DC'),
				//'in_widget' => __('In locations widget', 'W2DC'),
				'in_address_line' => __('In address line', 'W2DC'),
		);

		return $columns;
	}

	public function getItems($locations_levels) {
		$items_array = array();
		foreach ($locations_levels->levels_array as $id=>$level) {
			$items_array[$id] = array(
					'id' => $level->id,
					'locations_level_name' => $level->name,
					//'in_widget' => $level->in_widget,
					'in_address_line' => $level->in_address_line,
			);
		}
		return $items_array;
	}

	public function prepareItems($locations_levels) {
		$this->_column_headers = array($this->get_columns(), array(), array());

		$this->items = $this->getItems($locations_levels);
	}

	public function column_locations_level_name($item) {
		$actions = array(
				'edit' => sprintf('<a href="?page=%s&action=%s&level_id=%d">' . __('Edit', 'W2DC') . '</a>', $_GET['page'], 'edit', $item['id']),
				'delete' => sprintf('<a href="?page=%s&action=%s&level_id=%d">' . __('Delete', 'W2DC') . '</a>', $_GET['page'], 'delete', $item['id']),
		);
		return sprintf('%1$s %2$s', sprintf('<a href="?page=%s&action=%s&level_id=%d">' . $item['locations_level_name'] . '</a>', $_GET['page'], 'edit', $item['id']), $this->row_actions($actions));
	}

	/* public function column_in_widget($item) {
		if ($item['in_widget'])
			return '<img src="' . W2DC_RESOURCES_URL . 'images/accept.png" />';
		else
			return '<img src="' . W2DC_RESOURCES_URL . 'images/delete.png" />';
	} */

	public function column_in_address_line($item) {
		if ($item['in_address_line'])
			return '<img src="' . W2DC_RESOURCES_URL . 'images/accept.png" />';
		else
			return '<img src="' . W2DC_RESOURCES_URL . 'images/delete.png" />';
	}

	public function column_default($item, $column_name) {
		switch($column_name) {
			default:
				return $item[$column_name];
		}
	}

	function no_items() {
		__('No locations levels found', 'W2DC');
	}
}

add_action('init', 'locations_levels_names_into_strings');
function locations_levels_names_into_strings() {
	global $w2dc_instance, $sitepress;

	if (function_exists('icl_object_id') && $sitepress) {
		if (function_exists('icl_register_string'))
			foreach ($w2dc_instance->locations_levels->levels_array AS &$locations_level) {
				icl_register_string('Web 2.0 Directory', 'The name of locations level #' . $locations_level->id, $locations_level->name);
				$locations_level->name = icl_t('Web 2.0 Directory', 'The name of locations level #' . $locations_level->id, $locations_level->name);
			}
	}
}

?>