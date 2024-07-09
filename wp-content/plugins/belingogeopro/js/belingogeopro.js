jQuery(document).on('keyup', '#belingogeo_search_city', function() {
	var query = jQuery(this).val();
	var data = {
		action: 'load_cities',
		q: query
	}
	jQuery.ajax({
		type: 'POST',
		url: belingoGeo.ajaxurl,
		data: data,
		beforeSend: function() {
		  belingogeo_preloader_city_list();
		},
		success: function(response) {
		  jQuery('.quick-locations__values__container').html(response);
		}
	});
});