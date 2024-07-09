<?php

namespace Biatech\Lazev\Services;

use Biatech\Lazev\Base\Cache\CacheInterface;
use Biatech\Lazev\Base\Composite\Field;
use Biatech\Lazev\Requests\DellinBodyCalc;
use Biatech\Lazev\Specifications\GroupsMethodsSpecification;
use Biatech\Lazev\ValueObjects\Cargo;
use Biatech\Lazev\ValueObjects\Order;
use Biatech\Lazev\ValueObjects\Settings;

final class DellinProduceDateService extends AbstractDellinService
{

    const PRODUCE_DATE_FOR_TERMINAL = '/v2/request/terminal/dates';
    const PRODUCE_DATE_FOR_ADDRESS = '/v2/request/address/dates';
    const DATES_FORMAT = "Y-m-d";

    public Order $order;
    public Cargo $cargo;

    public DellinBodyCalc $requestHandler;

    public CacheInterface $cache;

    public function __construct(Settings $settings, Order $order, CacheInterface $cache)
    {

        parent::__construct($settings, $cache);
        $this->order = $order;
        $this->cache = $cache;
        $this->requestHandler = new DellinBodyCalc($settings, $order, $cache);
    }

    public function getProduceDateList(): array
    {
        $startTime = microtime(true);
        $isTerminalsDerrival = $this->settings->loadings_params->is_terminal_loading;
        ($isTerminalsDerrival) ? $this->buildRequestForTerminanls() : $this->buildRequestForAddress();

        $path = ($isTerminalsDerrival) ? self::PRODUCE_DATE_FOR_TERMINAL : self::PRODUCE_DATE_FOR_ADDRESS;

        $response = $this->client->post($path, $this->requestContainer->toArray());

        $rawData = json_decode($response);

        if (!isset($rawData->data->dates) && empty($rawData->data->dates)) {
            $exception = new \Exception('Неожидаемый ответ метода ' . $path);

            if ($this->settings->logging->is_logging) {
                $endTime = microtime(true) - $startTime;
                $this->addLoggerContext($path, $this->requestContainer->toArray(), $rawData, $endTime, $exception);
                $this->logger->error('Неожидаемый ответ метода ' . $path, $this->loggerContext);
            }
            throw $exception;
        }
        if ($this->settings->logging->is_logging) {
            $endTime = microtime(true) - $startTime;
            $this->addLoggerContext($path, $this->requestContainer->toArray(), $rawData, $endTime, null);
            $this->logger->debug('Выполненный метод ' . $path , $this->loggerContext);

        }
        //TODO добавить кеширование из запись в лог.
        return $rawData->data->dates;

    }

    public function getAvailableCalcDate(): string
    {

        //Получаем текущую дату, для последующих проверок.
        $dateNow = new \DateTimeImmutable();
        $formatDateNow = $dateNow->format("d-m-Y");
        //Получаем список дат доступных для сдачи груза на терминал
        $dates = $this->getProduceDateList();

        //Удаляем текущую дату
        $dates = array_diff($dates, [$formatDateNow]);

        $first = current($dates);

        $firstDate = new \DateTimeImmutable($first);
        $days = $this->settings->loadings_params->deliveryDelay;
        $differedDate = $firstDate->modify($days.' day');



        if(in_array($differedDate->format(self::DATES_FORMAT), $dates))
        {
            return $differedDate->format(self::DATES_FORMAT);
        }
        //TODO дописать алгоритим сравнивания дат.

        return next($dates);

    }
    private function buildRequestForAddress(): void
    {
        $this->withAppkey();
        $this->withSessionID();
        $this->requestContainer->add(new Field(['delivery', $this->requestHandler->buildDeliveryData()]));
        $this->requestContainer->add(new Field(['cargo', $this->requestHandler->cargo->buildFullCargoInfo()]));

    }

    private function buildRequestForTerminanls(): void
    {
        $this->withAppkey();
        $this->requestContainer->add(new Field(['delivery', $this->requestHandler->buildDeliveryData()->toArray()]));
        $this->requestContainer->add(new Field(['cargo', $this->requestHandler->cargo->buildFullCargoInfo()]));
    }

    private function addLoggerContext($path, $request,
                                      $response, $time, $exception): void
    {
        $this->loggerContext[] = ['requestPath' => $path,
                                'requestBody' => $request,
                                'responseBody' => $response,
                                'time' => $time,
                                'exception' => $exception];
    }
}