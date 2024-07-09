<?php


class DellinLogger {

    /**
     ** Init params for loging
     */
     const DEBUG_MODULE = false;
     const DEBUG_IO_CURL = false;
     const DEBUG_MESSAGE = false;
     const PATH_FOR_LOGS = DELLIN_PLUGIN_DIR.'/logs.log';
   

    public function __construct() {
        // Init default params.
       // define('PATH_FOR_LOGS', '/bitrix/modules/dellindev.shipping/logs/log-'.date("Y-m-d").'.log');
        if(self::DEBUG_MODULE){
          return self::Init();
        }
        
    }

    /**
     * Init and checked first method required for work module \
     * Инициализируем проверки все известные нам проблем при работе с модулем.
     */
    public static function Init(){
        self::is_curl_installed();
    }
    /**
     * 
     */

    public static function getProps($propsName){
        switch($propsName){
            case 'IO_CURL':
                return self::DEBUG_IO_CURL;
            break;
            case 'MESSAGE':
                return self::DEBUG_MESSAGE;
            break;
        }
            
    }

    /**
     * Checked curl in settings php \
     * Проверяем наличие модуля curl в php.
     * @return mixed - error in log file or false
     */


    public static function is_curl_installed(){
        if (!in_array('curl', get_loaded_extensions())){
            $data['type'] = 'CRITICAL';
            $data['body'] = 'Install in your settings php extensions curl.'.PHP_EOL.' Установите зависимость curl в свой интерпретатор php. \n'.PHP_EOL;
            return self::write_message(self::PATH_FOR_LOGS, $data);
        }
    }

    /**
     * This method write in not pattern message 
     * Used this method for cheked first reqirements \
     * Метод для записи уведомлений в свободном виде.
     * @param string $path - path for file logs
     * @param array $data - type and data for 
     * @return void
     */

    public static function write_message($data, $path = self::PATH_FOR_LOGS){
        $file = fopen($path, 'a');
        $dateNow = date("Y-m-d H:i:s");
        if($data){
            fwrite($file, $dateNow.'|'.$data['type'].'| :'.$data['body'].PHP_EOL);
        } 
        fclose($file);
    }

    /**
     * This method write in request\response message 
     * @param string $path - path for file logs
     * @param array $data - type and data for 
     * @param string $url - where query is sent  
     * @return void 
     */

    public static function write_io_message($url, $data){
        $dateNow = date("Y-m-d H:i:s");
        if($data){
            $log = '>>>>>>>>>>>>>>>>>>>>>>>>>>>>START>>>>>>>>>>>>>>>>>>>>>>>>>>>'.PHP_EOL.
                   '>>>>>>>TIME: '.$dateNow.'>>>>>>REQUEST TO:'.$url.'>>>>>>>>>>'.PHP_EOL.$data['bodyRequest'].PHP_EOL.
                   '>>>>>>>TIME: '.$dateNow.'>>>>>>RESPONSE:'.$url.'>>>>>>>>>>>>>'.PHP_EOL.$data['bodyResponse'].PHP_EOL.
                   '>>>>>>>>>>>>>>>>>>>>>>>>>>>>END>>>>>>>>>>>>>>>>>>>>>>>>>>>>>'.PHP_EOL;
            file_put_contents(DELLIN_PLUGIN_DIR.'/logs_IO_'.date("Y-m-d").'.log', $log, FILE_APPEND);
        } else {
            $data = array('type'=>'CRITICAL',
                          'body'=>'Sent '  );
            return self::write_message($data);
        }
    }
    
    
}

?>
