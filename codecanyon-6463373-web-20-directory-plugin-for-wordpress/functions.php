<?php 

if (!function_exists('w2dc_getValue')) {
	function w2dc_getValue($target, $key, $default = false) {
		$target = is_object($target) ? (array) $target : $target;
	
		if (is_array($target) && isset($target[$key]))
			return $target[$key];
	
		return $default;
	}
}

if (!function_exists('w2dc_addMessage')) {
	function w2dc_addMessage($message, $type = 'updated') {
		global $w2dc_messages;
	
		if (!isset($w2dc_messages[$type]) || (isset($w2dc_messages[$type]) && !in_array($message, $w2dc_messages[$type])))
			$w2dc_messages[$type][] = $message;
	
		if (session_id() == '')
			@session_start();
	
		if (!isset($_SESSION['w2dc_messages'][$type]) || (isset($_SESSION['w2dc_messages'][$type]) && !in_array($message, $_SESSION['w2dc_messages'][$type])))
			$_SESSION['w2dc_messages'][$type][] = $message;
	}
}

if (!function_exists('w2dc_renderMessages')) {
	function w2dc_renderMessages() {
		global $w2dc_messages;
	
		$messages = array();
		if (isset($w2dc_messages) && is_array($w2dc_messages) && $w2dc_messages)
			$messages = $w2dc_messages;
	
		if (session_id() == '')
			@session_start();
		if (isset($_SESSION['w2dc_messages']))
			$messages = array_merge($messages, $_SESSION['w2dc_messages']);
	
		$messages = w2dc_superUnique($messages);
	
		foreach ($messages AS $type=>$messages) {
			echo '<div class="' . $type . '">';
			foreach ($messages AS $message)
				echo '<p>' . $message . '</p>';
			echo '</div>';
		}
		
		$w2dc_messages = array();
		unset($_SESSION['w2dc_messages']);
	}
	function w2dc_superUnique($array) {
		$result = array_map("unserialize", array_unique(array_map("serialize", $array)));
		foreach ($result as $key => $value)
			if (is_array($value))
				$result[$key] = w2dc_superUnique($value);
		return $result;
	}
}

function w2dc_sumDates($date, $active_days, $active_months, $active_years)
{
	$date = strtotime('+'.$active_days.' day', $date);
	$date = strtotime('+'.$active_months.' month', $date);
	$date = strtotime('+'.$active_years.' year', $date);
	return $date;
}

if (!function_exists('w2dc_renderTemplate')) {
	function w2dc_renderTemplate($template, $args = array(), $return = false) {
		global $w2dc_instance;
	
		if ($args)
			extract($args);
		
		if (is_array($template)) {
			$plugin_template_path = $template[0];
			$template = $template[1];
		} else
			$plugin_template_path = W2DC_TEMPLATES_PATH;

		$core_theme_template_path = get_template_directory() . '/templates/' . $template;
		$core_child_theme_template_path = get_stylesheet_directory() . '/templates/' . $template;
		$core_template_path = $plugin_template_path . $template;

		// first of all check for this template in w2dc theme
		if (defined('W2DC_THEME_MODE') && (is_file($core_theme_template_path) || is_file($core_child_theme_template_path))) {
			if (is_file($core_child_theme_template_path))
				$template = $core_child_theme_template_path;
			else
				$template = $core_theme_template_path;
		} else {
			if (!is_file($template))
				if (!is_file($core_template_path))
					return false;
				else
					$template = $core_template_path;
		}

		$custom_template = str_replace('.tpl.php', '', $template) . '-custom.tpl.php';
		if (is_file($custom_template))
			$template = $custom_template;
	
		if ($return)
			ob_start();
	
		include($template);
		
		if ($return) {
			$output = ob_get_contents();
			ob_end_clean();
			return $output;
		}
	}
}

function w2dc_getCurrentListingInAdmin() {
	global $w2dc_instance;
	
	return $w2dc_instance->current_listing;
}

function w2dc_getIndexPage() {
	global $wpdb, $wp_rewrite;

	if (!($index_page = $wpdb->get_row("SELECT ID AS id, post_name AS slug FROM {$wpdb->posts} WHERE (post_content LIKE '%[" . W2DC_MAIN_SHORTCODE . "]%' OR post_content LIKE '%[" . W2DC_MAIN_SHORTCODE . " %') AND post_status = 'publish' AND post_type = 'page' LIMIT 1", ARRAY_A)))
		$index_page = array('slug' => '', 'id' => 0, 'url' => '');
	
	// adapted for WPML
	global $sitepress;
	if (function_exists('icl_object_id') && $sitepress) {
		if ($tpage = icl_object_id($index_page['id'], 'page')) {
			$index_page['id'] = $tpage;
			$index_page['slug'] = get_post($index_page['id'])->post_name;
		}
	}

	if ($index_page['id']) {
		if ($wp_rewrite->using_permalinks())
			$index_page['url'] = get_permalink($index_page['id']);
		else
			// found that on some instances of WP "native" trailing slashes may be missing
			$index_page['url'] = add_query_arg('page_id', $index_page['id'], home_url('/'));
	}
	
	return $index_page;
}

function w2dc_getListingPage() {
	global $wpdb, $wp_rewrite;

	if (!($listing_page = $wpdb->get_row("SELECT ID AS id, post_name AS slug FROM {$wpdb->posts} WHERE post_content LIKE '%[" . W2DC_LISTING_SHORTCODE . "]%' AND post_status = 'publish' AND post_type = 'page' LIMIT 1", ARRAY_A)))
		$listing_page = array('slug' => '', 'id' => 0, 'url' => '');
	
	// adapted for WPML
	global $sitepress;
	if (function_exists('icl_object_id') && $sitepress) {
		if ($tpage = icl_object_id($listing_page['id'], 'page')) {
			$listing_page['id'] = $tpage;
			$listing_page['slug'] = get_post($listing_page['id'])->post_name;
		}
	}

	if ($listing_page['id']) {
		if ($wp_rewrite->using_permalinks())
			$listing_page['url'] = get_permalink($listing_page['id']);
		else
			// found that on some instances of WP "native" trailing slashes may be missing
			$listing_page['url'] = add_query_arg('page_id', $listing_page['id'], home_url('/'));
	}

	return $listing_page;
}

function w2dc_directoryUrl($path = '') {
	global $w2dc_instance;
	
	// adapted for WPML
	global $sitepress;
	if (function_exists('icl_object_id') && $sitepress) {
		if ($sitepress->get_option('language_negotiation_type') == 3) {
			// remove any previous value.
			$w2dc_instance->index_page_url = remove_query_arg('lang', $w2dc_instance->index_page_url);
		}
	}

	if (!is_array($path)) {
		if ($path)
			$path = rtrim($path, '/') . '/';
		// found that on some instances of WP "native" trailing slashes may be missing
		$url = rtrim($w2dc_instance->index_page_url, '/') . '/' . $path;
	} else
		$url = add_query_arg($path, $w2dc_instance->index_page_url);

	// adapted for WPML
	global $sitepress;
	if (function_exists('icl_object_id') && $sitepress) {
		$url = $sitepress->convert_url($url);
	}
	
	return utf8_uri_encode($url);
}

function w2dc_ListingUrl($slug) {
	global $w2dc_instance;
	
	if ($w2dc_instance->listing_page_id)
		$listing_page_url = $w2dc_instance->listing_page_url;
	else
		$listing_page_url = $w2dc_instance->index_page_url;
	
	// adapted for WPML
	global $sitepress;
	if (function_exists('icl_object_id') && $sitepress) {
		if ($sitepress->get_option('language_negotiation_type') == 3) {
			// remove any previous value.
			$listing_page_url = remove_query_arg('lang', $listing_page_url);
		}
	}

	$url = add_query_arg(array('listing-w2dc' => $slug), $listing_page_url);

	// adapted for WPML
	global $sitepress;
	if (function_exists('icl_object_id') && $sitepress) {
		$url = $sitepress->convert_url($url);
	}
	
	return utf8_uri_encode($url);
}

function w2dc_get_term_parents($id, $tax, $link = false, $return_array = false, $separator = '/', &$chain = array()) {
	$parent = get_term($id, $tax);
	if (is_wp_error($parent) || !$parent)
		if ($return_array)
			return array();
		else 
			return '';

	$name = $parent->name;
	
	if ($parent->parent && ($parent->parent != $parent->term_id))
		w2dc_get_term_parents($parent->parent, $tax, $link, $return_array, $separator, $chain);
	
	if ($link)
		$chain[] = '<span itemprop="child" itemscope itemtype="http://data-vocabulary.org/Breadcrumb"><a href="' . get_term_link($parent->slug, $tax) . '" title="' . esc_attr(sprintf(__('View all listings in %s', 'W2DC'), $parent->name)) . '" itemprop="url"><span itemprop="title">' . $name . '</span></a></span>';
	else
		$chain[] = $name;
	
	if ($return_array)
		return $chain;
	else
		return implode($separator, $chain);
}

function w2dc_get_term_parents_slugs($id, $tax, &$chain = array()) {
	$parent = get_term($id, $tax);
	if (is_wp_error($parent) || !$parent)
		return '';

	$slug = $parent->slug;
	
	if ($parent->parent && ($parent->parent != $parent->term_id))
		w2dc_get_term_parents_slugs($parent->parent, $tax, $chain);

	$chain[] = $slug;

	return $chain;
}

