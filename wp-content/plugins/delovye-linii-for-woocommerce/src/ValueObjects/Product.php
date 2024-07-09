<?php

namespace Biatech\Lazev\ValueObjects;



use Biatech\Lazev\DTOs\PackageForRequestDTO;
use Biatech\Lazev\DTOs\ProductDimensionsDTO;

final class Product
{

    public string $productID;
    public string $name;
    public int $quantity;
    public ProductDimensionsDTO $dimensions;
    public string $price; //mb float
    public array $packages;
    public bool $isTaxIncluded;
    public float $taxValue;
    
    public string $vendorCode;//Артикул



    public function __construct(string $productID, string $name, int $quantity,
                                string $price, bool $isTaxIncluded, float $taxValue)
    {
        $this->packages = [];
        $this->productID = $productID;
        $this->name = $name;
        $this->quantity = $quantity;
        $this->price = $price;
        $this->isTaxIncluded = $isTaxIncluded;
        $this->taxValue = $taxValue;
    }

    /**
     * @param string $vendorCode
     */
    public function setVendorCode(string $vendorCode): void
    {
        $this->vendorCode = $vendorCode;
    }
        
    /**
     * @param ProductDimensionsDTO $dimensions
     */
    public function setDimensions(ProductDimensionsDTO $dimensions): void
    {
        $length = self::convertToM($dimensions->length, $dimensions->unit_sizes);
        $width = self::convertToM($dimensions->width, $dimensions->unit_sizes);
        $height = self::convertToM($dimensions->height, $dimensions->unit_sizes);
        
        $weight = self::convertToKg($dimensions->weight, $dimensions->unit_weight);
        
        $normalizeData = new ProductDimensionsDTO($length, $width, $height, $weight,
                                    $dimensions->unit_weight, $dimensions->unit_sizes );
        
        $this->dimensions = $normalizeData;
    }

    
    public function addPackages(PackageForRequestDTO $package): void
    {
        $this->packages[] = $package;
    }

    /**
     * @return ProductDimensionsDTO
     */
    public function getDimensions(): ProductDimensionsDTO
    {
        return $this->dimensions;
    }
    

    /**
     * @return array
     */
    public function getPackages(): array
    {
        return $this->packages;
    }
    
    
    public static function convertToM($value, $units):float
    {
        $units = strtoupper($units);
            switch($units){
                case 'CM':
                    return $value/100;
                    break;
                case 'MM':
                    return $value/1000;
                    break;
                case 'M' :
                    return $value;
                    break;
                default:
                    throw new \Exception('Невозможно перевести единицы в метры. Неизвестная единица измерения '.$units);
                    break;
            }
        }
    
    
    public static function convertToKg($value, $units): float
    {
        $units = strtoupper($units);
            switch($units){
                case 'G':
                    return $value/1000;
                    break;
                case 'T':
                    return $value*1000;
                    break;
                case 'KG' :
                    return $value;
                    break;
                default:
                    throw new \Exception('Невозможно товар перевести в КГ. Неизвестная единица измерения '.$units);
                    break;
            }
        }
    

}