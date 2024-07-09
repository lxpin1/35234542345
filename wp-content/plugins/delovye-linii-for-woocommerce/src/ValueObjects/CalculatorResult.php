<?php

namespace Biatech\Lazev\ValueObjects;

final class CalculatorResult
{
    public float $price;
    public \DateTime $dateDerrival;
    public \DateTime $dateArrival;
    public array $terminals;

    public function __construct(float $price, ?\DateTime $dateDerrival, 
                                ?\DateTime $dateArrival, array $terminals = [])
    {
        $this->price = $price;
        $this->dateDerrival = $dateDerrival;
        $this->dateArrival = $dateArrival;
        $this->terminals = $terminals;
    }
    
    public function getDays(): string
    {
        $interval = date_diff($this->dateDerrival, $this->dateArrival);
        return $interval->format('%D');
    }
    public function getDaysPluralFormat():string
    {
        $days = $this->getDays();
        if($days == 0)
        {
            //Если количество дней неудалось посчитать, делаем 0.
            return '';
        }
        $form = self::plural($days, 'день', 'дня', 'дней');
        return $days.' '.$form;
    }
    
    protected static function plural($n, $form1, $form2, $form3):string
    {
        return in_array($n % 10, array(2,3,4)) && !in_array($n % 100, array(11,12,13,14)) ? $form2 : ($n % 10 == 1 ? $form1 : $form3);
    }
}