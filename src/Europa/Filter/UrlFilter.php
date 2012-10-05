<?php

namespace Europa\Filter;

/**
 * Returns a valid URL from the specified value.
 * 
 * @category Filters
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class UrlFilter
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
        $value = (new ClassNameFilter)->__invoke($value);
        $value = str_replace('\\', '/', $value);
        $value = (new CamelCaseSplitFilter)->__invoke($value);
        $value = array_map('strtolower', $value);
        $value = implode('-', $value);
        $value = str_replace('/-', '/', $value);
        $value = trim($value, '-');
        
        return $value;
    }
}