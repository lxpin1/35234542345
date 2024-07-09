<?php
declare(strict_types=1);

namespace Biatech\Lazev\DTOs;


final class LoggerSettingsDTO
{
    public bool $is_logging;

    public function __construct(bool $is_logging = false)
    {
        $this->is_logging = $is_logging;
    }
}


