<?php

namespace Europa\App\Exception;

class NoResponse extends \Europa\Exception\Exception
{
  public $message = 'No response issued for ":request".';
}