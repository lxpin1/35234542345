<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://wpsolution.org
 * @since      1.0.0
 *
 * @package    Wps_Telegram_Chat
 * @subpackage Wps_Telegram_Chat/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wps_Telegram_Chat
 * @subpackage Wps_Telegram_Chat/admin
 * @author     wpSolution <info@wpsolution.org>
 */
class Wps_Telegram_Chat_Admin {

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
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $apiUrl, $version ) {

		$this->plugin_name = $plugin_name;
		$this->apiUrl = $apiUrl;
		$this->version = $version;
		$this->options = array();
		$this->screen = false;

	}

	/**
	 * Register the stylesheets for the admin area.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wps-telegram-chat-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
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
		 
		$screen = get_current_screen();
		if ( strripos($screen->id, $this->plugin_name) ){
			$this->screen = true;
			wp_enqueue_media(); // Enqueue WordPress media scripts
			wp_enqueue_script( $this->plugin_name . '-jQui', plugin_dir_url( __FILE__ ) . 'js/jquery-ui.js',
				array( 'jquery' ), $this->version, false );
			wp_enqueue_script( $this->plugin_name . '-admin', plugin_dir_url( __FILE__ ) . 'js/wps-telegram-chat-admin.js',
				array( 'jquery' ), $this->version, false );
		}
	}
	
	/*
		wps code here
	*/
	
	public function add_plugin_admin_menu() {
		// add main menu
		add_menu_page( 'WPS Telegram Chat', __('Telegram Chat', $this->plugin_name), 'manage_options', $this->plugin_name, '', 'dashicons-format-chat');
		// add first menu
		add_submenu_page( $this->plugin_name, 'WPS Telegram Chat Settings', 'Settings',
			'manage_options', $this->plugin_name, array($this, 'display_plugin_setup_page') );
		// for future add second menu and more
		/*add_submenu_page( $this->plugin_name, 'WPS Telegram Chat List', 'List',
			'manage_options', $this->plugin_name . '-list', array($this, 'display_plugin_list_page') );*/
	}
	
	public function add_action_links( $links ) {
		// Add settings action link to the plugins page.
		$settings_link = array(
			'<a href="' . admin_url( 'admin.php?page=' . $this->plugin_name ) . '">' . __('Settings', $this->plugin_name) . '</a>'
		);
		return array_merge(  $settings_link, $links );
	}
	
	public function display_plugin_setup_page() {
		// Render the settings page for this plugin.
		include_once( 'partials/wps-telegram-chat-admin-display.php' );
	}
	
	public function remove_notices() {
		// remove the notices in admin header
		if ( $this->screen ){
			remove_all_actions('user_admin_notices');
			remove_all_actions('admin_notices');
		}
	}
	
	public function customNotice($args) {
		// send Custom notifications to Telegram
		include_once( 'partials/wps-telegram-chat-notice.php' );
		return $args;
	}
	
	public function validate($input) {
		// Validate options
		//$valid = array();
		//$valid['token'] = (isset($input['token']) && !empty($input['token'])) ? $input['token'] : '';
		//return $valid;
		return $input; // without validation
	}

	public function checkСonnection() {
		if(isset($_GET['mode']) ){
			$options = $this->options;
			$url = $this->apiUrl . $options['token'] . '/' . $_GET['mode'];

			if( $options['proxy'] === 'script' ){ $url = $options['proxyUrl'] . '?query=' . $url; }

			$response = wp_remote_post( $url );
			echo wp_send_json($response);
		}
	}
	
	public function telegramAdminImg() {
		if(isset($_GET['id']) ){
			$image = wp_get_attachment_image( filter_input( INPUT_GET, 'id', FILTER_VALIDATE_INT ), 'thumbnail', false, array() );
			$data = array(
				'image' => $image,
			);
			wp_send_json_success( $data );
		} else {
			wp_send_json_error();
		}
	}
	
	public function options_update() {
		// Update all options
		register_setting($this->plugin_name, $this->plugin_name, array($this, 'validate'));
	}
	
	public function options_delete() {
		// Delete all options
		delete_option($this->plugin_name);
	}
	
	public function options_check() {
		
		$defaults = array(
			'proxy' => 'none',
			'proxyUrl' => '',
			'proxyPort' => '',
			'proxyType' => 'HTTP',
			'proxyUser' => '',
			'proxyPass' => '',

			'enabled' => '0',
			'token' => '',
			'chatId' => '',

			'specificPages' => '0',
			'chatOnByClass' => '',
			'chatOnByUrl' => '',
			'chatTelegramLink' => '',
			'chatAnonimImg' => '',
			'chatBotImg' => '',

			'chatCssVertical' => '',
			'chatCssHorizontal' => '',
			'chatCssBackground' => '',
			'chatIconBackground' => '',
			'chatCustomCss' => '',

			'alwaysOnline' => '1',
			'chatTitle' => 'wpSolution Live Chat',
			'chatWelcomeTxt' => __('Hi, Your satisfaction is our top priority, we are ready to answer your questions...', $this->plugin_name),
			'chatOfflineTxt' => __('We are offline, but send your email address and message here, we will contact you soon...', $this->plugin_name),
			'chatPlaceholder' => __('Type your message here...', $this->plugin_name),
			
			'wpsContactName' => __('Name', $this->plugin_name),
			'wpsNamePlaceholder' => __('Enter your name', $this->plugin_name),
			'wpsContactEmail' => __('How to contact you?', $this->plugin_name),
			'wpsEmailPlaceholder' => __('Enter e-mail or Phone number', $this->plugin_name),
			'wpsContactSubject' => __('Subject of discussion', $this->plugin_name),
			'wpsSubjectPlaceholder' => __('Type your message here...', $this->plugin_name),
			'wpsContactSubmit' => __('Send Message', $this->plugin_name),
			'wpsContactNotice' => __('✔ Thank you for contact us. We will respond to you soon. ', $this->plugin_name),
			
			'wpMailNotice' => '0',
			
			'chatVer' => false
		);
	
		$this->options = wp_parse_args( get_option($this->plugin_name), $defaults );
		
		$cfg = $this->options;
		
		// Upgrade version
		if( $this->version !== $cfg['chatVer'] ){
			$cfg['chatVer'] = $this->version;
			update_option( $this->plugin_name, $cfg );
			
			// clear dataJson
			$dataJson = WP_PLUGIN_DIR . '/' . $this->plugin_name . '/public/partials/data.json';
			file_put_contents( $dataJson, '{"data":[],"update_id":0}' );
			
			// add notice
			add_action('admin_notices', function(){
				echo '<div class="notice notice-success is-dismissible"><p>' . 
					__('The <b>WPS Telegram Chat</b> has been updated, please check your', $this->plugin_name) . 
					'<a href="' . esc_url( admin_url( 'admin.php?page=' . $this->plugin_name ) ) . '"> <b>' . 
					__('Settings', $this->plugin_name) . '</b> </a></p></div>';
			});
		}
	}

}
