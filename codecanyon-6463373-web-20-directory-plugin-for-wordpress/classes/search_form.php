<?php

class search_form {
	public $uid;
	public $controller;
	
	public function __construct($uid = null, $controller = '') {
		$this->uid = $uid;
		$this->controller = $controller;
	}

	public function display($columns = 2, $advanced_open = false) {
		global $w2dc_instance;

		// random ID needed because there may be more than 1 search form on one page
		$random_id = generateRandomVal();
		
		$search_url = ($w2dc_instance->index_page_url) ? w2dc_directoryUrl() : home_url('/');

		w2dc_renderTemplate('search_form.tpl.php', array('random_id' => $random_id, 'columns' => $columns, 'advanced_open' => $advanced_open, 'search_url' => $search_url, 'hash' => $this->uid, 'controller' => $this->controller));
	}
}
?>