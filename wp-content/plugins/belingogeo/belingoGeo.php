<?php

/**
 * Plugin Name: Belingo.GeoCiy
 * Description: The plugin adds the ability to select cities, unique pages are created with a unique url for each city. This allows you to uniqueize content for search engines.
 * Author URI:  https://belingo.ru
 * Author:      Belingo llc
 * Version:     1.10.2
 * Text Domain: belingogeo
 * Domain Path: /languages
 */

define("BELINGO_GEO_PLUGIN_DIR", __DIR__);
define("BELINGO_GEO_PLUGIN_URL", plugins_url() . '/belingogeo');

$belingogeo_cityObj = '';

add_filter('query_vars', function( $vars ) {
	$vars[] = 'geo_city';
	$vars[] = 'city_sitemap';
	$vars[] = 'sitemap_page';
	$vars[] = 'sitemap_type';
	$vars[] = 'sitemap_tax';
	return $vars;
});

require_once 'includes/belingogeo-city-class.php';
require_once 'includes/core-functions.php';
require_once 'includes/functions.php';
require_once 'includes/ajax-functions.php';
require_once 'includes/admin/functions.php';
require_once 'includes/admin/settings.php';
require_once 'includes/sitemaps.php';
require_once 'includes/shortcodes.php';
require_once 'includes/hooks.php';
require_once 'integrations/woocommerce.php';
require_once 'integrations/yoast.php';

define("BELINGO_GEO_VERSION", '1.10.2');

add_action('template_redirect', 'belingoGeo_init_city');

register_activation_hook( __FILE__, 'belingoGeo_install' ); 
function belingoGeo_install() {

	belingoGeo_register_post_types();
	belingogeo_create_default_city();

	// default values
	update_option('belingo_geo_exclude_nonobject', 1);
	update_option('belingo_geo_url_type', 'subdirectory');
	update_option('belingo_geo_sitemap_per_page', 1000);
	update_option('belingo_geo_basic_popup_window_header', __('Your delivery region', 'belingogeo'));
	update_option('belingo_geo_basic_popup_window_text1', __('Didn\'t find your city?', 'belingogeo'));
	update_option('belingo_geo_basic_popup_window_text2', __('We deliver worldwide', 'belingogeo'));

	flush_rewrite_rules();

}

register_deactivation_hook( __FILE__, 'belingoGeo_deactivation' );
function belingoGeo_deactivation() {

	flush_rewrite_rules();

}

add_action( 'init', 'belingoGeo_load_plugin_textdomain' );
function belingoGeo_load_plugin_textdomain() {
	load_plugin_textdomain( 'belingogeo', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' ); 
}