<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action('template_redirect', 'belingogeopro_template_redirect');
function belingogeopro_template_redirect() {
	if(function_exists('is_product')) {
		if(is_product()) {
			wc_delete_product_transients( get_the_ID() );
		}
	}
}

add_action('wp_ajax_belingogeopro_save_woo_prices', 'belingogeopro_save_woo_prices');
function belingogeopro_save_woo_prices() {

	if(isset($_POST['product_id'])) {
		$product_id = (int)sanitize_text_field($_POST['product_id']);
	}

	if(isset($_POST['cities'])) {
		$cities = json_decode(stripslashes($_POST['cities']));
	}

	if(isset($_POST['regular_prices'])) {
		$regular_prices = json_decode(stripslashes($_POST['regular_prices']));
	}

	if(isset($_POST['sale_prices'])) {
		$sale_prices = json_decode(stripslashes($_POST['sale_prices']));
	}

	$product_prices = [];
	foreach($cities as $key => $city) {
		$product_prices['_'.$city.'_regular_price'] = $regular_prices[$key];
		$product_prices['_'.$city.'_sale_price'] = $sale_prices[$key];
	}

	$update_product = wp_update_post(array(
        'ID'        => $product_id,
        'meta_input'=> $product_prices,
	));

	wc_delete_product_transients( $product_id );

	if($update_product) {
		echo 1;
	}

    die();

}

function belingogeopro_woo_price_func() {

	if ( class_exists( 'WooCommerce' ) ) {

		$args = [
			'posts_per_page' => -1
		];

		$cities = belingoGeo_get_cities($args);

		if(isset($_GET['paged'])) {
			$paged = $_GET['paged'];
		}else{
			$paged = 1;
		}

		$args = [
			'numberposts' => 5,
			'paged' => $paged,
			'type' => array('simple','variation')
		];

		if(isset($_GET['s'])) {
			$args['s'] = $_GET['s'];
		}

		$products = wc_get_products($args);
		
		echo '<form action=""><p class="search-box" style="margin-bottom: 20px;">
			<label class="screen-reader-text" for="post-search-input">'.__('Search products', 'belingogeo').'</label>
			<input type="hidden" name="page" value="belingogeo_woo_price">
			<input type="search" name="s" value="">
			<input type="submit" class="button" value="'.esc_attr('Search', 'belingogeo').'">
		</p></form>';
		echo '<div style="clear: both;"></div>';
		echo '<div style="overflow-x: scroll;">';
			echo '<table class="wp-list-table widefat striped">';
				echo '<thead>';
					echo '<tr>';
					echo '<td style="width: 260px;position:absolute;border-bottom:none;background: #fff;">'.__('Product Name', 'belingogeo').'</td>';		
					foreach ($cities as $key => $city) {
						echo '<td ';
						if($key == 0) {
							echo 'style="padding-left: 300px;"';
						}
						echo '>'.$city->get_name().'</td>';
					}
					echo '</tr>';
					echo '</thead>';
					echo '<tbody>';
					foreach($products as $product_key => $product) {
						echo '<tr>';
						echo '<td style="position: absolute;background: #fff;width:260px;height:99px;">'.$product->get_name().'<br><a href="#" class="button belingogeo_update_product" data-product-id="'.$product->get_id().'" style="margin-top: 5px;"><span class="update">'.__('Update', 'belingogeo').'</span><span class="updating hidden">'.__('Updating...', 'belingogeo').'</span></a></td>';
							foreach ($cities as $key => $city) {
								$regular_price = get_post_meta($product->get_id(), '_'.$city->get_slug().'_regular_price', true);
								$sale_price = get_post_meta($product->get_id(), '_'.$city->get_slug().'_sale_price', true);
								echo '<td ';
								if($key == 0) {
									echo 'style="padding-left: 300px;"';
								}
								echo '>';
								echo __('Regular price:', 'belingogeo').'<br>';
								echo '<input type="text" name="_regular_price_'.$product->get_id().'[]" value="'.$regular_price.'"><br>';
								echo __('Sale price:', 'belingogeo').'<br>';
								echo '<input type="text" name="_sale_price_'.$product->get_id().'[]" value="'.$sale_price.'"><br>';
								echo '<input type="hidden" name="city_'.$product->get_id().'[]" value="'.$city->get_slug().'">';
								echo '</td>';
							}
						echo '</tr>';
					}
				echo '</tbody>';
			echo '</table>';
		echo '</div>';
		$args = [
			'numberposts' => -1,
			'type' => array('simple','variation')
		];

		if(isset($_GET['s'])) {
			$args['s'] = $_GET['s'];
		}

		$products_count = count(wc_get_products($args));

		$args = [
			'base' => '?page=belingogeo_woo_price&paged=%#%',
			'format' => '',
			'total' => floor($products_count/5),
			'current' => $paged
		];
		echo '<div class="tablenav">';
		echo '<div class="tablenav-pages">';
		echo paginate_links($args);
		echo '</div>';
		echo '</div>';

	}else{
		_e('To work in this section you need to have WooCommerce installed.');
	}

}

