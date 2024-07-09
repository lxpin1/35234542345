<?php

declare(strict_types=1);

namespace Biatech\Lazev\Adapters;

use Biatech\Lazev\Base\IPluggable;

final class DellinShippingAdapter implements IPluggable
{
    public function register()
    {
        if ( in_array( 'woocommerce/woocommerce.php',
        apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) )
        {
            add_filter( 'woocommerce_shipping_methods', array( $this, 'addDellinShipping' ) );
            add_action( 'woocommerce_shipping_init', array( $this, 'init_method' ) );
            
        }
    }

    public function init_method()
    {
        if(!class_exists('Biatech\Lazev\Adapters\AdapterDellinShippingMethod'))
        {
            require_once dirname(__FILE__)."/AdapterDellinShippingMethod.php";
        }
    }
    
    public function addDellinShipping($methods)
    {
        $methods['dellin_shipping'] = 'Biatech\Lazev\Adapters\AdapterDellinShippingMethod';

        return $methods;
    }
}