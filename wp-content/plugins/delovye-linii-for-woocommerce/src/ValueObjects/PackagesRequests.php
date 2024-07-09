<?php

declare(strict_types=1);

namespace Biatech\Lazev\ValueObjects;

use Biatech\Lazev\DTOs\PackagesSettingsDTO;
use Biatech\Lazev\DTOs\PackageForRequestDTO;


use \Exception;

class PackagesRequests
{


    const COUNT_PACKING = ['0x82750921BC8128924D74F982DD961379',
                           '0x947845D9BDC69EFA49630D8C080C4FBE',
                           '0xad97901b0ecef0f211e889fcf4624fed',
                           '0xad97901b0ecef0f211e889fcf4624fea'
    ];

    public ?string $settings_resource;

    public array $result;

    public function __construct(?string $settings_resource)
    {
        $this->result = [];
        $this->settings_resource = $settings_resource;
        $this->prepareBuild();
    }


    public function prepareBuild()
    {
        if(!isset($this->settings_resource))
        {
            //TODO переписать на целевое исключение.
            throw new Exception('Информация по упаковкам пуста');
        }

        if(
            isset($this->settings_resource) &&
            $this->settings_resource != ''
        )
        {
            $prepare_string_to_array = explode(',', $this->settings_resource);
        

            foreach($prepare_string_to_array as $key=>$value)
            {
                $this->result[] = self::create_package($value);
            }
        }   
        
    }
    public function build(): array
    {
        return $this->result;
    }

    public static function create_package(string $service_uid): PackageForRequestDTO
    {
        if(in_array($service_uid,self::COUNT_PACKING))
        {
            return new PackageForRequestDTO($service_uid, 1);
        }

        return new PackageForRequestDTO($service_uid, null);
    }



}