function w2dc_get_term_parents_ids($id, $tax, &$chain = array()) {
	$parent = get_term($id, $tax);
	if (is_wp_error($parent) || !$parent)
		return '';

	$id = $parent->term_id;
	
	if ($parent->parent && ($parent->parent != $parent->term_id))
		w2dc_get_term_parents_ids($parent->parent, $tax, $chain);

	$chain[] = $id;

	return $chain;
}

function checkQuickList($is_listing_id = null)
{
	if (isset($_COOKIE['favourites']))
		$favourites = explode('*', $_COOKIE['favourites']);
	else
		$favourites = array();
	$favourites = array_values(array_filter($favourites));

	if ($is_listing_id)
		if (in_array($is_listing_id, $favourites))
			return true;
		else 
			return false;

	$favourites_array = array();
	foreach ($favourites AS $listing_id)
		if (is_numeric($listing_id))
		$favourites_array[] = $listing_id;
	return $favourites_array;
}

function getDatePickerFormat() {
	$wp_date_format = get_option('date_format');
	return str_replace(
			array('S',  'd', 'j',  'l',  'm', 'n',  'F',  'Y'),
			array('',  'dd', 'd', 'DD', 'mm', 'm', 'MM', 'yy'),
		$wp_date_format);
}

function w2dc_getDatePickerLangFile($locale) {
	if ($locale) {
		$locale = str_replace('_', '-', $locale);
		$lang_code = array_shift(explode('-', $locale));
		if (is_file(W2DC_RESOURCES_PATH . 'js/i18n/datepicker-'.$locale.'.js'))
			return W2DC_RESOURCES_URL . 'js/i18n/datepicker-'.$locale.'.js';
		elseif (is_file(W2DC_RESOURCES_PATH . 'js/i18n/datepicker-'.$lang_code.'.js'))
			return W2DC_RESOURCES_URL . 'js/i18n/datepicker-'.$lang_code.'.js';
	}
}

function w2dc_getDatePickerLangCode($locale) {
	if ($locale) {
		$locale = str_replace('_', '-', $locale);
		$lang_code = array_shift(explode('-', $locale));
		if (is_file(W2DC_RESOURCES_PATH . 'js/i18n/datepicker-'.$locale.'.js'))
			return $locale;
		elseif (is_file(W2DC_RESOURCES_PATH . 'js/i18n/datepicker-'.$lang_code.'.js'))
			return $lang_code;
	}
}

function generateRandomVal($val = null) {
	if (!$val)
		return rand(1, 10000);
	else
		return $val;
}

/**
 * Fetch the IP Address
 *
 * @return	string
 */
function ip_address()
{
	if (isset($_SERVER['REMOTE_ADDR']) && isset($_SERVER['HTTP_CLIENT_IP']))
		$ip_address = $_SERVER['HTTP_CLIENT_IP'];
	elseif (isset($_SERVER['REMOTE_ADDR']))
		$ip_address = $_SERVER['REMOTE_ADDR'];
	elseif (isset($_SERVER['HTTP_CLIENT_IP']))
		$ip_address = $_SERVER['HTTP_CLIENT_IP'];
	elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
		$ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
	else
		return false;

	if (strstr($ip_address, ',')) {
		$x = explode(',', $ip_address);
		$ip_address = trim(end($x));
	}

	$validation = new form_validation();
	if (!$validation->valid_ip($ip_address))
		return false;

	return $ip_address;
}

/**
 * Check if the device is a tablet.
 */
