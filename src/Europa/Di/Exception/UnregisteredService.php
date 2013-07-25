<?php

namespace Europa\Di\Exception;

class UnregisteredService extends \Europa\Exception\Exception
{
  public $message = 'The service ":name" was not registered in ":container".';
}