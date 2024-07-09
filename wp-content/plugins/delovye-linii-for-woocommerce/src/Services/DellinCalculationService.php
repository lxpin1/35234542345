<?php


namespace Biatech\Lazev\Services;

use Biatech\Lazev\Main;

use Biatech\Lazev\Base\Cache\CacheInterface;
use BiaTech\Lazev\Base\Composite\Field;
use Biatech\Lazev\Factories\FactoryMembersInPayload;
use Biatech\Lazev\Factories\FactoryPaymentInPayload;
use Biatech\Lazev\ValueObjects\CalculatorResult;
use Biatech\Lazev\ValueObjects\Cargo;
use Biatech\Lazev\ValueObjects\Order;
use Biatech\Lazev\ValueObjects\Settings;
use Biatech\Lazev\Requests\DellinBodyCalc;


final class DellinCalculationService extends AbstractDellinService
{

    public Order $order;
    public Settings $settings;
    public Cargo $cargo;

    const PATH_CALCULATOR_V2 = '/v2/calculator.json';
    const CACHE_TTL = 300;

    public DellinBodyCalc $requestHandler;

    public ?\DateTime $differedDate;
    public DellinProduceDateService $dellinProduceDateService;



    public function __construct(Settings $settings, Order $order, CacheInterface $cache)
    {
        $this->startTime = microtime(true);
        parent::__construct($settings, $cache);
        $this->order = $order;
        $this->dellinProduceDateService =
                        new DellinProduceDateService($settings, $order, $cache);

        $this->requestHandler = $this->dellinProduceDateService->requestHandler;
        $this->cargo = $this->requestHandler->cargo;

        $this->differedDate = $this->getDefferedDate();


    }

    public function getCalculate() : CalculatorResult
    {

        $hash = $this->getHashOrderAndSettings();

        if($this->cache->has($hash))
        {
            return $this->cache->get($hash);
        }

        $calculationResult = $this->calculate();

        if($calculationResult instanceof CalculatorResult)
        {
            $this->cache->set($hash, $calculationResult, self::CACHE_TTL );
        }
        
        return $calculationResult;

    }

    private function getHashOrderAndSettings(): string
    {

        $serializeSettings = serialize($this->settings);
        $serializeOrder = serialize($this->order);
        return md5($serializeOrder .''. $serializeSettings);


    }
    public function calculate(): CalculatorResult
    {

        $this->buildRequestCalc();

        $request = $this->client->post(self::PATH_CALCULATOR_V2,
                                    $this->requestContainer->toArray());

        $response = json_decode($request);


        if($this->settings->logging->is_logging)
        {

            $context = $this->getLoggerContext($response);

            $context[] =  new CalculatorResult($this->getCalculatePrice($response),
                                    $this->differedDate,
                                    $this->getDateArrival($response),
                                    $this->getTerminals($response));

            $this->logger->info('Выполнен расчёт метода '.self::PATH_CALCULATOR_V2, $context);

        }


        return new CalculatorResult($this->getCalculatePrice($response),
                                    $this->differedDate,
                                    $this->getDateArrival($response),
                                    $this->getTerminals($response));

    }


    public function getLoggerContext($response): array
    {
        return ['path' => self::PATH_CALCULATOR_V2,
                'requestPayload' => $this->requestContainer->toArray(),
                'responsePayload' => $response,
                'time' => microtime(true) - $this->startTime
        ];
    }

    /**
     * @param \DateTime $differedDate
     */
    public function setDifferedDate(\DateTime $differedDate): void
    {
        $this->differedDate = $differedDate;
    }


    public function getCalculatePrice($response)
    {

        if($response->data->price > 0)
        {
            return $response->data->price;
        }

        if($this->settings->logging->is_logging)
        {
            $this->logger->error(
                    'Результат расчёта '.self::PATH_CALCULATOR_V2.' не может быть меньше нуля',
                    $this->getLoggerContext($response));
        }

         //TODO написать в логи
        throw new \Exception('Цена меньше нуля');
    }

    public function getDateArrival($response)
    {
        if(!$this->settings->default_cargo_params->is_terminal_unloading)
        {
            return new \DateTime($response->data->orderDates->arrivalToOspReceiver);
        }

        if($this->settings->default_cargo_params->is_terminal_unloading)
        {
            return new \DateTime ($response->data->orderDates->derivalFromOspReceiver);
        }

        throw new \Exception('Невозможно получить дату поступления на ОСП или дату готовности к отгрузки до адреса');

    }


    public function getTerminals($response): array
    {
        return $response->data->arrival->terminals;
    }

    public function getDefferedDate():\DateTime
    {
        if(!isset($this->differedDate))
        {
            $availableDateString = $this->dellinProduceDateService->getAvailableCalcDate();
            $date = new \DateTime($availableDateString);
            $this->differedDate = $date;

            return $date;
        }

        return $this->differedDate;
    }

    public function buildRequestCalc()
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

    public function buildMembers()
    {
        $members = $this->settings->counteragents;
        $members[] = $this->order->counteragentInfo;

        $factory = new FactoryMembersInPayload();

        return  $factory->create($members, 'calc');

    }

    public function buildPayment()
    {

        $factory = new FactoryPaymentInPayload($this->settings,$this->cache);

        return $factory->create($this->order->orderInfo->paymentInfo,
                                $this->order->arrivalLocation->kladr_city,
                                $this->settings->loadings_params->kladrCodeDeliveryFrom,
                                $this->order->products);
    }


}