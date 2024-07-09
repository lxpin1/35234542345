<?php
include("ApiCore.php");

//include(DELLIN_PLUGIN_DIR."/inc/class-dellin-shipping-method.php");
//new Dellin_Shipping_Method();
/**
 * Class DellinAPI
 * Класс предназначен для расчета стоимости доставки.
 */
class DellinApi extends ApiCore
{

	/**
	 * Расчет стоимости доставки.
	 * @param array $cart
	 * @param array $arConfig
	 * @param bool $caching
	 * @return array|bool
	 */

	public function Calculate($cart, $arConfig)
	{

		$data = self::GetDeliveryData($cart, $arConfig);
		if(!isset($data['error'])){
            //debug


			$validation = self::IsDataValid($data, $cart, $arConfig);
			if ($validation['status'] == 'ok') {
				//После валидации данных кешируем дату, что бы не слать лишних одинаковых запросов
				//ВНИМАНИЕ! Кеширование работает, только если есть соответствующий плагин.
				//Без плагина кеширование работать не будет.
				$cache_id = 'dellin_calc|'.json_encode($data).'&'.json_encode($arConfig);
				$expire = 600;
				if(!wp_cache_get($cache_id) || isset($arConfig['reset_session'])){
					$response = self::sendApiRequest('calculator',$data);
//                    echo "<pre>";
//                    var_dump($response);
//                    echo "</pre>";


					if($response == null){
                        wc_add_notice(esc_html('Не возможно расчитать стоимость доставки одного из методов доставки', 'error'));
					}
					if($response == null){
						return false;
					}
					if (isset($response->errors) && $response->errors !== null) {
						if (isset($response->errors->message)) {
							$result['status'] = "api_unavailable";
							$result['body'] = $response->errors->message;
							return $result;
						}else{
							$result['body'] = "";

							foreach($response->errors as $index => $error){
                                if(is_string($error)){
                                    $result['status'] = 'error';
                                    $result['body'] = $error;
                                } else {
									$errorFields = '';
								   if(isset($error->fields))
								   {
									$errorFields = implode(' , ',$error->fields);
								   }
                                   
                                   $result['status'] = "error";
                                   $result['body'] .= "[".$error->code."]".$error->detail." Поля: ".$errorFields."| ";
                                }
							}
							$result['data']['state'] = false;
						}
					}else{

					//	if(isset($_SESSION['current_terminals'])) unset($_SESSION['current_terminals']);

                        if(isset($response->data->arrival->terminals)){
							//session_start();
						//    $_SESSION['current_terminals'] = $response->data->arrival->terminals;
						    //alternative method
                            $cache_id_terms = 'dellin|term|'.json_encode(WC()->customer->get_shipping());
							WC()->session->set( $cache_id_terms, $response->data->arrival->terminals );

                            wp_cache_set($cache_id_terms, $response->data->arrival->terminals, '', $expire );

                        }

						if ($price = self::CalculatePrice($response, $arConfig))
						{
							$currentDate = new DateTime(date('Y-m-d'));
							if($data['delivery']['arrival']['variant'] == 'terminal'){
								$arrivalDate = new DateTime ($response->data->orderDates->arrivalToOspReceiver);
							}else{
								$arrivalDate = new DateTime ($response->data->orderDates->derivalFromOspReceiver);
							}
							$dateDiff = self::dateDifference($arrivalDate,$currentDate);
							$result['status'] = 'OK';
							$result['body']   = array('price'=>$price,'time'=>$dateDiff);
						}
						if(isset($response->data->availableDeliveryTypes->small) && $arConfig['is_small_goods_price']=="1" ){
							$result['delivery_type'] = "small";
						}else{
							$result['delivery_type'] = "auto";
						}
					}
					wp_cache_set( $cache_id, $result,"",$expire ); // добавим данные в кэш
					return $result;
				} else {
					return wp_cache_get($cache_id);
				}
			}else{
				return [
					'status' => 'error',
					'errors' => $validation['errors']
				];

			}
		}else{
			return [
				'status'=>'error',
				'errors'=>$data['errors']
			];
		}
	}

