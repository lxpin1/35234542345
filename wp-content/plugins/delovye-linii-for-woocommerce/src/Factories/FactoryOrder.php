<?php

declare(strict_types=1);


namespace Biatech\Lazev\Factories;


use Biatech\Lazev\Adapters\IAdapterOrder;
use Biatech\Lazev\ValueObjects\Order;


final class FactoryOrder {

    public ?IAdapterOrder $adapterOrder;
    //TODO сделать возможность порождать
    // заказ гранулярно


    public function create(): Order
    {

        if(isset($this->adapterOrder))
        {
            return  new Order($this->adapterOrder->getProducts(),
                              $this->adapterOrder->getArrivalLocation(),
                              $this->adapterOrder->getOrderInfo(),
                              $this->adapterOrder->workTimeArrival(),
                              $this->adapterOrder->getCounterAgentInfo()
            );
        }

    }

    public function setDataOrderAdapter(IAdapterOrder $adapter) : void
    {
        $this->adapterOrder = $adapter;
    }


}