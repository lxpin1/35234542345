<?php
declare(strict_types=1);

namespace Biatech\Lazev\ValueObjects;

use \ArrayObject;

final class RequirementsRequest
{


    public ?string $requirements_settings;

    public function __construct(?string $requirements_settings)
    {
        $this->requirements_settings = $requirements_settings;
    }


    public function build(): array
    {
        $result = [];
               
        if(
            isset($this->requirements_settings) &&
            $this->requirements_settings != ''
        )
        {
            $prepare_string_to_array = explode(',', $this->requirements_settings);
            foreach ($prepare_string_to_array as $requirement)
            {
                $result[] = $requirement;
            }
        }


        return $result;
    }
}

