<?php
declare(strict_types=1);


namespace Biatech\Lazev\DTOs;


final class LoadingParamsDTO
{
    //camel case -> frontend out settings
    public ?string $groupCargoPlacement;
    public ?string $kladrCodeDeliveryFrom;
    public ?string $cityDeliveryFrom;
    public ?int $deliveryDelay;
    public ?int $terminal_id;
    public bool $is_terminal_loading;
    public ?string $loadingAddress;


    public function __construct(?string $groupCargoPlacement, ?string $kladrCodeDeliveryFrom,
                                ?string $cityDeliveryFrom,?bool $is_terminal_loading=true, ?int $terminal_id = null,
                                ?string $loading_address=null, ?int $deliveryDelay = 1)
    {
        $this->groupCargoPlacement = $groupCargoPlacement;
        $this->kladrCodeDeliveryFrom = $kladrCodeDeliveryFrom;
        $this->cityDeliveryFrom = $cityDeliveryFrom;
        $this->deliveryDelay = $deliveryDelay;
        $this->terminal_id = $terminal_id;
        $this->loadingAddress = $loading_address;
        $this->is_terminal_loading = $is_terminal_loading;
    }
    
}

