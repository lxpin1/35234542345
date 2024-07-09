<?php

namespace Biatech\Lazev\Requests;


use Biatech\Lazev\Base\Cache\CacheInterface;
use Biatech\Lazev\Base\Composite\Container;
use Biatech\Lazev\DTOs\ProductDimensionsDTO;
use Biatech\Lazev\Main;
use Biatech\Lazev\Specifications\SmallCargoSpecification;
use Biatech\Lazev\ValueObjects\Cargo;
use Biatech\Lazev\ValueObjects\Order;
use Biatech\Lazev\ValueObjects\PackagesRequests;
use Biatech\Lazev\ValueObjects\Settings;
use BiaTech\Lazev\Base\Composite\Field;


abstract class AbstractDellinRequest
{

    public Cargo $cargo;
    public array $counteragents;
    public Settings $settings;
    public Container $payment;
    public ?Order $order;
    public CacheInterface $cache;


    public string $deliveryType = 'auto';




    public function __construct(Settings $settings, Order $order, CacheInterface $cache)
    {
        $this->settings = $settings;
        $this->order = $order;
        $this->cache = $cache;
        $this->buildCargo();
    }


    function buildPackages()
    {
        return $this->settings->packages->build();
    }

    function buildCargo()
    {

        $this->cargo = new Cargo($this->settings, $this->order->orderInfo, $this->order->arrivalLocation,
                                $this->order->products);


    }


    function getTimeToDerrival()
    {

       $workIntervals = $this->settings->workIntervalsDerrival;
        return (!$this->settings->loadings_params->is_terminal_loading)?
                        new Field(['time', $workIntervals->get_time_to_request()]) : '';

    }

    function getTimeToArival()
    {

        $workIntervals = $this->order->workIntervals->get_time_to_request();

        return (!$this->settings->default_cargo_params->is_terminal_unloading)? new Field(['time', $workIntervals]): '';

    }

    public function buildDeliveryData()
    {

        $delivery = new Container();

        $deliveryType = $this->buildDeliveryType();
        $arrival = new Field(['arrival', $this->buildDeliveryArrival()]);
        $derival = new Field(['derival',$this->buildDeliveryDerrival()]);
        $packages = new Field(['packages', $this->buildPackages()]);

        $delivery->add($deliveryType);
        $delivery->add($arrival);
        $delivery->add($derival);
        $delivery->add($packages);


        return $delivery;

    }

     abstract public function buildDeliveryDerrival();

     abstract function buildDeliveryArrival();

    public function getArivalVariantField()
    {
        $variantType = ($this->settings->default_cargo_params->is_terminal_unloading)?'terminal':'address';
        return new Field(['variant', $variantType]);
    }

    public function getDerrivalVariantField()
    {
        $variantType = ($this->settings->loadings_params->is_terminal_loading)?'terminal':'address';
        return new Field(['variant', $variantType]);
    }

    public function setProduceDate(string $produceDate): void
    {
        $this->produceDate = $produceDate;
    }

    public function isSmallCargoTry()
    {

        $arrivalKladr = $this->order->arrivalLocation->kladr_city;
        $derrivalKladr = $this->settings->loadings_params->kladrCodeDeliveryFrom;

        $dimensions = new ProductDimensionsDTO($this->cargo->maxProductLength,
                                                       $this->cargo->maxProductWidth,
                                                       $this->cargo->maxProductHeight,
                                                        null,
                                                        null,
                                                        null);

        $spec = new SmallCargoSpecification($this->settings, $arrivalKladr,
                                                    $derrivalKladr, $dimensions,
                                                    $this->cargo->totalVolume,
                                                    $this->cargo->totalWeight, $this->cache);
        return $spec->isSatisfiedBy();

    }


    public function buildDeliveryType()
    {

        if($this->isSmallCargoTry())
        {
            $this->deliveryType = 'small';
            $this->cargo->setDeliveryType('small');
        }

        $type = new Field(['type', $this->deliveryType]);
        return new Field(['deliveryType', $type]);

    }


    /**
     * @return Container
     */
    public function buildProductInfo(): Container
    {
        $productInfo = new Container();

        $type = new Field(['type', 4]);
        $productType = new Field(['productType', Main::PRODUCT_TYPE ]);
        $param = new Field(['param', 'module version']);
        $value = new Field(['value', Main::MODULE_VERSION]);
        $infoContainer = new Container();
        $infoContainer->add($param);
        $infoContainer->add($value);
        $productInfo->add($type);
        $productInfo->add($productType);

        $infoField = new Field(['info', array($infoContainer->toArray())]);

        $productInfo->add($infoField);

        return $productInfo;
    }

}