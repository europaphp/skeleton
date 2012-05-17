<?php

namespace Europa\Filter;

/**
 * Filters a value using multiple filters. If one of the return values of a filter is a class that
 * exits, it is returned. If it does not exist, false is returned.
 * 
 * @category Filters
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class ClassResolutionFilter extends FilterArrayAbstract
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
            $class = $filter->filter($value);
            if (class_exists($class, true)) {
                return $class;
            }
        }
        return false;
    }
}
