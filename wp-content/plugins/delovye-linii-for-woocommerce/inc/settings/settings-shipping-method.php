<?php
/**
 * Settings for shipping method.
 *
 * @package Dellin shipping/Settings
 */

defined( 'ABSPATH' ) || exit;
    
$important_message = '';


$arTimeValues = array('NULL' => __( 'Time is not selected', 'dellin-shipping-for-woocommerce' ));
for($n = 0;$n<=24;$n++){
    if($n == 24){
        $arTimeValues["23:59"]= "23:59";
    }else{
        $timeValue = (($n<10)?"0":"").$n.":00";
        $arTimeValues[$timeValue]= $timeValue;
    }
}

$loading_unloading_types = array(
    'NULL'=> __('Back','dellin-shipping-for-woocommerce'),
    '0xb83b7589658a3851440a853325d1bf69'=>__('Side','dellin-shipping-for-woocommerce'),
    '0xabb9c63c596b08f94c3664c930e77778'=>__('Top','dellin-shipping-for-woocommerce')
);
$additionalTransportRequirements = array(
    'NULL'                                  => __('Not required','dellin-shipping-for-woocommerce'),
    '0x9951e0ff97188f6b4b1b153dfde3cfec'    => __('Open','dellin-shipping-for-woocommerce'),
    '0x818e8ff1eda1abc349318a478659af08'    => __('Tent required','dellin-shipping-for-woocommerce')
);