function w2dc_is_tablet($user_agent = null) {
	$tablet_devices = array(
			'iPad'              => 'iPad|iPad.*Mobile',
			'NexusTablet'       => '^.*Android.*Nexus(((?:(?!Mobile))|(?:(\s(7|10).+))).)*$',
			'SamsungTablet'     => 'SAMSUNG.*Tablet|Galaxy.*Tab|SC-01C|GT-P1000|GT-P1003|GT-P1010|GT-P3105|GT-P6210|GT-P6800|GT-P6810|GT-P7100|GT-P7300|GT-P7310|GT-P7500|GT-P7510|SCH-I800|SCH-I815|SCH-I905|SGH-I957|SGH-I987|SGH-T849|SGH-T859|SGH-T869|SPH-P100|GT-P3100|GT-P3108|GT-P3110|GT-P5100|GT-P5110|GT-P6200|GT-P7320|GT-P7511|GT-N8000|GT-P8510|SGH-I497|SPH-P500|SGH-T779|SCH-I705|SCH-I915|GT-N8013|GT-P3113|GT-P5113|GT-P8110|GT-N8010|GT-N8005|GT-N8020|GT-P1013|GT-P6201|GT-P7501|GT-N5100|GT-N5110|SHV-E140K|SHV-E140L|SHV-E140S|SHV-E150S|SHV-E230K|SHV-E230L|SHV-E230S|SHW-M180K|SHW-M180L|SHW-M180S|SHW-M180W|SHW-M300W|SHW-M305W|SHW-M380K|SHW-M380S|SHW-M380W|SHW-M430W|SHW-M480K|SHW-M480S|SHW-M480W|SHW-M485W|SHW-M486W|SHW-M500W|GT-I9228|SCH-P739|SCH-I925|GT-I9200|GT-I9205|GT-P5200|GT-P5210|SM-T311|SM-T310|SM-T210|SM-T210R|SM-T211|SM-P600|SM-P601|SM-P605|SM-P900|SM-T217|SM-T217A|SM-T217S|SM-P6000|SM-T3100|SGH-I467|XE500',
			// @reference: http://www.labnol.org/software/kindle-user-agent-string/20378/
			'Kindle'            => 'Kindle|Silk.*Accelerated|Android.*\b(KFOT|KFTT|KFJWI|KFJWA|KFOTE|KFSOWI|KFTHWI|KFTHWA|KFAPWI|KFAPWA|WFJWAE)\b',
			// Only the Surface tablets with Windows RT are considered mobile.
			// @ref: http://msdn.microsoft.com/en-us/library/ie/hh920767(v=vs.85).aspx
			'SurfaceTablet'     => 'Windows NT [0-9.]+; ARM;',
			// @ref: http://shopping1.hp.com/is-bin/INTERSHOP.enfinity/WFS/WW-USSMBPublicStore-Site/en_US/-/USD/ViewStandardCatalog-Browse?CatalogCategoryID=JfIQ7EN5lqMAAAEyDcJUDwMT
			'HPTablet'          => 'HP Slate 7|HP ElitePad 900|hp-tablet|EliteBook.*Touch',
			// @note: watch out for PadFone, see #132
			'AsusTablet'        => '^.*PadFone((?!Mobile).)*$|Transformer|TF101|TF101G|TF300T|TF300TG|TF300TL|TF700T|TF700KL|TF701T|TF810C|ME171|ME301T|ME302C|ME371MG|ME370T|ME372MG|ME172V|ME173X|ME400C|Slider SL101',
			'BlackBerryTablet'  => 'PlayBook|RIM Tablet',
			'HTCtablet'         => 'HTC Flyer|HTC Jetstream|HTC-P715a|HTC EVO View 4G|PG41200',
			'MotorolaTablet'    => 'xoom|sholest|MZ615|MZ605|MZ505|MZ601|MZ602|MZ603|MZ604|MZ606|MZ607|MZ608|MZ609|MZ615|MZ616|MZ617',
			'NookTablet'        => 'Android.*Nook|NookColor|nook browser|BNRV200|BNRV200A|BNTV250|BNTV250A|BNTV400|BNTV600|LogicPD Zoom2',
			// @ref: http://www.acer.ro/ac/ro/RO/content/drivers
			// @ref: http://www.packardbell.co.uk/pb/en/GB/content/download (Packard Bell is part of Acer)
			// @ref: http://us.acer.com/ac/en/US/content/group/tablets
			// @note: Can conflict with Micromax and Motorola phones codes.
			'AcerTablet'        => 'Android.*; \b(A100|A101|A110|A200|A210|A211|A500|A501|A510|A511|A700|A701|W500|W500P|W501|W501P|W510|W511|W700|G100|G100W|B1-A71|B1-710|B1-711|A1-810)\b|W3-810',
			// @ref: http://eu.computers.toshiba-europe.com/innovation/family/Tablets/1098744/banner_id/tablet_footerlink/
			// @ref: http://us.toshiba.com/tablets/tablet-finder
			// @ref: http://www.toshiba.co.jp/regza/tablet/
			'ToshibaTablet'     => 'Android.*(AT100|AT105|AT200|AT205|AT270|AT275|AT300|AT305|AT1S5|AT500|AT570|AT700|AT830)|TOSHIBA.*FOLIO',
			// @ref: http://www.nttdocomo.co.jp/english/service/developer/smart_phone/technical_info/spec/index.html
			'LGTablet'          => '\bL-06C|LG-V900|LG-V909\b',
			'FujitsuTablet'     => 'Android.*\b(F-01D|F-05E|F-10D|M532|Q572)\b',
			// Prestigio Tablets http://www.prestigio.com/support
			'PrestigioTablet'   => 'PMP3170B|PMP3270B|PMP3470B|PMP7170B|PMP3370B|PMP3570C|PMP5870C|PMP3670B|PMP5570C|PMP5770D|PMP3970B|PMP3870C|PMP5580C|PMP5880D|PMP5780D|PMP5588C|PMP7280C|PMP7280|PMP7880D|PMP5597D|PMP5597|PMP7100D|PER3464|PER3274|PER3574|PER3884|PER5274|PER5474|PMP5097CPRO|PMP5097|PMP7380D|PMP5297C|PMP5297C_QUAD',
			// @ref: http://support.lenovo.com/en_GB/downloads/default.page?#
			'LenovoTablet'      => 'IdeaTab|S2110|S6000|K3011|A3000|A1000|A2107|A2109|A1107',
			'YarvikTablet'      => 'Android.*(TAB210|TAB211|TAB224|TAB250|TAB260|TAB264|TAB310|TAB360|TAB364|TAB410|TAB411|TAB420|TAB424|TAB450|TAB460|TAB461|TAB464|TAB465|TAB467|TAB468)',
			'MedionTablet'      => 'Android.*\bOYO\b|LIFE.*(P9212|P9514|P9516|S9512)|LIFETAB',
			'ArnovaTablet'      => 'AN10G2|AN7bG3|AN7fG3|AN8G3|AN8cG3|AN7G3|AN9G3|AN7dG3|AN7dG3ST|AN7dG3ChildPad|AN10bG3|AN10bG3DT',
			// IRU.ru Tablets http://www.iru.ru/catalog/soho/planetable/
			'IRUTablet'         => 'M702pro',
			'MegafonTablet'     => 'MegaFon V9|\bZTE V9\b',
			// @ref: http://www.e-boda.ro/tablete-pc.html
			'EbodaTablet'       => 'E-Boda (Supreme|Impresspeed|Izzycomm|Essential)',
			// @ref: http://www.allview.ro/produse/droseries/lista-tablete-pc/
			'AllViewTablet'           => 'Allview.*(Viva|Alldro|City|Speed|All TV|Frenzy|Quasar|Shine|TX1|AX1|AX2)',
			// @reference: http://wiki.archosfans.com/index.php?title=Main_Page
			'ArchosTablet'      => '\b(101G9|80G9|A101IT)\b|Qilive 97R',
			// @ref: http://www.ainol.com/plugin.php?identifier=ainol&module=product
			'AinolTablet'       => 'NOVO7|NOVO8|NOVO10|Novo7Aurora|Novo7Basic|NOVO7PALADIN|novo9-Spark',
			// @todo: inspect http://esupport.sony.com/US/p/select-system.pl?DIRECTOR=DRIVER
			// @ref: Readers http://www.atsuhiro-me.net/ebook/sony-reader/sony-reader-web-browser
			// @ref: http://www.sony.jp/support/tablet/
			'SonyTablet'        => 'Sony.*Tablet|Xperia Tablet|Sony Tablet S|SO-03E|SGPT12|SGPT121|SGPT122|SGPT123|SGPT111|SGPT112|SGPT113|SGPT211|SGPT213|SGP311|SGP312|SGP321|EBRD1101|EBRD1102|EBRD1201',
			// @ref: db + http://www.cube-tablet.com/buy-products.html
			'CubeTablet'        => 'Android.*(K8GT|U9GT|U10GT|U16GT|U17GT|U18GT|U19GT|U20GT|U23GT|U30GT)|CUBE U8GT',
			// @ref: http://www.cobyusa.com/?p=pcat&pcat_id=3001
			'CobyTablet'        => 'MID1042|MID1045|MID1125|MID1126|MID7012|MID7014|MID7015|MID7034|MID7035|MID7036|MID7042|MID7048|MID7127|MID8042|MID8048|MID8127|MID9042|MID9740|MID9742|MID7022|MID7010',
			// @ref: http://www.match.net.cn/products.asp
			'MIDTablet'         => 'M9701|M9000|M9100|M806|M1052|M806|T703|MID701|MID713|MID710|MID727|MID760|MID830|MID728|MID933|MID125|MID810|MID732|MID120|MID930|MID800|MID731|MID900|MID100|MID820|MID735|MID980|MID130|MID833|MID737|MID960|MID135|MID860|MID736|MID140|MID930|MID835|MID733',
			// @ref: http://pdadb.net/index.php?m=pdalist&list=SMiT (NoName Chinese Tablets)
			// @ref: http://www.imp3.net/14/show.php?itemid=20454
			'SMiTTablet'        => 'Android.*(\bMID\b|MID-560|MTV-T1200|MTV-PND531|MTV-P1101|MTV-PND530)',
			// @ref: http://www.rock-chips.com/index.php?do=prod&pid=2
			'RockChipTablet'    => 'Android.*(RK2818|RK2808A|RK2918|RK3066)|RK2738|RK2808A',
			// @ref: http://www.fly-phone.com/devices/tablets/ ; http://www.fly-phone.com/service/
			'FlyTablet'         => 'IQ310|Fly Vision',
			// @ref: http://www.bqreaders.com/gb/tablets-prices-sale.html
			'bqTablet'          => 'bq.*(Elcano|Curie|Edison|Maxwell|Kepler|Pascal|Tesla|Hypatia|Platon|Newton|Livingstone|Cervantes|Avant)|Maxwell.*Lite|Maxwell.*Plus',
			// @ref: http://www.huaweidevice.com/worldwide/productFamily.do?method=index&directoryId=5011&treeId=3290
			// @ref: http://www.huaweidevice.com/worldwide/downloadCenter.do?method=index&directoryId=3372&treeId=0&tb=1&type=software (including legacy tablets)
			'HuaweiTablet'      => 'MediaPad|IDEOS S7|S7-201c|S7-202u|S7-101|S7-103|S7-104|S7-105|S7-106|S7-201|S7-Slim',
			// Nec or Medias Tab
			'NecTablet'         => '\bN-06D|\bN-08D',
			// Pantech Tablets: http://www.pantechusa.com/phones/
			'PantechTablet'     => 'Pantech.*P4100',
			// Broncho Tablets: http://www.broncho.cn/ (hard to find)
			'BronchoTablet'     => 'Broncho.*(N701|N708|N802|a710)',
			// @ref: http://versusuk.com/support.html
			'VersusTablet'      => 'TOUCHPAD.*[78910]|\bTOUCHTAB\b',
			// @ref: http://www.zync.in/index.php/our-products/tablet-phablets
			'ZyncTablet'        => 'z1000|Z99 2G|z99|z930|z999|z990|z909|Z919|z900',
			// @ref: http://www.positivoinformatica.com.br/www/pessoal/tablet-ypy/
			'PositivoTablet'    => 'TB07STA|TB10STA|TB07FTA|TB10FTA',
			// @ref: https://www.nabitablet.com/
			'NabiTablet'        => 'Android.*\bNabi',
			'KoboTablet'        => 'Kobo Touch|\bK080\b|\bVox\b Build|\bArc\b Build',
			// French Danew Tablets http://www.danew.com/produits-tablette.php
			'DanewTablet'       => 'DSlide.*\b(700|701R|702|703R|704|802|970|971|972|973|974|1010|1012)\b',
			// Texet Tablets and Readers http://www.texet.ru/tablet/
			'TexetTablet'       => 'NaviPad|TB-772A|TM-7045|TM-7055|TM-9750|TM-7016|TM-7024|TM-7026|TM-7041|TM-7043|TM-7047|TM-8041|TM-9741|TM-9747|TM-9748|TM-9751|TM-7022|TM-7021|TM-7020|TM-7011|TM-7010|TM-7023|TM-7025|TM-7037W|TM-7038W|TM-7027W|TM-9720|TM-9725|TM-9737W|TM-1020|TM-9738W|TM-9740|TM-9743W|TB-807A|TB-771A|TB-727A|TB-725A|TB-719A|TB-823A|TB-805A|TB-723A|TB-715A|TB-707A|TB-705A|TB-709A|TB-711A|TB-890HD|TB-880HD|TB-790HD|TB-780HD|TB-770HD|TB-721HD|TB-710HD|TB-434HD|TB-860HD|TB-840HD|TB-760HD|TB-750HD|TB-740HD|TB-730HD|TB-722HD|TB-720HD|TB-700HD|TB-500HD|TB-470HD|TB-431HD|TB-430HD|TB-506|TB-504|TB-446|TB-436|TB-416|TB-146SE|TB-126SE',
			// @note: Avoid detecting 'PLAYSTATION 3' as mobile.
			'PlaystationTablet' => 'Playstation.*(Portable|Vita)',
			// @ref: http://www.galapad.net/product.html
			'GalapadTablet'     => 'Android.*\bG1\b',
			// @ref: http://www.micromaxinfo.com/tablet/funbook
			'MicromaxTablet'    => 'Funbook|Micromax.*\b(P250|P560|P360|P362|P600|P300|P350|P500|P275)\b',
			// http://www.karbonnmobiles.com/products_tablet.php
			'KarbonnTablet'     => 'Android.*\b(A39|A37|A34|ST8|ST10|ST7|Smart Tab3|Smart Tab2)\b',
			// @ref: http://www.myallfine.com/Products.asp
			'AllFineTablet'     => 'Fine7 Genius|Fine7 Shine|Fine7 Air|Fine8 Style|Fine9 More|Fine10 Joy|Fine11 Wide',
			// @ref: http://www.proscanvideo.com/products-search.asp?itemClass=TABLET&itemnmbr=
			'PROSCANTablet'     => '\b(PEM63|PLT1023G|PLT1041|PLT1044|PLT1044G|PLT1091|PLT4311|PLT4311PL|PLT4315|PLT7030|PLT7033|PLT7033D|PLT7035|PLT7035D|PLT7044K|PLT7045K|PLT7045KB|PLT7071KG|PLT7072|PLT7223G|PLT7225G|PLT7777G|PLT7810K|PLT7849G|PLT7851G|PLT7852G|PLT8015|PLT8031|PLT8034|PLT8036|PLT8080K|PLT8082|PLT8088|PLT8223G|PLT8234G|PLT8235G|PLT8816K|PLT9011|PLT9045K|PLT9233G|PLT9735|PLT9760G|PLT9770G)\b',
			// @ref: http://www.yonesnav.com/products/products.php
			'YONESTablet' => 'BQ1078|BC1003|BC1077|RK9702|BC9730|BC9001|IT9001|BC7008|BC7010|BC708|BC728|BC7012|BC7030|BC7027|BC7026',
			// @ref: http://www.cjshowroom.com/eproducts.aspx?classcode=004001001
			// China manufacturer makes tablets for different small brands (eg. http://www.zeepad.net/index.html)
			'ChangJiaTablet'    => 'TPC7102|TPC7103|TPC7105|TPC7106|TPC7107|TPC7201|TPC7203|TPC7205|TPC7210|TPC7708|TPC7709|TPC7712|TPC7110|TPC8101|TPC8103|TPC8105|TPC8106|TPC8203|TPC8205|TPC8503|TPC9106|TPC9701|TPC97101|TPC97103|TPC97105|TPC97106|TPC97111|TPC97113|TPC97203|TPC97603|TPC97809|TPC97205|TPC10101|TPC10103|TPC10106|TPC10111|TPC10203|TPC10205|TPC10503',
			// @ref: http://www.gloryunion.cn/products.asp
			// @ref: http://www.allwinnertech.com/en/apply/mobile.html
			// @ref: http://www.ptcl.com.pk/pd_content.php?pd_id=284 (EVOTAB)
			// aka. Cute or Cool tablets. Not sure yet, must research to avoid collisions.
			'GUTablet'          => 'TX-A1301|TX-M9002|Q702', // A12R|D75A|D77|D79|R83|A95|A106C|R15|A75|A76|D71|D72|R71|R73|R77|D82|R85|D92|A97|D92|R91|A10F|A77F|W71F|A78F|W78F|W81F|A97F|W91F|W97F|R16G|C72|C73E|K72|K73|R96G
			// @ref: http://www.pointofview-online.com/showroom.php?shop_mode=product_listing&category_id=118
			'PointOfViewTablet' => 'TAB-P506|TAB-navi-7-3G-M|TAB-P517|TAB-P-527|TAB-P701|TAB-P703|TAB-P721|TAB-P731N|TAB-P741|TAB-P825|TAB-P905|TAB-P925|TAB-PR945|TAB-PL1015|TAB-P1025|TAB-PI1045|TAB-P1325|TAB-PROTAB[0-9]+|TAB-PROTAB25|TAB-PROTAB26|TAB-PROTAB27|TAB-PROTAB26XL|TAB-PROTAB2-IPS9|TAB-PROTAB30-IPS9|TAB-PROTAB25XXL|TAB-PROTAB26-IPS10|TAB-PROTAB30-IPS10',
			// @ref: http://www.overmax.pl/pl/katalog-produktow,p8/tablety,c14/
			// @todo: add more tests.
			'OvermaxTablet'     => 'OV-(SteelCore|NewBase|Basecore|Baseone|Exellen|Quattor|EduTab|Solution|ACTION|BasicTab|TeddyTab|MagicTab|Stream|TB-08|TB-09)',
			// @ref: http://hclmetablet.com/India/index.php
			'HCLTablet'         => 'HCL.*Tablet|Connect-3G-2.0|Connect-2G-2.0|ME Tablet U1|ME Tablet U2|ME Tablet G1|ME Tablet X1|ME Tablet Y2|ME Tablet Sync',
			// @ref: http://www.edigital.hu/Tablet_es_e-book_olvaso/Tablet-c18385.html
			'DPSTablet'         => 'DPS Dream 9|DPS Dual 7',
			// @ref: http://www.visture.com/index.asp
			'VistureTablet'     => 'V97 HD|i75 3G|Visture V4( HD)?|Visture V5( HD)?|Visture V10',
			// @ref: http://www.mijncresta.nl/tablet
			'CrestaTablets'     => 'CTP(-)?810|CTP(-)?818|CTP(-)?828|CTP(-)?838|CTP(-)?888|CTP(-)?978|CTP(-)?980|CTP(-)?987|CTP(-)?988|CTP(-)?989',
			// @ref: http://www.tesco.com/direct/hudl/
			'Hudl'              => 'Hudl HT7S3',
			// @ref: http://www.telstra.com.au/home-phone/thub-2/
			'TelstraTablet'     => 'T-Hub2',
			'GenericTablet'     => 'Android.*\b97D\b|Tablet(?!.*PC)|ViewPad7|BNTV250A|MID-WCDMA|LogicPD Zoom2|\bA7EB\b|CatNova8|A1_07|CT704|CT1002|\bM721\b|rk30sdk|\bEVOTAB\b|SmartTabII10|SmartTab10',
	);

	foreach ($tablet_devices as $regex) {
		$regex = str_replace('/', '\/', $regex);

		if ((bool) preg_match('/'.$regex.'/is', $user_agent)) {
			return true;
		}
	}
	return false;
}

