<?php

namespace Europa\Filter;

class ToStringFilter
{
    public function __invoke($value)
    {
        if (is_string($value)) {
            return $value;
        }

        if ($value === true) {
            $value = 'true';
        } elseif ($value === false) {
            $value = 'false';
        } elseif (is_numeric($value)) {
            $value = (string) $value;
        } elseif (is_array($value) || is_object($value)) {
            $value = serialize($value);
        } elseif (is_null($value)) {
            $value = 'null';
        }

        return (string) $value;
    }
}