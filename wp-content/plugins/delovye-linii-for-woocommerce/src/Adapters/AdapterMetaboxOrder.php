<?php

namespace Biatech\Lazev\Adapters;

use Biatech\Lazev\Base\Cache\CacheWp;
use Biatech\Lazev\Base\IPluggable;
use Biatech\Lazev\Factories\FactoryMetaboxStatus;
use Biatech\Lazev\Factories\FactoryModalAdmin;
use Biatech\Lazev\Factories\FactoryOrder;
use Biatech\Lazev\Factories\FactoryProfileMetaData;
use Biatech\Lazev\Factories\FactorySettings;
use Biatech\Lazev\Services\DellinCalculationService;
use Biatech\Lazev\Services\DellinOrdersLogService;
use Biatech\Lazev\Services\DellinRequestService;
use Biatech\Lazev\Services\DellinTerminalsService;

defined('ABSPATH') || exit;

final class AdapterMetaboxOrder implements IPluggable
{



    public function register()
    {
        // подключение метабоксов
        add_action( 'add_meta_boxes', array($this, 'registerMetaBox'));
        add_action('woocommerce_checkout_update_order_meta', array($this,'dellinShippingTrackId'));
        
        // подключение мета данных внутрь информации по заказу профиля
        add_action('woocommerce_view_order', array($this, 'check_shipping_status'));


        //Отправка request (legacy)
        add_action( 'wp_ajax_send_request', [$this, 'dellin_send_request'] ); // для авторизованных пользователей
        add_action( 'wp_ajax_nopriv_send_request', [$this,  'dellin_send_request'] ); // для неавторизованных

    }
    
    function registerMetaBox() {
            global $post;
            $id = $post->ID;
            if(isset($_GET['post']) && $_GET['post'] == $id && (isset($_GET['action']) && $_GET['action'] == 'edit') ){
    
                $order = wc_get_order( $post->ID );
    
                if($order){
                    $shipping_method = @array_shift($order->get_shipping_methods());
                    $shipping_method_id = $shipping_method['method_id'];
                    if($shipping_method_id == 'dellin_shipping'){
                        add_meta_box(
                            'order_data',
                            esc_html__( 'Доставка деловые линии', 'text-domain' ),
                            array($this, 'renderMetaBox'),
                            'shop_order', // shop_order is the post type of the admin order page
                            'normal', // change to 'side' to move box to side column 
                            'low' // priority (where on page to put the box)
                        );
                        $v = '0.02';//версия
                        wp_localize_script('jquery', 'dellinVars',
                            array(
                                'url' => admin_url('admin-ajax.php'),
                                'postId' => $id
                            ));
    
                        wp_register_script('dellinDeliverySettingsScript', \DellinShipping::plugin_dir_url() . '/../assets/js/dellinDeliveryRequest.js', array('jquery'), $v);
                        wp_enqueue_script('dellinDeliverySettingsScript');
    
                        // add meta field for trackId and status
                        // Save and display custom fields in order item meta
     //                   add_action( 'woocommerce_add_order_item_meta', 'addCustomFieldsOrderItemMeta', 20, 3 );
    //                    update_post_meta( $post->ID, '_dellin_track_id', 'None', '' );
    //                    update_post_meta($post->ID, '_dellin_status_track', 'None', '' );
    
                        wp_register_style('dellinDeliverySettingsStyle', \DellinShipping::plugin_dir_url() . 'assets/css/dellinDeliveryRequest.css', array(), $v);
                        wp_enqueue_style('dellinDeliverySettingsStyle');
    
                    }
                }
            }
        }
        
                
        /**
             * Method get DOM block in metabox.
             * Using for RequestV2.
             */
        public static function renderMetaBox() {
                global $post;
                $order = wc_get_order($post->ID);
                $trackId = get_post_meta( $post->ID, '_dellin_track_id', true );
                $shipping_method = @array_shift($order->get_shipping_methods());
                $instance_id = $shipping_method['instance_id'];
        
                if($trackId == ''){
                    // Если запрос делается впервые.
        	        self::createModal();
                } else {

                    $factorySettings = new FactorySettings();
                    $settings = $factorySettings->create($instance_id);
                    $cache = new CacheWp();
                    $ordersService = new DellinOrdersLogService($settings, $cache);
                    $detailsInfo = $ordersService->getOrderByRequestID($trackId);
                    $factoryView = new FactoryMetaboxStatus($trackId, $detailsInfo->stateName);
                    $factoryView->create();
                }
            }


