<?php


namespace Biatech\Lazev\Services;

use Biatech\Lazev\Main;

use Biatech\Lazev\Base\Cache\CacheInterface;
use Biatech\Lazev\Base\Composite\Container;
use BiaTech\Lazev\Base\Composite\Field;
use Biatech\Lazev\Factories\FactoryMembersInPayload;
use Biatech\Lazev\Factories\FactoryPaymentInPayload;
use Biatech\Lazev\ValueObjects\CalculatorResult;
use Biatech\Lazev\ValueObjects\Cargo;
use Biatech\Lazev\ValueObjects\Order;
use Biatech\Lazev\ValueObjects\Settings;
use Biatech\Lazev\Requests\DellinBodyCalc;


final class DellinRequestService extends AbstractDellinService
{

    public Order $order;
    public Settings $settings;
    public Cargo $cargo;

    const PATH_REQUEST_V2 = '/v2/request.json';



    //TODO заменить
    public DellinBodyCalc $requestHandler;

    public ?\DateTime $differedDate;

    public DellinProduceDateService $dellinProduceDateService;


    public function __construct(Settings $settings, Order $order, CacheInterface $cache)
    {
        $this->startTime = microtime(true);
        parent::__construct($settings, $cache);
        $this->order = $order;
        $this->requestHandler = new DellinBodyCalc($settings, $order, $cache);

        $this->requestHandler->buildCargo();

        $this->cargo = $this->requestHandler->cargo;
        $this->differedDate = null;

    }



    public function getTrackingNumber()
    {
        $this->buildRequest();

        $request = $this->client->post(self::PATH_REQUEST_V2, $this->requestContainer->toArray());

        $response = \json_decode($request);

        if($this->settings->logging->is_logging)
        {
            $context = $this->getLoggerContext($response);
            $this->logger->info('Выполнен расчёт метода '.self::PATH_REQUEST_V2, $context);
        }
        return $response;
    }

    private function buildRequest()
    {
        $this->withAppkey();
        $this->withSessionID();

        $this->requestContainer->add(new Field(['delivery', $this->requestHandler->buildDeliveryData()]))
                               ->add(new Field(['cargo', $this->cargo->buildFullCargoInfo()]))
                               ->add(new Field(['members', $this->buildMembers()]))
                               ->add(new Field(['payment', $this->buildPayment()]))
                               ->add(new Field(['productInfo',[
                                                                'type'=> 4,
                                                                'productType'=> Main::PRODUCT_TYPE,
                                                                'info'=>[
                                                                        ['param'=> 'module version',
                                                                            'value'=> Main::MODULE_VERSION]
                                                                ]
                                ]]));

    }

    public function buildMembers(): Container
    {
        $members = $this->settings->counteragents;
        $members[] = $this->order->counteragentInfo;

        $factory = new FactoryMembersInPayload();

        return  $factory->create($members, 'request');

    }

    public function buildPayment(): Container
    {

        $factory = new FactoryPaymentInPayload($this->settings, $this->cache);

        return $factory->create($this->order->orderInfo->paymentInfo,
                                    $this->order->arrivalLocation->kladr_city,
                                    $this->settings->loadings_params->kladrCodeDeliveryFrom,
                                    $this->order->products);
    }

    public function getLoggerContext($response): array
        {
            return ['path' => self::PATH_REQUEST_V2,
                    'requestPayload' => $this->requestContainer->toArray(),
                    'responsePayload' => $response,
                    'time' => microtime(true) - $this->startTime
            ];
        }

}