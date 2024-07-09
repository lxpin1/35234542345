<?php

declare(strict_types=1);

namespace Biatech\Lazev\Helpers;


final class ExclusionList
{
    
    public static function listForRegionName(): array
    {

        //От коллектора иногда приходят пустые значения.
		 return [
             " саха (якутия)" => "саха",
			" северная осетия-алания"=> 'алания',
			' марий эл' => "марий",
		 ];
    }

    public static function listForPlaces(): array
    {
    
            //метод предназначен для городов федерального значения, которые иногда ошибочно приписывают к областям,
    	 //например, "севастополь крым" - это ошибка, севастополь это город федерального значения.
    
    		 return array(
                 "москва" => array(
                     'cityName' => "москва",
    				 'regionName' => "москва"
    			 ),
    			 "севастополь" => array(
                     'cityName' => "севастополь" ,
    				 'regionName' => "севастополь"
    			 ),
    			 "санкт-петербург" => array(
                     'cityName' => "санкт-петербург",
    				 'regionName' => "санкт-петербург"
    			 ),
    		 );
        }

    public static function listForQuery(): array
    {
        return [
            "бел белгородская " => [
                'q'=> "белгород белгородская",
			   'cityName' => "белгород",
			   'regionName'=> "белгородская"
		   ]
		 ];
    }
}