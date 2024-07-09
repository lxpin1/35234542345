<?php

declare(strict_types=1);

namespace Biatech\Lazev\Services;

use Biatech\Lazev\Base\Composite\Container;
use Biatech\Lazev\Base\Composite\Field;
use Biatech\Lazev\Client\DellinClient;
use Biatech\Lazev\DTOs\AuthDTOFields;
use Psr\Http\Message\ResponseInterface;

use Psr\Log\LoggerInterface;
use Biatech\Lazev\Base\Cache\CacheInterface;


final class DellinAuthService
{

    private DellinClient $client;

    private AuthDTOFields $authDTOFields;
    private LoggerInterface $logger;
    private CacheInterface  $cache;

    const URL_LOGIN = '/v3/auth/login.json';
    const CACHE_TTL = '43200';//24h
    const CACHE_PATH = 'dellin.session.id';


    //TODO Выполнить методы для логаута и информации сессии.
//    const URL_LOGOUT = '/v3/auth/logout.json';
//    const URL_SESSION_INFO = '/v3/auth/session_info.json';

    public ?string $sessionID;
    public ?string $appkey;
    public ?bool $isAuthUser;

    public function __construct(?AuthDTOFields $authDTOFields, CacheInterface $cache)
    {
        $this->isAuthUser = false;
        $this->cache = $cache;

        if(!$authDTOFields instanceof AuthDTOFields)
        {
            throw new \Exception('Не валидные данные для авторизации');
        }

        $this->authDTOFields = $authDTOFields;
        $appkey = trim($authDTOFields->appkey);

        if(empty($appkey))
        {
            throw new \Exception('Ключ приложения не может быть пустой строкой');
        }

        if(isset($appkey) && $appkey != '')
        {
            $this->appkey = $this->authDTOFields->appkey;
        }

        $this->client = new DellinClient();
    }

    private function doLogin(): string
    {

        //$login = ;
        //$password = $this->authDTOFields->password;

        if(!isset($this->authDTOFields->login) && !isset($this->authDTOFields->password) &&
           $this->authDTOFields->login == '' && $this->authDTOFields->password == '')
            {
                throw new \Exception('Логин и пароль пустые');
            }

        return $this->client->post(self::URL_LOGIN, $this->buildRequestLogin());

    }

    private function buildRequestLogin(): array
    {
        $root = new Container();

        $appkey = new Field(['appkey', $this->getAppkey()]);
        $login = new Field(['login', $this->authDTOFields->login]);
        $password = new Field(['password', $this->authDTOFields->password]);

        $root->add($appkey)
             ->add($login)
             ->add($password);

        return $root->toArray();

    }


    /**
     * @return string|null
     */
    public function getAppkey(): ?string
    {
        return $this->appkey;
    }

    /**
     * @return string|null
     */
    public function getSessionID(): string
    {
        //TODO добавить логику для кеширования.
        // параметр rewive bool;

        $hash = $this->getHashToCache();
        if($this->cache->has(self::CACHE_PATH))
        {
            $currentValue = $this->cache->get(self::CACHE_PATH);
            if($currentValue->hash == $hash)
            {
                $this->isAuthUser = true;
                $this->sessionID = $currentValue->sessionID;

                return $this->sessionID;
            }

        }

        $request = $this->doLogin();

        $rawData = json_decode($request);

        if(!isset($rawData->data->sessionID) && $rawData->data->sessionID == '' )
        {
            throw new \Exception('Ответ метода авторизации '.self::URL_LOGIN.' не может быть обработан');
        }

        $this->sessionID = $rawData->data->sessionID;
        $this->isAuthUser = true;

        
        $storage = new \stdClass();
        $storage->hash = $hash;
        $storage->sessionID = $this->sessionID;


        $this->cache->set(self::CACHE_PATH, $storage, self::CACHE_TTL);

        return $this->sessionID;
    }

    private function getHashToCache()
    {
        $serializedData = serialize($this->authDTOFields);
        return md5($serializedData);
    }
    /**
     * @return bool|null
     */
    public function isAuthUser(): ?bool
    {
        return $this->isAuthUser;
    }

}

