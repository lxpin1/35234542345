// JavaScript Document
/*eslint-env es6*/
/*eslint-disable no-console*/
/*global window*/

(function($) {
	'use strict';
	
	$(function(){
		
		var plugin = 'wpsTelegramChat';
		
		$('#wpsContactSubmit input').on('click', function(){
			
			var name = $('#wpsContactName input').val();
			var email = $('#wpsContactEmail input').val();
			var message = $('#wpsContactSubject textarea').val();
			
			message = '\n'
				+ '<i>' + lang.myNameIs + ':</i> <b>' + name + '</b>\n'
				+ '<i>' + lang.myEmailIs + ':</i> <b>' + email + '</b>\n'
				+ '<i><u>' + lang.mySubject + ':</u></i> ' + '\n\n' + message;
			
			sendPost({ mode: 'sendMessage', text: message }, function(obj){
				var body = obj.result.body;
				
				if(body.ok){
					$('#wpsContactSubmit').remove();
					$('#wpsContactNotice').show();
				}
			});
		});
		
		function sendPost(data, callback){
			
			data.action = 'telegramHandler';
			data.nonce = window[plugin].cookie.nonce;
			
			$.post( window[plugin]['ajaxUrl'], data, function(response){
				var obj;
				
				try { obj = JSON.parse(response); }catch(e){ /* error */ }
				
				try { obj.result.body = JSON.parse(obj.result.body); }catch(e){ /* error */ }
				
				try { if( !obj.result.body.ok ){ console.log( 'ERROR: ', obj ); } }
				catch(e){
					console.log( 'ERROR: ', response );
				}
				
				if(typeof obj === 'object'){
					if(obj.debug){ console.log( 'Response: ', obj ); }
					callback(obj);
				}
			});
		}
		
	});

})( jQuery );