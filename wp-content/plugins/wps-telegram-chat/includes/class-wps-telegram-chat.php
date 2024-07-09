<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://wpsolution.org
 * @since      1.0.0
 *
 * @package    Wps_Telegram_Chat
 * @subpackage Wps_Telegram_Chat/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Wps_Telegram_Chat
 * @subpackage Wps_Telegram_Chat/includes
 * @author     wpSolution <info@wpsolution.org>
 */
class Wps_Telegram_Chat {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Wps_Telegram_Chat_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'WPS_TELEGRAM_CHAT_VERSION' ) ) {
			$this->version = WPS_TELEGRAM_CHAT_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'wps-telegram-chat';
		$this->apiUrl = 'https://api.telegram.org/bot';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Wps_Telegram_Chat_Loader. Orchestrates the hooks of the plugin.
	 * - Wps_Telegram_Chat_i18n. Defines internationalization functionality.
	 * - Wps_Telegram_Chat_Admin. Defines all hooks for the admin area.
	 * - Wps_Telegram_Chat_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wps-telegram-chat-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wps-telegram-chat-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wps-telegram-chat-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wps-telegram-chat-public.php';

		$this->loader = new Wps_Telegram_Chat_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Wps_Telegram_Chat_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Wps_Telegram_Chat_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Wps_Telegram_Chat_Admin( $this->get_plugin_name(), $this->get_apiUrl(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		
		/*
			wps code here
		*/
		
		// Add menu item from admin/class-wps-telegram-chat-admin.php
		$this->loader->add_action('admin_menu', $plugin_admin, 'add_plugin_admin_menu');
		
		// Add Settings link to the plugin from admin/class-wps-telegram-chat-admin.php
		$plugin_basename = plugin_basename(plugin_dir_path( __DIR__ ) . $this->plugin_name . '.php');
		$this->loader->add_filter('plugin_action_links_' . $plugin_basename, $plugin_admin, 'add_action_links');
		
		// Save or Update our plugin options
		$this->loader->add_action('plugins_loaded', $plugin_admin, 'options_check' );
		$this->loader->add_action('admin_init', $plugin_admin, 'options_update');
		
		// remove the notices in admin header
		$this->loader->add_action('in_admin_header', $plugin_admin, 'remove_notices');
		
		// send notices in telegram
		$this->loader->add_action('wp_mail', $plugin_admin, 'customNotice');
		
		// custom action
		$this->loader->add_action('wp_ajax_checkСonnection', $plugin_admin, 'checkСonnection');
		$this->loader->add_action('wp_ajax_telegramAdminImg', $plugin_admin, 'telegramAdminImg');
		$this->loader->add_action('wp_ajax_options_delete', $plugin_admin, 'options_delete');
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {
		
		if( empty( get_option($this->get_plugin_name())['enabled'] ) ){ return; }
			
		$plugin_public = new Wps_Telegram_Chat_Public( $this->get_plugin_name(), $this->get_apiUrl(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		
		/*
			wps code here
		*/
		$this->loader->add_action( 'init', $plugin_public, 'init');
		$this->loader->add_action( 'init', $plugin_public, 'sessionStart', 1 );
		$this->loader->add_action( 'wp_login', $plugin_public, 'sessionDestroy' );
		$this->loader->add_action( 'wp_logout', $plugin_public, 'sessionDestroy' );
		$this->loader->add_action( 'wp_footer', $plugin_public, 'display_plugin_front_page' );

		$this->loader->add_action( 'http_api_curl', $plugin_public, 'modify_curl', 10, 3 );
		
		// custom action
		$this->loader->add_action( 'wp_ajax_telegramHandler', $plugin_public, 'telegramHandler' );
		$this->loader->add_action( 'wp_ajax_nopriv_telegramHandler', $plugin_public, 'telegramHandler' );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}
	
	public function get_apiUrl() {
		return $this->apiUrl;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Wps_Telegram_Chat_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
