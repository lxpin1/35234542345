<?php

/**
 * Plugin Name: Belingo.GeoCiy Pro
 * Description: Add-on for the belingoGeo plugin, expanding its functionality
 * Author URI:  https://belingo.ru
 * Author:      Belingo llc
 * Version:     1.10.1
 * Text Domain: belingogeopro
 * Domain Path: /languages
 */

define("BELINGO_GEO_PRO_PLUGIN_DIR", __DIR__);
define("BELINGO_GEO_PRO_PLUGIN_URL", plugins_url() . '/belingogeopro');
define("BELINGO_GEO_PRO_VERSION", '1.10.1');

if(class_exists('BelingoGeo_City')) {
	require_once 'includes/functions.php';
	require_once 'includes/woocommerce.php';
}

register_activation_hook( __FILE__, 'belingogeopro_install' ); 
function belingogeopro_install() {

}

register_deactivation_hook( __FILE__, 'belingogeopro_deactivation' );
function belingogeopro_deactivation() {

}

add_action( 'init', 'belingogeopro_load_plugin_textdomain' );
function belingogeopro_load_plugin_textdomain() {
	load_plugin_textdomain( 'belingogeopro', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' ); 
}