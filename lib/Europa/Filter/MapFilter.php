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
class MapFilter implements FilterInterface
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
     * @parma array $map The mapping to use.
     * 
     * @return \Europa\Filter\MapFilter
     */
    public function __construct(array $map)
    {
        $this->map = $map;
    }
    
    /**
     * Filters the value and returns the filtered value.
     * 
     * @param mixed $value The value to filter.
     * 
     * @return mixed
     */
    public function filter($value)
    {
        if (isset($this->map[$value])) {
            return $this->map[$value];
        }
        return $value;
    }
}
