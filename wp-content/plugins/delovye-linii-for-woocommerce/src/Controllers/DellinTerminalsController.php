<?php

namespace Biatech\Lazev\Controllers;

use Biatech\Lazev\Base\Cache\CacheWp;
use Biatech\Lazev\Base\IPluggable;
use Biatech\Lazev\DTOs\AuthDTOFields;
use Biatech\Lazev\Factories\FactorySettings;
use Biatech\Lazev\Services\DellinTerminalsService;

class DellinTerminalsController implements IPluggable
{

    public $namespace = 'dellin-shippment/v1';
    public $resource_name = 'get-terminals';

    public function register()
    {
        add_action('rest_api_init', [$this, 'register_routes']);
    }


// Register our routes.
    public function register_routes()
    {
        register_rest_route($this->namespace, '/' . $this->resource_name , array(
            // Notice how we are registering multiple endpoints the 'schema' equates to an OPTIONS request.
            array(
                'methods' => 'POST',
                'callback' => array($this, 'get_terminals'),
                'permission_callback' => array($this, 'get_terminals_permissions_check'),
            )
        ));
    }


    /**
     * Check permissions for the posts.
     *
     * @param WP_REST_Request $request Current request.
     */
    public function get_terminals_permissions_check($request)
    {
       return current_user_can('manage_woocommerce');
    }


    public function get_terminals($request)
    {
        try{

            $raw_content = $request->get_body();
            $body = json_decode($raw_content);

            $auth =  new AuthDTOFields( $body->appkey,
                                        null,
                                        null);

            $factory = new FactorySettings();
            $cache = new CacheWp();
            $factory->setAuth($auth);
            $settings = $factory->create(null);

            $terminalsService = new DellinTerminalsService($settings, $cache);


            $response = ['status' => 'success',
                         'code' => 200,
                         'terminals' => $terminalsService->getTerminalsOnCityEasyForm($body->kladr)];



            return rest_ensure_response($response);

        } catch (\Exception $exception){


            return ['status' => 'error',
                    'code' => 500,
                    'message' => $exception->getMessage(),
                    'tracert' => $exception->getTraceAsString()];

        }

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