	/**
	 * @param $cart
	 * @param $arConfig
	 * @param string $type
	 *
	 * @return array
	 * @throws Exception
	 */
	public static function GetDeliveryData($cart, $arConfig, $type = 'calc'){
		$sessionID = self::getDellinSessionId($arConfig);
		$deliveryType = 'auto';

		if(!empty($cart->deliveryCity)){
			//$receiverKladr = self::GetCityKLADRCode($cart->deliveryCity, $cart->state );
			$findKLADR = self::findKLADR($arConfig['appkey'], $cart->deliveryCity, $cart->state, $cart->postcode);
			$receiverKladr = $findKLADR->code;
			if($arConfig['is_small_goods_price'] == 'yes'){
				$deliveryType = 'small';
			}
			$opfList = self::GetOpfList($arConfig['appkey'])['list'];
			if(is_array($opfList)){
				foreach($opfList as $opf){	
					$arOpfList[$opf['uid']] = $opf;
				}
			}
			//коды ИП для разных стран
			$receiverPPCodes = array(
				"RU" => "0xab91feea04f6d4ad48df42161b6c2e7a"
			);

			$cargoParams = self::getCargoParams($cart,$arConfig);
			if(!$cargoParams) {
				$errors['cargo_global_oversize'] =__('Cargo is too large for delivering');
			}
			if($arConfig['delivery_delay']=="" || $arConfig['delivery_delay'] == 0){
				$deliveryDelay = 1;
			}else{
				$deliveryDelay = $arConfig['delivery_delay'];
			}
			$produceData = array(
				"appKey"                => $arConfig['appkey'],
				"produceDate"           => date("Y-m-d",mktime(0, 0, 0, date("m")  , date("d")+$deliveryDelay, date("Y"))), //ориентировочная минимальная дата (сегодня + дней из настроек)
				"sizedVolume"           => $cargoParams['totalVolume'],
				"sizedWeight"           => $cargoParams['totalWeight'],
				"derivalPoint"          => $arConfig['kladr_code_delivery_from'],
				"derivalDoor"           => ($arConfig['is_goods_loading'] == "yes")?true:false,
				"deliveryType"          => "1",
				"derivalPeriodVisit"    => array(
					"workStart" => $arConfig['work_start'],
					"workEnd"   => $arConfig['work_end']
				),
				"oversizedWeight" => $cargoParams['oversizedWeight'],
				"oversizedVolume" => $cargoParams['oversizedVolume'],
				"length"          => $cargoParams['length'],
				"width"           => $cargoParams['width'],
				"height"          => $cargoParams['height']
			);
			$produceDate = self::GetProduceDate($produceData,$arConfig);
			if(!isset($produceDate->errors)){
				if(isset($produceDate->nearestDate)){//если дата не подошла
					$nearestDate = $produceDate->nearestDate;
				}else{
					$nearestDate = date("Y-m-d",mktime(0, 0, 0, date("m")  , date("d")+$deliveryDelay, date("Y")));
				}
			}else{
				$errors['produce_date_errors'] = $produceDate->errors;
			}

			$cargoParams['insurance'] = [
				"statedValue" =>($arConfig['is_insurance_goods_with_declared_price']=='yes')?($cart->totalPrice):0,
				"term"        => false
			];
			$api_data =[
				'appkey'=>$arConfig['appkey'],
				'delivery'=>[
					"deliveryType"=>[
						"type"=>$cargoParams['deliveryType']
					],
					'arrival'=>[],
					'derival'=>[
						'produceDate'=>$nearestDate,
					],
					'packages'=>[],
				],
				'members'=>[
					"requester" => [
						"role"  => "sender",
					],
				],
				'cargo'=>$cargoParams,
				'payment'=>[
					'paymentCity'=>$arConfig['kladr_code_delivery_from'],
					'type'=>'noncash'
				],
				'productInfo'=>array(
					'type'=> 4,
					'productType'=> 11,
					'info'=>array(
						array(
							'param'=> 'module version',
							'value'=> '1.1.3'
						)
					)
				)
			];

			if($sessionID){
				$api_data['sessionID']=$sessionID;
			}
			//если нам нужны данные для реквеста их надо дополнить
			//получаем данные для реквеста

			if($type == 'request'){

				$api_data['delivery']['deliveryType']['payer'] = self::isCacheOnDelivery($cart->payment_method)?"receiver":"sender";
				$api_data['delivery']['derival']['variant'] = ($arConfig['is_goods_loading'] =='yes')?'address':'terminal';
				$api_data['delivery']['derival']['payer'] = self::isCacheOnDelivery($cart->payment_method)?"receiver":"sender";

				$api_data['delivery']['arrival']['variant'] = ($arConfig['is_goods_unloading'] =='yes')?'address':'terminal';
				$api_data['delivery']['arrival']['payer'] = self::isCacheOnDelivery($cart->payment_method)?"receiver":"sender";

				$api_data['members'] = array(
					'requester' => array(
						'role'  => 'sender',
						'email' => $arConfig['sender_contact_email']
					),
					'sender'=> array(
						'counteragent'=> array(
							'form'=> $arConfig['sender_form'],
							'name'=> $arConfig['sender_name'],
							'inn'=> $arConfig['sender_inn'],
							"juridicalAddress" => ($arOpfList[$arConfig['sender_form']]['juridical'] == 1)?array(
								'search' => $arConfig['sender_juridical_address']
//								"street"    => $arConfig['sender_juridical_kladr_street'],
//								"house"     => $arConfig['sender_juridical_house_num'],
							):array(),
							"isAnonym" => false
						),
						'contactPersons' => array(
							array(
								"name" => $arConfig['sender_contact_name'],
								"save" => false
							)
						),
						"phoneNumbers" => array(
							array(
								"number" => preg_replace("/[^0-9]/", '', $arConfig['sender_contact_phone'])
							)
						),
						'email' => $arConfig['sender_contact_email']
					),
					'receiver'=> array(
						'counteragent'=> array(
							'form' =>  $receiverPPCodes[$cart->shipping_country], //физическое лицо. Если юр. лицо, то ниже counteragent изменится на массив параметров для юр. лица
							'name' => $cart->contact_person
						),
						'contactPersons'=> array(
							array(
							'name'=> $cart->contact_person,
							'save'=> false
							),
						),
						'phoneNumbers'=> array(
							array(
								"number" => $str = preg_replace("/[^0-9]/", '', $cart->contact_phone)
							),
						),
						"email" => $cart->contact_email
					),
				);
				$api_data['payment']['primaryPayer'] = 'sender';
				//Для
				if($cart->person_type == '1'){
					$api_data['members']['receiver']['counteragent'] = array(
						"name" => $cart->organisation_name,
						"inn"  => $cart->organisation_inn,
						"juridicalAddress" => array(
							"search"    => $cart->organisation_address ,
						),
						"isAnonym" =>false
					);
					$api_data['members']['receiver']['dataForReceipt'] = array('send'=>false);
				}
				//
				if($cart->person_type == '0'){
					$api_data['members']['receiver']['counteragent']['name'] = $cart->contact_person;
					$api_data['members']['receiver']['counteragent']['phone'] =  preg_replace("/[^0-9]/", '', $cart->contact_phone);
					$api_data['members']['receiver']['counteragent']['isAnonym'] = true;
					$api_data['members']['receiver']['dataForReceipt'] = array('send'=>false);
				}


				if(self::isCacheOnDelivery($cart->payment_method)){
					$api_data['payment']['type'] = 'cash';
					$api_data['payment']['primaryPayer'] = 'receiver';
					$api_data['payment']['cashOnDelivery'][0]['orderNumber'] = intval($cart->idOrder);
					$api_data['payment']['cashOnDelivery'][0]['orderDate'] = date('Y-m-d',strtotime($cart->created_date));
					$api_data['payment']['cashOnDelivery'][0]['paymentType']  = 'cash';
				}


			}
			if($arConfig['is_goods_loading'] == "yes"){

				$api_data['delivery']['derival']['variant'] = 'address';
//				if($type == 'request'){
//					$api_data['delivery']['derival']['address']['house'] = $arConfig['loading_house_num'];
//				}
				if(!empty($arConfig['loading_address'])){
					// методы CDI
				//	$api_data['delivery']['derival']['address']['street'] = $arConfig['loading_address'];
					$api_data['delivery']['derival']['address']['search'] = $arConfig['loading_address'];
				}else{
					// методы CDI
				//	$api_data['delivery']['derival']['address']['street'] = $arConfig['kladr_code_delivery_from'];
					$api_data['delivery']['derival']['address']['search'] = get_option('woocommerce_store_city').', '.get_option('woocommerce_story_address');
				}

				$api_data['delivery']['derival']['time'] = array(
					'worktimeStart' => $arConfig['work_start'],
					'worktimeEnd'   => $arConfig['work_end'],

				);
				if("NULL" !== $arConfig['work_break_start'] && "NULL" !== $arConfig['work_break_end']){
					$api_data['delivery']['derival']['time']['breakStart'] = $arConfig['work_break_start'];
					$api_data['delivery']['derival']['time']['breakEnd'] = $arConfig['work_break_end'];

				}

			}else{
				$api_data['delivery']['derival']['variant'] = 'terminal';
				$api_data['delivery']['derival']['terminalID'] = $arConfig['terminal_id'];
			}
			if($arConfig['loading_transport_requirements']!== "NULL") $requirements[] = $arConfig['loading_transport_requirements'];
			if($arConfig['loading_transport_equipments'] !== "NULL") $requirements[] = $arConfig['loading_transport_equipments'];
			if(!empty($requirements)) $deliveryData["delivery"]["derival"]['requirements'] = $requirements;



			if($arConfig['is_goods_unloading'] == "yes"){
				$api_data['delivery']['arrival']['variant'] = 'address';

	//			$api_data['delivery']['arrival']['address']['street'] = $receiverKladr;
				$api_data['delivery']['arrival']['address']['search'] = $cart->postcode.', Россия, '. $cart->state.', '.$cart->deliveryCity.', '.$cart->address;
//				if($type == 'request'){
//					$api_data['delivery']['arrival']['address']['house'] = $arConfig['loading_house_num'];
//				}
				$api_data['delivery']['arrival']['time'] = [
					'worktimeStart' => (isset($cart->worktime_start) && !empty($cart->worktime_start))?
                                        $cart->worktime_start : "08:00",
					'worktimeEnd'   => (isset($cart->worktime_end) && !empty($cart->worktime_end))?
                                        $cart->worktime_end : "18:00",

				];

				if("NULL" !== $arConfig['work_break_start'] && "NULL" !== $arConfig['work_break_end']){
					$api_data['delivery']['arrival']['time']['breakStart'] = $arConfig['work_break_start'];
					$api_data['delivery']['arrival']['time']['breakEnd'] = $arConfig['work_break_end'];
				}

			}else{
				$api_data['delivery']['arrival']['variant'] = 'terminal';
				if($type == 'request'){
                    $api_data['delivery']['arrival']['terminalID'] = $cart->terminal_id;
                } else {
                    $api_data['delivery']['arrival']['city'] = $receiverKladr;
                }


			}

			$arPackages = self::GetSelectedPackingTypesId(
				[
					'box'=> $arConfig['packing_for_goods_box'],
					'hard' => $arConfig['packing_for_goods_hard'],
					'additional' => $arConfig['packing_for_goods_additional'],
					'bubble' => $arConfig['packing_for_goods_bubble'],
					'bag' => $arConfig['packing_for_goods_bag'],
					'pallet' => $arConfig['packing_for_goods_pallet'],
					'car_glass' =>$arConfig['packing_for_goods_car_glass'],
					'car_parts' => $arConfig['packing_for_goods_car_parts'],
					'complex_pallet' => $arConfig['packing_for_goods_complex_pallet'],
					'complex_hard' => $arConfig['packing_for_goods_complex_hard'],
				]
			);
			foreach($arPackages as $packageUid){
				$arPackage = ['uid'=>$packageUid,'payer'=>'sender'];
				//если мешки или коробки, тоставим количество 1, т.к. поле обязательное, но мы не можем предсказать, сколько их нужно
				if($packageUid == "0x82750921BC8128924D74F982DD961379"
				   || $packageUid=="0x947845D9BDC69EFA49630D8C080C4FBE"
				   || $packageUid=="0xad97901b0ecef0f211e889fcf4624fed"
				   || $packageUid=="0xad97901b0ecef0f211e889fcf4624fea"
				) $arPackage['count']=1;
				$packages[] = $arPackage;
			}


			if(isset($packages)) $api_data["delivery"]["packages"] = $packages;

			if(!empty($arConfig['counteragent']) && "NULL" !== $arConfig['counteragent']){
				$api_data['members']['requester']['uid'] = $arConfig['counteragent'];
			}
			return $api_data;

		}else{

			$errors['empty_arrival_point'] = __('Empty arrival point','dellin-shipping-for-woocommerce');
		}
		if(!empty($errors)) return ['error'=>true,'errors'=>$errors];
	}

