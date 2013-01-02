<?php

namespace Europa\Filter;

class ClassResolutionFilter extends FilterArrayAbstract
{
    public function __invoke($value)
    {
        foreach ($this as $filter) {
            $class = $filter->__invoke($value);
            
            if (class_exists($class, true)) {
                return $class;
            }
        }
    }
}