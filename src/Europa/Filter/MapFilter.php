<?php

namespace Europa\Filter;

/**
 * Returns a mapped value.
 * 
 * @category Filters
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class MapFilter
{
    /**
     * The value map.
     * 
     * @var array
     */
    private $map;
    
    /**
     * Sets up the mapping.
     * 
     * @param array $map The mapping to use.
     * 
     * @return MapFilter
     */
    public function __construct(array $map)
    {
        $this->map = $map;
    }

    /**
     * Adds mapping.
     * 
     * @return MapFilter
     */
    public function map($from, $to)
    {
        $this->map[$from] = $to;
        return $this;
    }

    /**
     * Clears the mapping.
     * 
     * @return MapFilter
     */
    public function clear()
    {
        $this->map = [];
        return $this;
    }
    
    /**
     * Filters the value and returns the filtered value.
     * 
     * @param mixed $value The value to filter.
     * 
     * @return mixed
     */
    public function __invoke($value)
    {
        if (isset($this->map[$value])) {
            return $this->map[$value];
        }

        return $value;
    }
}