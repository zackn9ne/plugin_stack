<?php 

class w2dc_content_field_email extends w2dc_content_field {
	protected $can_be_ordered = false;
	
	public function isNotEmpty($listing) {
		if ($this->value)
			return true;
		else
			return false;
	}

	public function renderInput() {
		w2dc_renderTemplate('content_fields/fields/email_input.tpl.php', array('content_field' => $this));
	}
	
	public function validateValues(&$errors, $data) {
		$field_index = 'w2dc-field-input-' . $this->id;

		$validation = new form_validation();
		$rules = 'valid_email';
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
		w2dc_renderTemplate('content_fields/fields/email_output.tpl.php', array('content_field' => $this, 'listing' => $listing));
	}
	
	public function validateCsvValues($value, &$errors) {
		$validation = new form_validation();
		if ($validation->valid_email($value))
			return $value;
	}
	
	public function renderOutputForMap($location, $listing) {
		return w2dc_renderTemplate('content_fields/fields/email_output_map.tpl.php', array('content_field' => $this, 'listing' => $listing), true);
	}
}
?>