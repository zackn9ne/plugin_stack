<?php

class w2dc_csv_manager {
	public $menu_page_hook;
	
	private $test_mode = false;
	
	private $log = array('errors' => array(), 'messages' => array());
	private $header_columns = array();
	private $rows = array();
	private $collated_fields = array();
	
	private $csv_file_name;
	private $images_dir;
	private $columns_separator;
	private $images_separator;
	private $categories_separator;
	private $category_not_found;
	private $selected_user;
	private $do_geocode;
	private $is_claimable;
	
	public $collation_fields;
	
	public function __construct() {
		add_action('admin_menu', array($this, 'menu'));
	}
	
	public function menu() {
		$this->menu_page_hook = add_submenu_page('w2dc_admin',
			__('CSV Import', 'W2DC'),
			__('CSV Import', 'W2DC'),
			'administrator',
			'w2dc_csv_import',
			array($this, 'w2dc_csv_import')
		);
	}
	
	private function buildCollationColumns() {
		global $w2dc_instance;
		
		$this->collation_fields = array(
				'title' => __('Title*', 'W2DC'),
				'level_id' => __('Level ID*', 'W2DC'),
				'user' => __('Author', 'W2DC'),
				'categories_list' => __('Categories', 'W2DC'),
				'listing_tags' => __('Tags', 'W2DC'),
				'content' => __('Description', 'W2DC'),
				'excerpt' => __('Summary', 'W2DC'),
				'location_id' => __('Location ID', 'W2DC'),
				'address_1' => __('Address part 1', 'W2DC'),
				'address_2' => __('Address part 2', 'W2DC'),
				'address_3' => __('Address part 3', 'W2DC'),
				'address_4' => __('Address part 4', 'W2DC'),
				'zip' => __('Zip code or postal index', 'W2DC'),
				'latitude' => __('Latitude', 'W2DC'),
				'longitude' => __('Longitude', 'W2DC'),
				'images' => __('Images files', 'W2DC'),
				'map_icon_file' => __('Map icon file', 'W2DC'),
		);
		
		$this->collation_fields = apply_filters('w2dc_csv_collation_fields_list', $this->collation_fields);
		
		foreach ($w2dc_instance->content_fields->content_fields_array AS $field)
			if (!$field->is_core_field)
			$this->collation_fields[$field->slug] = $field->name;
	}
	
