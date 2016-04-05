<?php 

class w2dc_listing {
	public $post;
	public $level;
	public $expiration_date;
	public $order_date;
	public $listing_created = false;
	public $status; // active, expired, unpaid, stopped
	public $categories = array();
	public $locations = array();
	public $content_fields = array();
	public $logo_file;
	public $map_zoom;
	public $logo_image;
	public $images = array();
	public $videos = array();
	public $map;
	public $is_claimable;
	public $claim;
	public $logo_animation_effect;

	public function __construct($level_id = null) {
		if ($level_id) {
			// New listing
			$this->setLevelByID($level_id);
		}
	}
	
	// Load existed listing
	public function loadListingFromPost($post) {
		if (is_object($post))
			$this->post = $post;
		elseif (is_numeric($post))
			if (!($this->post = get_post($post)))
				return false;

		if ($this->setLevelByPostId()) {
			$this->setMetaInformation();
			$this->setLocations();
			$this->setContentFields();
			$this->setMapZoom();
			$this->setMedia();
			$this->setClaiming();
			
			apply_filters('w2dc_listing_loading', $this);
		}
		return true;
	}

	public function setLevelByID($level_id) {
		global $w2dc_instance;

		$levels = $w2dc_instance->levels;
		$this->level = $levels->getLevelById($level_id);
	}
	
	public function setMetaInformation() {
		if (!$this->level->eternal_active_period)
			$this->expiration_date = get_post_meta($this->post->ID, '_expiration_date', true);

		$this->order_date = get_post_meta($this->post->ID, '_order_date', true);

		$this->status = get_post_meta($this->post->ID, '_listing_status', true);

		$this->listing_created = get_post_meta($this->post->ID, '_listing_created', true);

		return $this->expiration_date;
	}

	public function setLevelByPostId($post_id = null) {
		global $w2dc_instance, $wpdb;

		if (!$post_id)
			$post_id = $this->post->ID;

		$levels = $w2dc_instance->levels;

		if ($level_id = $wpdb->get_var("SELECT level_id FROM {$wpdb->levels_relationships} WHERE post_id=" . $post_id))
			return $this->level = $levels->levels_array[$level_id];
		return $this->level;
	}

	public function setLocations() {
		global $wpdb;

		$results = $wpdb->get_results("SELECT * FROM {$wpdb->locations_relationships} WHERE post_id=".$this->post->ID, ARRAY_A);
		
		foreach ($results AS $row) {
			if ($row['location_id'] || $row['map_coords_1'] != '0.000000' || $row['map_coords_2'] != '0.000000' || $row['address_line_1'] || $row['zip_or_postal_index']) {
				$location = new w2dc_location($this->post->ID);
				$location_settings = array(
						'id' => $row['id'],
						'selected_location' => $row['location_id'],
						'address_line_1' => $row['address_line_1'],
						'address_line_2' => $row['address_line_2'],
						'zip_or_postal_index' => $row['zip_or_postal_index'],
						'additional_info' => $row['additional_info'],
				);
				if ($this->level->google_map) {
					$location_settings['manual_coords'] = w2dc_getValue($row, 'manual_coords');
					$location_settings['map_coords_1'] = w2dc_getValue($row, 'map_coords_1');
					$location_settings['map_coords_2'] = w2dc_getValue($row, 'map_coords_2');
					if ($this->level->google_map_markers)
						$location_settings['map_icon_file'] = w2dc_getValue($row, 'map_icon_file');
				}
				$location->createLocationFromArray($location_settings);
				
				$this->locations[] = $location;
			}
		}
	}

	public function setMapZoom() {
		if (!$this->map_zoom = get_post_meta($this->post->ID, '_map_zoom', true))
			$this->map_zoom = get_option('w2dc_default_map_zoom');
	}

	public function setContentFields() {
		global $w2dc_instance;

		$post_categories_ids = wp_get_post_terms($this->post->ID, W2DC_CATEGORIES_TAX, array('fields' => 'ids'));
		$this->content_fields = $w2dc_instance->content_fields->loadValues($this->post->ID, $post_categories_ids, $this->level->id);
	}
	
	public function setMedia() {
		if ($this->level->images_number) {
			if ($images = get_post_meta($this->post->ID, '_attached_image')) {
				foreach ($images AS $image_id)
					$this->images[$image_id] = get_post($image_id, ARRAY_A);

				if (($logo_id = (int)get_post_meta($this->post->ID, '_attached_image_as_logo', true)) && in_array($logo_id, array_keys($this->images)))
					$this->logo_image = $logo_id;
				else
					$this->logo_image = array_shift(array_keys($this->images));
			} else
				$this->images = array();
		}
		
		if ($this->level->videos_number) {
			if ($videos = get_post_meta($this->post->ID, '_attached_video_id')) {
				$videos_captions = get_post_meta($this->post->ID, '_attached_video_caption');
				foreach ($videos AS $key=>$video) {
					if (isset($videos_captions[$key]))
						$caption = $videos_captions[$key];
					else
						$caption = '';
					$this->videos[] = array('caption' => $caption, 'id' => $video);
				}
			}
		}
	}
	
