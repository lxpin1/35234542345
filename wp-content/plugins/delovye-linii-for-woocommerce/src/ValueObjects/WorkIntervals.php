<?php
declare(strict_types=1);

namespace Biatech\Lazev\ValueObjects;

use Biatech\Lazev\Base\Composite\Container;
use BiaTech\Lazev\Base\Composite\Field;
use Biatech\Lazev\ValueObjects\Counteragent;

use \stdClass;

final class WorkIntervals
{
    public ?string $workStart;
    public ?string $workBreakStart;
    public ?string $workBreakEnd;
    public ?string $workEnd;

    public ?string $interval_work;
    public ?string $interval_lunch;

    public function __construct(?string $workStart=null, ?string $workBreakStart=null,
                                ?string $workBreakEnd=null, ?string $workEnd=null)
    {
  
        $this->workStart = $workStart;
        $this->workEnd = $workEnd;
        $this->workBreakStart = $workBreakStart;
        $this->workBreakEnd = $workBreakEnd;

        
    }

    public function set_interval_ant_desing(?string $interval_work, ?string $interval_lunch = null): void
    {
        
        $this->interval_work = $interval_work;
        $this->interval_lunch = $interval_lunch;
        if($interval_work != '')
        {
            $this->normalize_work_interval();
        }
        if($interval_lunch != '')
        {
            $this->normalize_work_lunch();
        }

    }

    public function normalize_work_interval(): void
    {
        if(isset($this->interval_work))
        {
            $raw_data = $this->prepareTimeFormats($this->interval_work);
            $this->workStart = $raw_data[0];
            $this->workEnd = $raw_data[1];
        }
    }


    public function normalize_work_lunch(): void
    {
        if(isset($this->interval_lunch))
        {
            $raw_array = $this->prepareTimeFormats($this->interval_lunch);
            $this->workBreakStart = $raw_array[0];
            $this->workBreakEnd = $raw_array[1];
        }
    }

    private function prepareTimeFormats(string $ant_design_format): array 
    {
        $result = explode(',', $ant_design_format);

        if(count($result) != 2)
        {
            throw new \Exception('Количество аргументов в массиве интервала времени не равно двум');
        }

        return $result;
        
    }


    public function get_time_to_request() : Container
    {
        $request_object = new Container();
        // if time not install
        if(!isset($this->workStart) || !isset($this->workEnd) )
        {
            $fieldWorktimeStart = new Field(['worktimeStart',"9:00"]);
            $fieldWorkTimeEnd = new Field(['worktimeEnd', "17:00"]);
            $request_object->add($fieldWorktimeStart)
                           ->add($fieldWorkTimeEnd);
            return $request_object;
        }

        $fieldWorktimeStart = new Field(['worktimeStart',$this->workStart]);
        $fieldWorkTimeEnd = new Field(['worktimeEnd', $this->workEnd]);
        $request_object->add($fieldWorktimeStart)
                       ->add($fieldWorkTimeEnd);

        //TODO implements times validation with ruls 4 hours and custom Exceptions
        if(isset($this->workBreakStart) && isset($this->workBreakEnd))
        {
            $request_object->add(new Field(['breakStart', $this->workBreakStart]))
                           ->add(new Field(['breakEnd', $this->workBreakEnd]));
        }
                
        return $request_object;
    }


}

