<?php
declare(strict_types=1);


namespace Biatech\Lazev\DTOs;

final class YuriInfoDTO
{
    public ?string $yuri_name;
    public ?string $yuri_inn;
    public ?string $yuri_address;


    public function __construct(?string $yuri_name,
                                ?string $yuri_inn,
                                ?string $yuri_address)
    {
        $this->yuri_name = $yuri_name;
        $this->yuri_inn = $yuri_inn;
        $this->yuri_address = $yuri_address;
    }



}