	public function setClaiming() {
		$this->is_claimable = get_post_meta($this->post->ID, '_is_claimable', true);
		$this->claim = new w2dc_listing_claim($this->post->ID);
	}
	
	public function getContentField($field_id) {
		if (isset($this->content_fields[$field_id]))
			return $this->content_fields[$field_id];
	}

	public function display($is_single = false, $return = false) {
		return w2dc_renderTemplate('frontend/listing.tpl.php', array('listing' => $this, 'is_single' => $is_single), $return);
	}
	
	public function renderContentFields($is_single = true) {
		global $w2dc_instance;

		$content_fields_on_single = array();
		foreach ($this->content_fields AS $content_field) {
			if (
				$content_field->isNotEmpty($this) &&
				((!$is_single && $content_field->on_exerpt_page) || ($is_single && $content_field->on_listing_page))
			)
				if ($is_single)
					$content_fields_on_single[] = $content_field;
				else 
					$content_field->renderOutput($this);
		}

		if ($is_single && $content_fields_on_single) {
			$content_fields_by_groups = $w2dc_instance->content_fields->sortContentFieldsByGroups($content_fields_on_single);
			foreach ($content_fields_by_groups AS $item) {
				if (is_a($item, 'w2dc_content_field') || (is_a($item, 'w2dc_content_fields_group') && !$item->on_tab))
					$item->renderOutput($this);
			}
		}
	}
	
	public function getFieldsGroupsOnTabs() {
		global $w2dc_instance;

		$fields_groups = array();
		foreach ($this->content_fields AS $content_field)
			if (
				$content_field->on_listing_page &&
				$content_field->group_id &&
				$content_field->isNotEmpty($this) &&
				($content_fields_group = $w2dc_instance->content_fields->getContentFieldsGroupById($content_field->group_id)) &&
				$content_fields_group->on_tab &&
				!in_array($content_field->group_id, array_keys($fields_groups))
			) {
				$content_fields_group->setContentFields($this->content_fields);
				if ($content_fields_group->content_fields_array)
					$fields_groups[$content_field->group_id] = $content_fields_group;
			}
		return $fields_groups;
	}

	public function isMap() {
		foreach ($this->locations AS $location)
			if ($location->map_coords_1 != '0.000000' || $location->map_coords_2 != '0.000000')
				return true;

		return false;
	}
	
	public function renderMap($unique_map_id = null, $show_directions = true, $static_image = false, $enable_radius_cycle = false, $enable_clusters = false, $show_summary_button = false, $show_readmore_button = false) {
		$this->map = new google_maps;
		$this->map->setUniqueId($unique_map_id);
		$this->map->collectLocations($this);
		$this->map->display($show_directions, $static_image, $enable_radius_cycle, $enable_clusters, $show_summary_button, $show_readmore_button, false, get_option('w2dc_default_map_height'), false, false, get_option('w2dc_map_style'));
	}
	
	public function title() {
		return get_the_title($this->post);
	}

	public function processRaiseUp($invoke_hooks = true) {
		if ($this->level->raiseup_enabled) {
			$continue = true;
			if ($invoke_hooks)
				$continue = apply_filters('w2dc_listing_raiseup', $continue, $this);

			if ($continue) {
				$listings_ids = array();

				// adapted for WPML
				global $sitepress;
				if (function_exists('icl_object_id') && $sitepress) {
					$trid = $sitepress->get_element_trid($this->post->ID, 'post_' . W2DC_POST_TYPE);
					$translations = $sitepress->get_element_translations($trid);
					foreach ($translations AS $lang=>$translation)
						$listings_ids[] = $translation->element_id;
				} else
					$listings_ids[] = $this->post->ID;

				foreach ($listings_ids AS $listing_id)
					update_post_meta($listing_id, '_order_date', time());

				return true;
			}
		}
	}

