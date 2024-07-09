<?php

namespace Biatech\Lazev\Controllers;

use Biatech\Lazev\Base\IPluggable;

class DellinSettingsController implements IPluggable
{

    public $namespace = 'dellin-shippment/v1';
    public $resource_name = 'dellin-settings-plugin';

    public function register()
    {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    // Register our routes.
    public function register_routes()
    {
        register_rest_route($this->namespace, '/' . $this->resource_name . '/(?P<id>[\d]+)', array(
            // Here we register the readable endpoint for collections.
            array(
                'methods' => 'GET',
                'callback' => array($this, 'get_options'),
                'permission_callback' => array($this, 'get_settings_permissions_check'),
            )
        ));
        register_rest_route($this->namespace, '/' . $this->resource_name . '/(?P<id>[\d]+)', array(
            // Notice how we are registering multiple endpoints the 'schema' equates to an OPTIONS request.
            array(
                'methods' => 'POST',
                'callback' => array($this, 'set_settings_in_options'),
                'permission_callback' => array($this, 'get_settings_permissions_check'),
            )
        ));
    }

    /**
     * Check permissions for the posts.
     *
     * @param WP_REST_Request $request Current request.
     */
    public function get_settings_permissions_check($request)
    {
        return current_user_can('manage_woocommerce');
    }

    public function get_options($request)
    {

        $instance_id = $request->get_params()['id'];

        $data = get_option('woocommerce_dellin_shipping_calc_' . $instance_id . '_settings');

        return rest_ensure_response($data);
    }


    public function set_settings_in_options($request)
    {
        //TODO перенести в репозиторий
        $instance_id = $request->get_params()['id'];
        $option_name = 'woocommerce_dellin_shipping_' . $instance_id . '_settings';
        $options_value = serialize($request->get_body()); //json to serialize string

        $result = update_option($option_name, $options_value, 'yes');
        if ($result) {
            $state = ['state' => 'success',
                      'message' => 'Изменения применены'
            ];
        }

        if (!$result) {
            $state = ['state' => 'update',
                      'message' => 'Изменений нет'
            ];
        }


        return rest_ensure_response($state);
    }


    // Sets up the proper HTTP status code for authorization.
    public function authorization_status_code()
    {

        $status = 401;

        if (is_user_logged_in()) {
            $status = 403;
        }

        return $status;
    }
}





