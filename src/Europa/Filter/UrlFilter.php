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
class UrlFilter implements FilterInterface
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
        $value = (new ClassNameFilter)->filter($value);
        $value = str_replace('\\', '/', $value);
        $value = (new CamelCaseSplitFilter)->filter($value);
        $value = array_map('strtolower', $value);
        $value = implode('-', $value);
        $value = str_replace('/-', '/', $value);
        $value = trim($value, '-');
        
        return $value;
    }
}