function w2dc_crop_content($limit = 35, $strip_html = true, $has_link = true, $nofollow = false) {
	if (has_excerpt())
		$raw_content = get_the_excerpt();
	elseif (get_option('w2dc_cropped_content_as_excerpt') && get_post()->post_content !== '')
		$raw_content = get_the_content();
	else 
		return ;

	if ($strip_html) {
		$raw_content = strip_tags($raw_content);
		$pattern = get_shortcode_regex();
		// Remove shortcodes from excerpt
		$raw_content = preg_replace_callback("/$pattern/s", 'w2dc_remove_shortcodes', $raw_content);
	}
	$raw_content = apply_filters('the_content', $raw_content);
	$raw_content = str_replace(']]>', ']]&gt;', $raw_content);
	
	if (!$limit)
		return $raw_content;
	
	if ($has_link)
		$readmore = ' <a href="'.get_permalink(get_the_ID()).'" '.(($nofollow) ? 'rel="nofollow"' : '').'>&#91;...&#93;</a>';
	else
		$readmore = ' &#91;...&#93;';

	$content = explode(' ', $raw_content, $limit);
	if (count($content) >= $limit) {
		array_pop($content);
		$content = implode(" ", $content) . $readmore;
	} else
		$content = $raw_content;

	return $content;
}

// Remove shortcodes from excerpt
function w2dc_remove_shortcodes($m) {
	if (function_exists('su_cmpt') && su_cmpt() !== false)
	if ($m[2] == su_cmpt() . 'dropcap' || $m[2] == su_cmpt() . 'highlight' || $m[2] == su_cmpt() . 'tooltip')
		return $m[0];

	// allow [[foo]] syntax for escaping a tag
	if ($m[1] == '[' && $m[6] == ']')
		return substr($m[0], 1, -1);

	return $m[1] . $m[6];
}

function w2dc_is_anyone_in_taxonomy($tax) {
	global $wpdb;
	return $wpdb->get_var('SELECT COUNT(*) FROM ' . $wpdb->term_taxonomy . ' WHERE `taxonomy`="' . $tax . '"');
}

function w2dc_comments_open() {
	if (get_option('w2dc_listings_comments_mode') == 'enabled' || (get_option('w2dc_listings_comments_mode') == 'wp_settings' && comments_open()))
		return true;
	else 
		return false;
}

