<?php
declare(strict_types=1);
/**
 * FluentInterface
 */

namespace Biatech\Lazev\Client;

use Biatech\Lazev\Base\DellinLogger;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;

final class DellinClient
{
   private const URL_STAGE = 'https://api.stage.dellin.dev';
   private const URL_PROD = 'https://api.dellin.ru';
    
    private Client $client;
    public LoggerInterface $logger;

    public function __construct()
    {

        $this->client = new Client([
            'base_uri' => $this->getBaseUri(),
        ]);

        $this->logger = new DellinLogger();
    }

    private function getBaseUri()
    {
        return self::URL_PROD;
//        return (getenv('ENVIRONMENT') == 'cms_stage')?
//                                                        self::URL_STAGE : self::URL_PROD;
 //       return self::URL_STAGE;
    }

    public function get(string $url)
    {

        $request =  $this->client->get($url);
        $code =  $request->getStatusCode();

        if($code != 200)
        {
            throw new \Exception('Не возможно получить доступ к '.$url.' статус ответа HTTP -'.$code);
        }

        return $request->getBody()->getContents();
    }

    public function post(string $path, array $payload): string
    {
        try {

            $payload = json_encode($payload,  JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
            $options = [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'charset' => 'utf-8'
                ],
                'body' => $payload
            ];


            $request = $this->client->request("POST", $path, $options);

            return $request->getBody()->getContents();

        } catch (ClientException $exception) {
            //TODO сделать нормальый обработчик

            $response = $exception->getResponse();
            $responseBody = $response->getBody()->getContents();
            $context = ['path' => $path,
                        'exception' => $exception,
                        'bodyRequest' => $payload,
                        'bodyResponse' => $responseBody
            ];
            $this->logger->error('Ответ от API с кодом 400',  $context);

            throw new \Exception($responseBody, 400, $exception );

        } catch (ConnectException $connectException) {

            $context = ['path' => $path,
                        'exception' => $connectException,
                        'bodyRequest' => $payload,
            ];

            $this->logger->error('Отсутствует подключение по HTTP', $context);
            throw new \Exception('Не возможно получить доступ к методу '.$path.' статус ответа HTTP 500', 500, $connectException );

        } 
    }



}