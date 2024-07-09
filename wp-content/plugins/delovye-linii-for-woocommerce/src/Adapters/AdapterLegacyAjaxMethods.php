<?php

namespace Biatech\Lazev\Adapters;

use Biatech\Lazev\Base\Cache\CacheWp;
use Biatech\Lazev\Base\IPluggable;
use Biatech\Lazev\DTOs\AuthDTOFields;
use Biatech\Lazev\DTOs\LoggerSettingsDTO;
use Biatech\Lazev\Factories\FactorySettings;
use Biatech\Lazev\Services\DellinCounteragentsService;
use Biatech\Lazev\Services\DellinLocationService;
use Biatech\Lazev\Services\DellinOPFService;

final class AdapterLegacyAjaxMethods implements IPluggable
{

    
    public function register()
    {
        
        //получение контрагентов
        add_action( 'wp_ajax_get_counteragents', [$this,'dellin_get_counteragents']  ); // для авторизованных пользователей
        add_action( 'wp_ajax_nopriv_get_counteragents', [$this,'dellin_get_counteragents']  ); // для неавторизованных
        
        //Получение организационно-правовых форм
        add_action( 'wp_ajax_get_opf', [$this,'dellin_get_opf'] ); // для авторизованных пользователей
        add_action( 'wp_ajax_nopriv_get_opf', [$this,'dellin_get_opf'] ); // для неавторизованных
                
        //Поиск города по названию
        add_action( 'wp_ajax_search_city',[$this,'dellin_search_city']  ); // для авторизованных пользователей
        add_action( 'wp_ajax_nopriv_search_city',[$this,'dellin_search_city']  ); // для неавторизованных
        
    }
    
    public function dellin_get_counteragents() {
        try {

            $login = sanitize_text_field(trim($_REQUEST['login']));
            $password = sanitize_text_field(trim($_REQUEST['password']));
            $appkey = sanitize_text_field(trim($_REQUEST['appkey']));
            $logs = sanitize_text_field(trim($_REQUEST['is_logs']));
            $is_debug = ($logs == 'true');

            $auth =  new AuthDTOFields( $appkey,
                        $login,
                        $password);

            $logging = new LoggerSettingsDTO($is_debug);

            $factory = new FactorySettings();
            $cache = new CacheWp();
            $factory->setAuth($auth);
            $factory->setLoggerSettings($logging);
            $settings = $factory->create(null);

            $serviceCounteragets = new DellinCounteragentsService($settings, $cache);

            $response = $serviceCounteragets->getLegacyFacadeCounteragents();

            echo json_encode($response);
            die();

        }
        catch (\Exception $exception)
        {
            $result = [];
            $result['code'] = 400;
            $result['error'] = $exception->getMessage();
            $result['exception'] = $exception->getTraceAsString();

            echo json_encode($result);
            die();
        }

    }

    public function dellin_get_opf(){
        try {
            $appkey = sanitize_text_field(trim($_REQUEST['appkey']));

            $auth =  new AuthDTOFields( $appkey,
                        null,
                        null);
            $factory = new FactorySettings();
            $factory->setAuth($auth);
            $settings = $factory->create(null);
            $cache = new CacheWp();

            $opfService = new DellinOPFService($settings, $cache);

            echo json_encode($opfService->getOpfAndCountryLegacy());
            die();


        } catch (\Exception $exception)
        {

            $result = [];
            $result['code'] = 400;
            $result['error'] = $exception->getMessage();

            echo json_encode($result);
            die();

        }

    }
    
    
    public function dellin_search_city(){
        try {
            $query = sanitize_text_field($_REQUEST['query']);
            $appkey = sanitize_text_field(trim($_REQUEST['appkey']));

            $auth =  new AuthDTOFields( $appkey,
                        null,
                        null);
            $factory = new FactorySettings();
            $factory->setAuth($auth);
            $settings = $factory->create(null);
            $cache = new CacheWp();

            $locationService = new DellinLocationService($settings, $cache);
            $request = $locationService->locationSearch($query);

            if(!isset($request))
            {
                throw new \Exception('Отсутствует список городов');
            }

            $response = [];
            if(is_array($request) && count($request)>0){
                foreach ($request as $city)
                {
                    $item = [
                        'label' => $city->aString,
                        'code' => $city->code,
                    ];
                    $response[] = $item;
                }
            }

            echo \wp_json_encode($response, JSON_UNESCAPED_UNICODE);
            die();


        } catch (\Exception $exception) {
            $response = ['code'=> 400,
                        'error'=> $exception->getMessage(),
                        'tracert' => $exception->getTrace()];
            echo \wp_json_encode($response, JSON_UNESCAPED_UNICODE);
            die();
        }

    }
        
    
    
}