	/**
	 * Расчет конечной стоимости.
	 * @param object $response
	 * @param array $arConfig
	 * @return bool|float
	 */
	protected static function CalculatePrice($response, $arConfig)
	{
		$total_price = 0;
		$is_small_goods_price = ($arConfig['is_small_goods_price'] == 'yes');
		if ($response->data->price > 0) {
			$total_price = $response->data->price;
		}
		$additional_price = $total_price - $response->data->availableDeliveryTypes->auto;
		if ($is_small_goods_price && isset($response->data->availableDeliveryTypes->small)) {
			$total_price = $response->data->availableDeliveryTypes->small + $additional_price;
		}
		/**/
		return $total_price;
	}

    /**
     * Id для авторизации в API деловых линий
     * @var string
     */

    public static function GetCityKLADRCode($locationName, $state)
	{

		if (isset($locationName)) {
			$arName = explode(" ", trim($locationName));
			if ($arName > 3) {
				usort($arName, array('DellinApi', 'sort_func'));
				$searchString = implode(" ", array_slice($arName, 0, 2));
				$locationName = $searchString;
			}
			$dl_locations = self::SearchCity($locationName);
			if(empty($dl_locations)) return false;
		}
		if (count($dl_locations) > 0) {
			$dl_city = self::SelectCityByRegion($dl_locations, $locationName, $state);
			$kladr_code = (string)$dl_city->code;
		} else {
			return $dl_locations->errors;
		}

		return $kladr_code;
	}


	/**
     * Рассчитываем кол-во грузовых мест.
     * @param array $arOrder
     * @param array $arConfig
     * @return int
     */
    protected static function GetNumbersOfCargoPlaces($arOrder, $arConfig)
    {
        $numbers_of_places = 1;

        switch ($arConfig['LOADING_GROUPING_OF_GOODS']['VALUE']) {
            // Если считаем весь заказ, как 1 грузоместо.
            case 'ONE_CARGO_SPACE':
                break;

            // Если группируем каждый вид товара, как отдельное грузоместо.
            case 'SEPARATED_CARGO_SPACE':
                $numbers_of_places = count($arOrder['ITEMS']);
                break;

            // Если каждая единица товара - отдельное грузоместо.
            case 'SINGLE_ITEM_SINGLE_SPACE':
                $numbers_of_places = 0;

                foreach ($arOrder['ITEMS'] as $item) {
                    $numbers_of_places += $item['QUANTITY'];
                }
                break;
        }
        return $numbers_of_places;
    }

    /**
     * Конвертируем граммы в килограммы.
     * @param float $bx_goods_weight_in_gram
     * @return bool|float|int
     */
    protected static function ConvertWeightFromGramToKilogram($bx_goods_weight_in_gram)
    {
        $weight_in_kg = CSaleMeasure::Convert((float)$bx_goods_weight_in_gram, "G", "KG");
        return $weight_in_kg > 0 ? $weight_in_kg : 0.01;
    }

    /**
     * Получаем массив с параметрами веса, основываясь на перегрузе
     * @param float $bx_compare_weight
     * @param null|float $bx_set_weight
     * @return array
     */
    public static function GetWeightArray($bx_compare_weight, $bx_set_weight = null) {
        $compare_weight = self::ConvertWeightFromGramToKilogram($bx_compare_weight);
        $total_weight = is_null($bx_set_weight) ? $compare_weight : self::ConvertWeightFromGramToKilogram($bx_set_weight);

        if (ApiCore::IsOversizedWeight($compare_weight)) {
            return array('sized' => $total_weight, 'oversized' => $total_weight);
        }

        return array('sized' => $total_weight);
    }

    /**
     * Определяем параметры "sizedWeight" и "oversizedWeight" по способу группировки груза
     * @param array $arOrder
     * @param array $arConfig
     * @return array
     */
    static function CalculateWeight($arOrder, $arConfig)
    {
        $arWeight = array();
        switch ($arConfig['LOADING_GROUPING_OF_GOODS']['VALUE']) {
            // Если считаем весь заказ, как 1 грузоместо.

            case 'ONE_CARGO_SPACE':
                $arWeight = self::GetWeightArray($arOrder['WEIGHT']);
                break;

            // Если группируем каждый вид товара, как отдельное грузоместо.
            case 'SEPARATED_CARGO_SPACE':
                $max_item_weight = 0;
                $sum_item_weight = 0;
                foreach ($arOrder['ITEMS'] as $item) {
                    $item_weight = $item['WEIGHT'] * $item['QUANTITY'];
                    $sum_item_weight += $item_weight;

                    if ($item_weight > $max_item_weight) {
                        $max_item_weight = $item_weight;
                    }
                }

                $arWeight = self::GetWeightArray($max_item_weight, $sum_item_weight);
                break;

            // Если каждая единица товара - отдельное грузоместо.
            case 'SINGLE_ITEM_SINGLE_SPACE':
                $max_item_weight = 0;
                $sum_item_weight = 0;

                foreach ($arOrder['ITEMS'] as $item) {
                    $sum_item_weight += $item['WEIGHT'] * $item['QUANTITY'];

                    if ($item['WEIGHT'] > $max_item_weight) {
                        $max_item_weight = $item['WEIGHT'];
                    }
                }
                $arWeight = self::GetWeightArray($max_item_weight, $sum_item_weight);
                break;
        }

        if(empty($arWeight)) $arWeight['sized'] = 0.1;
        return $arWeight;
    }

