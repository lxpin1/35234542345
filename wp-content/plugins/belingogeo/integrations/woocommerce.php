<?php

add_filter( 'woocommerce_get_breadcrumb', 'belingoGeo_breadcrumb_hook', 10, 2 );
function belingoGeo_breadcrumb_hook($crumbs, $obj) {

	$belingo_geo_basic_show_in_breadcrumbs = get_option( 'belingo_geo_basic_show_in_breadcrumbs' );

	if($belingo_geo_basic_show_in_breadcrumbs) {
		if(get_query_var('geo_city')) {
			$city = belingoGeo_get_current_city();
			if($city) {
				$city_crumbs = [];
				$city_url = belingoGeo_append_city_url(home_url(), $city->get_slug());
				$city_url = apply_filters('belingogeo_woo_bredcrumbs_city_url', $city_url);
				foreach ($crumbs as $key => $crumb) {
					if($key == 1) {
						$city_crumbs[] = [
							$city->get_name(),
							$city_url
						];
					}
					$city_crumbs[] = $crumb;
				}
				$crumbs = $city_crumbs;
			}
		}
	}

	return $crumbs;

}

?>