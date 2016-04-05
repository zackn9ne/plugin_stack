<?php

define('W2DC_FSUBMIT_PATH', plugin_dir_path(__FILE__));

function w2dc_fsubmit_loadPaths() {
	define('W2DC_FSUBMIT_TEMPLATES_PATH', W2DC_FSUBMIT_PATH . 'templates/');

	if (!defined('W2DC_THEME_MODE')) {
		define('W2DC_FSUBMIT_RESOURCES_PATH', W2DC_FSUBMIT_PATH . 'resources/');
		define('W2DC_FSUBMIT_RESOURCES_URL', plugins_url('/', __FILE__) . 'resources/');
	}
}
add_action('init', 'w2dc_fsubmit_loadPaths', 0);

define('W2DC_FSUBMIT_SHORTCODE', 'webdirectory-submit');
define('W2DC_DASHBOARD_SHORTCODE', 'webdirectory-dashboard');

include_once W2DC_FSUBMIT_PATH . 'classes/dashboard_controller.php';
include_once W2DC_FSUBMIT_PATH . 'classes/submit_controller.php';
include_once W2DC_FSUBMIT_PATH . 'classes/levels_table_controller.php';

global $w2dc_wpml_dependent_options;
$w2dc_wpml_dependent_options[] = 'w2dc_tospage';
$w2dc_wpml_dependent_options[] = 'w2dc_submit_login_page';
$w2dc_wpml_dependent_options[] = 'w2dc_dashboard_login_page';

class w2dc_fsubmit_plugin {

	public function init() {
		global $w2dc_instance, $w2dc_shortcodes_init;
		
		if (!get_option('w2dc_installed_fsubmit'))
			w2dc_install_fsubmit();
		add_action('w2dc_version_upgrade', 'w2dc_upgrade_fsubmit');

		add_filter('w2dc_build_settings', array($this, 'plugin_settings'));

		// add new shortcodes for frontend submission and dashboard
		$w2dc_shortcodes_init['webdirectory-submit'] = 'w2dc_submit_controller';
		$w2dc_shortcodes_init['webdirectory-dashboard'] = 'w2dc_dashboard_controller';
		$w2dc_shortcodes_init['webdirectory-levels-table'] = 'w2dc_levels_table_controller';
		add_shortcode('webdirectory-submit', array($w2dc_instance, 'renderShortcode'));
		add_shortcode('webdirectory-dashboard', array($w2dc_instance, 'renderShortcode'));
		add_shortcode('webdirectory-levels-table', array($w2dc_instance, 'renderShortcode'));
		
		add_action('init', array($this, 'getSubmitPage'), 0);
		add_action('init', array($this, 'getDasboardPage'), 0);

		add_filter('w2dc_get_edit_listing_link', array($this, 'edit_listings_links'), 10, 2);

		add_action('w2dc_directory_frontpanel', array($this, 'add_submit_button'));
		add_action('w2dc_directory_frontpanel', array($this, 'add_claim_button'));
		
		add_action('w2dc_directory_frontpanel', array($this, 'add_logout_button'));

		add_action('init', array($this, 'remove_admin_bar'));
		add_action('admin_init', array($this, 'restrict_dashboard'));

		add_action('w2dc_listing_process_activate', array($this, 'listing_activation_post_status'), 10, 2);
		
		if (get_option('w2dc_payments_addon')) {
			add_action('show_user_profile', array($this, 'add_user_profile_fields'));
			add_action('edit_user_profile', array($this, 'add_user_profile_fields'));
			add_action('personal_options_update', array($this, 'save_user_profile_fields'));
			add_action('edit_user_profile_update', array($this, 'save_user_profile_fields'));
		}
		
		add_filter('no_texturize_shortcodes', array($this, 'w2dc_no_texturize'));
		
		/* add_action('pmpro_checkout_after_level_cost', array($this, 'pmpro_level_description'));
		add_action('pmpro_membership_level_after_other_settings', array($this, 'pmpro_level_settings'));
		add_action('pmpro_save_membership_level', array($this, 'pmpro_save_level_settings'), 10, 1);
		add_action('pmpro_after_membership_level_profile_fields', array($this, 'pmpro_user_profile_fields'), 10, 1);
		add_filter("pmpro_pages_shortcode_levels", array($this, 'pmpro_pages_shortcode_levels')); */
		
		add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts_styles'));
	}
	