function w2dc_get_term_by_path($term_path, $full_match = true, $output = OBJECT) {
	$term_path = rawurlencode( urldecode( $term_path ) );
	$term_path = str_replace( '%2F', '/', $term_path );
	$term_path = str_replace( '%20', ' ', $term_path );

	global $wp_rewrite;
	if ($wp_rewrite->using_permalinks()) {
		$term_paths = '/' . trim( $term_path, '/' );
		$leaf_path  = sanitize_title( basename( $term_paths ) );
		$term_paths = explode( '/', $term_paths );
		$full_path = '';
		foreach ( (array) $term_paths as $pathdir )
			$full_path .= ( $pathdir != '' ? '/' : '' ) . sanitize_title( $pathdir );
	
		//$terms = get_terms( array(W2DC_CATEGORIES_TAX, W2DC_LOCATIONS_TAX, W2DC_TAGS_TAX), array('get' => 'all', 'slug' => $leaf_path) );
		$terms = array();
		if ($term = get_term_by('slug', $leaf_path, W2DC_CATEGORIES_TAX))
			$terms[] = $term;
		if ($term = get_term_by('slug', $leaf_path, W2DC_LOCATIONS_TAX))
			$terms[] = $term;
		if ($term = get_term_by('slug', $leaf_path, W2DC_TAGS_TAX))
			$terms[] = $term;
	
		if ( empty( $terms ) )
			return null;
	
		foreach ( $terms as $term ) {
			$path = '/' . $leaf_path;
			$curterm = $term;
			while ( ( $curterm->parent != 0 ) && ( $curterm->parent != $curterm->term_id ) ) {
				$curterm = get_term( $curterm->parent, $term->taxonomy );
				if ( is_wp_error( $curterm ) )
					return $curterm;
				$path = '/' . $curterm->slug . $path;
			}

			if ( $path == $full_path ) {
				$term = get_term( $term->term_id, $term->taxonomy, $output );
				_make_cat_compat( $term );
				return $term;
			}
		}
	
		// If full matching is not required, return the first cat that matches the leaf.
		if ( ! $full_match ) {
			$term = reset( $terms );
			$term = get_term( $term->term_id, $term->taxonomy, $output );
			_make_cat_compat( $term );
			return $term;
		}
	} else {
		if ($term = get_term_by('slug', $term_path, W2DC_CATEGORIES_TAX))
			return $term;
		if ($term = get_term_by('slug', $term_path, W2DC_LOCATIONS_TAX))
			return $term;
		if ($term = get_term_by('slug', $term_path, W2DC_TAGS_TAX))
			return $term;
	}

	return null;
}

