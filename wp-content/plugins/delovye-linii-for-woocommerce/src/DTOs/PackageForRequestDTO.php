<?php

declare(strict_types=1);


namespace Biatech\Lazev\DTOs;


final class PackageForRequestDTO
{
    public ?string $uid;
    // TODO implement dynamic settings
    // public ?string $name;
    // public ?string $alias;
    // public ?string $uid_services;
    // public ?string $uid_type;
    // public ?int $count;
    // public ?bool $is_quantitative;

    public function __construct(?string $uid_service, ?int $count)
    {
        $this->uid = $uid_service;
        if(isset($count)){
            $this->count = $count;
        }

    }
}

