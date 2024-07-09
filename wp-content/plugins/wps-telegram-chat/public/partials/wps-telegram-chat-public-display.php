<?php

/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       https://wpsolution.org
 * @since      1.0.0
 *
 * @package    Wps_Telegram_Chat
 * @subpackage Wps_Telegram_Chat/public/partials
 */

	$cfg = array();
	$cfg['url'] = plugin_dir_url(dirname(__FILE__));
	
	// get this plugin as name
	$thisPlugin = $this->plugin_name;
	
	// Получаем все значения элементов формы
	$options = $this->options;
	
	// get images
	if(!$options['chatAnonimImg']){ $options['chatAnonimImg'] = $cfg['url'] . 'img/anonim-icon.svg'; }
	else{ $options['chatAnonimImg'] = wp_get_attachment_image_url( $options['chatAnonimImg'], 'thumbnail' ); }
	
	if(!$options['chatBotImg']){ $options['chatBotImg'] = $cfg['url'] . 'img/bot-icon.svg'; }
	else{ $options['chatBotImg'] = wp_get_attachment_image_url( $options['chatBotImg'], 'thumbnail' ); }
	
	// Новый объект для javascript
	$jsObj = $options;
	// заменим токен и чат ID для приватности
	$jsObj['token'] = substr($jsObj['token'], 0, 8) . '****-****' . substr($jsObj['token'], -8);
	$jsObj['chatId'] = substr($jsObj['chatId'], 0, 2) . '**-**' . substr($jsObj['chatId'], -2);
	$jsObj['proxyUrl'] = substr($jsObj['proxyUrl'], 0, 0) . '***' . substr($jsObj['proxyUrl'], -8);
	$jsObj['proxyPort'] = substr($jsObj['proxyPort'], 0, 1) . '**' . substr($jsObj['proxyPort'], -1);
	$jsObj['proxyUser'] = substr($jsObj['proxyUser'], 0, 2) . '**-**' . substr($jsObj['proxyUser'], -2);
	$jsObj['proxyPass'] = substr($jsObj['proxyPass'], 0, 2) . '**-**' . substr($jsObj['proxyPass'], -2);
	// add custom var
	$jsObj['ajaxUrl'] = admin_url('admin-ajax.php');
	$jsObj['baseUrl'] = $cfg['url'];
	
	// get site time
	$dt = new DateTime();
	$dt->setTimezone( wp_timezone() );
	$dt->setTimestamp( time() );
	$jsObj['srvTime'] = $dt->format('D H:i');
?>

<script>var wpsTelegramChat = <?php echo wp_json_encode($jsObj); ?></script>

<style>
	body #wps-telegram-chat{<?php
		echo esc_html( $options['chatCssVertical'] );
		echo esc_html( $options['chatCssHorizontal'] );
		echo esc_html( $options['chatCssBackground'] );
	?>}
	body #wps-telegram-chat.hide{<?php
		echo esc_html( $options['chatIconBackground'] );
	?>}

	<?php echo esc_html( $options['chatCustomCss'] ); ?>
	
	#wps-telegram-chat-content .content .icon{
		background-image: url( <?php echo esc_url($options['chatAnonimImg']); ?> );
	}
	#wps-telegram-chat-content .incomingMessage .icon{
		background-image: url( <?php echo esc_url($options['chatBotImg']); ?> );
	}
</style>

<div id="<?php echo esc_attr($thisPlugin); ?>" class="init">
	<?php if( !empty($options['chatTelegramLink']) ){ ?>
		<div id="<?php echo esc_attr($thisPlugin.'-app'); ?>">
			<a href="<?php echo esc_url($options['chatTelegramLink']); ?>" target="_blank">&nbsp;</a>
		</div>
	<?php } ?>
	<div id="<?php echo esc_attr($thisPlugin.'-update'); ?>"></div>
	<div id="<?php echo esc_attr($thisPlugin.'-close'); ?>"></div>
	<div id="<?php echo esc_attr($thisPlugin.'-wrapp'); ?>">
		<div id="<?php echo esc_attr($thisPlugin.'-header'); ?>">
			<h5><?php echo esc_html($options['chatTitle']); ?></h5>
		</div>
		<div id="<?php echo esc_attr($thisPlugin.'-welcome'); ?>">
			<p><?php echo esc_html($options['chatWelcomeTxt']); ?></p>
		</div>
		<div id="<?php echo esc_attr($thisPlugin.'-content'); ?>">
			<div class="contentWrapper"></div>
		</div>
		<div id="<?php echo esc_attr($thisPlugin.'-input'); ?>">
			<div id="<?php echo esc_attr($thisPlugin.'-textarea'); ?>">
				<textarea placeholder="<?php echo esc_html($options['chatPlaceholder']); ?>"></textarea>
			</div>
			<div id="<?php echo esc_attr($thisPlugin.'-send'); ?>">
				<i></i>
			</div>
		</div>
	</div>
</div>