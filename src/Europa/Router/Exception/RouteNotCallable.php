<?php

namespace Europa\Router\Exception;

class RouteNotCallable extends \Europa\Exception\Exception
{
  public $message = 'The controller provided for ":route" is not callable.';
}