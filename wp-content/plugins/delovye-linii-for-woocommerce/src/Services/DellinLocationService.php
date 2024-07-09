<?php
    //Добро пожаловать в костыленд
    // так как мы работает с КЛАДРом, который очень сильно может
    // отличаться от записей в ФИАС

declare(strict_types=1);

namespace Biatech\Lazev\Services;


use Biatech\Lazev\Base\Composite\Field;

use Biatech\Lazev\Helpers\ExclusionList;


final class DellinLocationService extends AbstractDellinService
{

    const URL_KLADR = '/v2/public/kladr.json';


    const SUBJECTS_FED_TYPES = ['автономная область','автономный округ', 'ао',
                				'Республика', 'республика', 'край', "область", 'Респ.',
                				'респ.', 'обл', 'г.', 'г '];





    public function findKLADR(?string $locationName,
    						  ?string $regionName, ?string $zip)
    {
            $this->startTime = microtime(true);

        //переводим все строковые параметры в нижний регистр.

            $locationName = mb_strtolower($locationName);
            $regionName = mb_strtolower($regionName);

            //числовой тип приводим к числовому
            $zip = (int)$zip;

            //Фильтруем вводные параметры населённого пункта.
            //Список для фильтрации сформирован согласно ОКАТО и ОКТМО
    		// для субьектов федерации.
    		// необходимость для городов требует анализа.
            $typesSubjectFed = self::SUBJECTS_FED_TYPES;

            $shortLocationName = $locationName;// тут пока ничего не фильтруем, т.к. ручной ввод.
            $shortRegionName = str_replace($typesSubjectFed, '', $regionName);



            //Работа по населённому пункту входящему в состав субъекта федерации

            if(array_key_exists($shortLocationName, ExclusionList::listForPlaces())) {
                foreach(ExclusionList::listForPlaces() as $place => $value){
                    if($place == $shortLocationName){
                        $shortLocationName = $value['cityName'];
                        $shortRegionName = $value['regionName'];
                    }
                }
            }

            //Работа с исключениями по субъектам федерации


            if(array_key_exists($shortRegionName, ExclusionList::listForRegionName())){

                foreach (ExclusionList::listForRegionName() as $regionCandidat => $regionValue){
                    if($shortRegionName == $regionCandidat){
                        $shortRegionName = $regionValue;
                    }
                }
            }

            $q = $shortLocationName." ".$shortRegionName;


            if(array_key_exists($q, ExclusionList::listForQuery())){

                foreach (ExclusionList::listForQuery() as $regionCandidat => $values){
                    if($regionCandidat == $q){

                        $q = $values['q'];

                        $shortRegionName = $values['regionName'];
                        $shortLocationName = $values['cityName'];
                    }
                }
            }

            $locationList = $this->locationSearch($q);


            // для логгера, т.к. не предусмотренно логгирование пока за скобками.
             if(empty($locationList) || (count($locationList) < 1)){
                 $message = 'Список городов пуст после обращения к методу '.self::URL_KLADR;
                 $this->addLoggerContext($locationList, $zip, $locationName,
                            $regionName, $shortRegionName, $shortLocationName);

                 $this->logger->error($message, $this->loggerContext);
                 throw new \Exception('Список городов пуст после обращения к методу '.self::URL_KLADR);
             }

            //TODO подумать как описать в логгере

            if(count($locationList) == 1){

                $result = $locationList[0];

            } else {

                $result = self::selectLocationIfPlacesMany($locationList, $shortLocationName, $shortRegionName, $zip);

            }


             $this->addLoggerContext($locationList, $zip, $locationName, $regionName,
                                    $shortRegionName, $shortLocationName, $result);

             $this->logger->debug('Результат отработки поиска города ', $this->loggerContext);

            return $result;

    }



    public function addLoggerContext(?array $locationList,?int $zip, ?string $locationName, ?string $regionName,
                                 ?string $shortRegionName, ?string $shortLocationName, ?object $result = null, ?array $poolPlaces = null )
    {

         $this->loggerContext[] = ['city_list' => $locationList,
                      'pool_places' => $poolPlaces,
                                   'path' => self::URL_KLADR,
                                   'zip' => $zip,
                                   'param_city_name' => $locationName,
                                   'param_region_name' => $regionName,
                                   'short_region_name' => $shortRegionName,
                                   'short_location_name' => $shortLocationName,
                                   'dl_city' => $result,
                                   'time' => microtime(true) - $this->startTime
             ];
    }

    /**
         * Метод для проверки названия субъекта федерации.
         * Строгое сравнение с субъектами не требуется.
         * @param $needly
         * @param $haystack
         * @return bool
         */

    public static function checkEqual($needly, $haystack){

            //Приводим к нижнему регистру вводный параметр
            if(strpos(mb_strtolower($haystack), mb_strtolower($needly)) === false){
                return false;
            } else {
                return true;
            }

        }

    public function locationSearch($q){

            $q = str_replace('ё','е', $q);
            $field = new Field(['q', $q]);
            $this->requestContainer->add($field);
            $this->withAppkey();
            $request = $this->client->post(self::URL_KLADR, $this->requestContainer->toArray());

            $rawData = json_decode($request);

            if(!isset($rawData->cities) && $rawData->cities == [])
            {
                throw new \Exception('При обращении к методу '.self::URL_KLADR.' произошла ошибка');
            }

            return $rawData->cities;

        }


    public function selectLocationIfPlacesMany($locationList, $shortLocationName, $shortRegionName, $zip = false){

            //массив объектов которые подходят по условия первой итерации списка городов от api.
            //сокращаем количество элементов до одного.
            $poolPlaces = [];


            foreach ($locationList as $location){

                $itemLocationName = mb_strtolower($location->searchString);
                $isRegionEqual = self::checkEqual(trim($shortRegionName), trim($location->region_name));
                $isLocationEqual = self::checkEqual(trim($itemLocationName), trim($shortLocationName));

                if($isLocationEqual && $isRegionEqual){
                    $poolPlaces[] = $location;
                }

            }


            if(count($poolPlaces) !== 1){

                foreach ($poolPlaces as $place){

                    if($place->postalCode == $zip){
                        $result = $place;
                    }

                }

            } else {
                $result = $poolPlaces[0];
            }

            // if(empty($result)){

            //     $fnName = 'locationKLADR';

            //     $message = Loc::getMessage("ERROR_MESSAGE_MANY_PLACES");
            //     $context = $this->loggerContext($locationList, $zip, $locationName, $regionName,
            //                    $shortRegionName, $shortLocationName, $result, $poolPlaces);

            //     $this->logger->error($message, $context);

            //     //TODO временное решение, пока в методе API не добавят все индексы.
            //     // данное решение от части верное
                 $result = $poolPlaces[0];
            // }

            return $result;

        }



    
}