function w2dc_get_fa_icons_names() {
	$icons[] = 'w2dc-fa-adjust';
	$icons[] = 'w2dc-fa-adn';
	$icons[] = 'w2dc-fa-align-center';
	$icons[] = 'w2dc-fa-align-justify';
	$icons[] = 'w2dc-fa-align-left';
	$icons[] = 'w2dc-fa-align-right';
	$icons[] = 'w2dc-fa-ambulance';
	$icons[] = 'w2dc-fa-anchor';
	$icons[] = 'w2dc-fa-android';
	$icons[] = 'w2dc-fa-angellist';
	$icons[] = 'w2dc-fa-angle-double-down';
	$icons[] = 'w2dc-fa-angle-double-left';
	$icons[] = 'w2dc-fa-angle-double-right';
	$icons[] = 'w2dc-fa-angle-double-up';
	$icons[] = 'w2dc-fa-angle-down';
	$icons[] = 'w2dc-fa-angle-left';
	$icons[] = 'w2dc-fa-angle-right';
	$icons[] = 'w2dc-fa-angle-up';
	$icons[] = 'w2dc-fa-apple';
	$icons[] = 'w2dc-fa-archive';
	$icons[] = 'w2dc-fa-area-chart';
	$icons[] = 'w2dc-fa-arrow-circle-down';
	$icons[] = 'w2dc-fa-arrow-circle-left';
	$icons[] = 'w2dc-fa-arrow-circle-o-down';
	$icons[] = 'w2dc-fa-arrow-circle-o-left';
	$icons[] = 'w2dc-fa-arrow-circle-o-right';
	$icons[] = 'w2dc-fa-arrow-circle-o-up';
	$icons[] = 'w2dc-fa-arrow-circle-right';
	$icons[] = 'w2dc-fa-arrow-circle-up';
	$icons[] = 'w2dc-fa-arrow-down';
	$icons[] = 'w2dc-fa-arrow-left';
	$icons[] = 'w2dc-fa-arrow-right';
	$icons[] = 'w2dc-fa-arrow-up';
	$icons[] = 'w2dc-fa-arrows';
	$icons[] = 'w2dc-fa-arrows-alt';
	$icons[] = 'w2dc-fa-arrows-h';
	$icons[] = 'w2dc-fa-arrows-v';
	$icons[] = 'w2dc-fa-asterisk';
	$icons[] = 'w2dc-fa-at';
	$icons[] = 'w2dc-fa-automobile';
	$icons[] = 'w2dc-fa-backward';
	$icons[] = 'w2dc-fa-ban';
	$icons[] = 'w2dc-fa-bank';
	$icons[] = 'w2dc-fa-bar-chart';
	$icons[] = 'w2dc-fa-bar-chart-o';
	$icons[] = 'w2dc-fa-barcode';
	$icons[] = 'w2dc-fa-bars';
	$icons[] = 'w2dc-fa-bed';
	$icons[] = 'w2dc-fa-beer';
	$icons[] = 'w2dc-fa-behance';
	$icons[] = 'w2dc-fa-behance-square';
	$icons[] = 'w2dc-fa-bell';
	$icons[] = 'w2dc-fa-bell-o';
	$icons[] = 'w2dc-fa-bell-slash';
	$icons[] = 'w2dc-fa-bell-slash-o';
	$icons[] = 'w2dc-fa-bicycle';
	$icons[] = 'w2dc-fa-binoculars';
	$icons[] = 'w2dc-fa-birthday-cake';
	$icons[] = 'w2dc-fa-bitbucket';
	$icons[] = 'w2dc-fa-bitbucket-square';
	$icons[] = 'w2dc-fa-bitcoin';
	$icons[] = 'w2dc-fa-bold';
	$icons[] = 'w2dc-fa-bolt';
	$icons[] = 'w2dc-fa-bomb';
	$icons[] = 'w2dc-fa-book';
	$icons[] = 'w2dc-fa-bookmark';
	$icons[] = 'w2dc-fa-bookmark-o';
	$icons[] = 'w2dc-fa-briefcase';
	$icons[] = 'w2dc-fa-btc';
	$icons[] = 'w2dc-fa-bug';
	$icons[] = 'w2dc-fa-building';
	$icons[] = 'w2dc-fa-building-o';
	$icons[] = 'w2dc-fa-bullhorn';
	$icons[] = 'w2dc-fa-bullseye';
	$icons[] = 'w2dc-fa-bus';
	$icons[] = 'w2dc-fa-buysellads';
	$icons[] = 'w2dc-fa-cab';
	$icons[] = 'w2dc-fa-calculator';
	$icons[] = 'w2dc-fa-calendar';
	$icons[] = 'w2dc-fa-calendar-o';
	$icons[] = 'w2dc-fa-camera';
	$icons[] = 'w2dc-fa-camera-retro';
	$icons[] = 'w2dc-fa-car';
	$icons[] = 'w2dc-fa-caret-down';
	$icons[] = 'w2dc-fa-caret-left';
	$icons[] = 'w2dc-fa-caret-right';
	$icons[] = 'w2dc-fa-caret-square-o-down';
	$icons[] = 'w2dc-fa-caret-square-o-left';
	$icons[] = 'w2dc-fa-caret-square-o-right';
	$icons[] = 'w2dc-fa-caret-square-o-up';
	$icons[] = 'w2dc-fa-caret-up';
	$icons[] = 'w2dc-fa-cart-arrow-down';
	$icons[] = 'w2dc-fa-cart-plus';
	$icons[] = 'w2dc-fa-cc';
	$icons[] = 'w2dc-fa-cc-amex';
	$icons[] = 'w2dc-fa-cc-discover';
	$icons[] = 'w2dc-fa-cc-mastercard';
	$icons[] = 'w2dc-fa-cc-paypal';
	$icons[] = 'w2dc-fa-cc-stripe';
	$icons[] = 'w2dc-fa-cc-visa';
	$icons[] = 'w2dc-fa-certificate';
	$icons[] = 'w2dc-fa-chain';
	$icons[] = 'w2dc-fa-chain-broken';
	$icons[] = 'w2dc-fa-check';
	$icons[] = 'w2dc-fa-check-circle';
	$icons[] = 'w2dc-fa-check-circle-o';
	$icons[] = 'w2dc-fa-check-square';
	$icons[] = 'w2dc-fa-check-square-o';
	$icons[] = 'w2dc-fa-chevron-circle-down';
	$icons[] = 'w2dc-fa-chevron-circle-left';
	$icons[] = 'w2dc-fa-chevron-circle-right';
	$icons[] = 'w2dc-fa-chevron-circle-up';
	$icons[] = 'w2dc-fa-chevron-down';
	$icons[] = 'w2dc-fa-chevron-left';
	$icons[] = 'w2dc-fa-chevron-right';
	$icons[] = 'w2dc-fa-chevron-up';
	$icons[] = 'w2dc-fa-child';
	$icons[] = 'w2dc-fa-circle';
	$icons[] = 'w2dc-fa-circle-o';
	$icons[] = 'w2dc-fa-circle-o-notch';
	$icons[] = 'w2dc-fa-circle-thin';
	$icons[] = 'w2dc-fa-clipboard';
	$icons[] = 'w2dc-fa-clock-o';
	$icons[] = 'w2dc-fa-close';
	$icons[] = 'w2dc-fa-cloud';
	$icons[] = 'w2dc-fa-cloud-download';
	$icons[] = 'w2dc-fa-cloud-upload';
	$icons[] = 'w2dc-fa-cny';
	$icons[] = 'w2dc-fa-code';
	$icons[] = 'w2dc-fa-code-fork';
	$icons[] = 'w2dc-fa-codepen';
	$icons[] = 'w2dc-fa-coffee';
	$icons[] = 'w2dc-fa-cog';
	$icons[] = 'w2dc-fa-cogs';
	$icons[] = 'w2dc-fa-columns';
	$icons[] = 'w2dc-fa-comment';
	$icons[] = 'w2dc-fa-comment-o';
	$icons[] = 'w2dc-fa-comments';
	$icons[] = 'w2dc-fa-comments-o';
	$icons[] = 'w2dc-fa-compass';
	$icons[] = 'w2dc-fa-compress';
	$icons[] = 'w2dc-fa-connectdevelop';
	$icons[] = 'w2dc-fa-copy';
	$icons[] = 'w2dc-fa-copyright';
	$icons[] = 'w2dc-fa-credit-card';
	$icons[] = 'w2dc-fa-crop';
	$icons[] = 'w2dc-fa-crosshairs';
	$icons[] = 'w2dc-fa-css3';
	$icons[] = 'w2dc-fa-cube';
	$icons[] = 'w2dc-fa-cubes';
	$icons[] = 'w2dc-fa-cut';
	$icons[] = 'w2dc-fa-cutlery';
	$icons[] = 'w2dc-fa-dashboard';
	$icons[] = 'w2dc-fa-dashcube';
	$icons[] = 'w2dc-fa-database';
	$icons[] = 'w2dc-fa-dedent';
	$icons[] = 'w2dc-fa-delicious';
	$icons[] = 'w2dc-fa-desktop';
	$icons[] = 'w2dc-fa-deviantart';
	$icons[] = 'w2dc-fa-diamond';
	$icons[] = 'w2dc-fa-digg';
	$icons[] = 'w2dc-fa-dollar';
	$icons[] = 'w2dc-fa-dot-circle-o';
	$icons[] = 'w2dc-fa-download';
	$icons[] = 'w2dc-fa-dribbble';
	$icons[] = 'w2dc-fa-dropbox';
	$icons[] = 'w2dc-fa-drupal';
	$icons[] = 'w2dc-fa-edit';
	$icons[] = 'w2dc-fa-eject';
	$icons[] = 'w2dc-fa-ellipsis-h';
	$icons[] = 'w2dc-fa-ellipsis-v';
	$icons[] = 'w2dc-fa-empire';
	$icons[] = 'w2dc-fa-envelope';
	$icons[] = 'w2dc-fa-envelope-o';
	$icons[] = 'w2dc-fa-envelope-square';
	$icons[] = 'w2dc-fa-eraser';
	$icons[] = 'w2dc-fa-eur';
	$icons[] = 'w2dc-fa-euro';
	$icons[] = 'w2dc-fa-exchange';
	$icons[] = 'w2dc-fa-exclamation';
	$icons[] = 'w2dc-fa-exclamation-circle';
	$icons[] = 'w2dc-fa-exclamation-triangle';
	$icons[] = 'w2dc-fa-expand';
	$icons[] = 'w2dc-fa-external-link';
	$icons[] = 'w2dc-fa-external-link-square';
	$icons[] = 'w2dc-fa-eye';
	$icons[] = 'w2dc-fa-eye-slash';
	$icons[] = 'w2dc-fa-eyedropper';
	$icons[] = 'w2dc-fa-facebook';
	$icons[] = 'w2dc-fa-facebook-f';
	$icons[] = 'w2dc-fa-facebook-official';
	$icons[] = 'w2dc-fa-facebook-square';
	$icons[] = 'w2dc-fa-fast-backward';
	$icons[] = 'w2dc-fa-fast-forward';
	$icons[] = 'w2dc-fa-fax';
	$icons[] = 'w2dc-fa-female';
	$icons[] = 'w2dc-fa-fighter-jet';
	$icons[] = 'w2dc-fa-file';
	$icons[] = 'w2dc-fa-file-archive-o';
	$icons[] = 'w2dc-fa-file-audio-o';
	$icons[] = 'w2dc-fa-file-code-o';
	$icons[] = 'w2dc-fa-file-excel-o';
	$icons[] = 'w2dc-fa-file-image-o';
	$icons[] = 'w2dc-fa-file-movie-o';
	$icons[] = 'w2dc-fa-file-o';
	$icons[] = 'w2dc-fa-file-pdf-o';
	$icons[] = 'w2dc-fa-file-photo-o';
	$icons[] = 'w2dc-fa-file-picture-o';
	$icons[] = 'w2dc-fa-file-powerpoint-o';
	$icons[] = 'w2dc-fa-file-sound-o';
	$icons[] = 'w2dc-fa-file-text';
	$icons[] = 'w2dc-fa-file-text-o';
	$icons[] = 'w2dc-fa-file-video-o';
	$icons[] = 'w2dc-fa-file-word-o';
	$icons[] = 'w2dc-fa-file-zip-o';
	$icons[] = 'w2dc-fa-files-o';
	$icons[] = 'w2dc-fa-film';
	$icons[] = 'w2dc-fa-filter';
	$icons[] = 'w2dc-fa-fire';
	$icons[] = 'w2dc-fa-fire-extinguisher';
	$icons[] = 'w2dc-fa-flag';
	$icons[] = 'w2dc-fa-flag-checkered';
	$icons[] = 'w2dc-fa-flag-o';
	$icons[] = 'w2dc-fa-flash';
	$icons[] = 'w2dc-fa-flask';
	$icons[] = 'w2dc-fa-flickr';
	$icons[] = 'w2dc-fa-floppy-o';
	$icons[] = 'w2dc-fa-folder';
	$icons[] = 'w2dc-fa-folder-o';
	$icons[] = 'w2dc-fa-folder-open';
	$icons[] = 'w2dc-fa-folder-open-o';
	$icons[] = 'w2dc-fa-font';
	$icons[] = 'w2dc-fa-forumbee';
	$icons[] = 'w2dc-fa-forward';
	$icons[] = 'w2dc-fa-foursquare';
	$icons[] = 'w2dc-fa-frown-o';
	$icons[] = 'w2dc-fa-futbol-o';
	$icons[] = 'w2dc-fa-gamepad';
	$icons[] = 'w2dc-fa-gavel';
	$icons[] = 'w2dc-fa-gbp';
	$icons[] = 'w2dc-fa-ge';
	$icons[] = 'w2dc-fa-gear';
	$icons[] = 'w2dc-fa-gears';
	$icons[] = 'w2dc-fa-genderless';
	$icons[] = 'w2dc-fa-gift';
	$icons[] = 'w2dc-fa-git';
	$icons[] = 'w2dc-fa-git-square';
	$icons[] = 'w2dc-fa-github';
	$icons[] = 'w2dc-fa-github-alt';
	$icons[] = 'w2dc-fa-github-square';
	$icons[] = 'w2dc-fa-gittip';
	$icons[] = 'w2dc-fa-glass';
	$icons[] = 'w2dc-fa-globe';
	$icons[] = 'w2dc-fa-google';
	$icons[] = 'w2dc-fa-google-plus';
	$icons[] = 'w2dc-fa-google-plus-square';
	$icons[] = 'w2dc-fa-google-wallet';
	$icons[] = 'w2dc-fa-graduation-cap';
	$icons[] = 'w2dc-fa-gratipay';
	$icons[] = 'w2dc-fa-group';
	$icons[] = 'w2dc-fa-h-square';
	$icons[] = 'w2dc-fa-hacker-news';
	$icons[] = 'w2dc-fa-hand-o-down';
	$icons[] = 'w2dc-fa-hand-o-left';
	$icons[] = 'w2dc-fa-hand-o-right';
	$icons[] = 'w2dc-fa-hand-o-up';
	$icons[] = 'w2dc-fa-hdd-o';
	$icons[] = 'w2dc-fa-header';
	$icons[] = 'w2dc-fa-headphones';
	$icons[] = 'w2dc-fa-heart';
	$icons[] = 'w2dc-fa-heart-o';
	$icons[] = 'w2dc-fa-heartbeat';
	$icons[] = 'w2dc-fa-history';
	$icons[] = 'w2dc-fa-home';
	$icons[] = 'w2dc-fa-hospital-o';
	$icons[] = 'w2dc-fa-hotel';
	$icons[] = 'w2dc-fa-html5';
	$icons[] = 'w2dc-fa-ils';
	$icons[] = 'w2dc-fa-image';
	$icons[] = 'w2dc-fa-inbox';
	$icons[] = 'w2dc-fa-indent';
	$icons[] = 'w2dc-fa-info';
	$icons[] = 'w2dc-fa-info-circle';
	$icons[] = 'w2dc-fa-inr';
	$icons[] = 'w2dc-fa-instagram';
	$icons[] = 'w2dc-fa-institution';
	$icons[] = 'w2dc-fa-ioxhost';
	$icons[] = 'w2dc-fa-italic';
	$icons[] = 'w2dc-fa-joomla';
	$icons[] = 'w2dc-fa-jpy';
	$icons[] = 'w2dc-fa-jsfiddle';
	$icons[] = 'w2dc-fa-key';
	$icons[] = 'w2dc-fa-keyboard-o';
	$icons[] = 'w2dc-fa-krw';
	$icons[] = 'w2dc-fa-language';
	$icons[] = 'w2dc-fa-laptop';
	$icons[] = 'w2dc-fa-lastfm';
	$icons[] = 'w2dc-fa-lastfm-square';
	$icons[] = 'w2dc-fa-leaf';
	$icons[] = 'w2dc-fa-leanpub';
	$icons[] = 'w2dc-fa-legal';
	$icons[] = 'w2dc-fa-lemon-o';
	$icons[] = 'w2dc-fa-level-down';
	$icons[] = 'w2dc-fa-level-up';
	$icons[] = 'w2dc-fa-life-bouy';
	$icons[] = 'w2dc-fa-life-ring';
	$icons[] = 'w2dc-fa-life-saver';
	$icons[] = 'w2dc-fa-lightbulb-o';
	$icons[] = 'w2dc-fa-line-chart';
	$icons[] = 'w2dc-fa-link';
	$icons[] = 'w2dc-fa-linkedin';
	$icons[] = 'w2dc-fa-linkedin-square';
	$icons[] = 'w2dc-fa-linux';
	$icons[] = 'w2dc-fa-list';
	$icons[] = 'w2dc-fa-list-alt';
	$icons[] = 'w2dc-fa-list-ol';
	$icons[] = 'w2dc-fa-list-ul';
	$icons[] = 'w2dc-fa-location-arrow';
	$icons[] = 'w2dc-fa-lock';
	$icons[] = 'w2dc-fa-long-arrow-down';
	$icons[] = 'w2dc-fa-long-arrow-left';
	$icons[] = 'w2dc-fa-long-arrow-right';
	$icons[] = 'w2dc-fa-long-arrow-up';
	$icons[] = 'w2dc-fa-magic';
	$icons[] = 'w2dc-fa-magnet';
	$icons[] = 'w2dc-fa-mail-forward';
	$icons[] = 'w2dc-fa-mail-reply';
	$icons[] = 'w2dc-fa-mail-reply-all';
	$icons[] = 'w2dc-fa-male';
	$icons[] = 'w2dc-fa-map-marker';
	$icons[] = 'w2dc-fa-mars';
	$icons[] = 'w2dc-fa-mars-double';
	$icons[] = 'w2dc-fa-mars-stroke';
	$icons[] = 'w2dc-fa-mars-stroke-h';
	$icons[] = 'w2dc-fa-mars-stroke-v';
	$icons[] = 'w2dc-fa-maxcdn';
	$icons[] = 'w2dc-fa-meanpath';
	$icons[] = 'w2dc-fa-medium';
	$icons[] = 'w2dc-fa-medkit';
	$icons[] = 'w2dc-fa-meh-o';
	$icons[] = 'w2dc-fa-mercury';
	$icons[] = 'w2dc-fa-microphone';
	$icons[] = 'w2dc-fa-microphone-slash';
	$icons[] = 'w2dc-fa-minus';
	$icons[] = 'w2dc-fa-minus-circle';
	$icons[] = 'w2dc-fa-minus-square';
	$icons[] = 'w2dc-fa-minus-square-o';
	$icons[] = 'w2dc-fa-mobile';
	$icons[] = 'w2dc-fa-mobile-phone';
	$icons[] = 'w2dc-fa-money';
	$icons[] = 'w2dc-fa-moon-o';
	$icons[] = 'w2dc-fa-mortar-board';
	$icons[] = 'w2dc-fa-motorcycle';
	$icons[] = 'w2dc-fa-music';
	$icons[] = 'w2dc-fa-navicon';
	$icons[] = 'w2dc-fa-neuter';
	$icons[] = 'w2dc-fa-newspaper-o';
	$icons[] = 'w2dc-fa-openid';
	$icons[] = 'w2dc-fa-outdent';
	$icons[] = 'w2dc-fa-pagelines';
	$icons[] = 'w2dc-fa-paint-brush';
	$icons[] = 'w2dc-fa-paper-plane';
	$icons[] = 'w2dc-fa-paper-plane-o';
	$icons[] = 'w2dc-fa-paperclip';
	$icons[] = 'w2dc-fa-paragraph';
	$icons[] = 'w2dc-fa-paste';
	$icons[] = 'w2dc-fa-pause';
	$icons[] = 'w2dc-fa-paw';
	$icons[] = 'w2dc-fa-paypal';
	$icons[] = 'w2dc-fa-pencil';
	$icons[] = 'w2dc-fa-pencil-square';
	$icons[] = 'w2dc-fa-pencil-square-o';
	$icons[] = 'w2dc-fa-phone';
	$icons[] = 'w2dc-fa-phone-square';
	$icons[] = 'w2dc-fa-photo';
	$icons[] = 'w2dc-fa-picture-o';
	$icons[] = 'w2dc-fa-pie-chart';
	$icons[] = 'w2dc-fa-pied-piper';
	$icons[] = 'w2dc-fa-pied-piper-alt';
	$icons[] = 'w2dc-fa-pinterest';
	$icons[] = 'w2dc-fa-pinterest-p';
	$icons[] = 'w2dc-fa-pinterest-square';
	$icons[] = 'w2dc-fa-plane';
	$icons[] = 'w2dc-fa-play';
	$icons[] = 'w2dc-fa-play-circle';
	$icons[] = 'w2dc-fa-play-circle-o';
	$icons[] = 'w2dc-fa-plug';
	$icons[] = 'w2dc-fa-plus';
	$icons[] = 'w2dc-fa-plus-circle';
	$icons[] = 'w2dc-fa-plus-square';
	$icons[] = 'w2dc-fa-plus-square-o';
	$icons[] = 'w2dc-fa-power-off';
	$icons[] = 'w2dc-fa-print';
	$icons[] = 'w2dc-fa-puzzle-piece';
	$icons[] = 'w2dc-fa-qq';
	$icons[] = 'w2dc-fa-qrcode';
	$icons[] = 'w2dc-fa-question';
	$icons[] = 'w2dc-fa-question-circle';
	$icons[] = 'w2dc-fa-quote-left';
	$icons[] = 'w2dc-fa-quote-right';
	$icons[] = 'w2dc-fa-ra';
	$icons[] = 'w2dc-fa-random';
	$icons[] = 'w2dc-fa-rebel';
	$icons[] = 'w2dc-fa-recycle';
	$icons[] = 'w2dc-fa-reddit';
	$icons[] = 'w2dc-fa-reddit-square';
	$icons[] = 'w2dc-fa-refresh';
	$icons[] = 'w2dc-fa-remove';
	$icons[] = 'w2dc-fa-renren';
	$icons[] = 'w2dc-fa-reorder';
	$icons[] = 'w2dc-fa-repeat';
	$icons[] = 'w2dc-fa-reply';
	$icons[] = 'w2dc-fa-reply-all';
	$icons[] = 'w2dc-fa-retweet';
	$icons[] = 'w2dc-fa-rmb';
	$icons[] = 'w2dc-fa-road';
	$icons[] = 'w2dc-fa-rocket';
	$icons[] = 'w2dc-fa-rotate-left';
	$icons[] = 'w2dc-fa-rotate-right';
	$icons[] = 'w2dc-fa-rouble';
	$icons[] = 'w2dc-fa-rss';
	$icons[] = 'w2dc-fa-rss-square';
	$icons[] = 'w2dc-fa-rub';
	$icons[] = 'w2dc-fa-ruble';
	$icons[] = 'w2dc-fa-rupee';
	$icons[] = 'w2dc-fa-save';
	$icons[] = 'w2dc-fa-scissors';
	$icons[] = 'w2dc-fa-search';
	$icons[] = 'w2dc-fa-search-minus';
	$icons[] = 'w2dc-fa-search-plus';
	$icons[] = 'w2dc-fa-sellsy';
	$icons[] = 'w2dc-fa-send';
	$icons[] = 'w2dc-fa-send-o';
	$icons[] = 'w2dc-fa-server';
	$icons[] = 'w2dc-fa-share';
	$icons[] = 'w2dc-fa-share-alt';
	$icons[] = 'w2dc-fa-share-alt-square';
	$icons[] = 'w2dc-fa-share-square';
	$icons[] = 'w2dc-fa-share-square-o';
	$icons[] = 'w2dc-fa-shekel';
	$icons[] = 'w2dc-fa-sheqel';
	$icons[] = 'w2dc-fa-shield';
	$icons[] = 'w2dc-fa-ship';
	$icons[] = 'w2dc-fa-shirtsinbulk';
	$icons[] = 'w2dc-fa-shopping-cart';
	$icons[] = 'w2dc-fa-sign-out';
	$icons[] = 'w2dc-fa-signal';
	$icons[] = 'w2dc-fa-simplybuilt';
	$icons[] = 'w2dc-fa-sitemap';
	$icons[] = 'w2dc-fa-skyatlas';
	$icons[] = 'w2dc-fa-skype';
	$icons[] = 'w2dc-fa-slack';
	$icons[] = 'w2dc-fa-sliders';
	$icons[] = 'w2dc-fa-slideshare';
	$icons[] = 'w2dc-fa-smile-o';
	$icons[] = 'w2dc-fa-soccer-ball-o';
	$icons[] = 'w2dc-fa-sort';
	$icons[] = 'w2dc-fa-sort-alpha-asc';
	$icons[] = 'w2dc-fa-sort-alpha-desc';
	$icons[] = 'w2dc-fa-sort-amount-asc';
	$icons[] = 'w2dc-fa-sort-amount-desc';
	$icons[] = 'w2dc-fa-sort-asc';
	$icons[] = 'w2dc-fa-sort-desc';
	$icons[] = 'w2dc-fa-sort-down';
	$icons[] = 'w2dc-fa-sort-numeric-asc';
	$icons[] = 'w2dc-fa-sort-numeric-desc';
	$icons[] = 'w2dc-fa-sort-up';
	$icons[] = 'w2dc-fa-soundcloud';
	$icons[] = 'w2dc-fa-space-shuttle';
	$icons[] = 'w2dc-fa-spinner';
	$icons[] = 'w2dc-fa-spoon';
	$icons[] = 'w2dc-fa-spotify';
	$icons[] = 'w2dc-fa-square';
	$icons[] = 'w2dc-fa-square-o';
	$icons[] = 'w2dc-fa-stack-exchange';
	$icons[] = 'w2dc-fa-stack-overflow';
	$icons[] = 'w2dc-fa-star';
	$icons[] = 'w2dc-fa-star-half';
	$icons[] = 'w2dc-fa-star-half-empty';
	$icons[] = 'w2dc-fa-star-half-full';
	$icons[] = 'w2dc-fa-star-half-o';
	$icons[] = 'w2dc-fa-star-o';
	$icons[] = 'w2dc-fa-steam';
	$icons[] = 'w2dc-fa-steam-square';
	$icons[] = 'w2dc-fa-step-backward';
	$icons[] = 'w2dc-fa-step-forward';
	$icons[] = 'w2dc-fa-stethoscope';
	$icons[] = 'w2dc-fa-stop';
	$icons[] = 'w2dc-fa-street-view';
	$icons[] = 'w2dc-fa-strikethrough';
	$icons[] = 'w2dc-fa-stumbleupon';
	$icons[] = 'w2dc-fa-stumbleupon-circle';
	$icons[] = 'w2dc-fa-subscript';
	$icons[] = 'w2dc-fa-subway';
	$icons[] = 'w2dc-fa-suitcase';
	$icons[] = 'w2dc-fa-sun-o';
	$icons[] = 'w2dc-fa-superscript';
	$icons[] = 'w2dc-fa-support';
	$icons[] = 'w2dc-fa-table';
	$icons[] = 'w2dc-fa-tablet';
	$icons[] = 'w2dc-fa-tachometer';
	$icons[] = 'w2dc-fa-tag';
	$icons[] = 'w2dc-fa-tags';
	$icons[] = 'w2dc-fa-tasks';
	$icons[] = 'w2dc-fa-taxi';
	$icons[] = 'w2dc-fa-tencent-weibo';
	$icons[] = 'w2dc-fa-terminal';
	$icons[] = 'w2dc-fa-text-height';
	$icons[] = 'w2dc-fa-text-width';
	$icons[] = 'w2dc-fa-th';
	$icons[] = 'w2dc-fa-th-large';
	$icons[] = 'w2dc-fa-th-list';
	$icons[] = 'w2dc-fa-thumb-tack';
	$icons[] = 'w2dc-fa-thumbs-down';
	$icons[] = 'w2dc-fa-thumbs-o-down';
	$icons[] = 'w2dc-fa-thumbs-o-up';
	$icons[] = 'w2dc-fa-thumbs-up';
	$icons[] = 'w2dc-fa-ticket';
	$icons[] = 'w2dc-fa-times';
	$icons[] = 'w2dc-fa-times-circle';
	$icons[] = 'w2dc-fa-times-circle-o';
	$icons[] = 'w2dc-fa-tint';
	$icons[] = 'w2dc-fa-toggle-down';
	$icons[] = 'w2dc-fa-toggle-left';
	$icons[] = 'w2dc-fa-toggle-off';
	$icons[] = 'w2dc-fa-toggle-on';
	$icons[] = 'w2dc-fa-toggle-right';
	$icons[] = 'w2dc-fa-toggle-up';
	$icons[] = 'w2dc-fa-train';
	$icons[] = 'w2dc-fa-transgender';
	$icons[] = 'w2dc-fa-transgender-alt';
	$icons[] = 'w2dc-fa-trash';
	$icons[] = 'w2dc-fa-trash-o';
	$icons[] = 'w2dc-fa-tree';
	$icons[] = 'w2dc-fa-trello';
	$icons[] = 'w2dc-fa-trophy';
	$icons[] = 'w2dc-fa-truck';
	$icons[] = 'w2dc-fa-try';
	$icons[] = 'w2dc-fa-tty';
	$icons[] = 'w2dc-fa-tumblr';
	$icons[] = 'w2dc-fa-tumblr-square';
	$icons[] = 'w2dc-fa-turkish-lira';
	$icons[] = 'w2dc-fa-twitch';
	$icons[] = 'w2dc-fa-twitter';
	$icons[] = 'w2dc-fa-twitter-square';
	$icons[] = 'w2dc-fa-umbrella';
	$icons[] = 'w2dc-fa-underline';
	$icons[] = 'w2dc-fa-undo';
	$icons[] = 'w2dc-fa-university';
	$icons[] = 'w2dc-fa-unlink';
	$icons[] = 'w2dc-fa-unlock';
	$icons[] = 'w2dc-fa-unlock-alt';
	$icons[] = 'w2dc-fa-unsorted';
	$icons[] = 'w2dc-fa-upload';
	$icons[] = 'w2dc-fa-usd';
	$icons[] = 'w2dc-fa-user';
	$icons[] = 'w2dc-fa-user-md';
	$icons[] = 'w2dc-fa-user-plus';
	$icons[] = 'w2dc-fa-user-secret';
	$icons[] = 'w2dc-fa-user-times';
	$icons[] = 'w2dc-fa-users';
	$icons[] = 'w2dc-fa-venus';
	$icons[] = 'w2dc-fa-venus-double';
	$icons[] = 'w2dc-fa-venus-mars';
	$icons[] = 'w2dc-fa-viacoin';
	$icons[] = 'w2dc-fa-video-camera';
	$icons[] = 'w2dc-fa-vimeo-square';
	$icons[] = 'w2dc-fa-vine';
	$icons[] = 'w2dc-fa-vk';
	$icons[] = 'w2dc-fa-volume-down';
	$icons[] = 'w2dc-fa-volume-off';
	$icons[] = 'w2dc-fa-volume-up';
	$icons[] = 'w2dc-fa-warning';
	$icons[] = 'w2dc-fa-wechat';
	$icons[] = 'w2dc-fa-weibo';
	$icons[] = 'w2dc-fa-weixin';
	$icons[] = 'w2dc-fa-whatsapp';
	$icons[] = 'w2dc-fa-wheelchair';
	$icons[] = 'w2dc-fa-wifi';
	$icons[] = 'w2dc-fa-windows';
	$icons[] = 'w2dc-fa-won';
	$icons[] = 'w2dc-fa-wordpress';
	$icons[] = 'w2dc-fa-wrench';
	$icons[] = 'w2dc-fa-xing';
	$icons[] = 'w2dc-fa-xing-square';
	$icons[] = 'w2dc-fa-yahoo';
	$icons[] = 'w2dc-fa-yen';
	$icons[] = 'w2dc-fa-youtube';	
	$icons[] = 'w2dc-fa-youtube-play';
	$icons[] = 'w2dc-fa-youtube-square';
	return $icons;
}

