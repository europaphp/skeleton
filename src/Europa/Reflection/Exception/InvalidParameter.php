<?php

namespace Europa\Reflection\Exception;

class InvalidParameter extends \Europa\Exception\Exception
{
  public $message = 'The required parameter ":name" for ":function()" was not specified.';
}