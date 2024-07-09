jQuery(document).on('click', '.belingogeo_update_product', function(e) {
	e.preventDefault();

	jQuery(this).children('.update').addClass('hidden');
	jQuery(this).children('.updating').removeClass('hidden');

	var product_id = jQuery(this).data('product-id');

	cities = [];
	jQuery('input[name="city_'+product_id+'[]"]').each(function(key, val) {
		cities.push(val.value);
	});

	regular_prices = [];
	jQuery('input[name="_regular_price_'+product_id+'[]"]').each(function(key, val) {
		regular_prices.push(val.value);
	});

	sale_prices = [];
	jQuery('input[name="_sale_price_'+product_id+'[]"]').each(function(key, val) {
		sale_prices.push(val.value);
	});

	cities = JSON.stringify(cities);
	regular_prices = JSON.stringify(regular_prices);
	sale_prices = JSON.stringify(sale_prices);

	jQuery.ajax({
		type: 'POST',
		url: '/wp-admin/admin-ajax.php',
		data: 'action=belingogeopro_save_woo_prices&cities='+cities+'&regular_prices='+regular_prices+'&sale_prices='+sale_prices+'&product_id='+product_id,
		success: function(resp) {
			if(resp == '1') {
				jQuery('.belingogeo_update_product .update').removeClass('hidden');
				jQuery('.belingogeo_update_product .updating').addClass('hidden');
			}
		}
	});

});