function w2dc_current_user_can_edit_listing($listing_id) {
	if (!current_user_can('edit_others_posts')) {
		$post = get_post($listing_id);
		$current_user = wp_get_current_user();
		if ($current_user->ID != $post->post_author)
			return false;
		if ($post->post_status == 'pending'  && !is_admin())
			return false;
	}
	return true;
}

function w2dc_get_edit_listing_link($listing_id, $context = 'display') {
	if (w2dc_current_user_can_edit_listing($listing_id)) {
		return apply_filters('w2dc_get_edit_listing_link', get_edit_post_link($listing_id, $context), $listing_id);
	}
}

function w2dc_show_edit_button($listing_id) {
	if (
		w2dc_current_user_can_edit_listing($listing_id)
		&&
		(
			(get_option('w2dc_fsubmit_addon') && isset($w2dc_instance->dashboard_page_url) && $w2dc_instance->dashboard_page_url)
			||
			(!get_option('w2dc_fsubmit_addon') || !isset($w2dc_instance->dashboard_page_url) || !$w2dc_instance->dashboard_page_url)
			||
			(get_option('w2dc_fsubmit_addon') && !get_option('w2dc_hide_admin_bar'))
		)
	)
		return true;
}

function w2dc_hex2rgba($color, $opacity = false) {
	$default = 'rgb(0,0,0)';

	//Return default if no color provided
	if(empty($color))
		return $default;

	//Sanitize $color if "#" is provided
	if ($color[0] == '#' ) {
		$color = substr( $color, 1 );
	}

	//Check if color has 6 or 3 characters and get values
	if (strlen($color) == 6) {
		$hex = array( $color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5] );
	} elseif ( strlen( $color ) == 3 ) {
		$hex = array( $color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2] );
	} else {
		return $default;
	}

	//Convert hexadec to rgb
	$rgb =  array_map('hexdec', $hex);

	//Check if opacity is set(rgba or rgb)
	if($opacity){
		if(abs($opacity) > 1)
			$opacity = 1.0;
		$output = 'rgba('.implode(",",$rgb).','.$opacity.')';
	} else {
		$output = 'rgb('.implode(",",$rgb).')';
	}

	//Return rgb(a) color string
	return $output;
}

?>