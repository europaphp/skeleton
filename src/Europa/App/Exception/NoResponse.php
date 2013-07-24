<?php

namespace Europa\App\Exception;

class NoResponse extends \Europa\Exception\Exception
{
  public $message = 'Could not issue a response for the :request.';
}