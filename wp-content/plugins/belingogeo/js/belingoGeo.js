function show_popup_window(window_id) {
	var window_width = jQuery(window).width();
	var margin_top = parseInt(jQuery(window_id).height())/2;
	var margin_left = parseInt(jQuery(window_id).width())/2;
	jQuery(window_id).css('margin-top', -margin_top);
	if(window_width >= 787) {
		jQuery(window_id).css('margin-left', -margin_left);
	}else{
		jQuery(window_id).css('left', 'auto');
	}
	jQuery(window_id).show();
	jQuery('.popup-window-overlay').show();
}

function belingogeo_preloader_city_list() {
	jQuery('.quick-locations__values__container').html('<div style="padding:30px 0;text-align:center;"><svg xmlns:svg="http://www.w3.org/2000/svg" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.0" width="100px" height="100px" viewBox="0 0 128 128" xml:space="preserve"><path fill="#a0a1a7" d="M64.4 16a49 49 0 0 0-50 48 51 51 0 0 0 50 52.2 53 53 0 0 0 54-52c-.7-48-45-55.7-45-55.7s45.3 3.8 49 55.6c.8 32-24.8 59.5-58 60.2-33 .8-61.4-25.7-62-60C1.3 29.8 28.8.6 64.3 0c0 0 8.5 0 8.7 8.4 0 8-8.6 7.6-8.6 7.6z"><animateTransform attributeName="transform" type="rotate" from="0 64 64" to="360 64 64" dur="1800ms" repeatCount="indefinite"></animateTransform></path></svg></div>');
}

jQuery(document).ready(function() {

	var data = { 
		action: 'get_widget_city'
	}
	jQuery.post(belingoGeo.ajaxurl, data, function(response) {
		jQuery('.geolocation__value').html(response);
	});

	var data = { 
		action: 'show_city_question',
		back_url: belingoGeo.backurl,
		object_id: belingoGeo.object_id,
		object: belingoGeo.object,
	}
	jQuery.post(belingoGeo.ajaxurl, data, function(response) {

		if(response.redirect) {
			location.href = response.redirect;
		}else{
			jQuery('.geolocation_with_question__link').after(response.show_question);
		}

	});

	jQuery(document).on('click','.select_geo_city', function(e) {

		e.preventDefault();

		city_name = jQuery(this).data('name');
		city_name_orig = jQuery(this).data('name-orig');

		var data = { 
			action: 'write_city_cookie',
			city_name: city_name,
			city_name_orig: city_name_orig,
			object_id: belingoGeo.object_id,
			object: belingoGeo.object,
			back_url: belingoGeo.backurl
		}
		jQuery.post(belingoGeo.ajaxurl, data, function(response) {
			location.href = response.redirect;
		});
	});

	jQuery(document).on('click','.continue-without-geo', function(e) {
		e.preventDefault();
		var data = { 
			action: 'write_nogeo_cookie',
			back_url: belingoGeo.backurl
		}
		jQuery.post(belingoGeo.ajaxurl, data, function(response) {
			location.href = response.redirect;
		});
	});

	jQuery(document).on('click','#geolocationChangeCity, .geolocationChangeCity, .geolocation__link', function() {
		show_popup_window('#cityChange');
		var data = {
			action: 'load_cities'
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

	jQuery(document).on('click','.popup-window-close-icon', function() {
    	jQuery('.popup-window').hide();
    	jQuery('.popup-window-overlay').hide();
	});

});