<?php

namespace Biatech\Lazev\Requests;

use Biatech\Lazev\Base\Composite\Container;
use Biatech\Lazev\Base\Composite\Field;

final class DellinBodyCalc extends AbstractDellinRequest
{

    public ?string $defferedDate;
    public function buildDeliveryDerrival(): Container
    {
        $derrival = new Container();

        $produceDate = new Field(['produceDate', $this->getDefferedDate()]);

        $derrival->add($this->getDerrivalAddressOrTerminal());
        $derrival->add($this->getDerrivalVariantField());

        (!$this->settings->loadings_params->is_terminal_loading)?
                                                $derrival->add($this->getTimeToDerrival()) : '';

        $derrival->add($produceDate);
      
        $derrival->add($this->getRequirementsDerival());

        return $derrival;


    }

     public function getRequirementsDerival()
    {

      return  new Field(['requirements',
                         $this->settings->default_cargo_params->requirements_transport->build()]);
    }

    function getDerrivalAddressOrTerminal()
    {
        $terminalID = new Field(['terminalID', $this->settings->loadings_params->terminal_id]);

        $addressBody = new Container();

        $search = new Field(['search', $this->settings->loadings_params->loadingAddress]);
        $addressBody->add($search);
        $address = new Field(['address', $addressBody]);


        return ($this->settings->loadings_params->is_terminal_loading)?$terminalID : $address;
    }

    /**
     * @param string|null $defferedDay
     */
    public function setDefferedDate(?string $defferedDate): void
    {
        $this->defferedDate = $defferedDate;
    }

    public function getDefferedDate()
    {
        if(!isset($this->defferedDate))
        {
             $dateNow = new \DateTime();
             $delay = $this->settings->loadings_params->deliveryDelay;
             $defferedDate = $dateNow->add(\DateInterval::createFromDateString($delay.' days'));
             return $defferedDate->format('Y-m-d');
        }
    }

    private function getKLADRAddressArrival(): Container
    {
        /**
         * Для того что бы получить ответ калькулятора в 90% случаев из 100
         */

        $arrivalKLADR = $this->order->arrivalLocation->kladr_city;

        $fieldcity = new Field(['city', $arrivalKLADR]);
        $fieldstreet = new Field(['street', $arrivalKLADR]);

        $containerAddress = new Container();
        $containerAddress->add($fieldcity);
        $containerAddress->add($fieldstreet);

 //       $address = new Field(['address', $containerAddress]);

        return $containerAddress;

    }

    function buildDeliveryArrival()
    {
        $arrival = new Container();

        $arrivalKLADR = $this->order->arrivalLocation->kladr_city;

        $city = new Field(['city', $arrivalKLADR]);

        $inlinePoints = $this->order->arrivalLocation->coordinates_inline;
        $inlineAddress = $this->order->arrivalLocation->address_inline;
        $searchVariants = $inlinePoints ?? $inlineAddress;


       // $terminal = new Field(['terminalID', $this->order->arrivalLocation->terminal_id]);


        $arrival->add($this->getArivalVariantField());
        $addressBody = new Container();
        if(!$this->settings->default_cargo_params->is_terminal_unloading)
        {
                $search = new Field(['search', $searchVariants]);
                $addressBody->add($search);
        }


        if($this->settings->default_cargo_params->is_terminal_unloading)
        {
        //        $addressBody->add($this->getKLADRAddressArrival());
                $arrival->add($city);
        }

        $address = new Field(['address', $addressBody]);

        if(!$this->settings->default_cargo_params->is_terminal_unloading){
            $arrival->add($this->getTimeToArival());
            $arrival->add($address);
        }




       return $arrival;


    }
}