$additionalTransportEquipments = array(
    'NULL'                                  => __('Not required','dellin-shipping-for-woocommerce'),
    '0x92fce2284f000b0241dad7c2e88b1655'    => __('Tail lift','dellin-shipping-for-woocommerce'),
    '0x88f93a2c37f106d94ff9f7ada8efe886'    => __('Manipilator','dellin-shipping-for-woocommerce')
);
$groupingGoods = array(
    'ONE_CARGO_SPACE'          => __('One cargo space for whole order','dellin-shipping-for-woocommerce'),
    'SEPARATED_CARGO_SPACE'    => __('Separated cargo space for each kind of goods','dellin-shipping-for-woocommerce'),
    'SINGLE_ITEM_SINGLE_SPACE' => __('Separated cargo space for each good in the order','dellin-shipping-for-woocommerce')
);
//вытаскиваем значения аякс-списков из базы или из POST запроса (при сохранении )
if(isset($_GET['page']) && sanitize_text_field($_GET['page']) == 'wc-settings' &&
   isset($_GET['tab']) && sanitize_text_field($_GET['tab']) == 'shipping' &&
   isset($_GET['instance_id'])) {
    if (!function_exists('getShippingOptionsValues')) {
        function getShippingOptionsValues()
        {
            return get_option('woocommerce_dellin_shipping_calc_' . sanitize_text_field($_GET['instance_id']) . '_settings');
        }
    }

    if(!empty($_POST)){
        $shipping_options_values = [
            'counteragent'  => sanitize_text_field($_POST['woocommerce_dellin_shipping_calc_counteragent']),
            'opf_country'   => sanitize_text_field($_POST['woocommerce_dellin_shipping_calc_opf_country']),
            'sender_form'   => sanitize_text_field($_POST['woocommerce_dellin_shipping_calc_sender_form']),
            'terminal_id'   => sanitize_text_field($_POST['woocommerce_dellin_shipping_calc_terminal_id'])
        ];
    }else{
        $shipping_options_values = getShippingOptionsValues();
    }

}
$settings_basic = array(
	'basic'      => array(
		'title' => __( 'Basic Settings', 'dellin-shipping-for-woocommerce' ),
		'type'  => 'title',
	),
	'title'      => array(
		'title'             => __( 'Title', 'dellin-shipping-for-woocommerce' ),
		'description'       => __( 'This title will be displayed in checkout', 'dellin-shipping-for-woocommerce' ),
		'type'              => 'text',
		'default'           => __( 'Dellin shipping', 'dellin-shipping-for-woocommerce' ),
		'custom_attributes' => array(
			'required' => 'required',
		),
	),
    'appkey'=>array(
        'title'       => __( 'API key', 'dellin-shipping-for-woocommerce' ),
        'description'       => __( 'Enter the API key', 'dellin-shipping-for-woocommerce' ),
        'type'              => 'text',
        'custom_attributes' => array(
            'required' => 'required',
        )
    ),
    'login'=>array(
        'title'       => __( 'Login', 'dellin-shipping-for-woocommerce' ),
        'description'       => __( 'Enter your login on dellin.ru', 'dellin-shipping-for-woocommerce' ),
        'type'              => 'text',
    ),
    'password'=>array(
        'title'       => __( 'Password', 'dellin-shipping-for-woocommerce' ),
        'description'       => __( 'Enter your password on dellin.ru', 'dellin-shipping-for-woocommerce' ),
        'type'              => 'password',
    ),
    'request_email'=>array(
        'title'       => __( 'Адрес электронной почты заказчика', 'dellin-shipping-for-woocommerce' ),
        'description'       => __( 'Введите адрес электронной почты заказчика (обязательно)', 'dellin-shipping-for-woocommerce' ),
        'type'              => 'text',
        'custom_attributes' => array(
            'required' => 'required',
        )
    ),
    'counteragent'       => array(
        'title'       => __( 'Counteragent', 'dellin-shipping-for-woocommerce' ),
        'description' => __( 'Select counteragent (required if you have entered login and password)', 'dellin-shipping-for-woocommerce'),
        'type'        => 'select',
        'default'     => '',
        'options'     => array(),
        'custom_attributes' => array(
            'data-selected_value' =>isset($shipping_options_values['counteragent'])?$shipping_options_values['counteragent']:""
        )
    ),
    'delayed_delivery_title'      => array(
        'title' => __( 'Delayed delivery', 'dellin-shipping-for-woocommerce' ),
        'type'  => 'title',
    ),
    'delivery_delay'=>array(
        'title'       => __( 'Delivery delay', 'dellin-shipping-for-woocommerce' ),
        'description'       => __( 'Enter amount of days of the delivery delay (1 day default)', 'dellin-shipping-for-woocommerce' ),
        'type'              => 'text',
    ),
    'delivery_point_from_title'      => array(
        'title' => __( 'Delivery point from', 'dellin-shipping-for-woocommerce' ),
        'type'  => 'title',
    ),
    'kladr_code_delivery_from'=>array(
        'title'       => __( 'Departure kladr code', 'dellin-shipping-for-woocommerce' ),
        'description'       => __( 'Enter departure kladr code', 'dellin-shipping-for-woocommerce' ),
        'type'              => 'number',
        'class'=>'cityKladrInput',
        'custom_attributes' => array(
            'required' => 'required',
        )
    ),
    'sender_info'      => array(
        'title' => __( 'Sender info', 'dellin-shipping-for-woocommerce' ),
        'type'  => 'title',

    ),
    'opf_country'       => array(
        'title'       => __( 'Organizational and legal form country', 'dellin-shipping-for-woocommerce' ),
        'description' => __( 'Select organizational and legal form country', 'dellin-shipping-for-woocommerce'),
        'type'        => 'select',
        'default'     => '',
        'options'     => array(),
        'custom_attributes' => array(
            'required' => 'required',
            'data-selected_value' =>isset($shipping_options_values['opf_country'])?$shipping_options_values['opf_country']:""
        )
    ),
    'sender_form'       => array(
        'title'       => __( 'Sender organizational and legal form', 'dellin-shipping-for-woocommerce' ),
        'description' => __( 'Select sender organizational and legal form', 'dellin-shipping-for-woocommerce'),
        'type'        => 'select',
        'default'     => '',
        'options'     => array(),
        'custom_attributes' => array(
            'required' => 'required',
            'data-selected_value' =>isset($shipping_options_values['sender_form'])?$shipping_options_values['sender_form']:""
        )
    ),
    'sender_name'=>array(
        'title'       => __( 'Sender name', 'dellin-shipping-for-woocommerce' ),
        'description'       => __( 'Enter sender name', 'dellin-shipping-for-woocommerce' ),
        'type'              => 'text',
        'custom_attributes' => array(
            'required' => 'required',
        )
    ),
    'sender_inn'=>array(
        'title'       => __( 'Sender INN', 'dellin-shipping-for-woocommerce' ),
        'description'       => __( 'Enter sender name', 'dellin-shipping-for-woocommerce' ),
        'type'              => 'text',
        'custom_attributes' => array(
            'required' => 'required',
        )
    ),
	'sender_contact_name'=>array(
		'title'       => __( 'Контактное лицо', 'dellin-shipping-for-woocommerce' ),
		'description'       => __( 'Введите контактное лицо', 'dellin-shipping-for-woocommerce' ),
		'type'              => 'text',
		'custom_attributes' => array(
			'required' => 'required',
		)
	),
    'sender_contact_phone'=>array(
        'title'       => __( 'Sender contact phone', 'dellin-shipping-for-woocommerce' ),
        'description'       => __( 'Enter sender contact form', 'dellin-shipping-for-woocommerce' ),
        'type'              => 'text',
        'custom_attributes' => array(
            'required' => 'required',
        )
    ),
    'sender_contact_email'=>array(
        'title'       => __( 'Контактный адрес электронной почты организации', 'dellin-shipping-for-woocommerce' ),
        'description'       => __( 'Введите контактный адрес электронной почты организации', 'dellin-shipping-for-woocommerce' ),
        'type'              => 'text',
        'custom_attributes' => array(
            'required' => 'required',
        )
    ),
    'juridical_person_info'      => array(
        'title' => __( 'Juridical person info', 'dellin-shipping-for-woocommerce' ),
        'type'  => 'title',
    ),
    'sender_juridical_address'       => array(
        'title'       => __( 'Юридический адрес отправителя', 'dellin-shipping-for-woocommerce' ),
        'description' => __( 'Введите юридический адрес в формате: "Россия, Свердловская область, г. Екатеринбург, проспект Ленина, д. 1"', 'dellin-shipping-for-woocommerce' ),
        'type'        => 'text'
    ),
    'small_cargo_title'      => array(
        'title' => __( 'Small cargo', 'dellin-shipping-for-woocommerce' ),
        'type'  => 'title',
    ),
    'is_small_goods_price'       => array(
        'title'       => __( 'Small cargo', 'dellin-shipping-for-woocommerce' ),
        'description' => __( 'Check this box if you want module to try calculate by small cargo tariff', 'dellin-shipping-for-woocommerce' ),
        'type'        => 'checkbox'
    ),
    'insurance_title'      => array(
        'title' => __( 'Insurance', 'dellin-shipping-for-woocommerce' ),
        'type'  => 'title',
    ),
    'is_insurance_goods_with_declared_price'       => array(
        'title'       => __( 'Insure the cargo with declared price', 'dellin-shipping-for-woocommerce' ),
        'description' => __( 'Check this box if you want to insure the cargo with declared price', 'dellin-shipping-for-woocommerce' ),
        'type'        => 'checkbox'
    ),
    'loading_title'      => array(
        'title' => __( 'Loading', 'dellin-shipping-for-woocommerce' ),
        'type'  => 'title',
    ),
    'work_time_interval'      => array(
        'title' => __( 'The interval between start and end must be 4 and higher hours', 'dellin-shipping-for-woocommerce' ),
        'type'  => 'title',
    ),
    'work_start'       => array(
        'title'       => __( 'Work start', 'dellin-shipping-for-woocommerce' ),
        'description' => __( 'Select time when work starts', 'dellin-shipping-for-woocommerce'),
        'type'        => 'select',
        'default'     => 'NULL',
        'options'     => $arTimeValues,
        'custom_attributes' => array(
            'required' => 'required',
        )
    ),
    'work_end'       => array(
        'title'       => __( 'Work end', 'dellin-shipping-for-woocommerce' ),
        'description' => __( 'Select time when work ends', 'dellin-shipping-for-woocommerce'),
        'type'        => 'select',
        'default'     => 'NULL',
        'options'     => $arTimeValues,
        'custom_attributes' => array(
            'required' => 'required',
        )
    ),
    'work_break_start'       => array(
        'title'       => __( 'Work break start', 'dellin-shipping-for-woocommerce' ),
        'description' => __( 'Select time when break starts', 'dellin-shipping-for-woocommerce'),
        'type'        => 'select',
        'default'     => 'NULL',
        'options'     => $arTimeValues,
    ),
    'work_break_end'       => array(
        'title'       => __( 'Work break end', 'dellin-shipping-for-woocommerce' ),
        'description' => __( 'Select time when break ends', 'dellin-shipping-for-woocommerce'),
        'type'        => 'select',
        'default'     => 'NULL',
        'options'     => $arTimeValues
    ),
    'terminal_id'     => array(
        'title'       => __( 'departure terminal', 'dellin-shipping-for-woocommerce' ),
        'description' => __( 'Select departure terminal', 'dellin-shipping-for-woocommerce'),
        'type'        => 'select',
        'default'     => '',
        'options'     => array(),
        'custom_attributes'=>array(
            'data-selected_value' =>isset($shipping_options_values['terminal_id'])?$shipping_options_values['terminal_id']:""
        )

    ),
    'is_goods_loading'       => array(
        'title'       => __( 'loading at the address', 'dellin-shipping-for-woocommerce' ),
        'description' => __( 'Check this box if you want cargo to be loaded at the certain address', 'dellin-shipping-for-woocommerce'),
        'type'        => 'checkbox',
        'default'     => '',
        'options'     => array(),
    ),
	'loading_address'       => array(
		'title'       => __( 'Loading address', 'dellin-shipping-for-woocommerce' ),
		'description' => __( 'Введите адрес погрузки в формате: "Россия, Свердловская область, г. Екатеринбург, проспект Ленина, д. 1"', 'dellin-shipping-for-woocommerce' ),
		'type'        => 'text',
	),
    'loading_type'       => array(
        'title'       => __( 'Loading type', 'dellin-shipping-for-woocommerce' ),
        'description' => __( 'Select loading type', 'dellin-shipping-for-woocommerce'),
        'type'        => 'select',
        'default'     => 'NULL',
        'options'     => $loading_unloading_types
    ),
    'loading_transport_requirements'       => array(
        'title'       => __( 'Loading transport requirements', 'dellin-shipping-for-woocommerce' ),
        'description' => __( 'Select loading transport requirement', 'dellin-shipping-for-woocommerce'),
        'type'        => 'select',
        'default'     => 'NULL',
        'options'     => $additionalTransportRequirements
    ),
    'loading_transport_equipments'       => array(
        'title'       => __( 'Loading transport equipments', 'dellin-shipping-for-woocommerce' ),
        'description' => __( 'Select loading transport equipment', 'dellin-shipping-for-woocommerce'),
        'type'        => 'select',
        'default'     => 'NULL',
        'options'     => $additionalTransportEquipments
    ),
    'loading_grouping_of_goods'       => array(
        'title'       => __( 'Loading grouping of goods', 'dellin-shipping-for-woocommerce' ),
        'description' => __( 'Select loading grouping of goods', 'dellin-shipping-for-woocommerce'),
        'type'        => 'select',
        'default'     => 'NULL',
        'options'     => $groupingGoods
    ),
    'unloading_title'      => array(
        'title' => __( 'Unloading', 'dellin-shipping-for-woocommerce' ),
        'type'  => 'title',
    ),
    'is_goods_unloading'       => array(
        'title'       => __( 'Unloading at the address', 'dellin-shipping-for-woocommerce' ),
        'description' => __( 'Check this box if you want cargo to be unloaded at the certain address', 'dellin-shipping-for-woocommerce'),
        'type'        => 'checkbox',
        'default'     => '',
        'options'     => array(),
    ),
    'unloading_type'       => array(
        'title'       => __( 'Unloading type', 'dellin-shipping-for-woocommerce' ),
        'description' => __( 'Select unloading type', 'dellin-shipping-for-woocommerce'),
        'type'        => 'select',
        'default'     => 'NULL',
        'options'     => $loading_unloading_types
    ),
    'unloading_transport_requirements'       => array(
        'title'       => __( 'Unloading transport requirements', 'dellin-shipping-for-woocommerce' ),
        'description' => __( 'Select unloading transport requirement', 'dellin-shipping-for-woocommerce'),
        'type'        => 'select',
        'default'     => 'NULL',
        'options'     => $additionalTransportRequirements
    ),
    'unloading_transport_equipments'       => array(
        'title'       => __( 'Unloading transport equipments', 'dellin-shipping-for-woocommerce' ),
        'description' => __( 'Select unloading transport equipment', 'dellin-shipping-for-woocommerce'),
        'type'        => 'select',
        'default'     => 'NULL',
        'options'     => $additionalTransportEquipments
    ),
    'packing_title'      => array(
        'title' => __( 'Packing', 'dellin-shipping-for-woocommerce' ),
        'type'  => 'title',
    ),
    'packing_for_goods_box'       => array(
        'title'       => __( 'Box', 'dellin-shipping-for-woocommerce' ),
        'description' => __( 'Check this box if you want cargo to be packaged in cardboard box', 'dellin-shipping-for-woocommerce'),
        'type'        => 'checkbox',
        'default'     => '',
        'options'     => array(),
    ),
    'packing_for_goods_hard'       => array(
        'title'       => __( 'Hard packing', 'dellin-shipping-for-woocommerce' ),
        'description' => __( 'Check this box if you want cargo to be packaged in hard packing', 'dellin-shipping-for-woocommerce'),
        'type'        => 'checkbox',
        'default'     => '',
        'options'     => array(),
    ),
    'packing_for_goods_additional'       => array(
        'title'       => __( 'Additional packing', 'dellin-shipping-for-woocommerce' ),
        'description' => __( 'Check this box if you want cargo to be packaged in additional packing', 'dellin-shipping-for-woocommerce'),
        'type'        => 'checkbox',
        'default'     => '',
        'options'     => array(),
    ),
    'packing_for_goods_bubble'       => array(
        'title'       => __( 'Bubble wrap packing', 'dellin-shipping-for-woocommerce' ),
        'description' => __( 'Check this box if you want cargo to be packaged in bubble wrap', 'dellin-shipping-for-woocommerce'),
        'type'        => 'checkbox',
        'default'     => '',
        'options'     => array(),
    ),
    'packing_for_goods_bag'       => array(
        'title'       => __( 'Bag packing', 'dellin-shipping-for-woocommerce' ),
        'description' => __( 'Check this box if you want cargo to be packaged in bag', 'dellin-shipping-for-woocommerce'),
        'type'        => 'checkbox',
        'default'     => '',
        'options'     => array(),
    ),
    'packing_for_goods_bag'       => array(
        'title'       => __( 'Bag packing', 'dellin-shipping-for-woocommerce' ),
        'description' => __( 'Check this box if you want cargo to be packaged in bag', 'dellin-shipping-for-woocommerce'),
        'type'        => 'checkbox',
        'default'     => '',
        'options'     => array(),
    ),
    'packing_for_goods_pallet'       => array(
        'title'       => __( 'Pallet packing (to arrival terminal only)', 'dellin-shipping-for-woocommerce' ),
        'description' => __( 'Check this box if you want cargo to be packaged in pallet', 'dellin-shipping-for-woocommerce'),
        'type'        => 'checkbox',
        'default'     => '',
        'options'     => array(),
    ),
	'packing_for_goods_car_glass'       => array(
        'title'       => __( 'Special packing for car glass', 'dellin-shipping-for-woocommerce' ),
        'description' => __( 'Check this box if you want cargo to be packaged in special packing for car glass', 'dellin-shipping-for-woocommerce'),
        'type'        => 'checkbox',
        'default'     => '',
        'options'     => array(),
    ),
    'packing_for_goods_car_parts'       => array(
            'title'       => __( 'Special packing for car spare parts', 'dellin-shipping-for-woocommerce' ),
            'description' => __( 'Check this box if you want cargo to be packaged in special packing for car spare parts', 'dellin-shipping-for-woocommerce'),
            'type'        => 'checkbox',
            'default'     => '',
            'options'     => array(),
    ),
	'packing_for_goods_complex_pallet'       => array(
            'title'       => __( 'Complex packing with pallet and amortisation', 'dellin-shipping-for-woocommerce' ),
            'description' => __( 'Check this box if you want cargo to be packaged in complex packing with pallet and amortisation', 'dellin-shipping-for-woocommerce'),
            'type'        => 'checkbox',
            'default'     => '',
            'options'     => array(),
    ),
    'packing_for_goods_complex_hard'       => array(
            'title'       => __( 'Complex packing with crate and amortisation', 'dellin-shipping-for-woocommerce' ),
            'description' => __( 'Check this box if you want cargo to be packaged in complex packing with crate and amortisation', 'dellin-shipping-for-woocommerce'),
            'type'        => 'checkbox',
            'default'     => '',
            'options'     => array(),
    )
);


$settings_basic = apply_filters( 'dellin_basic_shipping_settings', $settings_basic );

return apply_filters( 'dellin_shipping_settings', $settings_basic );
