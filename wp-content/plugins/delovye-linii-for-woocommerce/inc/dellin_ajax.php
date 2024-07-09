<?php

//получение контрагентов
add_action( 'wp_ajax_get_counteragents', 'dellin_get_counteragents' ); // для авторизованных пользователей
add_action( 'wp_ajax_nopriv_get_counteragents', 'dellin_get_counteragents' ); // для неавторизованных

//Получение организационно-правовых форм
add_action( 'wp_ajax_get_opf', 'dellin_get_opf' ); // для авторизованных пользователей
add_action( 'wp_ajax_nopriv_get_opf', 'dellin_get_opf' ); // для неавторизованных

//Получение терминалов отправления
add_action( 'wp_ajax_get_terminals', 'dellin_get_terminals' ); // для авторизованных пользователей
add_action( 'wp_ajax_nopriv_get_terminals', 'dellin_get_terminals' ); // для неавторизованных

//Получение терминалов получения в чекауте
add_action( 'wp_ajax_get_terminals_in_checkout', 'dellin_get_terminals_in_checkout' ); // для авторизованных пользователей
add_action( 'wp_ajax_nopriv_get_terminals_in_checkout', 'dellin_get_terminals_in_checkout' ); // для неавторизованных
//add_action('woocommerce_shipping_show_shipping_calculator', 'dellin_get_terminals_in_checkout', 10, 2);


//Задаём терминал получения для конкретного пользователя
add_action( 'wp_ajax_set_terminal_in_cart', 'dellin_set_terminal_in_cart' ); // для авторизованных пользователей
add_action( 'wp_ajax_nopriv_set_terminal_in_cart', 'dellin_set_terminal_in_cart' ); // для неавторизованных

//Поиск города по названию
add_action( 'wp_ajax_search_city', 'dellin_search_city' ); // для авторизованных пользователей
add_action( 'wp_ajax_nopriv_search_city', 'dellin_search_city' ); // для неавторизованных

//Поиск Улицы по названию
add_action( 'wp_ajax_search_street', 'dellin_search_street' ); // для авторизованных пользователей
add_action( 'wp_ajax_nopriv_search_street', 'dellin_search_street' ); // для неавторизованных

//Отправка request

include(DELLIN_PLUGIN_DIR."/inc/dellinClasses/DellinApi.php");
//include(DELLIN_PLUGIN_DIR."/inc/class.php");

function dellin_get_counteragents() {
    $config['login'] = sanitize_text_field(trim($_REQUEST['login']));
    $config['password'] = sanitize_text_field(trim($_REQUEST['password']));
    $config['appkey'] = sanitize_text_field(trim($_REQUEST['appkey']));
    $config['reset_session'] = sanitize_text_field(trim($_REQUEST['reset_session']));

    $sessionID = DellinApi::GetDellinSessionId($config);
//    echo '<pre>';
//    var_dump($config);
//    var_dump($sessionID);
//    echo '</pre>';
//    die();
    $arCounterAgents = ['' => ['name'=>__('Counteragent is not selected','dellin-shipping-for-woocommerce')]];
    $counterAgents = Null;
    //if(isset($sessionID) && $sessionID !== Null && !isset($sessionID->errors)) {
    $data = array('appkey' => $config['appkey'], 'sessionID' => $sessionID, "full_info" => true);
    $counterAgents  = DellinApi::getCounteragents($data);

    //}
    $error = false;
    if($counterAgents->errors == 'Unauthorized'){
        $error = __('Authorization error','dellin-shipping-for-woocommerce');
    }
    if(is_array($counterAgents)){
        $arCounterAgents = $arCounterAgents + $counterAgents;
    }
    $result = array('counteragents'=>$arCounterAgents,'sessionID'=>$sessionID);
    if($error){
        $result['error']=$error;
    }
    $result['count'] = count($arCounterAgents);
    echo json_encode($result);
    die();
}

function dellin_get_opf(){
    $appkey = sanitize_text_field(trim($_REQUEST['appkey']));
	$arOpf = DellinApi::GetOpf($appkey);
    if(empty($arOpf['opf']) && empty($arOpf['country'])){
        $arOpf['error'] = __('Api is not available','dellin-shipping-for-woocommerce');
    }
	echo json_encode($arOpf);
    die();
}

function dellin_get_terminals(){
    if(sanitize_text_field($_REQUEST['mode']) == "request_terminals" ){//все терминалы доступные по направлению
		$arTerminals[0] = __('Terminal not selected','dellin-shipping-for-woocommerce');
		$apiKey = sanitize_text_field(trim($_REQUEST['appkey']));
		if($apiKey){
			if("" !== sanitize_text_field($_REQUEST['kladr'])){
				$data = array(
					"appkey" =>$apiKey,
					"code" => sanitize_text_field(trim($_REQUEST['kladr'])),
					"direction" => "derival"
				);
				$arTerminalIdValues = DellinApi::getRequestTerminals($data);
				if(is_array($arTerminalIdValues)){
                    $arTerminals = $arTerminals + $arTerminalIdValues;
                }
			}else{
                $result['error'] = __('Departure KLADR is not set','dellin-shipping-for-woocommerce');
			}
		}
		if($arTerminalIdValues == NULL){
		    $result['error'] = __('Api is not available','dellin-shipping-for-woocommerce');
        }
		$result['terminals'] = $arTerminals;
		echo json_encode($result);
		die();
	}
}

