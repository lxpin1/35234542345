<?php

namespace Biatech\Lazev\Repositories;

final class WoocommerceOrder
{
    private $wpdb;

    public function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb;
    }
    
    public function replace_legacy_method_id()
    {
        
        $query = "UPDATE woocommerce.wp_woocommerce_order_itemmeta
                SET meta_value='dellin_shipping'
                WHERE meta_value='dellin_shipping_calc'";
        
        return $this->wpdb->query($query);
        
    }
    
    public function replace_legacy_instance_id(int $instance_id_legacy, int $instance_id)
    {
        
        $instance_id = \esc_sql($instance_id);
        $instance_id_legacy = \esc_sql($instance_id_legacy);
        $query = "UPDATE woocommerce.wp_woocommerce_order_itemmeta
                    SET meta_value=".$instance_id."
                    WHERE meta_key='instance_id'
                        AND meta_value = ".$instance_id_legacy;
        
        return $this->wpdb->query($query);
        
    }
}
