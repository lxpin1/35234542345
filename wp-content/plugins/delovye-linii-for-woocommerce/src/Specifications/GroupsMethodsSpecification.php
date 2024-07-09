<?php

namespace Biatech\Lazev\Specifications;

use \ReflectionClass;

final class GroupsMethodsSpecification implements ISpecification
{
    
    const ONE_CARGO_SPACE = 'ONE_CARGO_SPACE';
    const SEPARATED_CARGO_SPACE = 'SEPARATED_CARGO_SPACE';
    const SINGLE_ITEM_SINGLE_SPACE = 'SINGLE_ITEM_SINGLE_SPACE';
    
    public string $groupMethod;

    public function __construct(string $groupMethod)
    {
        $this->groupMethod = $groupMethod;
    } 
    
    public function isSatisfiedBy(): bool
    {
        $reflection = new ReflectionClass($this);
         
        $values = $reflection->getConstants();
        
        return  in_array($this->groupMethod, $values, true);
        
    }
}