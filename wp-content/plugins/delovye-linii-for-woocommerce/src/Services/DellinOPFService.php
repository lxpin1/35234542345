<?php

namespace Biatech\Lazev\Services;

final class DellinOPFService extends AbstractDellinService
{

    const PATH_OPF_CODES = '/v1/public/opf_list.json';
    const PATH_COUNTRIES_CODES = '/v1/public/countries.json';

    const CACHE_TTL = '86400';// 1 day (?)

    const CACHE_PATH_OPFLIST = 'dellin.dictonary.opfList';
    const CACHE_PATH_COUNTRIES_LIST = 'dellin.dictonary.countriesList';


    public array $contriesCodes;

    public array $opfCodes;

    /**
     * @return array
     * @throws \Exception
     */
    public function getContriesCodes(): array
    {

        $this->contriesCodes = [];

        $this->withAppkey();

        $requestUrlCSV = $this->client->post(self::PATH_COUNTRIES_CODES, $this->requestContainer->toArray());

        $response = json_decode($requestUrlCSV);
        if(!isset($response->url))
        {
            throw new \Exception('Не возможно обработать ответ метода '.self::PATH_COUNTRIES_CODES);
        }

        $requestCSV = $this->client->get($response->url);

        $rows = explode("\n", $requestCSV);
        $keys = str_getcsv($rows[0]);

        foreach ($rows as $num => $row) {
            if ($num != 0) {
                $values = str_getcsv($row);
                $arRow = [];
                if ($values[0]) {
                    foreach ($values as $index => $value) {
                        $arRow[$keys[$index]] = $value;
                    }

                        $this->contriesCodes[$arRow['countryUID']] = $arRow;
                }
            }
        }

        return $this->contriesCodes;
    }



    public function getOpfCodes(): array
    {

        $this->withAppkey();

        $requestUrlCSV = $this->client->post(self::PATH_OPF_CODES, $this->requestContainer->toArray());

        $response = json_decode($requestUrlCSV);
        if(!isset($response->url))
        {
            throw new \Exception('Не возможно обработать ответ метода '.self::PATH_OPF_CODES);
        }

        $requestCSV = $this->client->get($response->url);


        $rows = explode("\n",$requestCSV);
        $result = [];
        $keys = str_getcsv($rows[0]);
        foreach($rows as $num=>$row){
            if($num != 0){
                $arValues = str_getcsv($row);
                $arRow = [];
                if($arValues[0]){
                    foreach($arValues as $index=>$value){
                        $arRow[$keys[$index]] = $value;
                    }
                    $result['list'][$arRow['uid']] = $arRow;
                }

            }
        }

        return $result;


    }

    public function getOpfAndCountryLegacy(): array
    {

        $arCountriesList = [];
        $arOpfList = [];

        $opfList = $this->getOpfCodes()['list'];

        $countries = $this->getContriesCodes();

        foreach($countries as $country){
            $arCountriesList[$country['countryUID']] = $country['country'];
        }
        asort($arCountriesList);

        foreach($opfList as $id => $opf){
            $arOpfList[$opf['countryUID']][$opf['uid']] = $opf['name'];
            asort( $arOpfList[$opf['countryUID']]);
        }



        return ['code'=>200,
                'opf'=>$arOpfList,
                'countries'=>$arCountriesList];

    }




}