<?php
declare(strict_types=1);



namespace Biatech\Lazev\DTOs;


final class OrderInfoDTO
{
    public ?string $orderID;
    public ?string $shipmentID;
    public ?float $priceBasketItems;
    public ?float $priceShipment;
    public ?float $totalPriceOrder;

    public ?PaymentInfoOrderDTO $paymentInfo;

    public function __construct(?PaymentInfoOrderDTO $paymentInfo ,?float $priceBasketItems,
                                ?float $totalPriceOrder = null, ?string $orderID = null,
                                    ?string $shipmentID = null, ?float $priceShipment = null)
    {
        $this->paymentInfo = $paymentInfo;
        $this->priceShipment = $priceShipment;
        $this->priceBasketItems = $priceBasketItems;
        $this->totalPriceOrder = $totalPriceOrder;
        $this->orderID = $orderID;
        $this->shipmentID = $shipmentID;

    }


}