	public function w2dc_no_texturize($shortcodes) {
		$shortcodes[] = 'webdirectory-submit';
		$shortcodes[] = 'webdirectory-dashboard';

		return $shortcodes;
	}
	
	public function pmpro_level_description() {
		global $w2dc_instance, $pmpro_level;

		?>
		<table id="w2dc_directory_listings" class="pmpro_checkout top1em" width="100%" cellpadding="0" cellspacing="0" border="0">
			<thead>
				<tr>
					<th><?php _e('Directory listings available', 'W2DC'); ?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>
					<?php foreach ($w2dc_instance->levels->levels_array as $level): ?>
						<div>
							<label><?php echo $level->name; ?>:</label>
							<label><?php echo getPMPROlistingsNumberByLevel($pmpro_level->id, $level->id); ?></label>
						</div>
					<?php endforeach; ?>
					</td>
				</tr>
			</tbody>
		</table>
		<?php 
	}

	public function pmpro_level_settings() {
		global $w2dc_instance, $wpdb;

		if (isset($_REQUEST['edit']))
			$edit = $_REQUEST['edit'];	
		else
			$edit = false;

		$w2dc_pmpro_levels = get_option('w2dc_pmpro_levels');
		?>
		<script>
		jQuery(document).ready(function($) {
			$("input[name*='w2dc_level_unlimited_']").each( function() {
				levelUnlimitedChange($(this));
			});

			$("input[name*='w2dc_level_unlimited_']").change( function() {
				levelUnlimitedChange($(this));
			});

			function levelUnlimitedChange(checkbox) {
				if (checkbox.is(':checked'))
					checkbox.parent().parent().find(".w2dc_level_value").attr('disabled', 'true');
				else
					checkbox.parent().parent().find(".w2dc_level_value").removeAttr('disabled');
			}
		});
		</script>
		<h3 class="topborder"><?php _e('Directory Settings', 'W2DC');?></h3>
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row" valign="top"><label><?php _e('Listings levels', 'W2DC');?>:</label></th>
					<td>
						<?php
						if ($edit) {
							$pmpro_level = $wpdb->get_row("SELECT * FROM $wpdb->pmpro_membership_levels WHERE id = '$edit' LIMIT 1", OBJECT);
							$pmpro_level_id = $pmpro_level->id;
						} else 
							$pmpro_level_id = 0;

						echo "<ul>";
						foreach ($w2dc_instance->levels->levels_array as $level) {
							echo "<li>";
							echo "<span>";
							echo "<input name='w2dc_level_{$level->id}' class='w2dc_level_value' type='text' size='2' value='".(isset($w2dc_pmpro_levels[$pmpro_level_id][$level->id]) ? $w2dc_pmpro_levels[$pmpro_level_id][$level->id]['value'] : 0)."' />";
							_e(' or ', 'W2DC');
							echo "<label><input name='w2dc_level_unlimited_{$level->id}' type='checkbox' class='unlimited_listings' value='1' ".(isset($w2dc_pmpro_levels[$pmpro_level_id][$level->id]) ? checked($w2dc_pmpro_levels[$pmpro_level_id][$level->id]['unlimited'], 1, false) : 'checked="checked"')." />" . __('unlimited', 'W2DC') . '</label> ';
							echo "- {$level->name}";
							echo "</span>";
							echo "</li>";
						}
						echo "</ul>";
						?>
						<small><?php _e('Enter number of allowed directory listings for this membership level or set unlimited.', 'W2DC');?></small>
					</td>
				</tr>
			</tbody>
		</table>
		<?php 
	}
	
	public function pmpro_save_level_settings($saveid) {
		global $w2dc_instance, $msg, $msgt;
		
		$validation = new form_validation();
		foreach ($w2dc_instance->levels->levels_array as $level) {
			$validation->set_rules('w2dc_level_'.$level->id, __('Listings number', 'W2DC'), 'numeric');
			$validation->set_rules('w2dc_level_unlimited_'.$level->id, __('Listings unlimited', 'W2DC'), 'is_checked');
		}
		if ($validation->run()) {
			$result = get_option('w2dc_pmpro_levels') ? get_option('w2dc_pmpro_levels') : array();
			foreach ($w2dc_instance->levels->levels_array as $level) {
				$result[$saveid][$level->id]['value'] = w2dc_getValue($validation->result_array(), 'w2dc_level_'.$level->id, 0);
				$result[$saveid][$level->id]['unlimited'] = w2dc_getValue($validation->result_array(), 'w2dc_level_unlimited_'.$level->id);
			}
			update_option('w2dc_pmpro_levels', $result);
		} else {
			$msg = -1;
			$msgt = sprintf(__("Error updating membership level: %s", 'W2DC'), $validation->error_string());
		}
	}

