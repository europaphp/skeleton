<?php

namespace Europa\Filter;

class MapFilter
{
    private $map;
    
    public function __construct(array $map)
    {
        $this->map = $map;
    }

    public function map($from, $to)
    {
        $this->map[$from] = $to;
        return $this;
    }
    
    public function __invoke($value)
    {
        if (isset($this->map[$value])) {
            return $this->map[$value];
        }

        return $value;
    }
}