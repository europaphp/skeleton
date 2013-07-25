<?php

namespace Europa\Di\Exception;

class InvalidService extends \Europa\Exception\Exception
{
  public $message = 'The service ":name" is required to be an instance of ":type". Instance of ":class" given.';
}