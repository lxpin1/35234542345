<?php

defined( 'ABSPATH' ) || exit;

class Dellin_Fields
{

	public function __construct (){
		// кастомные значения для юриков (B2B выпиленно)
//		add_action( 'woocommerce_before_checkout_billing_form', array($this, 'organisation_checkout_field'));
//		add_action( 'woocommerce_before_edit_account_form', array($this, 'organisation_checkout_field'));
//		add_action( 'woocommerce_checkout_process', array($this, 'my_custom_checkout_field_process'));
		add_action( 'woocommerce_checkout_update_order_meta', array($this,  'my_custom_checkout_field_update_order_meta'));
//		add_action( 'woocommerce_order_details_after_customer_details', array($this, 'organisation_checkout_field_echo_in_order'));
//		add_action( 'woocommerce_insert_organisation_details',  array($this, 'organisation_checkout_field_echo_in_order'));
//		add_action( 'woocommerce_admin_order_data_after_shipping_address', array($this, 'organisation_checkout_field_echo_in_admin_order' ), 10 );
		add_action( 'wp_footer',  array($this, 'terminalFields'));
        add_action( 'woocommerce_after_checkout_form', array($this,'terminalFields') );
//		add_filter( 'woocommerce_checkout_fields', array($this, 'add_filter_in_checkout_custom_field'), 1);
        add_action('woocommerce_calculated_shipping', array($this, 'change_calc_method'));
        add_action( 'woocommerce_after_shipping_rate', array( $this, 'add_terminals' ), 10, 2 );
	}


    /**
     * @param $instance_id
     *
     * @return bool
     */

    public function is_shipping_terminal($instance_id){
        $config = DellinApi::getConfig($instance_id);
        $result = false;
        if(is_array($config)  && $config['is_goods_unloading'] == 'no'){
            $result =  true;
        }
        return $result;
    }

    /**
     * @param $teminals_in_session
     * @return array
     */

	function getValidTerminals($teminals_in_session){
	    $options = [];

	    foreach ($teminals_in_session as $value){

	        $options += [$value->id => $value->address];

        }

	    return $options;
    }




	function add_terminals($method){



        if($this->is_shipping_terminal($method->instance_id) && is_checkout()){
            $customer = WC()->customer;
            $cache_id = 'dellin|term|'.json_encode($customer->get_shipping());
            // Неадекватная работа на некоторых плагинах объектного кеширования.
        //   var_dump(wp_cache_get($cache_id));
       // $terminals = wp_cache_get($cache_id);
            $terminals =  WC()->session->get( $cache_id );
            woocommerce_form_field('terminal_id' , array(
                'title'       => __( 'departure terminal', 'dellin-shipping-for-woocommerce' ),
                'description' => __( 'Select departure terminal', 'dellin-shipping-for-woocommerce'),
                'type'        => 'select',
                'default'     => '',
                'options'     => $this->getValidTerminals($terminals),
            ));

        }

    }



    /**
     * Переопределяем метод просчёта стоимости.
     * Метод находится в шорткоде class-wc-shortcode-cart.php.
     *
     */
	function change_calc_method(){

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



	function terminalFields(){
		if (is_checkout()){

			$current_user = wp_get_current_user();
			$user_id = $current_user->ID;
			$nonce = wp_create_nonce('dellin-terminal');
            $customer = WC()->customer;
        //     $cache_id = 'dellin|term|'.json_encode($customer->get_shipping());
        //    // $terminals = wp_cache_get($cache_id);
        //    $terminals = WC()->session->get( $cache_id );

            


			?>
			<script type="text/javascript">
                jQuery(function($) {
                   

                    var doGetRequest = function(terminal_id, user_id){
                        return jQuery.ajax({
                            url: wc_add_to_cart_params.ajax_url,
                            data: {'ajax':'y',
                                'action':'set_terminal_in_cart',
                                'security': '<?php echo esc_html($nonce) ?>' ,
                                'terminal_id':terminal_id, 'id': user_id},
                            method: "POST",
                            dataType: "json"
                        }).done(function(response){
                            var select = jQuery('#terminal_id');
                            if(undefined != response['error'] && jQuery(select).closest('tr').find('.error').length == 0){
                                $(document.body).trigger('update_checkout');
                                    window.location.reload();
                            }
                        });
                    }

                    $('#terminal_id').on('change', function () {

                        var terminal_id = $('#terminal_id > option:selected').val();
                            console.log($('#terminal_id > option:selected').val())
                            doGetRequest(terminal_id, <?php echo esc_html($user_id) ?>)
                        $(document.body).trigger('update_checkout');

                    })

                    // $(document.body).trigger('update_checkout');
                    // console.log('Event: update_checkout');

                });
			</script>
			<?php
		}
	}



    function my_custom_checkout_field_update_order_meta() {
        $current_user = wp_get_current_user();
        $user_id = $current_user->ID;

        if ( ! empty( $_POST['terminal_id'] ) ) { update_user_meta( $user_id, 'terminal_id', sanitize_text_field( $_POST['terminal_id'] ) ); }
    }




}


