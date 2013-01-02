<?php

namespace Europa\Filter;

class FromStringFilter
{
    public function __invoke($value)
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