    static function convertToM($value,$units){
		if($value == '')
		{
			return null;
		}

		switch($units){
			case 'cm':
				return $value/100;
			break;
			case 'mm':
				return $value/1000;
			break;
			case 'm' :
				return $value;
			break;
		}
    }
	static function convertToKg($value,$units){
		switch($units){
			case 'g':
				return $value/1000;
				break;
			case 't':
				return $value*1000;
				break;
			case 'kg' :
				return $value;
				break;
		}
	}
	static function productOversizeValidation($arDimensions,$stricts){
    	//ВАЖНО: ДЛИНА В STRICTS ДОЛЖНА БЫТЬ ЗАПОЛНЕНА САМЫМ БОЛЬШИМ ЗНАЧЕНИЕМ СТОРОНЫ (13.6м для фуры, к примеру).
    	//смотрим на каждый товар и проверяем, можно ли уложить его в фуру как есть и с поворотом. Если нет, вообще дальше не считаем
		$length = $arDimensions['length'];
		$width = $arDimensions['width'];
		$height = $arDimensions['height'];
		$totalWeight = $arDimensions['totalWeight'];
		$totalVolume = $arDimensions['totalVolume'];
		if($totalWeight >= $stricts['totalWeight']) return false;
		if($totalVolume >= $stricts['totalVolume']) return false;

		//если товар торчит из фуры/грузоместа по самой длинной стороне ('length'), то даже с поворотом груз не уложить
		if($length > $stricts['length'] || $width > $stricts['length'] || $height > $stricts['length']){
			return false;
		}
		$isOversized = true;
		//Если все стороны меньше минимального значения одной стороны, то товар габаритный.
		// Если есть сторона больше минимального значения (но меньше stricts['length']),
		// то 2 остальные не могут быть больше минимального значения, поэтому вертим товар во всех трех плоскостях.
		// Если находим такое положение, где товар не выпирает из габаритов, значит товар габаритный.
		if(
			($length <= $stricts['width_height'] && $length <= $stricts['width_height'] && $height<= $stricts['width_height'])||
			($length > $stricts['width_height'] && $width <= $stricts['width_height'] && $height <= $stricts['width_height']) ||
			($width > $stricts['width_height'] && $length <= $stricts['width_height'] && $height <= $stricts['width_height']) ||
			($height > $stricts['width_height'] && $length <= $stricts['width_height'] && $width <= $stricts['width_height'])
		){
			$isOversized = false;
		}
		if($isOversized) return false;

		//По итогу всех проверок, если общий вес/объем товаров вписывается в фуру/грузоместо
		// и товар можно положить так, чтобы он не выпирал из фуры/грузоместа,
		// значит он габаритный
		return true;
	}

	private static function getProductsList($cartItems){
        $productsList = [];
        $products = ($cartItems->isDataInOrder)?$cartItems->get_items() : $cartItems->get_cart();


        foreach($products as $item){
	        $itemId = ($cartItems->isDataInOrder)?$item['product_id'] : $item['data']->get_id();
	        $itemQuantity = ($cartItems->isDataInOrder)? $item->get_quantity() : $item['quantity'];
            $_product =  wc_get_product($itemId);
            $productsList[] = [
                'id'        => $_product->get_id(),
                'name'      => $_product->get_name(),
                'slug'      => $_product->get_slug(),
                'length'    => $_product->get_length(),
                'width'     => $_product->get_width(),
                'height'    => $_product->get_height(),
                'weight'    => $_product->get_weight(),
                'price'     => $_product->get_price(),
                'quantity'  => $itemQuantity

            ];
        }

		//debug
//		echo '<pre>';
//		var_dump($products);
//		echo  '</pre>';
//		die();

        return $productsList;
    }

	static function convertProductDimensions(&$products){
    	foreach($products as &$product){
            $productDimensionUnit = get_option('woocommerce_dimension_unit');
            $productWeightUnit    = get_option('woocommerce_weight_unit');
            $product['length']    = self::convertToM($product['length'],$productDimensionUnit);
            $product['width']     = self::convertToM($product['width'],$productDimensionUnit);
            $product['height']    = self::convertToM($product['height'],$productDimensionUnit);
            $product['weight']    = self::convertToKG($product['weight'],$productWeightUnit);
	    }
	}

