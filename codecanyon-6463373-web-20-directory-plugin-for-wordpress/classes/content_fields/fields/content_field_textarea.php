<?php 

class w2dc_content_field_textarea extends w2dc_content_field {
	public $max_length = 500;
	public $html_editor = false;
	public $do_shortcodes = false;

	protected $can_be_ordered = false;
	protected $is_configuration_page = true;
	protected $can_be_searched = true;
	
	public function isNotEmpty($listing) {
		if ($this->value)
			return true;
		else
			return false;
	}

	public function configure() {
		global $wpdb, $w2dc_instance;

		if (w2dc_getValue($_POST, 'submit') && wp_verify_nonce($_POST['w2dc_configure_content_fields_nonce'], W2DC_PATH)) {
			$validation = new form_validation();
			$validation->set_rules('max_length', __('Max length', 'W2DC'), 'required|is_natural_no_zero');
			$validation->set_rules('html_editor', __('HTML editor enabled', 'W2DC'), 'is_checked');
			$validation->set_rules('do_shortcodes', __('Shortcodes processing', 'W2DC'), 'is_checked');
			if ($validation->run()) {
				$result = $validation->result_array();
				if ($wpdb->update($wpdb->content_fields, array('options' => serialize(array('max_length' => $result['max_length'], 'html_editor' => $result['html_editor'], 'do_shortcodes' => $result['do_shortcodes']))), array('id' => $this->id), null, array('%d')))
					w2dc_addMessage(__('Field configuration was updated successfully!', 'W2DC'));
				
				$w2dc_instance->content_fields_manager->showContentFieldsTable();
			} else {
				$this->max_length = $validation->result_array('max_length');
				$this->html_editor = $validation->result_array('html_editor');
				$this->do_shortcodes = $validation->result_array('do_shortcodes');
				w2dc_addMessage($validation->error_string(), 'error');

				w2dc_renderTemplate('content_fields/fields/textarea_configuration.tpl.php', array('content_field' => $this));
			}
		} else
			w2dc_renderTemplate('content_fields/fields/textarea_configuration.tpl.php', array('content_field' => $this));
	}
	
	public function buildOptions() {
		if (isset($this->options['max_length']))
			$this->max_length = $this->options['max_length'];
		if (isset($this->options['html_editor']))
			$this->html_editor = $this->options['html_editor'];
		if (isset($this->options['do_shortcodes']))
			$this->do_shortcodes = $this->options['do_shortcodes'];
	}
	
	public function renderInput() {
		w2dc_renderTemplate('content_fields/fields/textarea_input.tpl.php', array('content_field' => $this));
	}
	
	public function validateValues(&$errors, $data) {
		$field_index = 'w2dc-field-input-' . $this->id;
	
		$validation = new form_validation();
		$rules = 'max_length[' . $this->max_length . ']';
		if ($this->canBeRequired() && $this->is_required)
			$rules .= '|required';
		$validation->set_rules($field_index, $this->name, $rules);
		if (!$validation->run())
			$errors[] = $validation->error_string();

		return $validation->result_array($field_index);
	}
	
	public function saveValue($post_id, $validation_results) {
		return update_post_meta($post_id, '_content_field_' . $this->id, $validation_results);
	}
	
	public function loadValue($post_id) {
		$this->value = get_post_meta($post_id, '_content_field_' . $this->id, true);
	}
	
	public function renderOutput($listing = null) {
		w2dc_renderTemplate('content_fields/fields/textarea_output.tpl.php', array('content_field' => $this, 'listing' => $listing));
	}
	
	public function validateCsvValues($value, &$errors) {
		if (strlen($value) > $this->max_length)
			$errors[] = sprintf(__('The %s field can not exceed %s characters in length.', 'W2DC'), $this->name, $this->max_length);
		else
			return $value;
	}
	
	public function renderOutputForMap($location, $listing) {
		return $content_field->value;
	}
}
?>