<?php
declare(strict_types=1);


namespace Biatech\Lazev\Helpers;

use Biatech\Lazev\Repositories\WoocommerceSettings;

/**
 * Имплементация конвертера настроек.
 * Маппинг упаковок, доп. услуг не реализован из-за недостатка времени.
 */


final class LegacySettingsMapper {


    const MAP_FIELDS = [
        //legacy => current
        'counteragent' => 'counteragents',
        'opf_country' => 'country',
        'sender_form' => 'opf',
        'sender_name' => 'companyName',
        'sender_inn' => 'inn',
        'sender_juridical_address' =>'yuriAddress',
        'sender_contact_name'  => 'contactPersonName',
        'sender_contact_phone' => 'contactPersonPhone',
        'sender_contact_email' => 'contactPersonEmail',
        'appkey'=>'appkey',
        'login'=>'login',
        'password'=> 'password',
        'delivery_delay' => 'defferedDays',
        'kladr_code_delivery_from' => 'locationKladr',
        'is_small_goods_price' => 'includeSmallCargo',
        'is_insurance_goods_with_declared_price'=> 'isInsurance',
        'is_goods_loading' => 'derrivalType',
        'is_goods_unloading' => 'arrivalGoodsLoading',
        'loading_transport_equipments' => 'additionalPackages',
        'loading_transport_requirements' => 'additionalServices',
        'loading_address' => 'fieldAddress',
        'terminal_id' => 'fieldTerminal'
    
    ];

    const MAP_ADD_EMPTY = [
        'intervalWork',
        'intervalLunch',
        'groupTupe',
        'location',
        'isUseDefaultCargoParams',
        'typeLoading',
        'requirementsTransport',
        'isLogs',
        'title',
        'groupType'

    ];

    private ?WoocommerceSettings $repository;
    
    public function __construct()
    {
        $this->repository = new WoocommerceSettings();
    }

    public function adaptiveLegacyByInstanceId(int $instance_legacy_id ): string
    {
        $storage = [];
        $legacyOptions = $this->repository->get_legacy_options_by_instance_id($instance_legacy_id);
        
        foreach ($legacyOptions as $key=> $value)
        {
            //пропускаем, то что не знаем
            if(!isset(self::MAP_FIELDS[$key]))
            {
                continue;
            }

             //проверяем на значение
            if($value == 'yes' || $value == 'no')
            {
                $value = ($value == 'yes')?'true':'';
            }
             
            if($value == 'NULL')
            {
                $value = '';
            }
             
            $array = [self::MAP_FIELDS[$key] => $value ];
            $storage = array_merge($storage, $array);
        }
        
        foreach (self::MAP_ADD_EMPTY as $option)
        {
            $storage = array_merge($storage, [$option => '']);
        }
        
        return json_encode($storage);

    }




}