    static function getCargoParams($cart,$arConfig){

        $products = self::getProductsList($cart);
    	self::convertProductDimensions($products);
    	$maxProductLength = 0;
    	$maxProductHeight = 0;
    	$maxProductWidth = 0;
	    $weight = 0;
    	$totalWeight = 0;
    	$totalVolume = 0;
	    $oversizedVolume = 0;
	    $oversizedWeight = 0;
	    $freightName='';

    	foreach($products as $index=>$product){
			
    		if(self::hasNumberValue($product['length']) > $maxProductLength) $maxProductLength = $product['length'];
    		if(self::hasNumberValue($product['height']) > $maxProductHeight) $maxProductHeight = $product['height'];
    		if(self::hasNumberValue($product['width'])  > $maxProductWidth) $maxProductWidth = $product['width'];
			
			
    		$totalWeight += self::hasNumberValue($product['weight'])*$product['quantity'];

    		$totalVolume += self::hasNumberValue( $product['length']) *
							self::hasNumberValue( $product['height'] ) *
							self::hasNumberValue( $product['width']) *
							self::hasNumberValue( $product['quantity']);

		    $freightName .= (($index != 0) ? ',' : '') . $product['name'];
	    }
	    $arDimensions = [
	    	'length'=>$maxProductLength,
		    'width'=>$maxProductWidth,
		    'height'=>$maxProductHeight,
		    'totalWeight'=>$totalWeight,
		    'totalVolume'=>$totalVolume
	    ];
	    $globalStricts = [
		    'length'=>13.6,
		    'width_height'=>2.4,
		    'totalVolume' => 80,
		    'totalWeight' => 20000
	    ];
	    $placeStricts = [
		    'length' => 3,
		    'width_height'=>3,
		    'totalVolume'=>27,
		    'totalWeight' => 100
	    ];
	    $smallStricts = [
            'length' => 0.54,
            'width_height'=>0.39,
            'totalVolume'=>0.1,
            'totalWeight' => 10
        ];

		if(self::productOversizeValidation($arDimensions,$globalStricts)){//проверяем габариты товаров, нет ли сликом огромных или тяжелых для всей фуры
			switch ($arConfig['loading_grouping_of_goods']) {
				//Если весь товар в одно грузоместо
				case 'ONE_CARGO_SPACE':
					$quantity  = 1;
					if(!self::productOversizeValidation($arDimensions, $placeStricts)){
						$oversizedVolume = $totalVolume;
						$oversizedWeight = $totalWeight;
					}
					$weight = $totalWeight;
					break;
				// Если группируем каждый вид товара, как отдельное грузоместо.
				case 'SEPARATED_CARGO_SPACE':
					$quantity = count($products);
					$maxPlaceWeight = 0;
					foreach($products as $product){
						$arDimensions = [
							'length' => $product['length'],
							'width'=>$product['width'],
							'height'=>$product['height'],
							'totalWeight'=>$product['weight'] * $product['quantity'],
							'totalVolume'=>$product['length']*$product['width']*$product['height']*$product['quantity']
						];
						if($arDimensions['totalWeight'] > $maxPlaceWeight) $maxPlaceWeight = $arDimensions['totalWeight'];
						if(!self::productOversizeValidation($arDimensions,$placeStricts)){
							$oversizedWeight += $arDimensions['totalWeight'];
							$oversizedVolume += $arDimensions['totalVolume'];
						}
					}
					$weight = $maxPlaceWeight;
					break;
				// Если каждая единица товара - отдельное грузоместо.
				case 'SINGLE_ITEM_SINGLE_SPACE':
					$quantity = 0;
					$maxProductWeight = 0;
					foreach ($products as $product){
						$arDimensions = [
							'length'        => $product['length'],
							'width'         =>$product['width'],
							'height'        =>$product['height'],
							'totalWeight'   =>$product['weight'],
							'totalVolume'   =>$product['length']*$product['width']*$product['height']
						];
						if($product['weight'] > $maxProductWeight) $maxProductWeight = $product['weight'];
						if(!self::productOversizeValidation($arDimensions,$placeStricts)){
							$oversizedWeight += $arDimensions['totalWeight']*$product['quantity'];
							$oversizedVolume += $arDimensions['totalVolume'];
						}
						$quantity += $product['quantity'];
					}
					$weight = $maxProductWeight;

					break;
			}
			$small_available_packages = [
			    'packing_for_goods_box',
                'packing_for_goods_additional',
                'packing_for_goods_bubble',
                'packing_for_goods_bag'

            ];
			$packagesAvailable = true;
			foreach ($arConfig as $id=>$param){
			    if(strpos($id,'packing_for_goods_') && !in_array($id,$small_available_packages)){
                    $packagesAvailable = false;
                }
            }
			if($arConfig['loading_grouping_of_goods'] !== 'ONE_CARGO_SPACE' || $arConfig['is_small_goods_price'] != 1 ||
                !self::productOversizeValidation($arDimensions,$smallStricts) || !$packagesAvailable ||
                $arConfig['is_goods_loading'] != 1 || $arConfig['is_goods_unloading'] != 1 ){
			    $deliveryType = 'auto';
            }else{
			    $deliveryType = 'small';
            }
            $maxSide = $maxProductLength;
			if($maxProductWidth > $maxProductLength){
                $maxSide = $maxProductWidth;
                $maxProductWidth = $maxProductLength;
                $maxProductLength = $maxSide;
            }

            if($maxProductHeight > $maxProductLength){
                $maxSide = $maxProductHeight;
                $maxProductHeight = $maxProductLength;
                $maxProductLength = $maxSide;
            }

			$result = [
				'quantity'          =>$quantity,
				'length'            =>$maxProductLength?round($maxProductLength,2):0.01,
				'width'             =>$maxProductWidth?round($maxProductWidth,2):0.01,
				'height'            =>$maxProductHeight?round($maxProductHeight,2):0.01,
				'weight'            =>$weight,
				'totalVolume'       =>$totalVolume,
				'totalWeight'       =>$totalWeight,
				'oversizedWeight'   =>$oversizedWeight,
				'oversizedVolume'   =>$oversizedVolume,
				'freightName'       => $freightName,
                'deliveryType'      => $deliveryType
			];
		} else {
			return false;
		}

		return $result;
    }

    /**
     * Получаем массив из id типов упаковки.
     * @param array $bx_fields
     * @return array
     */
    public static function GetSelectedPackingTypesId(/*$requestType,*/$fields)
    {
        $packagesArray = self::$request_packages_id;
        $value_list = array();

        foreach ($fields as $key => $item) {
            if (!isset($item) || $item !== 'yes') {
                continue;
            }

            $value_list[] = $packagesArray[$key];
        }
        return $value_list;
    }



    /**
     * Проверяем код на валидность.
     * @param array $data
     * @param array $arOrder
     * @param array $arConfig
     * @return bool
     */
    public static function IsDataValid($data, $cart, $arConfig)
    {



        $errors = array();
        $status = 'ok';
        if($cart->totalPrice <= 0){
            $errors['zero_order_price'] = __('Zero order price','dellin-shipping-for-woocommerce');
        }

        if($data["appkey"] == ''){
            $errors['empty_app_key'] = __('API key is not set','dellin-shipping-for-woocommerce');
        }

        if(($data['delivery']['derival']['variant'] == 'terminal' && $data['delivery']['derival']['terminalID']=="") ||
            ($data['delivery']['derival']['variant'] == 'address' && (!isset($cart->address) ||
             $data['delivery']['derival']['address']['search']==""))){
	        $errors['empty_derival_point'] = __('Departure point is not set','dellin-shipping-for-woocommerce');
        }

	    if(($data['delivery']['arrival']['variant'] == 'address'
           && (!isset($cart->address) || $data['delivery']['arrival']['address']['search']==""))){
            $errors['empty_arrival_point'] = __('Arrival point is not set','dellin-shipping-for-woocommerce');
        }

        if($data['delivery']['arrival']['variant'] == "terminal" || $data['delivery']['derival']['variant'] == "terminal")   {
	        if(($data['delivery']['arrival']['variant'] == 'terminal' && $arConfig['kladr_code_delivery_from'] == $data['delivery']['arrival']['city']) ||
		        ($data['delivery']['arrival']['variant'] == 'address' && (isset($data['delivery']['arrival']['address']['street']) && $arConfig['kladr_code_delivery_from'] == $data['delivery']['arrival']['address']['street']))
	        ){
		        $errors['same_city_terminal_delivery'] = __('Delivery to terminal is not available for the same arrival city as departure city','dellin-shipping-for-woocommerce');
	        }
        }



        if(!empty($errors)){
            $status = 'error';
            //debug
//        echo '<pre>';
//        var_dump($errors);
//        echo '</pre>';
//        die();
        }



	    return array(
		    'status'=> $status,
		    'errors' => $errors
	    );

    }

	/**
	 * @param $user_id
	 *
	 * @return mixed
	 */

    function getUserShippingData($user_id){
		return get_user_meta( $user_id, 'shipping_address_1', true );
    }

