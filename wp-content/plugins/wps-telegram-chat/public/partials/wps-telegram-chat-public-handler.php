<?php

/**
 * @link       https://wpsolution.org
 * @since      1.0.0
 *
 * @package    Wps_Telegram_Chat
 * @subpackage Wps_Telegram_Chat/public/partials
 */
	
	global $cfg, $debug;
	$cfg = array();
	$cfg['cookie'] = array();
	$cfg['response'] = array();
	
	$cfg['thisPlugin'] = $this->plugin_name;
	$cfg['exData'] = __DIR__ . '/data.json';
	
	$nonce = wp_verify_nonce( $_POST['nonce'], $cfg['thisPlugin'] );
	if(!$nonce){
		echo '!NONCE';
		wp_die();
	}
	
	// Get plugin option fields
	$options = $this->options;
	
	if(isset( $_COOKIE[ $cfg['thisPlugin'] ]) ){
		
		$cfg['cookie'] = filter_var_array( json_decode( stripslashes( $_COOKIE[ $cfg['thisPlugin'] ] ), true ),
			[
				'user' => sanitize_user('user'),
				'nonce' => sanitize_key('nonce'),
				'lastUpdate' => FILTER_SANITIZE_NUMBER_INT,
				'lastUpdateType' => sanitize_html_class('lastUpdateType')
			]
		);
	}
	
	$allowed_html = array(
		'b' => array(),
		'i' => array(),
		'u' => array()
	);
	
	$cfg['mode'] = sanitize_html_class( $_POST['mode'] );
	$cfg['token'] = $options['token'];
	$cfg['proxy'] = $options['proxy'];
	$cfg['proxyUrl'] = $options['proxyUrl'];
	$cfg['chatId'] = $options['chatId'];
	$cfg['apiUrl'] = $this->apiUrl;
	$cfg['sendMessage'] = $cfg['apiUrl'] . $cfg['token'] . '/sendMessage';
	$cfg['getUpdates'] = $cfg['apiUrl'] . $cfg['token'] . '/getUpdates';
	$cfg['message'] = !empty($_POST['text']) ? wp_kses($_POST['text'], $allowed_html) : false;
	$cfg['user'] = '#User_' . wpsGetUserName();
	
	// delay before next request
	$cfg['time'] = time() - 4; // 4 seconds delay
	$cfg['lastUpdate'] = $cfg['cookie']['lastUpdate']; // last update for post
	$cfg['lastUpdate'] = $cfg['time'] < $cfg['lastUpdate'] ? true : false;
	
	if( $cfg['lastUpdate'] && $cfg['mode'] == $cfg['cookie']['lastUpdateType'] ){
		echo 'SPAM';
		wp_die();
	}
	
	$cfg['cookie']['lastUpdate'] = time();
	$cfg['cookie']['lastUpdateType'] = $cfg['mode'];

	if($cfg['mode'] == 'sendMessage' && $cfg['message']){ wpsSendMessage(); }
	else if($cfg['mode'] == 'getUpdates'){ wpsGetUpdates(); }
	
	function wpsGetUserName(){
		global $cfg;
		$user = $cfg['cookie']['user'];
		
		if (!$user) {
			$user = wp_get_current_user()->display_name;
			
			if(!$user){ $user = mt_rand(123456789, 999999999); }
			$user = preg_replace('/[^a-zA-Z0-9_]/', '_', $user);
			
			$cfg['cookie']['user'] = $user;
		}
		return $user;
	}
	
	function wpsGetUpdates(){
		global $cfg, $debug;
		
		// read file
		$exData = file_get_contents($cfg['exData']);
		$exData = json_decode( $exData, true );
		if(!$exData){
			$exData = array();
			$exData['update_id'] = -1;
			$exData['data'] = array();
		}
		
		$fileUpdate = filemtime($cfg['exData']); // last update for file
		
		// clear the file if it has not been updated for 60 minutes
		if($cfg['time'] - 3600 > $fileUpdate){ file_put_contents( $cfg['exData'], '', LOCK_EX ); }
		
		$fileUpdate = $cfg['time'] < $fileUpdate ? true : false;
		
		if(!$fileUpdate && !$cfg['lastUpdate']){
			// get updates
			$offset = $exData['update_id'] + 1;
			if($offset){ $offset = '?offset=' . $offset; } else { $offset = ''; }

			if( $cfg['proxy'] === 'script' ){
				$cfg['getUpdates'] = $cfg['proxyUrl'] . '?query=' . $cfg['getUpdates'];
			}

			$result = wp_remote_post( $cfg['getUpdates'] . $offset );
			
		}else{
			$result = array();
			$result['body'] = '{"ok":true,"result":[]}';
		}
		
		if ( is_wp_error($result) ) {
			// error
			$reply = array();
		}else{
			// response
			$reply = json_decode( $result['body'], true );
			if( !$reply || !$reply['ok'] ){ $reply = array(); }
			else{ $reply = $reply['result']; }

			foreach($reply as $key => $item){
				if($item['update_id']){
					$reply[ 'id_' . $item['update_id'] ] = $item;
					unset( $reply[$key] );
				}
			}
		}
		
		$data = array_merge($exData['data'], $reply);
		
		// procces data
		$toMessages = array();
		$toFile = array();
		$toFile['data'] = array();
		
		foreach($data as $item){
			$replyToMessage = !empty( $item['message']['reply_to_message'] ) ? $item['message']['reply_to_message'] : false;
			
			$id = 'id_' . $item['update_id'];
			$date = $item['message']['date'];
			$text = $item['message']['text'];
			
			if($replyToMessage){
				preg_match('/#User_(.+)\n/', $replyToMessage['text'], $user);
				$user = $user[1];
				
				if($user == $cfg['cookie']['user']){
					$toMessages[] = array(
						'user' => $user,
						'date' => $date,
						'text' => $text
					);
					apply_filters( 'wps_after_get_response', json_encode($toMessages) );
				}else{
					$toFile['data'][$id] = $item;
				}
			}else{
				// service messages
				//$toFile['data'][$id] = $item;
			}
		}
		
		// update ID
		$reply_id = !empty( end($reply)['update_id'] ) ? end($reply)['update_id'] : 0;
		//if(!$reply_id){ $reply_id = 0; }
		
		$data_id = !empty( end($data)['update_id'] ) ? end($data)['update_id'] : 0;
		//if(!$data_id){ $data_id = 0; }
		
		$update_id = max ( $exData['update_id'], $reply_id, $data_id );
		$toFile['update_id'] = $update_id;
		
		if( serialize($toFile['data']) != serialize($exData['data']) || $toFile['update_id'] != $exData['update_id'] ){
			// if the data has changed save to file
			file_put_contents( $cfg['exData'], json_encode($toFile), LOCK_EX );
		}
		
		$reply = array();
		$reply['toMessage'] = $toMessages;
		
		//$debug = true;
		
		$cfg['response']['result'] = $result;
		$cfg['response']['reply'] = $reply;
		$cfg['response']['exData'] = $toFile;
		$cfg['response']['debug'] = $debug;
	}
	
	function wpsSendMessage(){
		global $cfg;
		$args = array(
			'body' => array(
				'text' => $cfg['user'] . PHP_EOL . $cfg['message'],
				'chat_id' => $cfg['chatId'],
				'parse_mode' => 'HTML'
			)
		);

		$isGet = false;
		if( $cfg['proxy'] === 'script' ){
			$cfg['sendMessage'] = $cfg['proxyUrl'] . '?query=' . $cfg['sendMessage'];
			
			if( strpos($cfg['proxyUrl'], 'script.google') ){
				$isGet = true;
				$cfg['sendMessage'] = $cfg['sendMessage'] . '&args=' . urlencode( json_encode($args['body']) );
			}
		}
		
		if($isGet){
			$cfg['response']['result'] = wp_remote_post( $cfg['sendMessage'] );
		}else{
			$cfg['response']['result'] = wp_remote_post( $cfg['sendMessage'], $args );
		}
		apply_filters( 'wps_after_send_message', $cfg['response']['result']['body'] );
	}
	
	wpsSaveCookie();
	function wpsSaveCookie(){
		global $cfg;
		setcookie( $cfg['thisPlugin'], json_encode($cfg['cookie']), time() + 1800, '/' );
	}
	
	$cfg['response']['cookie'] = $cfg['cookie'];
	
	echo wp_json_encode( $cfg['response'] );
	wp_die(); // exit;
	
?>