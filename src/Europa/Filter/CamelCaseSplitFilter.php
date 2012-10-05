<?php

namespace Europa\Filter;

/**
 * Splits the word by uppercase characters and returns them as an array. No other transformations are performed.
 * 
 * @category Filters
 * @package  Europa
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  Copyright (c) 2011 Trey Shugart http://europaphp.org/license
 */
class CamelCaseSplitFilter
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
        $parts = array('');

        foreach (str_split($value) as $char) {
            $lower = strtolower($char);

            if ($char === $lower) {
                $parts[count($parts) - 1] .= $lower;
            } else {
                $parts[] = $char;
            }
        }

        return $parts;
    }
}