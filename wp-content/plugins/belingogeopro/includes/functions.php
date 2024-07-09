<?php

require_once ABSPATH . 'wp-admin/includes/plugin.php';

if(get_option('belingo_geo_url_type') == 'subdomain') {

	$city_from_host = belingoGeo_get_city_from_host();

	add_filter('site_url', 'belingogeopro_filter_domain_url', 10, 1);
	add_filter('home_url', 'belingogeopro_filter_domain_url', 10, 1);
	add_filter('admin_url', 'belingogeopro_filter_domain_url', 10, 1);
	add_filter('plugins_url', 'belingogeopro_filter_domain_url', 10, 1);
	add_filter('content_url', 'belingogeopro_filter_domain_url', 10, 1);
	add_filter('wpseo_stylesheet_url', 'belingogeopro_append_domain_city_in_yoast_sitemap', 10, 1);
	add_filter('style_loader_src', 'belingogeopro_filter_domain_url', 10, 1);
	add_filter('script_loader_src', 'belingogeopro_filter_domain_url', 10, 1);
	add_filter('the_content', 'belingogeopro_the_content_filter');
	add_filter('wp_get_attachment_url', 'belingogeopro_get_attachment_url_filter', 10, 2);
	add_filter('wpseo_opengraph_url', 'belingogeopro_wpseo_opengraph_urls');
	add_filter('wpseo_canonical', 'belingogeopro_wpseo_opengraph_urls');
	add_filter('wpseo_opengraph_image', 'belingogeopro_wpseo_opengraph_urls');
	add_filter('template_directory_uri', 'belingogeopro_filter_domain_url', 10, 1);
	//add_filter('wpseo_schema_graph', 'belingogeopro_wpseo_schema_graph_filter', 10, 2);
	add_filter('permalink_manager_filter_permalink_base', 'belingogeopro_filter_domain_url', 10, 1);

}

function belingogeopro_filter_domain_url($url) {
	return belingogeopro_append_domain_city($url);
}

function belingogeopro_the_content_filter($content) {
	return belingogeopro_append_domain_city($content);
}

function belingogeopro_get_attachment_url_filter($url, $attachment_id) {
	return belingogeopro_append_domain_city($url);
}

function belingogeopro_wpseo_opengraph_urls($u) {
	return belingogeopro_append_domain_city($u);
}

