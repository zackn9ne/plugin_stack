<?php 

class w2dc_search_controller extends w2dc_frontend_controller {

	public function init($args = array()) {
		parent::init($args);

		$shortcode_atts = array_merge(array(
				'columns' => 2,
				'advanced_open' => false,
				'uid' => null,
		), $args);
		
		$this->args = $shortcode_atts;
		
		$hash = false;
		if ($this->args['uid'])
			$hash = md5($this->args['uid']);

		$this->search_form = new search_form($hash, 'listings_controller');
		
		apply_filters('w2dc_frontend_controller_construct', $this);
	}

	public function display() {
		ob_start();
		$this->search_form->display($this->args['columns'], $this->args['advanced_open']);
		$output = ob_get_clean();

		return $output;
	}
}

?>