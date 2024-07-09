<?php
declare(strict_types=1);

namespace BiaTech\Lazev\Specifications;

interface ISpecification
{
    public function isSatisfiedBy():bool;
}