function belingogeopro_get_city_price($price, $product_id, $type = 'regular') {
		
	$city = belingogeo_get_current_city();
	if($city) {
		$city_price = get_post_meta($product_id, '_'.$city->get_slug().'_'.$type.'_price', true);
		if($city_price && !empty($city_price) && $city_price != $price) {
			$price = $city_price;
		}
	}
	return $price;

}


add_filter('woocommerce_product_get_price', 'belingogeopro_city_price', 99, 2 );
add_filter('woocommerce_product_get_regular_price', 'belingogeopro_city_price', 99, 2 );
add_filter('woocommerce_product_variation_get_regular_price', 'belingogeopro_city_price', 99, 2 );
add_filter('woocommerce_product_variation_get_price', 'belingogeopro_city_price', 99, 2 );
function belingogeopro_city_price( $price, $product ) {
    return (float)belingogeopro_get_city_price($price, $product->get_id());
}

add_filter('woocommerce_product_get_sale_price', 'belingogeopro_city_sale_price', 99, 2 );
add_filter('woocommerce_product_variation_get_sale_price', 'belingogeopro_city_sale_price', 99, 2 );
function belingogeopro_city_sale_price( $price, $product ) {

	$sale_price = belingogeopro_get_city_price($price, $product->get_id(), 'sale');
	if(!empty($sale_price)) {
		$price = $sale_price;
	}
    return $price;

}

add_filter( 'woocommerce_get_price_html', 'belingogeopro_dynamic_sale_price_html', 20, 2 );
function belingogeopro_dynamic_sale_price_html( $price_html, $product ) {
    if( $product->is_type('variable') ) return $price_html;

    if($product->get_sale_price() > 0) {
    	$price_html = wc_format_sale_price( wc_get_price_to_display( $product, array( 'price' => $product->get_regular_price() ) ), wc_get_price_to_display(  $product, array( 'price' => $product->get_sale_price() ) ) ) . $product->get_price_suffix();
	}

    return $price_html;
}

add_filter('woocommerce_variation_prices_price', 'belingogeopro_variable_price', 99, 3 );
add_filter('woocommerce_variation_prices_regular_price', 'belingogeopro_variable_price', 99, 3 );
function belingogeopro_variable_price( $price, $variation, $product ) {
    return (float)belingogeopro_get_city_price($price, $variation->get_id());
}

add_filter('woocommerce_variation_prices_sale_price', 'belingogeopro_variable_sale_price', 99, 3 );
function belingogeopro_variable_sale_price( $price, $variation, $product ) {

	$sale_price = belingogeopro_get_city_price($price, $variation->get_id(), 'sale');
	if(!empty($sale_price)) {
		$price = (float)$sale_price;
	}
    return $price;
}

add_filter( 'woocommerce_get_variation_prices_hash', 'belingogeopro_add_price_multiplier_to_variation_prices_hash', 99, 3 );
function belingogeopro_add_price_multiplier_to_variation_prices_hash( $price_hash, $product, $for_display ) {
    $price_hash[] = '';
    return $price_hash;
}

?>