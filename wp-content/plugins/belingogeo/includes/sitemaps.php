<?php

function belingogeo_generate_sitemap() {

	$city = get_query_var('city_sitemap');
	$post_type = get_query_var('sitemap_type');
	$per_page = get_option('belingo_geo_sitemap_per_page');
	if(get_query_var('sitemap_page')) {
		$page = get_query_var('sitemap_page');
	}else{
		$per_page = -1;
		$page = 1;
	}
	if($post_type == 'post') {
		$urls = belingoGeo_get_sitemap_post_urls($city,$per_page,$page);
	}elseif($post_type == 'page') {
		$urls = belingoGeo_get_sitemap_page_urls($city,$per_page,$page);
	}else{
		if(get_query_var('sitemap_tax')) {
			$taxonomy = get_query_var('sitemap_tax');
			$urls = belingoGeo_get_sitemap_taxonomies_urls($city,$taxonomy);
		}else{
			$urls = belingoGeo_get_sitemap_custom_posts_urls($city,$per_page,$page,$post_type);
		}
	}
	
	if(is_array($urls) && count($urls) > 0) {
		belingoGeo_get_xml($urls);
		exit;
	}else{
		global $wp_query;
		$wp_query->set_404();
	    status_header( 404 );
	    nocache_headers();
	}
	
}

// Добавление новых sitemap в Yaost SEO Sitemap
add_filter('wpseo_sitemap_index', 'belingogeo_modify_yaost_sitemap');
function belingogeo_modify_yaost_sitemap() {

	$xml = '';

	$disable_urls = get_option('belingo_geo_basic_disable_url');

	if($disable_urls && $disable_urls == 1) {
		return;
	}

	$allow = true;
	$allow = apply_filters('belingogeo_allow_modify_yaost_sitemap', $allow);

	if(!$allow) {
		return;
	}

	$per_page = get_option('belingo_geo_sitemap_per_page');

	$arr_posts_exclude = [];
	$arr_terms_exclude = [];
	if(get_option('belingo_geo_exclude_posts')) {
		$arr_posts_exclude = get_option('belingo_geo_exclude_posts');
	}
	if(get_option('belingo_geo_exclude_terms')) {
		$arr_terms_exclude = get_option('belingo_geo_exclude_terms');
	}
	$query = new WP_Query([
		'post_type'=>'post',
		'post_status'=>'publish',
		'post__not_in'=>$arr_posts_exclude,
		'category__not_in'=>$arr_terms_exclude
	]);
	$count_posts = $query->found_posts;

	$arr_pages_exclude = [];
	if(get_option('belingo_geo_exclude_pages')) {
		$arr_pages_exclude = get_option('belingo_geo_exclude_pages');
	}
	$query = new WP_Query([
		'post_type'=>'page',
		'post_status'=>'publish',
		'post__not_in'=>$arr_pages_exclude
	]);
	$count_pages = $query->found_posts;

	$args = array(
       'public'   => true,
       'publicly_queryable' => true,
       '_builtin' => false,
    );
    $arr_post_types_exclude = get_option('belingo_geo_exclude_post_types');
    $post_types = get_post_types( $args, 'names', 'and' );
    foreach ( $post_types  as $post_type ) {
	    if(!array_key_exists($post_type, (array)$arr_post_types_exclude)) {
	    	$count_custom_posts_var = 'count_custom_posts_'.$post_type;
	    	$custom_posts_pages_var = 'custom_posts_pages_'.$post_type;
			$$count_custom_posts_var = wp_count_posts($post_type)->publish;
			$custom_posts_pages = $$count_custom_posts_var/$per_page;
			$$custom_posts_pages_var = round($custom_posts_pages);
		}
	}

	$post_pages = round($count_posts/$per_page);
	$page_pages = round($count_pages/$per_page);

	if($post_pages < 1) {
		$post_pages = 1;
	}

	if($page_pages < 1) {
		$page_pages = 1;
	}

	$args = [
		'posts_per_page' => -1
	];
	foreach (belingoGeo_get_cities($args) as $city) {

		$default_city = belingogeo_get_default_city();
		if($default_city && $default_city->get_slug() == $city->get_slug()) {
			continue;
		}

		$belingo_geo_exclude_all_posts = get_option('belingo_geo_exclude_all_posts');
		if(!$belingo_geo_exclude_all_posts) {
			$page = 1;
			while($post_pages >= $page) {
				$url = [
					"loc" => get_site_url() . '/'.$city->get_slug().'_post.sitemap'.$page.'.xml',
					"lastmod" => date('c',time())
				];
				$xml .= belingoGeo_get_xml_sitemap($url);
				$page++;
			}
		}
		$page = 1;
		while($page_pages >= $page) {
			$url = [
				"loc" => get_site_url() . '/'.$city->get_slug().'_page.sitemap'.$page.'.xml',
				"lastmod" => date('c',time())
			];
			$xml .= belingoGeo_get_xml_sitemap($url);
			$page++;
		}
		foreach ( $post_types  as $post_type ) {
			$page = 1;
			$custom_posts_pages_var = 'custom_posts_pages_'.$post_type;
			if(isset($$custom_posts_pages_var)) {
				if($$custom_posts_pages_var < 1) {
					$$custom_posts_pages_var = 1;
				}
				while($$custom_posts_pages_var >= $page) {
					$url = [
						"loc" => get_site_url() . '/'.$city->get_slug().'_'.$post_type.'.sitemap'.$page.'.xml',
						"lastmod" => date('c',time())
					];
					$xml .= belingoGeo_get_xml_sitemap($url);
					$page++;
				}
			}
			$taxonomies = get_object_taxonomies( array( 'post_type' => $post_type ), 'objects' );
			$exclude_taxonomies = get_option('belingo_geo_exclude_taxonomies');
			foreach($taxonomies as $taxonomy) {
				if($taxonomy->public == 1 && $taxonomy->show_ui == 1 && !array_key_exists($taxonomy->name, (array)$exclude_taxonomies)) {
					$url = [
						"loc" => get_site_url() . '/'.$city->get_slug().'_sitemap_tax_'.$taxonomy->name.'.xml',
						"lastmod" => date('c',time())
					];
					$xml .= belingoGeo_get_xml_sitemap($url);
				}
			}
		}
	}

	return $xml;

}

