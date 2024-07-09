<?php
declare(strict_types=1);



namespace Biatech\Lazev\Adapters;

use Biatech\Lazev\DTOs\AuthDTOFields;
use Biatech\Lazev\DTOs\LoadingParamsDTO;
use Biatech\Lazev\DTOs\CargoParamsDTOFields;
use Biatech\Lazev\DTOs\LoggerSettingsDTO;

use Biatech\Lazev\ValueObjects\PackagesRequests;
use Biatech\Lazev\ValueObjects\WorkIntervals;


interface IAdapterSettings
{
    public function get_auth(): AuthDTOFields;

    public function get_loading_params(): LoadingParamsDTO;

    public function get_cargo_params(): CargoParamsDTOFields;

    public function get_packages(): PackagesRequests;

    public function get_counteragents(): array;

    public function get_logger_settings(): LoggerSettingsDTO;

    public function get_work_intervals(): WorkIntervals;

}