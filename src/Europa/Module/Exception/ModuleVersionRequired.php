<?php

namespace Europa\Module\Exception;

class ModuleVersionRequired extends \Europa\Exception\Exception
{
  public $message = 'The module ":name", currently at v:version", is required to be at v:requiredVersion by :dependant.';
}