function belingoGeo_get_sitemap_post_urls($city,$per_page,$page) {

	$urls = [];

	$belingo_geo_exclude_all_posts = get_option('belingo_geo_exclude_all_posts');
	if($belingo_geo_exclude_all_posts) {
		return $urls;
	}

	// записи
	$arr_posts_exclude = [];
	$arr_terms_exclude = [];
	if(get_option('belingo_geo_exclude_posts')) {
		$arr_posts_exclude = get_option('belingo_geo_exclude_posts');
	}
	if(get_option('belingo_geo_exclude_terms')) {
		$arr_terms_exclude = get_option('belingo_geo_exclude_terms');
	}
	$query = new WP_Query([
		'paged'=>$page,
		'post_type'=>'post',
		'posts_per_page'=>$per_page,
		'post_status'=>'publish',
		'post__not_in'=>$arr_posts_exclude,
		'category__not_in'=>$arr_terms_exclude
	]);
	foreach ($query->posts as $post_item) {
		if(!empty($post_item->post_name)) {
			$loc = get_the_permalink($post_item->ID);
			$current_city = belingoGeo_get_current_city();
			if($current_city) {
				$loc = belingogeo_remove_city_url($loc, $current_city->get_slug());
			}
			$loc = belingoGeo_append_city_url($loc, $city);
			$urls[] = [
				"loc" => $loc,
				"lastmod" => date('c',strtotime($post_item->post_modified))
			];
		}
	}

	return $urls;

}

function belingoGeo_get_sitemap_page_urls($city,$per_page,$page) {

	$urls = [];

	// страницы
	$arr_pages_exclude = [];
	if(get_option('belingo_geo_exclude_pages')) {
		$arr_pages_exclude = get_option('belingo_geo_exclude_pages');
	}
	$query = new WP_Query([
		'paged'=>$page,
		'post_type'=>'page',
		'posts_per_page'=>$per_page,
		'post_status'=>'publish',
		'post__not_in'=>$arr_pages_exclude
	]);
	foreach ($query->posts as $post_item) {
		if(!empty($post_item->post_name)) {
			$loc = get_the_permalink($post_item->ID);
			$current_city = belingoGeo_get_current_city();
			if($current_city) {
				$loc = belingogeo_remove_city_url($loc, $current_city->get_slug());
			}
			$loc = belingoGeo_append_city_url($loc, $city);
			$urls[] = [
				"loc" => $loc,
				"lastmod" => date('c',strtotime($post_item->post_modified))
			];
		}
	}

	return $urls;
	
}

