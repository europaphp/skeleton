<?php

namespace Europa\Filter;

/**
 * Filters a value using more than one filter.
 * 
 * @category Filters
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class FilterArray implements FilterInterface
{
    /**
     * The array of filters to use.
     * 
     * @var array
     */
    private $filters = array();
    
    /**
     * Adds a filter.
     * 
     * @param \Europa\Filter\FilterInterface $filter The filter to add.
     * 
     * @return \Europa\Filter\FilterArray
     */
    public function add(FilterInterface $filter)
    {
        $this->filters[] = $filter;
        return $this;
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
        foreach ($this->filters as $filter) {
            $value = $filter->filter($value);
        }
        return $value;
    }
}
