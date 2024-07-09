<?php
declare(strict_types=1);

namespace Biatech\Lazev\DTOs;

final class ProductDimensionsDTO
{
    public ?float $length;
    public ?float $width;
    public ?float $height;
    public ?float $weight;
    public ?string $unit_weight;
    public ?string $unit_sizes;

    public function __construct(?float $length, ?float $width,
                                ?float $height, ?float $weight,
                                ?string $unit_weight, ?string $unit_sizes)
    {
        $this->length = $length;
        $this->width = $width;
        $this->height = $height;
        $this->weight = $weight;
        $this->unit_weight = $unit_weight;
        $this->unit_sizes = $unit_sizes;
    }
}

