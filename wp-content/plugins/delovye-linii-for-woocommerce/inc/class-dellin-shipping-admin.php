<?php

defined( 'ABSPATH' ) || exit;
class DellinShipping_Admin {
	/**
	 * Constructor
	 */
	public function __construct() {
        include(DELLIN_PLUGIN_DIR.'/inc/dellin_ajax.php');
    //    add_action( 'admin_enqueue_scripts', array($this,'admin_dellin_scripts'));

	}
	public function get_lang_vars(){
	    return json_encode(array(
            'WC_DELLIN_SHIPPING_PROCESSING' => __('Processing','dellin-shipping-for-woocommerce'),
            'WC_DELLIN_SHIPPING_FIND_KLADR_CITY_BUTTON' => __('Find city KLADR','dellin-shipping-for-woocommerce'),
            'WC_DELLIN_SHIPPING_FIND_KLADR_STREET_BUTTON' => __('Find street KLADR','dellin-shipping-for-woocommerce'),
            'WC_DELLIN_SHIPPING_SEARCH_MSG' => __('Start entering the name','dellin-shipping-for-woocommerce'),
            'WC_DELLIN_SHIPPING_BUTTON_SELECT' => __('Select','dellin-shipping-for-woocommerce'),
            'WC_DELLIN_SHIPPING_BUTTON_CLOSE' => __('Close','dellin-shipping-for-woocommerce')

        ));
    }




    public function admin_dellin_scripts() {
	    //TODO - получить айдишник метода доставки и проверить, что это dellin_shipping_calc
        if(isset($_GET['page']) && sanitize_text_field($_GET['page']) == 'wc-settings' && (isset($_GET['tab']) && sanitize_text_field($_GET['tab']) == 'shipping')) {
            if(isset($_GET['instance_id'])){
                global $wpdb;

                $shipping_method_info =  $wpdb->get_row( "SELECT * FROM ".$wpdb->prefix."woocommerce_shipping_zone_methods WHERE instance_id='".sanitize_text_field($_GET['instance_id'])."'");
                if($shipping_method_info->method_id == 'dellin_shipping_calc'){
                    $v = '0.02';
                    wp_localize_script('jquery', 'dellinVars',
                        array(
                            'url' => admin_url('admin-ajax.php'),
                            'nonce' => wp_create_nonce('dellin-nonce'),
                            'wp_rest_nonce' => wp_create_nonce( 'wp_rest' ),
                            'langVars' => $this->get_lang_vars(),
                            'spinnerSrc' =>DellinShipping::plugin_dir_url().'/../assets/img/wait.gif'
                        )
                    );

                    wp_register_style('dellinDeliverySettingsStyle', DellinShipping::plugin_dir_url() . '/../assets/css/dellinDeliverySettings.css', array(), $v);
                    wp_enqueue_style('dellinDeliverySettingsStyle');

                    wp_register_script('dellinDeliverySettingsScript', DellinShipping::plugin_dir_url() . '/../assets/js/dellinDeliverySettings.js', array('jquery'), $v);
                    wp_enqueue_script('dellinDeliverySettingsScript');
                }
            }
        }

    }



    public static function dellinRenderTableStatus($trackId, $trackStatus){
		global $post;

        include_once dirname( __FILE__ ).'/view/metabox-dellin-status.php';
    }

    public static function getLoadingUnloadingTypes($value){
        switch($value){
            case 'NULL':
                return __('Back','dellin-shipping-for-woocommerce');
                break;
            case '0xb83b7589658a3851440a853325d1bf69':
                return __('Side','dellin-shipping-for-woocommerce');
                break;
            case '0xabb9c63c596b08f94c3664c930e77778':
                return __('Top','dellin-shipping-for-woocommerce');
                break;
        }
    }


    public static function createModal(){
        
        global $post;
        $order = wc_get_order($post->ID);
        $dataOrder = $order->get_data();
        global $woocommerce;

		$shipping_method = @array_shift($order->get_shipping_methods());
        $config = DellinApi::getConfig($shipping_method['instance_id']);

        add_thickbox();

        $shipping_method_title = '';
        $shipping_method_total = '';

        foreach( $order->get_items( 'shipping' ) as $item_id => $shipping_item_obj ){
            $shipping_method_title       = $shipping_item_obj->get_method_title();
            $shipping_method_total       = $shipping_item_obj->get_total();
        }

        $shipping_days = preg_replace('/[^0-9]/', '', $shipping_method_title);


        $dateNow = date('Y-m-d');
        $dateDelay = date('Y-m-d', strtotime($dateNow.'+ '.$config['delivery_delay'].' days'));
        $dateSend = date('Y-m-d', strtotime($dateDelay.'+ '.$shipping_days.' days'));

        $arRowsStatus = array(  //array('Статус:', $status),
                                //array('Трек номер:', $trackId),
                                array(esc_html('Дата отправки:'), $dateDelay),
                                array(esc_html('Дата доставки:'), $dateSend)
        );
       $arRowsContact = array( 
                    array(esc_html('Тип доставки:'), ($config['is_goods_unloading'] == 'no')?
                                                            esc_html('до терминала') : esc_html('до адреса')),
                    array(esc_html('Город:'), esc_html($dataOrder['shipping']['city'])),
                    array(esc_html('Имя:'), esc_html($dataOrder['shipping']['first_name'])),
                    array(esc_html('Фамилия:'),esc_html( $dataOrder['shipping']['last_name']))
        );

       $arRowsLoad = array(
           array(esc_html('Специальные требования к транспорту:'), self::getAdditionalTransportEquipments($config['loading_transport_equipments']) ),
           array(esc_html('Дополнительная комплектация:'), self::getAdditionalTransportRequirements($config['loading_transport_requirements'])),
           array(esc_html('Тип разгрузки машины:'), self::getLoadingUnloadingTypes($config['loading_type']))
        );
       $arRowsUnLoad = array(
        array(esc_html('Специальные требования к транспорту:'), self::getAdditionalTransportEquipments($config['unloading_transport_equipments'])),
        array(esc_html('Дополнительная комплектация:'), self::getAdditionalTransportRequirements($config['unloading_transport_requirements'])),
        array(esc_html('Тип разгрузки машины:'), self::getLoadingUnloadingTypes($config['unloading_type']))
    );
       $arRowsOther = array(
           array(esc_html('Тип платильщика:'), ($dataOrder['shipping']['company'] == 'on')?__('Organization','dellin-shipping-for-woocommerce'): __('individual','dellin-shipping-for-woocommerce') ),
           array(esc_html('Адрес доставки:'), esc_html($dataOrder['shipping']['address_1'])),
           array(esc_html('Стоимость доставки:'), esc_html($shipping_method_total)),
	    );

        include_once dirname( __FILE__ ).'/view/modal-dellin-shipping.php';

    }




}

