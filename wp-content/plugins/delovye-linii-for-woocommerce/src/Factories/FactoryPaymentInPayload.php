<?php

namespace Biatech\Lazev\Factories;

use Biatech\Lazev\Base\Cache\CacheInterface;
use Biatech\Lazev\Base\Composite\Container;
use Biatech\Lazev\DTOs\PaymentInfoOrderDTO;
use Biatech\Lazev\Services\DellinAuthService;
use Biatech\Lazev\ValueObjects\Settings;
use Biatech\Lazev\Base\Composite\Field;

final class FactoryPaymentInPayload
{


    private Settings $settings;
    
    private bool $isRequest;
    private DellinAuthService $authService;
    
    private string $kladrArrival;
    private string $kladrDerival;
    
    private array $products;
    
    private PaymentInfoOrderDTO $paymentInfo;
    
    

    public function __construct(Settings $settings, CacheInterface $cache)
    {
            $this->settings = $settings;
            $this->authService = new DellinAuthService($settings->auth, $cache);
    }

    public function create(PaymentInfoOrderDTO $paymentInfo, string $kladrArrival,
                          string $kladrDerrival, array $products, bool $isRequest = false): Container
    {
        $this->isRequest = $isRequest;
        $this->paymentInfo = $paymentInfo;
        $this->kladrArrival = $kladrArrival;
        $this->kladrDerival = $kladrDerrival;
        $this->products = $products;
        
        return $this->buildPaymentData();
        
    }
    
    public function buildPaymentData()
    {

        $payment = new Container();

        $paymentCity = ($this->isRequest && $this->paymentInfo->isCashOnDelivery)?
                                                                        $this->kladrArrival : $this->kladrDerival;

        $primaryPayer = ($this->paymentInfo->isCashOnDelivery)? 'receiver' : 'sender';


        $fieldPaymentCity = new Field(['paymentCity', $paymentCity]);
        $typeField = new Field(['type', ($this->isRequest && $this->paymentInfo->isCashOnDelivery)?'cash':'noncash']);
        $primaryPayment = new Field(['primaryPayer', $primaryPayer]);

        $payment->add($fieldPaymentCity);
        $payment->add($typeField);
        $payment->add($primaryPayment);

        ($this->isRequest && $this->paymentInfo->isCashOnDelivery)?$payment->add($this->buildCashOnDelivery()):null;

        return $payment;

    }

    /**
     * @throws \Exception
     */
    public function buildCashOnDelivery(){

        if(empty($this->authService->getSessionID())){
            throw new \Exception('Наложенный платёж доступен только для авторизированных пользователей');
        }

        //type = 4
        //productType = 11


        $fieldCashOnDelivery = new Field(['cashOnDelivery', [
            [
                'orderNumber'=> $this->order->orderData->shipment_id,
                'orderData' => date('Y-m-d',strtotime($this->order->orderData->create_date)),
                'paymentType' => 'cash', 
                'products' =>  $this->buildProductsArray()]
            ]
        ]);

        return $fieldCashOnDelivery;
    }
    
        public function buildProductsArray(){
//            $arProducts = [];
//
//            foreach( $this->products as $product){
//
//                    $taxValueForApi = $product->getTaxValue( *100;//TODO пїЅпїЅпїЅпїЅпїЅ пїЅпїЅпїЅпїЅпїЅпїЅпїЅпїЅпїЅ пїЅпїЅпїЅпїЅпїЅпїЅпїЅпїЅ.
//
//            if($product->isTaxIncluded()){
//
//                    $price = $product->getPrice();
//
//            } else {
//                    $price = $product->getPrice() + ($product->getPrice * floatval($product->getTaxValue()));
//            }
//
//
//            $arProducts[] = $this->buildProductItem($product->getName(), $product->getProductId(), $product->getQuantity(),
//                                                    $price, $taxValueForApi);
//
//            }
//
//        return $arProducts;
        return [];
    }
    
    
}