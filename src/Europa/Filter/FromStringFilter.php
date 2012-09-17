<?php

namespace Europa\Filter;

/**
 * Converts the value from a string to a scalar data type.
 * 
 * @category Filters
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class FromStringFilter implements FilterInterface
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
        if ($value === 'true') {
            $value = true;
        } elseif ($value === 'false') {
            $value = false;
        } elseif (is_numeric($value)) {
            $value = strpos($value, '.') === false ? (int) $value : (float) $value;
        } elseif ($value === 'null') {
            $value = null;
        }
        return $value;
    }
}