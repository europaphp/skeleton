<?php

namespace Europa\App\Exception;

class NoController extends \Europa\Exception\Exception
{
  public $message = 'No controller was found for ":request".';
}