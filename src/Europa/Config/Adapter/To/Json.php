<?php

namespace Europa\Config\Adapter\To;
use Europa\Exception\Exception;

class Json
{
  private $options;

  public function __construct($options = 0)
  {
    $this->options = $options;
  }

  public function __invoke($data)
  {
    return json_encode($data, $this->options);
  }
}