	public function pmpro_user_profile_fields($user) {
var_dump($user);
		?>
		<table class="form-table">
			<tr>
				<th><label for="membership_level"><?php _e("Available directory listings", "W2DC"); ?></label></th>
				<td>
		<?php 
		if (!get_user_meta($user, 'w2dc_pmpro_level_listings')) {
			if (!pmpro_getMembershipLevelForUser())
				_e('Level');
		}
		?>
				</td>
			</tr>
		</table>
		<?php 
		var_dump(get_user_meta($user, 'w2dc_pmpro_level_listings'));
		var_dump(get_option('w2dc_pmpro_levels'));
	}
	
	public function pmpro_pages_shortcode_levels() {
		$w2dc_pmpro_levels = get_option('w2dc_pmpro_levels') ? get_option('w2dc_pmpro_levels') : array();

		return w2dc_renderTemplate(array(W2DC_FSUBMIT_TEMPLATES_PATH, 'pmpro/levels.tpl.php'), array('w2dc_pmpro_levels' => $w2dc_pmpro_levels), true);
	}

	public function plugin_settings($options) {
		global $sitepress; // adapted for WPML

		$pages = get_pages(array(
			'depth' => 0, 'child_of' => 0,
			'selected' => 0, 'echo' => 0,
			'name' => 'page_id', 'id' => '',
			'show_option_none' => '', 'show_option_no_change' => '',
			'option_none_value' => ''
		));
		$all_pages[] = array('value' => 0, 'label' => __('- Select page -', 'W2DC'));
		foreach ($pages AS $page)
			$all_pages[] = array('value' => $page->ID, 'label' => $page->post_title);
		
		$options['template']['menus']['general']['controls']['fsubmit'] = array(
			'type' => 'section',
			'title' => __('Frontend submission and dashboard', 'W2DC'),
			'fields' => array(
				array(
					'type' => 'radiobutton',
					'name' => 'w2dc_fsubmit_login_mode',
					'label' => __('Select frontend submission login mode', 'W2DC'),
					'items' => array(
						array(
							'value' => 1,
							'label' => __('login required', 'W2DC'),
						),
						array(
							'value' => 2,
							'label' => __('necessary to fill in contact form', 'W2DC'),
						),
						array(
							'value' => 3,
							'label' => __('not necessary to fill in contact form', 'W2DC'),
						),
						array(
							'value' => 4,
							'label' => __('do not even show contact form', 'W2DC'),
						),
					),
					'default' => array(
						get_option('w2dc_fsubmit_login_mode'),
					),
				),
				array(
					'type' => 'select',
					'name' => 'w2dc_fsubmit_default_status',
					'label' => __('Post status after frontend submit', 'W2DC'),
					'items' => array(
						array(
							'value' => 1,
							'label' => __('Pending Review', 'W2DC'),
						),
						array(
							'value' => 2,
							'label' => __('Draft', 'W2DC'),
						),
						array(
							'value' => 3,
							'label' => __('Published', 'W2DC'),
						),
					),
					'default' => array(
						get_option('w2dc_fsubmit_default_status'),
					),
				),
				array(
					'type' => 'select',
					'name' => 'w2dc_fsubmit_edit_status',
					'label' => __('Post status after listing was modified', 'W2DC'),
					'items' => array(
						array(
							'value' => 1,
							'label' => __('Pending Review', 'W2DC'),
						),
						array(
							'value' => 2,
							'label' => __('Draft', 'W2DC'),
						),
						array(
							'value' => 3,
							'label' => __('Published', 'W2DC'),
						),
					),
					'default' => array(get_option('w2dc_fsubmit_edit_status'),),
				),
				array(
					'type' => 'toggle',
					'name' => 'w2dc_fsubmit_button',
					'label' => __('Enable submit listing button', 'W2DC'),
					'default' => get_option('w2dc_fsubmit_button'),
				),
				array(
					'type' => 'toggle',
					'name' => 'w2dc_hide_admin_bar',
					'label' => __('Hide top admin bar at the frontend for regular users and do not allow them to see dashboard at all', 'W2DC'),
					'default' => get_option('w2dc_hide_admin_bar'),
				),
				array(
					'type' => 'toggle',
					'name' => 'w2dc_allow_edit_profile',
					'label' => __('Allow users to manage own profile at the frontend dashboard', 'W2DC'),
					'default' => get_option('w2dc_allow_edit_profile'),
				),
				array(
					'type' => 'toggle',
					'name' => 'w2dc_enable_tags',
					'label' => __('Enable listings tags input at the frontend', 'W2DC'),
					'default' => get_option('w2dc_enable_tags'),
				),
				array(
					'type' => 'select',
					'name' => get_wpml_dependent_option_name('w2dc_tospage'), // adapted for WPML
					'label' => __('Require Terms of Services on submission page?', 'W2DC'),
					'description' => __('If yes, create a WordPress page containing your TOS agreement and assign it using the dropdown above.', 'W2DC') . get_wpml_dependent_option_description(),
					'items' => $all_pages,
					'default' => (get_wpml_dependent_option('w2dc_tospage') ? array(get_wpml_dependent_option('w2dc_tospage')) : array(0)), // adapted for WPML
				),
				array(
					'type' => 'select',
					'name' => get_wpml_dependent_option_name('w2dc_submit_login_page'), // adapted for WPML
					'label' => __('Use custom login page for listings submission process', 'W2DC'),
					'description' => __('When login required before submission user have to login using default login form. You may use any 3rd party plugin to make custom login page and assign it with submission process using the dropdown above.', 'W2DC') . get_wpml_dependent_option_description(),
					'items' => $all_pages,
					'default' => (get_wpml_dependent_option('w2dc_submit_login_page') ? array(get_wpml_dependent_option('w2dc_submit_login_page')) : array(0)), // adapted for WPML
				),
				array(
					'type' => 'select',
					'name' => get_wpml_dependent_option_name('w2dc_dashboard_login_page'), // adapted for WPML
					'label' => __('Use custom login page for login into dashboard', 'W2DC'),
					'description' => __('You may use any 3rd party plugin to make custom login page and assign it with login into dashboard using the dropdown above.', 'W2DC') . get_wpml_dependent_option_description(),
					'items' => $all_pages,
					'default' => (get_wpml_dependent_option('w2dc_dashboard_login_page') ? array(get_wpml_dependent_option('w2dc_dashboard_login_page')) : array(0)), // adapted for WPML
				),
			),
		);
		$options['template']['menus']['general']['controls']['claim'] = array(
			'type' => 'section',
			'title' => __('Claim functionality', 'W2DC'),
			'fields' => array(
				array(
					'type' => 'toggle',
					'name' => 'w2dc_claim_functionality',
					'label' => __('Enable claim functionality', 'W2DC'),
					'default' => get_option('w2dc_claim_functionality'),
				),
				array(
					'type' => 'toggle',
					'name' => 'w2dc_claim_approval',
					'label' => __('Approval of claim required', 'W2DC'),
					'description' => __('In other case claim will be processed immediately without any notifications', 'W2DC'),
					'default' => get_option('w2dc_claim_approval'),
				),
				array(
					'type' => 'radiobutton',
					'name' => 'w2dc_after_claim',
					'label' => __('What will be with listing status after successful approval?', 'W2DC'),
					'description' => __('When set to expired - renewal may be payment option', 'W2DC'),
					'items' => array(
						array(
							'value' => 'active',
							'label' =>__('just approval', 'W2DC'),
						),
						array(
							'value' => 'expired',
							'label' =>__('expired status', 'W2DC'),
						),
					),
					'default' => array(
							get_option('w2dc_after_claim')
					),
				),
				array(
					'type' => 'toggle',
					'name' => 'w2dc_hide_claim_contact_form',
					'label' => __('Hide contact form on claimable listings', 'W2DC'),
					'default' => get_option('w2dc_hide_claim_contact_form'),
				),
				array(
					'type' => 'toggle',
					'name' => 'w2dc_hide_claim_metabox',
					'label' => __('Hide claim metabox at the frontend dashboard', 'W2DC'),
					'default' => get_option('w2dc_hide_claim_metabox'),
				),
			),
		);
		
		// adapted for WPML
		global $sitepress;
		if (function_exists('icl_object_id') && $sitepress) {
			$options['template']['menus']['advanced']['controls']['wpml']['fields'][] = array(
				'type' => 'toggle',
				'name' => 'w2dc_enable_translations',
				'label' => __('Enable frontend translations management', 'W2DC'),
				'default' => get_option('w2dc_enable_translations'),
			);
		}
		
		$options['template']['menus']['notifications']['controls']['notifications']['fields'][] = array(
			'type' => 'textarea',
			'name' => 'w2dc_newuser_notification',
			'label' => __('Registration of new user notification', 'W2DC'),
			'default' => get_option('w2dc_newuser_notification'),
		);

		$options['template']['menus']['notifications']['controls']['notifications']['fields'][] = array(
			'type' => 'textarea',
			'name' => 'w2dc_newlisting_admin_notification',
			'label' => __('Notification to admin about new listing creation', 'W2DC'),
			'default' => get_option('w2dc_newlisting_admin_notification'),
		);

		$options['template']['menus']['notifications']['controls']['notifications']['fields'][] = array(
			'type' => 'textarea',
			'name' => 'w2dc_claim_notification',
			'label' => __('Notification of claim to current listing owner', 'W2DC'),
			'default' => get_option('w2dc_claim_notification'),
		);
		$options['template']['menus']['notifications']['controls']['notifications']['fields'][] = array(
			'type' => 'textarea',
			'name' => 'w2dc_claim_approval_notification',
			'label' => __('Notification of successful approval of claim', 'W2DC'),
			'default' => get_option('w2dc_claim_approval_notification'),
		);
		
		return $options;
	}

