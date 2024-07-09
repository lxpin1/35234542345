<?php
namespace Biatech\Lazev;

use Biatech\Lazev\Adapters\AdapterLegacyAjaxMethods;

final class Main
{

    const PRODUCT_TYPE = 11;
    const MODULE_VERSION = '2.0.0';
    public static function get_services()
    {
        return [
            Adapters\AdapterConverterLegacySettings::class,
            Adapters\AdapterLegacyAjaxMethods::class,
            Controllers\DellinTerminalsController::class,
            Controllers\DellinSettingsController::class,
            Adapters\AdapterScriptAdminSettings::class,
            Adapters\DellinShippingAdapter::class,
            Adapters\AdapterTerminalBlock::class,
            Adapters\AdapterMetaboxOrder::class
        ];
    }

    public static function register_services()
    {
        foreach ( self::get_services() as $class ) {
            $service = self::get_instance( $class );
            if ( method_exists( $service, 'register' ) ) {
                $service->register();
            }
        }
    }

    private static function get_instance( $class )
    {

        $service = new $class();
        return $service;
        
    }
}