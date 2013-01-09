<?php

namespace Europa\View;
use Europa\Config\Config;

class Jsonp extends Json
{
    private $callback;

    public function __construct($callback)
    {
        $this->callback = $callback;
    }
  
    public function __invoke(array $context = array())
    {
        return $this->callback . '(' . parent::render($context) . ')';
    }
}