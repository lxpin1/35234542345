<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://wpsolution.org
 * @since      1.0.0
 *
 * @package    Wps_Telegram_Chat
 * @subpackage Wps_Telegram_Chat/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Wps_Telegram_Chat
 * @subpackage Wps_Telegram_Chat/public
 * @author     wpSolution <info@wpsolution.org>
 */
class Wps_Telegram_Chat_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;
	
	private $apiUrl;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;
	
	/**
	 * The options of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array    $options    Getting plugin options.
	 */
	private $options;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $apiUrl, $version ) {

		$this->plugin_name = $plugin_name;
		$this->apiUrl = $apiUrl;
		$this->version = $version;
		$this->options = get_option($plugin_name);

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wps_Telegram_Chat_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wps_Telegram_Chat_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wps-telegram-chat-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wps_Telegram_Chat_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wps_Telegram_Chat_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wps-telegram-chat-public.js', array( 'jquery' ), $this->version, false );

	}
	
	/*
		wps code here
	*/
	public function modify_curl($handle, $args, $url){
		// Modify cURL handle
		
		$isTelegramApi = strpos( $url, $this->apiUrl );
		$options = $this->options; // Get plugin option fields

		if ($isTelegramApi !== false){ // if telegram api

			if( $options['proxy'] === 'proxy' ){
			
				if ( !empty($options['proxyUrl']) && !empty( $options['proxyPort'] ) ) {
					
					curl_setopt ( $handle, CURLOPT_SSL_VERIFYHOST, false);
					curl_setopt ( $handle, CURLOPT_SSL_VERIFYPEER, false);

					curl_setopt( $handle, CURLOPT_PROXY, $options['proxyUrl'] );
					curl_setopt( $handle, CURLOPT_PROXYPORT, $options['proxyPort'] );
					curl_setopt( $handle, CURLOPT_PROXYTYPE, 'CURLPROXY_' . $options['proxyType'] );
					
					// Auth
					if ( !empty($options['proxyUser']) && !empty($options['proxyPass']) ) {
						$auth = $options['proxyUser'] . ':' . $options['proxyPass'];
						curl_setopt( $handle, CURLOPT_PROXYAUTH, CURLAUTH_ANY );
						curl_setopt( $handle, CURLOPT_PROXYUSERPWD, $auth );
					}
				}
			}
		}
	}

	public function display_plugin_front_page(){
		// Render plugin for frontend.
		include_once( 'partials/wps-telegram-chat-public-display.php' );
	}
	
	public function telegramHandler(){
		// Handler plugin for frontend.
		include_once( 'partials/wps-telegram-chat-public-handler.php' );
	}
	
	public function init() {
		// Render shortcodes
		include_once( 'partials/wps-telegram-chat-shortcodes.php' );
	}
	
	public function sessionStart(){
		$cfg = array();
		$cfg['thisPlugin'] = $this->plugin_name;
		
		if(!isset( $_COOKIE[ $cfg['thisPlugin'] ]) ){
			$cfg['cookie'] = array();
			$cfg['cookie']['nonce'] = wp_create_nonce($cfg['thisPlugin']);
			setcookie( $cfg['thisPlugin'], json_encode($cfg['cookie']), time() + 1800, '/' );
		}
	}
	
	public function sessionDestroy(){
		// cookie destroy
		unset( $_COOKIE[ $this->plugin_name ] );
		setcookie( $this->plugin_name, null, -1, '/' );
	}

}
