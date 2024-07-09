<?php
declare(strict_types=1);
/**
 * Переиспользуемая сущность Cargo. Определяет параметры для погрузки.
 * Объектно-ориентированное представление для работы с сборщиком параметров для отправки запросов.
 * Сущность Cargo используется в методах получения produceDate, CalculatorV2, RequestV2.
 * Текущая сущность может изменять параметр deliveryType внутри конфига на малогабарит(при пройденной валидации).
 * @author: Vadim Lazev
 * @company: BIA-Tech
 * @year: 2024
 */



namespace Biatech\Lazev\ValueObjects;


use Biatech\Lazev\DTOs\OrderInfoDTO;
use Biatech\Lazev\DTOs\ProductDimensionsDTO;
use Biatech\Lazev\Specifications\DimensionsPassSpecification;
use Biatech\Lazev\Specifications\GroupsMethodsSpecification;
use Biatech\Lazev\Specifications\SmallCargoSpecification;

final class Cargo
{

    /**
     * Глобальные ограничения грузоперевозок.
     * По сути - это размер еврофуры.
     */
    const GLOBAL_STRICTS = [
        'length' => 13.6,
        'width_height' => 2.4,
        'totalVolume' => 80,
        'totalWeight' => 20000
    ];

    /**
     * Рассмотреть добавление метода
     * https://dev.dellin.ru/api/catalogs/parametry-negabaritnogo-gruza/
     * Из рисков - замедление работы и так медленного калькулятора
     */
    const PLACE_STRICTS = [
        'length' => 3,
        'width_height' => 3,
        'totalVolume' => 27,
        'totalWeight' => 99
    ];
    /**
     * Настройки глобальные для метода доставки.
     * На что мы тут смотрим и что влияет:
     * - Характер груза;
     * - значения параметров по-умолчанию (и их ВГХ);
     * - Группировки грузоместа;
     * - Малогабаритный груз;
     * - флаг передачи объявленной стоимости;
     * - точка отправления и параметр направления;
     */

    private Settings $settings;


    /**
     * Позиции товарного каталога или справочника номенклуатуры,
     *  можно сопоставить с грузоместами, но грузоместа в requestv2
     *  не передаются поэтому приводим к cargo;
     *
     */
    public array $products;

    /**
     * Дополнительные параметры
     */
    public OrderInfoDTO $orderInfo;
    /**
     * Параметры получателя
     */
    public Location $location;

    public float $totalVolume = 0;
    public float $totalWeight = 0;

    public int $quantity = 0;
    public float $maxProductLength = 0;
    public float $maxProductHeight = 0;
    public float $maxProductWidth = 0;
    /**
     * Заявленная максимальная масса
     */
    public float $weight = 0;
    public float $maxSide = 0;
    /**
     * Параметры негабарита
     */
    public float $oversizedWeight = 0;
    public float $oversizedVolume = 0;

    public string $freightName = '';
    public string $deliveryType = 'auto';



    public function __construct(Settings $settings, OrderInfoDTO $orderInfo,
                                Location $location, array $products)
    {
        $this->settings = $settings;
        $this->orderInfo = $orderInfo;
        $this->location = $location;

        if($products == [])
        {
            throw new \Exception('Не возможно собрать груз. Позиции товарного каталога отсутствуют.');
        }

        $this->setChangeProductDimensions($products);
        $this->products = $products;

        $this->setMaxDemensions();
        $this->groupQuantityProductsToCargoWithOversize();
        $this->switchToValidLWH();


    }

    public function hasCapacityNotValid(): bool
    {
        $dimensions = new ProductDimensionsDTO($this->maxProductLength, $this->maxProductWidth, $this->maxProductHeight,
            null, null, null);


        $spec = new DimensionsPassSpecification(self::GLOBAL_STRICTS, $this->totalVolume,
            $this->totalWeight, $dimensions);


        return $spec->isSatisfiedBy();
    }

    private function setChangeProductDimensions(&$products): void
    {
        if ($this->settings->default_cargo_params->isUseDefaultDimensions) {
            foreach ($products as $product) {
                //ЕСЛИ хотя бы один параметр пустой, тогда подставляем
                //значения по-умолчанию.
                $this->freightName .=  $product->name;
                if (
                    empty($product->dimensions->length) ||
                    empty($product->dimensions->width) ||
                    empty($product->dimensions->height) ||
                    empty($product->dimensions->weight)
                ) {
                    $product->setDimensions($this->settings->default_cargo_params->dimensions);
                }
            }
        }
    }

