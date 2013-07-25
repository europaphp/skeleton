<?php

namespace Europa\Fs\Exception;

class InvalidRootPath extends \Europa\Exception\Exception;
{
  public $message 'The root path ":path" does not exist.';
}