	public function w2dc_csv_import() {
		if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'import_settings') {
			// 2nd Step
			$this->csvCollateColumns();
		} elseif (isset($_REQUEST['action']) && $_REQUEST['action'] == 'import_collate' && isset($_REQUEST['csv_file_name'])) {
			// 3rd Step
			$this->csvImport();
		} elseif (!isset($_REQUEST['action'])) {
			// 1st Step
			$this->csvImportSettings();
		}
	}
	
	// 1st Step
	public function csvImportSettings($vars = array()) {

		w2dc_renderTemplate('csv_manager/import_settings.tpl.php', $vars);
	}

	// 2nd Step
	public function csvCollateColumns() {
		$this->buildCollationColumns();
		$users = get_users(array('orderby' => 'ID'));

		if ((w2dc_getValue($_POST, 'submit') || w2dc_getValue($_POST, 'goback')) && wp_verify_nonce($_POST['w2dc_csv_import_nonce'], W2DC_PATH)) {
			$errors = false;

			$validation = new form_validation();
			$validation->set_rules('columns_separator', __('Columns separator', 'W2DC'), 'required');
			$validation->set_rules('images_separator', __('Images separator', 'W2DC'), 'required');
			$validation->set_rules('categories_separator', __('categories separator', 'W2DC'), 'required');

			// GoBack button places on import results page
			if (w2dc_getValue($_POST, 'goback')) {
				$validation->set_rules('csv_file_name', __('CSV file name', 'W2DC'), 'required');
				$validation->set_rules('images_dir', __('Images directory', 'W2DC'));
				$validation->set_rules('category_not_found', __('Category not found', 'W2DC'), 'required');
				$validation->set_rules('listings_author', __('Listings author', 'W2DC'), 'required|numeric');
				$validation->set_rules('do_geocode', __('Geocode imported listings', 'W2DC'));
				if (get_option('w2dc_fsubmit_addon') && get_option('w2dc_claim_functionality'))
					$validation->set_rules('is_claimable', __('Configure imported listings as claimable', 'W2DC'));
				$validation->set_rules('fields[]', __('Listings fields', 'W2DC'));
			}

			if ($validation->run()) {
				$this->columns_separator = $validation->result_array('columns_separator');
				$this->images_separator = $validation->result_array('images_separator');
				$this->categories_separator = $validation->result_array('categories_separator');
				
				// GoBack button places on import results page
				if (w2dc_getValue($_POST, 'goback')) {
					$this->csv_file_name = $validation->result_array('csv_file_name');
					$this->images_dir = $validation->result_array('images_dir');
					$this->category_not_found = $validation->result_array('category_not_found');
					$this->selected_user = $validation->result_array('listings_author');
					$this->do_geocode = $validation->result_array('do_geocode');
					if (get_option('w2dc_fsubmit_addon') && get_option('w2dc_claim_functionality'))
						$this->is_claimable = $validation->result_array('is_claimable');
					$this->collated_fields = $validation->result_array('fields[]');
				}

				// GoBack button places on import results page
				if (w2dc_getValue($_POST, 'goback')) {
					$csv_file_name = $this->csv_file_name;

					if (!is_file($csv_file_name)) {
						w2dc_addMessage(__('CSV temp file doesn\'t exist', 'W2DC'));
						return $this->csvImportSettings($validation->result_array());
					}

					if ($this->images_dir && !is_dir($this->images_dir)) {
						w2dc_addMessage(__('Images temp directory doesn\'t exist', 'W2DC'));
						return $this->csvImportSettings($validation->result_array());
					}
				} else {
					$csv_file = $_FILES['csv_file'];

					if ($csv_file['error'] || !is_uploaded_file($csv_file['tmp_name'])) {
						w2dc_addMessage(__('There was a problem trying to upload CSV file', 'W2DC'), 'error');
						return $this->csvImportSettings($validation->result_array());
					}
	
					if (strtolower(pathinfo($csv_file['name'], PATHINFO_EXTENSION)) != 'csv' && $csv_file['type'] != 'text/csv') {
						w2dc_addMessage(__('This is not CSV file', 'W2DC'), 'error');
						return $this->csvImportSettings($validation->result_array());
					}
					
					$upload_dir = wp_upload_dir();
					$csv_file_name = $upload_dir['path'] . '/' . $csv_file["name"];
					move_uploaded_file($csv_file['tmp_name'], $csv_file_name);

					if ($_FILES['images_file']['tmp_name']) {
						$images_file = $_FILES['images_file'];
						
						if ($images_file['error'] || !is_uploaded_file($images_file['tmp_name'])) {
							w2dc_addMessage(__('There was a problem trying to upload ZIP images file', 'W2DC'), 'error');
							return $this->csvImportSettings($validation->result_array());
						}
	
						if (!$this->extractImages($images_file['tmp_name'])) {
							w2dc_addMessage(__('There was a problem trying to unpack ZIP images file', 'W2DC'), 'error');
							return $this->csvImportSettings($validation->result_array());
						}
					}
				}
				
				$this->extractCsv($csv_file_name);

				if ($this->log['errors']) {
					foreach ($this->log['errors'] AS $message)
						w2dc_addMessage($message, 'error');

					return $this->csvImportSettings($validation->result_array());
				}

				w2dc_renderTemplate('csv_manager/collate_columns.tpl.php', array(
						'collation_fields' => $this->collation_fields,
						'collated_fields' => $this->collated_fields,
						'headers' => $this->header_columns,
						'rows' => $this->rows,
						'columns_separator' => $this->columns_separator,
						'images_separator' => $this->images_separator,
						'categories_separator' => $this->categories_separator,
						'csv_file_name' => $csv_file_name,
						'images_dir' => $this->images_dir,
						'users' => $users,
						'category_not_found' => $this->category_not_found,
						'listings_author' => $this->selected_user,
						'do_geocode' => $this->do_geocode,
						'is_claimable' => $this->is_claimable,
				));
			} else {
				w2dc_addMessage($validation->error_string(), 'error');
				
				return $this->csvImportSettings($validation->result_array());
			}
		} else
			return $this->csvImportSettings();
	}
	
	// 3rd Step
	public function csvImport() {
		$this->buildCollationColumns();

		if ((w2dc_getValue($_POST, 'submit') || w2dc_getValue($_POST, 'tsubmit')) && wp_verify_nonce($_POST['w2dc_csv_import_nonce'], W2DC_PATH)) {
			if (w2dc_getValue($_POST, 'tsubmit'))
				$this->test_mode = true;

			$errors = false;

			$validation = new form_validation();
			$validation->set_rules('csv_file_name', __('CSV file name', 'W2DC'), 'required');
			$validation->set_rules('images_dir', __('Images directory', 'W2DC'));
			$validation->set_rules('columns_separator', __('Columns separator', 'W2DC'), 'required');
			$validation->set_rules('images_separator', __('Images separator', 'W2DC'), 'required');
			$validation->set_rules('categories_separator', __('categories separator', 'W2DC'), 'required');
			$validation->set_rules('category_not_found', __('Category not found', 'W2DC'), 'required');
			$validation->set_rules('listings_author', __('Listings author', 'W2DC'), 'required|numeric');
			$validation->set_rules('do_geocode', __('Geocode imported listings', 'W2DC'), 'is_checked');
			if (get_option('w2dc_fsubmit_addon') && get_option('w2dc_claim_functionality'))
				$validation->set_rules('is_claimable', __('Configure imported listings as claimable', 'W2DC'), 'is_checked');
			$validation->set_rules('fields[]', __('Listings fields', 'W2DC'));
				
			if ($validation->run()) {
				$this->csv_file_name = $validation->result_array('csv_file_name');
				$this->images_dir = $validation->result_array('images_dir');
				$this->columns_separator = $validation->result_array('columns_separator');
				$this->images_separator = $validation->result_array('images_separator');
				$this->categories_separator = $validation->result_array('categories_separator');
				$this->category_not_found = $validation->result_array('category_not_found');
				$this->selected_user = $validation->result_array('listings_author');
				$this->do_geocode = $validation->result_array('do_geocode');
				if (get_option('w2dc_fsubmit_addon') && get_option('w2dc_claim_functionality'))
					$this->is_claimable = $validation->result_array('is_claimable');
				$this->collated_fields = $validation->result_array('fields[]');
				
				if (!is_file($this->csv_file_name))
					$this->log['errors'][] = __('CSV temp file doesn\'t exist', 'W2DC');

				if ($this->images_dir && !is_dir($this->images_dir))
					$this->log['errors'][] = __('Images temp directory doesn\'t exist', 'W2DC');
				
				if (!in_array('title', $this->collated_fields))
					$this->log['errors'][] = __('Title field wasn\'t collated', 'W2DC');
				
				if (!in_array('level_id', $this->collated_fields))
					$this->log['errors'][] = __('Level ID field wasn\'t collated', 'W2DC');
		
				if ($this->selected_user != 0 && !get_userdata($this->selected_user))
					$this->log['errors'][] = __('There isn\'t author user you selected', 'W2DC');
				if ($this->selected_user == 0 && !in_array('user', $this->collated_fields))
					$this->log['errors'][] = __('Author field wasn\'t collated and default author wasn\'t selected', 'W2DC');

				$this->extractCsv($this->csv_file_name);

				if (!$this->log['errors']) {
					$this->processCSV();
	
					if (!$this->test_mode) {
						unlink($this->csv_file_name);
						if ($this->images_dir)
							$this->removeImagesDir($this->images_dir);
					}
				}
				
				w2dc_renderTemplate('csv_manager/import_results.tpl.php', array(
						'log' => $this->log,
						'test_mode' => $this->test_mode,
						'fields' => $this->collated_fields,
						'columns_separator' => $this->columns_separator,
						'images_separator' => $this->images_separator,
						'categories_separator' => $this->categories_separator,
						'csv_file_name' => $this->csv_file_name,
						'images_dir' => $this->images_dir,
						'category_not_found' => $this->category_not_found,
						'listings_author' => $this->selected_user,
						'do_geocode' => $this->do_geocode,
						'is_claimable' => $this->is_claimable,
				));
			} else {
				w2dc_addMessage($validation->error_string(), 'error');
				
				return $this->csvImportSettings($validation->result_array());
			}
		}
	}
	
	private function extractCsv($csv_file) {
		ini_set('auto_detect_line_endings', true);

		if ($fp = fopen($csv_file, 'r')) {
			$n = 0;
			while (($line_columns = @fgetcsv($fp, 0, $this->columns_separator)) !== FALSE) {
				if ($line_columns) {
					if (!$this->header_columns) {
						$this->header_columns = $line_columns;
						foreach ($this->header_columns as &$column)
							$column = trim($column);
					} else {
						if (count($line_columns) > count($this->header_columns))
							$this->log['errors'][] = sprintf(__('Line %d has too many columns', 'W2DC'), $n+1);
						elseif (count($line_columns) > count($this->header_columns))
							$this->log['errors'][] = sprintf(__('Line %d has less columns than header line', 'W2DC'), $n+1);
						else
							$this->rows[] = $line_columns;
					}
				}
				$n++;
			}
			@fclose($fp);
		} else {
			$this->log['errors'][] = __('Can\'t open CSV file', 'W2DC');
			return false;
		}
	}
	
	private function extractImages($zip_file) {
		$dir = trailingslashit(trailingslashit(sys_get_temp_dir()) . 'w2dc_' . time());
		
		require_once(ABSPATH . 'wp-admin/includes/class-pclzip.php');
		
		$zip = new PclZip($zip_file);
		if ($files = $zip->extract(PCLZIP_OPT_PATH, $dir, PCLZIP_OPT_REMOVE_ALL_PATH)) {
			$this->images_dir = $dir;
			return true;
		}

		return false;
	}
	
	private function removeImagesDir($dir) {
		foreach (scandir($dir) as $file) {
			if ($file == '.' || $file == '..')  continue;
	
			if (is_dir($dir . $file)) {
				$this->remove_directory($dir . $file);
				rmdir($dir.  $file);
			} else {
				unlink($dir . $file);
			}
		}
		rmdir($dir);
	}

	private function processCSV() {
		global $wpdb, $w2dc_instance;
		
		$this->log['messages'][] = sprintf(__('Import started, number of available rows in file: %d', 'W2DC'), count($this->rows));
		if ($this->test_mode)
			$this->log['messages'][] = __('Test mode enabled', 'W2DC');

		$users_logins = array();
		$users_emails = array();
		$users_ids = array();
		$users = get_users();
		foreach ($users AS $user) {
			$users_logins[] = $user->user_login;
			$users_emails[] = $user->user_email;
			$users_ids[] = $user->ID;
		}

		$levels = $w2dc_instance->levels->levels_array;
		$levels_ids = array_keys($levels);

		$total_rejected_lines = 0;
		foreach ($this->rows as $line=>$row) {
			$n = $line+1;
			$error_on_line = false;
			$new_listing = array();
			foreach ($this->collated_fields as $i=>$field) {
				$value = trim($row[$i]);

				if ($field == 'title') {
					$new_listing['title'] = $value;
				} elseif ($field == 'user') {
					if (!$this->selected_user) {
						if ((($key = array_search($value, $users_logins)) !== FALSE) || (($key = array_search($value, $users_emails)) !== FALSE) || (($key = array_search($value, $users_ids))) !== FALSE)
							$new_listing['user_id'] = $users_ids[$key];
						else {
							$this->log['errors'][] = sprintf(__('line %d: ', 'W2DC') . __('User "%s" doesn\'t exist', 'W2DC'), $n, $value);
							$error_on_line = true;
						}
					} else 
						$new_listing['user_id'] = $this->selected_user;
				} elseif ($field == 'level_id') {
					if (in_array($value, $levels_ids))
						$new_listing['level_id'] = $value;
					else {
						$this->log['errors'][] = sprintf(__('line %d: ', 'W2DC') . __('Wrong level ID', 'W2DC'), $n);
						$error_on_line = true;
					}
				} elseif ($field == 'categories_list') {
					$new_listing['categories'] = array_filter(array_map('trim', explode($this->categories_separator, $value)));
				} elseif ($field == 'listing_tags') {
					$new_listing['tags'] = array_filter(array_map('trim', explode($this->categories_separator, $value)));
				} elseif ($field == 'location_id') {
					if (get_term($value, W2DC_LOCATIONS_TAX))
						$new_listing['location_id'] = $value;
					else {
						$this->log['errors'][] = sprintf(__('line %d: ', 'W2DC') . __('Directory location with ID "%d" wasn\'t found', 'W2DC'), $n, $value);
						$error_on_line = true;
					}
				} elseif ($field == 'content') {
					$new_listing['content'] = $value;
				} elseif ($field == 'excerpt') {
					$new_listing['excerpt'] = $value;
				} elseif (strpos($field, 'address_') !== FALSE) {
					$new_listing['addresses'][str_replace('address_', '', $field)] = $value;
				} elseif ($field == 'zip') {
					$new_listing['zip'] = $value;
				} elseif ($field == 'latitude') {
					$new_listing['latitude'] = $value;
				} elseif ($field == 'longitude') {
					$new_listing['longitude'] = $value;
				} elseif ($field == 'images') {
					if ($this->images_dir) {
						$new_listing['images'] = array_filter(array_map('trim', explode($this->images_separator, $value)));
					} else {
						$this->log['errors'][] = sprintf(__('line %d: ', 'W2DC') . __('Images column was specified, but ZIP archive wasn\'t upload'), $n);
						$error_on_line = true;
					}
				} elseif ($content_field = $w2dc_instance->content_fields->getContentFieldBySlug($field)) {
					if (is_a($content_field, 'w2dc_content_field_checkbox')) {
						if ($value = array_map('trim', explode($this->categories_separator, $value)))
							if (count($value) == 1)
								$value = array_shift($value);
					}

					$errors = array();
					$new_listing['content_fields'][$field] = $content_field->validateCsvValues($value, $errors);
					foreach ($errors AS $error) {
						$this->log['errors'][] = sprintf(__('line %d: ', 'W2DC') . $error, $n);
						$error_on_line = true;
					}
				} elseif ($field == 'map_icon_file')
					$new_listing['map_icon_file'] = $value;
				
				$new_listing = apply_filters('w2dc_csv_process_fields', $new_listing, $field, $value);
			}

			if (!$error_on_line) {
				if (!$this->test_mode) {
					$new_listing_level = $levels[$new_listing['level_id']];

					$new_post_args = array(
							'post_title' => $new_listing['title'],
							'post_type' => W2DC_POST_TYPE,
							'post_author' => (isset($new_listing['user_id']) ? $new_listing['user_id'] : $this->selected_user),
							'post_status' => 'publish',
							'post_content' => (isset($new_listing['content']) ? $new_listing['content'] : ''),
							'post_excerpt' => (isset($new_listing['excerpt']) ? $new_listing['excerpt'] : ''),
					);
					$new_post_id = wp_insert_post($new_post_args);
					
					$wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->levels_relationships} (post_id, level_id) VALUES(%d, %d) ON DUPLICATE KEY UPDATE level_id=%d", $new_post_id, $new_listing_level->id, $new_listing_level->id));
					
					add_post_meta($new_post_id, '_listing_created', true);
					add_post_meta($new_post_id, '_order_date', time());
					add_post_meta($new_post_id, '_listing_status', 'active');
					
					if (!$new_listing_level->eternal_active_period) {
						$expiration_date = w2dc_sumDates(time(), $new_listing_level->active_days, $new_listing_level->active_months, $new_listing_level->active_years);
						add_post_meta($new_post_id, '_expiration_date', $expiration_date);
					}
					
					if (isset($new_listing['addresses'])) {
						ksort($new_listing['addresses']);
						$new_listing['address_line_1'] = implode(' ', $new_listing['addresses']);
					}
					
					if ($this->do_geocode && (isset($new_listing['address_line_1']) || isset($new_listing['location_id']))) {
						$location_string = '';
						if (isset($new_listing['location_id'])) {
							$chain = array();
							$parent_id = $new_listing['location_id'];
							while ($parent_id != 0) {
								if ($term = get_term($parent_id, W2DC_LOCATIONS_TAX)) {
									$chain[] = $term->name;
									$parent_id = $term->parent;
								} else
									$parent_id = 0;
							}
							$location_string = implode(', ', $chain);
						}
						if (isset($new_listing['address_line_1']))
							$location_string = $new_listing['address_line_1'] . ' ' . $location_string;
						if (get_option('w2dc_default_geocoding_location'))
							$location_string = $location_string . ' ' . get_option('w2dc_default_geocoding_location');

						$geoname = new locationGeoname;
						if ($result = $geoname->geonames_request(trim($location_string), 'coordinates')) {
							$new_listing['longitude'] = $result[0];
							$new_listing['latitude'] = $result[1];
						}
					}
	
					if (isset($new_listing['location_id']) || isset($new_listing['address_line_1']) || isset($new_listing['zip']) || (isset($new_listing['latitude']) && isset($new_listing['longitude']))) {
						$args = array(
								'w2dc_location[]' => array(1),
								'selected_tax[]' => array(isset($new_listing['location_id']) ? $new_listing['location_id'] : 0),
								'address_line_1[]' => array(isset($new_listing['address_line_1']) ? $new_listing['address_line_1'] : ''),
								'address_line_2[]' => array(''),
								'zip_or_postal_index[]' => array(isset($new_listing['zip']) ? $new_listing['zip'] : ''),
								'manual_coords[]' => ((isset($new_listing['location_id']) || isset($new_listing['address_line_1']) || isset($new_listing['zip'])) ? array() :(isset($new_listing['latitude']) && isset($new_listing['longitude'])) ? array(1) : array()),
								'map_coords_1[]' => array(isset($new_listing['latitude']) ? $new_listing['latitude'] : ''),
								'map_coords_2[]' => array(isset($new_listing['longitude']) ? $new_listing['longitude'] : ''),
								'map_zoom' => get_option('w2dc_default_map_zoom'),
								'map_icon_file[]' => array(isset($new_listing['map_icon_file']) ? $new_listing['map_icon_file'] : ''),
						);
						
						$args = apply_filters('w2dc_csv_save_location_args', $args, $new_post_id, $new_listing);
						
						$w2dc_instance->locations_manager->saveLocations($new_listing_level, $new_post_id, $args);
					}
	
					if (isset($new_listing['categories'])) {
						foreach ($new_listing['categories'] as $category_name) {
							if ($term = term_exists($category_name, W2DC_CATEGORIES_TAX))
								$new_listing['categories_ids'][] = intval($term['term_id']);
							else {
								if ($this->category_not_found == 'create') {
									if ($newterm = wp_insert_term($category_name, W2DC_CATEGORIES_TAX))
										if (!is_wp_error($newterm))
											$new_listing['categories_ids'][] = intval($newterm['term_id']);
										else
											$this->log['messages'][] = sprintf(__('line %d: ', 'W2DC') . __('Something wrong with directory category "%s"', 'W2DC'), $n, $category_name);
								} else
									$this->log['messages'][] = sprintf(__('line %d: ', 'W2DC') . __('Directory category "%s" wasn\'t found, was skipped', 'W2DC'), $n, $category_name);
							}
						}
						if (isset($new_listing['categories_ids']))
							wp_set_object_terms($new_post_id, $new_listing['categories_ids'], W2DC_CATEGORIES_TAX);
					}
	
					if (isset($new_listing['tags'])) {
						foreach ($new_listing['tags'] as $tag_name) {
							if ($term = term_exists($tag_name, W2DC_TAGS_TAX))
								$new_listing['tags_ids'][] = intval($term['term_id']);
							else {
								if ($this->category_not_found == 'create') {
									if ($newterm = wp_insert_term($tag_name, W2DC_TAGS_TAX))
										if (!is_wp_error($newterm))
											$new_listing['tags_ids'][] = intval($newterm['term_id']);
										else
											$this->log['messages'][] = sprintf(__('line %d: ', 'W2DC') . __('Something wrong with directory tag "%s"', 'W2DC'), $n, $tag_name);
								} else
									$this->log['messages'][] = sprintf(__('line %d: ', 'W2DC') . __('Directory tag "%s" wasn\'t found, was skipped', 'W2DC'), $n, $tag_name);
							}
						}
						if (isset($new_listing['tags_ids']))
							wp_set_object_terms($new_post_id, $new_listing['tags_ids'], W2DC_TAGS_TAX);
					}
					
					if (isset($new_listing['content_fields'])) {
						foreach ($new_listing['content_fields'] AS $field=>$values) {
							$content_field = $w2dc_instance->content_fields->getContentFieldBySlug($field);
							$content_field->saveValue($new_post_id, $values);
						}
					}
					
					if (isset($new_listing['images'])) {
						foreach ($new_listing['images'] AS $image_file_name) {
							if (file_exists($this->images_dir . $image_file_name)) {
								$filepath = $this->images_dir . $image_file_name;
							
								$file = array('name' => basename($filepath),
										'tmp_name' => $filepath,
										'error' => 0,
										'size' => filesize($filepath)
								);
							
								copy($filepath, $filepath . '.backup');
								$image = wp_handle_sideload($file, array('test_form' => FALSE));
								rename($filepath . '.backup', $filepath);

								if (!isset($image['error'])) {
									$attachment = array(
											'post_mime_type' => $image['type'],
											'post_title' => '',
											'post_content' => '',
											'post_status' => 'inherit'
									);
									if ($attach_id = wp_insert_attachment($attachment, $image['file'], $new_post_id)) {
										require_once(ABSPATH . 'wp-admin/includes/image.php');
										$attach_data = wp_generate_attachment_metadata($attach_id, $image['file']);
										wp_update_attachment_metadata($attach_id, $attach_data);
										
										// insert attachment ID to the post meta
										add_post_meta($new_post_id, '_attached_image', $attach_id);
									} else
										$this->log['errors'][] = sprintf(_x('Image file "%s" could not be inserted.', 'admin csv-import', 'WPBDM'), $image_file_name);
								} else
									$this->log['errors'][] = sprintf(_x('Image file "%s" wasn\'t attached. Full path: "%s". Error: %s', 'W2DC'), $image_file_name, $filepath, $image['error']);
							} else
								$this->log['errors'][] = sprintf(_x('There isn\'t specified image file "%s" inside ZIP file. Or temp folder wasn\'t created: "%s"', 'W2DC'), $image_file_name, $this->images_dir);
						}
					}

					if (get_option('w2dc_fsubmit_addon') && get_option('w2dc_claim_functionality') && $this->is_claimable)
						add_post_meta($new_post_id, '_is_claimable', true);
					
					do_action('w2dc_csv_create_listing', $new_post_id, $new_listing);
				}
			} else {
				$total_rejected_lines++;
			}
		}

		$this->log['messages'][] = sprintf(__('Import finished, number of errors: %d, total rejected lines: %d', 'W2DC'), count($this->log['errors']), $total_rejected_lines);
	}
}

?>