	public function getSubmitPage() {
		global $w2dc_instance, $wpdb, $wp_rewrite;
		
		$w2dc_instance->submit_page_url = '';
		$w2dc_instance->submit_page_slug = '';
		$w2dc_instance->submit_page_id = 0;
		
		if ($submit_page = $wpdb->get_row("SELECT ID AS id, post_name AS slug FROM {$wpdb->posts} WHERE (post_content LIKE '%[" . W2DC_FSUBMIT_SHORTCODE . "]%' OR post_content LIKE '%[" . W2DC_FSUBMIT_SHORTCODE . " %') AND post_status = 'publish' AND post_type = 'page' LIMIT 1", ARRAY_A)) {
			$w2dc_instance->submit_page_id = $submit_page['id'];
			$w2dc_instance->submit_page_slug = $submit_page['slug'];
			
			// adapted for WPML
			global $sitepress;
			if (function_exists('icl_object_id') && $sitepress) {
				if ($tpage = icl_object_id($w2dc_instance->submit_page_id, 'page')) {
					$w2dc_instance->submit_page_id = $tpage;
					$w2dc_instance->submit_page_slug = get_post($w2dc_instance->submit_page_id)->post_name;
				}
			}
			
			if ($wp_rewrite->using_permalinks())
				$w2dc_instance->submit_page_url = get_permalink($w2dc_instance->submit_page_id);
			else
				$w2dc_instance->submit_page_url = add_query_arg('page_id', $w2dc_instance->submit_page_id, home_url('/'));
		}

		if (get_option('w2dc_fsubmit_button') && $w2dc_instance->submit_page_id === 0 && is_admin())
			w2dc_addMessage(__('<b>Web 2.0 Directory Frontend submit plugin</b>: sorry, but there isn\'t any page with [webdirectory-submit] shortcode. This is impossible to add new listings from frontend.', 'W2DC'));
	}

