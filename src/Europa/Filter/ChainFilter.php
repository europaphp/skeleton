<?php

namespace Europa\Filter;

class ChainFilter extends FilterArrayAbstract
{
    public function __invoke($value)
    {
        foreach ($this as $filter) {
            $value = $filter->__invoke($value);
        }

        return $value;
    }
}