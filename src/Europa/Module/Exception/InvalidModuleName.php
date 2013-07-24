<?php

namespace Europa\Module\Exception;

class InvalidModuleName extends \Europa\Exception\Exception
{
  public $message = 'Invalid module name: :name. Modules names are required to be in the format "vendor-name/module-name". The name must be compliant with https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md.';
}