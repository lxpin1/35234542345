<?php

/**
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://wps.dir.md
 * @since             4.4.2
 * @package           Wps_Telegram_Chat
 *
 * @wordpress-plugin
 * Plugin Name:       WPS Telegram Chat
 * Plugin URI:        https://wps.dir.md/plugin/wps-telegram-chat
 * Description:       With a Telegram Chat for website, your site visitors will be able to contact you directly from your website at all times.
 * Version:           4.4.2
 * Author:            wpSolution
 * Author URI:        https://wps.dir.md
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wps-telegram-chat
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'WPS_TELEGRAM_CHAT_VERSION', '4.4.2' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wps-telegram-chat-activator.php
 */
function activate_wps_telegram_chat() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wps-telegram-chat-activator.php';
	Wps_Telegram_Chat_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wps-telegram-chat-deactivator.php
 */
function deactivate_wps_telegram_chat() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wps-telegram-chat-deactivator.php';
	Wps_Telegram_Chat_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_wps_telegram_chat' );
register_deactivation_hook( __FILE__, 'deactivate_wps_telegram_chat' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wps-telegram-chat.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_wps_telegram_chat() {

	$plugin = new Wps_Telegram_Chat();
	$plugin->run();

}
run_wps_telegram_chat();
