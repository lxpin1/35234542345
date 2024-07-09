<?php

function belingoGeo_append_city_url($url, $city) {

	if(preg_match('/\/'.$city.'\//', $url)) {
		return $url;
	}

	if(!empty($city)) {
		$url = belingogeo_insert_city_url($url, $city);
	}else{
		if(isset($_COOKIE['geo_city'])) {
			$city = sanitize_text_field($_COOKIE['geo_city']);
			$url = belingogeo_insert_city_url($url, $city);
		}
	}

	return $url;

}

function belingogeo_insert_city_url($url, $city) {

	$charset = mb_detect_encoding($url);
	$url = iconv($charset, "UTF-8", $url);

	$url = str_replace(get_site_url(),get_site_url().'/'.$city,$url);

	$url = apply_filters('belingogeo_insert_city_url', $url, $city);

	return $url;

}

function belingogeo_is_city_in_url($url) {

	$pathAr = explode("/", $url);
	if(is_array($pathAr)) {
		foreach($pathAr as $path) {
			if(!empty($path)) {
				$city = belingogeo_get_city_by('slug', $path);
				if($city) {
					return $city;
				}
			}
		}
	}

	return false;

}

function belingogeo_remove_city_url($url, $city) {

	$charset = mb_detect_encoding($url);
	$url = iconv($charset, "UTF-8", $url);

	if(preg_match('/^\/'.$city.'\//', $url)) {
		return str_replace('/'.$city,'',$url);
	}

	$siteurl = preg_replace('/\/$/', '', get_option('siteurl'));

	$protocol = 'http://';

	if(is_ssl()) {
		$protocol = 'https://';
	}

	$host = str_replace( ['www.',$protocol], '', $siteurl );

	$url = str_replace($host.'/'.$city, $host, $url);

	$url = apply_filters('belingogeo_remove_city_url', $url, $city);

	return $url;

}

function belingogeo_download_csv_file($file) {

	if ( function_exists( 'gc_enable' ) ) {
		gc_enable(); // phpcs:ignore PHPCompatibility.FunctionUse.NewFunctions.gc_enableFound
	}
	if ( function_exists( 'apache_setenv' ) ) {
		@apache_setenv( 'no-gzip', 1 ); // @codingStandardsIgnoreLine
	}
	@ini_set( 'zlib.output_compression', 'Off' ); // @codingStandardsIgnoreLine
	@ini_set( 'output_buffering', 'Off' ); // @codingStandardsIgnoreLine
	@ini_set( 'output_handler', '' ); // @codingStandardsIgnoreLine
	ignore_user_abort( true );
	if ( function_exists( 'set_time_limit' ) && false === strpos( ini_get( 'disable_functions' ), 'set_time_limit' ) && ! ini_get( 'safe_mode' ) ) { // phpcs:ignore PHPCompatibility.IniDirectives.RemovedIniDirectives.safe_modeDeprecatedRemoved
		@set_time_limit( 0 ); // @codingStandardsIgnoreLine
	}
	nocache_headers();
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=' . basename($file) );
	header( 'Pragma: no-cache' );
	header( 'Expires: 0' );
	readfile($file);

}

function belingogeo_get_all_meta_keys() {
    global $wpdb;

    $type = 'cities';

    $res = $wpdb->get_results($wpdb->prepare(
        "SELECT DISTINCT meta_key FROM {$wpdb->postmeta} WHERE post_id IN
        (SELECT ID FROM {$wpdb->posts} WHERE post_type = %s)", $type
    ), ARRAY_A);

    return $res;

}

function belingoGeo_get_cities($args = array()) {

	$cities = [];

	$default_args = [
		'post_type'   => 'cities',
		'post_status' => 'publish',
		'suppress_filters' => true
	];

	$args = array_merge($default_args, $args);

	$query = new WP_Query($args);
	if($query->have_posts()) {
		while($query->have_posts()) {
			$query->the_post();
			$city = new BelingoGeo_City(get_the_ID());
			if($city->get_slug() != 'default-city') {
				$cities[] = $city;
			}
		}
		wp_reset_postdata();
	}

	return $cities;

}

function belingogeo_remove_nogeo_cookie() {
	if(isset($_COOKIE['nogeo'])) {
		unset($_COOKIE['nogeo']);
		setcookie('nogeo', 1, time()-1209600, COOKIEPATH, COOKIE_DOMAIN, false);
	}
}

function belingogeo_save_geo_cookie($city_name, $city_slug) {
	setcookie('geo_city', $city_slug, time()+1209600, COOKIEPATH, COOKIE_DOMAIN, false);
	$city = belingogeo_get_city_by('slug', $city_name);
	if(!$city) {
		setcookie('nogeo_name', $city_name, time()+1209600, COOKIEPATH, COOKIE_DOMAIN, false);
	}
}

function belingogeo_remove_geo_cookie() {
	if(isset($_COOKIE['geo_city'])) {
		$city_name = sanitize_text_field($_COOKIE['geo_city']);
		unset($_COOKIE['geo_city']);
		setcookie('geo_city', $city_name, time()-1209600, COOKIEPATH, COOKIE_DOMAIN, false);
	}
}

