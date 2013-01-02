<?php

namespace Europa\Filter;

class LowerCamelCaseFilter
{
    public function __invoke($value)
    {
        $ucc   = new UpperCamelCaseFilter;
        $value = $ucc->__invoke($value);
        $value = lcfirst($value);

        return $value;
    }
}