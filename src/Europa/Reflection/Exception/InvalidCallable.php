<?php

namespace Europa\Router\Exception;

class InvalidCallable extends \Europa\Exception\Exception
{
  public $message = 'The callable could not be reflected.';
}