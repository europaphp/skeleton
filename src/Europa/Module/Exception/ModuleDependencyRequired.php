<?php

namespace Europa\Module\Exception;

class ModuleDependencyRequired extends \Europa\Exception\Exception
{
  public $message = 'The module :dependant requires :name.';
}