function belingoGeo_get_sitemap_custom_posts_urls($city,$per_page,$page,$post_type) {

	$urls = [];

    $arr_post_types_exclude = get_option('belingo_geo_exclude_post_types');
	if(!array_key_exists($post_type, (array)$arr_post_types_exclude)) {
		$posts = new WP_Query(array(
		    'paged'=>$page,
			'post_type'		=>	$post_type,
			'posts_per_page'=>	$per_page,
			'post_status'	=>	'publish'
		));
		foreach ($posts->posts as $post_item) {
			if(!empty($post_item->post_name)) {
				$loc = get_permalink($post_item->ID);
				$current_city = belingoGeo_get_current_city();
				if($current_city) {
					$loc = belingogeo_remove_city_url($loc, $current_city->get_slug());
				}
				$loc = belingoGeo_append_city_url($loc, $city);
				$urls[] = [
					"loc" => $loc,
					"lastmod" => date('c',strtotime($post_item->post_modified))
				];
			}
		}
	}

    return $urls;
	
}

function belingoGeo_get_sitemap_taxonomies_urls($city,$taxonomy) {

	$urls = [];

	$exclude_taxonomies = get_option('belingo_geo_exclude_taxonomies');

	if(!array_key_exists($taxonomy, $exclude_taxonomies)) {

		$terms = get_terms(array(
			'taxonomy' => $taxonomy
		));
		foreach ($terms as $term) {
			$loc = get_term_link($term->slug, $taxonomy);
			$current_city = belingoGeo_get_current_city();
			if($current_city) {
				$loc = belingogeo_remove_city_url($loc, $current_city->get_slug());
			}
			$loc = belingoGeo_append_city_url($loc, $city);
			$urls[] = [
				"loc" => $loc,
				"lastmod" => date('c',time())
			];
		}

	}

    return $urls;
	
}

function belingoGeo_get_xml($urls) {

	$headers = [
			'HTTP/1.1 200 OK' => 200,
			'X-Robots-Tag: noindex, follow'  => '',
			'Content-Type: text/xml; charset=' . esc_attr( 'UTF-8' ) => '',
		];


	foreach ( $headers as $header => $status ) {
		if ( is_numeric( $status ) ) {
			header( $header, true, $status );
			continue;
		}
		header( $header, true );
	}

	$headers_string = '';

	$headers_string .= '<?xml version="1.0" encoding="UTF-8"?>' . "\n";

	if(check_url(plugins_url('/wordpress-seo/css/main-sitemap.xsl'))) {
		$headers_string .= '<?xml-stylesheet type="text/xsl" href="'.plugins_url('/wordpress-seo/css/main-sitemap.xsl').'"?>';
	}

	$headers_string .=	 '<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" '
			. 'xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd '
			. 'http://www.google.com/schemas/sitemap-image/1.1 http://www.google.com/schemas/sitemap-image/1.1/sitemap-image.xsd" '
			. 'xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

	echo apply_filters('belingogeo_xml_sitemap_headers_string', $headers_string);

	foreach ($urls as $key => $url) {
		echo belingoGeo_get_xml_sitemap_url($url);
	}
		
	echo '</urlset>';

}

function check_url($url) {

	$headers = @get_headers($url);
	if(!$headers || $headers[0] == 'HTTP/1.1 404 Not Found') {
	    return false;
	}else{
	    return true;
	}

}

function belingoGeo_get_xml_sitemap_url($url) {

	return '	<url>' . "\n" .
				'		<loc>'.$url['loc'].'</loc>' . "\n" .
				'		<lastmod>'.$url['lastmod'].'</lastmod>' . "\n" .
			  '	</url>' . "\n";

}

function belingoGeo_get_xml_sitemap($url) {

	return '<sitemap>' . "\n" .
				'<loc>'.$url['loc'].'</loc>' . "\n" .
				'<lastmod>'.$url['lastmod'].'</lastmod>' . "\n" .
			  '</sitemap>' . "\n";

}

?>