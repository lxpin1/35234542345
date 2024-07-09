<?php

function belingogeo_rewrite_sitemaps($cities) {

	$rules = [];

	foreach ($cities as $city) {
		$rules['^('.$city->get_slug().')_([^\.]*)\.sitemap([0-9]*)\.xml$'] = 'index.php?city_sitemap=$matches[1]&sitemap_type=$matches[2]&sitemap_page=$matches[3]';
		$rules['^('.$city->get_slug().')_sitemap_tax_([^\.]*)\.xml$'] = 'index.php?city_sitemap=$matches[1]&sitemap_type=$matches[2]&sitemap_tax=$matches[2]';
	}

	return $rules;

}

function belingogeo_rewrite_frontpage($cities) {

	$rules = [];

	foreach($cities as $city) {
		$show_on_front = get_option('show_on_front');
		if($show_on_front == 'page') {
			$page_on_front = get_option('page_on_front');
			$page = get_post($page_on_front);
			$rules['^'.$city->get_slug().'/?$'] = 'index.php?pagename='.$page->post_name.'&geo_city='.$city->get_slug();
		}else{
			$rules['^'.$city->get_slug().'/?$'] = 'index.php?geo_city='.$city->get_slug();
		}
	}

	return $rules;

}

function belingoGeo_city_url_redirect() {

	$disable_urls = get_option('belingo_geo_basic_disable_url');
	$is_exclude = belingogeo_is_exclude();

	if(isset($_SERVER['REQUEST_URI'])) {
		$request_uri = sanitize_url($_SERVER['REQUEST_URI']);
	}else{
		$request_uri = '';
	}

	$allow = true;
	$allow = apply_filters('belingogeo_allow_city_url_redirect', $allow, $disable_urls, $is_exclude, $request_uri);

	if(!$allow) {
		return;
	}

	if(get_query_var('city_sitemap')) {
		$is_exclude = true;
	}

	if(is_search()) {
		$is_exclude = true;
	}

	if(isset($_COOKIE['geo_city']) && !get_query_var('geo_city') && !$disable_urls && !$is_exclude) {
		$city_url = belingoGeo_append_city_url(get_site_url().$request_uri, sanitize_text_field($_COOKIE['geo_city']));
		Header("Location: ".$city_url);
		exit;
	}

	if(get_query_var('geo_city') && $is_exclude) {
		$url = belingogeo_remove_city_url($request_uri, get_query_var('geo_city'));
		Header("Location: ".$url);
		exit;
	} 

}

function belingoGeo_init_cityObj() {
	global $belingogeo_cityObj;

	do_action('belingogeo_before_init_cityobj', $belingogeo_cityObj);

	if(isset($_COOKIE['geo_city']) && !get_query_var('geo_city')) {
		$geo_city = sanitize_text_field($_COOKIE['geo_city']);	
	}
	if(!isset($_COOKIE['geo_city']) && get_query_var('geo_city')) {
		$geo_city = get_query_var('geo_city');
	}
	if(isset($_COOKIE['geo_city']) && get_query_var('geo_city')) {
		$geo_city = get_query_var('geo_city');
	}

	if(isset($geo_city)) {
		$belingogeo_cityObj = belingogeo_get_city_by('slug', $geo_city);
	}

}

function belingogeo_create_default_city() {

	$default_city = belingogeo_get_city_by('slug', 'default-city');
	if(!$default_city) {
		$city_data = array(
			'post_type'		=> 'cities',
			'post_title'    => 'Default',
			'post_status'   => 'publish',
			'post_name'		=> 'default-city'
		);
		$city_id = wp_insert_post( $city_data );
	}

}

function belingoGeo_init_city() {

	belingoGeo_init_cityObj();

	belingoGeo_city_url_redirect();

	if(get_query_var('geo_city') && !isset($_COOKIE['geo_city'])) {
		$city = belingoGeo_get_current_city();
		if($city) {
			belingogeo_remove_nogeo_cookie();
			belingogeo_save_geo_cookie($city->get_name(), $city->get_slug());
		}
	}

	if(get_query_var('city_sitemap')) {
		belingogeo_generate_sitemap();
	}

}

?>
