<?php 

class w2dc_content_field_datetime extends w2dc_content_field {
	public $is_time = true;
	
	protected $is_configuration_page = true;
	protected $can_be_searched = true;
	
	public function isNotEmpty($listing) {
		if (array_filter($this->value))
			return true;
		else
			return false;
	}

	public function configure() {
		global $wpdb, $w2dc_instance;

		if (w2dc_getValue($_POST, 'submit') && wp_verify_nonce($_POST['w2dc_configure_content_fields_nonce'], W2DC_PATH)) {
			$validation = new form_validation();
			$validation->set_rules('is_time', __('Enable time in field', 'W2DC'), 'is_checked');
			if ($validation->run()) {
				$result = $validation->result_array();
				if ($wpdb->update($wpdb->content_fields, array('options' => serialize(array('is_time' => $result['is_time']))), array('id' => $this->id), null, array('%d')))
					w2dc_addMessage(__('Field configuration was updated successfully!', 'W2DC'));
				
				$w2dc_instance->content_fields_manager->showContentFieldsTable();
			} else {
				$this->is_time = $validation->result_array('is_time');
				w2dc_addMessage($validation->error_string(), 'error');

				w2dc_renderTemplate('content_fields/fields/datetime_configuration.tpl.php', array('content_field' => $this));
			}
		} else
			w2dc_renderTemplate('content_fields/fields/datetime_configuration.tpl.php', array('content_field' => $this));
	}
	
	public function buildOptions() {
		if (isset($this->options['is_time']))
			$this->is_time = $this->options['is_time'];
	}
	
	public function delete() {
		global $wpdb;
	
		$wpdb->delete($wpdb->postmeta, array('meta_key' => '_content_field_' . $this->id . '_date'));
		$wpdb->delete($wpdb->postmeta, array('meta_key' => '_content_field_' . $this->id . '_hour'));
		$wpdb->delete($wpdb->postmeta, array('meta_key' => '_content_field_' . $this->id . '_minute'));
	
		$wpdb->delete($wpdb->content_fields, array('id' => $this->id));
		return true;
	}
	
	public function renderInput() {
		wp_enqueue_script('jquery-ui-datepicker');
		
		if ($i18n_file = w2dc_getDatePickerLangFile(get_locale())) {
			wp_register_script('datepicker-i18n', $i18n_file, array('jquery-ui-datepicker'));
			wp_enqueue_script('datepicker-i18n');
		}

		w2dc_renderTemplate('content_fields/fields/datetime_input.tpl.php', array('content_field' => $this, 'dateformat' => getDatePickerFormat()));
	}
	
	public function validateValues(&$errors, $data) {
		$field_index_date = 'w2dc-field-input-' . $this->id;
		$field_index_hour = 'w2dc-field-input-hour_' . $this->id;
		$field_index_minute = 'w2dc-field-input-minute_' . $this->id;

		$validation = new form_validation();
		$rules = '';
		if ($this->canBeRequired() && $this->is_required)
			$rules .= 'required|is_natural_no_zero';
		$validation->set_rules($field_index_date, $this->name, $rules);
		$validation->set_rules($field_index_hour, $this->name);
		$validation->set_rules($field_index_minute, $this->name);
		if (!$validation->run())
			$errors[] = $validation->error_string();

		return array('date' => $validation->result_array($field_index_date), 'hour' => $validation->result_array($field_index_hour), 'minute' => $validation->result_array($field_index_minute));
	}
	
	public function saveValue($post_id, $validation_results) {
		if ($validation_results && is_array($validation_results)) {
			update_post_meta($post_id, '_content_field_' . $this->id . '_date', $validation_results['date']);
			update_post_meta($post_id, '_content_field_' . $this->id . '_hour', $validation_results['hour']);
			update_post_meta($post_id, '_content_field_' . $this->id . '_minute', $validation_results['minute']);
			return true;
		}
	}
	
	public function loadValue($post_id) {
		$this->value = array(
			'date' => get_post_meta($post_id, '_content_field_' . $this->id . '_date', true),
			'hour' => get_post_meta($post_id, '_content_field_' . $this->id . '_hour', true),
			'minute' => get_post_meta($post_id, '_content_field_' . $this->id . '_minute', true)
		);
			
	}
	
	public function renderOutput($listing = null) {
		if ($this->value['date']) {
			$formatted_date = mysql2date(get_option('date_format'), date('Y-m-d H:i:s', $this->value['date']));
	
			w2dc_renderTemplate('content_fields/fields/datetime_output.tpl.php', array('content_field' => $this, 'formatted_date' => $formatted_date, 'listing' => $listing));
		}
	}
	
	public function orderParams() {
		$order_params = array('orderby' => 'meta_value_num', 'meta_key' => '_content_field_' . $this->id . '_date');
		if (get_option('w2dc_orderby_exclude_null'))
			$order_params['meta_query'] = array(
				array(
					'key' => '_content_field_' . $this->id . '_date',
					'value'   => array(''),
					'compare' => 'NOT IN'
				)
			);
		return $order_params;
	}
	
	public function validateCsvValues($value, &$errors) {
		$output = array();
		if ($tmstmp = strtotime($value)) {
			$output['minute'] = date('i', $tmstmp);
			$output['hour'] = date('H', $tmstmp);
			$output['date'] = $tmstmp - 3600*$output['hour'] - 60*$output['minute'];
		}
		return $output;
	}
	
	public function renderOutputForMap($location, $listing) {
		if ($this->value['date']) {
			$formatted_date = mysql2date(get_option('date_format'), date('Y-m-d H:i:s', $this->value['date']));
	
			return w2dc_renderTemplate('content_fields/fields/datetime_output_date.tpl.php', array('content_field' => $this, 'formatted_date' => $formatted_date, 'listing' => $listing), true);
		}
	}
}
?>