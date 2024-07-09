<?php

add_action('wp_ajax_nopriv_load_cities', 'belingogeo_load_cities');
add_action('wp_ajax_load_cities', 'belingogeo_load_cities');
function belingogeo_load_cities() {

	belingogeo_load_template('before_cities_list.php');
	$args = [
		'posts_per_page' => -1,
		'orderby' => 'title',
		'order' => 'ASC'
	];

	if(isset($_POST['q'])) {
		$q = sanitize_text_field($_POST['q']);
		$args['s'] = $q;
	}

	$args = apply_filters('belingogeo_load_cities_args', $args);

	$cities = belingoGeo_get_cities($args);
   	if($cities) {
        foreach ($cities as $city) {
        	$data['city'] = $city;
            belingogeo_load_template('city_in_list.php', $data);
        }
    }
    belingogeo_load_template('after_cities_list.php');

    wp_die();

}

add_action('wp_ajax_nopriv_show_city_question', 'show_city_question');
add_action('wp_ajax_show_city_question', 'show_city_question');
function show_city_question() {

	$result = [];

	if(!isset($_COOKIE['geo_city']) && !isset($_COOKIE['nogeo']) && !get_query_var('geo_city')) {

		$city = belingoGeo_check_city();

		$belingo_geo_basic_forced_confirmation_city = get_option('belingo_geo_basic_forced_confirmation_city');
		if($belingo_geo_basic_forced_confirmation_city) {

			if(isset($_POST['object_id'])) {
				$object_id = sanitize_text_field($_POST['object_id']);
			}

			if(isset($_POST['object'])) {
				$object = sanitize_text_field($_POST['object']);
			}

			if(isset($_POST['back_url'])) {
				$back_url = get_site_url().sanitize_url($_POST['back_url']);
			}

			belingogeo_save_geo_cookie($city->get_name(), $city->get_slug());
			$is_exclude = belingogeo_is_exclude($object_id, $object, $city->get_slug());
			$disable_urls = get_option('belingo_geo_basic_disable_url');
			if(isset($back_url)) {
				if(!$is_exclude && !$disable_urls) {
					$back_url = belingoGeo_append_city_url($back_url, $city->get_slug());
				}

				$back_url = apply_filters('belingogeo_backurl_in_ajax', $back_url, $is_exclude, $disable_urls, $city->get_slug());

				$result['redirect'] = $back_url;
			}
		}else{

			$data = [
				"city" 	   => $city
			];


			ob_start();
			$result = belingogeo_load_template('question_city.php', $data);
			$result['show_question'] = ob_get_contents();
			ob_end_clean();

		}
	}

	wp_send_json($result);

}

add_action('wp_ajax_nopriv_get_widget_city', 'belingoGeo_get_widget_city');
add_action('wp_ajax_get_widget_city', 'belingoGeo_get_widget_city');
function belingoGeo_get_widget_city() {

	$city = belingoGeo_get_current_city();

	if($city) {
		echo esc_html($city->get_name());
		wp_die();
	}

	if(isset($_COOKIE['nogeo_name']) && !isset($_COOKIE['nogeo'])) {
		echo esc_html($_COOKIE['nogeo_name']);
		wp_die();
	}

	$belingo_geo_basic_default_nonecity = get_option('belingo_geo_basic_default_nonecity');
	if(!empty($belingo_geo_basic_default_nonecity)) {
		$city = belingogeo_get_city_by('id', $belingo_geo_basic_default_nonecity[0]);
		if($city) {
			echo esc_html($city->get_name());
			wp_die();
		}
	}

	$belingo_geo_basic_default_text_nonecity = get_option('belingo_geo_basic_default_text_nonecity');
	if(!empty($belingo_geo_basic_default_text_nonecity)) {
		echo esc_html($belingo_geo_basic_default_text_nonecity);
	}else{
		echo esc_html('Not found', 'belingogeo'); // :()
	}

	wp_die();

}

add_action('wp_ajax_nopriv_write_city_cookie', 'belingoGeo_write_city_cookie');
add_action('wp_ajax_write_city_cookie', 'belingoGeo_write_city_cookie');
function belingoGeo_write_city_cookie() {

	$data = [
		'redirect' => '/'
	];

	if(isset($_POST['object_id'])) {
		$object_id = sanitize_text_field($_POST['object_id']);
	}

	if(isset($_POST['object'])) {
		$object = sanitize_text_field($_POST['object']);
	}

	if(isset($_POST['city_name'])) {
		$city_slug = sanitize_text_field($_POST['city_name']);
	}

	if(isset($_POST['city_name_orig'])) {
		$city_name = sanitize_text_field($_POST['city_name_orig']);
	}

	if(isset($_POST['back_url'])) {
		$back_url = get_site_url().sanitize_url($_POST['back_url']);
	}

	belingogeo_remove_nogeo_cookie();

	if(isset($city_slug) && isset($city_name)) {
		belingogeo_save_geo_cookie($city_name, $city_slug);
		$is_exclude = belingogeo_is_exclude($object_id, $object, $city_slug);
		$disable_urls = get_option('belingo_geo_basic_disable_url');
		if(isset($back_url)) {
			if(!$is_exclude && !$disable_urls) {
				$back_url = belingoGeo_append_city_url($back_url, $city_slug);
			}

			$back_url = apply_filters('belingogeo_backurl_in_ajax', $back_url, $is_exclude, $disable_urls, $city_slug);

			$data['redirect'] = $back_url;
		}
	}

	if (function_exists('wc_delete_product_transients')) {
    	wc_delete_product_transients();
	}

	wp_send_json($data);

}

add_action('wp_ajax_nopriv_write_nogeo_cookie', 'belingoGeo_write_nogeo_cookie');
add_action('wp_ajax_write_nogeo_cookie', 'belingoGeo_write_nogeo_cookie');
function belingoGeo_write_nogeo_cookie() {

	if(isset($_POST['back_url'])) {
		$back_url = get_site_url().sanitize_url($_POST['back_url']);
	}

	belingogeo_remove_geo_cookie();
	belingogeo_save_nogeo_cookie();

	$back_url = apply_filters('belingogeo_backurl_in_nogeo_ajax', $back_url);

	$data['redirect'] = $back_url;

	if (function_exists('wc_delete_product_transients')) {
    	wc_delete_product_transients();
	}

	wp_send_json($data);

}

?>