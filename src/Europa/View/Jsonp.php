<?php

namespace Europa\View;
use Europa\Config\Config;

class Jsonp extends Json
{
    private $config;

    public function __construct($config = [])
    {
        $this->config = new Config($this->config, $config);
    }
  
    public function __invoke(array $context = array())
    {
        return $this->callback . '(' . parent::render($context) . ')';
    }
}