	/**
	 * @param $data
	 * @param $deliveryConfig
	 * @param bool $baseDate
	 *
	 * @return bool|mixed
	 * @throws Exception
	 */
	public static function GetProduceDate($data,$deliveryConfig,$baseDate=false){
        //if(!empty(self::$lang)) self::getLang();
		if(!$baseDate) $baseDate = $data['produceDate'];
		$produceDate = self::sendApiRequest('produceDate',$data,'json');
		if(!isset($produceDate)){
			return false;
		}
		if(isset($produceDate->errors)){
			return $produceDate->errors;
		}
		if ($produceDate->produce_available) {//проверяем, возможна ли отгрузка хоть на какой-нибудь терминал в городе в эту дату
			if($deliveryConfig['is_goods_loading'] == "0") {
				//если да, проверяем расписание терминала
				if(!empty($deliveryConfig['terminal_id'])){
					$terminal = self::GetTerminals($deliveryConfig['api_key'],$deliveryConfig['kladr_code_delivery_from'], $deliveryConfig['terminal_id'])['terminal'];
					foreach ($terminal->worktables->worktable as $worktable) {
						$departmentname = trim($worktable->department);
						if ($departmentname ==  'Приём и выдача груза' || $departmentname == 'Приём груза') {
							$weekDay = strtolower(date('l',strtotime($data['produceDate'])));
							if ($worktable->{$weekDay} !== "-" ) {
								if(str_replace(" ","",$worktable->{$weekDay}) == 'Приём груза'){
									$startTime = strtotime(date('Y-m-d') . " " . "00:00");
									$endTime =  strtotime(date('Y-m-d') . " " . "23:59");
									$workHours = array($startTime,$endTime);
								}else{
									$workHours = explode("-", $worktable->{$weekDay});
									$startTime = strtotime(date('Y-m-d') . " " . $workHours[0]);
									$endTime = ($workHours[1] == '24:00')? strtotime(date('Y-m-d') . " 23:59"):strtotime(date('Y-m-d') . " " . $workHours[1]);
								}

								$shopStartTime = strtotime((($deliveryConfig['work_start']<10)?"0":"").$deliveryConfig['work_start'].":00");
								$shopEndTime = strtotime((($deliveryConfig['work_end']<10)?"0":"").$deliveryConfig['work_end'].":00");

								if ($shopEndTime <= $startTime || $shopStartTime >= $endTime) {
									$produceDate->changeTime = $workHours;
								}
								$produceDate->nearestDate = $data['produceDate'];
								break;
							}else{

								$oldProduceDate = new DateTime($data['produceDate']);
								$newProduceDate = $oldProduceDate->modify('+1 day');
								$datediff = self::dateDifference($newProduceDate->format('Y-m-d'),$baseDate);
								if($datediff <=2){
									$data['produceDate'] = $newProduceDate->format('Y-m-d');
									$produceDate = self::GetProduceDate($data,$deliveryConfig,$baseDate);
									$produceDate->DATE_CHANGED = true;
								}
							}
						}
					}
					if(!isset($produceDate->nearestDate)){
						$produceDate->produce_available = false;
						$produceDate->errors['error_terminal_department'] = __('selected terminal can not accept this cargo','dellin-shipping-for-woocommerce');
					}
				}else{
					$produceDate->errors['error_terminal_id'] = self::$lang->_('There are no terminals for shipping at specified date. Please try to change delivery delay in module settings','dellin-shipping-for-woocommerce');
				}
			}else{
				$produceDate->nearestDate = $data['produceDate'];
			}
		}else{
			$oldProduceDate = new DateTime($data['produceDate']);
			$newProduceDate = $oldProduceDate->modify('+1 day');
			$datediff = self::dateDifference($newProduceDate->format('Y-m-d'),$baseDate);
			if($datediff <=2){
				$data['produceDate'] = $newProduceDate->format('Y-m-d');
				$produceDate = self::GetProduceDate($data, $deliveryConfig, $baseDate);
				$produceDate->DATE_CHANGED = true;
			}
			else{
				wc_add_notice(esc_html('Невозможно определить дату предполагаемой отгрузки для метода доставки'),'error');
			}
		}
		return $produceDate;
	}

	public function getInstanceId(){
			return get_option('dellin_shipping_instance_id');
	}
	/**
	 * Разница между датами
	 * @param $date_1
	 * @param $date_2
	 * @param string $differenceFormat
	 * @return string
	 */
	public static function dateDifference($date_1 , $date_2 , $differenceFormat = '%a' )
	{
		$interval = date_diff($date_1, $date_2);

		return $interval->format($differenceFormat);

	}

	public static function getCounterAgents($data){
		$arCounterAgents = [];
		$counterAgentsData = ApiCore::sendApiRequest('requestCounteragents',$data);
		if(!isset($counterAgentsData->errors)) {
			$counterAgents = $counterAgentsData->counteragents;
			if(!empty($counterAgents)){
                foreach ($counterAgents as $counterAgent) {
                    if ($counterAgent->uid) {
                        $arCounterAgents[$counterAgent->uid] = $counterAgent;
                    }
                }
            }
		}
		return $arCounterAgents;
	}

	public static function getOpf($appkey){
		$arCountriesList = [];
		$arOpfList = [];
		if(isset($appkey)){
			$opfList = ApiCore::GetOpfList($appkey)['list'];
			if(isset($opfList)){
				$countries = ApiCore::GetCountries($appkey);

				foreach($countries as $country){
					$arCountriesList[$country['countryUID']] = $country['country'];
				}
				asort($arCountriesList);

				foreach($opfList as $id => $opf){
					$arOpfList[$opf['countryUID']][$opf['uid']] = $opf['name'];
					asort( $arOpfList[$opf['countryUID']]);
				}

			}
			return array('opf'=>$arOpfList,'countries'=>$arCountriesList);
		}
	}




	public static function getRequestTerminals($data){
		$terminalsOb = ApiCore::sendApiRequest('requestTerminal',$data);
		$terminalsInfo = ApiCore::GetTerminals($data['appkey']);
		if(isset($terminalsInfo) && !empty($terminalsInfo)){
			foreach ($terminalsOb->terminals as $terminal) {
				if(isset($terminalsInfo[$terminal->city_code]['terminals'][$terminal->id]) && $terminalsInfo[$terminal->city_code]['terminals'][$terminal->id]->receiveCargo){ //если терминал может принять товар
					$arTerminalIdValues[$terminal->id] = $terminal->address;
				}
			}
		}
		return $arTerminalIdValues;
	}

    /**
     * Получаем sessionId из кэша или авторизуемся
     * */
	public static function getDellinSessionId($arConfig){
	    //TODO добавить кэширование с помощью внешнего плагина, т.к. станадртное работает не глобально.
		if($arConfig['login'] && $arConfig['password']){
            // пробуем получить кэш и вернем его если он есть
            $cache_key = 'dellin_shipping';
            $expire = 30*24*60*60;
            if(!wp_cache_get( $cache_key ) || isset($arConfig['reset_session'])){
				$result = self::sendApiRequest('login',$arConfig);
				if(isset($result->sessionID)){
					$sessionId = $result->sessionID;
                    wp_cache_set( $cache_key, $result,"",$expire ); // добавим данные в кэш
				}

			} else{
                $cache = wp_cache_get( $cache_key );
				$sessionId = $cache->sessionID;
			}
			if(isset($sessionId)){
				return $sessionId;
			}else{
				return $result;
			}

		}
		else{
			return false;
		}
	}

	/**
	 * @param $instance_id
	 *
	 * @return bool|mixed|void
	 */
	public static function getConfig($instance_id){

		$config = get_option('woocommerce_dellin_shipping_calc_' . $instance_id. '_settings');
		
		return $config;
	}

