<?php

/**
 * @link       https://wpsolution.org
 * @since      1.0.0
 *
 * @package    Wps_Telegram_Chat
 * @subpackage Wps_Telegram_Chat/public/partials
 */

	global $cfg, $options, $thisVersion;
	
	$thisVersion = $this->version;
	
	$cfg = array();
	$cfg['url'] = plugin_dir_url(dirname(__DIR__));
	
	// Получаем значения полей плагина
	$options = $this->options;
	
	add_shortcode( 'wps-telegram-feedback', 'wpsContactForm' );
	
	function wpsContactForm( $atts ){
		
		global $cfg, $options, $thisVersion;
		
		wp_enqueue_script( 'wps-telegram-feedback',
			$cfg['url'] . 'public/js/wps-telegram-feedback.js', array( 'wps-telegram-chat' ), $thisVersion, false );
		
		wp_localize_script( 'wps-telegram-feedback', 'lang', array(
			'myNameIs' => esc_html($options['wpsContactName']),
			'myEmailIs' => esc_html($options['wpsContactEmail']),
			'mySubject' => esc_html($options['wpsContactSubject'])
		));
		
		ob_start(); // start html buffer ?>
			
			<div id="wps-telegram-contactForm">
				<div id="wps-telegram-formWrapper">
				
					<div id="wpsContactName">
						<label>
							<span><?php echo esc_html($options['wpsContactName']); ?></span>
							<input type="text" placeholder="<?php echo esc_html($options['wpsNamePlaceholder']); ?>" />
						</label>
					</div>
					
					<div id="wpsContactEmail">
						<label>
							<span><?php echo esc_html($options['wpsContactEmail']); ?></span>
							<input type="text" placeholder="<?php echo esc_html($options['wpsEmailPlaceholder']); ?>" />
						</label>
					</div>
					
					<div id="wpsContactSubject">
						<label>
							<span><?php echo esc_html($options['wpsContactSubject']); ?></span>
							<textarea placeholder="<?php echo esc_html($options['wpsSubjectPlaceholder']); ?>"></textarea>
						</label>
					</div>
					
					<div id="wpsContactSubmit">
						<div>
							<input type="button" class="button submit" value="<?php echo esc_html($options['wpsContactSubmit']); ?>" />
						</div>
					</div>
					
					<div id="wpsContactNotice">
						<div>
							<?php echo esc_html($options['wpsContactNotice']); ?>
						</div>
					</div>
					
				</div>
			</div>
			
		<?php return ob_get_clean(); // buffer output
	}

?>