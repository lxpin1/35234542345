<?php

namespace Biatech\Lazev\Adapters;

defined('ABSPATH') || exit;

use Biatech\Lazev\Adapters\OrderAdapter;
use Biatech\Lazev\Base\Cache\CacheWp;
use Biatech\Lazev\Base\DellinLogger;
use Biatech\Lazev\Factories\FactoryOrder;
use Biatech\Lazev\Factories\FactorySettings;
use Biatech\Lazev\Services\DellinCalculationService;
use Biatech\Lazev\Services\DellinProduceDateService;
use Biatech\Lazev\ValueObjects\Settings;
use Biatech\Lazev\Services\DellinLocationService;

/**
 * Метод доставки скармливается в Woocommerce
 */
class AdapterDellinShippingMethod extends \WC_Shipping_Method
{
    /**
     * @param int $instance_id id.
     */
    public function __construct($instance_id = 0)
    {

        $this->id = 'dellin_shipping';
        $this->instance_id = absint($instance_id);
        $this->method_title = __('Деловые линии');
        $this->method_description = __('The plugin allows you to automatically calculate shipping costs using dellin.ru API service.', 'dellin-shipping-for-woocommerce');
        $this->supports = array(
            'shipping-zones',
            'instance-settings',
            //   'instance-settings-modal',
        );

        $this->instance_form_fields = array(
            'enabled' => array(
                'title' => __('Enable/Disable'),
                'type' => 'checkbox',
                'label' => __('Включить этот метод доставки'),
                'default' => 'yes',
            ),
            'title' => array(
                'title' => __('Название метода'),
                'type' => 'text',
                'description' => __('This controls the title which the user sees during checkout.'),
                'default' => __('Деловые линии'),
                'desc_tip' => true
            )
        );


        $settings = $this->getSettings();

        if(!isset($settings->name))
            {
                $this->title =  __('Деловые линии');
                $this->method_title =  __('Деловые линии');
            }

        if(isset($settings->name))
        {
            $this->title = $settings->name;
            $this->method_title = $settings->name;
        }



        add_action('woocommerce_update_options_' . $instance_id, [$this, 'process_admin_options']);
        // add_action('woocommerce_update_options_shipping_'.$instance_id, array( $this, 'process_admin_options' ) );
     //   add_action('woocommerce_before_cart', array($this, 'terminal_field_in_cart'), 1);
        //		add_action('woocommerce_terminal_in_shipping_calculator', array($this, 'terminal_field_in_cart'), 1);
//		add_action('woocommerce_cart_contents', array($this, 'check_terminal_id'), 11);


    }


    public function getSettings()
    {

        $factorySettings = new FactorySettings();

        return $factorySettings->create($this->instance_id, null);
    }


    /**
     * Calculate_shipping function.
     *
     * @param array $package (default: array()).
     */
    public function calculate_shipping($package = array())
    {

        if (is_checkout() || is_cart())
        {
            try {
                
                global $woocommerce;
                $cart = $woocommerce->cart;

                $settings = $this->getSettings();

                $factoryOrder = new FactoryOrder();
                $adapterOrder = new OrderAdapter($cart, $settings, $package);
                $factoryOrder->setDataOrderAdapter($adapterOrder);
                $order = $factoryOrder->create();
                
                $cache = new CacheWp();
                $calcService = new DellinCalculationService($settings, $order, $cache);

                $result = $calcService->getCalculate();



                $this->add_rate(
                        array(
                    'id' => esc_html($this->get_rate_id()),
                        'label' => esc_html($this->title . ' ' . ' (' . $result->getDaysPluralFormat(). ')'),
                        'cost' => esc_html($result->price),
                        'meta_data' => ['terminals'=> $result->terminals ],
                        'package' => $package
                    )
                );


            } catch (\Exception $exception) {


                $logger = new DellinLogger();
                $context = [
                    'method_id' => $this->instance_id,
                    'exceptionTrace' => $exception->getTrace()
                ];

                $logger->error($exception->getMessage(), $context);
                if(is_admin())
                {

                    $this->add_rate(
                        [
                            'id' => esc_html($this->get_rate_id()),
                            'label' => esc_html($this->title.'. Расчёт не состоялся ')
                        ]
                    );

                }

            }

        }

    }


    /**
     * Output the shipping settings screen.
     */
    public function get_admin_options_html()
    {
        if (!$this->instance_id) {
            echo '<h2>' . esc_html($this->get_method_title()) . '</h2>';
        }
        echo '<div id="react-app"></div>';
        echo '<div class="legacy_data" style="display:none;">';
        echo '</div>';
    }



}
