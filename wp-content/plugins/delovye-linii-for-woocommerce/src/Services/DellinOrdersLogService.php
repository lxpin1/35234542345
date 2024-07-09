<?php


namespace Biatech\Lazev\Services;

use Biatech\Lazev\Base\Composite\Field;

final class DellinOrdersLogService extends AbstractDellinService
{
    const PATH_ORDERS_V3 = 'v3/orders.json';
    
    public function getOrderByRequestID(string $requestID)
    {
        $this->startTime = microtime(true);
        
        $this->withAppkey();
        $this->withSessionID();
        $field = new Field(['docIds', [$requestID]]);
        $this->requestContainer->add($field);
        
        $request = $this->client->post(self::PATH_ORDERS_V3, $this->requestContainer->toArray());
        
        $bodyResponse = json_decode($request);
        return $bodyResponse->orders[0];
    }
}