function dellin_search_city(){
    $cities = [];
    $query = str_replace(__('YO'),__('YE'), sanitize_text_field($_REQUEST['query']));
    //Вы думаете это смешно?
    // Вот когда из-за этих букв и кодировки не будет работать АПИ - смешно не будет))).
    // Формируем строку запроса и отправляем.


    $cities = DellinApi::SearchCity($query);

    if($cities == null){
        $result['error'] = __('City not found','dellin-shipping-for-woocommerce');
    }else{
        $result = array_slice($cities,0, sanitize_text_field($_REQUEST['count']));
    }
    echo wp_json_encode($result, JSON_UNESCAPED_UNICODE);
    die();

}

//function dellin_get_terminals_in_checkout(){
//
//	if($_REQUEST['mode'] == "request_terminals_checkout" ){//все терминалы доступные по направлению
//		$arTerminals[''] = __('Terminal not selected','dellin-shipping-for-woocommerce');
//		global $woocommerce;
//
//		if(!$_REQUEST['instance_id']) {
//			$_REQUEST['instance_id'] = DellinApi::getInstanceId();
//		}
//
//
//		$config = DellinApi::getConfig($_REQUEST['instance_id']);
//		$apiKey = $config['appkey'];
////		$apiKey = trim($_REQUEST['appkey']);
//		$city = $_REQUEST['city'];
//		$state = $_REQUEST['state'];
//
//		if($apiKey){
//			if('' !== $city){
//				$kladr = DellinApi::GetCityKLADRCode($city, $state);
//
//
//
//				$data = array(
//					"appkey" =>$apiKey,
//					"code" => trim($kladr),
//					"direction" => "derival"
//				);
//
//
//				$arTerminalIdValues = DellinApi::getRequestTerminals($data);
//
//				if(is_array($arTerminalIdValues)){
//					$arTerminals = $arTerminals + $arTerminalIdValues;
//				}
//			}else{
//				$result['error'] = __('Departure KLADR is not set','dellin-shipping-for-woocommerce');
//			}
//		}
//		if($arTerminalIdValues == NULL){
//			$result['error'] = __('Api is not available','dellin-shipping-for-woocommerce');
//		}
//		$result['terminals'] = $arTerminals;
//		echo json_encode($result);
//		wp_die();
//	}
//}
//
//function dellin_set_terminal_in_cart(){
//
//	var_dump($_POST['security']);
//	if( ! wp_verify_nonce( $_POST['security'], 'dellin-terminal' ) ) die( 'Stop!');
//	$user_id = $_POST['id'];
//	if ( ! empty( $_POST['terminal_id'] ) ) {
//		update_user_meta( $user_id,
//				'terminal_id',
//						 sanitize_text_field( $_POST['terminal_id'])
//		);
//		$response = array('status'=>'OK');
//		echo json_encode($response);
//
//		global $woocommerce;
//		$woocommerce->cart->calculate_shipping();
//	//	do_action('woocommerce_cart_shipping_packages');
//		wp_die();
//	}
//
//}

function dellin_search_street(){
//	$m = VmModel::getModel('shipmentmethod');
//	$deliveryId = $app->input->getInt('cid');
//	$deliveryConfig = $m->getShipment($deliveryId)->shipment_params;
//	$params = [];
//	echo "<pre>";
//	VmTable::bindParameterable($m,'shipment_params',$params);
//	var_dump($params);
//	echo "</pre>";
//	die();
	$kladr = sanitize_text_field(trim($_REQUEST['kladr']));
	$apiKey = sanitize_text_field(trim($_REQUEST['apikey']));
	$street = sanitize_text_field(trim($_REQUEST['query']));
	$count = sanitize_text_field(trim($_REQUEST['count']));
	$login = sanitize_text_field(trim($_REQUEST['login']));
	$password = sanitize_text_field(trim($_REQUEST['password']));
	$streets = [];
	$arParams = array(
		"appkey"    => $apiKey,
		"code"      => $kladr,
		"street"    => $street,
		"limit"     => $count?$count:2
	);
	if(isset($login) && isset($password)){
		$sessionID = DellinAPI::GetDellinSessionId(['appkey' => $apiKey,'login'=>$login,'password'=>$password]);
		if(isset($sessionID)){
			$arParams["sessionID"] = $sessionID;
		}

	}
	$streets = DellinApi::GetStreetKladr($arParams);
	if($streets['STATUS'] == 'ERROR'){
	    $result['error'] = $streets['BODY'];
    }else{
        $result = $streets['streets'];
    }
    echo wp_json_encode($result, JSON_UNESCAPED_UNICODE);
    die();
}

