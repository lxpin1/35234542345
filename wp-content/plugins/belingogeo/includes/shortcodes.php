<?php

add_shortcode('belingogeo_city_field', 'belingogeo_city_field_shortcode');
function belingogeo_city_field_shortcode($atts) {

	$atts = shortcode_atts( [
		'field' => 'city_padej1',
	], $atts );

	$result = '';

	$city = belingoGeo_get_current_city();

	if(!$city) {
		$city = belingogeo_get_default_city();
	}

	if(!$city) {
		$city = belingoGeo_get_city_by('slug', 'default-city');
	}

	if($city) {
		if($atts['field'] == 'city_name') {
			$result = $city->get_name();
		}elseif($atts['field'] == 'city_slug') {
			$result = $city->get_slug();
		}else{
			$meta = $city->get_meta();
			if(isset($meta[$atts['field']])) {
				$result = $meta[$atts['field']][0];
			}
		}
	}
	
	return apply_filters( 'belingogeo_city_field', $result );

}

add_shortcode("belingogeo_city_content", "belingogeo_city_content_shortcode");
function belingogeo_city_content_shortcode($atts, $content) {

	$atts = shortcode_atts( [
		'city' 			=> '',
		'exclude'		=> 0
	], $atts );

	$exclude = (int)$atts['exclude'];

	if(!empty($atts['city'])) {
		$atts_city = explode(",", $atts['city']);
	}else{
		$atts_city = '';
	}

	$result = '';
	$show_content = false;

	$current_city = belingoGeo_get_current_city();
	$default_city = belingogeo_get_default_city();

	if($current_city) {
		if(is_array($atts_city)) {
			if($exclude == 0) {
				if(in_array($current_city->get_slug(), $atts_city)) {
					$show_content = true;
				}
			}else{
				if(!in_array($current_city->get_slug(), $atts_city)) {
					$show_content = true;
				}
			}
		}else{
			if($exclude == 1 || ($default_city && $default_city->get_slug() == $current_city->get_slug())) {
				$show_content = true;
			}
		}
	}elseif(!$current_city && !is_array($atts_city)) {
		if($exclude == 0) {
			$show_content = true;
		}
	}

	if($show_content) {
		$result = do_shortcode($content);
	}

	return apply_filters( 'belingogeo_city_content', $result );

}

add_shortcode("belingogeo_select_city", "belingoGeo_select_city_shortcode");
function belingoGeo_select_city_shortcode($atts) {

	$atts = shortcode_atts( [
		'show' => '',
	], $atts );

	if(!empty($atts['show'])) {
		if($atts['show'] == 'mobile' && !wp_is_mobile()) {
			return;
		}
		if($atts['show'] == 'desktop' && wp_is_mobile()) {
			return;
		}
	}

	$data = [];

	ob_start();
	belingogeo_load_template('select_city.php', $data);
	$content = ob_get_contents();
	ob_end_clean();
	return $content;

}

add_shortcode("belingogeo_popup_select_city", "belingoGeo_popup_select_city_shortcode");
function belingoGeo_popup_select_city_shortcode($atts) {

	$data = [];

	$data['header'] = get_option('belingo_geo_basic_popup_window_header');
	$data['text1'] = get_option('belingo_geo_basic_popup_window_text1');
	$data['text2'] = get_option('belingo_geo_basic_popup_window_text2');

	ob_start();
	belingogeo_load_template('popup_select_city.php', $data);
	$content = ob_get_contents();
	ob_end_clean();
	return $content;

}

add_shortcode("belingogeo_selector", "belingoGeo_selector_shortcode");
function belingoGeo_selector_shortcode($atts) {

	$atts = shortcode_atts( [
		'show_question' => '',
	], $atts );

	$data = [
		'show_question' => true
	];

	if($atts['show_question'] == 'false') {
		$data['show_question'] = false;
	}

	ob_start();
	belingogeo_load_template('selector.php', $data);
	$content = ob_get_contents();
	ob_end_clean();
	return $content;

}

add_shortcode('belingogeo_region_field', 'belingogeo_region_field_shortcode');
function belingogeo_region_field_shortcode($atts) {

	$atts = shortcode_atts( [
		'field' => 'bg_regions_phone',
	], $atts );

	$result = '';

	$city = belingoGeo_get_current_city();

	if(!$city) {
		$city = belingogeo_get_default_city();
	}

	if(!$city) {
		$city = belingoGeo_get_city_by('slug', 'default-city');
	}

	if($city) {
		$terms = wp_get_post_terms( $city->get_id(), 'bg_regions', array('fields' => 'ids') );
		if($terms && is_array($terms) && count($terms)>0) {
			$region = get_term($terms[0]);
			if($atts['field'] == 'bg_regions_name') {
				$result = $region->name;
			}elseif($atts['field'] == 'bg_regions_slug') {
				$result = $region->slug;
			}else{
				$meta = get_term_meta($region->term_id, $atts['field'], true);
				if($meta && !empty($meta)) {
					$result = $meta;
				}
			}
		}
	}
	
	return apply_filters( 'belingogeo_region_field', $result );

}

