<?php

declare(strict_types=1);

namespace Biatech\Lazev\Services;


use Biatech\Lazev\Base\Composite\Field;

use \stdClass;


final class DellinTerminalsService extends AbstractDellinService
{
    //Dictonaty
    const PATH_TERMINALS = '/v3/public/terminals.json';
    
    //search
   // const SEARCH_TERMINALS
    const CACHE_TTL = '43200';//24h
    const CACHE_PATH = 'dellin.dictonary.terminals';
    

    public function getTerminals(?string $kladr = null, ?string $terminalID = null): array
    {

        $directory = $this->getDirectoryTerminals();
        
        $terminalsList = [];

        if($kladr && $terminalID ){ //set terminalId and codeCladr
            $terminalList['terminal'] = self::getTerminalsOnCityAndTerminal($directory, $terminalID);
        } elseif(!$kladr && $terminalID ){ // set terminalId
            $terminalList['terminal'] = self::getTerminalsOnCityAndTerminal($directory, $terminalID);
        } elseif( $kladr && !$terminalID){ // set kladrcode
            $terminalList = self::getTerminalsOnCity($directory, $kladr);
        } elseif(!$kladr){ // empty params send all catalog info
            $terminalList = self::getTerminalsOnEmptyParams($directory);
        }

        return $terminalList;
    }
    
    public function getCountsTerminalsInCity(string $kladr): int
    {
        $terminals = $this->getTerminals($kladr);
        if(empty($terminals))
        {
            return 0;
        }
        return count($terminals['terminals']);
    }
    
    private function getDirectoryTerminals(): \stdClass
    {
        

        if($this->cache->has(self::CACHE_PATH))
        {
            if(isset($this->settings->logging->is_logging)&&$this->settings->logging->is_logging)
            {
                $this->logger->info('Справочник терминалов представлен из cache', ["sourceUrl" => null]);
            }
            return $this->cache->get(self::CACHE_PATH);
        }


        $this->requestContainer->add(new Field(['appkey', $this->authService->getAppkey()]));
        
        $request = $this->client->post(self::PATH_TERMINALS,  $this->requestContainer->toArray());

        $responseUrl = json_decode($request);

        if(!isset($responseUrl->url) && $responseUrl->url == '')
        {
            if($this->settings->logging->is_logging)
            {
                $this->logger->error('Метод '.self::PATH_TERMINALS.' прислал не ожиданный ответ');
            }
            throw new \Exception('Метод '.self::PATH_TERMINALS.' прислал не ожиданный ответ');

        }

        $requestDirectory = $this->client->get($responseUrl->url);

        $responseDirectory = json_decode($requestDirectory);


        if(!isset($responseDirectory->city))
        {
            if(isset($this->settings->logging->is_logging) && $this->settings->logging->is_logging)
            {
                $this->logger->error('URL'.$responseUrl->url.' прислал не ожиданный ответ');
            }
            throw new \Exception("Список терминалов пуст");
        }

        $this->cache->set(self::CACHE_PATH, $responseDirectory, self::CACHE_TTL);

        if(isset($this->settings->logging->is_logging)&&$this->settings->logging->is_logging)
        {
            $this->logger->info('Справоник терминалов обновлён.', ["sourceUrl" => $responseUrl->url]);
        }

        return $responseDirectory;

    }


    public static function getTerminalsOnEmptyParams($obCities): array
    {
        $arTerminalsByCities = array();
        foreach($obCities->city as $city){
            $terminalsObs = $city->terminals->terminal;
            $arTerminals = array();

            foreach($terminalsObs as $key=>$terminalOb){
                $arTerminals[$terminalOb->id] = $terminalOb;
            }
                $cityData = array(
                    'cityName' => $city->name,
                    'cityID' => $city->cityID,
                    'terminals' => $arTerminals
                );
            $arTerminalsByCities[$city->code] = $cityData;
        }
            return  $arTerminalsByCities;
    }

    public static function getTerminalsOnCity($obCities, $cityKladr): array
    {
        $arCityTerminals = [];
        foreach($obCities->city as $city){
            if($city->code == $cityKladr){
                $cityData = array(
                    'cityName'  => $city->name,
                        'cityID'    => $city->cityID,
                        'cityKladr' => $city->code,
                        'terminals' => $city->terminals->terminal
                    );
                $arCityTerminals = $cityData;
                break 1;
            }
        }
            return $arCityTerminals;
    }

    public static function getTerminalsOnCityAndTerminal($obCities, $terminalId): \stdClass
    {
        $finedTerminal = new stdClass();

        foreach($obCities->city as $city){

            foreach($city->terminals->terminal as $terminal){

                if($terminal->id == $terminalId)
                {
                    //add cityInfo not good solution
                        $cityData = new stdClass();
                        $cityData->name = $city->name;
                        $cityData->ID = $city->cityID;
                        $cityData->code = $city->code;
                        $cityData->latitude = $city->latitude;
                        $cityData->longitude = $city->longitude;

                        $finedTerminal = $terminal;
                        $finedTerminal->infoCity = $cityData;

                        break 2;
                }

            }

        }
            return $finedTerminal;
    }

    public function getTerminalsOnCityEasyForm(string $kladr): array
    {

        $result = [];
        $request = $this->getTerminals($kladr);
        $terminals = $request['terminals'] ?? false;

        if($terminals){
            foreach ($terminals as $terminal)
            {
                $objTerminal = new \stdClass();
                $objTerminal->id = $terminal->id;
                $objTerminal->address = $terminal->address;
                $objTerminal->coordinate = ['latitude'=> $terminal->latitude,
                                            'longitude' => $terminal->longitude];
                $objTerminal->name = $terminal->name;
                $objTerminal->label = $terminal->address.' - '.$terminal->name;
                $result[] = $objTerminal;
            }
        }


        return  $result;

    }




}