function belingogeopro_wpseo_schema_graph_filter($graph, $context){
	$flags = ( JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
	$graph = wp_json_encode($graph, $flags);
	$graph = belingogeopro_append_domain_city($graph);
	$graph = json_decode($graph);

	return $graph;
}

function belingogeopro_append_domain_city_in_yoast_sitemap($string) {
	global $city_from_host;
	$city_slug = $city_from_host;
	if($city_slug) {

		$protocol = 'http://';

		if(is_ssl()) {
			$protocol = 'https://';
		}

		$host = str_replace( ['www.',$protocol], '', get_option('siteurl') );
		$string = str_replace('//'.$host, '//'.$city_slug.'.'.$host, $string);
	}

	return $string;;

}

function belingogeopro_append_domain_city($content, $city_slug='') {
	global $city_from_host;
	if(empty($city_slug)) {
		$city_slug = $city_from_host;
	}

	if(preg_match('/\/\/([^\.]*)\./', $content, $matches)) {
		if(isset($matches[1])) {
			$city = belingogeo_get_city_by('slug', $matches[1]);
			if($city) {
				$content = belingogeopro_remove_domain_city($content, $city->get_slug());
			}
		}
	}

	if($city_slug) {

		$protocol = 'http://';

		if(is_ssl()) {
			$protocol = 'https://';
		}

		$host = str_replace( ['www.',$protocol], '', get_option('siteurl') );
		$content = str_replace('//'.$host, '//'.$city_slug.'.'.$host, $content);
	}

	return $content;

}

function belingogeopro_remove_domain_city($url, $city) {

	$charset = mb_detect_encoding($url);
	$url = iconv($charset, "UTF-8", $url);

	return str_replace($city.'.', '', $url);
	
}

add_filter('belingogeo_insert_city_url', 'belingogeopro_insert_city_url', 10, 2);
function belingogeopro_insert_city_url($url, $city) {

	if(get_option('belingo_geo_url_type') == 'subdomain') {
		$url = belingogeo_remove_city_url($url, $city);
		global $city_from_host;
		$current_city_slug = $city_from_host;
		if($current_city_slug && $current_city_slug != $city) {
			$url = belingogeopro_remove_domain_city($url, $current_city_slug);
		}

		$url = belingogeopro_append_domain_city($url, $city);
	}

	return $url;

}

add_filter('belingogeo_backurl_in_ajax', 'belingogeopro_backurl_in_ajax', 10, 4);
function belingogeopro_backurl_in_ajax($back_url, $is_exclude, $disable_urls, $city_slug) {

	if(get_option('belingo_geo_url_type') == 'subdomain') {
		if($is_exclude) {
			global $city_from_host;
			$current_city_slug = $city_from_host;
			if($current_city_slug) {
				$back_url = belingogeopro_remove_domain_city($back_url, $current_city_slug);
			}
		}
	}else{
		if($disable_urls) {

			$belingo_geo_basic_redirect_page = get_option('belingo_geo_basic_redirect_page');

			if($belingo_geo_basic_redirect_page) {
				$current_path = wp_parse_url($back_url);

				if(preg_match('/^\/([^\/]*)/', $current_path['path'], $matches)) {
					$city = belingogeo_get_city_by('slug', $matches[1]);
					if($city) {
						$current_path['path'] = str_replace('/'.$city->get_slug(), '', $current_path['path']);
					}
				}

				$page = get_page_by_path($city_slug.$current_path['path']);
				if($page) {
					$back_url = get_permalink($page->ID);
				}
			}
		}
	}

	return $back_url;

}

add_filter('belingogeo_backurl_in_nogeo_ajax', 'belingogeopro_backurl_in_nogeo_ajax', 10, 1);
function belingogeopro_backurl_in_nogeo_ajax($back_url) {

	if(get_option('belingo_geo_url_type') == 'subdomain') {
		global $city_from_host;
		$current_city_slug = $city_from_host;
		if($current_city_slug) {
			$back_url = belingogeopro_remove_domain_city($back_url, $current_city_slug);
		}
	}

	return $back_url;

}

add_filter('belingogeo_menu_settings_name', 'belingogeopro_menu_settings_pro', 10, 1);
add_filter('belingogeo_settings_version', 'belingogeopro_menu_settings_pro', 10, 1);
function belingogeopro_menu_settings_pro($value) {

	$value .= " Pro";

	return $value;

}

add_filter('belingo_geo_display_settings', 'belingopro_geo_display_settings', 10, 1);
function belingopro_geo_display_settings($args) {

	if(isset($args['is_pro']) && isset($args['disabled'])) {

		$args['is_pro'] = false;
		$args['disabled'] = false;

		if(isset($args['options']) && is_array($args['options'])) {
			foreach ($args['options'] as $key => $option) {
				if(isset($option['disabled']) && $option['disabled']) {
					$args['options'][$key]['disabled'] = false;
				}
			}
		}

	}

	return $args;

}

add_filter('belingogeo_allow_generate_links', 'belingogeopro_allow_generate_links', 10, 3);
function belingogeopro_allow_generate_links($allow, $url, $object) {

	if(get_option('belingo_geo_url_type') == 'subdomain') {
		$allow = false;
	}

	return $allow;

}

add_filter('belingogeo_allow_rewrite_rules', 'belingogeopro_allow_rewrite_rules', 10, 2);
function belingogeopro_allow_rewrite_rules($allow, $rules) {

	if(get_option('belingo_geo_url_type') == 'subdomain') {
		$allow = false;
	}

	return $allow;

}

add_filter('belingogeo_allow_city_url_redirect', 'belingogeopro_allow_city_url_redirect', 10, 4);
function belingogeopro_allow_city_url_redirect($allow, $disable_urls, $is_exclude, $request_uri) {

	if(get_option('belingo_geo_url_type') == 'subdomain') {
		$allow = false;
	}

	return $allow;

}

add_filter('belingogeo_allow_modify_yaost_sitemap', 'belingogeopro_allow_modify_yaost_sitemap', 10, 1);
function belingogeopro_allow_modify_yaost_sitemap($allow) {

	if(get_option('belingo_geo_url_type') == 'subdomain') {
		$allow = false;
	}

	return $allow;

}

add_action('belingogeo_before_init_cityobj', 'belingogeopro_before_init_cityobj', 10, 1);
function belingogeopro_before_init_cityobj($belingogeo_cityObj) {

	if(get_option('belingo_geo_url_type') == 'subdomain' && !is_login() && !is_admin() && !wp_is_json_request()) {
		global $city_from_host;
		$city = $city_from_host;
		if($city) {
			set_query_var('geo_city', $city);
		}else{
			if(isset($_COOKIE['geo_city'])) {
				belingogeopro_city_url_redirect();
			}
		}
	}

}

function belingogeopro_city_url_redirect() {

	$is_exclude = belingogeo_is_exclude();

	if(isset($_SERVER['REQUEST_URI'])) {
		$request_uri = sanitize_url($_SERVER['REQUEST_URI']);
	}else{
		$request_uri = '';
	}

	if(isset($_COOKIE['geo_city']) && !$is_exclude) {
		$city_url = belingoGeo_append_city_url(get_site_url().$request_uri, sanitize_text_field($_COOKIE['geo_city']));
		Header("Location: ".$city_url);
		exit;
	}

	//if(get_query_var('geo_city') && $is_exclude) {
	//	$url = belingogeo_remove_city_url($request_uri, get_query_var('geo_city'));
	//	Header("Location: ".$url);
	//	exit;
	//} 

}

function belingoGeo_get_city_from_host() {

	if(isset($_SERVER['HTTP_HOST'])) {
		$http_host = sanitize_url($_SERVER['HTTP_HOST']);
	}

	$protocol = 'http://';

	//if(is_ssl()) {
	//	$protocol = 'https://';
	//}

	$host = str_replace( ['www.',$protocol], '', $http_host );
	if(preg_match('/^([^\.]*)\./', $host, $matches)) {
		if(isset($matches[1])) {
			$city = belingogeo_get_city_by('slug', $matches[1]);
			if($city) {
				return $matches[1];
			}
		}
	}

	return false;

}

add_action('init', 'belingogeopro_init');
function belingogeopro_init() {

	if(get_option('belingo_geo_url_type') == 'subdomain') {
		global $city_from_host;

		if(isset($_SERVER['REQUEST_URI'])) {
			$request_uri = sanitize_url($_SERVER['REQUEST_URI']);
		}else{
			$request_uri = '';
		}

		if(preg_match('/sitemap.*\.xml/', $request_uri) && $city_from_host) {
			$default_city = belingogeo_get_default_city();
			$city = belingogeo_get_city_by('slug', $city_from_host);
			if($default_city && $city) {
				if($default_city->get_slug() == $city->get_slug()) {
					$url = belingogeopro_remove_domain_city(get_site_url().$request_uri, $city->get_slug());
					Header("Location: ".$url);
					exit;
				}
			}
		}
	}	

}

function belingogeopro_generate_links($url, $object) {

	if(is_object($object)) {
		$object_name = get_class($object);
		if($object_name == 'WP_Term') {
			$object_id = $object->term_id;
		}elseif($object_name == 'WP_Post') {
			$object_id = $object->ID;
		}
	}else{
		$object_name = 'WP_Post';
		$object_id = $object;
	}

	if(get_option('belingo_geo_url_type') == 'subdomain') {
		global $city_from_host;
		$current_city_slug = $city_from_host;

		if(isset($_SERVER['REQUEST_URI'])) {
			$request_uri = sanitize_url($_SERVER['REQUEST_URI']);
		}else{
			$request_uri = '';
		}
		
		$sitemap_city_slug = '';

		if(preg_match('/sitemap.*\.xml/', $request_uri)) {
			$sitemap_city_slug = $current_city_slug;
		}

		if(belingogeo_is_exclude($object_id, $object_name, $sitemap_city_slug)) {
			if($current_city_slug) {
				$url = belingogeopro_remove_domain_city($url, $current_city_slug);
			}
		}
	}

	return $url;

}
add_filter( 'page_link', 'belingogeopro_generate_links', 20, 2 );
add_filter( 'post_link', 'belingogeopro_generate_links', 20, 2 );
add_filter( 'term_link', 'belingogeopro_generate_links', 20, 2 );
add_filter( 'post_type_link', 'belingogeopro_generate_links', 20, 2 );

add_filter( 'woocommerce_form_field', 'belingogeopro_woocommerce_form_field_filter', 10, 4 );
function belingogeopro_woocommerce_form_field_filter( $field, $key, $args, $value ){

	$belingo_geo_basic_woo_auto_detect_city_checkout = get_option('belingo_geo_basic_woo_auto_detect_city_checkout');

	if($belingo_geo_basic_woo_auto_detect_city_checkout) {
		$city = belingoGeo_get_current_city();
		if($city) {
			if($key == 'billing_city') {
				$field = preg_replace('/value="[^"]*"/', 'value="'.$city->get_name().'"', $field);
			}
		}
	}

	return $field;

}

function belingogeopro_load_template($template, $data = []) {

	$template_dir_name = 'belingogeo';
	$template_dir_name = apply_filters( 'belingogeo_template_dir_name', $template_dir_name );

	if($overridden_template = locate_template($template_dir_name. '/'. $template)) {
	   require $overridden_template;
	}else{
	   require BELINGO_GEO_PRO_PLUGIN_DIR . '/templates/'. $template;
	}

}

add_action('belingogeo_before_cities_list_container', 'belingogeopro_search_in_popup_window');
function belingogeopro_search_in_popup_window() {

	if(get_option('belingo_geo_basic_enable_search_in_popup')) {
		belingogeopro_load_template('search-in-popup.php');
	}

}

function belingogeo_insert_city($args = array()) {

	$default_args = [
		'post_type'   => 'cities',
		'post_status' => 'publish'
	];

	$args = array_merge($default_args, $args);

	return wp_insert_post($args);

}

function belingogeopro_get_args($city) {

	$args = [];

	if(isset($city['city_slug'])) {
		$args['post_name'] = sanitize_text_field($city['city_slug']);
	}

	if(isset($city['city_name'])) {
		$args['post_title'] = sanitize_text_field($city['city_name']);
	}

	foreach($city as $key => $value) {
		if(!in_array($key, array('ID', 'city_name', 'city_slug', 'city_regions'))) {
			$args['meta_input'][$key] = $value;
		}
		if($key == 'city_regions' && !empty($value)) {
			$regions = explode(",", $value);
			$args['tax_input']['bg_regions'] = $regions;
		}
	}

	return $args;

}

function belingogeopro_import_func() {
	if(isset($_FILES['file'])) {
		$mimes = array('text/csv');
		if(in_array($_FILES['file']['type'], $mimes)) {
			$uploadfile = basename($_FILES['file']['name']);
			if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile)) {
				
				$data = [];

				if($fp = fopen($uploadfile, "r")) {
					$i = 0;
					while($row = fgetcsv($fp, 0, ";")) {
						if($i == 0) {
							$headers = [];
							foreach($row as $header) {
								$headers[] = sanitize_text_field(trim($header));
							}
						}else{
							$values = [];
							foreach($row as $key => $value) {
								$values[$headers[$key]] = $value;
							}
							$data[] = $values;
						}
						$i++;
					}
				}

				foreach($data as $city) {
					if(array_key_exists('ID', $city) && !empty($city['ID'])) {
						$cityObj = belingogeo_get_city_by('id', $city['ID']);
						if($cityObj) {
							$city_id = $cityObj->get_id();
							$args = [
					            'ID' => $city_id
					        ];
					        $args = array_merge($args, belingogeopro_get_args($city));
							wp_update_post($args);
					    }else{
					    	$args = belingogeopro_get_args($city);
					    	$city_id = belingogeo_insert_city($args);
					    }
					}else{
					    $city_id = belingogeo_insert_city( belingogeopro_get_args($city) );
					}
				}

				unlink($uploadfile);

				echo '<div class="notice notice-success is-dismissible"><p>'.__('Data uploaded successfully!', 'belingogeopro').'</p></div>';
			}
		}else{
			echo '<div class="notice notice-error is-dismissible"><p>'.__('Sorry! Only .csv files.', 'belingogeopro').'</p></div>';
		}
	}
	echo '<form method="post" action="" enctype="multipart/form-data">';
	echo '<input type="file" name="file">';
	echo '<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="'.__('Load cities', 'belingogeopro').'"></p>';
	echo '</form>';
}

