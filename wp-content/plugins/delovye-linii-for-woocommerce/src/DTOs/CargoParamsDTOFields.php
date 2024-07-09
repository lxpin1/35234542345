<?php

declare(strict_types=1);


namespace Biatech\Lazev\DTOs;

use Biatech\Lazev\DTOs\ProductDimensionsDTO;
use Biatech\Lazev\ValueObjects\RequirementsRequest;

final class CargoParamsDTOFields
{
    public bool $isSmallGoods;
    public bool $isInsuranceGoodsWithDeclarePrice;
    public bool  $isUseDefaultDimensions;
    public ?ProductDimensionsDTO $dimensions;
    //loading
    public ?RequirementsRequest $requirements_transport;
    //unloading
    public bool $is_terminal_unloading;


    public function __construct(bool $isSmallGoods, bool $isInsuranceGoodsWithDeclarePrice,
            ?ProductDimensionsDTO $dimensions, ?RequirementsRequest $requirements_transport,
            bool  $isUseDefaultDimensions = false, bool $is_terminal_unloading = false)
    {

        $this->isSmallGoods = $isSmallGoods;
        $this->isInsuranceGoodsWithDeclarePrice = $isInsuranceGoodsWithDeclarePrice;
        $this->isUseDefaultDimensions = $isUseDefaultDimensions;
        $this->dimensions = $dimensions;
        $this->requirements_transport = $requirements_transport;
        $this->is_terminal_unloading = $is_terminal_unloading;
    }
}


