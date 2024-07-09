<?php

/*
*
* City Confirmation Window Template
*
*/

defined( 'ABSPATH' ) || exit;

?>

<div class="popup-window-overlay" id="popup-window-overlay-cityConfirm" style="z-index: 1099; width: 100%; height: 100%; filter: none; opacity: 1; display: block;left:0;top:0;"></div>
<div id="cityConfirm" style="display: block;position: absolute;" class="popup-window pop-up city-confirm">
	<div id="popup-window-content-cityConfirm" class="popup-window-content">
		<div class="your-city">
			<div class="your-city__label"><?php esc_html_e('Your city', 'belingogeo'); ?></div>
			<div class="your-city__val"> <?php echo esc_html($data['city']->get_name()); ?>?</div>
		</div>
	</div>
	<span class="popup-window-close-icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 352 512"><path d="M242.72 256l100.07-100.07c12.28-12.28 12.28-32.19 0-44.48l-22.24-22.24c-12.28-12.28-32.19-12.28-44.48 0L176 189.28 75.93 89.21c-12.28-12.28-32.19-12.28-44.48 0L9.21 111.45c-12.28 12.28-12.28 32.19 0 44.48L109.28 256 9.21 356.07c-12.28 12.28-12.28 32.19 0 44.48l22.24 22.24c12.28 12.28 32.2 12.28 44.48 0L176 322.72l100.07 100.07c12.28 12.28 32.2 12.28 44.48 0l22.24-22.24c12.28-12.28 12.28-32.19 0-44.48L242.72 256z"/></svg></span>
	<div class="popup-window-buttons">
		<button name="cityConfirmYes" class="btn_buy popdef select_geo_city" data-name-orig="<?php echo esc_attr($data['city']->get_name()); ?>" data-name="<?php echo esc_attr($data['city']->get_slug()); ?>"><?php esc_html_e('Yes', 'belingogeo'); ?></button>
		<button name="cityConfirmChange" class="btn_buy apuo geolocationChangeCity"><?php esc_html_e('Choose another city', 'belingogeo'); ?></button>
		<div style="text-align: center;">
			<a class="continue-without-geo" href="#">
				<?php esc_html_e('Continue without the city', 'belingogeo'); ?>	
			</a>
		</div>
	</div>
</div>