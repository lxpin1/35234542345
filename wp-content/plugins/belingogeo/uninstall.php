<?php 

if( ! defined('WP_UNINSTALL_PLUGIN') )
	exit;

require_once 'includes/belingogeo-city-class.php';
require_once 'includes/core-functions.php';

$args = [
	'posts_per_page' => -1
];
$cities = belingoGeo_get_cities($args);
if($cities) {
	foreach ($cities as $city) {
		wp_delete_post( $city->get_id(), true );
	}
}


?>