    private function setMaxDemensions(): void
    {

        $products = $this->products;

        /**
         * Определяем самые крупные габариты для валидации возможности погрузки.
         *
         */

        foreach ($products as $index => $product) {


            if ($product->dimensions->length > $this->maxProductLength) $this->maxProductLength = $product->dimensions->length;
            if ($product->dimensions->height > $this->maxProductHeight) $this->maxProductHeight = $product->dimensions->height;
            if ($product->dimensions->width > $this->maxProductWidth) $this->maxProductWidth = $product->dimensions->width;
            if ($product->dimensions->weight > $this->weight) $this->weight = $product->dimensions->weight;
            // ---Start---
            // Выпилил эт параметр т.к. он применяюется ниже при группировании грузомест.
            $this->totalWeight += $product->dimensions->weight * $product->quantity;
            $this->totalVolume += $product->dimensions->length * $product->dimensions->height * $product->dimensions->width * $product->quantity;


            // ---END---
            $this->freightName .= (($index != 0) ? ',' : '') . $product->name;
        }

    }


    public function groupQuantityProductsToCargoWithOversize():void
    {


        /**
         * Про негабарит.
         * Негабрит считается по месту, но грузовых мест нет.
         * В нашем случае грузовое место и позиция товарного каталога
         * это одно и то же. Поэтому нам нужно сверять каждый товар
         * на предмет ограничений негабарита.
         */

        $checker = new GroupsMethodsSpecification($this->settings->loadings_params->groupCargoPlacement);

        if (!$checker->isSatisfiedBy()) {
            throw new \Exception('Неожиданный метод группировки');
        }

        if ($this->settings->loadings_params->groupCargoPlacement == GroupsMethodsSpecification::SINGLE_ITEM_SINGLE_SPACE) {
            /**
             * Один товар - одно грузовое место.
             * Берём и каждый товар сравниваем на ограничения негабарита
             */

            foreach ($this->products as $product) {

                $volume = $product->dimensions->length * $product->dimensions->height * $product->dimensions->width;
                $this->quantity += $product->quantity;

                $this->totalVolume += $volume * $product->quantity;

                $spec = new DimensionsPassSpecification(self::PLACE_STRICTS, $volume,
                    $product->dimensions->weight, $product->dimensions);

                if ($spec->isSatisfiedBy()) {
                    $this->oversizedWeight += $product->dimensions->weight * $product->quantity;
                    $this->oversizedVolume += $volume * $product->quantity;
                }
            }
        }

        /**
         * Группируем по уникальному идентификатору товара.
         * Собираем товары в импровизириованный брикет и сравниваем с ограничениями негабарита.
         */

        if ($this->settings->loadings_params->groupCargoPlacement == GroupsMethodsSpecification::SEPARATED_CARGO_SPACE) {
            $this->quantity = count($this->products);

            foreach ($this->products as $product) {
                $volume = ($product->dimensions->length * $product->dimensions->height *
                        $product->dimensions->width) * $product->quantity;
                $weight = $product->dimensions->weight * $product->quantity;


                //При группировке укладываем позиции в высоту.
                $height = $product->dimensions->height * $product->quantity;

                // заменяем позиции для проверки негаба.
                $product->dimensions->height = $height;
                $product->dimensions->weight = $weight;






                //Сверяем наибольшую сторону места и массу, если она больше - заменяем.
                if ($product->dimensions->height > $this->maxProductHeight) $this->maxProductHeight = $product->dimensions->height;
                if ($product->dimensions->weight > $this->weight) $this->weight = $product->dimensions->weight;


                $spec = new DimensionsPassSpecification(self::PLACE_STRICTS, $volume, $weight,
                    $product->dimensions);

                if ($spec->isSatisfiedBy()) {
                    $this->oversizedWeight += $weight;
                    $this->oversizedVolume += $volume;
                }
            }

        }

        /**
         * Группируем всё.
         * Например: ВЕСЬ заказ кладётся в деревянный ящик
         */
        if ($this->settings->loadings_params->groupCargoPlacement == GroupsMethodsSpecification::ONE_CARGO_SPACE) {

            $this->quantity = 1;
            $this->weight = $this->totalWeight;
            $height = 0;
            foreach ($this->products as $product) {
                $height += $product->dimensions->height * $product->quantity;
            }

            $this->maxProductHeight = $height;

            $dimensions = new ProductDimensionsDTO($this->maxProductLength, $this->maxProductWidth,
                $this->maxProductHeight, null, null, null);
            $spec = new DimensionsPassSpecification(self::PLACE_STRICTS, $this->totalVolume,
                $this->totalWeight, $dimensions);

            if ($spec->isSatisfiedBy()) {
                $this->oversizedWeight = $this->totalWeight;
                $this->oversizedVolume = $this->totalVolume;
            }
        }

    }

