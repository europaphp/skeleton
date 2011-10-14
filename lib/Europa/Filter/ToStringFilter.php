<?php

namespace Europa\Filter;

class ToStringFilter implements FilterInterface
{
    public function filter($value)
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
