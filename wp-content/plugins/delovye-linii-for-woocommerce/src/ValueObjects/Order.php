<?php

declare(strict_types=1);

namespace Biatech\Lazev\ValueObjects;

use Biatech\Lazev\DTOs\AuthDTOFields;
use Biatech\Lazev\DTOs\LoadingParamsDTO;
use Biatech\Lazev\DTOs\CargoParamsDTOFields;
use Biatech\Lazev\DTOs\LoggerSettingsDTO;


use \ArrayObject;
use Biatech\Lazev\DTOs\OrderInfoDTO;

final class Order
{
    
    public array $products;
    public CounteragentInfo $counteragentInfo;
    public Location $arrivalLocation;
    public OrderInfoDTO $orderInfo;
    public WorkIntervals $workIntervals;
    

    
    public function __construct(array $products, Location $arrivalLocation, OrderInfoDTO $orderInfo,
                                WorkIntervals $workIntervals, ?CounteragentInfo $counteragentInfo)
    {
        //TODO добавить валидации
        $this->products = $products;
        $this->counteragentInfo = $counteragentInfo;
        $this->arrivalLocation = $arrivalLocation;
        $this->orderInfo = $orderInfo;
        $this->workIntervals = $workIntervals;
    }



}