// JavaScript Document
/*eslint-disable no-console*/
/*global wp, wpsTelegramChat*/

(function($) {
	'use strict';
	
	$(function() {
		
		console.log( wpsTelegramChat );
		
		// accordion
		$('#wps-telegram-chat').accordion({ // init accordion UI
			header: 'h3',
			collapsible: true,
			heightStyle: 'content',
			icons: {
				'header': 'dashicons dashicons-arrow-right',
				'activeHeader': 'dashicons dashicons-arrow-down'
			},
			active: 0
		});
		
		// clear options
		$('#chatDeleteOptions').click(function(){
			$.get(wpsTelegramChat['ajaxUrl'], {action: 'options_delete'}, function() {
				document.location.reload(true);
			});
		});

		// popUpclose
		$('#popUp, #popUp .close').click(function(){
			$('#wps-telegram-chat').removeClass('popUp');
		});
		$('#popUp div').click(function(){ return false; });

		// Proxy options
		var proxy = $('#proxy input:checked').val();
		$('#chatProxy').attr('class', proxy);
		
		$('#proxy input:radio').on('change', function(){
			proxy = $(this).val();
			$('#chatProxy').attr('class', proxy);
		});
		
		// chatOnSpecificPages
		if($('#chatShow input:checkbox').is(':checked')){
			$('#chatShow').attr('class', 'onSpecific');
		}
		
		$('#chatShow input:checkbox').on('change', function(){
			if($('#chatShow input:checkbox').is(':checked')){
				$('#chatShow').attr('class', 'onSpecific');
			}else{
				$('#chatShow').removeAttr('class');
			}
		});
		
		// Schedule	
		if($('#alwaysOnline input').is(':checked')){
			$('#chatSchedule').attr('class', 'alwaysOnline');
		}
		
		$('#alwaysOnline input').on('change', function(){
			if($('#alwaysOnline input').is(':checked')){
				$('#chatSchedule').attr('class', 'alwaysOnline');
			}else{
				$('#chatSchedule').removeAttr('class');
			}
		});
		
		// fill Schedule
		$('#chatSchedule .dayBox input').each(function(){
			var startTime, endTime;
			var $parentBox = $(this).parents('.dayBox');
			var value = $(this).val();
			
			if(value){
				$(this).prop('checked', true);
				startTime = value.split('-')[0].split(':');
				endTime = value.split('-')[1].split(':');
				
				select(startTime, '.startTime select');
				select(endTime, '.endTime select');
			}
			
			function select(time, selector){
                if(wpsTelegramChat.timeFormat){
                    time[2] = time[0] >= 12 ? 'pm' : 'am';
                    time[0] = ('0' +(time[0] % 12) || 12).slice(-2);
                }

                $(selector, $parentBox).eq(0).val(time[0]);
                $(selector, $parentBox).eq(1).val(time[1]);
                $(selector, $parentBox).eq(2).val(time[2]);
            }
		});
		
		// change Schedule
		$('#chatSchedule .dayBox select').on('change', function(){

			var $parentBox = $(this).parents('.dayBox');
			var startTime = join($('.startTime', $parentBox));
			var endTime = join($('.endTime', $parentBox));
			
			function join($parent){
				var result = '';
				$('select', $parent).each(function(){
					var val = $(this).val();
					result = result ? result + ':' + val : val;
				});
				
				// convert 12 hour to 24 hours format
				if(result.indexOf('am')||result.indexOf('pm')){
					result = result.split(':');
					if(result[0] === 12){ result[0] = '00'; }
					if(result[2] === 'pm'){ result[0] = parseInt(result[0], 10) + 12; }
					result = result[0] + ':' + result[1];
				}
				
				return result;
			}
			
			// range check
			var hS = startTime.split(':')[0];
			var hE = endTime.split(':')[0];
			
			if(hS >= hE || hS > 23 || hE >23){
				$('#chatSchedule .info').prependTo($parentBox);
				$parentBox.addClass('error');
				$('input', $parentBox)
					.prop('checked', false)
					.prop('disabled', true);
			}else{
				$parentBox.removeClass('error');
				$('input', $parentBox)
					.prop('checked', true)
					.prop('disabled', false)
					.val(startTime + '-' + endTime);
			}
		});
		
		// telegramWebHook
		$('#chatSetWebHook, #chatDelWebHook, #chatGetWebHook').on('click', function(){
			var id = $(this).attr('id');
			var mode = '';
			
			$('#chatWebHook .info').html('');
			
			if(id === 'chatSetWebHook'){ mode = 'setWebhook'; }
			else if(id === 'chatDelWebHook'){ mode = 'deleteWebhook'; }
			else if(id === 'chatGetWebHook'){ mode = 'getWebhookInfo'; }
			
			console.log('webHook: ', mode);
			
			var token = $('#chatToken input').val();
			var apiUrl = 'https://api.telegram.org/bot' + token + '/' + mode;

			if( proxy === 'script' ){
				apiUrl = $('#proxyUrl input').val() + '?query=' + encodeURIComponent(apiUrl);
			}

			$.get( wpsTelegramChat['ajaxUrl'], {action: 'checkСonnection', mode: mode}, function(e) {
				response(e);
			});
			 
		});

		function response(data){
			//data = data.replace('0','');
			try { data = JSON.parse(data); } catch {}

			$.each(data, function(key, val){
				try { data[key] = JSON.parse(val); } catch {}
			});

			var output = '<pre>' + JSON.stringify(data, null, 4) + '</pre>';
			var $info = $('#popUp .info');
			
			$info.html(output).attr('class', 'info');
			$('#wps-telegram-chat').addClass('popUp');
			
			if(data.body && data.body.ok){
				if( data.body.result && !data.body.result.url ){ $info.addClass('ok');
				}else{ $info.addClass('error'); }
			}
		}
		
		// avatar images
		$('#chatGetAnonimImg, #chatGetBotImg').on('click', function(e){
			var type = $(this).attr('id').replace('Get', '');
			
			e.preventDefault();
             var image_frame;
             if(image_frame){ image_frame.open(); }
             
			// Define image_frame as wp.media object
			image_frame = wp.media({
				title: 'Select Media',
				multiple: false,
				library: { type : 'image' }
			});
			
			image_frame.on('close',function() {
				// On close, get selections and save to the hidden input
				// plus other AJAX stuff to refresh the image preview
				var selection =  image_frame.state().get('selection');
				var gallery_ids = new Array();
				var my_index = 0;
				selection.each(function(attachment) {
					gallery_ids[my_index] = attachment['id'];
					my_index++;
				});
				
				var ids = gallery_ids.join(',');
				if(ids.length === 0) return true;//if closed withput selecting an image
				$('input', '#' + type).val(ids);
				refreshImage(ids, type);
			});
			
			image_frame.on('open',function() {
				// On open, get the id from the hidden input
				// and select the appropiate images in the media manager
				var selection =  image_frame.state().get('selection');
				var ids = $('input', '#' + type).val().split(',');
				ids.forEach(function(id) {
					var attachment = wp.media.attachment(id);
					attachment.fetch();
					selection.add( attachment ? [ attachment ] : [] );
				});
			});
			
			image_frame.open();
		});
		
		// Ajax request to refresh the image preview
		function refreshImage(imgId, type){
			var data = {
				action: 'telegramAdminImg',
				id: imgId
			};
			
			$.get(wpsTelegramChat['ajaxUrl'], data, function(response) {
				if(response.success === true) {
					$('img', '#' + type).replaceWith( response.data.image );
				}
			});
		}
	});

})( jQuery );