	public function processActivate($is_renew = true) {
		$continue = true;
		if ($is_renew)
			$continue = apply_filters('w2dc_listing_renew', $continue, $this);
		
		if ($continue) {
			$listings = array();

			// adapted for WPML
			global $sitepress;
			if (function_exists('icl_object_id') && $sitepress) {
				$trid = $sitepress->get_element_trid($this->post->ID, 'post_' . W2DC_POST_TYPE);
				$translations = $sitepress->get_element_translations($trid);
				foreach ($translations AS $lang=>$translation) {
					$listing = new w2dc_listing();
					$listing->loadListingFromPost($translation->element_id);
					$listings[] = $listing;
				}
			} else
				$listings[] = $this;

			foreach ($listings AS $listing) {
				if (!$listing->level->eternal_active_period) {
					$expiration_date = w2dc_sumDates(time(), $listing->level->active_days, $listing->level->active_months, $listing->level->active_years);
					update_post_meta($listing->post->ID, '_expiration_date', $expiration_date);
				}
				update_post_meta($listing->post->ID, '_order_date', time());
				update_post_meta($listing->post->ID, '_listing_status', 'active');
				
				delete_post_meta($listing->post->ID, '_expiration_notification_sent');
				delete_post_meta($listing->post->ID, '_preexpiration_notification_sent');
		
				wp_update_post(array('ID' => $listing->post->ID, 'post_status' => 'publish'));

				do_action('w2dc_listing_process_activate', $listing, $is_renew);
			}
			return true;
		}
	}
	
	public function saveExpirationDate($date_array) {
		$new_tmstmp = $date_array['expiration_date_tmstmp'] + $date_array['expiration_date_hour']*3600 + $date_array['expiration_date_minute']*60;
		
		$listings_ids = array();
		
		// adapted for WPML
		global $sitepress;
		if (function_exists('icl_object_id') && $sitepress) {
			$trid = $sitepress->get_element_trid($this->post->ID, 'post_' . W2DC_POST_TYPE);
			$translations = $sitepress->get_element_translations($trid);
			foreach ($translations AS $lang=>$translation)
				$listings_ids[] = $translation->element_id;
		} else
			$listings_ids[] = $this->post->ID;

		$updated = false;
		foreach ($listings_ids AS $listing_id)
			if ($new_tmstmp != get_post_meta($listing_id, '_expiration_date', true)) {
				update_post_meta($listing_id, '_expiration_date', $new_tmstmp);
				$updated = true;
			}

		return $updated;
	}
	
	public function changeLevel($new_level_id, $invoke_hooks = true) {
		global $w2dc_instance, $wpdb;
		
		if (isset($w2dc_instance->levels->levels_array[$new_level_id]) && !$this->level->upgrade_meta[$new_level_id]['disabled']) {
			$listings = array();
			
			// adapted for WPML
			global $sitepress;
			if (function_exists('icl_object_id') && $sitepress) {
				$trid = $sitepress->get_element_trid($this->post->ID, 'post_' . W2DC_POST_TYPE);
				$translations = $sitepress->get_element_translations($trid);
				foreach ($translations AS $lang=>$translation) {
					$listing = new w2dc_listing();
					$listing->loadListingFromPost($translation->element_id);
					$listings[] = $listing;
				}
			} else
				$listings[] = $this;

			foreach ($listings AS $listing) {
				update_post_meta($listing->post->ID, '_old_level_id', $listing->level->id);
				update_post_meta($listing->post->ID, '_new_level_id', $new_level_id);
			}

			$continue = true;
			if ($invoke_hooks)
				$continue = apply_filters('w2dc_listing_upgrade', $continue, $this);
			
			if ($continue) {
				foreach ($listings AS $listing) {
					if ($wpdb->query("UPDATE {$wpdb->levels_relationships} SET level_id=" . $new_level_id . "  WHERE post_id=" . $listing->post->ID)) {
						if ($this->level->upgrade_meta[$new_level_id]['raiseup'])
							update_post_meta($listing->post->ID, '_order_date', time());
	
						$listing->setLevelByPostId($listing->post->ID);
	
						//  If new level has an option of limited active period - expiration date of listing will be reassigned automatically
						if (!$listing->level->eternal_active_period) {
							$expiration_date = w2dc_sumDates(time(), $listing->level->active_days, $listing->level->active_months, $listing->level->active_years);
							update_post_meta($listing->post->ID, '_expiration_date', $expiration_date);
						}
					}
				}
				return true;
			}
		}
	}


	/**
	 * Load existed listing especially for map info window
	 * 
	 * @param $post is required and must be object
	 */
	public function loadListingForMap($post) {
		$this->post = $post;
	
		if ($this->setLevelByPostId()) {
			$this->setLocations();
			$this->setMapZoom();
			$this->setLogoImage();
				
			apply_filters('w2dc_listing_map_loading', $this);
		}
		return true;
	}

