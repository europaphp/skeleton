<?php

namespace Europa\View\Exception;

class InvalidViewScript extends \Europa\Exception\Exception
{
  public $message = 'The view ":view" does not exist.';
}