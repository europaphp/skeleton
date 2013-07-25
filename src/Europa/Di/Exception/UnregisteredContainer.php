<?php

namespace Europa\Di\Exception;

class UnregisteredContainer extends \Europa\Exception\Exception
{
  public $message = 'The container ":name" is not registered.';
}