	/**
	 * Load existed listing especially for AJAX map - set only locations
	 * 
	 * @param $post is required and must be object
	 */
	public function loadListingForAjaxMap($post) {
		$this->post = $post;
	
		if ($this->setLevelByPostId())
			$this->setLocations();

		return true;
	}

	public function setLogoImage() {
		if ($this->level->images_number) {
			if ($logo_id = (int)get_post_meta($this->post->ID, '_attached_image_as_logo', true))
				$this->logo_image = $logo_id;
			else {
				$images = get_post_meta($this->post->ID, '_attached_image');
				$this->logo_image = array_shift($images);
			}
		}
	}

	public function setMapContentFields($map_content_fields, $location) {
		$post_categories_ids = wp_get_post_terms($this->post->ID, W2DC_CATEGORIES_TAX, array('fields' => 'ids'));
		$content_fields_output = array(
			$location->renderInfoFieldForMap()
		);
		
		foreach($map_content_fields AS $field_slug=>$content_field) {
			// is it native content field
			if (is_a($content_field, 'w2dc_content_field')) {
				if (
					(!$content_field->isCategories() || $content_field->categories === array() || array_intersect($content_field->categories, $post_categories_ids)) &&
					($content_field->is_core_field || !$this->level->content_fields || in_array($content_field->id, $this->level->content_fields))
				) {
					$content_field->loadValue($this->post->ID);
					$content_fields_output[] = $content_field->renderOutputForMap($location, $this);
				} else 
					$content_fields_output[] = null;
			} else
				$content_fields_output[] = apply_filters('w2dc_map_info_window_fields_values', $content_field, $field_slug, $this);
		}

		return $content_fields_output;
	}

	public function getExcerptFromContent($words_length = 35) {
		$the_excerpt = strip_tags(strip_shortcodes($this->post->post_content));
		$words = explode(' ', $the_excerpt, $words_length + 1);
		if (count($words) > $words_length) {
			array_pop($words);
			array_push($words, '…');
			$the_excerpt = implode(' ', $words);
		}
		return $the_excerpt;
	}
}

class w2dc_listing_claim {
	public $listing_id;
	public $claimer_id;
	public $claimer;
	public $claimer_message;
	public $status = null;
	
	public function __construct($listing_id) {
		$this->listing_id = $listing_id;
		if ($claim_record = get_post_meta($listing_id, '_claim_data', true)) {
			if (isset($claim_record['claimer_id'])) {
				$this->claimer_id = $claim_record['claimer_id'];
				if ($claimer = get_userdata($claim_record['claimer_id']))
					$this->claimer = $claimer;
			}
			if (isset($claim_record['claimer_message']))
				$this->claimer_message = $claim_record['claimer_message'];
			if (isset($claim_record['status']))
				$this->status = $claim_record['status'];
			else 
				$this->status = 'pending';
		}
	}
	
	public function updateRecord($claimer_id = null, $claimer_message = null, $status = null) {
		if ($claimer_id !== null) {
			$this->claimer_id = $claimer_id;
			update_post_meta($this->listing_id, '_claimer_id', $this->claimer_id);
			if ($claimer = get_userdata($claimer_id))
				$this->claimer = $claimer;
		}
		if ($claimer_message !== null)
			$this->claimer_message = $claimer_message;
		if ($status !== null)
			$this->status = $status;
		return update_post_meta($this->listing_id, '_claim_data', array('claimer_id' => $this->claimer_id, 'claimer_message' => $this->claimer_message, 'status' => $this->status));
	}
	
	public function deleteRecord() {
		delete_post_meta($this->listing_id, '_claimer_id');
		return delete_post_meta($this->listing_id, '_claim_data');
	}
	
	public function isClaimed() {
		return (bool) ($this->status == 'pending');
	}

	public function getClaimMessage() {
		if ($this->claimer_id == get_current_user_id()) {
			if ($this->status == 'approved')
				return __('Your claim was approved successully', 'W2DC');
			else
				return __('Your claim was not approved yet', 'W2DC');
		} else {
			if ($this->status != 'approved')
				return __('You may approve or decline claim', 'W2DC');
		}
	}
	
	public function isOption() {
		return ($this->status != 'approved' && $this->claimer_id != get_current_user_id());
	}
	
	public function approve() {
		$postarr = array(
				'ID' => $this->listing_id,
				'post_author' => $this->claimer_id
		);
		$result = wp_update_post($postarr, true);
		if (!is_wp_error($result)) {
			if (get_option('w2dc_after_claim') == 'expired')
				update_post_meta($this->listing_id, '_listing_status', 'expired');
			update_post_meta($this->listing_id, '_is_claimable', false);
			return $this->updateRecord(null, null, 'approved');
		}
	}
}

?>