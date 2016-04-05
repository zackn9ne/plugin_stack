<?php 

class w2dc_content_field_price_search extends w2dc_content_field_number_search {

	public function renderSearch($random_id, $columns = 2) {
		if ($this->mode == 'exact_number')
			w2dc_renderTemplate('search_fields/fields/price_input_exactnumber.tpl.php', array('search_field' => $this, 'columns' => $columns, 'random_id' => $random_id));
		elseif ($this->mode == 'min_max')
			w2dc_renderTemplate('search_fields/fields/price_input_minmax.tpl.php', array('search_field' => $this, 'columns' => $columns, 'random_id' => $random_id));
		elseif ($this->mode == 'min_max_slider' || $this->mode == 'range_slider')
			w2dc_renderTemplate('search_fields/fields/price_input_slider.tpl.php', array('search_field' => $this, 'columns' => $columns, 'random_id' => $random_id));
	}
}
?>