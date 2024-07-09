<?php

defined( 'ABSPATH' ) || exit;
/**
 * Dellin_Shipping_Method.
 */
class Dellin_Shipping_Method extends WC_Shipping_Method {
	/**
	 * Dellin_Shipping_Method constructor.
	 *
	 * @param int $instance_id id.
	 */
	public function __construct( $instance_id = 0 ) {

		$this->id                 = 'dellin_shipping_calc';
		$this->instance_id        = absint( $instance_id );
		$this->method_title       = __( 'Dellin shipping', 'dellin-shipping-for-woocommerce' );
		$this->method_description = __( 'The plugin allows you to automatically calculate shipping costs using dellin.ru API service.', 'dellin-shipping-for-woocommerce' );
		$this->supports           = array(
			'shipping-zones',
			'instance-settings',
		);

		$settings                   = include 'settings/settings-shipping-method.php';
		$this->instance_form_fields = $settings;

		foreach ( $settings as $key => $settings ) {
			$this->{$key} = $this->get_option( $key );
		}
		add_action('woocommerce_update_options_'.$instance_id, [$this, 'process_admin_options']);
       // add_action('woocommerce_update_options_shipping_'.$instance_id, array( $this, 'process_admin_options' ) );
		add_action('woocommerce_before_cart', array($this, 'terminal_field_in_cart'), 1);
//		add_action('woocommerce_terminal_in_shipping_calculator', array($this, 'terminal_field_in_cart'), 1);
//		add_action('woocommerce_cart_contents', array($this, 'check_terminal_id'), 11);


	}





	/**
	 * @param $instance_id
	 *
	 * @return bool
	 */

	public function is_shipping_terminal($instance_id){
		$config = DellinApi::getConfig($instance_id);
        $result = false;
		if($config['is_goods_unloading'] == 'no'){
			$result =  true;
		}
		return $result;
	}

	public function terminal_field_in_cart(){


		$current_user = wp_get_current_user();
		$user_id = $current_user->ID;
	//	$nonce = wp_create_nonce('dellin-terminal');

		$terminalId =  get_user_meta( $user_id, 'terminal_id', true );





	}

	/**
	 * Calculate_shipping function.
	 *
	 * @param array $package (default: array()).
	 */
	public function calculate_shipping( $package = array() ) {
		global $woocommerce;

        $isTerminal = $this->is_shipping_terminal($this->instance_id);
        if($isTerminal){
            update_option( 'dellin_shipping_instance_id', $this->instance_id );
        }

        $params = get_option('woocommerce_dellin_shipping_calc_' . $this->instance_id. '_settings');
		$DellinApi = new DellinApi();
        $cart = $woocommerce->cart;
       // $shippingInfo = $cart->get_shipping_packages();
        $cart->state = $package['destination']['state'];
        $cart->deliveryCity = $package['destination']['city'];

//        $cart->address = ($package['destination']['address']  == '')? $DellinApi->getUserShippingData($package['user']['ID']) :
//	                                                                  $package['destination']['address'];

        $cart->address = $package['destination']['address'];
        $cart->totalPrice = $package['cart_subtotal'];
		$cart->postcode = $package['destination']['postcode'];
		$cart->instance_id = $this->instance_id;
		$current_user = wp_get_current_user();
		$user_id = $current_user->ID;
        $cart->terminal_id = get_user_meta( $user_id, 'terminal_id', true );
        $cart->worktime_start = '08:00';
        $cart->worktime_end = '19:00';
//		echo '<pre>';
//		var_dump($package);
//		echo '</pre>';
//		die();

		if($this->is_shipping_terminal($this->instance_id)) {
//			$getUserMeta = get_user_meta( $user_id, 'terminal_id', true );
//			$getCustomerMeta = $woocommerce->customer
			$cart->terminal_id = get_user_meta( $user_id, 'terminal_id', true );
		}
		$isShippingDebug = (get_option('woocommerce_shipping_debug_mode') == 'yes' )? true : false;
        $calculationResult = $DellinApi->Calculate($cart,$params);

            if ($calculationResult['status'] == 'OK') {

                $pluralForm = self::plural($calculationResult['body']['time'],
                    esc_html('день'),
                    esc_html('дня'),
                    esc_html('дней'));
                $time = implode(" ", [$calculationResult['body']['time'], $pluralForm]);
                $this->add_rate(
                    array(
                        'id' => esc_html($this->get_rate_id()),
                        'label' => esc_html($this->title . ' ' . ' (' . $time . ')'),
                        'cost' => esc_html($calculationResult['body']['price']),
//                        'meta_data' => array('terminal_id2'=>  woocommerce_form_field('terminal_id1' , array(
//                            'title'       => __( 'departure terminal', 'dellin-shipping-for-woocommerce' ),
//                            'description' => __( 'Select departure terminal', 'dellin-shipping-for-woocommerce'),
//                            'type'        => 'select',
//                            'default'     => '',
//                            'options'     => array(),
//                        ))),
                       // 'package' => $package
                    )
                );


            } else {
                if (is_admin()) {

                    wc_add_notice(esc_html('Внимание! Произошла непредвиденная ошибка при расчёте стоимости доставки. 
                                        Используйте режим отладки для настройки модуля.'), 'error');
                    wc_add_notice(esc_html('' . $calculationResult['body'] . ''), 'error');
                }
            }
        }


    protected static function plural($n, $form1, $form2, $form3) {
        return in_array($n % 10, array(2,3,4)) && !in_array($n % 100, array(11,12,13,14)) ? $form2 : ($n % 10 == 1 ? $form1 : $form3);
    }


		/**
	 * Output the shipping settings screen.
	 */
	public function admin_options() {
	
		if ( ! $this->instance_id ) {
			echo '<h2>' . esc_html( $this->get_method_title() ) . '</h2>';
		}
		echo '<div id="react-app"></div>';
		echo '<div class="legacy_data" style="display:none;">';
		echo wp_kses_post( wpautop( $this->get_method_description() ) );
		echo $this->get_admin_options_html(); // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
		echo '</div>';
	}

	
	// public function process_admin_options()
	// {

	// 	// echo '<pre>';
	// 	// var_dump('test');
	// 	// echo '</pre>';
	// 	// die();
	
	// 	if ( ! $this->instance_id ) {
	// 		return parent::process_admin_options();
	// 	}
	
	// 	// Check we are processing the correct form for this instance.
	// 	if ( ! isset( $_REQUEST['instance_id'] ) || absint( $_REQUEST['instance_id'] ) !== $this->instance_id ) { // WPCS: input var ok, CSRF ok.
	// 		return false;
	// 	}
	
	// 	$this->init_instance_settings();
	
	// 	$post_data = $this->get_post_data();


	// 	foreach ( $this->get_instance_form_fields() as $key => $field ) {
	// 		if ( 'title' !== $this->get_field_type( $field ) ) {
	// 			try {
	// 				$this->instance_settings[ $key ] = $this->get_field_value( $key, $field, $post_data );
	// 			} catch ( Exception $e ) {
	// 				$this->add_error( $e->getMessage() );
	// 			}
	// 		}
	// 	}
	// }
}
