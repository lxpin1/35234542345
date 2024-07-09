<?php

declare(strict_types=1);


namespace Biatech\Lazev\Adapters;
/**
 * Адаптер позволяет маппить сущности от окружения не взирая 
 */
use Biatech\Lazev\Repositories\WoocommerceSettings;


//DTOs

use Biatech\Lazev\DTOs\AuthDTOFields;
use Biatech\Lazev\DTOs\LoadingParamsDTO;
use Biatech\Lazev\DTOs\PersonDTO;
use Biatech\Lazev\DTOs\CargoParamsDTOFields;
use Biatech\Lazev\DTOs\LoggerSettingsDTO;
use Biatech\Lazev\DTOs\ProductDimensionsDTO;
use Biatech\Lazev\DTOs\YuriInfoDTO;

use Biatech\Lazev\Specifications\GroupsMethodsSpecification;
use Biatech\Lazev\ValueObjects\PackagesRequests;
use Biatech\Lazev\ValueObjects\CounteragentInfo;
use Biatech\Lazev\Helpers\RolesMembers;

use \ArrayObject;
use Biatech\Lazev\ValueObjects\RequirementsRequest;
use Biatech\Lazev\ValueObjects\WorkIntervals;
use \stdClass;

class SettingsAdapter implements IAdapterSettings {

    public int $instance_id;

    private LoggerInterface $logger;

    public ?stdClass $raw_data;
    
    public ?string $title;

    public function __construct(int $instance_id)
    {
        $rep = new WoocommerceSettings();
        
        $this->instance_id = $instance_id;
        $settingsInfo = $rep->get_options_by_instance_id($instance_id);
        if(is_bool($settingsInfo) || is_null($settingsInfo))
        {
            $this->raw_data =null;
            $this->title = null;
        }

        if(is_string($settingsInfo))
        {
            $this->raw_data = json_decode($settingsInfo);
            $this->title = $this->raw_data->title;
        }


    }
    
    public function get_auth(): AuthDTOFields
    {

        return new AuthDTOFields(
            $this->raw_data->appkey,
            $this->raw_data->login,
            $this->raw_data->password
        );
    }

    public function get_loading_params(): LoadingParamsDTO
    {

        $groupType = ($this->raw_data->groupType == '')?GroupsMethodsSpecification::SINGLE_ITEM_SINGLE_SPACE:$this->raw_data->groupType;
        return new LoadingParamsDTO(
            $groupType,
            $this->raw_data->locationKladr,
            $this->raw_data->location,
            self::prepareBoolParams($this->raw_data->derrivalType),
            ($this->raw_data->fieldTerminal == '')?null:
                                                            (int)$this->raw_data->fieldTerminal,
            $this->raw_data->fieldAddress,
            (int)$this->raw_data->defferedDays
        );
    }

    public function get_cargo_params(): CargoParamsDTOFields
    {
        $demensions = null;
        if($this->raw_data->isUseDefaultCargoParams)
        {
            $demensions = new ProductDimensionsDTO(
                floatval($this->raw_data->length),
                floatval($this->raw_data->width),
                floatval($this->raw_data->height),
                floatval($this->raw_data->weight),
                'g',
                'mm'
                );
        }


        $typeLoading = self::prepareBoolParams($this->raw_data->typeLoading);
        $requirementsTransport = (!$typeLoading)?
                new RequirementsRequest($this->raw_data->requirementsTransport):new RequirementsRequest('');
        return new CargoParamsDTOFields(
            self::prepareBoolParams($this->raw_data->includeSmallCargo),
            self::prepareBoolParams($this->raw_data->isInsurance),
            $demensions,
            $requirementsTransport,
            self::prepareBoolParams($this->raw_data->isUseDefaultCargoParams),
            self::prepareBoolParams($this->raw_data->arrivalGoodsLoading)
            );

    }

    public function get_packages(): PackagesRequests
    {
        return new PackagesRequests($this->raw_data->additionalPackages);
    }

    public function get_counteragents(): array
    {
        $result = [];
        
        $contactPerson = new \Biatech\Lazev\DTOs\PersonDTO(
            $this->raw_data->contactPersonName,
            $this->raw_data->contactPersonPhone,
            $this->raw_data->contactPersonEmail
            );
        
        $yuriInfo = new \Biatech\Lazev\DTOs\YuriInfoDTO(
            $this->raw_data->companyName,
            $this->raw_data->inn,
            $this->raw_data->yuriAddress
            );
        
        $counteragent_uid = null;
        if( isset($this->raw_data->counteragents) &&
            $this->raw_data->counteragents != ""
        )
        {
            $counteragent_uid = $this->raw_data->counteragents;
        }
        
        $counteragent = new CounteragentInfo(
            $this->raw_data->opf,
            true,
            true,
            $this->raw_data->country,
            RolesMembers::SENDER,
            $counteragent_uid
            );
        
        $counteragent->add_contact_person($contactPerson);
        
        $counteragent->set_yuri_info($yuriInfo);
        
        $result[] = $counteragent;
        
        return $result;
    }

    public function get_logger_settings(): LoggerSettingsDTO
    {
        return new LoggerSettingsDTO(self::prepareBoolParams($this->raw_data->isLogs));
    }
    
    public function get_work_intervals(): WorkIntervals
    {
        $workIntervals = new WorkIntervals();
        $workIntervals->set_interval_ant_desing($this->raw_data->intervalWork, $this->raw_data->intervalLunch);

        return $workIntervals;
    }

    public static function prepareBoolParams(?string $value): bool
    {
            return $value  == 'true';
    }


}



