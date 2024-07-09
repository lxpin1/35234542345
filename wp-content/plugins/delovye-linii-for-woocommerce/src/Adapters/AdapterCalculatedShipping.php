<?php

namespace Biatech\Lazev\Adapters;

use Biatech\Lazev\Base\IPluggable;

defined('ABSPATH') || exit;


final class AdapterCalculatedShipping implements IPluggable
{
    /**
     *  Пепеопределяем расчёт имплементируемый в шорткоде
     *  class-wc-shortcode-cart.php
     *  т.к. данных не очень то и достаточно для расчёта до адреса.
     */

    public function register()
    {
        add_action('woocommerce_calculated_shipping', array($this, 'change_calc_method'));
        
    }
    
    
    public function change_calc_method(){

        try {

            WC()->shipping()->reset_shipping();

            $address = array();

            $address['country']  = isset( $_POST['calc_shipping_country'] ) ? wc_clean( wp_unslash( $_POST['calc_shipping_country'] ) ) : ''; // WPCS: input var ok, CSRF ok, sanitization ok.
            $address['state']    = isset( $_POST['calc_shipping_state'] ) ? wc_clean( wp_unslash( $_POST['calc_shipping_state'] ) ) : ''; // WPCS: input var ok, CSRF ok, sanitization ok.
            $address['postcode'] = isset( $_POST['calc_shipping_postcode'] ) ? wc_clean( wp_unslash( $_POST['calc_shipping_postcode'] ) ) : ''; // WPCS: input var ok, CSRF ok, sanitization ok.
            $address['city']     = isset( $_POST['calc_shipping_city'] ) ? wc_clean( wp_unslash( $_POST['calc_shipping_city'] ) ) : ''; // WPCS: input var ok, CSRF ok, sanitization ok.
            $address['address_1'] = isset($_POST['calc_shipping_address']) ? wc_clean( wp_unslash($_POST['calc_shipping_address'])) : ''; // WPCS: input var ok, CSRF ok, sanitization ok.
            $address['terminal_id'] = isset($_POST['terminal_id']) ? wc_clean( wp_unslash($_POST['terminal_id'])) : '';
    
    
            $address = apply_filters( 'woocommerce_cart_calculate_shipping_address', $address );
    
    
            $current_user = wp_get_current_user();
            $user_id = $current_user->ID;
    
            if($address['terminal_id'] !== ''){
                
                update_user_meta( $user_id,
                    'terminal_id',
                    sanitize_text_field( $_POST['terminal_id'])
                );
            }
    
            if ( $address['postcode'] && ! WC_Validation::is_postcode( $address['postcode'], $address['country'] ) ) {
                throw new Exception( __( 'Please enter a valid postcode / ZIP.', 'woocommerce' ) );
            } elseif ( $address['postcode'] ) {
                $address['postcode'] = wc_format_postcode( $address['postcode'], $address['country'] );
            }
    
            if ( $address['country'] ) {
                if ( ! WC()->customer->get_billing_first_name() ) {
                    WC()->customer->set_props('billing', $address );
                    WC()->customer->set_billing_address($address['address_1']);
                }
                WC()->customer->set_props('shipping', $address);
                WC()->customer->set_shipping_address($address['address_1']);
            } else {
                WC()->customer->set_props('billing', $address );
                WC()->customer->set_props('shipping', $address);
            }
    
            $woocommerce_ship_to_destination = get_option('woocommerce_ship_to_destination');
    
            if($woocommerce_ship_to_destination == 'billing_only'){
                WC()->customer->set_billing_country($address['country']);
                WC()->customer->set_billing_state($address['state']);
                WC()->customer->set_billing_city($address['city']);
                WC()->customer->set_billing_address($address['address_1']);
            }
    
    
    
            WC()->customer->set_calculated_shipping( true );
    
            WC()->customer->save();
    
    
    
            wc_add_notice( __( 'Shipping costs updated.', 'woocommerce' ), 'notice' );
    
    
    
            } catch ( Exception $e ) {
                if ( ! empty( $e ) ) {
                    wc_add_notice( $e->getMessage(), 'error' );
                }
            }
    }
}