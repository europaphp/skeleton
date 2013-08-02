<?php

namespace Europa\Config\Adapter\From;

class Xml
{
  public function __invoke($data)
  {
    return json_decode(json_encode((array) simplexml_load_string($data)));
  }
}