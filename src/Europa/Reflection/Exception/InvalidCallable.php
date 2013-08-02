<?php

namespace Europa\Reflection\Exception;

class InvalidCallable extends \Europa\Exception\Exception
{
  public $message = 'A variable with the type of ":type" cannot be reflected.';
}