add_shortcode("belingogeo_region_content", "belingogeo_region_content_shortcode");
function belingogeo_region_content_shortcode($atts, $content) {

	$atts = shortcode_atts( [
		'region' 			=> '',
		'exclude'		=> 0
	], $atts );

	$exclude = (int)$atts['exclude'];

	if(!empty($atts['region'])) {
		$atts_region = explode(",", $atts['region']);
	}else{
		$atts_region = '';
	}

	$result = '';
	$show_content = false;

	$current_city = belingoGeo_get_current_city();
	$default_city = belingogeo_get_default_city();

	if($current_city) {
		$terms = wp_get_post_terms( $current_city->get_id(), 'bg_regions', array('fields' => 'ids') );
		if($terms && is_array($terms) && count($terms)>0) {
			$region = get_term($terms[0]);
			if(is_array($atts_region)) {
				if($exclude == 0) {
					if(in_array($region->slug, $atts_region)) {
						$show_content = true;
					}
				}else{
					if(!in_array($region->slug, $atts_region)) {
						$show_content = true;
					}
				}
			}else{
				if($exclude == 1 || ($default_city && $default_city->get_slug() == $current_city->get_slug())) {
					$show_content = true;
				}
			}
		}else{
			if($exclude == 1 && is_array($atts_region)) {
				$show_content = true;
			}
		}
	}elseif(!$current_city && !is_array($atts_region)) {
		if($exclude == 0) {
			$show_content = true;
		}
	}

	if($show_content) {
		$result = do_shortcode($content);
	}

	return apply_filters( 'belingogeo_region_content', $result );

}

/**
*
* Next are deprecated shortcodes, they will be removed soon
*
*/

add_shortcode("city", "belingoGeo_city_shortcode");
function belingoGeo_city_shortcode() {
	return do_shortcode('[belingogeo_city_field field="city_name"]');
}

add_shortcode("widget_city", "belingoGeo_widget_city_shortcode");
function belingoGeo_widget_city_shortcode() {
	return do_shortcode('[belingogeo_city_field field="city_name"]');
}

add_shortcode("city_field", "belingoGeo_city_field_shortcode_deprecated");
function belingoGeo_city_field_shortcode_deprecated($atts) {
	return belingogeo_city_field_shortcode($atts);
}

add_shortcode("city_content", "belingoGeo_city_content_shortcode_deprecated");
function belingoGeo_city_content_shortcode_deprecated($atts, $content) {
	return belingogeo_city_content_shortcode($atts, $content);
}

add_shortcode("city_padej1", "belingoGeo_city_padej1_shortcode");
function belingoGeo_city_padej1_shortcode() {
	return do_shortcode('[belingogeo_city_field field="city_padej1"]');
}

add_shortcode("city_padej2", "belingoGeo_city_padej2_shortcode");
function belingoGeo_city_padej2_shortcode() {
	return do_shortcode('[belingogeo_city_field field="city_padej2"]');
}

add_shortcode("city_padej3", "belingoGeo_city_padej3_shortcode");
function belingoGeo_city_padej3_shortcode() {
	return do_shortcode('[belingogeo_city_field field="city_padej3"]');
}

add_shortcode("city_phone", "belingoGeo_city_phone_shortcode");
function belingoGeo_city_phone_shortcode() {
	return do_shortcode('[belingogeo_city_field field="city_phone"]');
}

add_shortcode("city_address", "belingoGeo_city_address_shortcode");
function belingoGeo_city_address_shortcode() {
	return do_shortcode('[belingogeo_city_field field="city_address"]');
}

add_shortcode("cities_addon_contacts", "belingoGeo_cities_addon_contacts_shortcode");
function belingoGeo_cities_addon_contacts_shortcode() {
	
	$city = belingoGeo_get_current_city();

	if($city) {
		$city_addon_contacts = json_decode(get_post_meta($city->get_id(),'city_addon_contacts',true));

		foreach ($city_addon_contacts as $key => $value) {
			$result[] = [
				"addon_contact_name" => base64_decode($value->addon_contact_name),
				"addon_contact_phone" => base64_decode($value->addon_contact_phone),
				"addon_contact_address" => base64_decode($value->addon_contact_address),
				"addon_contact_time" => base64_decode($value->addon_contact_time)
			];
		}

		ob_start();

		if(file_exists(get_template_directory() . '/belingogeo/cities_addon_contacts.php')) {
			include_once( get_template_directory() . '/belingogeo/cities_addon_contacts.php' );
		}else{
			include_once( WP_PLUGIN_DIR . '/belingogeo/templates/cities_addon_contacts.php' );
		}

		$result = ob_get_contents();
		ob_end_clean();
		
		return $result;

	}
	return '';
}

add_shortcode("select_city", "belingoGeo_select_city_shortcode_deprecated");
function belingoGeo_select_city_shortcode_deprecated() {
	return do_shortcode('[belingogeo_select_city]');
}

?>