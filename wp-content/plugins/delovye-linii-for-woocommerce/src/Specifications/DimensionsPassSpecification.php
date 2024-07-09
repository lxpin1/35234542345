<?php


namespace Biatech\Lazev\Specifications;
/**
 * Данная спецификация отвечает на чёткий вопрос попадает в ограничения по условию или нет.
 * Пока через эту спецификацию проходит негабарит и максимальные параметры фуры.
 * https://www.dellin.ru/articles/325/ -
 * https://www.dellin.ru/info/parcels/ -
 */

use Biatech\Lazev\DTOs\ProductDimensionsDTO;


final class DimensionsPassSpecification implements ISpecification
{
    /**
     * Условия для проверки.  Представленно в виде массива с параметрами:
     * totalVolume - общий объём или конерктный объём.
     * totalWeight - общая масса или масса конкретного грузоместа
     * length - длина (или другая наибольшая сторона)
     * width_height - ширина или высота . Сверяем по условию и ширину и высоту.
     */
    public array $conditions;
    
    /**
     *
     */
    public float $totalVolume;
    public float $totalWeight;
    
    /**
     * Контейнер для сравниваемых ВГХ
     */
    public ProductDimensionsDTO $dimensions;
    

    public function __construct(array $conditions, float $totalVolume, float $totalWeight,
                                ProductDimensionsDTO $dimensions)
    {
        $this->conditions = $conditions;
        $this->totalVolume = $totalVolume;
        $this->totalWeight = $totalWeight;
        $this->dimensions = $dimensions;
    }

    public function isSatisfiedBy(): bool
    {

        if($this->totalVolume >= $this->conditions['totalVolume'])
        {
            return true;
        }

        if($this->totalWeight >= $this->conditions['totalWeight'])
        {
            return true;
        }

        if($this->dimensions->length >= $this->conditions['length'])
        {
            return  true;
        }

        if($this->dimensions->width >= $this->conditions['width_height'])
        {
            return true;
        }

        if($this->dimensions->height >= $this->conditions['width_height'])
        {
            return true;
        }

        return  false;
    }
}