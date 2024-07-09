<?php

declare(strict_types=1);


namespace Biatech\Lazev\Factories;


use Biatech\Lazev\DTOs\AuthDTOFields;
use Biatech\Lazev\DTOs\CargoParamsDTOFields;
use Biatech\Lazev\DTOs\LoadingParamsDTO;
use Biatech\Lazev\DTOs\LoggerSettingsDTO;
use Biatech\Lazev\DTOs\PackagesSettingsDTO;
use Biatech\Lazev\ValueObjects\CounteragentInfo;
use Biatech\Lazev\ValueObjects\Settings;
use Biatech\Lazev\Adapters\SettingsAdapter;
use Biatech\Lazev\ValueObjects\WorkIntervals;


final class FactorySettings {

    public ?string $title;
    public ?AuthDTOFields $auth;
    public ?LoadingParamsDTO $loadingParams;
    public ?CargoParamsDTOFields $cargoParams;
    public ?PackagesSettingsDTO $packagesSettings;
    public ?LoggerSettingsDTO $loggerSettings;
    public ?CounteragentInfo $counteragentInfo;
    public ?WorkIntervals $workIntervals;

    public function __construct()
    {
        $this->title = null;
        $this->auth = null;
        $this->loadingParams = null;
        $this->cargoParams = null;
        $this->packagesSettings = null;
        $this->loggerSettings = null;
        $this->counteragentInfo = null;
        $this->workIntervals = null;
    }

    public function create(?int $instance_id, $context = null): Settings
    {

        if(isset($instance_id)){
            $adapter = new SettingsAdapter($instance_id);

            if(isset($adapter->raw_data) && $adapter->raw_data instanceof \stdClass)
            {
                return new Settings(
                    $adapter->title,
                $adapter->get_auth(),
                $adapter->get_loading_params(),
                $adapter->get_cargo_params(),
                $adapter->get_packages(),
                $adapter->get_logger_settings(),
                $adapter->get_counteragents(),
                $adapter->get_work_intervals()
            );

            }
        }


        return new Settings($this->title,
                            $this->auth,
                            $this->loadingParams,
                            $this->cargoParams,
                            $this->packagesSettings,
                            $this->loggerSettings,
                            $this->counteragentInfo,
                            $this->workIntervals);


    }


    /**
     * @param AuthDTOFields|null $auth
     * @return FactorySettings
     */
    public function setAuth(?AuthDTOFields $auth): FactorySettings
    {
        $this->auth = $auth;
        return  $this;
    }

    /**
     * @param CargoParamsDTOFields|null $cargoParams
     * @return FactorySettings
     */
    public function setCargoParams(?CargoParamsDTOFields $cargoParams): FactorySettings
    {
        $this->cargoParams = $cargoParams;
        return  $this;
    }

    /**
     * @param LoadingParamsDTO|null $loadingParams
     * @return FactorySettings
     */
    public function setLoadingParams(?LoadingParamsDTO $loadingParams): FactorySettings
    {
        $this->loadingParams = $loadingParams;
        return  $this;
    }

    /**
     * @param string|null $title
     * @return FactorySettings
     */
    public function setTitle(?string $title): FactorySettings
    {
        $this->title = $title;
        return  $this;
    }

    /**
     * @param PackagesSettingsDTO|null $packagesSettings
     * @return FactorySettings
     */
    public function setPackagesSettings(?PackagesSettingsDTO $packagesSettings): FactorySettings
    {
        $this->packagesSettings = $packagesSettings;
        return  $this;
    }

    /**
     * @param LoggerSettingsDTO|null $loggerSettings
     * @return FactorySettings
     */
    public function setLoggerSettings(?LoggerSettingsDTO $loggerSettings): FactorySettings
    {
        $this->loggerSettings = $loggerSettings;
        return  $this;
    }

    /**
     * @param CounteragentInfo|null $counteragentInfo
     * @return FactorySettings
     */
    public function setCounteragentInfo(?CounteragentInfo $counteragentInfo): FactorySettings
    {
        $this->counteragentInfo = $counteragentInfo;
        return  $this;
    }

    /**
     * @param WorkIntervals|null $workIntervals
     * @return FactorySettings
     */
    public function setWorkIntervals(?WorkIntervals $workIntervals): FactorySettings
    {
        $this->workIntervals = $workIntervals;
        return  $this;
    }
}