        function dellin_send_request(){

            //Legacy

        try{
                if(sanitize_text_field($_REQUEST['mode']) == "try_request" && sanitize_text_field($_REQUEST['id']) != null ){

                    $orderUpdate = sanitize_text_field($_REQUEST['orderChange']);

                    $id = sanitize_text_field($_REQUEST['id']);
                    $itemId = sanitize_text_field($_REQUEST['itemID']);

                    $order = wc_get_order($id);
                    $instance_id = array_shift($order->get_shipping_methods())->get_instance_id();
                    $factorySettings = new FactorySettings();
                    $settings = $factorySettings->create($instance_id);

                    $adapterOrder = new OrderAdapter(null, $settings, [], $order  );
                    $factoryOrder = new FactoryOrder();
                    $factoryOrder->setDataOrderAdapter($adapterOrder);
                    $dellinOrder = $factoryOrder->create();
                    $cache = new CacheWp();
                    $calculateService = new DellinCalculationService($settings, $dellinOrder, $cache);
//                    echo '<pre>';
//                    var_dump($calculateService);
//                    die();
                    $calculateResult = $calculateService->getCalculate();

                    $result = $calculateResult;

                    if($calculateResult->price > $dellinOrder->orderInfo->priceShipment)
                    {
                        $calcResult['data']['state'] = 'processing';
                        $calcResult['PRICE_CHANGED'] = true;
                    }

                    if($calcResult["PRICE_CHANGED"])
                    {
                        if(!is_null( $itemId))
                        {
                            if ( wc_update_order_item_meta(
                                                    $itemId, 'cost',
                                                    $calcResult['body']['price'], $dellinOrder->orderInfo->priceShipment ) ) {
                                $body   = array( "STATUS" => "OK", 'orderUpdate' => true,
												  'data'=> array('state'=>'processing'));
                                $result = $body;
                            }
                        }
                    }


                }
//                echo'<pre>';
//                var_dump($settings, $dellinOrder, $cache);
//                die();
                   $requestService = new DellinRequestService($settings, $dellinOrder, $cache);
                   $result = $requestService->getTrackingNumber();

                   $trackNumber =  $result->data->requestID;
                   if(isset($trackNumber))
                   {
                       update_post_meta( $id, '_dellin_track_id',  $trackNumber, '' );

                   }

                } catch (\Exception $e)
                {
                $result = [];

                    if($e->getPrevious() instanceof  \GuzzleHttp\Exception\ClientException)
                    {
                        $body = json_decode($e->getMessage());
                        $errors = $body->errors;
                        foreach($errors as $error)
                        {
                            $errorBody .= '['.$error->code.'] '.$error->detail.' | '.$error->fields[0].'<br/>';
                        }


                     $result = array('status' => 'error',
                                    'errors' => $errorBody,
                                    'data'=>array('state' => 'process'));


                    }

                }
               echo wp_json_encode($result, JSON_UNESCAPED_UNICODE);


            wp_die();

            }


