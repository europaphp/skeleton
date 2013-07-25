<?php

namespace Europa\Router\Exception;

class InvalidRouteConfiguration extends \Europa\Exception\Exception
{
  public $message = 'The route ":route" must specify a "call" option.';
}