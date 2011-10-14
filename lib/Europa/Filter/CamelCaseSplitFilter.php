<?php

namespace Europa\Filter;

class CamelCaseSplitFilter implements FilterInterface
{
    public function filter($value)
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