        public static function createModal()
        {

            global $post;
            $order = wc_get_order($post->ID);
            $dataOrder = $order->get_data();
            global $woocommerce;

            $shipping_method = @array_shift($order->get_shipping_methods());

            $factorySettings = new FactorySettings();

            $config = $factorySettings->create($shipping_method['instance_id']);


            add_thickbox();

            $shipping_method_title = '';
            $shipping_method_total = '';

            foreach( $order->get_items( 'shipping' ) as $item_id => $shipping_item_obj ){
                $shipping_method_title       = $shipping_item_obj->get_method_title();
                $shipping_method_total       = $shipping_item_obj->get_total();
            }

            $shipping_days = preg_replace('/[^0-9]/', '', $shipping_method_title);


            $dateNow = date('Y-m-d');
            $dateDelay = date('Y-m-d', strtotime($dateNow.'+ '.$config->loadings_params->deliveryDelay.' days'));
            $date = date('Y-m-d', strtotime($dateDelay.'+ '.$shipping_days.' days'));

            $arRowsStatus = array(  //array('Статус:', $status),
                                    //array('Трек номер:', $trackId),
                                    array(esc_html('Дата отправки:'), $dateDelay),
                                    array(esc_html('Дата доставки:'), $dateSend)
            );
            $arRowsContact = array(
                array(esc_html('Тип доставки:'), ($config->default_cargo_params->is_terminal_unloading)?
                                                                esc_html('до терминала') : esc_html('до адреса')),
                        array(esc_html('Город:'), esc_html($dataOrder['shipping']['city'])),
                        array(esc_html('Имя:'), esc_html($dataOrder['shipping']['first_name'])),
                        array(esc_html('Фамилия:'),esc_html( $dataOrder['shipping']['last_name']))
            );

            $arRowsLoad = array(
//                array(esc_html('Специальные требования к транспорту:'), self::getAdditionalTransportEquipments($config->default_cargo_params->requirements_transport) ),
//                array(esc_html('Дополнительная комплектация:'), self::getAdditionalTransportRequirements($config->default_cargo_params->requirements_transport)),
             //  array(esc_html('Тип разгрузки машины:'), self::getLoadingUnloadingTypes($config->default_cargo_params->))
            );

            $arRowsOther = array(
                array(esc_html('Тип покупателя:'), ($dataOrder['shipping']['company'] == 'on')?esc_html('Юридическое лицо'): __('Физическое лицо','dellin-shipping-for-woocommerce') ),
                array(esc_html('Адрес доставки:'), esc_html($dataOrder['shipping']['address_1'])),
                array(esc_html('Стоимость доставки:'), esc_html($shipping_method_total)),
    	    );

            $factoryView = new FactoryModalAdmin($post->ID, $arRowsContact, $arRowsStatus, $arRowsLoad, $arRowsOther);

            $factoryView->create();
        }


        public function check_shipping_status($order_id){

            $order = wc_get_order($order_id);
            $orderData = $order->get_data();

            $shipping_method = '';
            $instance_id = '';

            foreach( $order->get_items( 'shipping' ) as $item_id => $shipping_item_obj ) {
                $shipping_method = $shipping_item_obj->get_method_id();
                $instance_id = $shipping_item_obj->get_instance_id();
            }

            if($shipping_method != 'dellin_shipping'){
                return null;
            }

            $trackId = get_post_meta( $order_id, '_dellin_track_id', true );


            if(isset($trackId) && $trackId != '')
            {
                $settingsFactory = new FactorySettings();
                $settings = $settingsFactory->create($instance_id);

                $adapterOrder = new OrderAdapter(null, $settings, [], $order  );
                $factoryOrder = new FactoryOrder();
                $factoryOrder->setDataOrderAdapter($adapterOrder);
                $dellinOrder = $factoryOrder->create();

                $cache = new CacheWp();
                $terminalService = new DellinTerminalsService($settings, $cache);
                $ordersLogService = new DellinOrdersLogService($settings, $cache);
                $infoLogService = $ordersLogService->getOrderByRequestID($trackId);
                $terminalID = get_post_meta( $order_id, 'terminal_id', true );
                $terminalID = ($terminalID != '')?$terminalID:null;

                $terminalInfo = $terminalService->getTerminals(null, $terminalID);

                $terminalAddress =($terminalID  != '')?$terminalInfo['terminal']->fullAddress:'';


                $typeShipping = ($settings->default_cargo_params->is_terminal_unloading) ?
                                   esc_html('до терминала') : esc_html('до адреса') ;

                $fullAddress = ($settings->default_cargo_params->is_terminal_unloading)?
                                    $terminalAddress : esc_html($dellinOrder->arrivalLocation->address_inline);
                $trackStatus = ($trackId !== '')? esc_html($infoLogService->stateName) : false;

                $factoryView = new FactoryProfileMetaData($trackId, $fullAddress, $terminalID,
                                $trackStatus, $typeShipping, $terminalInfo);

                $factoryView->create();
            }
        }

        
}