function dellin_send_request(){

    if(sanitize_text_field($_REQUEST['mode']) == "try_request" && sanitize_text_field($_REQUEST['id']) != null ){

    	$orderUpdate = sanitize_text_field($_REQUEST['orderChange']);
        $id = sanitize_text_field($_REQUEST['id']);
        $itemId = sanitize_text_field($_REQUEST['itemID']);
        $result = DellinApi::CreateOrder($id, $orderUpdate, $itemId);

        echo wp_json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    wp_die();

}

//
//if($_REQUEST['action'] == 'get_counteragents')
//{
//	$config['login'] = trim($_REQUEST['login'));
//	$config['password'] = trim($_REQUEST['password'));
//	$config['appkey'] = trim($_REQUEST['appkey'));
//	$config['reset_session'] = trim($app->input->getBool('reset_session'));
//	$sessionID = DellinApi::GetDellinSessionId($config);
//	$arCounterAgents = ['' => ['name'=>$lang->_('SM_DELLIN_SHIPPING_COUNTERAGENT_NOT_SELECTED')]];
//    $counterAgents == Null;
//	//if(isset($sessionID) && $sessionID !== Null && !isset($sessionID->errors)) {
//		$data = array('appkey' => $config['appkey'], 'sessionID' => $sessionID, "full_info" => true);
//		$counterAgents  = DellinApi::getCounteragents($data);
//
//	//}
//    $error = false;
//    if($counterAgents->errors == 'Unauthorized' || $counterAgents == null){
//        $error = $lang->_('SM_DELLIN_SHIPPING_UNAUTHORIZED');
//    }
//    if(is_array($counterAgents)){
//        $arCounterAgents = $arCounterAgents + $counterAgents;
//    }
//    $result = array('counteragents'=>$arCounterAgents,'sessionID'=>$sessionID);
//    if($error){
//        $result['error']=$error;
//    }
//	echo json_encode($result);
//}
//
//if($app->input->getCmd('action') == 'get_opf'){
//	$appkey = trim($_REQUEST['appkey'));
//	$arOpf = DellinApi::GetOpf($appkey);
//    if(empty($arOpf['opf']) && empty($arOpf['country'])){
//        $arOpf['error'] = $lang->_('SM_DELLIN_SHIPPING_API_NOT_AVAILABLE');
//    }
//	echo json_encode($arOpf);
//}
//
//if($app->input->getCmd('action') == 'get_terminals'){
//	if($app->input->getCmd('mode') == "request_terminals" ){//все терминалы доступные по направлению
//		$arTerminals[''] = $lang->_('SM_DELLIN_SHIPPING_TERMINAL_NOT_SELECTED');
//		$apiKey = $_REQUEST['appkey');
//		if($apiKey){
//			if("" !== $_REQUEST['kladr')){
//				$data = array(
//					"appkey" =>$apiKey,
//					"code" => $_REQUEST['kladr'),
//					"direction" => "derival"
//				);
//				$arTerminalIdValues = DellinApi::getRequestTerminals($data);
//				if(is_array($arTerminalIdValues)){
//                    $arTerminals = $arTerminals + $arTerminalIdValues;
//                }
//
//			}else{
//				//error no kladr
//			}
//		}
//		if($arTerminalIdValues == NULL){
//		    $result['error'] = $lang->_('SM_DELLIN_SHIPPING_API_NOT_AVAILABLE');
//        }
//		$result['terminals'] = $arTerminals;
//		echo json_encode($result);
//	}
//}
//
//if($app->input->getCmd('action') == 'search_city'){
//
//		$cities = [];
//		$query = str_replace($lang->_('YO'),$lang->_('YE'),$_REQUEST['query'));
//		// Формируем строку запроса и отправляем.
//		$cities = DellinApi::SearchCity($query);
//		$result = array_slice($cities,0,$app->input->getCmd('count'));
//		echo json_encode($result);
//}
//if($app->input->getCmd('action') == 'search_street'){
////	$m = VmModel::getModel('shipmentmethod');
////	$deliveryId = $app->input->getInt('cid');
////	$deliveryConfig = $m->getShipment($deliveryId)->shipment_params;
////	$params = [];
////	echo "<pre>";
////	VmTable::bindParameterable($m,'shipment_params',$params);
////	var_dump($params);
////	echo "</pre>";
////	die();
//	$kladr = $app->input->getCmd('kladr');
//	$apiKey = $app->input->getCmd('apikey');
//	$street = $_REQUEST['query');
//	$count = $app->input->getInt('count');
//	$login = $_REQUEST['login');
//	$password = $_REQUEST['password');
//	$streets = [];
//	$arParams = array(
//		"appkey"    => $apiKey,
//		"code"      => $kladr,
//		"street"    => $street,
//		"limit"     => $count?$count:2
//	);
//	if(isset($login) && isset($password)){
//		$sessionID = DellinAPI::GetDellinSessionId(['appkey' => $apiKey,'login'=>$login,'password'=>$password]);
//		if(isset($sessionID)){
//			$arParams["sessionID"] = $sessionID;
//		}
//
//	}
//	$streets = DellinApi::GetStreetKladr($arParams);
//	$result = $streets['streets'];
//	echo json_encode($result);
//}

?>