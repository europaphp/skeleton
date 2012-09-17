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
class ChainFilter extends FilterArrayAbstract
{
    /**
     * Filters the value and returns the filtered value.
     * 
     * @param mixed $value The value to filter.
     * 
     * @return mixed
     */
    public function filter($value)
    {
        foreach ($this->getAll() as $filter) {
            $value = $filter->filter($value);
        }
        return $value;
    }
}