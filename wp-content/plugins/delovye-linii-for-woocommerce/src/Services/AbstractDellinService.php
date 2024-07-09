<?php
declare(strict_types=1);

namespace Biatech\Lazev\Services;

use Biatech\Lazev\Base\Cache\CacheInterface;
use Biatech\Lazev\Base\Composite\Container;
use Biatech\Lazev\Base\Composite\Field;
use Biatech\Lazev\Base\DellinLogger;
use Biatech\Lazev\Client\DellinClient;
use Biatech\Lazev\ValueObjects\Settings;


class AbstractDellinService
{
    protected Settings $settings;

    protected DellinAuthService $authService;

    protected DellinClient $client;
    public DellinLogger $logger;
    public float $startTime;
    public float $allTime;
    public ?array $loggerContext;
    public Container $requestContainer;

    public CacheInterface $cache;



    public function __construct( Settings $settings, CacheInterface $cache)
    {
        $this->client = new DellinClient();
        $this->authService = new DellinAuthService($settings->auth, $cache);
        $this->settings = $settings;
        $this->requestContainer = new Container();
        $this->logger = new DellinLogger();
        $this->loggerContext = [];
        $this->cache = $cache;
    }

    /**
     * @throws \Exception
     */
    public function withAppkey():AbstractDellinService
    {
        $this->authService = new DellinAuthService($this->settings->auth, $this->cache);
        $appKeyField = new Field(['appkey', $this->authService->getAppkey()]);
        $this->requestContainer->add($appKeyField);
        return $this;
    }

    /**
     * @throws \Exception
     */
    public function withSessionID():AbstractDellinService
    {
        $this->authService = new DellinAuthService($this->settings->auth, $this->cache);
        $appKeyField = new Field(['sessionID', $this->authService->getSessionID()]);
        $this->requestContainer->add($appKeyField);
        return $this;
    }

    
}