	public function getDasboardPage() {
		global $w2dc_instance, $wpdb, $wp_rewrite;
		
		$w2dc_instance->dashboard_page_url = '';
		$w2dc_instance->dashboard_page_slug = '';
		$w2dc_instance->dashboard_page_id = 0;

		if ($dashboard_page = $wpdb->get_row("SELECT ID AS id, post_name AS slug FROM {$wpdb->posts} WHERE post_content LIKE '%[" . W2DC_DASHBOARD_SHORTCODE . "]%' AND post_status = 'publish' AND post_type = 'page' LIMIT 1", ARRAY_A)) {
			$w2dc_instance->dashboard_page_id = $dashboard_page['id'];
			$w2dc_instance->dashboard_page_slug = $dashboard_page['slug'];
			
			// adapted for WPML
			global $sitepress;
			if (function_exists('icl_object_id') && $sitepress) {
				if ($tpage = icl_object_id($w2dc_instance->dashboard_page_id, 'page')) {
					$w2dc_instance->dashboard_page_id = $tpage;
					$w2dc_instance->dashboard_page_slug = get_post($w2dc_instance->dashboard_page_id)->post_name;
				}
			}
			
			if ($wp_rewrite->using_permalinks())
				$w2dc_instance->dashboard_page_url = get_permalink($w2dc_instance->dashboard_page_id);
			else
				$w2dc_instance->dashboard_page_url = add_query_arg('page_id', $w2dc_instance->dashboard_page_id, home_url('/'));
		}
	}
	
