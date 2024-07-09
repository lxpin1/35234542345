<?php

namespace Biatech\Lazev\Repositories;

final class WoocommerceSettings
{
    private $wpdb;

    public function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb;
    }


    public function get_shippment_method_by_instance_id($instance_id)
    {
        //TODO Typing object value or dto in return;
        $query = "SELECT * FROM ".
                    $this->wpdb->prefix."woocommerce_shipping_zone_methods WHERE instance_id='".$instance_id."'";
        $shipping_method_info =  $this->wpdb->get_row($query);
        return $shipping_method_info;
    }


    public function get_legacy_options_by_instance_id($instance_id)
    {
        //TODO Typing object value or dto in return;
        return get_option('woocommerce_dellin_shipping_calc_' . $instance_id. '_settings');
    }


    public function get_all_legacy_enabled_methods()
    {

        $query = "SELECT * FROM ".
                    $this->wpdb->prefix."woocommerce_shipping_zone_methods
                    WHERE method_id='dellin_shipping_calc' AND is_enabled = 1 ";

        return $this->wpdb->get_results($query);
    }


    public function create_new_method($zone_id, $method_order )
    {
        $zone_id = \esc_sql($zone_id);
        $method_order = \esc_sql($method_order);

        $query = "INSERT INTO ".$this->wpdb->prefix."woocommerce_shipping_zone_methods
                (zone_id, instance_id, method_id, method_order, is_enabled)
                VALUES(".$zone_id.", NULL, 'dellin_shipping', ".$method_order.", 1)";
        $push = $this->wpdb->query($query);

        return $this->wpdb->insert_id;
    }

    public function set_settings_by_instace_id( $instance_id, $value)
    {

        $option_name = 'woocommerce_dellin_shipping_' . $instance_id . '_settings';
        $options_value = serialize($value);

        return update_option($option_name, $options_value, 'yes');

    }

    public function set_unenebled_method_instance_id( $instance_id )
    {

        $table = $this->wpdb->prefix."woocommerce_shipping_zone_methods";
        $format = ['%d'];
        $where = ['instance_id' => $instance_id];

        return  $this->wpdb->update($table,
                ['is_enabled' => 0], $where, $format, $format);

    }

    public function get_options_by_instance_id($instance_id)
    {
        //TODO Typing object value or dto in return;
        $raw_options = get_option('woocommerce_dellin_shipping_' . $instance_id. '_settings');

        return unserialize($raw_options);
    }
}