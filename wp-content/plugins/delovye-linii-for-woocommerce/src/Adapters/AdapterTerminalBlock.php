<?php

declare(strict_types=1);

namespace Biatech\Lazev\Adapters;

use Biatech\Lazev\Base\IPluggable;
use Biatech\Lazev\Factories\FactorySettings;

final class AdapterTerminalBlock implements IPluggable
{

    public function register(): void
    {
        //Прокидываем в rate терминалы и рендерим
        add_action( 'woocommerce_after_shipping_rate', array( $this, 'add_terminals' ), 10, 2 );
        
        //Прокидываем при создании заказа terminal_id
        add_action( 'woocommerce_checkout_update_order_meta', array($this,  'save_selected_terminal'));
        
    }
    
    
   public function add_terminals($method): void
   {

        if($this->isMethodArrivalTerminal($method->instance_id) && is_checkout()){

            $metaData = $method->get_meta_data();
            $terminals =  $metaData['terminals'] ?? null;

            if(isset($terminals) && count($terminals) > 0)
            {
                woocommerce_form_field('terminal_id' , array(
                    'title'       => \esc_html__('Терминал получения' ),
                    'description' => \esc_html__('Выберите терминал получения'),
                    'type'        => 'select',
                    'default'     => '',
                    'options'     => $this->getValidTerminals($terminals),
                ));
            }
            
        }


   }
   
    function getValidTerminals($teminals_in_session){
        
            $options = [];
    
            foreach ($teminals_in_session as $value){
    
                $options += [$value->id => $value->address];
    
            }
    
    	    return $options;
        }
        
   public function isMethodArrivalTerminal(int $instance_id)
   {
       $factory = new FactorySettings();
       $settings = $factory->create($instance_id);
       
       return $settings->default_cargo_params->is_terminal_unloading;
       
   }
    
    
    function save_selected_terminal($order_id) {
    
        $terminal = !empty($_POST['terminal_id'])?\sanitize_text_field(wp_unslash($_POST['terminal_id'])):null;
        if (isset($terminal) ) {
            update_post_meta( $order_id, 'terminal_id', $terminal);
        }
    }
        
}