<?php

namespace Europa\Reflection\DocTag;

interface DocTagInterface
{
    public function __construct($tag, $value = null);

    public function __toString();
    
    public function tag();
    
    public function value();

    public function parse($tag);

    public function compile();
}