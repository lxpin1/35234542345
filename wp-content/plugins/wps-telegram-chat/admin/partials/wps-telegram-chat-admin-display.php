<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://wpsolution.org
 * @since      1.0.0
 *
 * @package    Wps_Telegram_Chat
 * @subpackage Wps_Telegram_Chat/admin/partials
 */

	// get this plugin as name
	$thisPlugin = $this->plugin_name;
	
	$cfg = array();
	$cfg['url'] = plugin_dir_url(dirname(__DIR__));
	$cfg['path'] = plugin_dir_path(dirname(__DIR__));
	$cfg['data'] = get_plugin_data( $cfg['path'].$thisPlugin.'.php' );
	
	// Получаем значения полей плагина
	$options = $this->options;
	
	// Schedule
	$daysOfWeek = wpsShiftArr(array(
		'sun' => __('sun', $thisPlugin),
		'mon' => __('mon', $thisPlugin),
		'tue' => __('tue', $thisPlugin),
		'wed' => __('wed', $thisPlugin),
		'thu' => __('thu', $thisPlugin),
		'fri' => __('fri', $thisPlugin),
		'sat' => __('sat', $thisPlugin)
	), get_option('start_of_week'));
	$timeFormat = strripos(get_option('time_format'), ' a') == false ? 0 : 1;
	
	function wpsShiftArr(array $arr, int $n): array{
		$n = $n % count($arr);
		$slice = array_splice($arr, $n);
		return array_merge($slice, $arr);
	}
	
	// add custom var
	$options['ajaxUrl'] = admin_url('admin-ajax.php');
	$options['timeFormat'] = $timeFormat;

?>

