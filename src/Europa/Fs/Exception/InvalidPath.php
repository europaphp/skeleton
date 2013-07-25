<?php

namespace Europa\Fs\Exception;

class InvalidPath extends \Europa\Exception\Exception;
{
  public $message = 'The path ":path" does not exist.';
}