<?php

namespace Europa\Module\Exception;

class BootstrapperNotCallable extends \Europa\Exception\Exception
{
  public $message = 'The bootstrapper for ":name" is not callable.';
}