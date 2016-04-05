<?php 

class w2dc_categories_manager {
	
	public function __construct() {
		global $pagenow;

		if ($pagenow == 'post-new.php' || $pagenow == 'post.php' || $pagenow == 'admin-ajax.php') {
			add_action('add_meta_boxes', array($this, 'removeCategoriesMetabox'));
			add_action('add_meta_boxes', array($this, 'addCategoriesMetabox'));
		}
		
		add_filter('manage_' . W2DC_CATEGORIES_TAX . '_custom_column', array($this, 'taxonomy_rows'), 15, 3);
		add_filter('manage_edit-' . W2DC_CATEGORIES_TAX . '_columns',  array($this, 'taxonomy_columns'));
		add_action(W2DC_CATEGORIES_TAX . '_edit_form_fields', array($this, 'edit_tag_form'));
		if ($pagenow == 'edit-tags.php' && isset($_GET['taxonomy']) && $_GET['taxonomy'] == W2DC_CATEGORIES_TAX)
			add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_category_edit_scripts'));
		add_action('wp_ajax_select_category_icon_dialog', array($this, 'select_category_icon_dialog'));
		add_action('wp_ajax_select_category_icon', array($this, 'select_category_icon'));

		// 'checked_ontop' for directory categories taxonomy must be always be false
		add_filter('wp_terms_checklist_args', array($this, 'unset_checked_ontop'), 100);
	}
	
	// remove native locations taxonomy metabox from sidebar
	public function removeCategoriesMetabox() {
		remove_meta_box(W2DC_CATEGORIES_TAX . 'div', W2DC_POST_TYPE, 'side');
	}

	public function addCategoriesMetabox($post_type) {
		if ($post_type == W2DC_POST_TYPE && ($level = w2dc_getCurrentListingInAdmin()->level) && ($level->categories_number > 0 || $level->unlimited_categories)) {
			add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts_styles'));

			add_meta_box(W2DC_CATEGORIES_TAX,
					__('Listing categories', 'W2DC'),
					'post_categories_meta_box',
					W2DC_POST_TYPE,
					'normal',
					'high',
					array('taxonomy' => W2DC_CATEGORIES_TAX));
		}
	}
	
	public function unset_checked_ontop($args) {
		if (isset($args['taxonomy']) && $args['taxonomy'] == W2DC_CATEGORIES_TAX)
			$args['checked_ontop'] = false;

		return $args;
	}

	public function validateCategories($level, &$postarr, &$errors) {
		if (isset($postarr['tax_input'][W2DC_CATEGORIES_TAX]) && is_array($postarr['tax_input'][W2DC_CATEGORIES_TAX])) {
			if ($postarr['tax_input'][W2DC_CATEGORIES_TAX][0] == 0)
				unset($postarr['tax_input'][W2DC_CATEGORIES_TAX][0]);

			if (!$level->unlimited_categories)
				// remove unauthorized categories
				$postarr['tax_input'][W2DC_CATEGORIES_TAX] = array_slice($postarr['tax_input'][W2DC_CATEGORIES_TAX], 0, $level->categories_number, true);

			if ($level->categories && array_diff($postarr['tax_input'][W2DC_CATEGORIES_TAX], $level->categories))
				$errors[] = __('Sorry, you can not choose some categories for this level!', 'W2DC');

			$post_categories_ids = $postarr['tax_input'][W2DC_CATEGORIES_TAX];
		} else
			$post_categories_ids = array();

		return $post_categories_ids;
	}

	public function validateTags(&$postarr, &$errors) {
		if (isset($postarr[W2DC_TAGS_TAX]) && $postarr[W2DC_TAGS_TAX]) {
			$post_tags_ids = array();
			foreach ($postarr[W2DC_TAGS_TAX] AS $tag) {
				if ($term = term_exists($tag, W2DC_TAGS_TAX)) {
					$post_tags_ids[] = intval($term['term_id']);
				} else {
					if ($newterm = wp_insert_term($tag, W2DC_TAGS_TAX))
						if (!is_wp_error($newterm))
							$post_tags_ids[] = intval($newterm['term_id']);
				}
			}
		} else
			$post_tags_ids = array();

		return $post_tags_ids;
	}
	
