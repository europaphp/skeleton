<?php

namespace Europa\View;
use Europa\Config;

class Jsonp
{
  private $config = [
    'callback' => 'jsonp_callback'
  ];

  public function __construct($config = [])
  {
    $this->config = new Config\Config($this->config, $config);
    $this->json = new Json($config);
  }

  public function __invoke(array $context = array())
  {
    return $this->config['callback'] . '(' . $this->json->__invoke($context) . ')';
  }
}