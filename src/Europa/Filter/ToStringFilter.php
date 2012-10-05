<?php

namespace Europa\Filter;

/**
 * Converts the value to a string representation.
 * 
 * @category Filters
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class ToStringFilter
{
    /**
     * Filters the value and returns the filtered value.
     * 
     * @param mixed $value The value to filter.
     * 
     * @return mixed
     */
    public function __invoke($value)
    {
        if ($value === true) {
            $value = 'true';
        } elseif ($value === false) {
            $value = 'false';
        } elseif (is_numeric($value)) {
            $value = (string) $value;
        } elseif (is_array($value)) {
            $value = serialize($value);
        } elseif (is_null($value)) {
            $value = 'null';
        }

        return (string) $value;
    }
}