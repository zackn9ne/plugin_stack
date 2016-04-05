<?php 

class w2dc_content_field_datetime_search extends w2dc_content_field_search {
	public $min_max_value = array('min' => '', 'max' => '');

	public function renderSearch($random_id, $columns = 2) {
		wp_enqueue_script('jquery-ui-datepicker');
		
		if ($i18n_file = w2dc_getDatePickerLangFile(get_locale())) {
			wp_register_script('datepicker-i18n', $i18n_file, array('jquery-ui-datepicker'));
			wp_enqueue_script('datepicker-i18n');
		}

		w2dc_renderTemplate('search_fields/fields/datetime_input.tpl.php', array('search_field' => $this, 'columns' => $columns, 'dateformat' => getDatePickerFormat(), 'random_id' => $random_id));
	}
	
	public function validateSearch(&$args, $defaults = array(), $include_GET_params = true) {
		$field_index = 'field_' . $this->content_field->slug . '_min';

		if ($include_GET_params)
			$value = w2dc_getValue($_GET, $field_index, w2dc_getValue($defaults, $field_index));
		else
			$value = w2dc_getValue($defaults, $field_index);

		if ($value && (is_numeric($value) || strtotime($value))) {
			$this->min_max_value['min'] = $value;
			$args['meta_query']['relation'] = 'AND';
			$args['meta_query'][] = array(
					'key' => '_content_field_' . $this->content_field->id . '_date',
					'value' => $this->min_max_value['min'],
					'type' => 'numeric',
					'compare' => '>='
			);
		}

		$field_index = 'field_' . $this->content_field->slug . '_max';
		$value = w2dc_getValue($_GET, $field_index, w2dc_getValue($defaults, $field_index));
		if ($value && (is_numeric($value) || strtotime($value))) {
			$this->min_max_value['max'] = $value;
			$args['meta_query']['relation'] = 'AND';
			$args['meta_query'][] = array(
					'key' => '_content_field_' . $this->content_field->id . '_date',
					'value' => $this->min_max_value['max'],
					'type' => 'numeric',
					'compare' => '<='
			);
		}
	}
	
	public function getBaseUrlArgs(&$args) {
		$field_index = 'field_' . $this->content_field->slug . '_min';
		if (isset($_GET[$field_index]) && $_GET[$field_index] && is_numeric($_GET[$field_index]))
			$args[$field_index] = $_GET[$field_index];

		$field_index = 'field_' . $this->content_field->slug . '_max';
		if (isset($_GET[$field_index]) && $_GET[$field_index] && is_numeric($_GET[$field_index]))
			$args[$field_index] = $_GET[$field_index];
	}
	
	public function getVCParams() {
		wp_enqueue_script('jquery-ui-datepicker');
		if ($i18n_file = w2dc_getDatePickerLangFile(get_locale())) {
			wp_register_script('datepicker-i18n', $i18n_file, array('jquery-ui-datepicker'));
			wp_enqueue_script('datepicker-i18n');
		}
		
		return array(
				array(
					'type' => 'datefieldmin',
					'param_name' => 'field_' . $this->content_field->slug . '_min',
					'heading' => __('From ', 'W2DC') . $this->content_field->name,
					'field_id' => $this->content_field->id,
					'dependency' => array('element' => 'custom_home', 'value' => '0'),
				),
				array(
					'type' => 'datefieldmax',
					'param_name' => 'field_' . $this->content_field->slug . '_max',
					'heading' => __('To ', 'W2DC') . $this->content_field->name,
					'field_id' => $this->content_field->id,
					'dependency' => array('element' => 'custom_home', 'value' => '0'),
				)
			);
	}
}

add_action('vc_before_init', 'w2dc_vc_init_datefield');
function w2dc_vc_init_datefield() {
	add_shortcode_param('datefieldmin', 'w2dc_datefieldmin_param');
	add_shortcode_param('datefieldmax', 'w2dc_datefieldmax_param');
}
function w2dc_datefieldmin_param($settings, $value) {
	return w2dc_renderTemplate('search_fields/fields/datetime_input_vc_min.tpl.php', array('settings' => $settings, 'value' => $value, 'dateformat' => getDatePickerFormat()), true);
}
function w2dc_datefieldmax_param($settings, $value) {
	return w2dc_renderTemplate('search_fields/fields/datetime_input_vc_max.tpl.php', array('settings' => $settings, 'value' => $value, 'dateformat' => getDatePickerFormat()), true);
}
?>