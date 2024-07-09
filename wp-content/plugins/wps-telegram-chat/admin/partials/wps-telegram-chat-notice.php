<?php

/**
 * @link       https://wpsolution.org
 * @since      1.0.0
 *
 * @package    Wps_Telegram_Chat
 * @subpackage Wps_Telegram_Chat/admin/partials
 */

	global $cfg, $options;
	
	// Получаем значения полей плагина
	$options = $this->options;
	
	$cfg = array();
	$cfg['hookName'] = current_filter();
	$cfg['botUrl'] = $this->apiUrl . $options['token'] . '/sendMessage';
	$cfg['adminEmail'] = get_bloginfo('admin_email');
	
	if( $cfg['hookName'] === 'wp_mail' && $options['wpMailNotice'] ){
		if( $args['to'] === $cfg['adminEmail'] ){
			wpsSendNotice( $args['message'] );
		}
	}

	function wpsSendNotice( $message ){
		global $cfg, $options;
		$params = array(
			'body' => array(
				'text' => $message,
				'chat_id' => $options['chatId'],
				'parse_mode' => 'HTML'
			)
		);
		
		$cfg['result'] = wp_remote_post( $cfg['botUrl'], $params );
	}

?>