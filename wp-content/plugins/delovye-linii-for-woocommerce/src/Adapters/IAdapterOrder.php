<?php
declare(strict_types=1);


namespace Biatech\Lazev\Adapters;

use Biatech\Lazev\DTOs\OrderInfoDTO;
use Biatech\Lazev\ValueObjects\WorkIntervals;
use Biatech\Lazev\ValueObjects\Location;
use Biatech\Lazev\ValueObjects\CounteragentInfo;

interface IAdapterOrder {
    public function getProducts(): array;
    public function getCounterAgentInfo():CounteragentInfo;
    public function getArrivalLocation():Location;
    public function getOrderInfo(): OrderInfoDTO;
    public function workTimeArrival(): WorkIntervals;
}