	public function taxonomy_columns($original_columns) {
		$new_columns = $original_columns;
		array_splice($new_columns, 1);
		$new_columns['w2dc_category_id'] = __('Category ID', 'W2DC');
		$new_columns['w2dc_category_icon'] = __('Icon', 'W2DC');
		if (isset($original_columns['description']))
			unset($original_columns['description']);
		return array_merge($new_columns, $original_columns);
	}
	
	public function taxonomy_rows($row, $column_name, $term_id) {
		if ($column_name == 'w2dc_category_id') {
			return $row . $term_id;
		}
		if ($column_name == 'w2dc_category_icon') {
			return $row . $this->choose_icon_link($term_id);
		}
		return $row;
	}
	
	public function edit_tag_form($term) {
		w2dc_renderTemplate('categories/select_icon_form.tpl.php', array('term' => $term));
	}
	
	public function choose_icon_link($term_id) {
		$icon_file = $this->getCategoryIconFile($term_id);
		w2dc_renderTemplate('categories/select_icon_link.tpl.php', array('term_id' => $term_id, 'icon_file' => $icon_file));
	}
	
	public function getCategoryIconFile($term_id) {
		if (($icons = get_option('w2dc_categories_icons')) && is_array($icons) && isset($icons[$term_id]))
			return $icons[$term_id];
	}
	
	public function select_category_icon_dialog() {
		$categories_icons = array();
		
		$categories_icons_files = scandir(W2DC_CATEGORIES_ICONS_PATH);
		foreach ($categories_icons_files AS $file)
			if (is_file(W2DC_CATEGORIES_ICONS_PATH . $file) && $file != '.' && $file != '..')
				$categories_icons[] = $file;
		
		w2dc_renderTemplate('categories/select_icons_dialog.tpl.php', array('categories_icons' => $categories_icons));
		die();
	}

	public function select_category_icon() {
		if (isset($_POST['category_id']) && is_numeric($_POST['category_id'])) {
			$category_id = $_POST['category_id'];
			$icons = get_option('w2dc_categories_icons');
			if (isset($_POST['icon_file']) && $_POST['icon_file']) {
				$icon_file = $_POST['icon_file'];
				if (is_file(W2DC_CATEGORIES_ICONS_PATH . $icon_file)) {
					$icons[$category_id] = $icon_file;
					update_option('w2dc_categories_icons', $icons);
					echo $category_id;
				}
			} else {
				if (isset($icons[$category_id]))
					unset($icons[$category_id]);
				update_option('w2dc_categories_icons', $icons);
			}
		}
		die();
	}
	
	public function admin_enqueue_category_edit_scripts() {
		wp_enqueue_script('categories_edit_scripts');
		wp_localize_script(
				'categories_edit_scripts',
				'categories_icons',
				array(
						'categories_icons_url' => W2DC_CATEGORIES_ICONS_URL,
						'ajax_dialog_title' => __('Select category icon', 'W2DC')
				)
		);
	}
	
	public function admin_enqueue_scripts_styles() {
		wp_enqueue_script('categories_scripts');

		if ($listing = w2dc_getCurrentListingInAdmin()) {
			if ($listing->level->unlimited_categories)
				$categories_number = 'unlimited';
			else 
				$categories_number = $listing->level->categories_number;

			wp_localize_script(
					'categories_scripts',
					'level_categories',
					array(
							'level_categories_array' => $listing->level->categories,
							'level_categories_number' => $categories_number,
							'level_categories_notice_disallowed' => __('Sorry, you can not choose this category for this level!', 'W2DC'),
							'level_categories_notice_number' => sprintf(__('Sorry, you can not choose more than %d categories!', 'W2DC'), $categories_number)
					)
			);
		}
	}
}

?>