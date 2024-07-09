<?php

namespace Biatech\Lazev\Specifications;
/**
 * Проверяем всё, что можем проверить по ниже приведённым статьям.
 * Имплементированные проверки:
 *  - проверка включённого фича-флага;
 *  - габариты на соответствие правил (к сожалению, хардкодом);
 *  - наличие терминалов в городе отправителя и получателя (без радиуса в 100км);
 *  - проверка направления (должно быть "от адреса - до адреса")
 * https://www.dellin.ru/articles/325/ -
 * https://www.dellin.ru/info/parcels/ -
 */

use Biatech\Lazev\Base\Cache\CacheInterface;
use Biatech\Lazev\DTOs\ProductDimensionsDTO;
use Biatech\Lazev\Services\DellinTerminalsService;
use Biatech\Lazev\ValueObjects\Settings;

final class SmallCargoSpecification implements ISpecification
{

    private Settings $settings;
    
    const SMALL_STRICTS = [
        'length' => 0.54,
        'width_height'=>0.39,
        'totalVolume'=>0.1,
        'totalWeight' => 30
      ];
    
    private DellinTerminalsService $terminalsService;
    
    public string $kladrArrival;
    public string $kladrDerrival;
    
    public int $totalVolume;
    public int $totalWeight;
    
    
    public ProductDimensionsDTO $dimensions;
    
    public function __construct(Settings $settings, string $kladrArrival, string $kladrDerrival,
                                ProductDimensionsDTO $dimensions, int $totalVolume, int $totalWeight,
                                CacheInterface $cache)
    {
        
        $this->settings = $settings;
        $this->kladrArrival = $kladrArrival;
        $this->kladrDerrival = $kladrDerrival;
        $this->dimensions = $dimensions;
        $this->totalVolume = $totalVolume;
        $this->totalWeight = $totalWeight;
        
        $this->terminalsService = new DellinTerminalsService($settings, $cache);
    }


    /**
     * Проверяем на прохождение габаритов, объёма и массы.
     */
    public function  isDimensionsPass(): bool
    {


        $specifications = new DimensionsPassSpecification(self::SMALL_STRICTS, $this->totalVolume,
                                                         $this->totalWeight, $this->dimensions);
        return  !$specifications->isSatisfiedBy();
    }

    public function hasTerminalsInCityArrival(): bool
    {
        $counts = $this->terminalsService->getCountsTerminalsInCity($this->kladrArrival);
        return  $counts > 0;
    }

    public function hasTerminalsInCityDerrival(): bool
    {
        $counts = $this->terminalsService->getCountsTerminalsInCity($this->kladrDerrival);
        return $counts > 0;
    }

    public  function hasNeedlyDirection(): bool
    {
        return (!$this->settings->loadings_params->is_terminal_loading &&
                !$this->settings->default_cargo_params->is_terminal_unloading);
    }
    public function isSatisfiedBy(): bool
    {
        if(!$this->settings->default_cargo_params->isSmallGoods)
        {
            return false;
        }

        if(!$this->hasNeedlyDirection())
        {
            return false;
        }

        if(!$this->isDimensionsPass())
        {
            return  false;
        }

        if(!$this->hasTerminalsInCityArrival() && !$this->hasTerminalsInCityDerrival())
        {
            return  false;
        }
        
        return  true;
    }
    
    
}