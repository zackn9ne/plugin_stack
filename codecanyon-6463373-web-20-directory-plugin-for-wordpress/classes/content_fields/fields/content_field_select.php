<?php 

class w2dc_content_field_select extends w2dc_content_field {
	public $selection_items = array();

	protected $can_be_ordered = false;
	protected $is_configuration_page = true;
	protected $is_search_configuration_page = true;
	protected $can_be_searched = true;
	
	public function isNotEmpty($listing) {
		if ($this->value)
			return true;
		else
			return false;
	}
	
	public function __construct() {
		// adapted for WPML
		add_action('init', array($this, 'content_fields_options_into_strings'));
	}

	public function configure() {
		global $wpdb, $w2dc_instance;

		if (w2dc_getValue($_POST, 'submit') && wp_verify_nonce($_POST['w2dc_configure_content_fields_nonce'], W2DC_PATH)) {
			$validation = new form_validation();
			$validation->set_rules('selection_items[]', __('Selection items', 'W2DC'), 'required');
			if ($validation->run()) {
				$result = $validation->result_array();
				if ($wpdb->update($wpdb->content_fields, array('options' => serialize(array('selection_items' => $result['selection_items[]']))), array('id' => $this->id), null, array('%d')))
					w2dc_addMessage(__('Field configuration was updated successfully!', 'W2DC'));
				
				$w2dc_instance->content_fields_manager->showContentFieldsTable();
			} else {
				$this->selection_items = $validation->result_array('selection_items[]');
				w2dc_addMessage($validation->error_string(), 'error');

				w2dc_renderTemplate('content_fields/fields/select_configuration.tpl.php', array('content_field' => $this));
			}
		} else
			w2dc_renderTemplate('content_fields/fields/select_configuration.tpl.php', array('content_field' => $this));
	}
	
	public function buildOptions() {
		if (isset($this->options['selection_items']))
			$this->selection_items = $this->options['selection_items'];
	}
	
	public function renderInput() {
		w2dc_renderTemplate('content_fields/fields/select_input.tpl.php', array('content_field' => $this));
	}
	
	public function validateValues(&$errors, $data) {
		$field_index = 'w2dc-field-input-' . $this->id;

		$validation = new form_validation();
		$rules = '';
		if ($this->canBeRequired() && $this->is_required)
			$rules .= '|required';
		$validation->set_rules($field_index, $this->name, $rules);
		if (!$validation->run())
			$errors[] = $validation->error_string();
		elseif ($selected_item = $validation->result_array($field_index)) {
			if (!in_array($selected_item, array_keys($this->selection_items)))
				$errors[] = sprintf(__('This selection option index "%d" doesn\'t exist', 'W2DC'), $selected_item);

			return $selected_item;
		}
	}
	
	public function saveValue($post_id, $validation_results) {
		return update_post_meta($post_id, '_content_field_' . $this->id, $validation_results);
	}
	
	public function loadValue($post_id) {
		$this->value = get_post_meta($post_id, '_content_field_' . $this->id, true);
	}
	
	public function renderOutput($listing = null) {
		w2dc_renderTemplate('content_fields/fields/select_radio_output.tpl.php', array('content_field' => $this, 'listing' => $listing));
	}

	public function validateCsvValues($value, &$errors) {
		if ($value)
			if (!in_array($value, $this->selection_items))
				$errors[] = sprintf(__('This selection option "%s" doesn\'t exist', 'W2DC'), $value);
			else
				return array_search($value, $this->selection_items);
		else 
			return '';
	}
	
	public function renderOutputForMap($location, $listing) {
		return $this->value;
	}

	// adapted for WPML
	public function content_fields_options_into_strings() {
		global $sitepress;

		if (function_exists('icl_object_id') && $sitepress) {
			if (function_exists('icl_register_string'))
				foreach ($this->selection_items AS $key=>&$item) {
					icl_register_string('Web 2.0 Directory', 'The option #' . $key . ' of content field #' . $this->id, $item);
					$item = icl_t('Web 2.0 Directory', 'The option #' . $key . ' of content field #' . $this->id, $item);
				}
		}
	}
}
?>