<script>var wpsTelegramChat = <?php echo wp_json_encode($options); ?></script>
<div id="<?php echo esc_attr($thisPlugin); ?>">

	<div id="infoBlock">
		<!--div id="authorLogo">
			<?php echo '<img src="' . esc_url($cfg['url'] . 'public/img/wpsolution.svg') . '" alt="">'; ?>
		</div-->
		<div id="info">
			<h2>
				<i class="dashicons dashicons-wordpress"></i>
				<?php echo esc_html($cfg['data']['Name']); ?>
			</h2>
			<div>
				<?php echo sprintf( __('Version %s By', $thisPlugin), $cfg['data']['Version'] ); ?>
				<a href="<?php echo esc_url($cfg['data']['AuthorURI']); ?>" target="_blank">
					<?php echo esc_html($cfg['data']['AuthorName']); ?></a><br>
				<?php _e('For more information', $thisPlugin); ?>
				<a href="<?php echo esc_url($cfg['data']['PluginURI']); ?>" target="_blank">
					<?php _e('Visit plugin site', $thisPlugin); ?></a>
			</div>
			<div>
				<a href="https://www.paypal.com/ncp/payment/47NR4KK73SSEU" target="_blank">
					<?php _e('A small gesture, becomes something bigger…', $thisPlugin); ?></a><br>
				<?php _e('Please consider making a donation to us. This plugin is free of any costs.
					We keep it running by doing our regular work.', $thisPlugin); ?>
			</div>
		</div>
	</div>

	<div id="popUp">
		<div>
			<p class="close">✕</p>
			<p class="info"></p>
		</div>
	</div>
	
	<form method="post" action="options.php">
	
		<?php // Выводит скрытые поля формы страницы настроек, к ним добавим свои
			settings_fields( $thisPlugin );
			do_settings_sections( $thisPlugin );
		?>
		
		<h3>
			<i class="dashicons dashicons-privacy"></i>
			<?php _e('Bot options', $thisPlugin); ?>
		</h3>
		
		<div id="chatOptions">
			<div id="chatEnabled">
				<label>
					<span><?php _e('Enabled', $thisPlugin); ?></span>
					<input type="checkbox" name="<?php echo esc_attr($thisPlugin.'[enabled]'); ?>"
						value="1" <?php echo checked( '1', $options['enabled'] ); ?>
					/>
				</label>
				<p></p>
			</div>
			
			<div id="chatToken">
				<label>
					<span><?php _e('Authentication token', $thisPlugin); ?></span>
					<input type="text" name="<?php echo esc_attr($thisPlugin.'[token]'); ?>"
						   value="<?php echo esc_attr($options['token']);?>"
						   placeholder="XXX:YYYYYYY"
					/>
				</label>
				<p>
					<?php _e('Each bot is given a unique authentication <b>token</b> when it is created.
					The token looks something like <code>123456:ABC-DEF1234ghIkl-zyx57W2v1u123ew11</code>.
					You can learn about obtaining tokens and generating new ones follow the link:',
					$thisPlugin); ?>
					<a target="_blank" href="https://core.telegram.org/bots/features#botfather">
						<?php _e('Read more', $thisPlugin); ?></a>
				</p>
			</div>
			
			<div id="chatId">
				<label>
					<span><?php _e('Telegram chat ID', $thisPlugin); ?></span>
					<input type="text" name="<?php echo esc_attr($thisPlugin.'[chatId]'); ?>"
						   value="<?php echo esc_attr($options['chatId']); ?>"
						   placeholder="XXXXXXXXX"
					/>
				</label>
				<p>
					<?php _e('In the telegram messenger every user, chat, and group
					is having a unique ID so we need to find our chat ID:',
					$thisPlugin); ?>
					<a target="_blank" href="https://t.me/getmyid_bot">Get My ID</a>
				</p>
			</div>
        </div>

		<h3>
			<i class="dashicons dashicons-admin-links"></i>
			<?php _e('Proxy options', $thisPlugin); ?>
		</h3>
		
		<div id="chatProxy">
			<p>
				<?php _e('You can use Proxy Options to bypass the bans on Telegram API by different hosts.',
					$thisPlugin); ?>
			</p>
			<div id="proxy">
				<label><span>Method</span></label>
				<label>
					<input type="radio" name="<?php echo esc_attr($thisPlugin.'[proxy]'); ?>"
						value="none" <?php echo checked( 'none', $options['proxy'] ); ?>
					/> <span><?php _e('NONE', $thisPlugin); ?></span>
				</label>
				<label>
					<input type="radio" name="<?php echo esc_attr($thisPlugin.'[proxy]'); ?>"
						value="script" <?php echo checked( 'script', $options['proxy'] ); ?>
					/> <span>SCRIPT</span>
				</label>
				<label>
					<input type="radio" name="<?php echo esc_attr($thisPlugin.'[proxy]'); ?>"
						value="proxy" <?php echo checked( 'proxy', $options['proxy'] ); ?>
					/> <span>PROXY</span>
				</label>
				<p>
					<?php _e('Please see the detailed instructions for:', $thisPlugin); ?>
					<a target="_blank" href="https://wps.dir.md/plugin/wps-telegram-chat/#accordion-item-proxy_script_cloudflare">
						cloudFlare</a> or
					<a target="_blank" href="https://wps.dir.md/plugin/wps-telegram-chat/#accordion-item-proxy_script_google_apps">
						googleScript</a>
				</p>
			</div>
			<div id="proxyUrl">
				<label>
					<span>Url / Host</span>
					<input type="text" name="<?php echo esc_attr($thisPlugin.'[proxyUrl]'); ?>"
						   value="<?php echo esc_attr($options['proxyUrl']);?>"
						   placeholder="IP / Domain or script URL"
					/>
				</label>
			</div>
			<div id="proxyOptions">
				<label>
					<span>Port</span>
					<input type="text" name="<?php echo esc_attr($thisPlugin.'[proxyPort]'); ?>"
						   value="<?php echo esc_attr($options['proxyPort']);?>"
						   placeholder="3128"
					/>
				</label>
				<fieldset id="proxyType">
					<label><span>Type</span></label>
					<label>
						<input type="radio" name="<?php echo esc_attr($thisPlugin.'[proxyType]'); ?>"
							value="HTTP" <?php echo checked( 'HTTP', $options['proxyType'] ); ?>
						/> <span>HTTP/S</span>
					</label>
					<label>
						<input type="radio" name="<?php echo esc_attr($thisPlugin.'[proxyType]'); ?>"
							value="SOCKS4" <?php echo checked( 'SOCKS4', $options['proxyType'] ); ?>
						/> <span>SOCKS4</span>
					</label>
					<label>
						<input type="radio" name="<?php echo esc_attr($thisPlugin.'[proxyType]'); ?>"
							value="SOCKS4A" <?php echo checked( 'SOCKS4A', $options['proxyType'] ); ?>
						/> <span>SOCKS4A</span>
					</label>
					<label>
						<input type="radio" name="<?php echo esc_attr($thisPlugin.'[proxyType]'); ?>"
							value="SOCKS5" <?php echo checked( 'SOCKS5', $options['proxyType'] ); ?>
						/> <span>SOCKS5</span>
					</label>
					<label>
						<input type="radio" name="<?php echo esc_attr($thisPlugin.'[proxyType]'); ?>"
							value="SOCKS5_HOSTNAME" <?php echo checked( 'SOCKS5_HOSTNAME', $options['proxyType'] ); ?>
						/> <span>SOCKS5_HOSTNAME</span>
					</label>
				</fieldset>
				<label>
					<span><?php _e('Username', $thisPlugin); ?></span>
					<input type="text" name="<?php echo esc_attr($thisPlugin.'[proxyUser]'); ?>"
						   value="<?php echo esc_attr($options['proxyUser']);?>"
						   placeholder="<?php _e('Leave empty if not required', $thisPlugin); ?>"
					/>
				</label>
				<br>
				<label>
					<span><?php _e('Password', $thisPlugin); ?></span>
					<input type="text" name="<?php echo esc_attr($thisPlugin.'[proxyPass]'); ?>"
						   value="<?php echo esc_attr($options['proxyPass']);?>"
						   placeholder="<?php _e('Leave empty if not required', $thisPlugin); ?>"
					/>
				</label>
			</div>
			<div id="chatWebHook">
				<p>
					<?php _e('Save your settings before Check webHook!', $thisPlugin); ?>
				</p>
				<label>
					<span><?php _e('WebHook', $thisPlugin); ?></span>
					<!--input type="text" name="<?php echo esc_attr($thisPlugin.'[webHook]'); ?>"
						   value="<?php //echo esc_attr($options['webHook']); ?>"
					/-->
				</label>
				<div>
					<button type="button" id="chatGetWebHook"><?php _e('Check webHook', $thisPlugin); ?></button>
					<!--button type="button" id="chatSetWebHook"><?php _e('Set webHook', $thisPlugin); ?></button-->
					<button type="button" id="chatDelWebHook"><?php _e('Delete webHook', $thisPlugin); ?></button>
				</div>
				<p>
					<?php _e('There are two mutually exclusive ways of receiving updates for your bot -
					the <b>getUpdates</b> method on one hand and <b>webHook</b> on the other. We need to be able 
					to connect and post updates with <b>getUpdates</b> method. This method will not work if an outgoing 
					<b>webHook</b> is set up.',
					$thisPlugin); ?>
				</p>
			</div>
		</div>
		
		<h3>
			<i class="dashicons dashicons-testimonial"></i>
			<?php _e('Chat Theme', $thisPlugin); ?>
		</h3>
		
		<div id="chatTheme">
		
			<div id="chatShow">
				<label>
					<span><?php _e('Only on specific pages', $thisPlugin); ?></span>
					<input type="hidden" name="<?php echo esc_attr($thisPlugin.'[specificPages]'); ?>" value="0" />
					<input type="checkbox" name="<?php echo esc_attr($thisPlugin.'[specificPages]'); ?>"
						value="1" <?php checked( 1, $options['specificPages'] ); ?>
					/>
				</label>
				<p>
					<?php _e('Show chat only on specific pages, by default chat shown on all pages.',
					$thisPlugin); ?>
				</p>
				<p id="chatOnSpecific">
					<b><?php _e('Separate multiple values with commas.', $thisPlugin); ?></b>
					<br><br>
					<label>
						<span><?php _e('If html body has class', $thisPlugin); ?></span>
						<input type="text" name="<?php echo esc_attr($thisPlugin.'[chatOnByClass]'); ?>"
							   value="<?php echo esc_attr($options['chatOnByClass']); ?>"
						/>
					</label>
					<br>
					<?php _e('<b>Commonly used WP body classes</b>: home, blog, archive, page, single, logged-in',
					$thisPlugin); ?>
					<br><br>
					<label>
						<span><?php _e('Or url contains a text', $thisPlugin); ?></span>
						<input type="text" name="<?php echo esc_attr($thisPlugin.'[chatOnByUrl]'); ?>"
							   value="<?php echo esc_attr($options['chatOnByUrl']); ?>"
						/>
					</label>
					<br>
					<?php _e('<b>Commonly used URL tags</b>: contact, blog, about, support, shop', $thisPlugin); ?>
				</p>
			</div>
			
			<div id="chatTitle">
				<label>
					<span><?php _e('Title', $thisPlugin); ?></span>
					<input type="text" name="<?php echo esc_attr($thisPlugin.'[chatTitle]'); ?>"
						   value="<?php echo esc_attr($options['chatTitle']); ?>"
					/>
				</label>
			</div>
			
			<div id="chatWelcomeTxt">
				<label>
					<span><?php _e('Welcome Text', $thisPlugin); ?></span>
					<textarea name="<?php echo esc_attr($thisPlugin.'[chatWelcomeTxt]'); ?>"
						><?php echo esc_attr($options['chatWelcomeTxt']); ?></textarea>
				</label>
			</div>
			
			<div id="chatOfflineTxt">
				<label>
					<span><?php _e('Offline Message', $thisPlugin); ?></span>
					<textarea name="<?php echo esc_attr($thisPlugin.'[chatOfflineTxt]'); ?>"
						><?php echo esc_attr($options['chatOfflineTxt']); ?></textarea>
				</label>
				<p>
					<?php _e('If the user does not receive a response within the first minute
					and depending on the "Schedule" an "Offline Message" will be displayed.',
					$thisPlugin); ?>
				</p>
			</div>
			
			<div id="chatPlaceholder">
				<label>
					<span><?php _e('Placeholder', $thisPlugin); ?></span>
					<input type="text" name="<?php echo esc_attr($thisPlugin.'[chatPlaceholder]'); ?>"
						   value="<?php echo esc_attr($options['chatPlaceholder']); ?>"
					/>
				</label>
				<p>
					<?php _e('The placeholder text specifies a short hint like
					"Type your message here..." in the input field before the user enters a value.',
					$thisPlugin); ?>
				</p>
			</div>
			
			<div id="chatTelegramLink">
				<label>
					<span><?php _e('Telegram Link', $thisPlugin); ?></span>
					<input type="text" name="<?php echo esc_attr($thisPlugin.'[chatTelegramLink]'); ?>"
						   value="<?php echo esc_attr($options['chatTelegramLink']); ?>"
						   placeholder="https://t.me/username"
					/>
				</label>
				<p>
					<?php _e('Telegram links let people contact you without knowing your phone number.
					If the link is specified, then the telegram icon is shown in the chat header,
					clicking on which opens the Telegram App:',
					$thisPlugin); ?>
					<a target="_blank" href="https://telegram.org/faq#usernames-and-t-me">
						<?php _e('Read more', $thisPlugin); ?></a>
				</p>
			</div>
			
			<div id="chatImg">
				<div id="chatAnonimImg">
					<span class="img">
					<?php
						if($options['chatAnonimImg']){
							echo wp_get_attachment_image( $options['chatAnonimImg'], 'thumbnail' );
						}else{
							echo '<img src="' . esc_url($cfg['url'] . 'public/img/anonim-icon.svg') . '" alt="">';
						}
					?>
					</span>
					<input type="hidden" name="<?php echo esc_attr($thisPlugin.'[chatAnonimImg]'); ?>"
						value="<?php echo esc_attr($options['chatAnonimImg']); ?>"
					/>
					<label>
						<span><?php _e('User Avatar', $thisPlugin); ?></span>
						<br>
						<button type="button" id="chatGetAnonimImg"><?php _e('Change image', $thisPlugin); ?></button>
					</label>
				</div>
				
				<div id="chatBotImg">
					<span class="img">
					<?php
						if($options['chatBotImg']){
							echo wp_get_attachment_image( $options['chatBotImg'], 'thumbnail' );
						}else{
							echo '<img src="' . esc_url($cfg['url'] . 'public/img/bot-icon.svg') . '" alt="">';
						}
					?>
					</span>
					<input type="hidden" name="<?php echo esc_attr($thisPlugin.'[chatBotImg]'); ?>"
						value="<?php echo esc_attr($options['chatBotImg']); ?>"
					/>
					<label>
						<span><?php _e('Your Avatar', $thisPlugin); ?></span>
						<br>
						<button type="button" id="chatGetBotImg"><?php _e('Change image', $thisPlugin); ?></button>
					</label>
				</div>
			</div>
			
		</div>

		<h3>
			<i class="dashicons dashicons-admin-customizer"></i>
			<?php _e('Chat CSS Customize', $thisPlugin); ?>
		</h3>
		
		<div id="chatCustomize">

			<div id="chatCssVertical">
				<label>
					<span><?php _e('Vertical position', $thisPlugin); ?></span>
					<input type="text" name="<?php echo esc_attr($thisPlugin.'[chatCssVertical]'); ?>"
						   value="<?php echo esc_attr($options['chatCssVertical']); ?>"
						   placeholder="bottom: 20px; top: auto;"
					/>
				</label>
				<p>
					<?php _e('CSS properties are participates in specifying the <b>vertical</b> position of the chat window.',
					$thisPlugin); ?>
					<a target="_blank" href="https://developer.mozilla.org/en-US/docs/Web/CSS/bottom">
						<?php _e('Read more', $thisPlugin); ?></a>
				</p>
			</div>

			<div id="chatCssHorizontal">
				<label>
					<span><?php _e('Horizontal position', $thisPlugin); ?></span>
					<input type="text" name="<?php echo esc_attr($thisPlugin.'[chatCssHorizontal]'); ?>"
						   value="<?php echo esc_attr($options['chatCssHorizontal']); ?>"
						   placeholder="right: 70px; left: auto;"
					/>
				</label>
				<p>
					<?php _e('CSS properties are participates in specifying the <b>horizontal</b> position of the chat window.',
					$thisPlugin); ?>
					<a target="_blank" href="https://developer.mozilla.org/en-US/docs/Web/CSS/right">
						<?php _e('Read more', $thisPlugin); ?></a>
				</p>
			</div>

			<div id="chatCssBackground">
				<label>
					<span><?php _e('Chat background', $thisPlugin); ?></span>
					<input type="text" name="<?php echo esc_attr($thisPlugin.'[chatCssBackground]'); ?>"
						   value="<?php echo esc_attr($options['chatCssBackground']); ?>"
						   placeholder="background: #ffffff;"
					/>
				</label>
			</div>

			<div id="chatIconBackground">
				<label>
					<span><?php _e('Chat Icon background', $thisPlugin); ?></span>
					<input type="text" name="<?php echo esc_attr($thisPlugin.'[chatIconBackground]'); ?>"
						   value="<?php echo esc_attr($options['chatIconBackground']); ?>"
						   placeholder="background: #30a3e6;"
					/>
				</label>
				<p>
					<?php _e('The background shorthand CSS property sets all <b>background</b> style properties at once, such as color,
					image, origin and size, or repeat method of the chat window.',
					$thisPlugin); ?>
					<a target="_blank" href="https://developer.mozilla.org/en-US/docs/Web/CSS/background">
						<?php _e('Read more', $thisPlugin); ?></a>
				</p>
			</div>

			<div id="chatCustomCss">
				<label>
					<span><?php _e('Custom CSS', $thisPlugin); ?></span>
					<textarea name="<?php echo esc_attr($thisPlugin.'[chatCustomCss]'); ?>"
						placeholder="body #wps-telegram-chat{ ... }"
					><?php echo esc_attr($options['chatCustomCss']); ?></textarea>
				</label>
				<p>
					<?php _e('You can use CSS to alter the font, color, size and other decorative features.',
					$thisPlugin); ?>
					<a target="_blank" href="https://developer.mozilla.org/en-US/docs/Web/CSS">
						<?php _e('Read more', $thisPlugin); ?></a>
				</p>
			</div>

		</div>
		
		<h3>
			<i class="dashicons dashicons-clock"></i>
			<?php _e('Schedule', $thisPlugin);?>
			<i><?php echo ' - Current time: ' . wp_date( get_option('time_format') );?></i>
		</h3>
		
		<div id="chatSchedule">
		
			<div id="alwaysOnline">
				<label>
					<span><?php _e('Always onLine', $thisPlugin); ?></span>
					<input type="hidden" name="<?php echo esc_attr($thisPlugin.'[alwaysOnline]'); ?>" value="0" />
					<input type="checkbox" name="<?php echo esc_attr($thisPlugin.'[alwaysOnline]'); ?>"
						value="1" <?php checked( 1, $options['alwaysOnline'] ); ?>
					/>
				</label>
				<p></p>
			</div>
			
			<div id="daysOfWeek">
				
				<?php
					for ($d = 0; $d < 7; $d++) {
						$dayId = array_keys($daysOfWeek)[$d];
						$dayName = $daysOfWeek[$dayId];
				?>
				<div id="<?php echo esc_html($dayId); ?>" class="dayBox">
					<label>
						<span class="dayName"><?php echo esc_html($dayName); ?></span>
						<input type="checkbox"
							name="<?php echo esc_attr($thisPlugin.'[' . $dayId . ']'); ?>"
							value="<?php echo !empty( $options[ $dayId ] ) ? esc_attr($options[ $dayId ]) : ''; ?>"
						/>
					</label>
					<div class="startTime">
						<span><?php _e('Start', $thisPlugin); ?></span>
						<label>
							<span><?php _e('H:', $thisPlugin); ?></span>
							<select>
								<?php
									for ($h = $timeFormat; $h < 24-11*$timeFormat; $h++){
										$value = str_pad($h, 2, '0', STR_PAD_LEFT);
								?>
								<option value="<?php echo esc_attr($value); ?>">
									<?php echo esc_attr($value); ?>
								</option>
								<?php } ?>
							</select>
						</label>
						<label>
							<span><?php _e('M:', $thisPlugin); ?></span>
							<select>
								<?php
									for ($m = 0; $m < 60; $m += 5){
										$value = str_pad($m, 2, '0', STR_PAD_LEFT);
								?>
								<option value="<?php echo esc_attr($value); ?>">
									<?php echo esc_attr($value); ?>
								</option>
								<?php } ?>
							</select>
						</label>
						<?php if($timeFormat){ ?>
						<label class="timeFormat">
							<select>
								<option value="am">AM</option>
								<option value="pm">PM</option>
							</select>
						</label>
						<?php } ?>
					</div>
					<div class="endTime">
						<span><?php _e('End', $thisPlugin); ?></span>
						<label>
							<span><?php _e('H:', $thisPlugin); ?></span>
							<select>
								<?php
									for ($h = $timeFormat; $h < 24-11*$timeFormat; $h++){
										$value = str_pad($h, 2, '0', STR_PAD_LEFT);
								?>
								<option value="<?php echo esc_attr($value); ?>">
									<?php echo esc_attr($value); ?>
								</option>
								<?php } ?>
							</select>
						</label>
						<label>
							<span><?php _e('M:', $thisPlugin); ?></span>
							<select>
								<?php
									for ($m = 0; $m < 60; $m += 5){
										$value = str_pad($m, 2, '0', STR_PAD_LEFT);
								?>
								<option value="<?php echo esc_attr($value); ?>">
									<?php echo esc_attr($value); ?>
								</option>
								<?php } ?>
							</select>
						</label>
						<?php if($timeFormat){ ?>
						<label class="timeFormat">
							<select>
								<option value="pm">PM</option>
								<option value="am">AM</option>
							</select>
						</label>
						<?php } ?>
					</div>
				</div>
				<?php } ?>
				<p class="info">
					<?php _e('The start time must be less than the end time by at least 1 hour', $thisPlugin); ?>
				</p>
				<p></p>
			</div>
		</div>
		
		<h3>
			<i class="dashicons dashicons-email-alt"></i>
			<?php _e('Contact Form', $thisPlugin);?>
		</h3>
		
		<div id="wpsContactForm">
		
			<p class="info">
				<?php _e('Use the <b>[wps-telegram-feedback]</b> shortcode to insert a contact form into a WordPress
				post or page, which will send a message to the telegram bot instead of email.', $thisPlugin); ?>
			</p>
			
			<div id="wpsContactName">
				<label>
					<span><?php _e('Title for Name field', $thisPlugin); ?></span>
					<input type="text" name="<?php echo esc_attr($thisPlugin.'[wpsContactName]'); ?>"
						   value="<?php echo esc_attr($options['wpsContactName']); ?>"
					/>
				</label>
			</div>
			
			<div id="wpsNamePlaceholder">
				<label>
					<span><?php _e('Placeholder for Name', $thisPlugin); ?></span>
					<input type="text" name="<?php echo esc_attr($thisPlugin.'[wpsNamePlaceholder]'); ?>"
						   value="<?php echo esc_attr($options['wpsNamePlaceholder']); ?>"
					/>
				</label>
			</div>
			
			<div id="wpsContactEmail">
				<label>
					<span><?php _e('Title for E-mail field', $thisPlugin); ?></span>
					<input type="text" name="<?php echo esc_attr($thisPlugin.'[wpsContactEmail]'); ?>"
						   value="<?php echo esc_attr($options['wpsContactEmail']); ?>"
					/>
				</label>
			</div>
			
			<div id="wpsEmailPlaceholder">
				<label>
					<span><?php _e('Placeholder for E-mail', $thisPlugin); ?></span>
					<input type="text" name="<?php echo esc_attr($thisPlugin.'[wpsEmailPlaceholder]'); ?>"
						   value="<?php echo esc_attr($options['wpsEmailPlaceholder']); ?>"
					/>
				</label>
			</div>
			
			<div id="wpsContactSubject">
				<label>
					<span><?php _e('Title for Subject field', $thisPlugin); ?></span>
					<input type="text" name="<?php echo esc_attr($thisPlugin.'[wpsContactSubject]'); ?>"
						   value="<?php echo esc_attr($options['wpsContactSubject']); ?>"
					/>
				</label>
			</div>
			
			<div id="wpsSubjectPlaceholder">
				<label>
					<span><?php _e('Placeholder for Subject', $thisPlugin); ?></span>
					<input type="text" name="<?php echo esc_attr($thisPlugin.'[wpsSubjectPlaceholder]'); ?>"
						   value="<?php echo esc_attr($options['wpsSubjectPlaceholder']); ?>"
					/>
				</label>
			</div>
			
			<div id="wpsContactSubmit">
				<label>
					<span><?php _e('Submit button text', $thisPlugin); ?></span>
					<input type="text" name="<?php echo esc_attr($thisPlugin.'[wpsContactSubmit]'); ?>"
						   value="<?php echo esc_attr($options['wpsContactSubmit']); ?>"
					/>
				</label>
			</div>
			
			<div id="wpsContactNotice">
				<label>
					<span><?php _e('Notice after sending', $thisPlugin); ?></span>
					<textarea name="<?php echo esc_attr($thisPlugin.'[wpsContactNotice]'); ?>"
						><?php echo esc_attr($options['wpsContactNotice']); ?></textarea>
				</label>
			</div>
			
		</div>
		
		<h3>
			<i class="dashicons dashicons-bell"></i>
			<?php _e('Notifications', $thisPlugin);?>
		</h3>
		
		<div id="wpsNotifications">
			<div id="wpMailNotice">
				<label>
					<input type="checkbox" name="<?php echo esc_attr($thisPlugin.'[wpMailNotice]'); ?>"
						value="1" <?php echo checked( '1', $options['wpMailNotice'] ); ?>
					/>
					<span><?php _e('Receive emails for the site Admin into Telegram App', $thisPlugin); ?></span>
				</label>
			</div>
		</div>
		
		<input type="hidden" name="<?php echo esc_attr($thisPlugin.'[chatVer]'); ?>"
			value="<?php echo esc_attr($options['chatVer']); ?>" />
		<?php submit_button(__('Save all changes', $thisPlugin), 'primary', 'submit', TRUE); ?>
		
	</form>
	<button type="button" id="chatDeleteOptions" style="display:none;"> <?php _e('Delete Options', $thisPlugin); ?> </button>
</div>