function belingogeopro_export_func() {
	if(isset($_GET['export']) && $_GET['export'] == '1') {
		$args = [
			'posts_per_page' => -1
		];
		$cities = belingoGeo_get_cities($args);
		if(count($cities) > 0) {
			$fp = fopen('export.csv', 'w');
			$headers = ['ID', 'city_name', 'city_slug', 'city_regions'];
			$custom_headers = [];
			$all_meta_keys = belingogeo_get_all_meta_keys();
			if(count($all_meta_keys) > 0) {
				foreach($all_meta_keys as $meta) {
					if(!preg_match('/^_/', $meta['meta_key']) && $meta['meta_key'] != 'city_addon_contacts') {
						$headers[] = $meta['meta_key'];
						$custom_headers[] = $meta['meta_key'];
					}
				}
			}
			fputcsv($fp, $headers, ";");
			foreach($cities as $city) {
				$regions = wp_get_post_terms( $city->get_id(), 'bg_regions', array('fields' => 'names') );
				$fields = [
					$city->get_id(),
					$city->get_name(),
					$city->get_slug(),
					implode(",", $regions)
				];
				foreach($custom_headers as $custom_header) {
					if($custom_header != 'city_addon_contacts') {
						$fields[] = get_post_meta($city->get_id(), $custom_header, true);
					}
				}
				fputcsv($fp, $fields, ";");
			}
			fclose($fp);
			belingogeo_download_csv_file('export.csv');
			unlink('export.csv');
			exit;
		}
	}
	echo '<p><a class="button button-primary" href="admin.php?page=belingogeo_export&export=1">'.__('Export current cities').'</a></p>';
}