	public function add_submit_button() {
		global $w2dc_instance;

		if (get_option('w2dc_fsubmit_button') && $w2dc_instance->submit_page_url)
			echo '<button class="w2dc-submit-listing-link w2dc-btn w2dc-btn-primary" name="submitlisting" onclick="window.location=\'' . w2dc_submitUrl() . '\';"><span class="w2dc-glyphicon w2dc-glyphicon-plus"></span> ' . __('Submit new listing', 'W2DC') . '</button> ';
	}

	public function add_claim_button($listing) {
		global $w2dc_instance;

		if ($listing && $listing->is_claimable && $w2dc_instance->dashboard_page_url && get_option('w2dc_claim_functionality') && $listing->post->post_author != get_current_user_id())
			echo '<button class="w2dc-claim-listing-link w2dc-btn w2dc-btn-primary" name="claimlisting" onclick="window.location=\'' . w2dc_dashboardUrl(array('listing_id' => $listing->post->ID, 'w2dc_action' => 'claim_listing')) . '\';"><span class="w2dc-glyphicon w2dc-glyphicon-flag"></span> ' . __('Is this your ad?', 'W2DC') . '</button> ';
	}

	public function add_logout_button() {
		global $w2dc_instance, $post;

		if ($post->ID == $w2dc_instance->dashboard_page_id)
			echo '<button class="w2dc-logout-link w2dc-btn w2dc-btn-primary" name="logout" onclick="window.location=\'' . wp_logout_url(w2dc_directoryUrl()) . '\';"><span class="w2dc-glyphicon w2dc-glyphicon-log-out"></span> ' . __('Log out', 'W2DC') . '</button>';
	}
	
	public function remove_admin_bar() {
		if (get_option('w2dc_hide_admin_bar') && !current_user_can('administrator') && !is_admin()) {
			show_admin_bar(false);
			add_filter('show_admin_bar', '__return_false', 99999);
		}
	}

	public function restrict_dashboard() {
		global $w2dc_instance, $pagenow;

		if ($pagenow != 'admin-ajax.php' && $pagenow != 'async-upload.php')
			if (get_option('w2dc_hide_admin_bar') && !current_user_can('administrator') && is_admin()) {
				//w2dc_addMessage(__('You can not see dashboard!', 'W2DC'), 'error');
				wp_redirect(w2dc_directoryUrl());
				die();
			}
	}

	public function edit_listings_links($url, $post_id) {
		global $w2dc_instance;

		if (!is_admin() && $w2dc_instance->dashboard_page_url && ($post = get_post($post_id)) && $post->post_type == W2DC_POST_TYPE)
			return w2dc_dashboardUrl(array('w2dc_action' => 'edit_listing', 'listing_id' => $post_id));
	
		return $url;
	}
	
	public function listing_activation_post_status($listing, $is_renew) {
		if (!$is_renew) {
			if (get_option('w2dc_fsubmit_default_status') == 1) {
				$post_status = 'pending';
				$message = __('Listing awaiting moderators approval.', 'W2DC');
			} elseif (get_option('w2dc_fsubmit_default_status') == 2) {
				$post_status = 'draft';
				$message = __('Listing was saved successfully as draft! Contact site manager, please.', 'W2DC');
			} elseif (get_option('w2dc_fsubmit_default_status') == 3) {
				$post_status = 'publish';
				$message = false;
			}
			wp_update_post(array('ID' => $listing->post->ID, 'post_status' => $post_status));
			if ($message)
				w2dc_addMessage($message);
		}
	}
	
