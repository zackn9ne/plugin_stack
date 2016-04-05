<?php 

class w2dc_categories_controller extends w2dc_frontend_controller {

	public function init($args = array()) {
		global $w2dc_instance;
		
		parent::init($args);

		$shortcode_atts = array_merge(array(
				'custom_home' => 0,
				'parent' => 0,
				'depth' => 1,
				'columns' => 2,
				'count' => 1,
				'subcats' => 0,
				'levels' => array(),
				'categories' => array(),
		), $args);
		$this->args = $shortcode_atts;

		if ($this->args['custom_home']) {
			if ($frontend_controller = $w2dc_instance->getShortcodeProperty('webdirectory', 'is_category'))
				$this->args['parent'] = $frontend_controller->category->term_id;

			$this->args['depth'] = w2dc_getValue($args, 'depth', get_option('w2dc_categories_nesting_level'));
			$this->args['columns'] = w2dc_getValue($args, 'columns', get_option('w2dc_categories_columns'));
			$this->args['count'] = w2dc_getValue($args, 'count', get_option('w2dc_show_category_count'));
			$this->args['subcats'] = w2dc_getValue($args, 'subcats', get_option('w2dc_subcategories_items'));
		} else {
			if (isset($this->args['levels']) && !is_array($this->args['levels']))
				if ($levels = array_filter(explode(',', $this->args['levels']), 'trim'))
					$this->args['levels'] = $levels;
	
			if (isset($this->args['categories']) && !is_array($this->args['categories']))
				if ($categories = array_filter(explode(',', $this->args['categories']), 'trim'))
					$this->args['categories'] = $categories;
		}

		apply_filters('w2dc_frontend_controller_construct', $this);
	}

	public function display() {
		ob_start();
		w2dc_renderAllCategories($this->args['parent'], $this->args['depth'], $this->args['columns'], $this->args['count'], $this->args['subcats'], $this->args['levels'], $this->args['categories']);
		$output = ob_get_clean();

		return $output;
	}
}

?>