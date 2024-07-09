<?php


declare(strict_types=1);

namespace Biatech\Lazev\Services;


use Biatech\Lazev\Base\Composite\Field;

final class DellinCounteragentsService extends AbstractDellinService
{

    const PATH_COUNTERAGENTS = '/v2/counteragents.json';
    
    /**
     * Делаем фасад старого  формата ответа, что бы схавал фронт.
     * TODO: удалить после перехода на нормальную структуру ответа.
     */
    public function getLegacyFacadeCounteragents(): array
    {
        $result = [];
        $counteragents = $this->getCounteragents();
        $result['counteragents'] = [''=>['name'=> 'Не выбран', 'uid'=>'']];
        $result['sessionID'] = $this->authService->getSessionID();
        
        foreach($counteragents as $counteragent)
        {
            $result['counteragents'][$counteragent->uid]= ["name"=>$counteragent->name, 'uid'=>$counteragent->uid];
        }
        
        $result['count'] = count($result);
        $result['code'] = 200;
        
        return $result;
    }
    
    public function getCounteragents(bool $fullInfo = false, ?string  $cuid = null) :  array
    {
        $this->startTime = microtime(true);
        $this->withAppkey();
        $this->withSessionID();
        
        if(isset($cuid))
        {
            $this->requestContainer->add(new Field(['cauid', $cuid]));
        }
        
        $this->requestContainer->add(new Field(['fullInfo', $fullInfo]));
        
        $request = $this->client->post(self::PATH_COUNTERAGENTS,  $this->requestContainer->toArray());
        
        $response = json_decode($request);


        
        if(isset($response->data->counteragents))
        {
            if($this->settings->logging->is_logging)
            {
                $context = [
                    'request'=> $this->requestContainer->toArray(),
                    'response' => $response,
                    'time' => microtime(true) - $this->startTime
                    ];
                
                $this->logger->info('Выполнено обращение к методу '.self::PATH_COUNTERAGENTS, $context);
    
                
            }
            return $response->data->counteragents; 
        }
        
        if($this->settings->logging->is_logging)
        {
            $context = [
                'request' => $this->requestContainer->toArray(),
                'response' => $response,
                'time' => microtime(true) - $this->startTime
            ];
            
            $this->logger->error('Список контрагентов от метода '.self::PATH_COUNTERAGENTS.' не определён', $context);
            
        }
        
        throw new \Exception('Невозможно получить список контрагентов');
        
    }
    
    
}