	public function add_user_profile_fields($user) { ?>
		<h3><?php _e('Directory billing information', 'W2DC'); ?></h3>
	
		<table class="form-table">
			<tr>
				<th><label for="w2dc_billing_name"><?php _e('Full name', 'W2DC'); ?></label></th>
				<td>
					<input type="text" name="w2dc_billing_name" id="w2dc_billing_name" value="<?php echo esc_attr(get_the_author_meta('w2dc_billing_name', $user->ID)); ?>" class="regular-text" /><br />
				</td>
			</tr>
			<tr>
				<th><label for="w2dc_billing_address"><?php _e('Full address', 'W2DC'); ?></label></th>
				<td>
					<textarea name="w2dc_billing_address" id="w2dc_billing_address" cols="30" rows="3"><?php echo esc_textarea(get_the_author_meta('w2dc_billing_address', $user->ID)); ?></textarea>
				</td>
			</tr>
		</table>
<?php }

	public function save_user_profile_fields($user_id) {
		if (!current_user_can('edit_user', $user_id))
			return false;

		update_user_meta($user_id, 'w2dc_billing_name', $_POST['w2dc_billing_name']);
		update_user_meta($user_id, 'w2dc_billing_address', $_POST['w2dc_billing_address']);
	}
	
	public function enqueue_scripts_styles($load_scripts_styles = false) {
		global $w2dc_instance, $w2dc_fsubmit_enqueued;
		if (($w2dc_instance->frontend_controllers || $load_scripts_styles) && !$w2dc_fsubmit_enqueued) {
			if (!(function_exists('is_rtl') && is_rtl()))
				wp_register_style('w2dc_fsubmit', W2DC_FSUBMIT_RESOURCES_URL . 'css/submitlisting.css');
			else
				wp_register_style('w2dc_fsubmit', W2DC_FSUBMIT_RESOURCES_URL . 'css/submitlisting-rtl.css');
			wp_enqueue_style('w2dc_fsubmit');
			if (is_file(W2DC_FSUBMIT_RESOURCES_PATH . 'css/submitlisting-custom.css'))
				wp_register_style('w2dc_fsubmit-custom', W2DC_FSUBMIT_RESOURCES_URL . 'css/submitlisting-custom.css');
			
			wp_enqueue_style('w2dc_fsubmit-custom');

			$w2dc_fsubmit_enqueued = true;
		}
	}
	
	public function enqueue_login_scripts_styles() {
		global $action;
		$action = 'login';
		do_action('login_enqueue_scripts');
		do_action('login_head');
	}
}

function w2dc_install_fsubmit() {
	add_option('w2dc_fsubmit_default_status', 3);
	add_option('w2dc_fsubmit_login_mode', 1);

	w2dc_upgrade_fsubmit('1.5.0');
	w2dc_upgrade_fsubmit('1.5.4');
	w2dc_upgrade_fsubmit('1.6.2');
	w2dc_upgrade_fsubmit('1.8.3');
	w2dc_upgrade_fsubmit('1.8.4');
	w2dc_upgrade_fsubmit('1.9.0');
	w2dc_upgrade_fsubmit('1.9.7');
	w2dc_upgrade_fsubmit('1.10.0');
	
	add_option('w2dc_installed_fsubmit', 1);
}

