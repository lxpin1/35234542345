<?php

declare(strict_types=1);

namespace Biatech\Lazev\Adapters;

use Biatech\Lazev\Base\IPluggable;
use Biatech\Lazev\DTOs\AuthDTOFields;
use Biatech\Lazev\Repositories\WoocommerceSettings;
use Biatech\Lazev\Factories\FactorySettings;
use Biatech\Lazev\Services\DellinAuthService;

use \stdClass;

final class AdapterScriptAdminSettings implements  IPluggable {

    public $version_script = '1.0.0';//major, feat, patch

    private ?string $page;

    private ?string $instance_id;

    private ?string $tab;

    private WoocommerceSettings $repository;

    const PAGE_TYPE = "wc-settings";
    const TAB_TYPE  = "shipping";

    public function __construct()
    {
        $this->page = (isset($_GET['page']))?sanitize_text_field($_GET['page']):null;
        
        $this->instance_id = (isset($_GET['instance_id']))?
                                            sanitize_text_field($_GET['instance_id']):null;

        $this->tab =  isset($_GET['tab'])?sanitize_text_field($_GET['tab']):null;
        $this->repository = new WoocommerceSettings();

    }

    public function register(): void
    {
        add_action( 'admin_enqueue_scripts', array($this,'admin_dellin_scripts'));
    }



    public function admin_dellin_scripts(): void
    {

        if(isset($this->page) && $this->page  == self::PAGE_TYPE &&
           (isset($this->tab) && $this->tab == self::TAB_TYPE)) {

            if(!isset($this->instance_id)){
                return ;
            }

            $get_shipping_params = $this->repository->get_shippment_method_by_instance_id($this->instance_id);

            if(!isset($get_shipping_params))
            {
                return;
            }

            $method_id = $get_shipping_params->method_id;
            

            if($method_id == 'dellin_shipping'){
                
                $this->set_script_dellinVars();
                $this->set_script_css();
                $this->set_script_js();
            }
           
        }

    }


    public function get_lang_vars(): string
    {
	    return json_encode(array(
            'WC_DELLIN_SHIPPING_PROCESSING' => __('Processing','dellin-shipping-for-woocommerce'),
            'WC_DELLIN_SHIPPING_FIND_KLADR_CITY_BUTTON' => __('Find city KLADR','dellin-shipping-for-woocommerce'),
            'WC_DELLIN_SHIPPING_FIND_KLADR_STREET_BUTTON' => __('Find street KLADR','dellin-shipping-for-woocommerce'),
            'WC_DELLIN_SHIPPING_SEARCH_MSG' => __('Start entering the name','dellin-shipping-for-woocommerce'),
            'WC_DELLIN_SHIPPING_BUTTON_SELECT' => __('Select','dellin-shipping-for-woocommerce'),
            'WC_DELLIN_SHIPPING_BUTTON_CLOSE' => __('Close','dellin-shipping-for-woocommerce')

        ));
    }

    public function set_script_dellinVars(): void
    {

        wp_localize_script('jquery', 'dellinVars',
                array(
                    'url' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce('dellin-nonce'),
                    'wp_rest_nonce' => wp_create_nonce( 'wp_rest' ),
                    'langVars' => $this->get_lang_vars(),
                    'spinnerSrc' =>self::plugin_dir_url().'../assets/img/wait.gif',
                    'legacy_options'=> $this->repository->get_legacy_options_by_instance_id($this->instance_id),
                    'options'=> $this->serialize_options()
                )
            );
    }

    public function serialize_options(): ?stdClass
    {
        //frontendlike 
        $options_raw = $this->repository->get_options_by_instance_id($this->instance_id);


        if(!$options_raw)
        {
            return json_decode('{}');
        }

        
        return json_decode($options_raw);
    }


    public function set_script_css(): void
    {

        wp_register_style('dellinDeliverySettingsStyle',
                           self::plugin_dir_url() . '../assets/css/dellinDeliverySettings.css',
                           array(), $this->version_script);
        wp_enqueue_style('dellinDeliverySettingsStyle');
    }

    public function set_script_js(): void
    {
        $js_path ='../assets/settings/build/index.js';
        $block_path = self::plugin_dir_url().$js_path;

        wp_enqueue_script(
            'my-custom-block',
            $block_path,
            array( 'react', 'wp-blocks', 'wp-block-editor', 'wp-i18n' ),
            $this->version_script
        );

    }

    public static function plugin_dir_url(): string
    {
        return plugin_dir_url( __FILE__ .'/../../../');
    }
}


