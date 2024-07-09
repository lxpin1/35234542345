<?php
/**
 * Plugin Name: Delovye Linii for WooCommerce
 * Description: Official Delovye Linii delivery plugin for WooCommerce allows calculating the cost and the date of delivery of the products in the cart, finding out the available methods and terminals of 
 * delivery, selecting Delovye Linii as delivery service, checking order status and estimated delivery date.
 * Version: 2.0.0
 * Author: Деловые Линии
 * Text Domain: dellin-shipping-for-woocommerce
 * Domain Path: /languages
 * WC requires at least: 4.6.0
 * WC tested up to: 8.2.1
 */
 

use Biatech\Lazev\Main;

defined( 'ABSPATH' ) || exit;


// Define DELLIN_PLUGIN_DIR.
if ( ! defined( 'DELLIN_PLUGIN_DIR' ) ) {
    define( 'DELLIN_PLUGIN_DIR', str_replace( '\\', '/', dirname( __FILE__ ) ));
}

if(! defined('DELLIN_PLUGIN_FILE'))
{
    define('DELLIN_PLUGIN_FILE', __FILE__);
}


if (file_exists(dirname(__FILE__) . '/vendor/autoload.php')) {
    require_once (dirname(__FILE__) . '/vendor/autoload.php');
}

if ( class_exists( 'Biatech\\Lazev\\Main' ) ) {
    Main::register_services();
}


/**
 *
 * Dellin Main Class
 *
 */
class DellinShipping {

    /**
     * Constructor.
     */

    public function __construct() {
        // apply plugin textdomain.

        add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );

        add_filter( 'auto_update_plugin', array( $this, 'auto_update_plugin' ), 10, 2 );

	    add_filter( 'woocommerce_ship_to_different_address_checked', '__return_true' );

    }

    /**
     * Load textdomain for a plugin
     */
    public function load_textdomain() {
        load_plugin_textdomain( 'dellin-shipping-for-woocommerce', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }

    /**
     * Auto update plugin
     *
     * @param bool   $should_update If should update.
     * @param object $plugin Plugin data.
     *
     * @return bool
     */
    public function auto_update_plugin( $should_update, $plugin ) {
        if ( 'dellin-shipping-for-woocommerce/dellin-shipping-for-woocommerce.php' === $plugin->plugin ) {
            return true;
        }

        return $should_update;
    }


    /**
     * Plugin dir url helper
     *
     * @return string
     */
    public static function plugin_dir_url() {
        return plugin_dir_url( __FILE__ );
    }


}


// Init plugin if woo is active.
if ( in_array(
    'woocommerce/woocommerce.php',
    apply_filters( 'active_plugins', get_option( 'active_plugins' ) ),
    true
) ) {
    new DellinShipping();
}

?>