add_filter( 'woocommerce_page_title', 'belingogeopro_woocommerce_page_title' );
function belingogeopro_woocommerce_page_title( $page_title ){

	$belingo_geo_basic_add_city_to_woo_page_title = get_option('belingo_geo_basic_add_city_to_woo_page_title');
	if($belingo_geo_basic_add_city_to_woo_page_title && !empty($belingo_geo_basic_add_city_to_woo_page_title)) {
		$page_title .= ' ' . do_shortcode($belingo_geo_basic_add_city_to_woo_page_title);
	}

	return $page_title;
}

add_filter( 'seopress_titles_title', 'belingogeopro_hook_seo_meta' );
add_filter( 'seopress_titles_desc', 'belingogeopro_hook_seo_meta' );
add_filter( 'rank_math/frontend/title', 'belingogeopro_hook_seo_meta' );
add_filter( 'rank_math/frontend/description', 'belingogeopro_hook_seo_meta' );
function belingogeopro_hook_seo_meta($content) {

	return htmlentities(do_shortcode(html_entity_decode($content)));

}

$rank_math_modules = get_option('rank_math_modules');
if(in_array('sitemap', (array)$rank_math_modules) && is_plugin_active( 'seo-by-rank-math/rank-math.php' )) {
	add_filter( 'rank_math/sitemap/index', 'belingogeopro_rank_match_xml_index_item', 99 );
	function belingogeopro_rank_match_xml_index_item($rank_match_sitemaps) {

		$rank_match_sitemaps = belingogeo_modify_yaost_sitemap();

		return $rank_match_sitemaps;

	}

	add_filter( 'belingogeo_xml_sitemap_headers_string', 'belingogeopro_xml_sitemap_rank_match_headers_string');
	function belingogeopro_xml_sitemap_rank_match_headers_string($headers_string) {

		$headers_string = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
		$headers_string .= '<?xml-stylesheet type="text/xsl" href="/main-sitemap.xsl"?>';

		$headers_string .= '<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd http://www.google.com/schemas/sitemap-image/1.1 http://www.google.com/schemas/sitemap-image/1.1/sitemap-image.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

		return $headers_string;

	}
}

