<?php 

class w2dc_content_field_select_search extends w2dc_content_field_search {
	public $search_input_mode = 'checkboxes';
	public $value = array();
	
	public function searchConfigure() {
		global $wpdb, $w2dc_instance;
	
		if (w2dc_getValue($_POST, 'submit') && wp_verify_nonce($_POST['w2dc_configure_content_fields_nonce'], W2DC_PATH)) {
			$validation = new form_validation();
			$validation->set_rules('search_input_mode', __('Search input mode', 'W2DC'), 'required');
			if ($validation->run()) {
				$result = $validation->result_array();
				if ($wpdb->update($wpdb->content_fields, array('search_options' => serialize(array('search_input_mode' => $result['search_input_mode']))), array('id' => $this->content_field->id), null, array('%d')))
					w2dc_addMessage(__('Search field configuration was updated successfully!', 'W2DC'));
	
				$w2dc_instance->content_fields_manager->showContentFieldsTable();
			} else {
				$this->search_input_mode = $validation->result_array('search_input_mode');
				w2dc_addMessage($validation->error_string(), 'error');
	
				w2dc_renderTemplate('search_fields/fields/select_checkbox_radio_configuration.tpl.php', array('search_field' => $this));
			}
		} else
			w2dc_renderTemplate('search_fields/fields/select_checkbox_radio_configuration.tpl.php', array('search_field' => $this));
	}
	
	public function buildSearchOptions() {
		if (isset($this->content_field->search_options['search_input_mode']))
			$this->search_input_mode = $this->content_field->search_options['search_input_mode'];
	}

	public function renderSearch($random_id, $columns = 2) {
		if ($this->search_input_mode =='radiobutton' && count($this->content_field->selection_items)) {
			$this->content_field->selection_items = array('' => __('All', 'W2DC')) + $this->content_field->selection_items;
			if (!$this->value)
				$this->value = array('');
		}

		w2dc_renderTemplate('search_fields/fields/select_checkbox_radio_input.tpl.php', array('search_field' => $this, 'columns' => $columns, 'random_id' => $random_id));
	}
	
	public function validateSearch(&$args, $defaults = array(), $include_GET_params = true) {
		$field_index = 'field_' . $this->content_field->slug;
		
		if ($include_GET_params)
			$value = w2dc_getValue($_GET, $field_index, w2dc_getValue($defaults, $field_index));
		else
			$value = w2dc_getValue($defaults, $field_index);

		if (!is_array($value))
			$value = array_filter(explode(',', $value), 'trim');

		if ($value) {
			$this->value = $value;
			$args['meta_query']['relation'] = 'AND';
			$args['meta_query'][] = array(
					'key' => '_content_field_' . $this->content_field->id,
					'value' => $this->value,
					'compare' => 'IN'
			);
		}
	}
	
	public function getVCParams() {
		return array(
				array(
						'type' => 'checkbox',
						'param_name' => 'field_' . $this->content_field->slug,
						'heading' => $this->content_field->name,
						'value' => array_flip($this->content_field->selection_items),
						'dependency' => array('element' => 'custom_home', 'value' => '0'),
				),
		);
	}
}
?>