<?php

namespace Europa\Di\Exception;

class CircularReference extends \Europa\Exception\Exception
{
  public $message = 'The service ":name" is being circularly referenced by ":references".';
}