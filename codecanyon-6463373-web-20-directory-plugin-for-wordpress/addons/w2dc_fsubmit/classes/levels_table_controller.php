<?php 

class w2dc_levels_table_controller extends w2dc_frontend_controller {

	public function init($args = array()) {
		global $w2dc_instance;

		parent::init($args);
		
		$shortcode_atts = array_merge(array(
				'show_period' => 1,
				'show_sticky' => 1,
				'show_featured' => 1,
				'show_categories' => 1,
				'show_locations' => 1,
				'show_maps' => 1,
				'show_images' => 1,
				'show_videos' => 1,
				'columns_same_height' => 1,
				'columns' => 3,
		), $args);
		
		$this->args = $shortcode_atts;

		$this->template = array(W2DC_FSUBMIT_TEMPLATES_PATH, 'submitlisting_step_level.tpl.php');

		apply_filters('w2dc_frontend_controller_construct', $this);
	}

	public function display() {
		$output =  w2dc_renderTemplate($this->template, array('frontend_controller' => $this), true);
		wp_reset_postdata();

		return $output;
	}
}

?>