<?php

namespace Europa\Module\Exception;

class BootstrapperNotCallable extends \Europa\Exception\Exception
{
  public $message = 'The module :name\'s bootstrapper is not callable.';
}