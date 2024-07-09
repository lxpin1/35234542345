<?php

namespace Biatech\Lazev\Adapters;

use Biatech\Lazev\Base\Cache\CacheWp;
use Biatech\Lazev\DTOs\OrderInfoDTO;
use Biatech\Lazev\DTOs\PaymentInfoOrderDTO;
use Biatech\Lazev\DTOs\PersonDTO;
use Biatech\Lazev\DTOs\ProductDimensionsDTO;
use Biatech\Lazev\Helpers\RolesMembers;
use Biatech\Lazev\Services\DellinLocationService;
use Biatech\Lazev\ValueObjects\CounteragentInfo;
use Biatech\Lazev\ValueObjects\Location;
use Biatech\Lazev\ValueObjects\Product;
use Biatech\Lazev\ValueObjects\Settings;
use Biatech\Lazev\ValueObjects\WorkIntervals;

final class OrderAdapter implements IAdapterOrder
{
    private ?\WC_Cart $cart;
    public ?\WC_Order $order;

    const OPF_RU_ANONIM = "0xab91feea04f6d4ad48df42161b6c2e7a";
    public array $products;
    public ?PaymentInfoOrderDTO $paymentInfo;
    private ?DellinLocationService $locationService;
    public ?Location $locationArrival;
    public ?WorkIntervals $workIntervalsArrival;
    public ?array $additionalFormData;

    public ?int $order_id;


    public function __construct(?\WC_Cart $cart,
                                Settings $settings,
                                ?array $additionalFormData = [],
                                ?\WC_Order $order = null)
    {
        $this->cart = $cart;
        $this->order = $order;
        $this->additionalFormData = $additionalFormData;
        $this->products = [];
        $cache = new CacheWp();
        $this->locationService = new DellinLocationService($settings, $cache);
        $this->setArrivalLocation();
        $this->setPaymentInfo();

    }

    /**
     * TODO: Переделать на получение корректной информации о методе оплаты.
     * @param PaymentInfoOrderDTO|null $paymentInfo
     */
    public function setPaymentInfo(): void
    {
        $this->paymentInfo = new PaymentInfoOrderDTO(null, false, false);
    }

    public function getProducts(): array
    {


        if (isset($this->cart)) {
            $items = $this->cart->get_cart();
        }

        if (isset($this->order)) {
            $items = $this->order->get_items();
        }


        foreach ($items as $item) {
            $itemID = (isset($this->cart)) ? $item['data']->get_id() : $item['product_id'];
            $itemQuantity = (isset($this->cart)) ? $item['quantity'] : $item->get_quantity();


            $productCatItem = wc_get_product($itemID);


          //  if ($productCatItem->is_in_stock())//Есть ли товар на складе
         //   {
                $product = new Product($itemID,
                    $productCatItem->get_name(),
                    $itemQuantity,
                    $productCatItem->get_price(),
                    true,
                    0);

                $dimensions = new ProductDimensionsDTO(floatval($productCatItem->get_length()),
                    floatval($productCatItem->get_width()),
                    floatval($productCatItem->get_height()),
                    floatval($productCatItem->get_weight()),
                    get_option('woocommerce_weight_unit'),
                    get_option('woocommerce_dimension_unit'));


                $product->setDimensions($dimensions);
                $product->setVendorCode($productCatItem->get_sku());

                $this->products[] = $product;
          //  }


        }


        return $this->products;

    }


    public function getCounterAgentInfo(): CounteragentInfo
    {
        if(isset($this->cart))
        {
            return new CounteragentInfo(self::OPF_RU_ANONIM, false,
            false, null, RolesMembers::RECEIVER, null,);
        }


        if(isset($this->order))
         {
             $dataOrder = $this->order->get_data();
             $result = new CounteragentInfo(self::OPF_RU_ANONIM, false,
                            false, null, RolesMembers::RECEIVER, null,);
             $contact_person = new PersonDTO(
                 $dataOrder['shipping']['first_name'].' '.$dataOrder['shipping']['last_name'],
                 $dataOrder['billing']['phone'],
                 $dataOrder['billing']['email']
                 );
             $result->add_contact_person($contact_person);
             return  $result;
         }
    }

    public function setArrivalLocation(): void
    {
        if(isset($this->cart))
        {
            $state = $this->additionalFormData['destination']['state'];
            $address = $this->additionalFormData['destination']['address'];
            $zip = $this->additionalFormData['destination']['postcode'];
            $city = $this->additionalFormData['destination']['city'];

        }

        if(isset($this->order))
        {
            $shippingInfo = $this->order->get_data()['shipping'];
            $state = $shippingInfo['state'];
            $address = $shippingInfo['address_1'];
            $zip = $shippingInfo['postcode'];
            $city = $shippingInfo['city'];
            $terminal = get_post_meta($this->order->get_data()['id'],'terminal_id', true);
        }

        $addressInline = $zip . ' Россия, ' . $state . ', ' . $city . ', ' . $address;

        $kladrCode = $this->locationService->findKLADR($city, $state, $zip)->code;

        $terminal_id = $terminal ?? null;


        $this->locationArrival = new Location($addressInline, $kladrCode,
                            null, $terminal_id);

    }

    public function getOrderInfo(): OrderInfoDTO
    {
        if(isset($this->cart))
        {
            return new OrderInfoDTO($this->paymentInfo, floatval($this->cart->get_displayed_subtotal()),
                                   floatval($this->cart->total), null,
                                    null, null);
        }

        if(isset($this->order))
        {
            $totalPrice =floatval($this->order->get_subtotal());
            $methodVar = $this->order->get_shipping_methods();//notice fix
            $shippingMethod = array_shift($methodVar);
            $shippingPrice = $shippingMethod["total"];
            $this->order_id = $this->order->get_data()['id'];
            return new OrderInfoDTO($this->paymentInfo, $totalPrice,
                                $totalPrice, $this->order_id, null, $shippingPrice );
        }
    }

    public function workTimeArrival(): WorkIntervals
    {
        $this->workIntervalsArrival = new WorkIntervals('9:00', null, null, '18:00');
        return $this->workIntervalsArrival;
    }


    public function getArrivalLocation(): Location
    {
        return $this->locationArrival;
    }
}