function belingogeo_save_nogeo_cookie() {
	setcookie('nogeo', 1, time()+1209600, COOKIEPATH, COOKIE_DOMAIN, false);
}

function belingogeo_get_default_city() {

	$default_city_id = get_option('belingo_geo_basic_default_nonecity');
	if($default_city_id) {
		$city = belingogeo_get_city_by('id', $default_city_id[0]);
	}

	if(isset($city)) {
		return $city;
	}

	return false;

}

function belingogeo_get_city_by($by, $value) {

	if($by == 'id') {
		$city = get_post($value, ARRAY_A);
		if($city) {
			$city_id = $city['ID'];
		}
	}

	if($by == 'slug' || $by == 'title') {
		$args = [
			'post_type' 	 => 'cities',
			'post_status' 	 => 'publish',
			'posts_per_page' => 1,
			'suppress_filters' => true
		];
		if($by == 'slug') {
			$args['name'] = $value;
		}
		if($by == 'title') {
			$args['title'] = $value;
		}
		$city = new WP_Query($args);
		if(count($city->posts) > 0) {
			foreach($city->posts as $post_obj) {
				$city_id = $post_obj->ID;
			}
		}
	}

	if(isset($city_id)) {
		return new BelingoGeo_City($city_id);
	}

	return false;

}

function belingoGeo_getUserIP() {

	$client = '';
	$remote = '';
	$forward = '';

    if(isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
        $REMOTE_ADDR = sanitize_text_field($_SERVER["HTTP_CF_CONNECTING_IP"]);
        $HTTP_CLIENT_IP = sanitize_text_field($_SERVER["HTTP_CF_CONNECTING_IP"]);
    }

    if(isset($HTTP_CLIENT_IP)) {
    	$client  = $HTTP_CLIENT_IP;
    }else{
    	if(isset($_SERVER['HTTP_CLIENT_IP'])) {
    		$client = sanitize_text_field($_SERVER['HTTP_CLIENT_IP']);
    	}
    }

    if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    	$forward = sanitize_text_field($_SERVER['HTTP_X_FORWARDED_FOR']);
    }

    if(isset($REMOTE_ADDR)) {
    	$remote  = $REMOTE_ADDR;
    }else{
    	if(isset($_SERVER['REMOTE_ADDR'])) {
    		$remote = sanitize_text_field($_SERVER['REMOTE_ADDR']);
    	}
    }

    if(filter_var($client, FILTER_VALIDATE_IP))
    {
        $ip = $client;
    }
    elseif(filter_var($forward, FILTER_VALIDATE_IP))
    {
        $ip = $forward;
    }
    else
    {
        $ip = $remote;
    }

    return $ip;

}

function belingogeo_load_template($template, $data = []) {

	$template_dir_name = 'belingogeo';
	$template_dir_name = apply_filters( 'belingogeo_template_dir_name', $template_dir_name );

	if($overridden_template = locate_template($template_dir_name. '/'. $template)) {
	   require $overridden_template;
	}else{
	   require BELINGO_GEO_PLUGIN_DIR . '/templates/'. $template;
	}

}

function belingoGeo_check_city() {

	require WP_PLUGIN_DIR . '/belingogeo/vendor/SxGeo/SxGeo.php';

	$SxGeo = new SxGeo(WP_PLUGIN_DIR . '/belingogeo/vendor/SxGeo/SxGeoCity.dat', SXGEO_BATCH | SXGEO_MEMORY);

	$sg = $SxGeo->getCity(belingoGeo_getUserIP());
	
	if(isset($sg['city']['name_ru']) && !empty($sg['city']['name_ru'])) {
		$city = belingogeo_get_city_by('title', $sg['city']['name_ru']);
		if(!$city) {
			$belingo_geo_basic_finding_nonecity = get_option('belingo_geo_basic_finding_nonecity');
			if($belingo_geo_basic_finding_nonecity) {
				$city = new BelingoGeo_City();
				$city->set_name($sg['city']['name_ru']);
				$city->set_slug(strtolower($sg['city']['name_en']));
			}
		}
	}

	if(!isset($city) || !$city) {
		$city = belingogeo_get_default_city();
		if(!$city) {
			$city = new BelingoGeo_City();
			$city->set_name(__('None city'));
			$city->set_slug('');
		}
	}

	return $city;

}

function belingoGeo_is_custom_post_type( $post = NULL ) {

    $all_custom_post_types = get_post_types( array ( '_builtin' => FALSE ) );

    if ( empty ( $all_custom_post_types ) )
        return FALSE;

    $custom_types      = array_keys( $all_custom_post_types );
    $current_post_type = get_post_type( $post );

    if ( ! $current_post_type )
        return FALSE;

    return in_array( $current_post_type, $custom_types );

}

function belingogeo_check_disallow_rule($key) {

	$disallow_rules = [
		"wc-auth",
		"wc-api",
		"sitemap",
		"wp-json",
		"attachment",
		"feed",
		"embed",
		"trackback",
		"\.txt",
		"\.ico",
		"wp-app",
		"wp-register.php",
		"search",
		"author"
	];

	foreach($disallow_rules as $disallow_rule) {
		if(preg_match('/'.$disallow_rule.'/', $key)) {
			return false;
		}
	}

	return true;

}

