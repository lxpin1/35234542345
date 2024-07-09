<?php

/*
*
* Showing city in list
*
*/

defined( 'ABSPATH' ) || exit;

?>

<li class="quick-locations__val select_geo_city" data-name-orig="<?php echo esc_attr($data['city']->get_name()); ?>" data-name="<?php echo esc_attr($data['city']->get_slug()); ?>">
	<?php echo esc_html($data['city']->get_name()); ?>
</li>  