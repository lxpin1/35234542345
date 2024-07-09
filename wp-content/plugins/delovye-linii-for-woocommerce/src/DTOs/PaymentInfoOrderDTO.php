<?php

namespace Biatech\Lazev\DTOs;

final class PaymentInfoOrderDTO
{
    public ?string $payment_id;
    public ?bool $isCashOnDelivery;
    public ?bool $isCash;
    
    public function __construct(?string $payment_id, ?bool $isCashOnDelivery = false,
                                ?bool $isCash)
    {
        $this->payment_id = $payment_id;
        $this->isCashOnDelivery = $isCashOnDelivery;
        $this->isCash = $isCash;
    }
}