function belingogeo_is_exclude($object_id = '', $object = '', $current_city = '') {

	if(empty($current_city)) {
		$city = belingoGeo_get_current_city();
	}else{
		$city = belingogeo_get_city_by('slug', $current_city);
	}

	if(!$city) {
		return true;
	}
	
	if($city && $city->get_slug() == 'default-city') {
		return true;
	}

	if(isset($_SERVER['REQUEST_URI'])) {
		$request_uri = sanitize_url($_SERVER['REQUEST_URI']);
	}else{
		$request_uri = '';
	}
	
	if(preg_match('/sitemap.*\.xml/', $request_uri) && get_option('belingo_geo_url_type') != 'subdomain') {
		return true;
	}

	$default_city = belingogeo_get_default_city();
	if($default_city && $city) {
		if($default_city->get_slug() == $city->get_slug()) {
			return true;
		}
	}

	if(empty($object_id)) {
		$object_id = get_queried_object_id();
	}

	if(empty($object)) {
		$object = get_queried_object();
	    if(is_object($object)) {
	    	$object = get_class($object);
	    }else{
	    	$object = false;
	    }
	}

	$pages_exclude = get_option('belingo_geo_exclude_pages');
	$posts_exclude = get_option('belingo_geo_exclude_posts');
	$terms_exclude = get_option('belingo_geo_exclude_terms');
	$exclude_nonobject = get_option('belingo_geo_exclude_nonobject');
	$exclude_taxonomies = get_option('belingo_geo_exclude_taxonomies');
	$exclude_tags = get_option('belingo_geo_exclude_tags');
	$exclude_post_types = get_option('belingo_geo_exclude_post_types');
	$belingo_geo_exclude_all_posts = get_option('belingo_geo_exclude_all_posts');

	$is_exclude = false;

	if($object == 'WP_Post_Type') {
		$post_type = get_queried_object()->name;
		if(array_key_exists($post_type, (array)$exclude_post_types)) {
			$is_exclude = true;
		}
	}

	if($object == 'WP_Taxonomy') {
		$taxonomy = get_queried_object()->name;
		if(array_key_exists($taxonomy, (array)$exclude_taxonomies)) {
			$is_exclude = true;
		}
	}

	if($object == 'WP_Post') {

		if(in_array($object_id, (array)$posts_exclude)) {
			$is_exclude = true;
		}

		if(in_array($object_id, (array)$pages_exclude)) {
			$is_exclude = true;
		}

		$post_terms = get_the_terms($object_id, 'category');
		if($post_terms) {
			foreach($post_terms as $term) {
				if(in_array($term->term_id, (array)$terms_exclude)) {
					$is_exclude = true;
				}
			}
		}

		$post_obj = get_post( $object_id );
		if($post_obj && $post_obj->post_type) {
			if(array_key_exists($post_obj->post_type, (array)$exclude_post_types)) {
				$is_exclude = true;
			}
		}

		if ( $post_obj->post_type == 'post' ) {
			if($belingo_geo_exclude_all_posts) {
				$is_exclude = true;
			}
		}

	}

	if($object == 'WP_Term') {

		if(in_array($object_id, (array)$terms_exclude)) {
			$is_exclude = true;
		}

		if(in_array($object_id, (array)$exclude_tags)) {
			$is_exclude = true;
		}

		$term = get_term($object_id);
		if($term && $term->taxonomy) {
			if(array_key_exists($term->taxonomy, (array)$exclude_taxonomies)) {
				$is_exclude = true;
			}
			if($belingo_geo_exclude_all_posts) {
				if($term->taxonomy == 'category' || $term->taxonomy == 'post_tag') {
					$is_exclude = true;
				}
			}
		}

	}

	if(empty($object) || !$object) {
		if($exclude_nonobject) {
			$is_exclude = true;
		}
	}

	return $is_exclude;

}

if(!function_exists('belingoGeo_get_current_city')) {
	function belingoGeo_get_current_city() {
		global $belingogeo_cityObj;

		if(!empty($belingogeo_cityObj)) {
			$city = $belingogeo_cityObj;
		}

		if(!isset($city)) {
			if(isset($_COOKIE['geo_city']) && !get_query_var('geo_city')) {
				$city = belingogeo_get_city_by('slug', sanitize_text_field($_COOKIE['geo_city']));
			}

			if(!isset($_COOKIE['geo_city']) && get_query_var('geo_city')) {
				$city = belingogeo_get_city_by('slug', get_query_var('geo_city'));
			}

			if(isset($_COOKIE['geo_city']) && get_query_var('geo_city')) {
				$city = belingogeo_get_city_by('slug', get_query_var('geo_city'));
			}
		}

		if(!isset($city)) {
			$city = belingogeo_get_default_city();
		}

		if(isset($city)) {
			return $city;
		}

		return false;

	}
}

?>