	public static function getDataForOrder($idOrder){

		$order = wc_get_order($idOrder);
		$dataOrder = $order->get_data();
		$cart = $order;
		$shipping_method = @array_shift($order->get_shipping_methods());

		$cart->shipping_country = $dataOrder['shipping']['country'];
		$cart->state = $dataOrder['shipping']['state'];
		$cart->postcode = $dataOrder['shipping']['postcode'];
		$cart->address = $dataOrder['shipping']['address_1'];
		$cart->deliveryCity = $dataOrder['shipping']['city'];
		$cart->payment_method = $dataOrder['payment_method'];
		$cart->totalPrice = $order->get_subtotal();
		$cart->shippingPrice = $shipping_method['total'];
		$cart->contact_phone = $dataOrder['billing']['phone'];
		$cart->contact_email = $dataOrder['billing']['email'];
		$cart->customer_id = $dataOrder['customer_id'];
		$cart->terminal_id = get_user_meta( $cart->customer_id, 'terminal_id', true );
		$user_id_company = get_user_meta( $cart->customer_id, 'company', 'on' );
		$cart->contact_person = $dataOrder['shipping']['first_name'].' '.$dataOrder['shipping']['last_name'];
		$cart->idOrder = $idOrder;
		$cart->created_date = $order->order_date;
		$cart->isDataInOrder = true;

		$cart->person_type = ($user_id_company !== 'no')?'0':'1';
		if ($cart->person_type == '0'){
			$cart->organisation_name = get_user_meta( $cart->customer_id, 'organisation_name', true );
			$cart->organisation_address = get_user_meta( $cart->customer_id, 'organisation_address', true );
			$cart->organisation_inn = get_user_meta( $cart->customer_id, 'organisation_inn', true );
		//	$cart->organisation_opf = get_user_meta($cart->customer_id, 'organisation_opf', true);
		}

        $cart->worktime_start = get_user_meta( $cart->customer_id, 'worktime_start', true );
        $cart->worktime_end = get_user_meta( $cart->customer_id, 'worktime_end', true );

		return $cart;
	}

	public static function hasNumberValue($value)
	{
		$initialValue = $value;
		
		if(is_null($value))
		{
			return 0;
		}

		if(is_string($value) && $value == '')
		{
			$value = 0;
		}
		return floatval($value);
	}

	/**
	 * @param $idOrder
	 * @param bool $orderUpdate
	 * @param null $itemId
	 *
	 * @return array|bool|mixed|string[]|WP_Error
	 */
	static function CreateOrder($idOrder, $orderUpdate = false, $itemId = null){

		$order = wc_get_order($idOrder);
		$shipping_method = @array_shift($order->get_shipping_methods());
		$config = self::getConfig($shipping_method['instance_id']);
		$cart = self::getDataForOrder($idOrder);

		if($orderUpdate !== true || $orderUpdate = false) {

			$calcResult = self::ReCalculateOrder( $config, $cart );

			if ( $calcResult['status'] !== 'error' && $calcResult['STATUS'] !== 'API_UNAVAILABLE' ) {


				if ( $calcResult['PRICE_CHANGED'] ) {

					$result = $calcResult;
					try {
						//Проверяем указан ли айдишник метаполя где содержится стоимость, если указана изменяем заказ.
						if ( ! is_null( $itemId ) ) {
							if ( wc_update_order_item_meta( $itemId, 'cost', $calcResult['body']['price'], $cart->shippingPrice ) ) {
								$body   = array( "STATUS" => "OK", 'orderUpdate' => true,
												  'data'=> array('state'=>'processing'));
								$result = $body;
							}
						}
					} catch ( Exception $e ) {
						// Если произошла ошибка, пробуем транзакцией откатить и возвращаем ошибку.
						wc_transaction_query( 'rollback' );

						return new WP_Error( 'checkout-error', $e->getMessage() );
					}
				} else {

					$data = self::GetDeliveryData( $cart, $config, 'request' );
					if ( isset( $data->errors ) ) {
						$errorBody = '';
						if ( is_object( $data->errors ) ) {
							$arCategories = get_object_vars( $data->errors );
							foreach ( $arCategories as $catCode => $error ) {
								$arErrors = get_object_vars( $errors );
								foreach ( $arErrors as $code => $error ) {
									$errorBody .= '[' . $code . ']' . $error . '<br/>';
								}
							}
						} else {
							foreach ( $data->errors as $code => $error ) {
								$errorBody .= '[' . $code . ']' . $error . '<br/>';
							}
						}
						$result = array( 'STATUS' => 'ERROR', 'BODY' => $errorBody );

					} else {
						$result = ApiCore::sendApiRequest( 'request', $data );


						if ( $result->data->state == 'success' ) {
							// сохраняем трек
							//	var_dump($result->data->requestID);
							update_post_meta( $idOrder, '_dellin_track_id',  $result->data->requestID, '' );

							return $result;

						} else {

						    $errorBody = '';
                            foreach($result->errors as $error){
                                $errorBody .= '['.$error->code.'] '.$error->detail.' | '.$error->fields[0].'<br/>';
                            }
                            $result = array('status' => 'error',
                                'errors' => $errorBody,
                                'data'=>array('state' => 'process'));


                        }

                    }
				}
			} else {
				if ($calcResult['status'] == 'error'){
					$errorBody = '';
					if ( isset( $calcResult['body'] ) ) {
						$errorBody .= $calcResult['body'];
					}

						$result = array( 'status' => 'error', 'body' => $errorBody );

				}

			}

		}


		return $result;

	}

	/**
	 * Пересчет заказа перед созданием заявки
	 */
	function ReCalculateOrder($config, $cart){
		$calcResult = self::Calculate($cart, $config);
		if($calcResult['status'] !== "error" && $calcResult['STATUS'] !== "API_UNAVAILABLE"){
			$price = $calcResult['body']['price'];
			if(isset($price)){
				if((int)$cart->shippingPrice !== (int)$price){
					$calcResult['data']['state'] = 'processing';
					$calcResult['PRICE_CHANGED'] = true;
				}
				if($calcResult['BODY'][3]){
					$calcResult['TERMINAL_RECEIVE_TIME_CHANGED'] = true;
				}
			}
		}

//		echo '<pre>';
//		var_dump($calcResult);
//		echo '</pre>';
//		die();
		return $calcResult;

	}


    /**
     * Получение статуса заказа из API
     * */
    public static function GetOrderStatus($trackID, $instance_id){

        $config = self::getConfig($instance_id);
        $params = array('appkey' => $config['appkey'], 'docid' => $trackID);

        if (isset($params['docid'])) {
            $result = ApiCore::GetStatus($params);
            return $result;
        } else {
            return false;
        }

        if ($result && !isset($result->errors)) {
            return get_object_vars($result);
        } else {
            return false;
        }
    }


	/**
	 *
	 * Метод проверяющий наложный платёж.
	 *
	 * @param $dataInOrder
	 * @return bool
	 */
	protected static function isCacheOnDelivery($dataInOrder){
		return $dataInOrder == 'cod';
	}

