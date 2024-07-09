<?php


namespace Biatech\Lazev\Adapters;


use Biatech\Lazev\Base\IPluggable;
use Biatech\Lazev\Helpers\LegacySettingsMapper;
use Biatech\Lazev\Repositories\WoocommerceSettings;
use Biatech\Lazev\Repositories\WoocommerceOrder;

/**
 * Стратегия адаптирования
 */

final class AdapterConverterLegacySettings implements IPluggable
{
    
    private WoocommerceSettings $repSettings;
    private WoocommerceOrder $repOrder;

    private LegacySettingsMapper $mapper;
    
    private array $legacyMethods; // memo
    private array $currentMethods;
    
    const TIME_ADD_TO_EVENT = 604800;
    const EVENT_NAME = 'dellin_converter_action_hook';

    public function __construct()
    {
        
        $this->repSettings = new WoocommerceSettings();
        $this->repOrder = new WoocommerceOrder();
        $this->mapper = new LegacySettingsMapper();
        $this->legacyMethods = $this->repSettings->get_all_legacy_enabled_methods();
    //    add_action( self::EVENT_NAME, [__CLASS__, 'eventProcedure'], 10, 1  );

    }

    public function register()
    {
        register_activation_hook( DELLIN_PLUGIN_FILE, [$this, 'adaptiveLegacy'] );

     //   add_action( 'activated_plugin', [$this, 'adaptiveLegacy'], 10, 2 );
        //одноразовые процедуры связанные с методом доставки.
    //    add_action( self::EVENT_NAME, [__CLASS__, 'eventProcedure'], 10, 1  );


    }
    
    public function adaptiveLegacy()
    {
        
        /**
         * Суть адаптации заключается в:
         * - переносе настроек из старого метода dellin_shipping_calc в новые
         * созданные методы dellin_shipping;
         * - выключение старых методов dellin_shipping;
         * - создание таски для шедулера на случай проблем с заказами имеющимися;
         */

        if(!$this->legacyMethods)
        {
//            wp_admin_notice( 'Модуль доставки установлен успешно.
//                              Информация о предыдущих методах доставки не найдена.');
            return;
        }
        // Состояние процесса
        $state = [];

        // сначала создадим новые методы
        foreach ($this->legacyMethods as $legacyMethod)
        {

            $newInstance_id = $this->repSettings->create_new_method(
                                            (int)$legacyMethod->zone_id,
                                            (int)$legacyMethod->method_order
            );
            
            $options = $this->mapper->adaptiveLegacyByInstanceId($legacyMethod->instance_id);

            $write   =   $this->repSettings->set_settings_by_instace_id($newInstance_id, $options);

            if($write)
            {
                $state[] = ['legacyID'=> $legacyMethod->instance_id,
                            'currentMethod' => $newInstance_id
                      ];
                //выключаем старый метод.
                $this->repSettings->set_unenebled_method_instance_id($legacyMethod->instance_id);
                //Добавляем событие, которое состоится через n-время.

            }


            
            if(!$write)
            {
                //нельзя показывать сообщения нужно заменить на логи
//                return  wp_admin_notice( 'error','Модуль доставки установлен успешно.
//                                          Информация о предыдущих методах доставки не найдена.');
            }
            
        }

        if(is_array($state) && count($state)>0)
        {
            $this->repOrder->replace_legacy_method_id();
            foreach($state as $method)
            {
                $this->repOrder->replace_legacy_method_id();
                $this->repOrder->replace_legacy_instance_id($method["legacyID"], $method["currentMethod"]);
            }
        }
              
    }


    
}
