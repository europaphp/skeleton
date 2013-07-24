<?php

namespace Europa\Module\Exception;

class DuplicateModuleName extends \Europa\Exception\Exception
{
  public $message = 'Cannot add module ":name" because a module of the same name already exists. This may be because another module you are adding is attempting to use the same name.';
}