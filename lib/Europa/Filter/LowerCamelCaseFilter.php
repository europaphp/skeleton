<?php

namespace Europa\Filter;

class LowerCamelCaseFilter implements FilterInterface
{
    public function filter($value)
    {
        $ucc   = new UpperCamelCaseFilter;
        $value = $ucc->filter($value);
        $value = lcfirst($value);
        return $value;
    }
}