function w2dc_upgrade_fsubmit($new_version) {
	if ($new_version == '1.5.0') {
		add_option('w2dc_fsubmit_edit_status', 3);
		add_option('w2dc_fsubmit_button', 1);
		add_option('w2dc_hide_admin_bar', 0);
		add_option('w2dc_newuser_notification', 'Hello [author],
your listing "[listing]" was successfully submitted.

You may manage your listing using following credentials:
login: [login]
password: [password]');
	}
	
	if ($new_version == '1.5.4')
		add_option('w2dc_allow_edit_profile', 1);

	if ($new_version == '1.6.2')
		add_option('w2dc_enable_frontend_translations', 1);

	if ($new_version == '1.8.3') {
		add_option('w2dc_claim_functionality', 0);
		add_option('w2dc_claim_approval', 1);
		add_option('w2dc_after_claim', 'active');
		add_option('w2dc_hide_claim_contact_form', 0);
		add_option('w2dc_claim_notification', 'Hello [author],
your listing "[listing]" was claimed by [claimer].

You may approve or reject this claim at
[link]

[message]');
		add_option('w2dc_claim_approval_notification', 'Hello [claimer],
congratulations, your claim for listing "[listing]" was successfully approved.

Now you may manage your listing at 
[link]');
		add_option('w2dc_newlisting_admin_notification', 'Hello,
new user [user] created new listing "[listing]".

You may manage this listing at
[link]');
	}
	
	if ($new_version == '1.8.4') {
		add_option('w2dc_enable_tags', 1);
	}

	if ($new_version == '1.9.0') {
		add_option('w2dc_tospage', '');
	}

	if ($new_version == '1.9.7') {
		add_option('w2dc_hide_claim_metabox', 0);
	}

	if ($new_version == '1.10.0') {
		add_option('w2dc_submit_login_page', '');
		add_option('w2dc_dashboard_login_page', '');
	}
}

function w2dc_submitUrl($path = '') {
	global $w2dc_instance;

	// adapted for WPML
	global $sitepress;
	if (function_exists('icl_object_id') && $sitepress) {
		if ($sitepress->get_option('language_negotiation_type') == 3) {
			// remove any previous value.
			$w2dc_instance->submit_page_url = remove_query_arg('lang', $w2dc_instance->submit_page_url);
		}
	}

	if (!is_array($path)) {
		if ($path) {
			// found that on some instances of WP "native" trailing slashes may be missing
			$url = rtrim($w2dc_instance->submit_page_url, '/') . '/' . rtrim($path, '/') . '/';
		} else
			$url = $w2dc_instance->submit_page_url;
	} else
		$url = add_query_arg($path, $w2dc_instance->submit_page_url);

	// adapted for WPML
	global $sitepress;
	if (function_exists('icl_object_id') && $sitepress) {
		$url = $sitepress->convert_url($url);
	}

	return $url;
}

function w2dc_dashboardUrl($path = '') {
	global $w2dc_instance;
	
	if ($w2dc_instance->dashboard_page_url) {
		// adapted for WPML
		global $sitepress;
		if (function_exists('icl_object_id') && $sitepress) {
			if ($sitepress->get_option('language_negotiation_type') == 3) {
				// remove any previous value.
				$w2dc_instance->dashboard_page_url = remove_query_arg('lang', $w2dc_instance->dashboard_page_url);
			}
		}
	
		if (!is_array($path)) {
			if ($path) {
				// found that on some instances of WP "native" trailing slashes may be missing
				$url = rtrim($w2dc_instance->dashboard_page_url, '/') . '/' . rtrim($path, '/') . '/';
			} else
				$url = $w2dc_instance->dashboard_page_url;
		} else
			$url = add_query_arg($path, $w2dc_instance->dashboard_page_url);
	
		// adapted for WPML
		global $sitepress;
		if (function_exists('icl_object_id') && $sitepress) {
			$url = $sitepress->convert_url($url);
		}
	} else
		$url = w2dc_directoryUrl();

	return $url;
}

function getPMPROlistingsNumberByLevel($pmpro_level_id, $w2dc_level_id) {
	$w2dc_pmpro_levels = get_option('w2dc_pmpro_levels');

	if (!isset($w2dc_pmpro_levels[$pmpro_level_id][$w2dc_level_id]))
		return __('unlimited', 'W2DC');
	else {
		if ($w2dc_pmpro_levels[$pmpro_level_id][$w2dc_level_id]['unlimited'])
			return __('unlimited', 'W2DC');
		elseif ($w2dc_pmpro_levels[$pmpro_level_id][$w2dc_level_id]['value'] == 0)
			return __('N/A', 'W2DC');
		else
			return $w2dc_pmpro_levels[$pmpro_level_id][$w2dc_level_id]['value'];
	}
}

global $w2dc_fsubmit_instance;

$w2dc_fsubmit_instance = new w2dc_fsubmit_plugin();
$w2dc_fsubmit_instance->init();

?>