$seopress_xml_sitemap_option_name = get_option('seopress_xml_sitemap_option_name');
if(isset($seopress_xml_sitemap_option_name['seopress_xml_sitemap_general_enable']) && $seopress_xml_sitemap_option_name['seopress_xml_sitemap_general_enable'] == 1 && is_plugin_active( 'wp-seopress/seopress.php' )) {
	add_filter( 'seopress_sitemaps_xml_index_item', 'belingogeopro_seopress_xml_index_item' );
	function belingogeopro_seopress_xml_index_item($seopress_sitemaps) {

		$seopress_sitemaps .= belingogeo_modify_yaost_sitemap();

		return $seopress_sitemaps;

	}

	add_filter( 'belingogeo_xml_sitemap_headers_string', 'belingogeopro_xml_sitemap_seopress_headers_string');
	function belingogeopro_xml_sitemap_seopress_headers_string($headers_string) {

		$headers_string = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
		$headers_string .= '<?xml-stylesheet type="text/xsl" href="/sitemaps_xsl.xsl"?>';

		$headers_string .= '<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";

		return $headers_string;

	}
}

add_action( 'wp_enqueue_scripts', 'belingogeopro_scripts' );
function belingogeopro_scripts() {

	wp_enqueue_style('belingogeopro', BELINGO_GEO_PRO_PLUGIN_URL . '/css/belingogeopro.css', array(), BELINGO_GEO_PRO_VERSION);
	wp_enqueue_script('belingogeopro-scripts', BELINGO_GEO_PRO_PLUGIN_URL . '/js/belingogeopro.js', array('jquery'), BELINGO_GEO_PRO_VERSION, true);

}

add_action( 'admin_enqueue_scripts', 'belingogeopro_scripts_admin' );
function belingogeopro_scripts_admin() {
	wp_enqueue_script( 'belingogeopro-scripts-admin', BELINGO_GEO_PRO_PLUGIN_URL . '/js/belingogeoproadmin.js', array( 'jquery' ), BELINGO_GEO_PRO_VERSION, false );
}


?>