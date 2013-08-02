<?php

namespace Europa\App\Exception;

class InvalidController extends \Europa\Exception\Exception
{
  public $message = 'The controller is not valid: :controller';
}