	public static function getTerminalsCheckout ($methods, $cartCity, $cartState){
		$arTerminals[''] = __('Terminal not selected','dellin-shipping-for-woocommerce');
		foreach ($methods as $item){
			if($item->get_method_id()  == 'dellin_shipping_calc'){
				$instance_id = $item->get_instance_id();
				$config = DellinApi::getConfig($instance_id);
				$apiKey = $config['appkey'];
				if($apiKey){
					if('' !== $cartCity){
						$kladr = DellinApi::GetCityKLADRCode($cartCity,$cartState);
						$data = array(
							"appkey" =>$apiKey,
							"code" => trim($kladr),
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
			}
			return $result;
//			var_dump($result);
//			die();
		}

	}


	private static function sort_func($a,$b)
	{
		if (strlen($a) == strlen($b))
		{
			//  return 0;
		}

		return (strlen($a) > strlen( $b)) ? -1 : 1;
	}

	/**
	 * Метод поиска города
	 */

	public static function locationSearch($q, $appkey){

        $q = str_replace('ё','е', $q);

        //По-умолчанию лимит 35 элементов
        $bodyRequest = [
            'appkey' => $appkey,
            'q' => $q
        ];

        $response = self::sendCurl(ApiCore::$apiUrls['locationKLADR'], json_encode($bodyRequest));



        return json_decode($response)->cities;

    }

	public static function findKLADR($appkey, $locationName,
							 $regionName, $zip)
    {
        //переводим все строковые параметры в нижний регистр.

        $locationName = mb_strtolower($locationName);
        $regionName = mb_strtolower($regionName);

        //числовой тип приводим к числовому
        $zip = (int)$zip;


        //Фильтруем вводные параметры населённого пункта.
        //Список для фильтрации сформирован согласно ОКАТО и ОКТМО
		// для субьектов федерации.
		// необходимость для городов требует анализа. 
        $typesSubjectFed = ['автономная область','автономный округ', 'ао',
            				'Республика', 'республика', 'край', "область", 'Респ.',
            				'респ.', 'обл', 'г.', 'г '];

		$shortLocationName = $locationName;// тут пока ничего не фильтруем, т.к. ручной ввод. 
        $shortRegionName = str_replace($typesSubjectFed, '', $regionName);



        //Работа по населённому пункту входящему в состав субъекта федерации

        if(array_key_exists($shortLocationName, self::listForPlaces())) {
            foreach(self::listForPlaces() as $place => $value){
                if($place == $shortLocationName){
                    $shortLocationName = $value['cityName'];
                    $shortRegionName = $value['regionName'];
                }
            }
        }

        //Работа с исключениями по субъектам федерации


        if(array_key_exists($shortRegionName, self::listForRegionName())){

            foreach (self::listForRegionName() as $regionCandidat => $regionValue){
                if($shortRegionName == $regionCandidat){
                    $shortRegionName = $regionValue;
                }
            }
        }

        $q = $shortLocationName." ".$shortRegionName;


        if(array_key_exists($q, self::listForQuery())){

            foreach (self::listForQuery() as $regionCandidat => $values){
                if($regionCandidat == $q){

                    $q = $values['q'];

                    $shortRegionName = $values['regionName'];
                    $shortLocationName = $values['cityName'];
                }
            }
        }

        $locationList = self::locationSearch($q, $appkey);


// для логгера, т.к. не предусмотренно логгирование пока за скобками.
        // if(empty($locationList) || (count($locationList) < 1)){

        //     $fnName = 'locationKLADR';

        //     $message = Loc::getMessage("SPRINGF_MESSAGE_ERROR");

        //     $context = ['city_list' => json_encode($locationList, JSON_UNESCAPED_UNICODE),
        //         'fnName' => $fnName,
        //         'zip' => $zip,
        //         'param_city_name' => $locationName,
        //         'param_region_name' => $regionName,
        //         'short_region_name' => $shortRegionName,
        //         'short_location_name' => $shortLocationName,
        //     ];

        //     $this->logger->error($message, $context);

        //     throw new Exception(Loc::getMessage("LIST_CITIES_IS_EMPTY"));
        // }

        //TODO подумать как описать в логгере

        if(count($locationList) == 1){

            $result = $locationList[0];

        } else {

            $result = self::selectLocationIfPlacesMany($locationList, $shortLocationName, $shortRegionName, $zip);

        }

		// для логгирования, пока без него. 
        // $fnName = 'locationKLADR';


        // $message = Loc::getMessage('SPRINTF_CITY_RESULT_IN_LOGGER');


        // $context = ['city_list' => json_encode($locationList, JSON_UNESCAPED_UNICODE),
        //     'fnName' => $fnName,
        //     'zip' => $zip,
        //     'param_city_name' => $locationName,
        //     'param_region_name' => $regionName,
        //     'short_region_name' => $shortRegionName,
        //     'short_location_name' => $shortLocationName,
        //     'dl_city' => json_encode($result, JSON_UNESCAPED_UNICODE)
        // ];


        // $this->logger->debug($message, $context);

        return $result;

    }


	public static function selectLocationIfPlacesMany($locationList, $shortLocationName, $shortRegionName, $zip = false){

        //массив объектов которые подходят по условия первой итерации списка городов от api.
        //сокращаем количество элементов до одного.
        $poolPlaces = [];


        foreach ($locationList as $location){

            $itemLocationName = mb_strtolower($location->searchString);
            $isRegionEqual = self::checkEqual(trim($shortRegionName), trim($location->region_name));
            $isLocationEqual = self::checkEqual(trim($itemLocationName), trim($shortLocationName));

            if($isLocationEqual && $isRegionEqual){
                $poolPlaces[] = $location;
            }

        }


        if(count($poolPlaces) !== 1){

            foreach ($poolPlaces as $place){

                if($place->postalCode == $zip){
                    $result = $place;
                }

            }

        } else {
            $result = $poolPlaces[0];
        }

        // if(empty($result)){


        //     $fnName = 'locationKLADR';

        //     $message = Loc::getMessage("ERROR_MESSAGE_MANY_PLACES");

        //     $context = ['city_list' => json_encode($locationList, JSON_UNESCAPED_UNICODE),
        //                 'fnName' => $fnName,
        //                 'zip' => $zip,
        //                 'short_region_name' => $shortRegionName,
        //                 'short_location_name' => $shortLocationName,
        //                 'poolPlaces' => json_encode($poolPlaces, JSON_UNESCAPED_UNICODE),
        //                 'result' => json_encode($poolPlaces[0], JSON_UNESCAPED_UNICODE)
        //     ];

        //     $this->logger->error($message, $context);


        //     //TODO временное решение, пока в методе API не добавят все индексы.
        //     // данное решение от части верное
             $result = $poolPlaces[0];
        // }

        return $result;

    }

    /**
     * Метод для проверки названия субъекта федерации.
     * Строгое сравнение с субъектами не требуется.
     * @param $needly
     * @param $haystack
     * @return bool
     */

    public static function checkEqual($needly, $haystack){

       //Приводим к нижнему регистру вводный параметр
        if(strpos(mb_strtolower($haystack), mb_strtolower($needly)) === false){
            return false;
        } else {
            return true;
        }

    }

    public static function listForRegionName(){

		//От коллектора иногда приходят пустые значения.
		 return [
			" саха (якутия)" => "саха",
			" северная осетия-алания"=> 'алания',
			' марий эл' => "марий",
		 ];
	 }
 
	 public static function listForPlaces(){
 
	 //метод предназначен для городов федерального значения, которые иногда ошибочно приписывают к областям,
	 //например, "севастополь крым" - это ошибка, севастополь это город федерального значения.
 
		 return array(
			 "москва" => array(
				 'cityName' => "москва",
				 'regionName' => "москва"
			 ),
			 "севастополь" => array(
				 'cityName' => "севастополь" ,
				 'regionName' => "севастополь"
			 ),
			 "санкт-петербург" => array(
				 'cityName' => "санкт-петербург",
				 'regionName' => "санкт-петербург"
			 ),
		 );
	 }
 
	 public static function listForQuery(){
		 return [
			"бел белгородская " => [
			   'q'=> "белгород белгородская",
			   'cityName' => "белгород",
			   'regionName'=> "белгородская"
		   ]
		 ];
	 }


}
