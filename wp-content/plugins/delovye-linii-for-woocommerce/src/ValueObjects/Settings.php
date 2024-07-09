<?php
declare(strict_types=1);

namespace Biatech\Lazev\ValueObjects;

use Biatech\Lazev\DTOs\AuthDTOFields;
use Biatech\Lazev\DTOs\LoadingParamsDTO;
use Biatech\Lazev\DTOs\CargoParamsDTOFields;
use Biatech\Lazev\DTOs\LoggerSettingsDTO;


use \ArrayObject;

final class Settings
{
    public ?string $name;
    public ?AuthDTOFields $auth;
    public ?LoadingParamsDTO $loadings_params;
    public ?CargoParamsDTOFields $default_cargo_params;
    public ?PackagesRequests $packages;
    public ?LoggerSettingsDTO $logging;
    public ?array $counteragents;

    public ?WorkIntervals $workIntervalsDerrival;
//    public 

    public function __construct(?string $name, ?AuthDTOFields $auth, ?LoadingParamsDTO $loadings_params,
                        ?CargoParamsDTOFields $default_cargo_params, ?PackagesRequests $packages,
                        ?LoggerSettingsDTO $logging, ?array $counteragents, ?WorkIntervals $workIntervalsDerrival)
    {
        $this->name = $name;
        
        $this->set_auth_params($auth);
        $this->set_loading_params($loadings_params);
        $this->set_default_cargo_params($default_cargo_params);
        $this->set_packages($packages);
        $this->set_logging($logging);
        $this->set_counteragents($counteragents);
        $this->set_workIntervalsDerrival($workIntervalsDerrival);
    }

    public function set_auth_params(?AuthDTOFields $auth_fields):void
    {

        //TODO validation parameters
        $this->auth = $auth_fields;
    }

    public function set_loading_params(?LoadingParamsDTO $loading_params): void
    {
        //TODO validation parameters
        $this->loadings_params = $loading_params;
    }

    public function set_default_cargo_params(?CargoParamsDTOFields $default_cargo_params): void
    {
        //TODO validation parameters
        $this->default_cargo_params = $default_cargo_params;
    }

    /**
     * @param PackagesRequests|null $packages
     */
    public function set_packages(?PackagesRequests $packages): void
    {
        $this->packages = $packages;
    }

    /**
     * @param LoggerSettingsDTO|null $logging
     */
    public function set_logging(?LoggerSettingsDTO $logging): void
    {
        $this->logging = $logging;
    }

    /**
     * @param ArrayObject|null $counteragents
     */
    public function set_counteragents(?array $counteragents): void
    {
        $this->counteragents = $counteragents;
    }


    /**
     * @param WorkIntervals|null $workIntervalsDerrival
     */
    public function set_workIntervalsDerrival(?WorkIntervals $workIntervalsDerrival): void
    {

            $this->workIntervalsDerrival = $workIntervalsDerrival;

            if(!isset($this->workIntervalsDerrival))
            {
                $this->workIntervalsDerrival = new WorkIntervals(null,
                                                null,
                                                null,
                                                null);
            }

    }

}