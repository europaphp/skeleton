<?php

namespace Europa\Module\Exception;

class ModuleNotFoundException extends \Europa\Exception\Exception
{
  public $message = 'The module :name does not exist.';
}