    /**
     * Метод, который отвечает за кантовку груза внутри погрузочного места.
     *
     */
    public function switchToValidLWH(): void
    {
        // т.к. длина - это параметр погрузки вдоль кузова, принимаем его за макcимальный.

        $this->maxSide = $this->maxProductLength;
        if ($this->maxProductWidth > $this->maxProductLength) {
            $this->maxSide = $this->maxProductWidth;
            $this->maxProductWidth = $this->maxProductLength;
            $this->maxProductLength = $this->maxSide;
        }

        if ($this->maxProductHeight > $this->maxProductLength) {
            $this->maxSide = $this->maxProductHeight;
            $this->maxProductHeight = $this->maxProductLength;
            $this->maxProductLength = $this->maxSide;
        }

        if (empty($this->weight) ||
            empty($this->totalVolume) ||
            empty($this->totalWeight) ||
            empty($this->maxProductLength) ||
            empty($this->maxProductWidth) ||
            empty($this->maxProductHeight)) {
            throw new \Exception('Cargo is not valid');
        }
    }


    /**
     * Получение объявленной стоимости товаров всех в заказе
     */
    private function getStatedValue(): ?float
    {
        if ($this->settings->default_cargo_params->isInsuranceGoodsWithDeclarePrice) {
            return $this->orderInfo->priceBasketItems;
        }

        return 0;
    }

    /**
     * @param string $deliveryType
     */
    public function setDeliveryType(string $deliveryType): void
    {
        $this->deliveryType = $deliveryType;
    }

    /**
     * Метод формирующий результат мутирования данных для запросов.
     * Используется как конечный интерфейс для определения параметров для отправки сущности Cargo.
     * @return array
     * @throws \Exception
     */
    public function buildFullCargoInfo(): array
    {



        if ($this->hasCapacityNotValid()) {
            throw new \Exception('Размеры груза превышают максимальные размеры транспортного средства');
        }


        $result = [
            'quantity' => $this->quantity,
            'length' => (floatval($this->maxProductLength) < 0.01) ?
                0.01 : round($this->maxProductLength, 2),
            'width' => (floatval($this->maxProductWidth) < 0.01) ?
                0.01 : round($this->maxProductWidth, 2),
            'height' => (floatval($this->maxProductHeight) < 0.01) ?
                0.01 : round($this->maxProductHeight, 2),
            'weight' => (floatval($this->weight) < 0.01) ?
                0.01 : round($this->weight, 2),
            'totalVolume' => (floatval($this->totalVolume) < 0.01) ?
                0.01 : round(floatval($this->totalVolume), 2),
            'totalWeight' => (floatval($this->totalWeight) < 0.01) ?
                0.01 : round(floatval($this->totalWeight), 2),
            'insurance' => [
                'statedValue' => $this->getStatedValue(),
                'payer' => ($this->orderInfo->paymentInfo->isCashOnDelivery) ? 'receiver' : 'sender',
                'term' => $this->settings->default_cargo_params->isInsuranceGoodsWithDeclarePrice
            ],
            'freightName' => mb_substr($this->freightName, 0, 250,'utf8')
            // 'freightUID'    => '0xa4a904cf9927043442973c854c077430'//TODO предпочтительный вариант
        ];

        $oversizedWeight = ['oversizedWeight' => ($this->oversizedWeight < 0.01) ?
            0.01 : round(floatval($this->oversizedWeight), 2)];
        $oversizedVolume = ['oversizedVolume' => ($this->oversizedVolume < 0.01) ?
            0.01 : round(floatval($this->oversizedVolume), 2)];


        if (!empty($this->oversizedVolume) || !empty($this->oversizedWeight)) {
            $result = array_merge($result, $oversizedWeight, $oversizedVolume);
        }

        if (!empty($this->freightUID)) {
            $result = array_merge($result, ['freightUID' => $this->freightUID]);
        }

        if($this->deliveryType == 'small')
        {
            $result['quantity'] = 1;
        }

        return $result;

    }


}



