<?php

namespace Europa\Filter;

/**
 * Returns a camel cased string with the first character uppercase.
 * 
 * @category Filters
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class UpperCamelCaseFilter
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
        $temp  = array();
        $parts = preg_split('/[^a-zA-Z0-9]/', $value);

        foreach ($parts as $part) {
            $part = trim($part);

            if (!$part) {
                continue;
            }

            $temp[] = ucfirst($part);
        }

        $value = implode('', $temp);

        return $value;
    }
}