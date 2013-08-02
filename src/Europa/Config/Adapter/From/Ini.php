<?php

namespace Europa\Config\Adapter\From;

class Ini
{
  public function __invoke($data)
  {
    return parse_ini_string($data);
  }
}